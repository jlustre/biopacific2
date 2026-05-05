<?php

namespace Database\Seeders;

use App\Models\ChecklistItem;
use App\Models\DocType;
use App\Models\Position;
use Illuminate\Database\Seeder;

class ChecklistItemPositionBackfillSeeder extends Seeder
{
    public function run(): void
    {
        $positionsByCode = Position::query()->whereNotNull('position_code')->pluck('id', 'position_code');
        $docTypesByName = DocType::query()->pluck('id', 'name');

        $this->applyToSection('PART E', $this->positionIds($positionsByCode, ['CNA']));

        $this->applyToDocTypes($docTypesByName, [
            'Administrator Orientation' => $this->positionIds($positionsByCode, ['ADMIN']),
            'Blood Administration LN Competency Skills' => $this->positionIds($positionsByCode, ['DON', 'RN', 'LPN']),
            'Blood Glucose Skills checklist' => $this->positionIds($positionsByCode, ['DON', 'RN', 'LPN']),
            'CNA Competency Perineal Care Checklist' => $this->positionIds($positionsByCode, ['CNA']),
            'CNA skills checklist' => $this->positionIds($positionsByCode, ['CNA']),
            'DON Orientation Checklist' => $this->positionIds($positionsByCode, ['DON']),
            'Dietary_Department-Skills Checklist' => $this->positionIds($positionsByCode, ['DIET']),
            'Licensed Nurse Competency Checklist' => $this->positionIds($positionsByCode, ['DON', 'RN', 'LPN']),
            'NA skills checklist' => $this->positionIds($positionsByCode, ['CNA']),
            'Tracheostomy Care Skills Check' => $this->positionIds($positionsByCode, ['DON', 'RN', 'LPN']),
            'Treatment Nurse Care Skills Checklist' => $this->positionIds($positionsByCode, ['DON', 'RN', 'LPN']),
        ]);

        $this->applyToNames([
            'C.N.A. Certificate' => $this->positionIds($positionsByCode, ['CNA']),
            'CPR Card (License Nurses)' => $this->positionIds($positionsByCode, ['DON', 'RN', 'LPN']),
            'Professional License' => $this->positionIds($positionsByCode, ['DON', 'RN', 'LPN', 'SOCWRK', 'DIET', 'PHARM']),
            'Professional License - Copy' => $this->positionIds($positionsByCode, ['DON', 'RN', 'LPN', 'SOCWRK', 'DIET', 'PHARM']),
            'Administrator Orientation' => $this->positionIds($positionsByCode, ['ADMIN']),
            'Blood Administration LN Competency Skills' => $this->positionIds($positionsByCode, ['DON', 'RN', 'LPN']),
            'Blood Glucose Skills checklist' => $this->positionIds($positionsByCode, ['DON', 'RN', 'LPN']),
            'Blood Glucose Skills' => $this->positionIds($positionsByCode, ['DON', 'RN', 'LPN']),
            'CNA Competency Perineal Care Checklist' => $this->positionIds($positionsByCode, ['CNA']),
            'CNA skills checklist' => $this->positionIds($positionsByCode, ['CNA']),
            'Dietary_Department-Skills Checklist' => $this->positionIds($positionsByCode, ['DIET']),
            'DON Orientation Checklist' => $this->positionIds($positionsByCode, ['DON']),
            'Licensed Nurse Competency Checklist' => $this->positionIds($positionsByCode, ['DON', 'RN', 'LPN']),
            'NA skills checklist' => $this->positionIds($positionsByCode, ['CNA']),
            'Tracheostomy Care Skills Check' => $this->positionIds($positionsByCode, ['DON', 'RN', 'LPN']),
            'Treatment Nurse Care Skills Checklist' => $this->positionIds($positionsByCode, ['DON', 'RN', 'LPN']),
        ]);
    }

    private function applyToDocTypes($docTypesByName, array $map): void
    {
        foreach ($map as $docTypeName => $positionIds) {
            $docTypeId = $docTypesByName[$docTypeName] ?? null;

            if (!$docTypeId || !$positionIds) {
                continue;
            }

            ChecklistItem::query()
                ->where('doc_type_id', $docTypeId)
                ->update(['position_ids' => $positionIds]);
        }
    }

    private function applyToSection(string $section, array $positionIds): void
    {
        if (!$positionIds) {
            return;
        }

        ChecklistItem::query()
            ->where('section', $section)
            ->update(['position_ids' => $positionIds]);
    }

    private function applyToNames(array $map): void
    {
        foreach ($map as $name => $positionIds) {
            if (!$positionIds) {
                continue;
            }

            ChecklistItem::query()
                ->where('name', $name)
                ->update(['position_ids' => $positionIds]);
        }
    }

    private function positionIds($positionsByCode, array $codes): array
    {
        return collect($codes)
            ->map(fn ($code) => $positionsByCode[$code] ?? null)
            ->filter()
            ->values()
            ->all();
    }
}