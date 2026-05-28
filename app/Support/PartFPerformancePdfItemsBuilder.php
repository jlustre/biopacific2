<?php

namespace App\Support;

use App\Models\DocType;
use App\Models\EmployeeAssessmentItemEntry;
use App\Models\EmployeePerformanceAssessment;
use App\Models\EmployeePerformanceItem;
use App\Models\EmployeePerformanceSectionComment;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
class PartFPerformancePdfItemsBuilder
{
    /**
     * @return list<array{item_label: string, indent_level: int, is_parent?: bool, is_section_comment?: bool, rating?: string, review_date?: string, reviewer_name?: string, comments?: string}>
     */
    public static function build(EmployeePerformanceAssessment $assessment): array
    {
        $scorableIds = PartFPerformanceScoring::scorableItemIds();
        $rawItems = EmployeePerformanceItem::query()->orderBy('order')->get();

        if ($rawItems->isEmpty()) {
            return [];
        }

        $performanceSections = $rawItems->pluck('section')->unique()->filter()->values();
        $docTypeIdBySection = DocType::query()
            ->whereIn('name', $performanceSections)
            ->pluck('id', 'name');
        $sectionCommentsByDocTypeId = EmployeePerformanceSectionComment::query()
            ->where('employee_num', $assessment->employee_num)
            ->where('assessment_period_id', $assessment->assessment_period_id)
            ->whereIn('doc_type_id', $docTypeIdBySection->values())
            ->pluck('comment', 'doc_type_id');

        $entries = EmployeeAssessmentItemEntry::query()
            ->where('employee_num', $assessment->employee_num)
            ->where('assessment_period_id', $assessment->assessment_period_id)
            ->where('assessment_type', 'performance')
            ->whereNull('revoked_at')
            ->orderByDesc('assessment_date')
            ->orderByDesc('id')
            ->get()
            ->groupBy('source_item_id')
            ->map(fn (Collection $group) => $group->first());

        $assessmentItems = $assessment->itemsArray();
        $reviewerIds = $entries->pluck('assessed_by')->filter()->unique()->values();
        $reviewerNamesById = $reviewerIds->isEmpty()
            ? collect()
            : User::query()->whereIn('id', $reviewerIds)->pluck('name', 'id');

        $structuredItems = self::structuredPerformanceItems($rawItems);
        $ratedItemIds = [];

        foreach ($structuredItems as $structuredItem) {
            if ($structuredItem['is_parent'] || ! isset($scorableIds[$structuredItem['id']])) {
                continue;
            }

            $rating = self::ratingForItem($structuredItem['id'], $entries, $assessmentItems);
            if ($rating !== null && $rating !== '') {
                $ratedItemIds[$structuredItem['id']] = true;
            }
        }

        $pdfItems = [];
        $currentSection = null;
        $sectionHasOutputItems = false;
        $sectionHeaderAdded = false;

        $flushSectionComment = function (?string $section) use (
            &$pdfItems,
            &$sectionHasOutputItems,
            $docTypeIdBySection,
            $sectionCommentsByDocTypeId,
        ): void {
            if (! $section || ! $sectionHasOutputItems) {
                return;
            }

            $docTypeId = $docTypeIdBySection->get($section);
            $commentText = $docTypeId
                ? trim((string) ($sectionCommentsByDocTypeId->get($docTypeId) ?? ''))
                : '';

            $pdfItems[] = [
                'item_label' => 'Comments',
                'indent_level' => 0,
                'is_section_comment' => true,
                'comments' => $commentText,
            ];
            $sectionHasOutputItems = false;
        };

        foreach ($structuredItems as $index => $structuredItem) {
            $itemSection = $structuredItem['section'];

            if ($itemSection !== $currentSection) {
                $flushSectionComment($currentSection);
                $currentSection = $itemSection;
                $sectionHeaderAdded = false;
            }

            if ($structuredItem['is_parent']) {
                if (! self::parentHasRatedDescendant($structuredItems, $index, $ratedItemIds, $scorableIds)) {
                    continue;
                }

                if (! $sectionHeaderAdded) {
                    $pdfItems[] = [
                        'item_label' => $itemSection,
                        'indent_level' => 0,
                        'is_parent' => true,
                    ];
                    $sectionHeaderAdded = true;
                }

                $pdfItems[] = [
                    'item_label' => $structuredItem['item_label'],
                    'indent_level' => $structuredItem['indent_level'],
                    'is_parent' => true,
                ];
                $sectionHasOutputItems = true;

                continue;
            }

            $itemId = $structuredItem['id'];
            if (! isset($ratedItemIds[$itemId])) {
                continue;
            }

            if (! $sectionHeaderAdded) {
                $pdfItems[] = [
                    'item_label' => $itemSection,
                    'indent_level' => 0,
                    'is_parent' => true,
                ];
                $sectionHeaderAdded = true;
            }

            $entry = $entries->get($itemId);
            $rating = self::ratingForItem($itemId, $entries, $assessmentItems);
            $rating = strtoupper(trim((string) $rating));
            $reviewDate = optional($entry?->assessment_date)->toDateString() ?? '';
            $reviewerName = trim((string) ($reviewerNamesById[$entry?->assessed_by] ?? ''));

            $pdfItems[] = [
                'item_label' => $structuredItem['item_label'],
                'indent_level' => $structuredItem['indent_level'],
                'is_parent' => false,
                'rating' => $rating,
                'review_date' => self::formatShortDate($reviewDate),
                'reviewer_name' => $reviewerName,
                'comments' => trim((string) ($entry?->comments ?? '')),
            ];
            $sectionHasOutputItems = true;
        }

        $flushSectionComment($currentSection);

        return $pdfItems;
    }

    /**
     * @param  Collection<int, EmployeePerformanceItem>  $rawItems
     * @return list<array{id: int, section: string, item_label: string, indent_level: int, is_parent: bool}>
     */
    protected static function structuredPerformanceItems(Collection $rawItems): array
    {
        $structured = [];
        $values = $rawItems->values();

        foreach ($values as $itemIdx => $item) {
            $rawItemText = trim(strip_tags((string) ($item->item ?? '')));
            preg_match('/^(-+)/', $rawItemText, $itemIndentMatches);
            $indentLevel = min(strlen($itemIndentMatches[1] ?? ''), 2);
            $displayItem = ltrim((string) preg_replace('/^(-+)/', '', $rawItemText), '-');
            $nextItem = $values->get($itemIdx + 1);
            $nextRawItemText = trim(strip_tags((string) ($nextItem?->item ?? '')));
            preg_match('/^(-+)/', $nextRawItemText, $nextItemIndentMatches);
            $nextIndentLevel = min(strlen($nextItemIndentMatches[1] ?? ''), 2);
            $hasChildItems = (bool) ($nextItem && $nextIndentLevel > $indentLevel);
            $collapsibleParentItems = ['PERINEAL CARE', 'CNA SKILLS'];
            $isMainParentItem = $indentLevel === 0 && $hasChildItems && in_array($displayItem, $collapsibleParentItems, true);
            $isStructuralParent = $hasChildItems && ! $isMainParentItem;

            $structured[] = [
                'id' => (int) $item->id,
                'section' => (string) $item->section,
                'item_label' => $displayItem,
                'indent_level' => $indentLevel,
                'is_parent' => $isStructuralParent || $isMainParentItem,
            ];
        }

        return $structured;
    }

    /**
     * @param  list<array{id: int, section: string, item_label: string, indent_level: int, is_parent: bool}>  $structuredItems
     * @param  array<int, true>  $ratedItemIds
     * @param  array<int, true>  $scorableIds
     */
    protected static function parentHasRatedDescendant(array $structuredItems, int $parentIndex, array $ratedItemIds, array $scorableIds): bool
    {
        $parentIndent = $structuredItems[$parentIndex]['indent_level'];

        for ($index = $parentIndex + 1; $index < count($structuredItems); $index++) {
            $candidate = $structuredItems[$index];

            if ($candidate['indent_level'] <= $parentIndent) {
                break;
            }

            if (! $candidate['is_parent'] && isset($scorableIds[$candidate['id']]) && isset($ratedItemIds[$candidate['id']])) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  array<string, mixed>  $assessmentItems
     */
    protected static function ratingForItem(int $itemId, Collection $entries, array $assessmentItems): ?string
    {
        $entry = $entries->get($itemId);
        if ($entry && filled($entry->rating)) {
            return strtoupper(trim((string) $entry->rating));
        }

        $raw = $assessmentItems['F_'.$itemId] ?? $assessmentItems[(string) $itemId] ?? null;

        return EmployeePerformanceAssessment::itemRating($raw);
    }

    protected static function formatShortDate(mixed $value): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        try {
            return Carbon::parse($value)->format('m-d-y');
        } catch (\Throwable) {
            $value = trim((string) $value);

            return strlen($value) >= 10 ? Carbon::parse(substr($value, 0, 10))->format('m-d-y') : $value;
        }
    }
}
