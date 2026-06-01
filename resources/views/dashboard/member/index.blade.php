@extends('layouts.member-portal')

@php
    $newsEventsCount = $newsEventsCount ?? 0;
    $todayLabel = $todayLabel ?? now()->format('l, F j, Y');
    $complianceScore = $stats['employee_file_verified'] ?? null;
    $documentsNeededCount = $stats['documents_needed'] ?? 0;
    $showFacilityOversight = $showFacilityOversight ?? false;
    $dashboardPersona = $dashboardPersona ?? 'employee-default';
    $dashboardPersonaLabel = $dashboardPersonaLabel ?? 'Employee';
    $dashboardIntro = $dashboardIntro ?? 'Dashboard focus: Employee';
    $overviewCards = $overviewCards ?? [];
    $personalActions = $personalActions ?? [];
    $facilityActions = $facilityActions ?? [];
    $helpfulLinks = $helpfulLinks ?? [];
@endphp

@section('content')
<section class="px-4 py-6 sm:px-6 lg:px-8">
    <div class="grid gap-6 xl:grid-cols-12">
        <div class="relative overflow-hidden rounded-[2rem] bg-teal-700 text-white shadow-soft xl:col-span-8">
            <div class="pointer-events-none absolute -right-20 -top-20 h-64 w-64 rounded-full bg-teal-600/40" aria-hidden="true"></div>
            <div class="pointer-events-none absolute -bottom-24 -left-12 h-56 w-56 rounded-full bg-teal-800/50" aria-hidden="true"></div>
            <div class="relative z-10 p-6 sm:p-8">
                <div class="inline-flex rounded-full bg-white/15 px-3 py-1 text-xs font-semibold ring-1 ring-white/20">Today • {{ $todayLabel }}</div>
                <h2 class="mt-5 text-3xl font-black tracking-tight sm:text-4xl">Welcome, {{ $displayName }}</h2>
                <p class="mt-3 max-w-3xl text-teal-50">
                    {{ $dashboardIntro }}
                </p>

                <div class="mt-6 grid gap-3 sm:grid-cols-3">
                    <div class="rounded-2xl bg-white/10 p-4 ring-1 ring-white/20">
                        <p class="text-xs uppercase tracking-wide text-teal-100">Role</p>
                        <p class="mt-1 text-lg font-bold">{{ $dashboardPersonaLabel }}</p>
                    </div>
                    <div class="rounded-2xl bg-white/10 p-4 ring-1 ring-white/20">
                        <p class="text-xs uppercase tracking-wide text-teal-100">Assigned Facility</p>
                        <p class="mt-1 text-lg font-bold">{{ $facilityName ?: 'Unassigned' }}</p>
                    </div>
                    <div class="rounded-2xl bg-white/10 p-4 ring-1 ring-white/20">
                        <p class="text-xs uppercase tracking-wide text-teal-100">File Compliance</p>
                        <p class="mt-1 text-lg font-bold">{{ $complianceScore !== null ? $complianceScore . '%' : '—' }}</p>
                    </div>
                </div>
            </div>
        </div>

        <section id="profile" class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-card xl:col-span-4">
            <div class="flex items-center gap-4">
                <div class="flex h-20 w-20 items-center justify-center rounded-3xl bg-brand-100 text-2xl font-black text-brand-800">{{ $initials }}</div>
                <div>
                    <h2 class="text-xl font-black text-slate-950">{{ $displayName }}</h2>
                    <p class="text-sm text-slate-500">{{ $positionTitle }}</p>
                    <span class="mt-2 inline-flex rounded-full bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-700">Active</span>
                </div>
            </div>
            <div class="mt-6 grid gap-3 text-sm">
                <div class="flex justify-between border-b border-slate-100 pb-3"><span class="text-slate-500">Employee ID</span><span class="font-bold">{{ $employeeId }}</span></div>
                <div class="flex justify-between border-b border-slate-100 pb-3"><span class="text-slate-500">Department</span><span class="font-bold">{{ $departmentName ?: '—' }}</span></div>
                <div class="flex justify-between border-b border-slate-100 pb-3"><span class="text-slate-500">Facility</span><span class="font-bold">{{ $facilityName ?: '—' }}</span></div>
                <div class="flex justify-between"><span class="text-slate-500">Hire Date</span><span class="font-bold">{{ $hireDate }}</span></div>
            </div>
            <a href="{{ route('settings.profile') }}" class="mt-6 block w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-center text-sm font-bold hover:bg-slate-50">View Profile</a>
        </section>
    </div>

    <div class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach($overviewCards as $card)
            @php
                $toneMap = [
                    'brand' => ['bg' => 'bg-brand-50', 'text' => 'text-brand-700'],
                    'amber' => ['bg' => 'bg-amber-50', 'text' => 'text-amber-700'],
                    'rose' => ['bg' => 'bg-rose-50', 'text' => 'text-rose-700'],
                    'teal' => ['bg' => 'bg-teal-50', 'text' => 'text-teal-700'],
                ];
                $tone = $toneMap[$card['tone']] ?? $toneMap['brand'];
            @endphp
            <a href="{{ $card['route'] }}" class="group block rounded-3xl border border-slate-200 bg-white p-5 shadow-card transition hover:border-brand-300 hover:shadow-md">
                <div class="flex items-center justify-between">
                    <p class="text-sm font-medium text-slate-500">{{ $card['label'] }}</p>
                    <span class="rounded-xl {{ $tone['bg'] }} p-2 {{ $tone['text'] }}"><i class="fa-solid {{ $card['icon'] }}"></i></span>
                </div>
                <p class="mt-4 text-3xl font-black text-slate-950 group-hover:text-brand-700">{{ $card['value'] }}</p>
                <p class="text-sm font-semibold text-slate-500">{{ $card['hint'] }}</p>
            </a>
        @endforeach
    </div>

    <div class="mt-6 grid gap-6 xl:grid-cols-12">
        <div class="space-y-6 xl:col-span-8">
            <section class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-card">
                <div class="mb-5 flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-bold text-slate-950">My Work Center</h2>
                        <p class="text-sm text-slate-500">Tools currently available in the portal</p>
                    </div>
                </div>
                <div class="grid gap-3 sm:grid-cols-2">
                    @foreach($personalActions as $action)
                        <a href="{{ $action['route'] }}" class="rounded-2xl border border-slate-200 bg-slate-50 p-4 text-left transition hover:border-brand-300 hover:bg-brand-50">
                            <span class="text-2xl text-brand-600"><i class="fa-solid {{ $action['icon'] }}"></i></span>
                            <p class="mt-3 font-bold text-slate-950">{{ $action['title'] }}</p>
                            <p class="text-xs text-slate-500">{{ $action['subtitle'] }}</p>
                        </a>
                    @endforeach
                </div>
            </section>

            @if($showFacilityOversight)
                <section class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-card">
                    <div class="mb-5 flex items-center justify-between">
                        <div>
                            <h2 class="text-lg font-bold text-slate-950">Facility Oversight</h2>
                            <p class="text-sm text-slate-500">{{ $dashboardPersonaLabel }} actions for {{ $facilityName ?: 'your facility' }}</p>
                        </div>
                        <span class="rounded-full bg-teal-50 px-3 py-1 text-xs font-bold text-teal-700">{{ $dashboardPersonaLabel }}</span>
                    </div>
                    <div class="grid gap-3 sm:grid-cols-2">
                        @foreach($facilityActions as $action)
                            <a href="{{ $action['route'] }}" class="rounded-2xl border border-teal-100 bg-teal-50/50 p-4 text-left transition hover:border-teal-300 hover:bg-teal-50">
                                <span class="text-2xl text-teal-700"><i class="fa-solid {{ $action['icon'] }}"></i></span>
                                <p class="mt-3 font-bold text-slate-950">{{ $action['title'] }}</p>
                                <p class="text-xs text-slate-600">{{ $action['subtitle'] }}</p>
                            </a>
                        @endforeach
                    </div>
                </section>
            @endif
        </div>

        <aside class="space-y-6 xl:col-span-4">
            <section class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-card">
                <div class="mb-5 flex items-center justify-between">
                    <h2 class="text-lg font-bold text-slate-950">Current Priorities</h2>
                    <span class="text-xs font-bold text-slate-500">Action list</span>
                </div>

                <div class="space-y-3 text-sm">
                    <div class="flex items-start gap-3 rounded-2xl bg-slate-50 p-4">
                        <span class="mt-0.5 text-brand-600"><i class="fa-solid fa-circle-check"></i></span>
                        <p class="text-slate-700">
                            @if($documentsNeededCount > 0)
                                {{ $documentsNeededCount }} document {{ \Illuminate\Support\Str::plural('item', $documentsNeededCount) }} need your review.
                            @else
                                Document checklist is currently up to date.
                            @endif
                        </p>
                    </div>
                    <div class="flex items-start gap-3 rounded-2xl bg-slate-50 p-4">
                        <span class="mt-0.5 text-amber-600"><i class="fa-solid fa-user-pen"></i></span>
                        <p class="text-slate-700">
                            {{ $stats['trainings_pending_signature'] ?? 0 }} training {{ \Illuminate\Support\Str::plural('item', $stats['trainings_pending_signature'] ?? 0) }} pending signature.
                        </p>
                    </div>
                    <div class="flex items-start gap-3 rounded-2xl bg-slate-50 p-4">
                        <span class="mt-0.5 text-rose-600"><i class="fa-solid fa-shield"></i></span>
                        <p class="text-slate-700">
                            {{ ($stats['certifications_expiring'] ?? 0) + ($stats['certifications_expired'] ?? 0) }} certification {{ \Illuminate\Support\Str::plural('item', ($stats['certifications_expiring'] ?? 0) + ($stats['certifications_expired'] ?? 0)) }} need follow-up.
                        </p>
                    </div>
                </div>
            </section>

            <section class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-card">
                <h2 class="text-lg font-bold text-slate-950">Helpful Links</h2>
                <div class="mt-4 space-y-3 text-sm">
                    @foreach($helpfulLinks as $link)
                        <a href="{{ $link['route'] }}" class="block rounded-xl bg-slate-50 px-4 py-3 font-semibold text-slate-700 hover:bg-slate-100">{{ $link['label'] }}</a>
                    @endforeach
                </div>
            </section>
        </aside>
    </div>
</section>
@endsection
