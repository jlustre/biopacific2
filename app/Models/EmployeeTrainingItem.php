<?php

namespace App\Models;

use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EmployeeTrainingItem extends Model
{
    public const FREQUENCY_HIRING = 'hiring';

    public const FREQUENCY_ANNUAL = 'annual';

    public const FREQUENCY_BIENNIAL = 'biennial';

    public const FREQUENCY_TRIENNIAL = 'triennial';

    /**
     * Canonical frequency definitions.
     *
     * @var array<string, array{label: string, short: string, interval_years: int|null, badge: string}>
     */
    public const FREQUENCIES = [
        self::FREQUENCY_HIRING => [
            'label' => 'Upon hiring (one-time)',
            'short' => 'Hiring',
            'interval_years' => null,
            'badge' => 'bg-amber-100 text-amber-900',
        ],
        self::FREQUENCY_ANNUAL => [
            'label' => 'Annual (every year)',
            'short' => 'Annual',
            'interval_years' => 1,
            'badge' => 'bg-sky-100 text-sky-900',
        ],
        self::FREQUENCY_BIENNIAL => [
            'label' => 'Every 2 years',
            'short' => 'Every 2 yrs',
            'interval_years' => 2,
            'badge' => 'bg-violet-100 text-violet-900',
        ],
        self::FREQUENCY_TRIENNIAL => [
            'label' => 'Every 3 years',
            'short' => 'Every 3 yrs',
            'interval_years' => 3,
            'badge' => 'bg-indigo-100 text-indigo-900',
        ],
    ];

    protected $table = 'employee_training_items';

    protected $attributes = [
        'position_ids' => '["global"]',
        'frequency' => self::FREQUENCY_ANNUAL,
        'is_active' => true,
        'order' => 0,
    ];

    protected $fillable = [
        'name',
        'description',
        'content_url',
        'provider_label',
        'frequency',
        'position_ids',
        'order',
        'is_active',
    ];

    protected $casts = [
        'position_ids' => 'array',
        'is_active' => 'boolean',
        'order' => 'integer',
    ];

    /**
     * @return list<string>
     */
    public static function frequencyKeys(): array
    {
        return array_keys(self::FREQUENCIES);
    }

    /**
     * @return array{label: string, short: string, interval_years: int|null, badge: string}
     */
    public static function frequencyMeta(?string $frequency): array
    {
        return self::FREQUENCIES[$frequency] ?? self::FREQUENCIES[self::FREQUENCY_ANNUAL];
    }

    public function completions(): HasMany
    {
        return $this->hasMany(EmployeeTrainingCompletion::class, 'employee_training_item_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeHiring($query)
    {
        return $query->where('frequency', self::FREQUENCY_HIRING);
    }

    public function scopeRecurring($query)
    {
        return $query->where('frequency', '!=', self::FREQUENCY_HIRING);
    }

    public function scopeAnnual($query)
    {
        return $query->where('frequency', self::FREQUENCY_ANNUAL);
    }

    public function scopeBiennial($query)
    {
        return $query->where('frequency', self::FREQUENCY_BIENNIAL);
    }

    public function scopeApplicableToPosition($query, ?int $positionId)
    {
        if (! $positionId) {
            return $query;
        }

        return $query->where(function ($subquery) use ($positionId) {
            $subquery->whereNull('position_ids')
                ->orWhereJsonContains('position_ids', 'global')
                ->orWhereJsonContains('position_ids', $positionId)
                ->orWhereJsonContains('position_ids', (string) $positionId);
        });
    }

    public function isHiring(): bool
    {
        return $this->frequency === self::FREQUENCY_HIRING;
    }

    public function isAnnual(): bool
    {
        return $this->frequency === self::FREQUENCY_ANNUAL;
    }

    public function isBiennial(): bool
    {
        return $this->frequency === self::FREQUENCY_BIENNIAL;
    }

    public function isRecurring(): bool
    {
        return ! $this->isHiring();
    }

    /** Recurring trainings are tracked against an assessment period. */
    public function requiresAssessmentPeriod(): bool
    {
        return $this->isRecurring();
    }

    public function intervalYears(): ?int
    {
        return self::frequencyMeta($this->frequency)['interval_years'];
    }

    public function frequencyLabel(): string
    {
        return self::frequencyMeta($this->frequency)['label'];
    }

    public function frequencyShortLabel(): string
    {
        return self::frequencyMeta($this->frequency)['short'];
    }

    public function frequencyBadgeClass(): string
    {
        return self::frequencyMeta($this->frequency)['badge'];
    }

    /**
     * Whether this training is due based on the last approved completion.
     *
     * @return array{due: bool, satisfied_until: ?Carbon, next_due_at: ?Carbon, status_hint: ?string}
     */
    public function evaluateDue(?CarbonInterface $lastCompletedAt, ?CarbonInterface $asOf = null): array
    {
        $asOf = Carbon::parse($asOf ?? now())->startOfDay();

        if ($this->isHiring()) {
            $completed = $lastCompletedAt !== null;

            return [
                'due' => ! $completed,
                'satisfied_until' => null,
                'next_due_at' => null,
                'status_hint' => $completed ? 'Completed (one-time)' : null,
            ];
        }

        $years = max(1, (int) ($this->intervalYears() ?? 1));

        if ($lastCompletedAt === null) {
            return [
                'due' => true,
                'satisfied_until' => null,
                'next_due_at' => $asOf->copy(),
                'status_hint' => null,
            ];
        }

        $satisfiedUntil = Carbon::parse($lastCompletedAt)->startOfDay()->addYears($years);

        if ($asOf->lt($satisfiedUntil)) {
            return [
                'due' => false,
                'satisfied_until' => $satisfiedUntil,
                'next_due_at' => $satisfiedUntil->copy(),
                'status_hint' => 'Current through '.$satisfiedUntil->toDateString(),
            ];
        }

        return [
            'due' => true,
            'satisfied_until' => $satisfiedUntil,
            'next_due_at' => $asOf->copy(),
            'status_hint' => null,
        ];
    }

    public function appliesToEveryone(): bool
    {
        $ids = $this->position_ids;

        return $ids === null
            || $ids === []
            || in_array('global', $ids, true);
    }

    public function hasContentLink(): bool
    {
        return filled($this->content_url);
    }

    public function resolvedContentUrl(): ?string
    {
        if (! $this->hasContentLink()) {
            return null;
        }

        $url = trim((string) $this->content_url);
        if (str_starts_with($url, 'http://') || str_starts_with($url, 'https://')) {
            return $url;
        }

        return url($url);
    }
}
