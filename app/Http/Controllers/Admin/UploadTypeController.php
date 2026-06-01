<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\UploadType;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UploadTypeController extends Controller
{
    public function index(Request $request): View
    {
        $query = UploadType::query()->orderBy('name');

        if ($request->filled('search')) {
            $search = trim((string) $request->input('search'));
            $query->where(function ($scope) use ($search): void {
                $scope->where('name', 'like', '%' . $search . '%')
                    ->orWhere('description', 'like', '%' . $search . '%');
            });
        }

        if ($request->filled('department_id')) {
            $departmentId = (int) $request->input('department_id');
            $query->whereJsonContains('department_ids', $departmentId);
        }

        $requiresExpiry = $request->input('requires_expiry');
        if (in_array($requiresExpiry, ['0', '1', 0, 1], true)) {
            $query->where('requires_expiry', (int) $requiresExpiry);
        }

        $isLicenseOrCertification = $request->input('is_license_or_certification');
        if (in_array($isLicenseOrCertification, ['0', '1', 0, 1], true)) {
            $query->where('is_license_or_certification', (int) $isLicenseOrCertification);
        }

        $uploadTypes = $query->paginate(20)->withQueryString();
        $departments = Department::query()->orderBy('name')->get(['id', 'name']);

        return view('admin.upload-types.index', compact('uploadTypes', 'departments'));
    }

    public function create(): View
    {
        $departments = Department::query()->orderBy('name')->get(['id', 'name']);

        return view('admin.upload-types.create', compact('departments'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:upload_types,name',
            'description' => 'nullable|string',
            'department_ids' => 'nullable|array',
            'department_ids.*' => 'integer|exists:departments,id',
        ]);

        $validated['requires_expiry'] = $request->boolean('requires_expiry');
        $validated['is_license_or_certification'] = $request->boolean('is_license_or_certification');
        $validated['department_ids'] = collect($request->input('department_ids', []))
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        UploadType::query()->create($validated);

        return redirect()
            ->route('admin.upload-types.index')
            ->with('success', 'Document type created successfully.');
    }

    public function edit(UploadType $uploadType): View
    {
        $departments = Department::query()->orderBy('name')->get(['id', 'name']);

        return view('admin.upload-types.edit', compact('uploadType', 'departments'));
    }

    public function update(Request $request, UploadType $uploadType): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:upload_types,name,' . $uploadType->id,
            'description' => 'nullable|string',
            'department_ids' => 'nullable|array',
            'department_ids.*' => 'integer|exists:departments,id',
        ]);

        $validated['requires_expiry'] = $request->boolean('requires_expiry');
        $validated['is_license_or_certification'] = $request->boolean('is_license_or_certification');
        $validated['department_ids'] = collect($request->input('department_ids', []))
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        $uploadType->update($validated);

        return redirect()
            ->route('admin.upload-types.index')
            ->with('success', 'Document type updated successfully.');
    }

    public function destroy(UploadType $uploadType): RedirectResponse
    {
        $user = auth()->user();
        if (! $user || ! method_exists($user, 'hasRole') || ! $user->hasRole(['admin', 'super-admin'])) {
            abort(403, 'Only admin roles can delete document types.');
        }

        try {
            $uploadType->delete();
        } catch (QueryException) {
            return redirect()
                ->route('admin.upload-types.index')
                ->with('error', 'Cannot delete this document type because it is in use.');
        }

        return redirect()
            ->route('admin.upload-types.index')
            ->with('success', 'Document type deleted successfully.');
    }
}
