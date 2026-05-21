<?php

namespace App\Services;

use App\Models\BPEmpAddress;
use App\Models\BPEmpJobData;
use App\Models\BPEmployee;
use App\Models\BPEmpPhone;
use App\Models\BPEmpTaxData;
use App\Models\ImportLog;
use App\Models\ImportLogChange;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ImportLogReverter
{
    public function revert(ImportLog $importLog): array
    {
        if (!$importLog->canBeReverted()) {
            throw new \RuntimeException('This import cannot be reverted.');
        }

        $changes = $importLog->changes()
            ->orderByDesc('id')
            ->get();

        if ($changes->isEmpty()) {
            throw new \RuntimeException('No recorded changes to revert.');
        }

        $stats = ['restored' => 0, 'deleted' => 0, 'failed' => 0];
        $errors = [];

        DB::transaction(function () use ($changes, &$stats, &$errors) {
            foreach ($changes as $change) {
                try {
                    if ($change->action === ImportLogChange::ACTION_INSERTED) {
                        $this->deleteInserted($change);
                        $stats['deleted']++;
                    } elseif ($change->action === ImportLogChange::ACTION_UPDATED) {
                        $this->restoreUpdated($change);
                        $stats['restored']++;
                    }
                } catch (\Throwable $e) {
                    $stats['failed']++;
                    $errors[] = sprintf(
                        '%s (%s): %s',
                        $change->table_name,
                        $change->employee_num ?? '—',
                        $e->getMessage()
                    );
                }
            }
        });

        if ($stats['failed'] > 0 && $stats['restored'] === 0 && $stats['deleted'] === 0) {
            throw new \RuntimeException('Revert failed: ' . implode('; ', array_slice($errors, 0, 5)));
        }

        $importLog->update([
            'status' => ImportLog::STATUS_REVERTED,
            'can_revert' => false,
            'reverted_at' => now(),
            'reverted_by' => Auth::id(),
            'summary' => array_merge($importLog->summary ?? [], [
                'revert' => $stats,
                'revert_errors' => $errors,
            ]),
        ]);

        return ['stats' => $stats, 'errors' => $errors];
    }

    protected function deleteInserted(ImportLogChange $change): void
    {
        $key = $change->record_key ?? [];

        match ($change->table_name) {
            'bp_employees' => BPEmployee::query()
                ->when(isset($key['id']), fn ($q) => $q->where('id', $key['id']))
                ->when(isset($key['employee_num']), fn ($q) => $q->where('employee_num', $key['employee_num']))
                ->delete(),
            'bp_emp_job_data' => BPEmpJobData::query()
                ->where('assign_id', $key['assign_id'] ?? 0)
                ->delete(),
            'bp_emp_addresses' => BPEmpAddress::query()
                ->where('id', $key['id'] ?? 0)
                ->delete(),
            'bp_emp_phones' => BPEmpPhone::query()
                ->where('id', $key['id'] ?? 0)
                ->delete(),
            'bp_emp_tax_data' => BPEmpTaxData::query()
                ->where('tax_id', $key['tax_id'] ?? 0)
                ->delete(),
            default => throw new \RuntimeException('Unsupported table for revert: ' . $change->table_name),
        };
    }

    protected function restoreUpdated(ImportLogChange $change): void
    {
        $before = $change->before_data;
        if (!is_array($before) || empty($before)) {
            throw new \RuntimeException('Missing snapshot of previous data.');
        }

        $key = $change->record_key ?? [];

        match ($change->table_name) {
            'bp_employees' => BPEmployee::query()
                ->where('id', $key['id'] ?? $before['id'] ?? 0)
                ->update($this->onlyFillable(BPEmployee::class, $before)),
            'bp_emp_job_data' => BPEmpJobData::query()
                ->where('assign_id', $key['assign_id'] ?? $before['assign_id'] ?? 0)
                ->update($this->onlyFillable(BPEmpJobData::class, $before)),
            'bp_emp_addresses' => BPEmpAddress::query()
                ->where('id', $key['id'] ?? $before['id'] ?? 0)
                ->update($this->onlyFillable(BPEmpAddress::class, $before)),
            'bp_emp_phones' => BPEmpPhone::query()
                ->where('id', $key['id'] ?? $before['id'] ?? 0)
                ->update($this->onlyFillable(BPEmpPhone::class, $before)),
            'bp_emp_tax_data' => BPEmpTaxData::query()
                ->where('tax_id', $key['tax_id'] ?? $before['tax_id'] ?? 0)
                ->update($this->onlyFillable(BPEmpTaxData::class, $before)),
            default => throw new \RuntimeException('Unsupported table for revert: ' . $change->table_name),
        };
    }

    protected function onlyFillable(string $modelClass, array $data): array
    {
        $model = new $modelClass();
        $fillable = $model->getFillable();
        $guarded = array_flip($model->getGuarded());

        return collect($data)
            ->except(['id', 'assign_id', 'tax_id', 'created_at', 'updated_at'])
            ->filter(function ($value, $key) use ($fillable, $guarded) {
                if (isset($guarded[$key]) && $guarded[$key] === true) {
                    return false;
                }

                return in_array($key, $fillable, true) || empty($fillable);
            })
            ->all();
    }
}
