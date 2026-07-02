<?php

namespace Tests\Feature\Admin;

use App\Models\Backup;
use App\Models\User;
use App\Services\Backup\BackupService;
use App\Support\Backup\BackupStatus;
use App\Support\Backup\BackupType;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\BackupTestCase;

class BackupManagementTest extends BackupTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'user', 'guard_name' => 'web']);
    }

    public function test_guests_cannot_access_backup_module(): void
    {
        $this->get(route('admin.backups.index'))->assertRedirect(route('login'));
    }

    public function test_non_admin_users_cannot_access_backup_module(): void
    {
        $user = User::factory()->create();
        $user->assignRole('user');

        $this->actingAs($user)
            ->get(route('admin.backups.index'))
            ->assertForbidden();
    }

    public function test_admin_can_access_backup_module(): void
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);

        $this->assertTrue($admin->can('viewAny', Backup::class));

        Livewire::test(\App\Livewire\Admin\Backups\BackupDashboard::class)
            ->assertOk()
            ->assertSee('Total Backups');

        Livewire::test(\App\Livewire\Admin\Backups\CreateBackupForm::class)
            ->assertOk()
            ->assertSee('Create Backup');
    }

    public function test_structural_backup_can_be_created_and_completed(): void
    {
        Storage::fake('local');
        config(['backup.disk' => 'local', 'backup.process_immediately' => true]);

        $admin = $this->createAdmin();
        $this->actingAs($admin);

        $backup = app(BackupService::class)->create(
            backupType: BackupType::STRUCTURAL,
            selectedSections: ['system_settings'],
            notes: 'Test backup',
            backupName: 'Test Structural Backup',
        );

        $this->assertSame(BackupStatus::COMPLETED, $backup->status);
        $this->assertNotNull($backup->file_path);
        $this->assertGreaterThan(0, $backup->file_size);
        $this->assertTrue(Storage::disk('local')->exists($backup->file_path));
        $this->assertContains('settings', $backup->included_tables ?? []);
    }

    public function test_custom_folder_backup_completes_immediately(): void
    {
        $customDir = storage_path('app/backup-custom-test-' . uniqid());
        \Illuminate\Support\Facades\File::ensureDirectoryExists($customDir);

        try {
            config(['backup.process_immediately' => true]);

            $admin = $this->createAdmin();
            $this->actingAs($admin);

            $backup = app(BackupService::class)->create(
                backupType: BackupType::STRUCTURAL,
                selectedSections: ['system_settings'],
                backupName: 'Custom Folder Backup',
                destination: 'custom',
                customPath: $customDir,
            );

            $this->assertSame(BackupStatus::COMPLETED, $backup->status);
            $this->assertGreaterThan(0, $backup->file_size);
            $this->assertFileExists($customDir . DIRECTORY_SEPARATOR . basename($backup->file_path));
        } finally {
            if (is_dir($customDir)) {
                \Illuminate\Support\Facades\File::deleteDirectory($customDir);
            }
        }
    }

    public function test_admin_can_download_completed_backup(): void
    {
        Storage::fake('local');
        config(['backup.disk' => 'local']);

        $admin = $this->createAdmin();
        $backup = Backup::query()->create([
            'backup_name' => 'Downloadable Backup',
            'backup_type' => BackupType::STRUCTURAL,
            'file_path' => 'backups/download-test.zip',
            'file_size' => 12,
            'included_tables' => ['settings'],
            'status' => BackupStatus::COMPLETED,
            'created_by' => $admin->id,
        ]);

        Storage::disk('local')->put('backups/download-test.zip', 'test-zip-content');

        $this->actingAs($admin)
            ->get(route('admin.backups.download', $backup))
            ->assertOk();
    }

    public function test_scheduled_backup_command_creates_backup_when_enabled(): void
    {
        Storage::fake('local');
        config([
            'backup.disk' => 'local',
            'backup.schedule.enabled' => true,
            'backup.schedule.type' => BackupType::STRUCTURAL,
        ]);

        Artisan::call('backup:run-scheduled');

        $this->assertDatabaseCount('backups', 1);

        $backup = Backup::query()->first();
        $this->assertNotNull($backup);
        $this->assertSame(BackupType::STRUCTURAL, $backup->backup_type);
        $this->assertContains($backup->status, [
            BackupStatus::PENDING,
            BackupStatus::PROCESSING,
            BackupStatus::COMPLETED,
        ]);
    }

    public function test_scheduled_backup_command_exits_when_disabled(): void
    {
        config(['backup.schedule.enabled' => false]);

        Artisan::call('backup:run-scheduled');

        $this->assertDatabaseCount('backups', 0);
    }

    public function test_completed_backup_mirrors_to_s3_when_enabled(): void
    {
        Storage::fake('local');
        Storage::fake('s3');

        config([
            'backup.disk' => 'local',
            'backup.remote_mirror_enabled' => true,
            'backup.remote_disk' => 's3',
            'backup.remote_directory' => 'backups',
        ]);

        $admin = $this->createAdmin();
        $this->actingAs($admin);

        $backup = app(BackupService::class)->create(
            backupType: BackupType::STRUCTURAL,
            selectedSections: ['system_settings'],
            backupName: 'Remote Mirror Test',
        );

        $this->assertSame(BackupStatus::COMPLETED, $backup->status);
        $this->assertNotNull($backup->metadata['remote_path'] ?? null);
        $this->assertTrue(
            Storage::disk('s3')->exists($backup->metadata['remote_path'])
        );
    }

    protected function createAdmin(): User
    {
        $user = User::factory()->create();
        $user->assignRole('admin');

        return $user;
    }
}
