@csrf
@if(($method ?? 'POST') !== 'POST')
    @method($method)
@endif

@php
    $employeeFileSections = $employeeFileSections ?? \App\Services\ChecklistUploadTypeSyncService::EMPLOYEE_FILE_SECTIONS;
    $docTypes = $docTypes ?? collect();
@endphp

<div class="space-y-6">
    <div class="rounded-2xl border border-slate-200 bg-white p-6">
        <h2 class="text-lg font-bold text-slate-900">Document Type Details</h2>
        <div class="mt-4 grid gap-4 md:grid-cols-2">
            <div class="md:col-span-2">
                <label for="name" class="mb-1 block text-sm font-semibold text-slate-700">Name</label>
                <input
                    type="text"
                    id="name"
                    name="name"
                    value="{{ old('name', $uploadType->name ?? '') }}"
                    required
                    class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none"
                >
                <p class="mt-1 text-xs text-slate-500">This exact name is used on Documents pages and Checklist PART A–D.</p>
            </div>

            <div class="md:col-span-2">
                <label for="description" class="mb-1 block text-sm font-semibold text-slate-700">Description</label>
                <textarea
                    id="description"
                    name="description"
                    rows="3"
                    class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none"
                >{{ old('description', $uploadType->description ?? '') }}</textarea>
            </div>

            <div>
                <label for="checklist_section" class="mb-1 block text-sm font-semibold text-slate-700">Checklist section</label>
                <select
                    id="checklist_section"
                    name="checklist_section"
                    class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none"
                >
                    <option value="">General (not on PART A–D)</option>
                    @foreach($employeeFileSections as $section)
                        <option value="{{ $section }}" @selected(old('checklist_section', $uploadType->checklist_section ?? '') === $section)>
                            {{ $section }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="doc_type_id" class="mb-1 block text-sm font-semibold text-slate-700">Part A category</label>
                <select
                    id="doc_type_id"
                    name="doc_type_id"
                    class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none"
                >
                    <option value="">None</option>
                    @foreach($docTypes as $docType)
                        <option value="{{ $docType->id }}" @selected((int) old('doc_type_id', $uploadType->doc_type_id ?? 0) === (int) $docType->id)>
                            {{ $docType->name }}
                        </option>
                    @endforeach
                </select>
                <p class="mt-1 text-xs text-slate-500">Used for PART A groupings (Applicant / Identifications / Verifications).</p>
            </div>

            <div>
                <label for="sort_order" class="mb-1 block text-sm font-semibold text-slate-700">Sort order</label>
                <input
                    type="number"
                    id="sort_order"
                    name="sort_order"
                    min="0"
                    value="{{ old('sort_order', $uploadType->sort_order ?? 0) }}"
                    class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none"
                >
            </div>

            <div class="md:col-span-2">
                <label for="department_ids" class="mb-1 block text-sm font-semibold text-slate-700">Departments</label>
                <select
                    id="department_ids"
                    name="department_ids[]"
                    multiple
                    class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none"
                >
                    @php
                        $selectedDepartments = collect(old('department_ids', $uploadType->department_ids ?? []))
                            ->map(fn ($id) => (int) $id)
                            ->all();
                    @endphp
                    @foreach($departments as $department)
                        <option value="{{ $department->id }}" {{ in_array((int) $department->id, $selectedDepartments, true) ? 'selected' : '' }}>
                            {{ $department->name }}
                        </option>
                    @endforeach
                </select>
                <p class="mt-1 text-xs text-slate-500">Hold Ctrl while clicking to select multiple departments. Leave empty for all departments.</p>
            </div>

            <label class="inline-flex items-center gap-2 text-sm font-semibold text-slate-700">
                <input type="checkbox" name="requires_expiry" value="1" {{ old('requires_expiry', $uploadType->requires_expiry ?? false) ? 'checked' : '' }} class="rounded border-slate-300 text-brand-600 focus:ring-brand-500">
                Requires expiry date
            </label>

            <label class="inline-flex items-center gap-2 text-sm font-semibold text-slate-700">
                <input type="checkbox" name="is_license_or_certification" value="1" {{ old('is_license_or_certification', $uploadType->is_license_or_certification ?? false) ? 'checked' : '' }} class="rounded border-slate-300 text-brand-600 focus:ring-brand-500">
                License / Certification
            </label>

            <label class="md:col-span-2 inline-flex items-start gap-2 text-sm font-semibold text-slate-700">
                <input type="checkbox" name="applies_to_all_positions" value="1" {{ old('applies_to_all_positions', $uploadType->applies_to_all_positions ?? false) ? 'checked' : '' }} class="mt-0.5 rounded border-slate-300 text-brand-600 focus:ring-brand-500">
                <span>
                    Required for all positions
                    <span class="block text-xs font-normal text-slate-500">When checked, this document is required for every employee. Otherwise assign it under Position requirements.</span>
                </span>
            </label>
        </div>
    </div>

    <div class="flex items-center justify-end gap-3">
        <a href="{{ route('admin.upload-types.index') }}" class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Cancel</a>
        <button type="submit" class="rounded-xl bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">
            {{ $submitLabel ?? 'Save' }}
        </button>
    </div>
</div>
