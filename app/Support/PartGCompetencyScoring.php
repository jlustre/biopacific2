<?php

namespace App\Support;

class PartGCompetencyScoring
{
    public static function numericScore(?string $rating): ?int
    {
        return match (strtoupper(trim((string) $rating))) {
            'E', 'EXCELLENT', '3' => 3,
            'S', 'SATISFACTORY', '2' => 2,
            'U', 'UNSATISFACTORY', '1' => 1,
            default => null,
        };
    }

    public static function overallLabel(float $average, int $ratedCount): string
    {
        if ($ratedCount === 0) {
            return '—';
        }

        return match (true) {
            $average >= 2.5 => 'Excellent',
            $average >= 1.5 => 'Satisfactory',
            $average > 0 => 'Unsatisfactory',
            default => 'Needs Improvement',
        };
    }

    /**
     * @param  array<int|string, string>  $ratings  source_item_id => E|S|U|N
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
