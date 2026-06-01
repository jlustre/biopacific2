@extends('layouts.dashboard')

@php
    $facilityKey = $facilityKey ?? ($facility->slug ?? $facility->id);
    $stats = $stats ?? [];
    $isDonDashboard = $isDonDashboard ?? false;
    $donDepartmentName = $donDepartmentName ?? null;
    $statusLabel = $facility->status ?? 'Active';
    $statusBadgeClass = match (strtolower((string) $statusLabel)) {
        'active', 'open' => 'bg-emerald-400/20 text-emerald-100 ring-emerald-400/30',
        'inactive', 'closed' => 'bg-rose-400/20 text-rose-100 ring-rose-400/30',
        default => 'bg-amber-400/20 text-amber-100 ring-amber-400/30',
    };
@endphp

@section('content')
<div class="w-full space-y-6 sm:space-y-8">
    {{-- Facility hero --}}
    <section class="overflow-hidden rounded-[1.75rem] bg-gradient-to-br from-teal-900 via-teal-800 to-teal-950 p-6 text-white shadow-lg sm:p-8">
        <div class="flex flex-col gap-6 lg:flex-row lg:items-start lg:justify-between">
            <div class="min-w-0 flex-1">
                <div class="inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1 text-xs font-semibold ring-1 ring-white/20">
                    <span class="h-2 w-2 rounded-full bg-emerald-400"></span>
                    Facility operations hub
                </div>
                <h2 class="mt-4 text-2xl font-bold tracking-tight sm:text-3xl lg:text-4xl">{{ $facility->name }}</h2>
                @if($facility->address)
                <p class="mt-2 flex items-start gap-2 text-sm text-teal-100 sm:text-base">
                    <i class="fas fa-map-marker-alt mt-1 shrink-0 opacity-80"></i>
                    <span>{{ $facility->address }}</span>
                </p>
                @endif
                <div class="mt-5 flex flex-wrap gap-3 text-sm">
                    @if($facility->phone)
                    <a href="tel:{{ preg_replace('/\D+/', '', $facility->phone) }}"
                       class="inline-flex items-center gap-2 rounded-xl bg-white/10 px-3 py-2 ring-1 ring-white/15 transition hover:bg-white/20">
                        <i class="fas fa-phone text-teal-200"></i>
                        <span>{{ $facility->phone }}</span>
                    </a>
                    @endif
                    @if($facility->email)
                    <a href="mailto:{{ $facility->email }}"
                       class="inline-flex items-center gap-2 rounded-xl bg-white/10 px-3 py-2 ring-1 ring-white/15 transition hover:bg-white/20">
                        <i class="fas fa-envelope text-teal-200"></i>
                        <span class="truncate max-w-[220px] sm:max-w-xs">{{ $facility->email }}</span>
                    </a>
                    @endif
                </div>
            </div>
            <div class="flex shrink-0 flex-wrap gap-3 lg:flex-col lg:items-end">
                <span class="inline-flex items-center rounded-2xl px-4 py-2 text-sm font-bold ring-1 {{ $statusBadgeClass }}">
                    {{ $statusLabel }}
                </span>
                <p class="text-xs text-teal-200/90 lg:text-right">{{ now()->format('l, F j, Y') }}</p>
            </div>
        </div>
    </section>

    {{-- At-a-glance metrics --}}
    @if($isDonDashboard)
        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
            <a href="{{ route('admin.facility.job_openings', ['facility' => $facilityKey]) }}"
               class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:border-teal-200 hover:shadow-md">
                <div class="flex items-center justify-between">
                    <p class="text-sm font-medium text-slate-500">Job openings</p>
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-teal-50 text-teal-600">
                        <i class="fas fa-briefcase"></i>
                    </span>
                </div>
                <p class="mt-3 text-3xl font-black text-slate-900">{{ $stats['job_openings'] ?? 0 }}</p>
                <p class="mt-1 text-xs text-slate-500">Department hiring pipeline</p>
            </a>
            <a href="{{ route('admin.facility.documents', ['facility' => $facilityKey]) }}"
               class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:border-rose-200 hover:shadow-md">
                <div class="flex items-center justify-between">
                    <p class="text-sm font-medium text-slate-500">Expiring licenses & certifications</p>
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-rose-50 text-rose-600">
                        <i class="fas fa-id-badge"></i>
                    </span>
                </div>
                <p class="mt-3 text-3xl font-black text-slate-900">{{ $stats['expiring_credentials_30'] ?? 0 }}</p>
                <p class="mt-1 text-xs text-slate-500">Due within 30 days</p>
            </a>
            <a href="{{ route('admin.facility.employees', ['facility' => $facilityKey, 'checklist' => 'partG']) }}"
               class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:border-amber-200 hover:shadow-md">
                <div class="flex items-center justify-between">
                    <p class="text-sm font-medium text-slate-500">Competencies due</p>
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-50 text-amber-600">
                        <i class="fas fa-star"></i>
                    </span>
                </div>
                <p class="mt-3 text-3xl font-black text-slate-900">{{ $stats['competencies_due_30'] ?? 0 }}</p>
                <p class="mt-1 text-xs text-slate-500">Due in the next 30 days</p>
            </a>
            <a href="{{ route('admin.facility.employees', ['facility' => $facilityKey, 'checklist' => 'partF']) }}"
               class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:border-indigo-200 hover:shadow-md">
                <div class="flex items-center justify-between">
                    <p class="text-sm font-medium text-slate-500">Performance appraisals due</p>
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600">
                        <i class="fas fa-chart-line"></i>
                    </span>
                </div>
                <p class="mt-3 text-3xl font-black text-slate-900">{{ $stats['performance_due_30'] ?? 0 }}</p>
                <p class="mt-1 text-xs text-slate-500">Due in the next 30 days</p>
            </a>
            <a href="{{ route('admin.facility.documents', ['facility' => $facilityKey]) }}"
               class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:border-cyan-200 hover:shadow-md">
                <div class="flex items-center justify-between">
                    <p class="text-sm font-medium text-slate-500">Department to-do</p>
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-cyan-50 text-cyan-600">
                        <i class="fas fa-tasks"></i>
                    </span>
                </div>
                <p class="mt-3 text-3xl font-black text-slate-900">{{ $stats['department_tasks'] ?? 0 }}</p>
                <p class="mt-1 text-xs text-slate-500">Pending document reviews</p>
            </a>
        </section>
    @else
        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <a href="{{ route('admin.facility.job_openings', ['facility' => $facilityKey]) }}"
               class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:border-teal-200 hover:shadow-md">
                <div class="flex items-center justify-between">
                    <p class="text-sm font-medium text-slate-500">Job openings</p>
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-teal-50 text-teal-600">
                        <i class="fas fa-briefcase"></i>
                    </span>
                </div>
                <p class="mt-3 text-3xl font-black text-slate-900">{{ $stats['job_openings'] ?? 0 }}</p>
                <p class="mt-1 text-xs text-slate-500">Active listings for this site</p>
            </a>
            <a href="{{ route('admin.news.index') }}"
               class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:border-teal-200 hover:shadow-md">
                <div class="flex items-center justify-between">
                    <p class="text-sm font-medium text-slate-500">News & updates</p>
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-teal-50 text-teal-600">
                        <i class="fas fa-newspaper"></i>
                    </span>
                </div>
                <p class="mt-3 text-3xl font-black text-slate-900">{{ $stats['news_items'] ?? 0 }}</p>
                <p class="mt-1 text-xs text-slate-500">Published or linked articles</p>
            </a>
            <a href="{{ route('admin.facilities.webcontents.testimonials') }}"
               class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:border-amber-200 hover:shadow-md">
                <div class="flex items-center justify-between">
                    <p class="text-sm font-medium text-slate-500">Testimonials</p>
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-50 text-amber-600">
                        <i class="fas fa-quote-right"></i>
                    </span>
                </div>
                <p class="mt-3 text-3xl font-black text-slate-900">{{ $stats['testimonials'] ?? 0 }}</p>
                <p class="mt-1 text-xs text-slate-500">On your public site</p>
            </a>
            <a href="{{ route('admin.galleries.index') }}"
               class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:border-violet-200 hover:shadow-md">
                <div class="flex items-center justify-between">
                    <p class="text-sm font-medium text-slate-500">Gallery images</p>
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-violet-50 text-violet-600">
                        <i class="fas fa-images"></i>
                    </span>
                </div>
                <p class="mt-3 text-3xl font-black text-slate-900">{{ $stats['gallery_images'] ?? 0 }}</p>
                <p class="mt-1 text-xs text-slate-500">Media assets uploaded</p>
            </a>
        </section>
    @endif

    {{-- HR & operations --}}
    <section>
        <div class="mb-4 flex flex-col gap-1 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h3 class="text-lg font-bold text-slate-900 sm:text-xl">HR & Operations</h3>
                <p class="text-sm text-slate-500">
                    {{ $isDonDashboard
                        ? 'Department-focused actions for staffing, evaluations, and credential compliance.'
                        : 'Manage staffing, hiring, credentials, and facility data.' }}
                </p>
            </div>
        </div>

        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
            @php
                $employeesBaseUrl = route('admin.facility.employees', ['facility' => $facilityKey]) . '?facility=' . $facility->id;
                if ($isDonDashboard) {
                    $actions = [
                        [
                            'route' => route('admin.facility.job_openings', ['facility' => $facilityKey]),
                            'icon' => 'fa-briefcase',
                            'label' => 'Openings',
                            'desc' => 'Track openings relevant to your department',
                            'tone' => 'teal',
                        ],
                        [
                            'route' => route('admin.facility.hiring', ['facility' => $facilityKey]),
                            'icon' => 'fa-user-plus',
                            'label' => 'Hiring',
                            'desc' => 'Review applicants and onboarding for your team',
                            'tone' => 'sky',
                        ],
                        [
                            'route' => $employeesBaseUrl,
                            'icon' => 'fa-users',
                            'label' => 'Employees',
                            'desc' => 'View roster plus performance and competency status',
                            'tone' => 'emerald',
                        ],
                        [
                            'route' => route('admin.facility.documents', ['facility' => $facilityKey]),
                            'icon' => 'fa-file-alt',
                            'label' => 'Credentials & documents',
                            'desc' => 'Review licenses, certifications, and expiring docs',
                            'tone' => 'cyan',
                        ],
                        [
                            'route' => route('admin.facility.reports', ['facility' => $facilityKey]),
                            'icon' => 'fa-chart-bar',
                            'label' => 'Reports',
                            'desc' => 'Review due items and compliance status',
                            'tone' => 'rose',
                        ],
                    ];
                } else {
                    $actions = [
                        [
                            'route' => route('admin.facility.job_openings', ['facility' => $facilityKey]),
                            'icon' => 'fa-briefcase',
                            'label' => 'Job listings',
                            'desc' => 'Post and manage open roles',
                            'tone' => 'teal',
                        ],
                        [
                            'route' => route('admin.facility.hiring', ['facility' => $facilityKey]),
                            'icon' => 'fa-user-plus',
                            'label' => 'Hiring',
                            'desc' => 'Review applicants and onboarding',
                            'tone' => 'sky',
                        ],
                        [
                            'route' => $employeesBaseUrl,
                            'icon' => 'fa-users',
                            'label' => 'Employees',
                            'desc' => 'Roster, orientation, performance & competencies evaluation',
                            'tone' => 'emerald',
                            'sublinks' => [
                                [
                                    'route' => $employeesBaseUrl . '&checklist=partE',
                                    'label' => 'Orientation',
                                    'icon' => 'fa-clipboard-check',
                                ],
                                [
                                    'route' => $employeesBaseUrl . '&checklist=partF',
                                    'label' => 'Performance',
                                    'icon' => 'fa-chart-line',
                                ],
                                [
                                    'route' => $employeesBaseUrl . '&checklist=partG',
                                    'label' => 'Competencies',
                                    'icon' => 'fa-star',
                                ],
                            ],
                        ],
                        [
                            'route' => route('admin.facility.documents', ['facility' => $facilityKey]),
                            'icon' => 'fa-file-alt',
                            'label' => 'Documents',
                            'desc' => 'Credentials and compliance files',
                            'tone' => 'cyan',
                        ],
                        [
                            'route' => route('admin.facility.reports', ['facility' => $facilityKey]),
                            'icon' => 'fa-chart-bar',
                            'label' => 'Reports',
                            'desc' => 'Export and review facility data',
                            'tone' => 'rose',
                        ],
                    ];
                }
                $toneMap = [
                    'teal' => [
                        'card' => 'border-teal-400/40 bg-gradient-to-br from-teal-500 via-teal-600 to-teal-900 shadow-teal-900/30 hover:shadow-teal-900/45',
                        'icon' => 'bg-white/20 text-white ring-white/25',
                        'title' => 'text-white',
                        'desc' => 'text-teal-100',
                        'arrow' => 'text-white',
                    ],
                    'sky' => [
                        'card' => 'border-sky-400/40 bg-gradient-to-br from-sky-500 via-sky-600 to-sky-900 shadow-sky-900/30 hover:shadow-sky-900/45',
                        'icon' => 'bg-white/20 text-white ring-white/25',
                        'title' => 'text-white',
                        'desc' => 'text-sky-100',
                        'arrow' => 'text-white',
                    ],
                    'emerald' => [
                        'card' => 'border-emerald-400/40 bg-gradient-to-br from-emerald-500 via-emerald-600 to-emerald-900 shadow-emerald-900/30 hover:shadow-emerald-900/45',
                        'icon' => 'bg-white/20 text-white ring-white/25',
                        'title' => 'text-white',
                        'desc' => 'text-emerald-100',
                        'arrow' => 'text-white',
                    ],
                    'cyan' => [
                        'card' => 'border-cyan-400/40 bg-gradient-to-br from-cyan-500 via-cyan-600 to-cyan-900 shadow-cyan-900/30 hover:shadow-cyan-900/45',
                        'icon' => 'bg-white/20 text-white ring-white/25',
                        'title' => 'text-white',
                        'desc' => 'text-cyan-100',
                        'arrow' => 'text-white',
                    ],
                    'rose' => [
                        'card' => 'border-rose-400/40 bg-gradient-to-br from-rose-500 via-rose-600 to-rose-900 shadow-rose-900/30 hover:shadow-rose-900/45',
                        'icon' => 'bg-white/20 text-white ring-white/25',
                        'title' => 'text-white',
                        'desc' => 'text-rose-100',
                        'arrow' => 'text-white',
                    ],
                    'amber' => [
                        'card' => 'border-amber-400/40 bg-gradient-to-br from-amber-500 via-amber-600 to-amber-900 shadow-amber-900/30 hover:shadow-amber-900/45',
                        'icon' => 'bg-white/20 text-white ring-white/25',
                        'title' => 'text-white',
                        'desc' => 'text-amber-100',
                        'arrow' => 'text-white',
                    ],
                ];
            @endphp

            @foreach($actions as $action)
                @php $t = $toneMap[$action['tone']]; @endphp
                <a href="{{ $action['route'] }}"
                   class="group relative flex min-h-[8.5rem] flex-col overflow-hidden rounded-2xl border p-4 shadow-lg ring-1 ring-white/10 transition duration-200 hover:-translate-y-1 hover:scale-[1.02] {{ $t['card'] }}">
                    <span class="pointer-events-none absolute -right-8 -top-8 h-28 w-28 rounded-full bg-white/10"></span>
                    <span class="pointer-events-none absolute -bottom-10 -left-6 h-24 w-24 rounded-full bg-black/10"></span>
                    <div class="relative flex items-center justify-between gap-3">
                        <div class="flex min-w-0 items-center gap-3">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl text-lg ring-1 {{ $t['icon'] }}">
                                <i class="fas {{ $action['icon'] }}"></i>
                            </span>
                            <h4 class="truncate text-base font-bold {{ $t['title'] }}">{{ $action['label'] }}</h4>
                        </div>
                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-white/15 opacity-0 ring-1 ring-white/20 transition group-hover:opacity-100">
                            <i class="fas fa-arrow-right text-xs {{ $t['arrow'] }}"></i>
                        </span>
                    </div>
                    <p class="relative mt-3 text-sm {{ $t['desc'] }}">{{ $action['desc'] }}</p>
                </a>
            @endforeach

            @unless($isDonDashboard)
                @php $tImport = $toneMap['amber']; @endphp
                <button type="button"
                    onclick="document.getElementById('importModal').classList.remove('hidden')"
                    class="group relative flex min-h-[8.5rem] flex-col overflow-hidden rounded-2xl border p-4 text-left shadow-lg ring-1 ring-white/10 transition duration-200 hover:-translate-y-1 hover:scale-[1.02] focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-300 {{ $tImport['card'] }}">
                    <span class="pointer-events-none absolute -right-8 -top-8 h-28 w-28 rounded-full bg-white/10"></span>
                    <div class="relative flex items-center justify-between gap-3">
                        <div class="flex min-w-0 items-center gap-3">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl text-lg ring-1 {{ $tImport['icon'] }}">
                                <i class="fas fa-file-import"></i>
                            </span>
                            <h4 class="truncate text-base font-bold {{ $tImport['title'] }}">Import files</h4>
                        </div>
                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-white/15 ring-1 ring-white/20">
                            <i class="fas fa-plus text-xs {{ $tImport['arrow'] }}"></i>
                        </span>
                    </div>
                    <p class="relative mt-3 text-sm {{ $tImport['desc'] }}">Upload spreadsheets and map columns</p>
                </button>
            @endunless
        </div>
    </section>

</div>

@include('admin.facilities.partials.import-mapping-modal')
@endsection
