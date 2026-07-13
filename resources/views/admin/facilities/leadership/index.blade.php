@extends('layouts.dashboard')

@section('header')
<div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
    <div>
        <h1 class="text-xl font-bold text-slate-900">Facility leadership</h1>
        <p class="mt-1 text-sm text-slate-600">{{ !empty($canEdit) ? 'Manage leadership rosters by facility for the Facility Dashboard.' : 'View leadership rosters by facility.' }}</p>
    </div>
    <a href="{{ route('member.facility.dashboard') }}"
       class="inline-flex items-center rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50">
        <i class="fas fa-arrow-left mr-2"></i> Facility Dashboard
    </a>
</div>
@endsection

@section('content')
@if(session('success'))
<div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900">{{ session('success') }}</div>
@endif

<div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
    <table class="min-w-full divide-y divide-slate-200 text-sm">
        <thead class="bg-slate-50 text-left text-xs font-bold uppercase tracking-wide text-slate-500">
            <tr>
                <th class="px-4 py-3">Facility</th>
                <th class="px-4 py-3">Roles filled</th>
                <th class="px-4 py-3 text-right">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @forelse($facilities as $f)
            <tr class="hover:bg-slate-50/80">
                <td class="px-4 py-3 font-semibold text-slate-900">{{ $f->name }}</td>
                <td class="px-4 py-3 text-slate-600">{{ $filledCounts[$f->id] ?? 0 }}</td>
                <td class="px-4 py-3 text-right">
                    <a href="{{ route('admin.facility.leadership.edit', ['facility' => $f->slug ?? $f->id]) }}"
                       class="font-bold text-teal-700 hover:text-teal-900">{{ !empty($canEdit) ? 'Manage' : 'View' }} →</a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="3" class="px-4 py-8 text-center text-slate-500">No facilities available for your account.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
