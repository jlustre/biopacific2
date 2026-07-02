<?php

namespace App\Services\Backup;

use Illuminate\Support\Facades\Schema;

class BackupTableResolver
{
    /**
     * @param  list<string>  $selectedSections
     * @return list<string>
     */
    public function tablesForBackup(string $backupType, array $selectedSections = [], array $selectedFileGroups = []): array
    {
        $tables = match ($backupType) {
            'full' => array_merge(
                config('backup-groups.structural_tables', []),
                config('backup-groups.transactional_tables', [])
            ),
            'structural' => $this->tablesFromSections($selectedSections) ?: config('backup-groups.structural_tables', []),
            'transactional' => $this->tablesFromSections($selectedSections) ?: config('backup-groups.transactional_tables', []),
            'files' => [],
            default => [],
        };

        if ($backupType === 'full' && $selectedSections !== []) {
            $tables = $this->tablesFromSections($selectedSections);
        }

        return $this->filterExistingTables(array_values(array_unique($tables)));
    }

    /**
     * @param  list<string>  $sectionKeys
     * @return list<string>
     */
    public function tablesFromSections(array $sectionKeys): array
    {
        $sections = config('backup-groups.sections', []);
        $tables = [];

        foreach ($sectionKeys as $key) {
            if (! isset($sections[$key]['tables'])) {
                continue;
            }

            $tables = array_merge($tables, $sections[$key]['tables']);
        }

        return array_values(array_unique($tables));
    }

    /**
     * @return array<string, array{label: string, description: string, tables: list<string>}>
     */
    public function availableSections(): array
    {
        return config('backup-groups.sections', []);
    }

    /**
     * @return array<string, array{label: string, disk: string, paths: list<string>}>
     */
    public function availableFileGroups(): array
    {
        return config('backup-groups.file_paths', []);
    }

    /**
     * @return array<string, array{key: string, group: string, group_label: string, disk: string, path: string, label: string}>
     */
    public function availableFolders(): array
    {
        $folders = [];

        foreach ($this->availableFileGroups() as $groupKey => $group) {
            foreach ($group['paths'] as $path) {
                $key = $this->folderKey($groupKey, $path);
                $folders[$key] = [
                    'key' => $key,
                    'group' => $groupKey,
                    'group_label' => (string) ($group['label'] ?? $groupKey),
                    'disk' => (string) ($group['disk'] ?? 'local'),
                    'path' => $path,
                    'label' => $this->folderLabel($path),
                ];
            }
        }

        return $folders;
    }

    /**
     * @param  list<string>  $selectedFolders  Keys in group::path format
     * @param  list<string>  $selectedFileGroups
     * @return list<array{group: string, disk: string, path: string}>
     */
    public function resolveFilePaths(array $selectedFolders = [], array $selectedFileGroups = []): array
    {
        if ($selectedFolders !== []) {
            return $this->resolveSelectedFolders($selectedFolders);
        }

        $groups = $this->availableFileGroups();
        $keys = $selectedFileGroups === [] ? array_keys($groups) : $selectedFileGroups;
        $resolved = [];

        foreach ($keys as $key) {
            if (! isset($groups[$key])) {
                continue;
            }

            $group = $groups[$key];
            foreach ($group['paths'] as $path) {
                $resolved[] = [
                    'group' => $key,
                    'disk' => $group['disk'],
                    'path' => $path,
                ];
            }
        }

        return $resolved;
    }

    /**
     * @param  list<string>  $selectedFolderKeys
     * @return list<array{group: string, disk: string, path: string}>
     */
    protected function resolveSelectedFolders(array $selectedFolderKeys): array
    {
        $available = $this->availableFolders();
        $resolved = [];

        foreach ($selectedFolderKeys as $key) {
            if (! isset($available[$key])) {
                continue;
            }

            $folder = $available[$key];
            $resolved[] = [
                'group' => $folder['group'],
                'disk' => $folder['disk'],
                'path' => $folder['path'],
            ];
        }

        return $resolved;
    }

    public function folderKey(string $group, string $path): string
    {
        return $group . '::' . $path;
    }

    protected function folderLabel(string $path): string
    {
        return ucwords(str_replace(['_', '-'], ' ', basename($path)));
    }

    /**
     * @param  list<string>  $tables
     * @return list<string>
     */
    public function filterExistingTables(array $tables): array
    {
        return array_values(array_filter($tables, fn (string $table) => Schema::hasTable($table)));
    }

    public function tableExists(string $table): bool
    {
        return Schema::hasTable($table);
    }
}
