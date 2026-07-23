<?php

namespace App\Services;

use App\Models\PositionUploadTypeRequirement;
use App\Models\Upload;
use App\Models\UploadType;
use Illuminate\Support\Facades\DB;

class DocumentCatalogDedupeService
{
    /**
     * Canonical survivor name => list of duplicate general-type names to merge away.
     *
     * @return array<string, list<string>>
     */
    public function mergeMap(): array
    {
        return [
            'I-9' => ['I-9 Form', 'I - 9*', 'I-9*'],
            'Social Security Card' => ['Social Security Card - Copy', 'Social Security Card'],
            "Driver's License" => ["Driver's License - Copy*", 'Driver License/ID', "Driver's License - Copy"],
            'Emergency Contact Information' => ['Emergency Contact Designation', 'Emergency Contact Information'],
            'Job Description' => ['Job Description (Signed)', 'Job Description'],
            'Background Check' => ['Background Check Clearance', 'Background Check*', 'Background Check'],
            'Employee Handbook' => ['Employee Handbook Acknowledgment', 'Employee Handbook'],
            'W-4' => ['W-4 Form', 'W-4'],
            'Confidentiality' => ['Confidentiality Agreement', 'Confidentiality'],
            'Workplace Violence Prevention' => [
                'Workplace Violence Prevention Training Certificate',
                'Workplace Violence Prevention',
            ],
            'Direct Deposit Authorization' => [
                'Direct Deposit Authorization (Voided Check)',
                'Direct Deposit Authorization',
            ],
            'CPR Certification' => [
                'CPR Card (License Nurses)*',
                'CPR Card (License Nurses)',
                'CPR Certification',
            ],
            'Professional License' => [
                'Professional License*',
                'Professional License - Copy*',
                'Professional License - Copy',
                'Registered Nurse License',
                'Licensed Vocational Nurse License',
            ],
            'Green Card or Work Permit Authorization' => [
                'Green Card or Work Permit Autho. - Copy*',
                'Work Authorization',
            ],
            'Passport' => ['Passport - Copy*', 'Passport - Copy'],
            'C.N.A. Certificate' => ['C.N.A. Certificate*'],
            'OIG Verification' => ['OIG Verification*'],
            'SAM Verification' => ['SAM Verification*'],
            'Medical Exclusion/Ineligible Provider List' => ['Medical Exclusion/Ineligible Provider List*'],
        ];
    }

    /**
     * @return array{merged: int, renamed: int, remapped_uploads: int, remapped_requirements: int}
     */
    public function run(): array
    {
        $merged = 0;
        $renamed = 0;
        $remappedUploads = 0;
        $remappedRequirements = 0;

        DB::transaction(function () use (&$merged, &$renamed, &$remappedUploads, &$remappedRequirements): void {
            foreach ($this->mergeMap() as $canonicalName => $aliases) {
                $aliases = array_values(array_unique(array_map('trim', $aliases)));
                $types = UploadType::withoutGlobalScopes()
                    ->whereIn('name', array_merge([$canonicalName], $aliases))
                    ->get();

                if ($types->isEmpty()) {
                    continue;
                }

                $survivor = $types->first(fn (UploadType $type) => $type->checklist_item_id !== null)
                    ?? $types->first(fn (UploadType $type) => ! $type->trashed())
                    ?? $types->first();

                if (! $survivor) {
                    continue;
                }

                if (trim((string) $survivor->name) !== $canonicalName) {
                    $survivor->name = $canonicalName;
                    $survivor->save();
                    $renamed++;
                }

                if (method_exists($survivor, 'trashed') && $survivor->trashed()) {
                    $survivor->restore();
                }

                foreach ($types as $duplicate) {
                    if ((int) $duplicate->id === (int) $survivor->id) {
                        continue;
                    }

                    $remappedUploads += Upload::query()
                        ->where('upload_type_id', $duplicate->id)
                        ->update([
                            'upload_type_id' => $survivor->id,
                            'checklist_item_id' => $survivor->checklist_item_id,
                        ]);

                    $requirements = PositionUploadTypeRequirement::query()
                        ->where('upload_type_id', $duplicate->id)
                        ->get();

                    foreach ($requirements as $requirement) {
                        $exists = PositionUploadTypeRequirement::query()
                            ->where('position_id', $requirement->position_id)
                            ->where('upload_type_id', $survivor->id)
                            ->exists();

                        if (! $exists) {
                            PositionUploadTypeRequirement::query()->create([
                                'position_id' => $requirement->position_id,
                                'upload_type_id' => $survivor->id,
                                'is_required' => (bool) $requirement->is_required,
                            ]);
                            $remappedRequirements++;
                        }

                        $requirement->delete();
                    }

                    if ($duplicate->checklist_item_id && ! $survivor->checklist_item_id) {
                        $survivor->checklist_item_id = $duplicate->checklist_item_id;
                        $survivor->checklist_section = $duplicate->checklist_section ?? $survivor->checklist_section;
                        $survivor->doc_type_id = $duplicate->doc_type_id ?? $survivor->doc_type_id;
                        $survivor->save();
                    } elseif (
                        $duplicate->checklist_item_id
                        && $survivor->checklist_item_id
                        && (int) $duplicate->checklist_item_id !== (int) $survivor->checklist_item_id
                    ) {
                        // Point uploads that still reference the old checklist item at the survivor.
                        Upload::query()
                            ->where('checklist_item_id', $duplicate->checklist_item_id)
                            ->update(['checklist_item_id' => $survivor->checklist_item_id]);

                        \App\Models\ChecklistItem::query()
                            ->whereKey($duplicate->checklist_item_id)
                            ->delete();
                    }

                    $orphanChecklistItemId = $duplicate->checklist_item_id;
                    $duplicate->checklist_item_id = null;
                    $duplicate->checklist_section = null;
                    $duplicate->save();
                    $duplicate->delete();

                    if (
                        $orphanChecklistItemId
                        && (int) $orphanChecklistItemId !== (int) $survivor->checklist_item_id
                    ) {
                        \App\Models\ChecklistItem::query()
                            ->whereKey($orphanChecklistItemId)
                            ->whereDoesntHave('uploadType')
                            ->delete();
                    }

                    $merged++;
                }

                if ($survivor->checklist_section) {
                    app(ChecklistUploadTypeSyncService::class)->syncUploadType($survivor->fresh());
                }
            }

            // Strip trailing asterisks from remaining employee-file names.
            UploadType::query()
                ->employeeFileSections()
                ->where('name', 'like', '%*')
                ->each(function (UploadType $type) use (&$renamed): void {
                    $clean = rtrim(trim((string) $type->name), "* \t");
                    if ($clean !== '' && $clean !== $type->name) {
                        $type->name = $clean;
                        $type->save();
                        app(ChecklistUploadTypeSyncService::class)->syncUploadType($type);
                        $renamed++;
                    }
                });
        });

        return [
            'merged' => $merged,
            'renamed' => $renamed,
            'remapped_uploads' => $remappedUploads,
            'remapped_requirements' => $remappedRequirements,
        ];
    }
}
