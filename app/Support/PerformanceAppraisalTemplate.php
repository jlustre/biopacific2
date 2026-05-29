<?php

namespace App\Support;

use App\Models\Position;
use Illuminate\Support\Facades\DB;

class PerformanceAppraisalTemplate
{
    /**
     * Appraisal template keys sourced from the Excel workbooks.
     */
    public const RN_LVN = 'rn_lvn';
    public const CNA = 'cna';
    public const DIETARY_AIDE = 'dietary_aide';
    public const GENERAL_SERVICES = 'general_services';
    public const HOUSEKEEPER = 'housekeeper';
    public const LAUNDRY_AIDE = 'laundry_aide';
    public const MAINTENANCE = 'maintenance';
    public const MANAGEMENT = 'management';

    /**
     * @return array<string, string>
     */
    public static function positionTitleToTemplate(): array
    {
        return [
            'Activities Director' => 'management',
            'Activity Assistant' => 'general_services',
            'Administrator' => 'management',
            'Admissions Coordinator' => 'general_services',
            'Business Office Manager' => 'management',
            'Case Manager' => 'general_services',
            'Certified Nursing Assistant' => 'cna',
            'Charge Nurse' => 'management',
            'Cook' => 'dietary_aide',
            'Dietary Aide' => 'dietary_aide',
            'Dietary Manager' => 'management',
            'Director of Nursing' => 'management',
            'Director of Staff Development' => 'management',
            'Food Services Director' => 'management',
            'Housekeeper' => 'housekeeper',
            'Housekeeping Supervisor' => 'management',
            'IP Nurse' => 'rn_lvn',
            'Janitor' => 'housekeeper',
            'Laundry Staff' => 'laundry_aide',
            'Licensed Nurse' => 'rn_lvn',
            'Licensed Vocational Nurse' => 'rn_lvn',
            'MDS Coordinator' => 'general_services',
            'Maintenance Director' => 'management',
            'Maintenance Technician' => 'maintenance',
            'Marketing Director' => 'management',
            'Medical Records Clerk' => 'general_services',
            'Medical Records Director' => 'management',
            'Nursing Assistant' => 'cna',
            'OT/PT Assistant' => 'general_services',
            'Occupational Therapist' => 'general_services',
            'Office Staff' => 'general_services',
            'Other' => 'general_services',
            'Physical Therapist' => 'general_services',
            'Receptionist' => 'general_services',
            'Registered Nurse' => 'rn_lvn',
            'Rehab Manager' => 'management',
            'Resident Liaison' => 'general_services',
            'Social Services Director' => 'management',
            'Social Worker' => 'general_services',
            'Staff Development Coordinator' => 'general_services',
            'Unit Clerk' => 'general_services',
        ];
    }

    /**
     * @return array<string, list<string>>
     */
    public static function templatePositionTitles(): array
    {
        return [
            'rn_lvn' => ['IP Nurse', 'Licensed Nurse', 'Licensed Vocational Nurse', 'Registered Nurse'],
            'cna' => ['Certified Nursing Assistant', 'Nursing Assistant'],
            'dietary_aide' => ['Cook', 'Dietary Aide'],
            'general_services' => ['Activity Assistant', 'Admissions Coordinator', 'Case Manager', 'MDS Coordinator', 'Medical Records Clerk', 'OT/PT Assistant', 'Occupational Therapist', 'Office Staff', 'Other', 'Physical Therapist', 'Receptionist', 'Resident Liaison', 'Social Worker', 'Staff Development Coordinator', 'Unit Clerk'],
            'housekeeper' => ['Housekeeper', 'Janitor'],
            'laundry_aide' => ['Laundry Staff'],
            'maintenance' => ['Maintenance Technician'],
            'management' => ['Activities Director', 'Administrator', 'Business Office Manager', 'Charge Nurse', 'Dietary Manager', 'Director of Nursing', 'Director of Staff Development', 'Food Services Director', 'Housekeeping Supervisor', 'Maintenance Director', 'Marketing Director', 'Medical Records Director', 'Rehab Manager', 'Social Services Director'],
        ];
    }

    public static function templateForPositionTitle(?string $positionTitle): ?string
    {
        if (! filled($positionTitle)) {
            return null;
        }

        return static::positionTitleToTemplate()[trim($positionTitle)] ?? null;
    }

    /**
     * @return list<int>
     */
    public static function positionIdsForTemplate(string $template): array
    {
        $titles = static::templatePositionTitles()[$template] ?? [];

        if ($titles === []) {
            return [];
        }

        return DB::table('positions')
            ->whereIn('title', $titles)
            ->orderBy('id')
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    /**
     * @return list<int>
     */
    public static function positionIdsForPositionTitle(?string $positionTitle): array
    {
        $template = static::templateForPositionTitle($positionTitle);

        return $template ? static::positionIdsForTemplate($template) : [];
    }

    public static function positionIdForTitle(?string $positionTitle): ?int
    {
        if (! filled($positionTitle)) {
            return null;
        }

        $id = Position::query()->where('title', trim($positionTitle))->value('id');

        return $id ? (int) $id : null;
    }

    /**
     * @return array<string, string>
     */
    public static function templateDisplayLabels(): array
    {
        return [
            self::RN_LVN => 'RN & LVN Performance Appraisal',
            self::CNA => 'CNA Performance Appraisal',
            self::DIETARY_AIDE => 'Dietary Aide Performance Appraisal',
            self::GENERAL_SERVICES => 'General Services Performance Appraisal',
            self::HOUSEKEEPER => 'Housekeeper Performance Appraisal',
            self::LAUNDRY_AIDE => 'Laundry Aide Performance Appraisal',
            self::MAINTENANCE => 'Maintenance Performance Appraisal',
            self::MANAGEMENT => 'Management Performance Appraisal',
        ];
    }

    public static function displayLabelForTemplate(?string $template): ?string
    {
        if (! filled($template)) {
            return null;
        }

        return static::templateDisplayLabels()[$template] ?? null;
    }

    public static function displayLabelForPositionTitle(?string $positionTitle): ?string
    {
        return static::displayLabelForTemplate(static::templateForPositionTitle($positionTitle));
    }
}
