<?php

namespace App\Support;

class PartGCompetencyScoring
{
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
        return PartFPerformanceScoring::normalizeItemRating($rating);
    }

    public static function isValidItemRating(?string $rating): bool
    {
        return self::normalizeItemRating($rating) !== null;
    }

    public static function isBelowExpectationsItemRating(?string $rating): bool
    {
        return self::normalizeItemRating($rating) === 'B';
    }

    /**
     * @return array<string, string>
     */
    public static function itemRatingOptions(): array
    {
        return [
            'E' => 'Exceeds Expectations',
            'M' => 'Meets Expectations',
            'B' => 'Below Expectations',
        ];
    }

    /** Maximum points a single competency item can earn (E = Exceeds Expectations). */
    public const MAX_ITEM_POINTS = 3;

    public static function maxPointsForScorableItems(int $scorableItemCount): int
    {
        return max(0, $scorableItemCount) * self::MAX_ITEM_POINTS;
    }

    public static function pointsOfTotalLabel(int $earnedPoints, int $scorableItemCount): string
    {
        return $earnedPoints.' of '.self::maxPointsForScorableItems($scorableItemCount).' points';
    }

    public static function itemRatingLegendText(): string
    {
        return 'E = 3 points | M = 2 points | B = 1 point';
    }

    public static function averageLegendText(): string
    {
        $meetsMin = number_format(PartFPerformanceScoring::OVERALL_MEETS_MIN, 2, '.', '');
        $meetsMax = number_format(PartFPerformanceScoring::OVERALL_EXCEEDS_MIN - 0.01, 2, '.', '');
        $exceedsMin = number_format(PartFPerformanceScoring::OVERALL_EXCEEDS_MIN, 2, '.', '');

        return "Below {$meetsMin} = Below Expectations | {$meetsMin} to {$meetsMax} = Meets Expectations | {$exceedsMin} and above = Exceeds Expectations";
    }

    public static function numericScore(?string $rating): ?int
    {
        return PartFPerformanceScoring::numericScore((string) $rating);
    }

    public static function overallLabel(float $average, int $ratedCount): string
    {
        if ($ratedCount === 0) {
            return '—';
        }

        return PartFPerformanceScoring::overallLabel($average, $ratedCount);
    }

    public static function overallLabelOrNa(float $average, int $ratedCount): string
    {
        if ($ratedCount === 0) {
            return 'N/A';
        }

        return PartFPerformanceScoring::overallLabel($average, $ratedCount);
    }

    /**
     * @param  array<int|string, string>  $ratings  source_item_id => E|M|B
     * @return array{total_score: int, average_score: float, overall_rating: string}
     */
    public static function summarize(array $ratings): array
    {
        $total = 0;
        $count = 0;

        foreach ($ratings as $rating) {
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
}
