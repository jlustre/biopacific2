<?php

namespace App\Livewire\Admin\Backups;

use App\Services\Backup\BackupCustomPathService;
use Livewire\Attributes\Modelable;
use Livewire\Component;

class FolderPathPicker extends Component
{
    #[Modelable]
    public string $path = '';

    public string $inputId = 'folder-path';

    public string $placeholder = 'D:\BioPacific-Backups';

    public bool $showBrowser = false;

    public ?string $browseCurrent = null;

    public ?string $browseParent = null;

    /** @var list<array{path: string, name: string, writable: bool}> */
    public array $browseRoots = [];

    /** @var list<array{path: string, name: string, writable: bool}> */
    public array $browseEntries = [];

    public string $browserError = '';

    public function openBrowser(BackupCustomPathService $customPaths): void
    {
        $this->browserError = '';
        $this->showBrowser = true;

        try {
            if ($this->path !== '' && is_dir($this->path) && $customPaths->isAllowedPath($this->path)) {
                $this->loadBrowseResult($customPaths->browse($this->path));
            } else {
                $this->loadBrowseResult($customPaths->browse());
            }
        } catch (\Throwable $e) {
            $this->browserError = $e->getMessage();
            $this->loadBrowseResult($customPaths->browse());
        }
    }

    public function closeBrowser(): void
    {
        $this->showBrowser = false;
        $this->browserError = '';
    }

    public function browseTo(string $targetPath, BackupCustomPathService $customPaths): void
    {
        $this->browserError = '';

        try {
            $this->loadBrowseResult($customPaths->browse($targetPath));
        } catch (\Throwable $e) {
            $this->browserError = $e->getMessage();
        }
    }

    public function browseUp(BackupCustomPathService $customPaths): void
    {
        $this->browserError = '';

        try {
            if ($this->browseParent) {
                $this->loadBrowseResult($customPaths->browse($this->browseParent));

                return;
            }

            $this->loadBrowseResult($customPaths->browse());
        } catch (\Throwable $e) {
            $this->browserError = $e->getMessage();
        }
    }

    public function selectFolder(string $targetPath, BackupCustomPathService $customPaths): void
    {
        $this->browserError = '';

        try {
            $resolved = $customPaths->remember($targetPath);
            $this->path = $resolved;
            $this->closeBrowser();
        } catch (\Throwable $e) {
            $this->browserError = $e->getMessage();
        }
    }

    public function selectCurrentFolder(BackupCustomPathService $customPaths): void
    {
        if (! $this->browseCurrent) {
            return;
        }

        $this->selectFolder($this->browseCurrent, $customPaths);
    }

    /**
     * @param  array{
     *     current: ?string,
     *     parent: ?string,
     *     roots: list<array{path: string, name: string, writable: bool}>,
     *     entries: list<array{path: string, name: string, writable: bool}>
     * }  $result
     */
    protected function loadBrowseResult(array $result): void
    {
        $this->browseCurrent = $result['current'];
        $this->browseParent = $result['parent'];
        $this->browseRoots = $result['roots'];
        $this->browseEntries = $result['entries'];
    }

    public function render()
    {
        return view('livewire.admin.backups.folder-path-picker');
    }
}
