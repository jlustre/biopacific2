<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BPEmployee extends Model
{
    // Relationship: Employee has one phone (primary)
    public function phone()
    {
        return $this->hasOne(BPEmpPhone::class, 'employee_num', 'employee_num')->where('is_primary', BPEmpPhone::PRIMARY_YES);
    }

    // Relationship: Employee has one address
    public function address()
    {
        return $this->hasOne(\App\Models\BPEmpAddress::class, 'employee_num', 'employee_num');
    }

    // Relationship: Employee belongs to a user (by user_id when present, else email)
    public function user()
    {
        if (\App\Models\User::bpEmployeesTableHasUserId()) {
            return $this->belongsTo(\App\Models\User::class, 'user_id', 'id');
        }

        return $this->belongsTo(\App\Models\User::class, 'email', 'email');
    }
    protected $table = 'bp_employees';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $casts = [
        'dob' => 'date',
        'original_hire_dt' => 'date',
        'rehire_dt' => 'date',
        'badge_eff_dt' => 'date',
        'effdt_of_membership' => 'date',
    ];

    protected $fillable = [
        'user_id',
        'employee_num',
        'badge_num',
        'badge_eff_dt',
        'ssn',
        'first_name',
        'middle_name',
        'last_name',
        'gender',
        'dob',
        'original_hire_dt',
        'rehire_dt',
        'marital_status_id',
        'ethnic_group_id',
        'military_status_id',
        'citizenship_status_id',
        // Newly added columns
        'action_id',
        'union_code',
        'effdt_of_membership',
        'email',
    ];

    // Relationship: Employee has one phone (primary)
    // Relationship: Employee has many uploads
    public function uploads()
    {
        return $this->hasMany(\App\Models\Upload::class, 'employee_num', 'employee_num');
    }

    // Relationship: Employee has many assignments
    public function assignments()
    {
        return $this->hasMany(\App\Models\BPEmpJobData::class, 'employee_num', 'employee_num');
    }

    public function taxData()
    {
        return $this->hasMany(\App\Models\BPEmpTaxData::class, 'employee_num', 'employee_num');
    }

    public function currentTaxData()
    {
        return $this->hasOne(\App\Models\BPEmpTaxData::class, 'employee_num', 'employee_num')
            ->latestOfMany(['effdt', 'effseq']);
    }

    /**
     * Get the current (most recent) assignment from bp_emp_job_data
     */
    public function currentAssignment()
    {
        // Always get the latest assignment by effdt and effseq
        return $this->hasOne(\App\Models\BPEmpJobData::class, 'employee_num', 'employee_num')
            ->latestOfMany(['effdt', 'effseq']);
    }

    // Attribute accessor for current assignment instance
    public function getCurrentAssignmentAttribute()
    {
        // Use the loaded relation if available, otherwise query
        return $this->getRelationValue('currentAssignment') ?? $this->currentAssignment()->first();
    }

    public function getCurrentPositionAttribute()
    {
        $assignment = $this->current_assignment;
        if ($assignment && ($assignment->relationLoaded('position') || isset($assignment->position))) {
            return $assignment->position;
        }
        return null;
    }

    public function getCurrentDepartmentAttribute()
    {
        $assignment = $this->current_assignment;
        if ($assignment && ($assignment->relationLoaded('department') || isset($assignment->department))) {
            return $assignment->department;
        }
        return null;
    }

    /**
     * Roster-style name: "Last, First Middle".
     */
    public function formalName(): string
    {
        $last = trim((string) ($this->last_name ?? ''));
        $first = trim((string) ($this->first_name ?? ''));
        $middle = trim((string) ($this->middle_name ?? ''));
        $given = trim($first . ($middle !== '' ? ' ' . $middle : ''));

        if ($last !== '' && $given !== '') {
            return "{$last}, {$given}";
        }

        $fallback = trim(implode(' ', array_filter([$first, $middle, $last], fn (string $part) => $part !== '')));

        return $fallback !== '' ? $fallback : (string) ($this->employee_num ?? '');
    }

    /**
     * @param  array{last_name?: string, first_name?: string, middle_name?: string}  $a
     * @param  array{last_name?: string, first_name?: string, middle_name?: string}  $b
     */
    public static function compareByNameFields(array $a, array $b): int
    {
        foreach (['last_name', 'first_name', 'middle_name'] as $field) {
            $cmp = strnatcasecmp((string) ($a[$field] ?? ''), (string) ($b[$field] ?? ''));

            if ($cmp !== 0) {
                return $cmp;
            }
        }

        return 0;
    }

    /**
     * @return array{name: string, last_name: string, first_name: string, middle_name: string}
     */
    public function tableNameFields(): array
    {
        return [
            'name' => $this->formalName(),
            'last_name' => (string) ($this->last_name ?? ''),
            'first_name' => (string) ($this->first_name ?? ''),
            'middle_name' => (string) ($this->middle_name ?? ''),
        ];
    }

    /**
     * @param  list<array<string, mixed>>  $rows
     * @return list<array<string, mixed>>
     */
    public static function sortTableRowsByName(array $rows): array
    {
        usort($rows, fn (array $a, array $b) => self::compareByNameFields($a, $b));

        return array_values($rows);
    }

    public function scopeOrderedByName($query)
    {
        return $query
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->orderBy('middle_name');
    }

    public function getCurrentFacilityAttribute()
    {
        $assignment = $this->current_assignment;
        if ($assignment && ($assignment->relationLoaded('facility') || isset($assignment->facility))) {
            return $assignment->facility;
        }
        return null;
    }

    public function getCurrentUnionStatusAttribute()
    {
        $assignment = $this->current_assignment;
        return $assignment && $assignment->bargaining_unit_id ? true : false;
    }

    public function getHourlyStatusIdAttribute(): mixed
    {
        return $this->current_assignment?->hourly_status_id;
    }

    public function getStdHrsWeekAttribute(): mixed
    {
        return $this->current_assignment?->std_hrs_week;
    }

    public function getCompensationRateIdAttribute(): mixed
    {
        return $this->current_assignment?->compensation_rate_id;
    }

    public function getAmountAttribute(): mixed
    {
        return $this->current_assignment?->amount;
    }

    /**
     * Get the current union status via the latest assignment
     * Returns true if the assignment has a bargaining_unit_id, false otherwise
     */
    public function currentUnionStatus()
    {
        $assignment = $this->currentAssignment;
        return $assignment && $assignment->bargaining_unit_id ? true : false;
    }

    public function usesRehireDate(): bool
    {
        if (! $this->action_id) {
            return false;
        }

        $name = SelectOption::query()->whereKey($this->action_id)->value('name');

        return strcasecmp(trim((string) $name), 'Rehire') === 0;
    }

    public function assessmentPeriods()
    {
        return $this->hasMany(\App\Models\EmployeeAssessmentPeriod::class, 'employee_num', 'employee_num');
    }

    // Relationship: Employee has many phones
    public function phones()
    {
        return $this->hasMany(\App\Models\BPEmpPhone::class, 'employee_num', 'employee_num');
    }

    // Relationship: Employee has many addresses
    public function addresses()
    {
        return $this->hasMany(\App\Models\BPEmpAddress::class, 'employee_num', 'employee_num');
    }

    /**
     * Resolve an employee from an admin route key (numeric id or employee_num).
     *
     * @param  list<string>  $with
     */
    public static function findForAdminRoute(string|int $routeKey, array $with = []): self
    {
        $query = static::query();

        if ($with !== []) {
            $query->with($with);
        }

        if (is_numeric($routeKey)) {
            return $query->where('id', (int) $routeKey)->firstOrFail();
        }

        return $query->where('employee_num', $routeKey)->firstOrFail();
    }
}
