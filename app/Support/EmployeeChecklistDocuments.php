<?php

namespace App\Support;

use App\Models\BPEmpChecklist;
use App\Models\BPEmployee;
use App\Models\ChecklistItem;
use Illuminate\Support\Facades\Auth;

class EmployeeChecklistDocuments
{
    public static function markOnFile(
        BPEmployee $employee,
        ChecklistItem $item,
        ?string $expiresAt = null
    ): void {
        $checklist = BPEmpChecklist::firstOrNew(['employee_num' => $employee->employee_num]);
        $items = $checklist->items ?? [];
        $key = 'item_' . $item->id;
        $existing = is_array($items[$key] ?? null) ? $items[$key] : [];

        $items[$key] = array_merge($existing, [
            'checklist_item_id' => $item->id,
            'doc_type_id' => $item->doc_type_id,
            'on_file' => true,
            'verified_dt' => null,
            'verified_by' => null,
            'exp_dt' => $item->isExpiring ? $expiresAt : ($existing['exp_dt'] ?? null),
            'exp_dt_not_required' => $item->isExpiring ? 0 : 1,
        ]);

        $checklist->items = $items;
        $checklist->save();
    }

    public static function markVerified(
        BPEmployee $employee,
        ChecklistItem $item,
        ?string $verifiedAt = null,
        ?string $expiresAt = null,
        ?int $verifiedBy = null
    ): void {
        $checklist = BPEmpChecklist::firstOrNew(['employee_num' => $employee->employee_num]);
        $items = $checklist->items ?? [];
        $key = 'item_' . $item->id;
        $existing = is_array($items[$key] ?? null) ? $items[$key] : [];

        $items[$key] = array_merge($existing, [
            'checklist_item_id' => $item->id,
            'doc_type_id' => $item->doc_type_id,
            'on_file' => true,
            'verified_dt' => $verifiedAt ?? now()->toDateString(),
            'verified_by' => $verifiedBy ?? Auth::id(),
            'exp_dt' => $item->isExpiring
                ? ($expiresAt ?? ($existing['exp_dt'] ?? null))
                : ($existing['exp_dt'] ?? null),
            'exp_dt_not_required' => $item->isExpiring ? 0 : 1,
        ]);

        $checklist->items = $items;
        $checklist->save();
    }
}
