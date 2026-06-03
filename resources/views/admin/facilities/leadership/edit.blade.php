@extends('layouts.dashboard')

@section('header')
<div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
    <div>
        <a href="{{ route('admin.facilities.leadership.index') }}"
           class="inline-flex items-center text-sm font-semibold text-teal-700 hover:text-teal-900">
            <i class="fas fa-arrow-left mr-2"></i> All facilities
        </a>
        <h1 class="mt-2 text-xl font-bold text-slate-900">{{ $facility->name }} — Leadership</h1>
        <p class="mt-1 text-sm text-slate-600">Highest rank first. Names appear on the Facility Dashboard leadership panel.</p>
    </div>
    <a href="{{ route('member.facility.dashboard', ['facility' => $facility->slug ?? $facility->id]) }}"
       class="inline-flex items-center rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50">
        View dashboard
    </a>
</div>
@endsection

@section('content')
@if(session('success'))
<div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900">{{ session('success') }}</div>
@endif

<form method="POST" action="{{ route('admin.facility.leadership.update', ['facility' => $facility->getRouteKey()]) }}" class="space-y-4">
    @csrf
    @method('PUT')

    <div class="rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-100 px-4 py-3">
            <h2 class="text-sm font-bold text-slate-900">Standard leadership roles</h2>
        </div>
        <div class="divide-y divide-slate-100">
            @foreach($rows as $row)
                @if($row['is_custom'])
                    @continue
                @endif
                <div class="grid grid-cols-1 gap-2 px-4 py-3 sm:grid-cols-[8.5rem_minmax(12rem,1fr)_minmax(11rem,16rem)] sm:items-center sm:gap-x-4">
                    <div class="min-w-0 sm:w-[8.5rem] shrink-0">
                        <span class="inline-flex max-w-full rounded bg-teal-50 px-2 py-0.5 text-[10px] font-black uppercase leading-tight text-teal-800 ring-1 ring-teal-100">{{ $row['abbrev'] }}</span>
                    </div>
                    <label class="min-w-0 text-sm font-semibold text-slate-700" for="leadership-{{ $row['role_key'] }}">{{ $row['role_label'] }}</label>
                    <div class="min-w-0">
                        <input type="text"
                               id="leadership-{{ $row['role_key'] }}"
                               name="leadership[{{ $row['role_key'] }}]"
                               value="{{ old('leadership.'.$row['role_key'], $row['name']) }}"
                               placeholder="Name (leave blank if vacant)"
                               class="w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-teal-500 focus:ring-teal-500">
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="rounded-xl border border-slate-200 bg-white shadow-sm" x-data="{ customRows: @js(collect($rows)->where('is_custom', true)->values()->map(fn ($r) => ['id' => $r['assignment_id'], 'role_label' => $r['role_label'], 'name' => $r['name']])->all() ?: [['id' => null, 'role_label' => '', 'name' => '']]) }">
        <div class="flex items-center justify-between border-b border-slate-100 px-4 py-3">
            <h2 class="text-sm font-bold text-slate-900">Additional roles</h2>
            <button type="button"
                    @click="customRows.push({ id: null, role_label: '', name: '' })"
                    class="rounded-lg bg-teal-600 px-3 py-1.5 text-xs font-bold text-white hover:bg-teal-700">
                + Add role
            </button>
        </div>
        <template x-for="(row, index) in customRows" :key="index">
            <div class="grid gap-2 border-t border-slate-100 px-4 py-3 sm:grid-cols-12 sm:items-center">
                <input type="hidden" :name="'custom_roles[' + index + '][id]'" :value="row.id">
                <div class="sm:col-span-5">
                    <input type="text"
                           :name="'custom_roles[' + index + '][role_label]'"
                           x-model="row.role_label"
                           placeholder="Role title (e.g. Consultant Pharmacist)"
                           class="w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-teal-500 focus:ring-teal-500">
                </div>
                <div class="sm:col-span-5">
                    <input type="text"
                           :name="'custom_roles[' + index + '][name]'"
                           x-model="row.name"
                           placeholder="Person name"
                           class="w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-teal-500 focus:ring-teal-500">
                </div>
                <div class="flex justify-end sm:col-span-2">
                    <button type="button"
                            @click="customRows.splice(index, 1)"
                            class="text-xs font-bold text-rose-600 hover:text-rose-800">Remove</button>
                </div>
            </div>
        </template>
        <p class="border-t border-slate-100 px-4 py-2 text-[11px] text-slate-500">Custom roles are appended after standard roles on the dashboard.</p>
    </div>

    <div class="flex flex-wrap gap-2">
        <button type="submit"
                class="rounded-lg bg-teal-600 px-5 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-teal-700">
            Save leadership roster
        </button>
        <a href="{{ route('admin.facilities.leadership.index') }}"
           class="rounded-lg border border-slate-200 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">Cancel</a>
    </div>
</form>

@endsection
