@php
    $certificationsCenter = $certificationsCenter ?? [];
    $facilityReport = $facilityCertificationsReport ?? null;
    $showFacilityTab = ($isFacilityCertificationsAdmin ?? false) && !empty($facilityReport);
    $items = $certificationsCenter['items'] ?? [];
    $expiringUploads = $certificationsCenter['expiring_uploads'] ?? [];
    $summary = $certificationsCenter['summary'] ?? ($stats ?? []);
    $hasEmployeeRecord = $certificationsCenter['has_employee_record'] ?? false;
    $employmentPortalUrl = \Illuminate\Support\Facades\Route::has('employment.portal') ? route('employment.portal') : '#';
@endphp

<section id="certifications" class="overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-card" x-data="{ tab: 'mine', facilityFilter: '' }">
    <div class="border-b border-slate-200 bg-teal-50 p-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <div class="inline-flex items-center gap-2 rounded-full bg-teal-100/80 px-3 py-1 text-xs font-bold uppercase tracking-wide text-teal-800">
                    <i class="fa-solid fa-award"></i>
                    Certifications Center
                </div>
                <h2 class="mt-3 text-lg font-bold text-slate-950 sm:text-xl">Licenses & certifications</h2>
                <p class="mt-1 text-sm text-slate-500">Expiring credentials from your employee file checklist</p>
            </div>
            @if($hasEmployeeRecord)
                <a href="{{ $employmentPortalUrl }}" class="inline-flex items-center justify-center gap-2 rounded-2xl bg-brand-600 px-4 py-2.5 text-sm font-bold text-white hover:bg-brand-700">
                    <i class="fa-solid fa-briefcase"></i>
                    Employment portal
                </a>
            @endif
        </div>

        <div class="mt-5 flex flex-wrap gap-2 border-b border-slate-200 pb-0">
            <button type="button"
                @click="tab = 'mine'"
                :class="tab === 'mine' ? 'border-brand-600 text-brand-700' : 'border-transparent text-slate-500 hover:text-slate-700'"
                class="border-b-2 px-4 py-2.5 text-sm font-bold transition">
                My certifications
            </button>
            @if($showFacilityTab)
                <button type="button"
                    @click="tab = 'facility'"
                    :class="tab === 'facility' ? 'border-brand-600 text-brand-700' : 'border-transparent text-slate-500 hover:text-slate-700'"
                    class="border-b-2 px-4 py-2.5 text-sm font-bold transition">
                    Facility compliance
                </button>
            @endif
        </div>
    </div>

    <div x-show="tab === 'mine'" class="p-6">
        @unless($hasEmployeeRecord)
            <div class="rounded-3xl border border-dashed border-slate-200 bg-slate-50 p-10 text-center">
                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-200 text-2xl text-slate-500">
                    <i class="fa-solid fa-user-slash"></i>
                </div>
                <h3 class="mt-4 text-lg font-bold text-slate-950">No employee record linked</h3>
                <p class="mx-auto mt-2 max-w-md text-sm text-slate-500">Your account is not linked to an employee file yet. Contact HR if you believe this is an error.</p>
            </div>
        @else
            <div class="mb-6 grid gap-3 sm:grid-cols-2 lg:grid-cols-5">
                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                    <p class="text-xs font-medium uppercase tracking-wide text-slate-500">Total tracked</p>
                    <p class="mt-1 text-2xl font-black text-slate-950">{{ $summary['total'] ?? 0 }}</p>
                </div>
                <div class="rounded-2xl border border-emerald-100 bg-emerald-50 p-4">
                    <p class="text-xs font-medium uppercase tracking-wide text-emerald-700">Valid</p>
                    <p class="mt-1 text-2xl font-black text-emerald-900">{{ $summary['valid'] ?? 0 }}</p>
                </div>
                <div class="rounded-2xl border border-amber-100 bg-amber-50 p-4">
                    <p class="text-xs font-medium uppercase tracking-wide text-amber-700">Expiring soon</p>
                    <p class="mt-1 text-2xl font-black text-amber-900">{{ $summary['expiring'] ?? 0 }}</p>
                </div>
                <div class="rounded-2xl border border-rose-100 bg-rose-50 p-4">
                    <p class="text-xs font-medium uppercase tracking-wide text-rose-600">Expired</p>
                    <p class="mt-1 text-2xl font-black text-rose-900">{{ $summary['expired'] ?? 0 }}</p>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                    <p class="text-xs font-medium uppercase tracking-wide text-slate-500">Needs attention</p>
                    <p class="mt-1 text-2xl font-black text-slate-950">{{ $summary['missing'] ?? 0 }}</p>
                </div>
            </div>

            @if(count($items) === 0)
                <div class="rounded-3xl border border-dashed border-slate-200 bg-slate-50 p-10 text-center">
                    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-teal-100 text-2xl text-teal-700">
                        <i class="fa-solid fa-award"></i>
                    </div>
                    <h3 class="mt-4 text-lg font-bold text-slate-950">No expiring certifications for your position</h3>
                    <p class="mx-auto mt-2 max-w-md text-sm text-slate-500">When licenses or credentials apply to your role, they will appear here with renewal dates from your employee file.</p>
                </div>
            @else
                <div class="mb-8 overflow-x-auto rounded-2xl border border-slate-200">
                    <table class="w-full min-w-[720px] text-left text-sm">
                        <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                            <tr>
                                <th class="px-4 py-3">Credential</th>
                                <th class="px-4 py-3">Section</th>
                                <th class="px-4 py-3">Expiry</th>
                                <th class="px-4 py-3">Days</th>
                                <th class="px-4 py-3">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($items as $cert)
                                @php
                                    $rowClass = match ($cert['status'] ?? '') {
                                        'expired', 'expires_today', 'expiring_urgent' => 'bg-rose-50/40',
                                        'expiring_soon' => 'bg-amber-50/40',
                                        default => '',
                                    };
                                    $daysLabel = isset($cert['days_until'])
                                        ? ($cert['days_until'] < 0
                                            ? abs($cert['days_until']) . 'd ago'
                                            : ($cert['days_until'] === 0 ? 'Today' : $cert['days_until'] . 'd'))
                                        : '—';
                                @endphp
                                <tr class="{{ $rowClass }}">
                                    <td class="px-4 py-3">
                                        <p class="font-semibold text-slate-950">{{ $cert['title'] ?? '—' }}</p>
                                        @if(!empty($cert['doc_type']))
                                            <p class="mt-0.5 text-xs text-slate-500">{{ $cert['doc_type'] }}</p>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-slate-500">{{ $cert['section'] ?? '—' }}</td>
                                    <td class="px-4 py-3 text-slate-600">{{ $cert['exp_dt_formatted'] ?? '—' }}</td>
                                    <td class="px-4 py-3 font-semibold text-slate-700">{{ $daysLabel }}</td>
                                    <td class="px-4 py-3">
                                        <span class="rounded-full px-3 py-1 text-xs font-bold {{ $cert['badge_class'] ?? 'bg-slate-100 text-slate-700' }}">
                                            {{ $cert['status_label'] ?? '—' }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if(($summary['expiring'] ?? 0) > 0 || ($summary['expired'] ?? 0) > 0 || ($summary['missing'] ?? 0) > 0)
                    <p class="mb-6 rounded-2xl border border-teal-100 bg-teal-50 px-4 py-3 text-sm text-teal-900">
                        <i class="fa-solid fa-circle-info mr-1"></i>
                        Renewals and file updates are managed through HR.
                        <a href="{{ $employmentPortalUrl }}" class="font-bold text-brand-600 hover:text-brand-700">Open employment portal</a>
                        or contact your facility administrator.
                    </p>
                @endif
            @endif

            @if(count($expiringUploads) > 0)
                <div>
                    <h3 class="mb-3 flex items-center gap-2 text-sm font-bold uppercase tracking-wide text-slate-500">
                        <i class="fa-solid fa-cloud-arrow-up text-brand-600"></i>
                        Uploaded files with expiry dates
                    </h3>
                    <div class="overflow-x-auto rounded-2xl border border-slate-200">
                        <table class="w-full min-w-[560px] text-left text-sm">
                            <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                                <tr>
                                    <th class="px-4 py-3">File</th>
                                    <th class="px-4 py-3">Type</th>
                                    <th class="px-4 py-3">Uploaded</th>
                                    <th class="px-4 py-3">Expires</th>
                                    <th class="px-4 py-3 text-right">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach($expiringUploads as $upload)
                                    <tr>
                                        <td class="px-4 py-3 font-semibold text-slate-950">{{ $upload['name'] ?? '—' }}</td>
                                        <td class="px-4 py-3 text-slate-500">{{ $upload['type'] ?? '—' }}</td>
                                        <td class="px-4 py-3 text-slate-500">{{ $upload['uploaded_at'] ?? '—' }}</td>
                                        <td class="px-4 py-3 text-slate-500">{{ $upload['expires_at'] ?? '—' }}</td>
                                        <td class="px-4 py-3 text-right">
                                            @if(!empty($upload['view_url']))
                                                <a href="{{ $upload['view_url'] }}" target="_blank" rel="noopener" class="font-bold text-brand-600 hover:text-brand-700">View</a>
                                            @else
                                                <span class="text-slate-400">—</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        @endunless
    </div>

    @if($showFacilityTab)
        @php
            $facilitySummary = $facilityReport['summary'] ?? [];
            $facilityEmployees = $facilityReport['employees'] ?? [];
        @endphp
        <div x-show="tab === 'facility'" x-cloak class="p-6">
            <div class="mb-2 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <p class="text-sm text-slate-500">
                    <span class="font-bold text-slate-700">{{ $facilityReport['facility']['name'] ?? 'Facility' }}</span>
                    — expiring licenses & credentials across active employees
                </p>
                @if(!empty($facilityReport['employees_list_url']))
                    <a href="{{ $facilityReport['employees_list_url'] }}" class="text-sm font-bold text-brand-600 hover:text-brand-700">
                        Manage all employees →
                    </a>
                @endif
            </div>

            <div class="mb-6 grid gap-3 sm:grid-cols-2 lg:grid-cols-5">
                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                    <p class="text-xs font-medium uppercase text-slate-500">Employees</p>
                    <p class="mt-1 text-2xl font-black text-slate-950">{{ $facilitySummary['total_employees'] ?? 0 }}</p>
                </div>
                <div class="rounded-2xl border border-amber-100 bg-amber-50 p-4">
                    <p class="text-xs font-medium uppercase text-amber-700">With issues</p>
                    <p class="mt-1 text-2xl font-black text-amber-900">{{ $facilitySummary['employees_with_issues'] ?? 0 }}</p>
                </div>
                <div class="rounded-2xl border border-amber-100 bg-amber-50 p-4">
                    <p class="text-xs font-medium uppercase text-amber-700">Expiring</p>
                    <p class="mt-1 text-2xl font-black text-amber-900">{{ $facilitySummary['total_expiring'] ?? 0 }}</p>
                </div>
                <div class="rounded-2xl border border-rose-100 bg-rose-50 p-4">
                    <p class="text-xs font-medium uppercase text-rose-600">Expired</p>
                    <p class="mt-1 text-2xl font-black text-rose-900">{{ $facilitySummary['total_expired'] ?? 0 }}</p>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                    <p class="text-xs font-medium uppercase text-slate-500">Missing / unverified</p>
                    <p class="mt-1 text-2xl font-black text-slate-950">{{ $facilitySummary['total_missing'] ?? 0 }}</p>
                </div>
            </div>

            <div class="mb-4">
                <label for="facility-cert-filter" class="sr-only">Filter employees</label>
                <input type="search" id="facility-cert-filter" x-model="facilityFilter" placeholder="Filter by employee name…"
                    class="w-full max-w-md rounded-2xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm focus:border-brand-400 focus:outline-none focus:ring-2 focus:ring-brand-100" />
            </div>

            @if(count($facilityEmployees) === 0)
                <p class="rounded-2xl border border-dashed border-slate-200 bg-slate-50 px-4 py-8 text-center text-sm text-slate-500">No employees with active assignments at this facility.</p>
            @else
                <div class="overflow-x-auto rounded-2xl border border-slate-200">
                    <table class="w-full min-w-[760px] text-left text-sm">
                        <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                            <tr>
                                <th class="px-4 py-3">Employee</th>
                                <th class="px-4 py-3">Position</th>
                                <th class="px-4 py-3">Expiring</th>
                                <th class="px-4 py-3">Expired</th>
                                <th class="px-4 py-3">Missing</th>
                                <th class="px-4 py-3">Top issues</th>
                                <th class="px-4 py-3 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($facilityEmployees as $row)
                                <tr x-show="!facilityFilter || ($el.dataset.name || '').includes(facilityFilter.toLowerCase())" data-name="{{ strtolower($row['name'] ?? '') }}">
                                    <td class="px-4 py-3 font-semibold text-slate-950">{{ $row['name'] ?: '—' }}</td>
                                    <td class="px-4 py-3 text-slate-500">{{ $row['position'] ?? '—' }}</td>
                                    <td class="px-4 py-3">
                                        @if(($row['expiring_count'] ?? 0) > 0)
                                            <span class="rounded-full bg-amber-50 px-3 py-1 text-xs font-bold text-amber-700">{{ $row['expiring_count'] }}</span>
                                        @else
                                            <span class="text-slate-400">0</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        @if(($row['expired_count'] ?? 0) > 0)
                                            <span class="rounded-full bg-rose-50 px-3 py-1 text-xs font-bold text-rose-700">{{ $row['expired_count'] }}</span>
                                        @else
                                            <span class="text-slate-400">0</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        @if(($row['missing_count'] ?? 0) > 0)
                                            <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-700">{{ $row['missing_count'] }}</span>
                                        @else
                                            <span class="text-slate-400">0</span>
                                        @endif
                                    </td>
                                    <td class="max-w-xs px-4 py-3 text-slate-600">
                                        @if(!empty($row['top_issues']))
                                            <ul class="list-inside list-disc text-xs">
                                                @foreach($row['top_issues'] as $issue)
                                                    <li>{{ $issue }}</li>
                                                @endforeach
                                            </ul>
                                        @else
                                            <span class="text-emerald-600">All clear</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        @if(!empty($row['manage_url']))
                                            <a href="{{ $row['manage_url'] }}" class="font-bold text-brand-600 hover:text-brand-700">Manage</a>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    @endif
</section>
