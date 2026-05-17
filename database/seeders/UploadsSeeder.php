<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\Upload;
use App\Models\UploadType;
use App\Models\User;
use App\Models\BPEmployee;

class UploadsSeeder extends Seeder
{
    private const SAMPLE_SOURCE_DIR = 'C:/bio-pacific/website';

    /** EMP021–EMP035 (BPEmpEmployeesTableSeeder) */
    private const FACILITY_14_EMPLOYEES = [
        'EMP021', 'EMP022', 'EMP023', 'EMP024', 'EMP025',
        'EMP026', 'EMP027', 'EMP028', 'EMP029', 'EMP030',
        'EMP031', 'EMP032', 'EMP033', 'EMP034', 'EMP035',
    ];

    /** EMP036–EMP050 (BPEmpEmployeesTableSeeder) */
    private const FACILITY_17_EMPLOYEES = [
        'EMP036', 'EMP037', 'EMP038', 'EMP039', 'EMP040',
        'EMP041', 'EMP042', 'EMP043', 'EMP044', 'EMP045',
        'EMP046', 'EMP047', 'EMP048', 'EMP049', 'EMP050',
    ];

    /** @var list<string> Basenames resolved under SAMPLE_SOURCE_DIR, PDFs first. */
    private array $sampleBasenames = [];

    /** @var list<string> Absolute paths of files actually copied this run. */
    private array $copiedSourceFiles = [];

    private int $filesCopied = 0;

    public function run()
    {
        $uploadTypes = UploadType::all();
        if ($uploadTypes->isEmpty()) {
            $this->command?->warn('UploadsSeeder skipped: run UploadTypesTableSeeder first.');

            return;
        }

        $userId = User::query()->value('id');
        if ($userId === null) {
            $this->command?->warn('UploadsSeeder skipped: no users found for uploaded_by.');

            return;
        }

        $this->sampleBasenames = $this->discoverSampleBasenames();
        if ($this->sampleBasenames === []) {
            $this->command?->warn(
                'No sample PDF or image files found in ' . self::SAMPLE_SOURCE_DIR
                . '. Facility uploads will use text placeholders.'
            );
        } else {
            $this->command?->info(
                'Upload sample sources (' . count($this->sampleBasenames) . '): '
                . implode(', ', $this->sampleBasenames)
            );
        }

        $users = User::all();
        $employees = BPEmployee::with(['currentAssignment'])->get();

        $validPairs = [];
        foreach ($employees as $employee) {
            $assignment = $employee->currentAssignment;
            if ($assignment && $assignment->facility_id) {
                $validPairs[] = [
                    'employee' => $employee,
                    'facility_id' => $assignment->facility_id,
                ];
            }
        }

        $randomCount = 0;
        foreach (range(1, 100) as $i) {
            if (empty($validPairs)) {
                break;
            }
            $pair = collect($validPairs)->random();
            $employee = $pair['employee'];
            $facilityId = $pair['facility_id'];
            $uploadType = $uploadTypes->random();
            $user = $users->random();
            $originalFilename = $this->sampleBasenames !== []
                ? $this->sampleBasenames[($i - 1) % count($this->sampleBasenames)]
                : 'dummy' . $i . '.pdf';
            $storagePath = 'uploads/' . Str::random(10) . '_' . $originalFilename;

            if (!$this->copySampleToStorage($storagePath, $originalFilename, $i - 1)) {
                Storage::disk('public')->put($storagePath, 'dummy content');
            }

            if ($uploadType->requires_expiry) {
                if ($i % 8 === 0) {
                    $expiresAt = now()->subDays(rand(1, 365));
                } elseif ($i % 8 === 1) {
                    $expiresAt = now()->addDays(rand(1, 30));
                } else {
                    $expiresAt = now()->addDays(rand(31, 365));
                }
            } else {
                $expiresAt = null;
            }

            Upload::create([
                'facility_id' => $facilityId,
                'employee_num' => $employee->employee_num,
                'user_id' => $user->id,
                'upload_type_id' => $uploadType->id,
                'file_path' => $storagePath,
                'original_filename' => $originalFilename,
                'file_size' => Storage::disk('public')->size($storagePath),
                'uploaded_at' => now(),
                'expires_at' => $expiresAt,
                'effective_start_date' => now(),
                'comments' => 'Seeded upload',
            ]);
            $randomCount++;
        }

        $counts = $this->seedFacility14And17Uploads($userId);
        $totalRows = Upload::count();
        $this->command?->info(sprintf(
            'Uploads seeded: %d random, %d for facility 14, %d for facility 17 (%d new this run, %d rows total).',
            $randomCount,
            $counts[14],
            $counts[17],
            $randomCount + $counts[14] + $counts[17],
            $totalRows
        ));
        $this->command?->info(sprintf(
            'Sample files copied: %d (%s).',
            $this->filesCopied,
            $this->copiedSourceFiles === []
                ? 'none'
                : implode(', ', array_unique($this->copiedSourceFiles))
        ));
    }

    /**
     * @return list<string>
     */
    private function discoverSampleBasenames(): array
    {
        if (!is_dir(self::SAMPLE_SOURCE_DIR)) {
            $this->command?->warn('Sample directory not found: ' . self::SAMPLE_SOURCE_DIR);

            return [];
        }

        $pdfs = glob(self::SAMPLE_SOURCE_DIR . '/*.pdf') ?: [];
        $pdfs = array_merge($pdfs, glob(self::SAMPLE_SOURCE_DIR . '/*.PDF') ?: []);
        $images = glob(self::SAMPLE_SOURCE_DIR . '/*.{jpg,jpeg,png,gif}', GLOB_BRACE) ?: [];

        $paths = array_values(array_unique(array_merge($pdfs, $images)));
        sort($paths);

        return array_map('basename', $paths);
    }

    private function absoluteSamplePath(string $basename): string
    {
        return self::SAMPLE_SOURCE_DIR . '/' . $basename;
    }

    /**
     * Prefer demo images that match credential filenames; otherwise cycle all samples.
     */
    private function resolveSampleBasename(string $originalFilename, int $seedIndex): ?string
    {
        if ($this->sampleBasenames === []) {
            return null;
        }

        $needle = strtolower(pathinfo($originalFilename, PATHINFO_FILENAME));
        $keywordMap = [
            'cpr' => 'demoFirstAidCertificate.png',
            'first_aid' => 'demoFirstAidCertificate.png',
            'driver' => 'demoDriverLicense.png',
            'lvn' => 'demoLVNLicense.png',
            'rn_license' => 'demoRNLicense.png',
            'cna' => 'demoCNACertificate.png',
            'harassment' => 'demoSexualHarrasmentCertificate.png',
            'sexual' => 'demoSexualHarrasmentCertificate.png',
        ];

        foreach ($keywordMap as $keyword => $basename) {
            if (str_contains($needle, $keyword) && in_array($basename, $this->sampleBasenames, true)) {
                return $basename;
            }
        }

        $pdfBasenames = array_values(array_filter(
            $this->sampleBasenames,
            static fn (string $name) => str_ends_with(strtolower($name), '.pdf')
        ));
        if (str_ends_with(strtolower($originalFilename), '.pdf') && $pdfBasenames !== []) {
            return $pdfBasenames[$seedIndex % count($pdfBasenames)];
        }

        return $this->sampleBasenames[$seedIndex % count($this->sampleBasenames)];
    }

    private function copySampleToStorage(string $storagePath, string $originalFilename, int $seedIndex): bool
    {
        $basename = $this->resolveSampleBasename($originalFilename, $seedIndex);
        if ($basename === null) {
            return false;
        }

        $sourcePath = $this->absoluteSamplePath($basename);
        if (!is_readable($sourcePath)) {
            return false;
        }

        $directory = dirname($storagePath);
        if ($directory !== '.') {
            Storage::disk('public')->makeDirectory($directory);
        }

        $contents = file_get_contents($sourcePath);
        if ($contents === false || $contents === '') {
            return false;
        }

        Storage::disk('public')->put($storagePath, $contents);
        $this->filesCopied++;
        $this->copiedSourceFiles[] = $basename;

        return true;
    }

    private function isPlaceholderFile(string $storagePath): bool
    {
        if (!Storage::disk('public')->exists($storagePath)) {
            return true;
        }

        $size = Storage::disk('public')->size($storagePath);
        if ($size < 512) {
            return true;
        }

        $contents = Storage::disk('public')->get($storagePath);
        if ($contents === null || $contents === '') {
            return true;
        }

        $head = substr($contents, 0, 64);

        return str_starts_with($head, 'Seeded document placeholder')
            || str_starts_with($head, 'dummy content');
    }

    /**
     * Deterministic, idempotent uploads for facilities 14 and 17 (EMP021–EMP050).
     *
     * @return array{14: int, 17: int}
     */
    private function seedFacility14And17Uploads(int $userId): array
    {
        $uploadTypesByName = UploadType::all()->keyBy('name');
        $counts = [14 => 0, 17 => 0];
        $definitionIndex = 0;

        foreach ($this->facilityUploadDefinitions() as $definition) {
            $facilityId = $definition['facility_id'];
            $employeeNum = $definition['employee_num'];
            $typeName = $definition['upload_type'];
            $uploadType = $uploadTypesByName->get($typeName);

            if (!$uploadType) {
                $this->command?->warn("Upload type not found: {$typeName}");
                continue;
            }

            if (!$this->employeeBelongsToFacility($employeeNum, $facilityId)) {
                $this->command?->warn("Skipping {$employeeNum}: not assigned to facility {$facilityId}.");
                continue;
            }

            $slug = Str::slug($typeName, '_');
            $storagePath = Upload::employeeDirectory($employeeNum)
                . "/seed_fac{$facilityId}_{$slug}.pdf";

            $originalFilename = $definition['original_filename'];
            $existing = Upload::where('file_path', $storagePath)->first();
            $needsFile = !Storage::disk('public')->exists($storagePath)
                || $this->isPlaceholderFile($storagePath);

            if ($needsFile) {
                if (!$this->copySampleToStorage($storagePath, $originalFilename, $definitionIndex)) {
                    Storage::disk('public')->makeDirectory(Upload::employeeDirectory($employeeNum));
                    Storage::disk('public')->put(
                        $storagePath,
                        "Seeded document placeholder for {$originalFilename}"
                    );
                }
            }

            if ($existing) {
                if ($needsFile) {
                    $existing->update([
                        'file_size' => Storage::disk('public')->size($storagePath),
                        'original_filename' => $originalFilename,
                    ]);
                }
                $definitionIndex++;
                continue;
            }

            Upload::create([
                'facility_id' => $facilityId,
                'employee_num' => $employeeNum,
                'user_id' => $userId,
                'upload_type_id' => $uploadType->id,
                'file_path' => $storagePath,
                'original_filename' => $originalFilename,
                'file_size' => Storage::disk('public')->size($storagePath),
                'uploaded_at' => $definition['uploaded_at'] ?? now()->subDays(rand(5, 120)),
                'expires_at' => $this->resolveExpiresAt($uploadType, $definition['expiry'] ?? null),
                'effective_start_date' => $definition['effective_start_date'] ?? now()->subYear(),
                'comments' => $definition['comments'] ?? 'Seeded facility credential',
            ]);

            $counts[$facilityId]++;
            $definitionIndex++;
        }

        return $counts;
    }

    private function employeeBelongsToFacility(string $employeeNum, int $facilityId): bool
    {
        $allowed = $facilityId === 14 ? self::FACILITY_14_EMPLOYEES : self::FACILITY_17_EMPLOYEES;

        return in_array($employeeNum, $allowed, true);
    }

    private function resolveExpiresAt(UploadType $uploadType, ?string $expiry): mixed
    {
        if (!$uploadType->requires_expiry) {
            return null;
        }

        return match ($expiry) {
            'expired' => now()->subDays(rand(30, 400))->startOfDay(),
            'soon' => now()->addDays(rand(5, 28))->startOfDay(),
            'future' => now()->addDays(rand(60, 540))->startOfDay(),
            default => now()->addYear()->startOfDay(),
        };
    }

    /**
     * @return list<array{
     *     facility_id: int,
     *     employee_num: string,
     *     upload_type: string,
     *     original_filename: string,
     *     expiry?: string|null,
     *     comments?: string
     * }>
     */
    private function facilityUploadDefinitions(): array
    {
        return array_merge(
            $this->definitionsForFacility(14, self::FACILITY_14_EMPLOYEES),
            $this->definitionsForFacility(17, self::FACILITY_17_EMPLOYEES),
        );
    }

    /**
     * @param list<string> $employeeNums
     * @return list<array<string, mixed>>
     */
    private function definitionsForFacility(int $facilityId, array $employeeNums): array
    {
        $templates = [
            ['upload_type' => 'CPR Certification', 'original_filename' => 'cpr_bls_cert.pdf', 'expiry' => 'future'],
            ['upload_type' => 'First Aid Certification', 'original_filename' => 'first_aid_cert.pdf', 'expiry' => 'future'],
            ['upload_type' => 'Driver License/ID', 'original_filename' => 'ca_driver_license.pdf', 'expiry' => 'future'],
            ['upload_type' => 'TB Test Result', 'original_filename' => 'tb_skin_test_result.pdf', 'expiry' => 'soon'],
            ['upload_type' => 'Annual Influenza Vaccination', 'original_filename' => 'flu_vaccine_2025.pdf', 'expiry' => 'future'],
            ['upload_type' => 'HIPAA Training Certificate', 'original_filename' => 'hipaa_training_cert.pdf', 'expiry' => 'future'],
            ['upload_type' => 'Background Check Clearance', 'original_filename' => 'doj_fbi_clearance.pdf'],
            ['upload_type' => 'I-9 Form', 'original_filename' => 'i9_employment_eligibility.pdf'],
            ['upload_type' => 'W-4 Form', 'original_filename' => 'w4_withholding.pdf'],
            ['upload_type' => 'Physical Exam', 'original_filename' => 'annual_physical.pdf', 'expiry' => 'future'],
            ['upload_type' => 'COVID-19 Vaccination Record', 'original_filename' => 'covid_vaccination_card.pdf', 'expiry' => 'future'],
            ['upload_type' => 'Sexual Harassment Training Certificate', 'original_filename' => 'harassment_prevention_2025.pdf', 'expiry' => 'future'],
            ['upload_type' => 'Registered Nurse License', 'original_filename' => 'rn_license_ca.pdf', 'expiry' => 'future'],
            ['upload_type' => 'Licensed Vocational Nurse License', 'original_filename' => 'lvn_license_ca.pdf', 'expiry' => 'future'],
            ['upload_type' => 'Certified Nursing Assistant Certificate', 'original_filename' => 'cna_certificate.pdf', 'expiry' => 'expired'],
            ['upload_type' => 'Employee Handbook Acknowledgment', 'original_filename' => 'handbook_ack_signed.pdf'],
            ['upload_type' => 'Confidentiality Agreement', 'original_filename' => 'confidentiality_agreement.pdf'],
            ['upload_type' => 'Resume', 'original_filename' => 'employee_resume.pdf'],
            ['upload_type' => 'Social Security Card', 'original_filename' => 'ssn_card_copy.pdf'],
            ['upload_type' => 'Workplace Violence Prevention Training Certificate', 'original_filename' => 'wpv_training_cert.pdf', 'expiry' => 'future'],
            ['upload_type' => 'Dementia Care Training Record', 'original_filename' => 'dementia_care_training.pdf', 'expiry' => 'future'],
            ['upload_type' => 'Annual In-Service Training', 'original_filename' => 'annual_inservice_2025.pdf', 'expiry' => 'soon'],
            ['upload_type' => 'Hepatitis B Vaccination Record or Declination', 'original_filename' => 'hep_b_record.pdf', 'expiry' => 'future'],
        ];

        $definitions = [];
        $templateCount = count($templates);

        foreach ($employeeNums as $index => $employeeNum) {
            $first = $templates[$index % $templateCount];
            $second = $templates[($index + 7) % $templateCount];

            $definitions[] = array_merge($first, [
                'facility_id' => $facilityId,
                'employee_num' => $employeeNum,
            ]);
            $definitions[] = array_merge($second, [
                'facility_id' => $facilityId,
                'employee_num' => $employeeNum,
            ]);
        }

        return $definitions;
    }
}
