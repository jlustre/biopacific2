@csrf
@if(($method ?? 'POST') !== 'POST')
    @method($method)
@endif

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
                <p class="mt-1 text-xs text-slate-500">Hold Ctrl while clicking to select multiple departments.</p>
                <p class="mt-1 text-xs text-slate-500">Leave empty to make this document type available to all departments.</p>
            </div>

            <label class="inline-flex items-center gap-2 text-sm font-semibold text-slate-700">
                <input type="checkbox" name="requires_expiry" value="1" {{ old('requires_expiry', $uploadType->requires_expiry ?? false) ? 'checked' : '' }} class="rounded border-slate-300 text-brand-600 focus:ring-brand-500">
                Requires expiry date
            </label>

            <label class="inline-flex items-center gap-2 text-sm font-semibold text-slate-700">
                <input type="checkbox" name="is_license_or_certification" value="1" {{ old('is_license_or_certification', $uploadType->is_license_or_certification ?? false) ? 'checked' : '' }} class="rounded border-slate-300 text-brand-600 focus:ring-brand-500">
                License / Certification
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
