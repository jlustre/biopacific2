<?php

namespace App\Support;

use App\Models\EmployeePerformanceItem;

class PartFPerformanceScoring
{
    /**
     * @return array<int, true>
     */
    public static function scorableItemIds(): array
    {
        $scorableItemIds = [];
        $items = EmployeePerformanceItem::query()->orderBy('order')->get();

        foreach ($items->values() as $itemIdx => $item) {
            $rawItemText = trim(strip_tags((string) ($item->item ?? '')));
            preg_match('/^(-+)/', $rawItemText, $itemIndentMatches);
            $indentLevel = min(strlen($itemIndentMatches[1] ?? ''), 2);
            $nextItem = $items->values()->get($itemIdx + 1);
            $nextRawItemText = trim(strip_tags((string) ($nextItem?->item ?? '')));
            preg_match('/^(-+)/', $nextRawItemText, $nextItemIndentMatches);
            $nextIndentLevel = min(strlen($nextItemIndentMatches[1] ?? ''), 2);
            $hasChildItems = (bool) ($nextItem && $nextIndentLevel > $indentLevel);
            $collapsibleParentItems = ['PERINEAL CARE', 'CNA SKILLS'];
            $displayItem = ltrim((string) preg_replace('/^(-+)/', '', $rawItemText), '-');
            $isMainParentItem = $indentLevel === 0 && $hasChildItems && in_array($displayItem, $collapsibleParentItems, true);
            $isStructuralParent = $hasChildItems && ! $isMainParentItem;

            if (! $isMainParentItem && ! $isStructuralParent && ! $hasChildItems) {
                $scorableItemIds[(int) $item->id] = true;
            }
        }

        return $scorableItemIds;
    }

    /**
     * @param  array<int, string>  $ratings  source_item_id => E|S|U|N
     * @param  array<int, true>  $scorableItemIds
     * @return array{total_score: int, average_score: float, overall_rating: string}
     */
    public static function summarize(array $ratings, array $scorableItemIds): array
    {
        $total = 0;
        $count = 0;

        foreach ($ratings as $sourceItemId => $rating) {
            if (! isset($scorableItemIds[(int) $sourceItemId])) {
                continue;
            }

            $score = self::numericScore((string) $rating);
            if ($score === null) {
                continue;
            }

            $total += $score;
            $count++;
        }

        $average = $count > 0 ? round($total / $count, 2) : 0.0;

        return [
            'total_score' => $total,
            'average_score' => $average,
            'overall_rating' => self::overallLabel($average, $count),
        ];
    }

    public static function numericScore(string $rating): ?int
    {
        return match (strtoupper(trim($rating))) {
            'E', 'EXCELLENT', 'EXCEEDS', '3' => 3,
            'S', 'SATISFACTORY', 'MEETS', 'M', '2' => 2,
            'U', 'UNSATISFACTORY', 'BELOW', 'B', '1' => 1,
            default => null,
        };
    }

    public static function overallLabel(float $average, int $ratedCount): string
    {
        if ($ratedCount === 0) {
            return 'Not Rated';
        }

        if ($average >= 2.5) {
            return 'Excellent';
        }

        if ($average >= 1.5) {
            return 'Satisfactory';
        }

        return 'Unsatisfactory';
    }
}
