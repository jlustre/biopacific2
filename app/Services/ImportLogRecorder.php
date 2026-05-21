<?php

namespace App\Services;

use App\Models\ImportLog;
use App\Models\ImportLogChange;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ImportLogRecorder
{
    protected ?ImportLog $log = null;

    public function isActive(): bool
    {
        return $this->log !== null;
    }

    public function activeLog(): ?ImportLog
    {
        return $this->log;
    }

    public function begin(Request $request, int $facilityId): ?ImportLog
    {
        $meta = $request->input('import_log', []);
        if (!is_array($meta)) {
            $meta = [];
        }

        $this->log = ImportLog::create([
            'user_id' => Auth::id() ?? 1,
            'facility_id' => $facilityId,
            'import_mapping_preset_id' => $meta['preset_id'] ?? null,
            'source' => $meta['source'] ?? 'facility',
            'source_filename' => $meta['source_filename'] ?? null,
            'status' => ImportLog::STATUS_RUNNING,
            'started_at' => now(),
        ]);

        return $this->log;
    }

    /**
     * @template T of Model
     * @param  callable(): T  $callback
     * @return T
     */
    public function trackUpsert(
        string $tableName,
        ?Model $existing,
        callable $callback,
        ?string $employeeNum = null,
    ): Model {
        $model = $callback();

        if (!$this->log) {
            return $model;
        }

        $employeeNum = $employeeNum ?? $model->getAttribute('employee_num');

        if ($existing) {
            ImportLogChange::create([
                'import_log_id' => $this->log->id,
                'table_name' => $tableName,
                'employee_num' => $employeeNum,
                'action' => ImportLogChange::ACTION_UPDATED,
                'record_key' => $this->recordKeyFor($tableName, $existing),
                'before_data' => $existing->getAttributes(),
                'after_data' => $model->fresh()?->getAttributes() ?? $model->getAttributes(),
            ]);
        } else {
            ImportLogChange::create([
                'import_log_id' => $this->log->id,
                'table_name' => $tableName,
                'employee_num' => $employeeNum,
                'action' => ImportLogChange::ACTION_INSERTED,
                'record_key' => $this->recordKeyFor($tableName, $model),
                'before_data' => null,
                'after_data' => $model->getAttributes(),
            ]);
        }

        return $model;
    }

    public function finalize(
        ?int $httpStatus,
        bool $success,
        array $payload = [],
    ): ?ImportLog {
        if (!$this->log) {
            return null;
        }

        $importResults = $payload['importResults'] ?? [];
        $failures = $payload['failures'] ?? [];
        $duplicates = $payload['duplicates'] ?? [];

        $inserted = 0;
        $updated = 0;
        $errors = 0;
        $skipped = 0;

        foreach ($importResults as $result) {
            $action = $result['action'] ?? '';
            if ($action === 'inserted') {
                $inserted++;
            } elseif ($action === 'updated') {
                $updated++;
            } elseif ($action === 'error') {
                $errors++;
            } elseif ($action === 'skipped') {
                $skipped++;
            }
        }

        $hasChanges = $this->log->changes()->exists();
        $tablesAffected = $this->log->changes()
            ->distinct()
            ->pluck('table_name')
            ->sort()
            ->values()
            ->all();

        $status = ImportLog::STATUS_FAILED;
        $errorMessage = $payload['message'] ?? $payload['error'] ?? null;

        if ($success) {
            $status = ($errors > 0 || !empty($failures)) ? ImportLog::STATUS_PARTIAL : ImportLog::STATUS_COMPLETED;
        } elseif ($httpStatus === 409 && !empty($duplicates)) {
            $status = $hasChanges ? ImportLog::STATUS_PARTIAL : ImportLog::STATUS_FAILED;
            $errorMessage = $errorMessage ?? 'Import stopped: duplicate employee IDs require confirmation to overwrite.';
        } elseif ($hasChanges && ($inserted + $updated > 0)) {
            $status = ImportLog::STATUS_PARTIAL;
        }

        $canRevert = $hasChanges && in_array($status, [ImportLog::STATUS_COMPLETED, ImportLog::STATUS_PARTIAL], true);

        $this->log->update([
            'status' => $status,
            'tables_affected' => $tablesAffected,
            'summary' => [
                'http_status' => $httpStatus,
                'rows_inserted' => $inserted,
                'rows_updated' => $updated,
                'rows_skipped' => $skipped,
                'rows_error' => $errors,
                'rows_total' => count($importResults),
                'changes_recorded' => $this->log->changes()->count(),
                'duplicates_found' => count($duplicates),
            ],
            'error_message' => $errorMessage,
            'completed_at' => now(),
            'can_revert' => $canRevert,
        ]);

        $finished = $this->log->fresh();
        $this->log = null;

        return $finished;
    }

    public function failEarly(string $message, int $httpStatus = 422): ?ImportLog
    {
        if (!$this->log) {
            return null;
        }

        $this->log->update([
            'status' => ImportLog::STATUS_FAILED,
            'error_message' => $message,
            'completed_at' => now(),
            'can_revert' => false,
            'summary' => ['http_status' => $httpStatus],
        ]);

        $finished = $this->log->fresh();
        $this->log = null;

        return $finished;
    }

    protected function recordKeyFor(string $tableName, Model $model): array
    {
        return match ($tableName) {
            'bp_employees' => [
                'id' => $model->getKey(),
                'employee_num' => $model->getAttribute('employee_num'),
            ],
            'bp_emp_job_data' => [
                'assign_id' => $model->getAttribute('assign_id') ?? $model->getKey(),
                'employee_num' => $model->getAttribute('employee_num'),
                'effdt' => $model->getAttribute('effdt'),
                'effseq' => $model->getAttribute('effseq'),
            ],
            'bp_emp_addresses' => [
                'id' => $model->getKey(),
                'employee_num' => $model->getAttribute('employee_num'),
                'effdt' => $model->getAttribute('effdt'),
                'effseq' => $model->getAttribute('effseq'),
            ],
            'bp_emp_phones' => [
                'id' => $model->getKey(),
                'employee_num' => $model->getAttribute('employee_num'),
                'phone_type' => $model->getAttribute('phone_type'),
            ],
            'bp_emp_tax_data' => [
                'tax_id' => $model->getAttribute('tax_id') ?? $model->getKey(),
                'employee_num' => $model->getAttribute('employee_num'),
                'effdt' => $model->getAttribute('effdt'),
                'effseq' => $model->getAttribute('effseq'),
            ],
            default => ['id' => $model->getKey()],
        };
    }
}
