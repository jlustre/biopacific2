@php
    $documentsCenter = $documentsCenter ?? [];
    $facilityReport = $facilityComplianceReport ?? null;
    $showFacilityTab = ($isFacilityDocumentsAdmin ?? false) && !empty($facilityReport);
    $uploads = $documentsCenter['uploads'] ?? [];
    $complianceMissing = $documentsCenter['compliance_missing'] ?? [];
    $complianceComplete = $documentsCenter['compliance_complete'] ?? [];
    $signatures = $documentsCenter['signatures'] ?? [];
    $verifiedPercent = $documentsCenter['verified_percent'] ?? ($stats['employee_file_verified'] ?? null);
    $hasEmployeeRecord = $documentsCenter['has_employee_record'] ?? false;
    $missingCount = count($complianceMissing);
    $signatureCount = count($signatures);
    $uploadCount = count($uploads);
    $completeCount = count($complianceComplete);
    $employmentPortalUrl = \Illuminate\Support\Facades\Route::has('employment.portal') ? route('employment.portal') : '#';
@endphp

<section id="documents" class="overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-card" x-data="{ tab: 'mine', facilityFilter: '' }">
    <div class="border-b border-slate-200 bg-teal-50 p-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <div class="inline-flex items-center gap-2 rounded-full bg-cyan-100/80 px-3 py-1 text-xs font-bold uppercase tracking-wide text-cyan-800">
                    <i class="fa-solid fa-folder-open"></i>
                    Document Center
                </div>
                <h2 class="mt-3 text-lg font-bold text-slate-950 sm:text-xl">Documents & compliance</h2>
                <p class="mt-1 text-sm text-slate-500">Uploads, employee file checklist, and signatures</p>
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
                My documents
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

    {{-- My documents --}}
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
            <div class="mb-6 flex flex-wrap gap-3">
                <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
                    <p class="text-xs font-medium uppercase tracking-wide text-slate-500">File verified</p>
                    <p class="mt-1 text-2xl font-black text-slate-950">{{ $verifiedPercent !== null ? $verifiedPercent . '%' : '—' }}</p>
                </div>
                <div class="rounded-2xl border border-rose-100 bg-rose-50 px-4 py-3">
                    <p class="text-xs font-medium uppercase tracking-wide text-rose-600">Needs attention</p>
                    <p class="mt-1 text-2xl font-black text-rose-900">{{ $missingCount }}</p>
                </div>
                <div class="rounded-2xl border border-amber-100 bg-amber-50 px-4 py-3">
                    <p class="text-xs font-medium uppercase tracking-wide text-amber-700">Signatures</p>
                    <p class="mt-1 text-2xl font-black text-amber-900">{{ $signatureCount }}</p>
                </div>
                <div class="rounded-2xl border border-emerald-100 bg-emerald-50 px-4 py-3">
                    <p class="text-xs font-medium uppercase tracking-wide text-emerald-700">Uploads on file</p>
                    <p class="mt-1 text-2xl font-black text-emerald-900">{{ $uploadCount }}</p>
                </div>
                <div class="rounded-2xl border border-brand-100 bg-brand-50 px-4 py-3">
                    <p class="text-xs font-medium uppercase tracking-wide text-brand-700">Verified items</p>
                    <p class="mt-1 text-2xl font-black text-brand-900">{{ $completeCount }}</p>
                </div>
            </div>

            @if($signatureCount > 0)
                <div class="mb-8">
                    <h3 class="mb-3 flex items-center gap-2 text-sm font-bold uppercase tracking-wide text-slate-500">
                        <i class="fa-solid fa-signature text-amber-600"></i>
                        Signatures needed
                    </h3>
                    <ul class="space-y-3">
                        @foreach($signatures as $sig)
                            <li class="flex flex-col gap-3 rounded-2xl border border-amber-100 bg-amber-50/80 p-4 sm:flex-row sm:items-center sm:justify-between">
                                <div>
                                    <p class="font-bold text-amber-950">{{ $sig['title'] ?? 'Signature required' }}</p>
                                    <p class="mt-1 text-sm text-amber-800">{{ $sig['description'] ?? '' }}</p>
                                    @if(!empty($sig['due_at']))
                                        <p class="mt-1 text-xs text-amber-700">Due {{ \Carbon\Carbon::parse($sig['due_at'])->format('M j, Y') }}</p>
                                    @endif
                                </div>
                                <a href="{{ $employmentPortalUrl }}" class="shrink-0 rounded-xl bg-amber-600 px-4 py-2 text-sm font-bold text-white hover:bg-amber-700">Sign now</a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if($missingCount > 0)
                <div class="mb-8">
                    <h3 class="mb-3 flex items-center gap-2 text-sm font-bold uppercase tracking-wide text-slate-500">
                        <i class="fa-solid fa-triangle-exclamation text-rose-600"></i>
                        Compliance gaps
                    </h3>
                    <div class="overflow-x-auto rounded-2xl border border-slate-200">
                        <table class="w-full min-w-[560px] text-left text-sm">
                            <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                                <tr>
                                    <th class="px-4 py-3">Item</th>
                                    <th class="px-4 py-3">Section</th>
                                    <th class="px-4 py-3">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach($complianceMissing as $doc)
                                    @php
                                        $badgeClass = match ($doc['status'] ?? '') {
                                            'not_on_file' => 'bg-rose-50 text-rose-700',
                                            'expiry_missing' => 'bg-amber-50 text-amber-700',
                                            default => 'bg-slate-100 text-slate-700',
                                        };
                                    @endphp
                                    <tr>
                                        <td class="px-4 py-3 font-semibold text-slate-950">{{ $doc['title'] ?? '—' }}</td>
                                        <td class="px-4 py-3 text-slate-500">{{ $doc['section'] ?? '—' }}</td>
                                        <td class="px-4 py-3">
                                            <span class="rounded-full px-3 py-1 text-xs font-bold {{ $badgeClass }}">{{ $doc['status_label'] ?? 'Needs attention' }}</span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @elseif($completeCount > 0)
                <p class="mb-6 rounded-2xl border border-emerald-100 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">
                    <i class="fa-solid fa-circle-check mr-1"></i>
                    All applicable checklist items are on file and verified.
                </p>
            @endif

            <div>
                <h3 class="mb-3 flex items-center gap-2 text-sm font-bold uppercase tracking-wide text-slate-500">
                    <i class="fa-solid fa-cloud-arrow-up text-brand-600"></i>
                    My uploads
                </h3>
                @if($uploadCount === 0)
                    <p class="rounded-2xl border border-dashed border-slate-200 bg-slate-50 px-4 py-8 text-center text-sm text-slate-500">
                        No files uploaded to your employee record yet.
                        @if(\Illuminate\Support\Facades\Route::has('employment.portal'))
                            <a href="{{ $employmentPortalUrl }}" class="font-bold text-brand-600 hover:text-brand-700">Open employment portal</a>
                        @endif
                    </p>
                @else
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
                                @foreach($uploads as $upload)
                                    <tr>
                                        <td class="px-4 py-3 font-semibold text-slate-950">{{ $upload['name'] ?? '—' }}</td>
                                        <td class="px-4 py-3 text-slate-500">{{ $upload['type'] ?? '—' }}</td>
                                        <td class="px-4 py-3 text-slate-500">{{ $upload['uploaded_at'] ?? '—' }}</td>
                                        <td class="px-4 py-3 text-slate-500">{{ $upload['expires_at'] ?? '—' }}</td>
                                        <td class="px-4 py-3 text-right">
                                            @if(!empty($upload['view_url']))
                                                <a href="{{ $upload['view_url'] }}" target="_blank" rel="noopener" class="font-bold text-brand-600 hover:text-brand-700">View</a>
                                                @if(!empty($upload['download_url']))
                                                    <span class="text-slate-300">|</span>
                                                    <a href="{{ $upload['download_url'] }}" class="font-bold text-brand-600 hover:text-brand-700">Download</a>
                                                @endif
                                            @else
                                                <span class="text-slate-400">—</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        @endunless
    </div>

    {{-- Facility compliance --}}
    @if($showFacilityTab)
        @php
            $summary = $facilityReport['summary'] ?? [];
            $facilityEmployees = $facilityReport['employees'] ?? [];
        @endphp
        <div x-show="tab === 'facility'" x-cloak class="p-6">
            <div class="mb-2 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <p class="text-sm text-slate-500">
                    <span class="font-bold text-slate-700">{{ $facilityReport['facility']['name'] ?? 'Facility' }}</span>
                    — missing employee file items across active assignments
                </p>
                @if(!empty($facilityReport['employees_list_url']))
                    <a href="{{ $facilityReport['employees_list_url'] }}" class="text-sm font-bold text-brand-600 hover:text-brand-700">
                        Manage all employees →
                    </a>
                @endif
            </div>

            <div class="mb-6 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                    <p class="text-xs font-medium uppercase text-slate-500">Total employees</p>
                    <p class="mt-1 text-2xl font-black text-slate-950">{{ $summary['total_employees'] ?? 0 }}</p>
                </div>
                <div class="rounded-2xl border border-amber-100 bg-amber-50 p-4">
                    <p class="text-xs font-medium uppercase text-amber-700">With gaps</p>
                    <p class="mt-1 text-2xl font-black text-amber-900">{{ $summary['employees_with_gaps'] ?? 0 }}</p>
                </div>
                <div class="rounded-2xl border border-rose-100 bg-rose-50 p-4">
                    <p class="text-xs font-medium uppercase text-rose-600">Missing items</p>
                    <p class="mt-1 text-2xl font-black text-rose-900">{{ $summary['total_missing_items'] ?? 0 }}</p>
                </div>
                <div class="rounded-2xl border border-emerald-100 bg-emerald-50 p-4">
                    <p class="text-xs font-medium uppercase text-emerald-700">Avg compliance</p>
                    <p class="mt-1 text-2xl font-black text-emerald-900">{{ ($summary['average_compliance_percent'] ?? null) !== null ? $summary['average_compliance_percent'] . '%' : '—' }}</p>
                </div>
            </div>

            <div class="mb-4">
                <label for="facility-employee-filter" class="sr-only">Filter employees</label>
                <input type="search" id="facility-employee-filter" x-model="facilityFilter" placeholder="Filter by employee name…"
                    class="w-full max-w-md rounded-2xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm focus:border-brand-400 focus:outline-none focus:ring-2 focus:ring-brand-100" />
            </div>

            @if(count($facilityEmployees) === 0)
                <p class="rounded-2xl border border-dashed border-slate-200 bg-slate-50 px-4 py-8 text-center text-sm text-slate-500">No employees with active assignments at this facility.</p>
            @else
                <div class="overflow-x-auto rounded-2xl border border-slate-200">
                    <table class="w-full min-w-[720px] text-left text-sm">
                        <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                            <tr>
                                <th class="px-4 py-3">Employee</th>
                                <th class="px-4 py-3">Position</th>
                                <th class="px-4 py-3">Missing</th>
                                <th class="px-4 py-3">Top gaps</th>
                                <th class="px-4 py-3">Compliance</th>
                                <th class="px-4 py-3 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($facilityEmployees as $row)
                                <tr x-show="!facilityFilter || ($el.dataset.name || '').includes(facilityFilter.toLowerCase())" data-name="{{ strtolower($row['name'] ?? '') }}">
                                    <td class="px-4 py-3 font-semibold text-slate-950">{{ $row['name'] ?: '—' }}</td>
                                    <td class="px-4 py-3 text-slate-500">{{ $row['position'] ?? '—' }}</td>
                                    <td class="px-4 py-3">
                                        @if(($row['missing_count'] ?? 0) > 0)
                                            <span class="rounded-full bg-rose-50 px-3 py-1 text-xs font-bold text-rose-700">{{ $row['missing_count'] }}</span>
                                        @else
                                            <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-700">0</span>
                                        @endif
                                    </td>
                                    <td class="max-w-xs px-4 py-3 text-slate-600">
                                        @if(!empty($row['top_missing']))
                                            <ul class="list-inside list-disc text-xs">
                                                @foreach($row['top_missing'] as $item)
                                                    <li>{{ $item }}</li>
                                                @endforeach
                                            </ul>
                                        @else
                                            <span class="text-emerald-600">Complete</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-slate-600">{{ ($row['verified_percent'] ?? null) !== null ? $row['verified_percent'] . '%' : '—' }}</td>
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
