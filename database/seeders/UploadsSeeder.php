<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\Upload;
use App\Models\Facility;
use App\Models\UploadType;
use App\Models\User;
use App\Models\BPEmployee;
use App\Models\BPEmpAssignment;

class UploadsSeeder extends Seeder
{
    public function run()
    {
        // Get some images from C:/bio-pacific/website
        $imageDir = 'C:/bio-pacific/website';
        $images = collect(glob($imageDir . '/*.{jpg,jpeg,png,gif}', GLOB_BRACE));
        if ($images->isEmpty()) {
            $this->command->warn('No images found in ' . $imageDir . '. Using dummy uploads.');
        }

        $facilities = Facility::all();
        $uploadTypes = UploadType::all();
        $users = User::all();
        $employees = BPEmployee::with(['currentAssignment'])->get();

        $validPairs = [];
        foreach ($employees as $employee) {
            $assignment = $employee->currentAssignment;
            if ($assignment && $assignment->facility_id) {
                $validPairs[] = [
                    'employee' => $employee,
                    'facility_id' => $assignment->facility_id
                ];
            }
        }

        foreach (range(1, 100) as $i) {
            if (empty($validPairs)) break;
            $pair = collect($validPairs)->random();
            $employee = $pair['employee'];
            $facilityId = $pair['facility_id'];
            $uploadType = $uploadTypes->random();
            $user = $users->random();
            $fileName = $images->isNotEmpty() ? basename($images->random()) : 'dummy' . $i . '.jpg';
            $sourcePath = $images->isNotEmpty() ? $images->random() : null;
            $storagePath = 'uploads/' . Str::random(10) . '_' . $fileName;

            // Copy file to storage/app/public/uploads
            if ($sourcePath && file_exists($sourcePath)) {
                Storage::disk('public')->put($storagePath, file_get_contents($sourcePath));
            } else {
                // Create a dummy file
                Storage::disk('public')->put($storagePath, 'dummy content');
            }

            // Vary expires_at: bias toward green (future)
            if ($uploadType->requires_expiry) {
                if ($i % 8 === 0) {
                    // Already expired (about 1/8 of records)
                    $expiresAt = now()->subDays(rand(1, 365));
                } elseif ($i % 8 === 1) {
                    // Expiring soon (about 1/8 of records)
                    $expiresAt = now()->addDays(rand(1, 30));
                } else {
                    // Green (future, >30 days, about 3/4 of records)
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
                'original_filename' => $fileName,
                'file_size' => Storage::disk('public')->size($storagePath),
                'uploaded_at' => now(),
                'expires_at' => $expiresAt,
                'effective_start_date' => now(),
                'comments' => 'Seeded upload',
            ]);
        }
    }
}
