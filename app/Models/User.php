<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /**
     * Many-to-many relationship: User can belong to multiple facilities
     */
    public function facilities()
    {
        return $this->belongsToMany(Facility::class, 'facility_user', 'user_id', 'facility_id');
    }
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
        if ($this->hasRole(['admin'])) {
            return true;
        }

        // Facility admins, editors, and DSDs can only manage their assigned facility
        if ($this->hasRole(['facility-admin', 'facility-editor', 'facility-dsd'])) {
            return $this->facility_id == $facilityId;
        }

        return false;
    }

    /**
     * Get facilities this user can manage
     */
    public function managedFacilities()
    {
        if ($this->hasRole(['admin'])) {
            return Facility::all();
        }

        if ($this->hasRole(['facility-admin', 'facility-editor']) && $this->facility_id) {
            return Facility::where('id', $this->facility_id)->get();
        }

        return collect();
    }

    /**
     * Get job applications associated with this user
     */
    public function jobApplications()
    {
        return $this->hasMany(JobApplication::class);
    }

    /**
     * Get the employee checklist items for this user
     */
    public function employeeChecklists()
    {
        return $this->hasMany(EmployeeChecklist::class);
    }

    /**
     * Get the employee record for this user (legacy Employee model on bp_employees).
     */
    public function employee()
    {
        if (static::bpEmployeesTableHasUserId()) {
            return $this->hasOne(Employee::class, 'user_id', 'id');
        }

        return $this->hasOne(Employee::class, 'email', 'email');
    }

    /**
     * Bio-Pacific employee record linked by email (falls back when user_id column is absent).
     */
    public function bpEmployee()
    {
        if (static::bpEmployeesTableHasUserId()) {
            return $this->hasOne(BPEmployee::class, 'user_id', 'id');
        }

        return $this->hasOne(BPEmployee::class, 'email', 'email');
    }

    /**
     * Resolve the employee record for this user (user_id when available, otherwise email).
     */
    public function resolvedBpEmployee(array $with = []): ?BPEmployee
    {
        $makeQuery = fn () => $with === []
            ? BPEmployee::query()
            : BPEmployee::query()->with($with);

        if (static::bpEmployeesTableHasUserId()) {
            $byUserId = $makeQuery()->where('user_id', $this->id)->first();
            if ($byUserId) {
                return $byUserId;
            }
        }

        if (filled($this->email)) {
            return $makeQuery()->where('email', $this->email)->first();
        }

        return null;
    }

    public static function bpEmployeesTableHasUserId(): bool
    {
        static $hasUserId = null;

        if ($hasUserId !== null) {
            return $hasUserId;
        }

        if (! Schema::hasColumn('bp_employees', 'user_id')) {
            return $hasUserId = false;
        }

        try {
            BPEmployee::query()->select('id')->whereNull('user_id')->limit(1)->value('id');

            return $hasUserId = true;
        } catch (\Throwable) {
            return $hasUserId = false;
        }
    }
}
