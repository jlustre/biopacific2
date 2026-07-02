<?php



namespace App\Services\Backup;



use App\Models\Backup;

use Illuminate\Http\UploadedFile;

use Illuminate\Support\Facades\File;

use Illuminate\Support\Facades\Storage;

use RuntimeException;

use ZipArchive;



class BackupFileManager

{

    public function disk(): string

    {

        return (string) config('backup.disk', 'local');

    }



    public function directory(): string

    {

        return trim((string) config('backup.directory', 'backups'), '/');

    }



    public function ensureDirectory(?string $disk = null, ?string $directory = null): void

    {

        Storage::disk($disk ?? $this->disk())->makeDirectory($directory ?? $this->directory());

    }



    public function buildArchiveFilename(Backup $backup): string

    {

        $slug = preg_replace('/[^a-zA-Z0-9_-]+/', '-', strtolower($backup->backup_name)) ?: 'backup';



        return sprintf('%s-%d-%s.zip', $slug, $backup->id, now()->format('Ymd_His'));

    }



    public function buildArchivePath(Backup $backup): string

    {

        return $this->directory() . '/' . $this->buildArchiveFilename($backup);

    }



    public function createWorkingDirectory(Backup $backup): string

    {

        $path = storage_path('app/backup-work/' . $backup->id);

        File::ensureDirectoryExists($path);



        return $path;

    }



    public function cleanupWorkingDirectory(Backup $backup): void

    {

        $path = storage_path('app/backup-work/' . $backup->id);

        if (is_dir($path)) {

            File::deleteDirectory($path);

        }

    }



    /**

     * @param  list<array{group: string, disk: string, path: string}>  $filePaths

     */

    public function copyFilesToWorkingDirectory(string $workingDir, array $filePaths): array

    {

        $included = [];



        foreach ($filePaths as $entry) {

            $disk = Storage::disk($entry['disk']);

            if (! $disk->exists($entry['path'])) {

                continue;

            }



            $targetBase = $workingDir . '/files/' . $entry['group'] . '/' . $entry['disk'] . '/' . $entry['path'];

            $this->copyStoragePathToLocal($disk, $entry['path'], $targetBase);



            $included[] = [

                'group' => $entry['group'],

                'disk' => $entry['disk'],

                'path' => $entry['path'],

            ];

        }



        return $included;

    }



    public function zipWorkingDirectory(string $workingDir, string $archiveRelativePath, ?string $disk = null): int

    {

        $disk = $disk ?? $this->disk();

        $zip = new ZipArchive;

        $absoluteArchive = Storage::disk($disk)->path($archiveRelativePath);



        File::ensureDirectoryExists(dirname($absoluteArchive));



        if ($zip->open($absoluteArchive, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {

            throw new RuntimeException('Unable to create backup archive.');

        }



        $this->addDirectoryToZip($zip, $workingDir);

        $zip->close();



        return (int) filesize($absoluteArchive);

    }



    /**

     * @return array{path: string, size: int, disk: string}

     */

    public function storeArchiveFromWorkingDirectory(

        string $workingDir,

        string $filename,

        string $storageDisk,

        string $storageDirectory,

    ): array {

        $tempZip = $workingDir . '/_archive.zip';

        $zip = new ZipArchive;



        if ($zip->open($tempZip, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {

            throw new RuntimeException('Unable to create backup archive.');

        }



        $this->addDirectoryToZip($zip, $workingDir);

        $zip->close();



        $directory = trim($storageDirectory, '/');

        $relativePath = $directory !== '' ? $directory . '/' . $filename : $filename;

        if ($directory !== '') {

            Storage::disk($storageDisk)->makeDirectory($directory);

        }

        Storage::disk($storageDisk)->put($relativePath, file_get_contents($tempZip));



        if (is_file($tempZip)) {

            @unlink($tempZip);

        }



        return [

            'path' => $relativePath,

            'size' => (int) Storage::disk($storageDisk)->size($relativePath),

            'disk' => $storageDisk,

        ];

    }



    public function extractArchive(string $archiveRelativePath, string $destination, ?string $disk = null): void

    {

        $disk = $disk ?? $this->disk();

        $zip = new ZipArchive;

        $absoluteArchive = Storage::disk($disk)->path($archiveRelativePath);



        if ($zip->open($absoluteArchive) !== true) {

            throw new RuntimeException('Unable to open backup archive.');

        }



        File::ensureDirectoryExists($destination);

        $zip->extractTo($destination);

        $zip->close();

    }



    public function readManifest(string $archiveRelativePath, ?string $disk = null): array

    {

        $disk = $disk ?? $this->disk();

        $zip = new ZipArchive;

        $absoluteArchive = Storage::disk($disk)->path($archiveRelativePath);



        if ($zip->open($absoluteArchive) !== true) {

            throw new RuntimeException('Unable to open backup archive.');

        }



        $manifest = $zip->getFromName('manifest.json');

        $zip->close();



        if ($manifest === false) {

            throw new RuntimeException('Backup archive is missing manifest.json.');

        }



        $decoded = json_decode($manifest, true);

        if (! is_array($decoded)) {

            throw new RuntimeException('Backup manifest is invalid JSON.');

        }



        return $decoded;

    }



    public function readManifestFromUpload(UploadedFile $file): array

    {

        $tempPath = $file->getRealPath();

        $zip = new ZipArchive;



        if ($zip->open($tempPath) !== true) {

            throw new RuntimeException('Uploaded file is not a valid ZIP backup.');

        }



        $manifest = $zip->getFromName('manifest.json');

        $zip->close();



        if ($manifest === false) {

            throw new RuntimeException('Uploaded backup is missing manifest.json.');

        }



        $decoded = json_decode($manifest, true);

        if (! is_array($decoded)) {

            throw new RuntimeException('Uploaded backup manifest is invalid JSON.');

        }



        return $decoded;

    }



    public function storeUploadedBackup(UploadedFile $file): string

    {

        $this->ensureDirectory();

        $filename = 'uploaded-' . now()->format('Ymd_His') . '-' . preg_replace('/[^a-zA-Z0-9._-]+/', '-', $file->getClientOriginalName());



        return $file->storeAs($this->directory(), $filename, $this->disk());

    }



    public function deleteArchive(?string $relativePath, ?string $disk = null): void

    {

        $disk = $disk ?? $this->disk();

        if ($relativePath && Storage::disk($disk)->exists($relativePath)) {

            Storage::disk($disk)->delete($relativePath);

        }

    }



    public function totalStorageUsed(?string $disk = null, ?string $directory = null): int

    {

        $disk = $disk ?? $this->disk();

        $directory = $directory ?? $this->directory();

        $files = Storage::disk($disk)->allFiles($directory);

        $total = 0;



        foreach ($files as $file) {

            $total += (int) Storage::disk($disk)->size($file);

        }



        return $total;

    }



    protected function copyStoragePathToLocal($disk, string $path, string $target): void

    {

        if ($disk->directoryExists($path)) {

            File::ensureDirectoryExists($target);

            foreach ($disk->allFiles($path) as $file) {

                $relative = ltrim(str_replace($path, '', $file), '/');

                $localTarget = $target . '/' . $relative;

                File::ensureDirectoryExists(dirname($localTarget));

                File::put($localTarget, $disk->get($file));

            }



            return;

        }



        if ($disk->exists($path)) {

            File::ensureDirectoryExists(dirname($target));

            File::put($target, $disk->get($path));

        }

    }



    protected function addDirectoryToZip(ZipArchive $zip, string $directory, ?string $archivePrefix = null): void

    {

        if (! is_dir($directory)) {

            return;

        }



        $iterator = new \RecursiveIteratorIterator(

            new \RecursiveDirectoryIterator($directory, \FilesystemIterator::SKIP_DOTS),

            \RecursiveIteratorIterator::SELF_FIRST

        );



        foreach ($iterator as $item) {

            $fullPath = $item->getPathname();

            $relative = ltrim(str_replace($directory, '', $fullPath), DIRECTORY_SEPARATOR);

            $zipPath = $archivePrefix

                ? trim($archivePrefix . '/' . str_replace('\\', '/', $relative), '/')

                : str_replace('\\', '/', $relative);



            if ($item->isDir()) {

                $zip->addEmptyDir($zipPath);

            } else {

                $zip->addFile($fullPath, $zipPath);

            }

        }

    }

}


