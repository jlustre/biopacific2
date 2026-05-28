<?php

namespace App\Support;

use App\Models\EmployeePerformanceItem;

class PartFPerformanceScoring
{
    public const OVERALL_EXCEEDS_MIN = 2.51;

    public const OVERALL_MEETS_MIN = 1.75;

    public const OVERALL_BELOW_MIN = 1.00;

    /** @var list<string> */
    public const ITEM_RATING_CODES = ['E', 'M', 'B'];

    /**
     * @return list<string>
     */
    public static function ratingCodes(): array
    {
        return self::ITEM_RATING_CODES;
    }

    public static function normalizeItemRating(?string $rating): ?string
    {
        $rating = strtoupper(trim((string) $rating));

        if ($rating === '') {
            return null;
        }

        return match ($rating) {
            'E', 'EXCEEDS', 'EXCEEDS EXPECTATIONS', 'EXCELLENT', '3' => 'E',
            'M', 'MEETS', 'MEETS EXPECTATIONS', 'SATISFACTORY', 'S', '2' => 'M',
            'B', 'BELOW', 'BELOW EXPECTATIONS', 'UNSATISFACTORY', 'U', '1' => 'B',
            default => in_array($rating, self::ITEM_RATING_CODES, true) ? $rating : null,
        };
    }

    public static function isValidItemRating(?string $rating): bool
    {
        return self::normalizeItemRating($rating) !== null;
    }

    /**
     * @return list<array{description: string, expectation: string, range: string}>
     */
    public static function ratingDescriptionLegendRows(): array
    {
        return [
            [
                'description' => 'The employee exceeds the majority of performance expectations.',
                'expectation' => 'E = EXCEEDS EXPECTATIONS',
                'range' => '2.51 – 3.00',
            ],
            [
                'description' => 'The employee meets performance expectations with occasional deviations above and below expectations.',
                'expectation' => 'M = MEETS EXPECTATIONS',
                'range' => '1.75 – 2.50',
            ],
            [
                'description' => 'The employee has failed to meet one or more of the significant performance expectations.',
                'expectation' => 'B = BELOW EXPECTATIONS',
                'range' => '1.00 – 1.74',
            ],
        ];
    }

    /**
     * @return list<array{description: string, expectation: string, range: string}>
     */
    public static function expectationsLegendRows(): array
    {
        return self::ratingDescriptionLegendRows();
    }

    public static function overallRatingCode(?string $overallRating = null, ?float $average = null): string
    {
        if ($average !== null) {
            if ($average >= self::OVERALL_EXCEEDS_MIN) {
                return 'E';
            }

            if ($average >= self::OVERALL_MEETS_MIN) {
                return 'M';
            }

            if ($average > 0) {
                return 'B';
            }

            return '';
        }

        $normalized = strtolower(trim((string) $overallRating));

        if ($normalized === '' || $normalized === 'not rated' || $normalized === 'n/a') {
            return '';
        }

        if (str_contains($normalized, 'exceed') || $normalized === 'excellent') {
            return 'E';
        }

        if (str_contains($normalized, 'meet') || $normalized === 'satisfactory') {
            return 'M';
        }

        if (str_contains($normalized, 'below') || str_contains($normalized, 'unsatisfactory')) {
            return 'B';
        }

        return '';
    }

    public static function isBelowExpectationsRating(?string $overallRating): bool
    {
        $normalized = strtolower(trim((string) $overallRating));

        return in_array($normalized, ['below expectations', 'unsatisfactory'], true);
    }

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
        return match (self::normalizeItemRating($rating)) {
            'E' => 3,
            'M' => 2,
            'B' => 1,
            default => null,
        };
    }

    public static function overallLabel(float $average, int $ratedCount): string
    {
        if ($ratedCount === 0) {
            return 'Not Rated';
        }

        if ($average >= self::OVERALL_EXCEEDS_MIN) {
            return 'Exceeds Expectations';
        }

        if ($average >= self::OVERALL_MEETS_MIN) {
            return 'Meets Expectations';
        }

        return 'Below Expectations';
    }
}
