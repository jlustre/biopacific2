<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class EmployeeEmailMapping extends Model
{
    use HasFactory;

    public const CATEGORY_BOOK_A_TOUR = 'book-a-tour';

    public const CATEGORY_INQUIRY = 'inquiry';

    public const CATEGORY_HIRING = 'hiring';

    public const CATEGORY_HR = 'hr_inquiry';

    public const CATEGORY_SUPPORT = 'support';

    protected $fillable = [
        'facility_id',
        'category',
        'contact_role',
        'user_id',
        'employee_name',
        'employee_email',
        'title',
        'is_primary',
        'is_active',
        'on_vacation',
        'vacation_starts_at',
        'vacation_ends_at',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
        'is_active' => 'boolean',
        'on_vacation' => 'boolean',
        'vacation_starts_at' => 'date',
        'vacation_ends_at' => 'date',
    ];

    public function facility(): BelongsTo
    {
        return $this->belongsTo(Facility::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function websiteCategories(): array
    {
        return [
            self::CATEGORY_BOOK_A_TOUR => 'Book a Tour',
            self::CATEGORY_INQUIRY => 'General Inquiry',
            self::CATEGORY_HIRING => 'Hiring / Careers',
        ];
    }

    public static function portalHelpCategories(): array
    {
        return [
            self::CATEGORY_HR => config('portal-help.types.'.self::CATEGORY_HR, 'Contact HR'),
            self::CATEGORY_SUPPORT => config('portal-help.types.'.self::CATEGORY_SUPPORT, 'Technical Support'),
        ];
    }

    public static function allCategories(): array
    {
        return self::websiteCategories() + self::portalHelpCategories();
    }

    public static function isPortalHelpCategory(string $category): bool
    {
        return array_key_exists($category, self::portalHelpCategories());
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopePrimary($query)
    {
        return $query->where('is_primary', true);
    }

    public function scopeForCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    public function isAway(?Carbon $at = null): bool
    {
        if ($this->on_vacation) {
            return true;
        }

        $at = $at ?? now();
        $start = $this->vacation_starts_at;
        $end = $this->vacation_ends_at;

        if (! $start && ! $end) {
            return false;
        }

        if ($start && $at->lt($start->copy()->startOfDay())) {
            return false;
        }

        if ($end && $at->gt($end->copy()->endOfDay())) {
            return false;
        }

        return (bool) ($start || $end);
    }

    public function isAvailable(?Carbon $at = null): bool
    {
        return $this->is_active && ! $this->isAway($at);
    }

    public function resolvedEmail(): ?string
    {
        $email = $this->user?->email ?: $this->employee_email;

        return filled($email) ? strtolower(trim((string) $email)) : null;
    }

    public function displayName(): string
    {
        return $this->employee_name
            ?: ($this->user?->name ?? null)
            ?: ($this->resolvedEmail() ?? 'Contact');
    }

    public function contactRoleLabel(): string
    {
        if (! $this->contact_role) {
            return $this->is_primary ? 'Primary' : 'Secondary';
        }

        $roles = config('portal-help.contact_roles.'.$this->category, []);

        return $roles[$this->contact_role]['label'] ?? ucfirst(str_replace('_', ' ', $this->contact_role));
    }

    public function categoryLabel(): string
    {
        return self::allCategories()[$this->category] ?? ucfirst(str_replace(['-', '_'], ' ', (string) $this->category));
    }

    public static function getEffectivePrimary($facilityId, $category)
    {
        $base = self::query()->where('category', $category);

        if ($facilityId === null) {
            $base->whereNull('facility_id');
        } else {
            $base->where('facility_id', $facilityId);
        }

        $activePrimary = (clone $base)->primary()->active()->first();
        if ($activePrimary && ! $activePrimary->isAway()) {
            return $activePrimary;
        }

        return (clone $base)->active()
            ->get()
            ->first(fn (self $mapping) => ! $mapping->isAway());
    }

    public static function hasInactivePrimary($facilityId, $category)
    {
        $query = self::query()
            ->where('category', $category)
            ->primary()
            ->where('is_active', false);

        if ($facilityId === null) {
            $query->whereNull('facility_id');
        } else {
            $query->where('facility_id', $facilityId);
        }

        return $query->exists();
    }

    public static function getPrimaryStatus($facilityId, $category)
    {
        $query = self::query()->where('category', $category)->primary();

        if ($facilityId === null) {
            $query->whereNull('facility_id');
        } else {
            $query->where('facility_id', $facilityId);
        }

        $primary = $query->first();

        if (! $primary) {
            return ['status' => 'no_primary', 'message' => 'No primary contact assigned'];
        }

        if (! $primary->is_active || $primary->isAway()) {
            return [
                'status' => 'primary_inactive',
                'message' => 'Primary contact ('.$primary->employee_name.') is inactive or on vacation',
                'inactive_primary' => $primary,
            ];
        }

        return [
            'status' => 'active_primary',
            'message' => 'Primary contact ('.$primary->employee_name.') is active',
            'primary' => $primary,
        ];
    }
}
