<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles;
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'facility_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the user's initials
     */
    public function getInitialsAttribute(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->take(2)
            ->map(fn ($word) => Str::substr($word, 0, 1))
            ->implode('');
    }

    /**
     * Get the facility this user belongs to
     */
    public function facility()
    {
        return $this->belongsTo(Facility::class);
    }

    /**
     * Check if user can manage a specific facility
     */
    public function canManageFacility($facilityId): bool
    {
        // Web admins and regular admins can manage all facilities
        if ($this->hasRole(['web-admin', 'admin'])) {
            return true;
        }

        // Facility admins and editors can only manage their assigned facility
        if ($this->hasRole(['facility-admin', 'facility-editor'])) {
            return $this->facility_id == $facilityId;
        }

        return false;
    }

    /**
     * Get facilities this user can manage
     */
    public function managedFacilities()
    {
        if ($this->hasRole(['web-admin', 'admin'])) {
            return Facility::all();
        }

        if ($this->hasRole(['facility-admin', 'facility-editor']) && $this->facility_id) {
            return Facility::where('id', $this->facility_id)->get();
        }

        return collect();
    }
}
