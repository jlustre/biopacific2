<?php

namespace Tests\Feature;

use App\Jobs\ProcessEmployeeImport;
use App\Models\Facility;
use App\Models\ImportLog;
use App\Models\ImportMappingPreset;
use App\Models\User;
use App\Services\ImportLogRecorder;
use App\Services\ImportPresetImportRunner;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class EmployeeImportProgressTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
    }

    public function test_progress_is_counted_per_employee_and_cancelled_records_are_retained(): void
    {
        $user = User::factory()->create();
        Auth::login($user);
        $log = ImportLog::query()->create([
            'user_id' => $user->id,
            'facility_id' => 3,
            'source' => 'admin_preset',
            'status' => ImportLog::STATUS_RUNNING,
        ]);
        $request = Request::create('/imports', 'POST', [
            'import_log' => ['import_log_id' => $log->id],
        ]);
        $recorder = app(ImportLogRecorder::class);

        $recorder->begin($request, 3);
        $recorder->setTotalRows(3);
        $recorder->recordProgress('inserted');
        $log->update(['cancel_requested_at' => now()]);
        $recorder->finalize(200, true, [
            'success' => true,
            'importResults' => [
                ['action' => 'inserted', 'employee_num' => 'E-1'],
            ],
        ]);

        $log->refresh();
        $this->assertSame(ImportLog::STATUS_CANCELLED, $log->status);
        $this->assertSame(3, $log->total_rows);
        $this->assertSame(1, $log->processed_rows);
        $this->assertSame(1, $log->imported_rows);
        $this->assertNotNull($log->cancelled_at);
    }

    public function test_a_cancelled_queued_job_removes_its_private_workbook(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('employee-imports/test.xlsx', 'workbook');
        $user = User::factory()->create();
        $log = ImportLog::query()->create([
            'user_id' => $user->id,
            'facility_id' => 3,
            'source' => 'admin_preset',
            'source_filename' => 'employees.xlsx',
            'import_file_path' => 'employee-imports/test.xlsx',
            'status' => ImportLog::STATUS_QUEUED,
            'cancel_requested_at' => now(),
        ]);

        (new ProcessEmployeeImport($log->id))
            ->handle($this->mock(ImportPresetImportRunner::class));

        $log->refresh();
        $this->assertSame(ImportLog::STATUS_CANCELLED, $log->status);
        $this->assertNull($log->import_file_path);
        Storage::disk('local')->assertMissing('employee-imports/test.xlsx');
    }

    public function test_only_the_import_owner_can_poll_its_progress(): void
    {
        $facility = Facility::query()->create([
            'name' => 'Test Facility',
            'slug' => 'test-facility',
            'color_scheme_id' => null,
        ]);
        $owner = User::factory()->create();
        $owner->assignRole('admin');
        $otherAdmin = User::factory()->create();
        $otherAdmin->assignRole('admin');
        $log = ImportLog::query()->create([
            'user_id' => $owner->id,
            'facility_id' => $facility->id,
            'source' => 'admin_preset',
            'status' => ImportLog::STATUS_RUNNING,
            'total_rows' => 4,
            'processed_rows' => 2,
            'imported_rows' => 2,
        ]);

        $this->actingAs($otherAdmin)
            ->getJson(route('admin.facility.mapping-presets.import-status', $log))
            ->assertForbidden();

        $this->actingAs($owner)
            ->getJson(route('admin.facility.mapping-presets.import-status', $log))
            ->assertOk()
            ->assertJsonPath('import.processed', 2)
            ->assertJsonPath('import.percent', 50);
    }

    public function test_starting_an_import_stores_the_workbook_and_dispatches_the_queue_job(): void
    {
        Queue::fake();
        Storage::fake('local');
        $facility = Facility::query()->create([
            'name' => 'Queue Test Facility',
            'slug' => 'queue-test-facility',
            'color_scheme_id' => null,
        ]);
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $preset = ImportMappingPreset::query()->create([
            'user_id' => $admin->id,
            'facility_id' => $facility->id,
            'name' => 'Employee CSV',
            'mappings' => [[
                'worksheet' => 'Worksheet',
                'worksheet_column' => 'Employee ID',
                'table' => 'bp_employees',
                'table_column' => 'employee_num',
            ]],
        ]);

        $response = $this->actingAs($admin)->postJson(
            route('admin.facility.mapping-presets.run-import', $preset->id),
            [
                'facility_id' => $facility->id,
                'file' => UploadedFile::fake()->create('employees.csv', 10, 'text/csv'),
            ]
        );

        $response->assertStatus(202)
            ->assertJsonPath('queued', true)
            ->assertJsonPath('import.status', ImportLog::STATUS_QUEUED);
        $log = ImportLog::query()->findOrFail($response->json('import.id'));
        Storage::disk('local')->assertExists($log->import_file_path);
        Queue::assertPushed(ProcessEmployeeImport::class, fn ($job) => $job->importLogId === $log->id);
    }
}
