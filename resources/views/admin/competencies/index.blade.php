@extends('layouts.dashboard', ['title' => 'Competencies Management'])

@section('content')
@php
    $positionTitleById = $positions->keyBy('id');
@endphp
<div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
    <div>
        <h1 class="text-2xl font-black text-slate-900">Competencies Management</h1>
        <p class="mt-1 text-sm text-slate-500">Create and maintain Part G competency sections, manage checklist items inside each competency, and assign which positions require them.</p>
    </div>
    <div class="flex flex-wrap gap-2">
        @if(auth()->user()?->hasRole(['admin', 'super-admin']))
        <form method="POST" action="{{ route('admin.competencies.sync-seeder') }}"
              onsubmit="return confirm('Export every competency item and its position assignments into database/seeders/data/employee_competency_items.php?\n\nThis overwrites that file. Commit it to git so migrate:fresh --seed restores the current competency catalog.');">
            @csrf
            <button type="submit"
                    class="inline-flex items-center justify-center rounded-xl border border-amber-300 bg-amber-50 px-4 py-2 text-sm font-semibold text-amber-900 hover:bg-amber-100">
                <i class="fa-solid fa-database mr-2"></i> Update seeder
            </button>
        </form>
        @endif
        <a href="{{ route('admin.competencies.create') }}" class="inline-flex items-center justify-center rounded-xl bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">
            + Add competency
        </a>
    </div>
</div>

@if(session('success'))
<div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('success') }}</div>
@endif
@if(session('error'))
<div class="mb-4 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">{{ session('error') }}</div>
@endif

<form method="GET" class="mb-4 flex flex-wrap items-end gap-3 rounded-2xl border border-slate-200 bg-white p-4">
    <div>
        <label class="block text-xs font-semibold text-slate-600">Search</label>
        <input type="text" name="search" value="{{ $search }}" class="mt-1 rounded-lg border-slate-300 text-sm" placeholder="Competency or item…">
    </div>
    <button type="submit" class="rounded-lg bg-teal-600 px-4 py-2 text-sm font-semibold text-white hover:bg-teal-700">Filter</button>
    @if($search !== '')
        <a href="{{ route('admin.competencies.index') }}" class="rounded-lg bg-slate-100 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-200">Reset</a>
    @endif
</form>

<form method="POST" action="{{ route('admin.competencies.bulk-positions') }}" class="rounded-2xl border border-slate-200 bg-white shadow-sm">
    @csrf
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-3 py-2 text-left"><input type="checkbox" id="select-all-competencies" class="rounded border-slate-300"></th>
                    <th class="px-3 py-2 text-left font-semibold text-slate-600">Competency</th>
                    <th class="px-3 py-2 text-left font-semibold text-slate-600">Items</th>
                    <th class="px-3 py-2 text-left font-semibold text-slate-600">Positions</th>
                    <th class="px-3 py-2 text-right font-semibold text-slate-600">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($competencies as $competency)
                <tr>
                    <td class="px-3 py-2">
                        <input type="checkbox" name="section_keys[]" value="{{ $competency->section_key }}" class="competency-row-check rounded border-slate-300">
                    </td>
                    <td class="px-3 py-2">
                        <div class="font-semibold text-slate-900">{{ $competency->section }}</div>
                    </td>
                    <td class="px-3 py-2">{{ $competency->items_count }}</td>
                    <td class="px-3 py-2 text-xs text-slate-600">
                        @if($competency->applies_to_everyone)
                            All positions
                        @else
                            {{ collect($competency->position_ids ?? [])->map(fn ($id) => $positionTitleById->get((int) $id)?->title ?? "#{$id}")->filter()->join(', ') ?: 'None' }}
                        @endif
                    </td>
                    <td class="px-3 py-2 text-right whitespace-nowrap">
                        <a href="{{ route('admin.competencies.show', $competency->section_key) }}" class="font-semibold text-teal-700 hover:text-teal-900">Manage</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-4 py-8 text-center text-slate-500">No competencies configured yet. Add one to get started.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($competencies->isNotEmpty())
    <div class="flex flex-col gap-3 border-t border-slate-100 p-4 sm:flex-row sm:items-end sm:justify-between">
        <div class="flex flex-wrap gap-3">
            <label class="inline-flex items-center gap-2 text-sm"><input type="checkbox" name="apply_to_everyone" value="1" class="rounded border-slate-300"> Apply to everybody</label>
            <label class="inline-flex items-center gap-2 text-sm"><input type="checkbox" name="remove_from_everyone" value="1" class="rounded border-slate-300"> Remove from everybody</label>
        </div>
        <div class="flex flex-wrap items-end gap-2">
            <div>
                <label class="block text-xs font-semibold text-slate-600">Assign positions</label>
                <select name="position_ids[]" multiple class="mt-1 min-w-[14rem] rounded-lg border-slate-300 text-sm" size="4">
                    @foreach($positions as $position)
                        <option value="{{ $position->id }}">{{ $position->title }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">Update selected</button>
        </div>
    </div>
    @endif
</form>

<script>
(function () {
    var all = document.getElementById('select-all-competencies');
    if (!all) return;
    all.addEventListener('change', function () {
        document.querySelectorAll('.competency-row-check').forEach(function (cb) {
            cb.checked = all.checked;
        });
    });
})();
</script>
@endsection
