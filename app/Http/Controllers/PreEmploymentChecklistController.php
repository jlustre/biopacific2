<?php

namespace App\Http\Controllers;

use App\Models\EmployeeChecklist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PreEmploymentChecklistController extends Controller
{
    public function update(Request $request, EmployeeChecklist $employeeChecklist)
    {
        $user = $request->user();
        if (! $user || $employeeChecklist->user_id !== $user->id) {
            abort(403, 'Unauthorized');
        }

        $validated = $request->validate([
            'action' => ['required', 'in:save,submit'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $status = $employeeChecklist->status ?? 'draft';
        $editableStatuses = ['draft', 'returned'];
        if (! in_array($status, $editableStatuses, true)) {
            return back()->with('status', 'This item is locked and cannot be edited.');
        }

        $employeeChecklist->notes = $validated['notes'] ?? null;

        if ($validated['action'] === 'submit') {
            $employeeChecklist->status = 'submitted';
            $employeeChecklist->submitted_at = now();
        } else {
            $employeeChecklist->status = 'draft';
        }

        $employeeChecklist->save();

        return back()->with('status', 'Checklist item updated.');
    }

    public function returnForEdit(Request $request, EmployeeChecklist $employeeChecklist)
    {
        $user = $request->user();
        if (! $user || ! $user->hasAnyRole(['admin', 'facility-admin', 'hrrd'])) {
            abort(403, 'Unauthorized');
        }

        $validated = $request->validate([
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $employeeChecklist->status = 'returned';
        $employeeChecklist->returned_at = now();
        $employeeChecklist->returned_by = $user->id;
        $employeeChecklist->notes = $validated['notes'] ?? $employeeChecklist->notes;
        $employeeChecklist->save();

        return back()->with('status', 'Checklist item returned for edit.');
    }

    public function approve(Request $request, EmployeeChecklist $employeeChecklist)
    {
        $user = $request->user();
        if (! $user || ! $user->hasAnyRole(['admin', 'facility-admin', 'hrrd'])) {
            abort(403, 'Unauthorized');
        }

        if (($employeeChecklist->status ?? 'draft') !== 'submitted') {
            return back()->with('status', 'Only submitted items can be marked completed.');
        }

        $employeeChecklist->status = 'completed';
        $employeeChecklist->completed_at = now();
        $employeeChecklist->save();

        return back()->with('status', 'Checklist item marked completed.');
    }
}
