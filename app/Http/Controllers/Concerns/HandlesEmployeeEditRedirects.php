<?php

namespace App\Http\Controllers\Concerns;

use App\Models\BPEmployee;
use Illuminate\Http\RedirectResponse;

trait HandlesEmployeeEditRedirects
{
    /**
     * @return array<string, string>
     */
    protected function employeeFormRoutes(BPEmployee $employee, bool $isSelfService = false): array
    {
        if ($isSelfService) {
            return [
                'personal' => route('employment.personal.update'),
                'address' => route('employment.address.update'),
                'tax' => route('employment.tax.update'),
                'assignment' => route('employment.portal', ['tab' => 'job-data']),
                'documents_upload' => route('employment.documents.upload'),
                'documents_download' => route('employment.documents.download', ['document' => '__ID__']),
                'documents_view' => route('employment.documents.view', ['document' => '__ID__']),
                'documents_delete' => route('employment.documents.delete', ['document' => '__ID__']),
                'documents_update_base' => url('my-employment/documents'),
                'phones_add' => route('employment.phones.add'),
                'phones_update_base' => url('my-employment/phones'),
                'edit_page' => route('employment.portal'),
            ];
        }

        return [
            'personal' => route('admin.employees.personal.update', $employee->id),
            'address' => route('admin.employees.address.update', $employee->id),
            'tax' => route('admin.employees.tax.update', $employee->id),
            'assignment' => route('admin.employees.update_assignment', $employee->id),
            'documents_upload' => route('admin.employees.documents.upload', $employee->id),
            'documents_download' => route('admin.employees.documents.download', [$employee->id, '__ID__']),
            'documents_view' => route('admin.employees.documents.view', [$employee->id, '__ID__']),
            'documents_delete' => route('admin.employees.documents.delete', [$employee->id, '__ID__']),
            'documents_update_base' => url('admin/employees/' . $employee->id . '/documents'),
            'phones_add' => route('admin.employees.phones.add', $employee->id),
            'phones_update_base' => url('admin/employees/' . $employee->id . '/phones'),
            'edit_page' => route('admin.employees.edit', $employee->id),
        ];
    }

    protected function redirectToEmployeeEdit(BPEmployee|int $employee, ?string $tab = null, array $with = []): RedirectResponse
    {
        $employeeModel = $employee instanceof BPEmployee
            ? $employee
            : BPEmployee::with('currentAssignment')->findOrFail($employee);

        if (request()->routeIs('employment.*')) {
            $params = array_filter([
                'tab' => $tab,
                'facility' => request('facility') ?? $employeeModel->currentAssignment?->facility_id,
            ], fn ($value) => $value !== null && $value !== '');

            $redirect = redirect()->route('employment.portal', $params);
        } else {
            $redirect = redirect()->route('admin.employees.edit', $employeeModel->id);

            if ($tab) {
                $redirect = $redirect->with('employeeTab', $tab);
            }
        }

        foreach ($with as $key => $value) {
            $redirect = $redirect->with($key, $value);
        }

        if ($tab && request()->routeIs('employment.*')) {
            $redirect = $redirect->with('employeeTab', $tab);
        }

        return $redirect;
    }
}
