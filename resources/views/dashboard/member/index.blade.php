@extends('layouts.member-portal')

@php
    $personaLabel = $dashboardPersonaLabel ?? 'Team Member';
    $intro = $dashboardIntro ?? '';
    $kpis = $dashboardKpis ?? [];
    $myTasks = $dashboardMyTasks ?? [];
    $quickActions = $dashboardQuickActions ?? [];
    $requiredDocuments = $dashboardRequiredDocuments ?? [];
    $requiredDocumentsTotal = (int) ($dashboardRequiredDocumentsTotal ?? 0);
    $requiredDocumentsComplete = (int) ($dashboardRequiredDocumentsComplete ?? 0);
    $requiredDocumentsPositionTitle = $dashboardRequiredDocumentsPositionTitle ?? null;
    $requiredTrainings = $dashboardRequiredTrainings ?? [];
    $requiredTrainingsTotal = (int) ($dashboardRequiredTrainingsTotal ?? 0);
    $requiredTrainingsComplete = (int) ($dashboardRequiredTrainingsComplete ?? 0);
    $requiredTrainingsPositionTitle = $dashboardRequiredTrainingsPositionTitle ?? null;
    $requiredCredentials = $dashboardRequiredCredentials ?? [];
    $requiredCredentialsTotal = (int) ($dashboardRequiredCredentialsTotal ?? 0);
    $requiredCredentialsValid = (int) ($dashboardRequiredCredentialsValid ?? 0);
    $requiredCredentialsPositionTitle = $dashboardRequiredCredentialsPositionTitle ?? null;
    $toneMap = [
        'brand' => ['bg' => 'bg-teal-50', 'text' => 'text-teal-700', 'ring' => 'ring-teal-100'],
        'amber' => ['bg' => 'bg-amber-50', 'text' => 'text-amber-700', 'ring' => 'ring-amber-100'],
        'rose' => ['bg' => 'bg-rose-50', 'text' => 'text-rose-700', 'ring' => 'ring-rose-100'],
        'teal' => ['bg' => 'bg-slate-50', 'text' => 'text-slate-700', 'ring' => 'ring-slate-200'],
    ];
@endphp

@section('content')
<section class="mx-auto max-w-6xl px-4 py-4 sm:px-6 lg:py-5">
    <div class="flex flex-wrap items-start justify-between gap-3 rounded-2xl border border-slate-200 bg-white px-4 py-3 shadow-sm sm:px-5">
        <div class="min-w-0">
            <p class="text-[11px] font-bold uppercase tracking-wide text-slate-500">{{ $todayLabel ?? now()->format('l, M j') }}</p>
            <h1 class="mt-0.5 text-lg font-black text-slate-900 sm:text-xl">My Dashboard</h1>
            <p class="mt-1 max-w-2xl text-xs leading-relaxed text-slate-600 sm:text-sm">{{ $intro }}</p>
        </div>
        <div class="flex flex-wrap items-center gap-2 text-xs">
            <span class="rounded-full bg-teal-50 px-2.5 py-1 font-bold text-teal-800">{{ $personaLabel }}</span>
            <a href="{{ route('settings.profile') }}"
               class="rounded-full bg-teal-600 px-3 py-1.5 text-xs font-bold text-white shadow-sm transition hover:bg-teal-700 hover:shadow">My Profile</a>
        </div>
    </div>

    <div class="mt-3">
        <h2 class="sr-only">My stats</h2>
        <div class="grid gap-2 sm:grid-cols-2 lg:grid-cols-4">
            @foreach($kpis as $card)
                @php $tone = $toneMap[$card['tone'] ?? 'brand'] ?? $toneMap['brand']; @endphp
                <a href="{{ $card['route'] ?? '#' }}"
                   class="flex items-center gap-3 rounded-xl border border-slate-200 bg-white px-3 py-2.5 shadow-sm transition hover:border-teal-300 hover:shadow">
                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg {{ $tone['bg'] }} {{ $tone['text'] }} ring-1 {{ $tone['ring'] }}">
                        <i class="fa-solid {{ $card['icon'] ?? 'fa-chart-simple' }} text-sm"></i>
                    </span>
                    <div class="min-w-0">
                        <p class="text-[10px] font-bold uppercase tracking-wide text-slate-500">{{ $card['label'] }}</p>
                        <p class="text-xl font-black leading-none text-slate-900">{{ $card['value'] }}</p>
                        <p class="truncate text-[11px] text-slate-500">{{ $card['hint'] }}</p>
                    </div>
                </a>
            @endforeach
        </div>
    </div>

    <div class="mt-3 grid gap-3 lg:grid-cols-12">
        <div class="lg:col-span-7">
            <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="flex items-start justify-between gap-2 border-b border-slate-100 px-4 py-2.5">
                    <div class="min-w-0">
                        <h2 class="text-sm font-black text-slate-900">My tasks</h2>
                        <p class="text-[11px] text-slate-500">Assigned reviews, profile items, uploads, certifications, and checklist work for your role</p>
                    </div>
                    <a href="{{ route('member.tasks') }}"
                       class="shrink-0 rounded-lg border border-teal-200 bg-teal-50 px-2.5 py-1 text-[11px] font-bold text-teal-800 transition hover:bg-teal-100">
                        View all
                    </a>
                </div>
                @include('dashboard.member.partials.my-tasks-list', ['myTasks' => $myTasks])
            </div>
        </div>

        <aside class="lg:col-span-5">
            <div class="rounded-2xl border border-slate-200 bg-white p-3 shadow-sm">
                <h3 class="text-xs font-black uppercase tracking-wide text-slate-500">Work center</h3>
                <div class="mt-2 grid gap-1.5 sm:grid-cols-2 lg:grid-cols-1">
                    @foreach($quickActions as $action)
                    <a href="{{ $action['route'] }}" class="flex items-center gap-2.5 rounded-lg border border-slate-100 px-2.5 py-2 hover:border-teal-200 hover:bg-teal-50/50">
                        <i class="fa-solid {{ $action['icon'] }} text-teal-700"></i>
                        <span>
                            <span class="block text-xs font-bold text-slate-900">{{ $action['title'] }}</span>
                            <span class="block text-[10px] text-slate-500">{{ $action['subtitle'] }}</span>
                        </span>
                    </a>
                    @endforeach
                </div>
            </div>
            <p class="mt-2 rounded-xl border border-dashed border-slate-200 bg-slate-50 px-3 py-2 text-[11px] leading-relaxed text-slate-600">
                Employee ID, department, hire date, and account settings are on
                <a href="{{ route('settings.profile') }}" class="font-bold text-teal-700 hover:underline">My Profile</a>.
            </p>
        </aside>
    </div>

    @if($requiredDocumentsTotal > 0)
        <div class="mt-3 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm"
             x-data="{ expanded: sessionStorage.getItem('dashboard.requiredDocuments.expanded') === 'true', toggle() { this.expanded = !this.expanded; sessionStorage.setItem('dashboard.requiredDocuments.expanded', String(this.expanded)); } }">
            <div class="flex flex-col gap-2 border-b border-slate-100 px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-sm font-black text-slate-900">Required documents for your position</h2>
                    <p class="text-[11px] text-slate-500">
                        @if($requiredDocumentsPositionTitle)
                            {{ $requiredDocumentsPositionTitle }} ·
                        @endif
                        {{ $requiredDocumentsComplete }} of {{ $requiredDocumentsTotal }} approved
                    </p>
                </div>
                <div class="flex self-start items-center gap-2">
                    <a href="{{ route('member.documents') }}"
                       class="rounded-lg border border-teal-200 bg-teal-50 px-3 py-1.5 text-[11px] font-bold text-teal-800 hover:bg-teal-100">
                        Open My Documents
                    </a>
                    <button type="button" @click="toggle()" :aria-expanded="expanded"
                            class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 px-3 py-1.5 text-[11px] font-bold text-slate-600 hover:bg-slate-50">
                        <span x-text="expanded ? 'Collapse' : 'Expand'"></span>
                        <i class="fa-solid fa-chevron-down transition-transform" :class="{ 'rotate-180': expanded }"></i>
                    </button>
                </div>
            </div>

            <div x-show="expanded" x-cloak>
                @if(count($requiredDocuments) === 0)
                    <p class="px-4 py-4 text-sm font-semibold text-emerald-700">
                        <i class="fa-solid fa-circle-check mr-1"></i> All required position documents are approved and current.
                    </p>
                @else
                    <div class="divide-y divide-slate-100">
                        @foreach($requiredDocuments as $document)
                        @php
                            $status = $document['status'] ?? 'missing';
                            $statusClass = match ($status) {
                                'pending_review' => 'bg-sky-50 text-sky-700',
                                'expired' => 'bg-amber-50 text-amber-800',
                                'rejected' => 'bg-rose-100 text-rose-800',
                                default => 'bg-rose-50 text-rose-700',
                            };
                            $uploadUrl = route('member.documents', array_filter([
                                'upload_type_id' => $document['upload_type_id'] ?? null,
                            ]));
                        @endphp
                        <div class="flex flex-col gap-2 px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
                            <div class="min-w-0">
                                <p class="truncate text-sm font-bold text-slate-900">{{ $document['title'] ?? 'Required document' }}</p>
                                @if(!empty($document['verification_notes']))
                                    <p class="mt-0.5 text-xs text-rose-700">{{ $document['verification_notes'] }}</p>
                                @endif
                            </div>
                            <div class="flex shrink-0 items-center gap-2">
                                <span class="rounded-full px-2.5 py-1 text-[10px] font-bold uppercase tracking-wide {{ $statusClass }}">
                                    {{ $document['status_label'] ?? 'Action required' }}
                                </span>
                                @if($status === 'pending_review')
                                    <span class="rounded-lg border border-slate-200 px-2.5 py-1 text-[11px] font-semibold text-slate-400">Awaiting approval</span>
                                @else
                                    <a href="{{ $uploadUrl }}" class="rounded-lg bg-teal-600 px-2.5 py-1 text-[11px] font-bold text-white hover:bg-teal-700">
                                        {{ $status === 'rejected' ? 'Re-upload' : 'Upload' }}
                                    </a>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    @endif

    @if($requiredTrainingsTotal > 0)
        <div class="mt-3 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm"
             x-data="{ expanded: sessionStorage.getItem('dashboard.requiredTrainings.expanded') === 'true', toggle() { this.expanded = !this.expanded; sessionStorage.setItem('dashboard.requiredTrainings.expanded', String(this.expanded)); } }">
            <div class="flex flex-col gap-2 border-b border-slate-100 px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-sm font-black text-slate-900">Required training for your position</h2>
                    <p class="text-[11px] text-slate-500">
                        @if($requiredTrainingsPositionTitle)
                            {{ $requiredTrainingsPositionTitle }} ·
                        @endif
                        {{ $requiredTrainingsComplete }} of {{ $requiredTrainingsTotal }} completed
                    </p>
                </div>
                <div class="flex self-start items-center gap-2">
                    <a href="{{ route('member.checklists') }}"
                       class="rounded-lg border border-amber-200 bg-amber-50 px-3 py-1.5 text-[11px] font-bold text-amber-800 hover:bg-amber-100">
                        Open My Checklists
                    </a>
                    <button type="button" @click="toggle()" :aria-expanded="expanded"
                            class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 px-3 py-1.5 text-[11px] font-bold text-slate-600 hover:bg-slate-50">
                        <span x-text="expanded ? 'Collapse' : 'Expand'"></span>
                        <i class="fa-solid fa-chevron-down transition-transform" :class="{ 'rotate-180': expanded }"></i>
                    </button>
                </div>
            </div>

            <div x-show="expanded" x-cloak>
                @if($requiredTrainingsComplete === $requiredTrainingsTotal && count($requiredTrainings) === 0)
                    <p class="px-4 py-4 text-sm font-semibold text-emerald-700">
                        <i class="fa-solid fa-circle-check mr-1"></i> All required position training is complete and current.
                    </p>
                @else
                    <div class="divide-y divide-slate-100">
                        @foreach($requiredTrainings as $training)
                        @php
                            $status = $training['status'] ?? 'not_started';
                            $isCompleted = $status === 'completed';
                            $statusClass = $training['badge_class'] ?? match ($status) {
                                'completed' => 'bg-emerald-50 text-emerald-800',
                                'submitted', 'pending_signature' => 'bg-sky-50 text-sky-700',
                                'in_progress' => 'bg-amber-50 text-amber-800',
                                'overdue', 'rejected' => 'bg-rose-100 text-rose-800',
                                default => 'bg-slate-100 text-slate-700',
                            };
                            $trainingUrl = !empty($training['action_url']) && $training['action_url'] !== '#'
                                ? $training['action_url']
                                : route('member.checklists');
                        @endphp
                        <div class="flex flex-col gap-2 px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
                            <div class="min-w-0">
                                <p class="truncate text-sm font-bold text-slate-900">{{ $training['title'] ?? 'Required training' }}</p>
                                @if(!empty($training['subtitle']))
                                    <p class="mt-0.5 line-clamp-2 text-xs text-slate-500">{{ $training['subtitle'] }}</p>
                                @endif
                                @if(!empty($training['rejection_reason']))
                                    <p class="mt-0.5 text-xs text-rose-700">{{ $training['rejection_reason'] }}</p>
                                @endif
                            </div>
                            <div class="flex shrink-0 items-center gap-2">
                                <span class="rounded-full px-2.5 py-1 text-[10px] font-bold uppercase tracking-wide {{ $statusClass }}">
                                    {{ $training['status_label'] ?? 'Not started' }}
                                </span>
                                @if($isCompleted)
                                    <span class="rounded-lg border border-emerald-200 bg-emerald-50 px-2.5 py-1 text-[11px] font-semibold text-emerald-800">
                                        <i class="fa-solid fa-check mr-1"></i>Completed
                                    </span>
                                @elseif(in_array($status, ['submitted', 'pending_signature'], true))
                                    <span class="rounded-lg border border-slate-200 px-2.5 py-1 text-[11px] font-semibold text-slate-400">Awaiting review</span>
                                @else
                                    <a href="{{ $trainingUrl }}" class="rounded-lg bg-amber-600 px-2.5 py-1 text-[11px] font-bold text-white hover:bg-amber-700">
                                        {{ $status === 'rejected' ? 'Revise' : (($training['can_start'] ?? false) ? 'Start' : 'Continue') }}
                                    </a>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    @endif

    @if($requiredCredentialsTotal > 0)
        <div class="mt-3 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm"
             x-data="{ expanded: sessionStorage.getItem('dashboard.requiredCredentials.expanded') === 'true', toggle() { this.expanded = !this.expanded; sessionStorage.setItem('dashboard.requiredCredentials.expanded', String(this.expanded)); } }">
            <div class="flex flex-col gap-2 border-b border-slate-100 px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-sm font-black text-slate-900">Required licenses &amp; certifications</h2>
                    <p class="text-[11px] text-slate-500">
                        @if($requiredCredentialsPositionTitle)
                            {{ $requiredCredentialsPositionTitle }} ·
                        @endif
                        {{ $requiredCredentialsValid }} of {{ $requiredCredentialsTotal }} valid
                    </p>
                </div>
                <div class="flex self-start items-center gap-2">
                    <a href="{{ route('member.certifications') }}"
                       class="rounded-lg border border-violet-200 bg-violet-50 px-3 py-1.5 text-[11px] font-bold text-violet-800 hover:bg-violet-100">
                        Open My Credentials
                    </a>
                    <button type="button" @click="toggle()" :aria-expanded="expanded"
                            class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 px-3 py-1.5 text-[11px] font-bold text-slate-600 hover:bg-slate-50">
                        <span x-text="expanded ? 'Collapse' : 'Expand'"></span>
                        <i class="fa-solid fa-chevron-down transition-transform" :class="{ 'rotate-180': expanded }"></i>
                    </button>
                </div>
            </div>

            <div x-show="expanded" x-cloak>
                @if(count($requiredCredentials) === 0)
                    <p class="px-4 py-4 text-sm font-semibold text-emerald-700">
                        <i class="fa-solid fa-circle-check mr-1"></i> All required licenses and certifications are valid.
                    </p>
                @else
                    <div class="divide-y divide-slate-100">
                        @foreach($requiredCredentials as $credential)
                        @php
                            $status = $credential['status'] ?? 'not_on_file';
                            $statusClass = $credential['badge_class'] ?? match ($status) {
                                'expired', 'expires_today', 'expiring_urgent', 'rejected' => 'bg-rose-100 text-rose-800',
                                'expiring_soon', 'missing_expiry' => 'bg-amber-50 text-amber-800',
                                'not_verified' => 'bg-sky-50 text-sky-700',
                                default => 'bg-slate-100 text-slate-700',
                            };
                            $credentialUrl = route('member.certifications', array_filter([
                                'upload_type_id' => $credential['upload_type_id'] ?? null,
                            ]));
                            $pendingCredential = $status === 'not_verified';
                            $renewalCredential = in_array($status, ['expired', 'expires_today', 'expiring_urgent', 'expiring_soon', 'missing_expiry'], true);
                        @endphp
                        <div class="flex flex-col gap-2 px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
                            <div class="min-w-0">
                                <p class="truncate text-sm font-bold text-slate-900">{{ $credential['title'] ?? 'Required credential' }}</p>
                                <p class="mt-0.5 text-xs text-slate-500">
                                    {{ $credential['doc_type'] ?? 'License or certification' }}
                                    @if(!empty($credential['exp_dt_formatted']))
                                        · Expires {{ $credential['exp_dt_formatted'] }}
                                    @endif
                                </p>
                                @if(!empty($credential['verification_notes']))
                                    <p class="mt-0.5 text-xs text-rose-700">{{ $credential['verification_notes'] }}</p>
                                @endif
                            </div>
                            <div class="flex shrink-0 items-center gap-2">
                                <span class="rounded-full px-2.5 py-1 text-[10px] font-bold uppercase tracking-wide {{ $statusClass }}">
                                    {{ $credential['status_label'] ?? 'Action required' }}
                                </span>
                                @if($pendingCredential)
                                    <span class="rounded-lg border border-slate-200 px-2.5 py-1 text-[11px] font-semibold text-slate-400">Awaiting approval</span>
                                @else
                                    <a href="{{ $credentialUrl }}" class="rounded-lg bg-violet-600 px-2.5 py-1 text-[11px] font-bold text-white hover:bg-violet-700">
                                        {{ $status === 'rejected' ? 'Re-upload' : ($renewalCredential ? 'Renew' : 'Upload') }}
                                    </a>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    @endif
</section>
@endsection
