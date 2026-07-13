@extends('layouts.member-portal')

@section('content')
<section class="mx-auto max-w-3xl px-4 py-8 sm:px-6">
    <h1 class="text-2xl font-black text-slate-900">{{ $portalPageTitle ?? 'Select a facility' }}</h1>
    <p class="mt-2 text-sm text-slate-600">{{ $facilitySelectLead ?? 'Choose a facility to open its dashboard — staff, compliance, and team priorities for your scope.' }}</p>

    @if(!empty($organizationStats))
    <div class="mt-4 grid gap-2 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-xl border border-slate-200 bg-white px-3 py-2.5 shadow-sm">
            <p class="text-[10px] font-bold uppercase tracking-wide text-slate-500">Total facilities</p>
            <p class="text-xl font-black text-slate-900">{{ $organizationStats['facilities_total'] ?? 0 }}</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white px-3 py-2.5 shadow-sm">
            <p class="text-[10px] font-bold uppercase tracking-wide text-slate-500">Active facilities</p>
            <p class="text-xl font-black text-slate-900">{{ $organizationStats['facilities_active'] ?? 0 }}</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white px-3 py-2.5 shadow-sm">
            <p class="text-[10px] font-bold uppercase tracking-wide text-slate-500">States covered</p>
            <p class="text-xl font-black text-slate-900">{{ $organizationStats['states_covered'] ?? 0 }}</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white px-3 py-2.5 shadow-sm">
            <p class="text-[10px] font-bold uppercase tracking-wide text-slate-500">Employees with assignments</p>
            <p class="text-xl font-black text-slate-900">{{ $organizationStats['employees_total'] ?? 0 }}</p>
        </div>
    </div>
    @endif

    @if($facilities->isEmpty())
    <p class="mt-6 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
        No facilities are available for your account.
    </p>
    @else
    <div class="mt-6 rounded-2xl border border-amber-200 bg-amber-50 p-4 shadow-sm ring-1 ring-amber-100/80">
        <p class="text-xs font-bold uppercase tracking-wide text-amber-900/70">Select a facility</p>
        <ul class="mt-3 divide-y divide-amber-100 rounded-xl border border-amber-100 bg-white shadow-sm">
        @foreach($facilities as $f)
        @php
            $facilityRouteKey = $f->slug ?? $f->id;
            $switchRoute = $facilitySwitchRoute ?? 'member.facility.dashboard';
            // Entry routes without {facility} (e.g. competencies) remember session then redirect themselves.
            $facilityDestination = \Illuminate\Support\Facades\Route::getRoutes()->getByName($switchRoute)
                && in_array('facility', \Illuminate\Support\Facades\Route::getRoutes()->getByName($switchRoute)->parameterNames(), true)
                ? route($switchRoute, ['facility' => $facilityRouteKey], false)
                : route($switchRoute, [], false);
        @endphp
        <li>
            <a href="{{ route('member.select-facility', ['facility' => $facilityRouteKey, 'redirect' => $facilityDestination]) }}"
               class="flex items-center justify-between px-4 py-3 text-sm font-semibold text-teal-700 hover:bg-amber-50/80">
                <span>{{ $f->name }}</span>
                <span class="text-teal-500">→</span>
            </a>
        </li>
        @endforeach
        </ul>
    </div>
    @endif
</section>
@endsection
