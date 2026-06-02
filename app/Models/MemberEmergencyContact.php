<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class MemberEmergencyContact extends Model
{
    /**
     * Allowed relationship values for the emergency contact form.
     *
     * @return list<string>
     */
    public static function relationshipOptions(): array
    {
        return [
            'Spouse',
            'Partner',
            'Parent',
            'Child',
            'Brother',
            'Sister',
            'Grandparent',
            'Grandchild',
            'Friend',
            'Relative',
            'Guardian',
            'Neighbor',
            'Coworker',
            'In-law',
            'Other',
        ];
    }

    protected $fillable = [
        'user_id',
        'first_name',
        'last_name',
        'relationship',
        'phone',
        'email',
        'address1',
        'address2',
        'city',
        'state',
        'zip',
        'is_primary',
        'sort_order',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getFullNameAttribute(): string
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }

    public function getInitialsAttribute(): string
    {
        $first = Str::substr($this->first_name ?? '', 0, 1);
        $last = Str::substr($this->last_name ?? '', 0, 1);

        return strtoupper($first . $last) ?: '?';
    }

    public function getFormattedPhoneAttribute(): string
    {
        $digits = preg_replace('/\D+/', '', (string) $this->phone);

        if (strlen($digits) === 10) {
            return sprintf('(%s) %s-%s', substr($digits, 0, 3), substr($digits, 3, 3), substr($digits, 6));
        }

        return (string) $this->phone;
    }

    public function getFormattedAddressAttribute(): ?string
    {
        $line1 = trim((string) ($this->address1 ?? ''));
        $line2 = trim((string) ($this->address2 ?? ''));
        $city = trim((string) ($this->city ?? ''));
        $state = trim((string) ($this->state ?? ''));
        $zip = trim((string) ($this->zip ?? ''));

        $cityLine = trim(implode(', ', array_filter([
            $city,
            trim($state . ($zip !== '' ? ' ' . $zip : '')),
        ])));

        $parts = array_filter([$line1, $line2, $cityLine]);

        return $parts === [] ? null : implode(', ', $parts);
    }
}
