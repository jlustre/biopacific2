<?php

namespace Database\Seeders;

use App\Models\Facility;
use App\Models\Upload;
use App\Models\UploadType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ExpiringLicensesReportTestDataSeeder extends Seeder
{
    public function run(): void
    {
        $facilities = Facility::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->limit(5)
            ->get(['id', 'name']);

        if ($facilities->isEmpty()) {
            $this->command?->warn('Expiring license report test data skipped: no active facilities found.');

            return;
        }

        $uploadType = UploadType::query()->firstOrCreate(
            ['name' => 'Seeded Report License / Certification'],
            [
                'description' => 'Seeded license/certification document for report testing.',
                'requires_expiry' => true,
                'is_license_or_certification' => true,
            ]
        );

        if (! $uploadType->requires_expiry || ! $uploadType->is_license_or_certification) {
            $uploadType->forceFill([
                'requires_expiry' => true,
                'is_license_or_certification' => true,
            ])->save();
        }

        $now = now();
        $created = 0;

        foreach ($facilities as $facilityIndex => $facility) {
            foreach ([1, 2] as $employeeIndex) {
                $employeeNum = sprintf('RPT%03d%02d', (int) $facility->id, $employeeIndex);
                $firstName = $employeeIndex === 1 ? 'Report' : 'Sample';
                $lastName = Str::limit(Str::slug($facility->name, ''), 40, '') ?: 'Facility';

                $employeeExists = DB::table('bp_employees')
                    ->where('employee_num', $employeeNum)
                    ->exists();
                $employeePayload = [
                    'ssn' => null,
                    'first_name' => $firstName,
                    'middle_name' => null,
                    'last_name' => $lastName . $employeeIndex,
                    'email' => strtolower($employeeNum) . '@example.test',
                    'gender' => 'N',
                    'original_hire_dt' => $now->copy()->subYears(2)->toDateString(),
                    'updated_at' => $now,
                ];

                if (! $employeeExists) {
                    $employeePayload['created_at'] = $now;
                }

                DB::table('bp_employees')->updateOrInsert(
                    ['employee_num' => $employeeNum],
                    $employeePayload
                );

                $employeeId = (int) DB::table('bp_employees')
                    ->where('employee_num', $employeeNum)
                    ->value('id');

                $assignmentExists = DB::table('bp_emp_job_data')
                    ->where('employee_num', $employeeNum)
                    ->where('effdt', $now->copy()->subYear()->toDateString())
                    ->where('effseq', 0)
                    ->exists();

                if (! $assignmentExists) {
                    DB::table('bp_emp_job_data')->insert([
                        'employee_num' => $employeeNum,
                        'effdt' => $now->copy()->subYear()->toDateString(),
                        'effseq' => 0,
                        'facility_id' => $facility->id,
                        'dept_id' => null,
                        'position_id' => null,
                        'reports_to' => null,
                        'reg_temp' => 'r',
                        'full_part_time' => 'ft',
                        'created_by' => 1,
                        'updated_by' => 1,
                        'created_at' => $now,
                        'updated_at' => $now,
                        'bargaining_unit_id' => null,
                        'union_seniority_dt' => null,
                    ]);
                }

                $credentialExpiry = $employeeIndex === 1
                    ? $now->copy()->subDays(10)->toDateString()
                    : $now->copy()->addDays(25 + ($facilityIndex * 5))->toDateString();

                DB::table('bp_emp_credentials')->updateOrInsert(
                    [
                        'employee_num' => $employeeId,
                        'credential_type' => $employeeIndex === 1 ? 'RN License' : 'CPR Certification',
                    ],
                    [
                        'credential_number' => 'TEST-' . $employeeNum,
                        'issue_date' => $now->copy()->subYear()->toDateString(),
                        'expiry_date' => $credentialExpiry,
                        'issuing_authority' => 'Seeded Test Authority',
                        'verified_via' => 'Seeder',
                        'last_verified_dt' => $now->copy()->subDays(30)->toDateString(),
                        'status' => $employeeIndex === 1 ? 'e' : 'a',
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]
                );

                $storagePath = Upload::employeeDirectory($employeeNum) . '/seeded-report-license.pdf';
                if (! Storage::disk('public')->exists($storagePath)) {
                    Storage::disk('public')->makeDirectory(Upload::employeeDirectory($employeeNum));
                    Storage::disk('public')->put(
                        $storagePath,
                        "Seeded license/certification placeholder for {$employeeNum}"
                    );
                }

                $uploadExpiry = $employeeIndex === 1
                    ? $now->copy()->addDays(14)->toDateString()
                    : $now->copy()->addDays(75)->toDateString();

                DB::table('uploads')->updateOrInsert(
                    ['file_path' => $storagePath],
                    [
                        'facility_id' => $facility->id,
                        'employee_num' => $employeeNum,
                        'user_id' => null,
                        'upload_type_id' => $uploadType->id,
                        'checklist_item_id' => null,
                        'type' => 'credential',
                        'original_filename' => 'seeded-report-license.pdf',
                        'file_size' => Storage::disk('public')->size($storagePath),
                        'uploaded_at' => $now,
                        'expires_at' => $uploadExpiry,
                        'effective_start_date' => $now->copy()->subYear()->toDateString(),
                        'comments' => 'Seeded expiring license/certification report test document.',
                        'submission_reason' => 'initial_upload',
                        'verification_status' => $employeeIndex === 1 ? 'pending' : 'approved',
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]
                );

                $created++;
            }
        }

        $this->command?->info("Seeded expiring license report test data for {$created} employees.");
    }
}
