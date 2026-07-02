<?php

namespace App\Livewire\Admin\Backups;

use App\Services\Backup\BackupCustomPathService;
use App\Services\Backup\BackupDestinationResolver;
use App\Services\Backup\BackupService;
use App\Services\Backup\BackupTableResolver;
use App\Support\Backup\BackupType;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Validation\Rule;
use Livewire\Component;

class CreateBackupForm extends Component
{
    use AuthorizesRequests;

    public string $backupType = BackupType::STRUCTURAL;

    /** @var list<string> */
    public array $selectedSections = [];

    /** @var list<string> */
    public array $selectedFolders = [];

    public string $destination = 'local';

    public string $customPath = '';

    public string $notes = '';

    public string $backupName = '';

    public bool $isSubmitting = false;

    public function mount(
        BackupDestinationResolver $destinationResolver,
        BackupCustomPathService $customPaths,
    ): void {
        $this->authorize('create', \App\Models\Backup::class);
        $this->selectedSections = array_keys($this->sections());
        $this->selectedFolders = array_keys($this->folders());
        $this->destination = array_key_first($destinationResolver->available()) ?: 'local';
        $this->customPath = $customPaths->lastUsed() ?? '';
    }

    protected function rules(BackupDestinationResolver $destinationResolver): array
    {
        return [
            'backupType' => 'required|in:' . implode(',', BackupType::all()),
            'selectedSections' => 'array',
            'selectedFolders' => 'array',
            'destination' => ['required', Rule::in(array_keys($destinationResolver->available()))],
            'customPath' => 'required_if:destination,custom|nullable|string|max:500',
            'notes' => 'nullable|string|max:2000',
            'backupName' => 'nullable|string|max:255',
        ];
    }

    public function updatedBackupType(): void
    {
        if ($this->backupType === BackupType::FILES) {
            $this->selectedSections = [];
        }
    }

    public function createBackup(BackupService $backupService, BackupDestinationResolver $destinationResolver): void
    {
        $this->authorize('create', \App\Models\Backup::class);
        $this->validate($this->rules($destinationResolver));
        $this->isSubmitting = true;

        try {
            $backupService->create(
                backupType: $this->backupType,
                selectedSections: $this->selectedSections,
                selectedFileGroups: [],
                selectedFolders: in_array($this->backupType, [BackupType::FULL, BackupType::FILES], true)
                    ? $this->selectedFolders
                    : [],
                notes: $this->notes !== '' ? $this->notes : null,
                backupName: $this->backupName !== '' ? $this->backupName : null,
                destination: $this->destination,
                customPath: $this->destination === 'custom' ? $this->customPath : null,
            );

            $this->reset(['notes', 'backupName']);
            $this->dispatch('backup-module-refresh');
            session()->flash('backup_success', 'Backup created successfully.');
        } catch (\Throwable $e) {
            $this->addError('backupType', $e->getMessage());
        } finally {
            $this->isSubmitting = false;
        }
    }

    public function sections(): array
    {
        return app(BackupTableResolver::class)->availableSections();
    }

    public function folders(): array
    {
        return app(BackupTableResolver::class)->availableFolders();
    }

    public function destinations(): array
    {
        return app(BackupDestinationResolver::class)->available();
    }

    public function render()
    {
        return view('livewire.admin.backups.create-backup-form', [
            'backupTypes' => BackupType::labels(),
            'sections' => $this->sections(),
            'folders' => $this->folders(),
            'destinations' => $this->destinations(),
        ]);
    }
}
