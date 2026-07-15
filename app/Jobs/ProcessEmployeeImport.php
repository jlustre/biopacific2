<?php

namespace App\Jobs;

use App\Models\ImportLog;
use App\Models\ImportMappingPreset;
use App\Services\ImportPresetImportRunner;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Throwable;

class ProcessEmployeeImport implements ShouldQueue
{
    use Queueable;

    public int $tries = 1;

    public int $timeout = 1800;

    public function __construct(
        public int $importLogId,
        public bool $confirmOverwrite = false,
    ) {
        $this->onQueue('imports');
    }

    public function handle(ImportPresetImportRunner $runner): void
    {
        $log = ImportLog::query()->findOrFail($this->importLogId);

        if ($log->cancel_requested_at) {
            $this->markCancelled($log);
            $this->deleteWorkbook($log);

            return;
        }

        $preset = ImportMappingPreset::query()->findOrFail($log->import_mapping_preset_id);
        abort_unless($log->import_file_path && Storage::disk('local')->exists($log->import_file_path), 404);

        $previousUserId = Auth::id();
        Auth::loginUsingId($log->user_id);
        $log->update([
            'status' => ImportLog::STATUS_RUNNING,
            'started_at' => $log->started_at ?? now(),
            'error_message' => null,
        ]);

        try {
            $path = Storage::disk('local')->path($log->import_file_path);
            $file = new UploadedFile(
                $path,
                $log->source_filename ?: basename($path),
                null,
                null,
                true
            );

            $response = $runner->run(
                $preset,
                $file,
                (int) $log->facility_id,
                $this->confirmOverwrite,
                null,
                $log->id,
            );

            $log->refresh();
            if (($response->getData(true)['cancelled'] ?? false) || $log->cancel_requested_at) {
                $this->markCancelled($log);
            }

            if ($log->status !== ImportLog::STATUS_AWAITING_CONFIRMATION) {
                $this->deleteWorkbook($log);
            }
        } catch (Throwable $exception) {
            $log->update([
                'status' => ImportLog::STATUS_FAILED,
                'error_message' => $exception->getMessage(),
                'completed_at' => now(),
            ]);
            $this->deleteWorkbook($log);

            throw $exception;
        } finally {
            if ($previousUserId) {
                Auth::loginUsingId($previousUserId);
            } else {
                Auth::logout();
            }
        }
    }

    public function failed(?Throwable $exception): void
    {
        $log = ImportLog::query()->find($this->importLogId);
        if (! $log) {
            return;
        }

        $log->update([
            'status' => ImportLog::STATUS_FAILED,
            'error_message' => $exception?->getMessage() ?: 'The import queue job failed.',
            'completed_at' => now(),
        ]);
        $this->deleteWorkbook($log);
    }

    private function markCancelled(ImportLog $log): void
    {
        $log->update([
            'status' => ImportLog::STATUS_CANCELLED,
            'cancelled_at' => now(),
            'completed_at' => now(),
            'error_message' => 'Import cancelled by the user. Completed employee records were retained.',
            'can_revert' => $log->changes()->exists(),
        ]);
    }

    private function deleteWorkbook(ImportLog $log): void
    {
        if ($log->import_file_path) {
            Storage::disk('local')->delete($log->import_file_path);
            $log->update(['import_file_path' => null]);
        }
    }
}
