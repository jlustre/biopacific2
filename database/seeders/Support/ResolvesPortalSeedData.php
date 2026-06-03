<?php

namespace Database\Seeders\Support;

use App\Models\Department;
use App\Models\Facility;
use App\Models\Position;
use App\Models\User;
trait ResolvesPortalSeedData
{
    protected function corporateFacility(): ?Facility
    {
        return Facility::query()
            ->where('slug', config('member-portal.corporate_facility_slug', 'bio-pacific-corporate'))
            ->first();
    }

    protected function facilityByMatcher(string $key): ?Facility
    {
        $matcher = config("member-portal.demo_facility_matchers.{$key}", []);

        if ($matcher === []) {
            return null;
        }

        $query = Facility::query();

        foreach ($matcher as $column => $value) {
            $query->where($column, $value);
        }

        return $query->first();
    }

    protected function demoUser(string $roleKey): ?User
    {
        $email = config("member-portal.demo_user_emails.{$roleKey}");

        if (!$email) {
            return null;
        }

        return User::query()->where('email', $email)->first();
    }

    protected function nursingDepartment(): ?Department
    {
        return Department::query()->where('name', 'Nursing')->first();
    }

    /**
     * @return \Illuminate\Support\Collection<int, Position>
     */
    protected function nursingStaffPositions()
    {
        $department = $this->nursingDepartment();

        if (!$department) {
            return collect();
        }

        return Position::query()
            ->where('department_id', $department->id)
            ->whereIn('title', [
                'Certified Nursing Assistant',
                'Licensed Vocational Nurse',
                'Registered Nurse',
                'Charge Nurse',
            ])
            ->orderBy('id')
            ->get();
    }

    protected function demoEmail(string $roleKey, string $fallback): string
    {
        return (string) config("member-portal.demo_user_emails.{$roleKey}", $fallback);
    }
}
