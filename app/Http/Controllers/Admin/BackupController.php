<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Backup;
use App\Services\Backup\BackupLogger;
use App\Services\Backup\BackupRemoteStorage;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BackupController extends Controller
{
    use AuthorizesRequests;
    public function index()
    {
        $this->authorize('viewAny', Backup::class);

        return view('admin.backups.index', [
            'title' => 'Backup & Restore Management',
        ]);
    }

    public function download(Backup $backup, BackupLogger $logger, BackupRemoteStorage $remoteStorage): StreamedResponse
    {
        $this->authorize('download', $backup);

        $backup->registerStorageDisk();
        $localDisk = Storage::disk($backup->disk());
        if ($backup->file_path && $localDisk->exists($backup->file_path)) {
            $logger->logDownloaded($backup);

            return $localDisk->download($backup->file_path, basename($backup->file_path));
        }

        $remotePath = $backup->remotePath();
        $stream = $remoteStorage->downloadStream($remotePath);
        abort_if($stream === null, 404);

        $logger->logDownloaded($backup);

        return response()->streamDownload(function () use ($stream) {
            fpassthru($stream);
            if (is_resource($stream)) {
                fclose($stream);
            }
        }, $remoteStorage->downloadFilename($remotePath), [
            'Content-Type' => 'application/zip',
        ]);
    }
}
