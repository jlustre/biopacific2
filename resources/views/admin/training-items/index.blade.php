@extends('layouts.dashboard', ['title' => 'Training Configuration'])

@section('content')
@php
    $positionTitleById = $positions->keyBy('id');
@endphp
<div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
    <div>
        <h1 class="text-2xl font-black text-slate-900">Training Configuration</h1>
        <p class="mt-1 text-sm text-slate-500">Create and maintain training modules, then map which positions require each training (or assign trainings globally). Facility Trainings and Part H use this catalog to monitor completion.</p>
    </div>
    <div class="flex flex-wrap gap-2">
        @if(auth()->user()?->hasRole(['admin', 'super-admin']))
        <form method="POST" action="{{ route('admin.training-items.sync-seeder') }}"
              onsubmit="return confirm('Export every training module and its position assignments into database/seeders/data/employee_training_items.php?\n\nThis overwrites that file. Commit it to git so migrate:fresh --seed restores the current training catalog.');">
            @csrf
            <button type="submit"
                    class="inline-flex items-center justify-center rounded-xl border border-amber-300 bg-amber-50 px-4 py-2 text-sm font-semibold text-amber-900 hover:bg-amber-100">
                <i class="fa-solid fa-database mr-2"></i> Update seeder
            </button>
        </form>
        @endif
        <a href="{{ route('admin.training-items.create') }}" class="inline-flex items-center justify-center rounded-xl bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">
            + Add training module
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
        <input type="text" name="search" value="{{ $search }}" class="mt-1 rounded-lg border-slate-300 text-sm" placeholder="Training name…">
    </div>
    <div>
        <label class="block text-xs font-semibold text-slate-600">Frequency</label>
        <select name="frequency" class="mt-1 rounded-lg border-slate-300 text-sm">
            <option value="">All</option>
            @foreach(\App\Models\EmployeeTrainingItem::FREQUENCIES as $value => $meta)
                <option value="{{ $value }}" @selected($frequency === $value)>{{ $meta['short'] }}</option>
            @endforeach
        </select>
    </div>
    <button type="submit" class="rounded-lg bg-teal-600 px-4 py-2 text-sm font-semibold text-white hover:bg-teal-700">Filter</button>
</form>

<form method="POST" action="{{ route('admin.training-items.bulk-positions') }}" class="rounded-2xl border border-slate-200 bg-white shadow-sm">
    @csrf
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-3 py-2 text-left"><input type="checkbox" id="select-all-trainings" class="rounded border-slate-300"></th>
                    <th class="px-3 py-2 text-left font-semibold text-slate-600">Order</th>
                    <th class="px-3 py-2 text-left font-semibold text-slate-600">Training</th>
                    <th class="px-3 py-2 text-left font-semibold text-slate-600">Frequency</th>
                    <th class="px-3 py-2 text-left font-semibold text-slate-600">Positions</th>
                    <th class="px-3 py-2 text-left font-semibold text-slate-600">Active</th>
                    <th class="px-3 py-2 text-right font-semibold text-slate-600">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($items as $item)
                <tr>
                    <td class="px-3 py-2"><input type="checkbox" name="training_item_ids[]" value="{{ $item->id }}" class="training-row-check rounded border-slate-300"></td>
                    <td class="px-3 py-2">{{ $item->order }}</td>
                    <td class="px-3 py-2">
                        <div class="font-semibold text-slate-900">{{ $item->name }}</div>
                        @if($item->description)
                        <div class="text-xs text-slate-500">{{ \Illuminate\Support\Str::limit($item->description, 80) }}</div>
                        @endif
                        @if($item->content_url)
                        <a href="{{ $item->resolvedContentUrl() }}" target="_blank" rel="noopener noreferrer" class="mt-1 inline-block text-xs font-semibold text-sky-700 hover:text-sky-900">
                            Open module{{ $item->provider_label ? ' ('.$item->provider_label.')' : '' }}
                        </a>
                        @endif
                    </td>
                    <td class="px-3 py-2">
                        <span class="rounded-full px-2 py-0.5 text-[11px] font-semibold uppercase {{ $item->frequencyBadgeClass() }}">
                            {{ $item->frequencyShortLabel() }}
                        </span>
                    </td>
                    <td class="px-3 py-2 text-xs text-slate-600">
                        @if($item->appliesToEveryone())
                            All positions
                        @else
                            {{ collect($item->position_ids ?? [])->map(fn ($id) => $positionTitleById->get((int) $id)?->title ?? "#{$id}")->filter()->join(', ') }}
                        @endif
                    </td>
                    <td class="px-3 py-2">{{ $item->is_active ? 'Yes' : 'No' }}</td>
                    <td class="px-3 py-2 text-right whitespace-nowrap">
                        <a href="{{ route('admin.training-items.edit', $item) }}" class="font-semibold text-teal-700 hover:text-teal-900">Edit</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-4 py-8 text-center text-slate-500">No trainings configured yet. Add one to get started.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($items->isNotEmpty())
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
            <button type="submit" class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-black">Update selected</button>
        </div>
    </div>
    @endif
</form>

<div class="mt-4">{{ $items->links() }}</div>

<script>
document.getElementById('select-all-trainings')?.addEventListener('change', function (e) {
    document.querySelectorAll('.training-row-check').forEach(function (el) {
        el.checked = e.target.checked;
    });
});
</script>
@endsection
