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
    $requiredUploadTypes = collect($documentsCenter['required_upload_types'] ?? [])->values();
@endphp

<section id="documents" class="overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-card" x-data="memberDocumentsCenter({ uploadUrl: @js(route('employment.documents.upload')), uploadTypes: @js($requiredUploadTypes), csrf: @js(csrf_token()) })">
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

        <div class="mt-2 flex flex-wrap gap-2 border-b border-slate-200 pb-0">
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
                                    <th class="px-3 py-2">Item</th>
                                    <th class="px-3 py-2">Required</th>
                                    <th class="w-44 min-w-[11rem] whitespace-nowrap px-3 py-2">Status</th>
                                    <th class="w-20 whitespace-nowrap px-3 py-2 text-right">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach($complianceMissing as $doc)
                                    @php
                                        $badgeClass = match ($doc['status'] ?? '') {
                                            'not_on_file' => 'bg-rose-50 text-rose-700',
                                            'missing' => 'bg-rose-50 text-rose-700',
                                            'expired' => 'bg-amber-50 text-amber-700',
                                            'expiry_missing' => 'bg-amber-50 text-amber-700',
                                            default => 'bg-slate-100 text-slate-700',
                                        };
                                    @endphp
                                    <tr>
                                        <td class="px-3 py-2 font-semibold text-slate-950">{{ $doc['title'] ?? '—' }}</td>
                                        <td class="px-3 py-2 text-slate-500">{{ !empty($doc['required']) ? 'Yes' : 'No' }}</td>
                                        <td class="whitespace-nowrap px-3 py-2">
                                            <span class="inline-flex whitespace-nowrap rounded-full px-3 py-1 text-xs font-bold {{ $badgeClass }}">{{ $doc['status_label'] ?? 'Needs attention' }}</span>
                                        </td>
                                        <td class="px-3 py-2 text-right">
                                            @if($employmentPortalUrl !== '#')
                                                <button type="button" @click="openUploadModal(@js($doc['upload_type_id'] ?? null))" class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-brand-200 text-brand-600 hover:bg-brand-50 hover:text-brand-700" title="Upload document" aria-label="Upload document">
                                                    <i class="fa-solid fa-upload"></i>
                                                </button>
                                            @else
                                                <span class="inline-flex h-8 w-8 cursor-not-allowed items-center justify-center rounded-lg border border-slate-200 text-slate-300" title="Upload unavailable" aria-label="Upload unavailable">
                                                    <i class="fa-solid fa-upload"></i>
                                                </span>
                                            @endif
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
                                            <div class="inline-flex items-center gap-2">
                                                @if(!empty($upload['view_url']))
                                                    <a href="{{ $upload['view_url'] }}" target="_blank" rel="noopener" class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-brand-200 text-brand-600 hover:bg-brand-50 hover:text-brand-700" title="View document" aria-label="View document">
                                                        <i class="fa-solid fa-eye"></i>
                                                    </a>
                                                @else
                                                    <span class="inline-flex h-8 w-8 cursor-not-allowed items-center justify-center rounded-lg border border-slate-200 text-slate-300" title="View unavailable" aria-label="View unavailable">
                                                        <i class="fa-solid fa-eye"></i>
                                                    </span>
                                                @endif

                                                @if(!empty($upload['edit_url']))
                                                    <a href="{{ $upload['edit_url'] }}" class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-brand-200 text-brand-600 hover:bg-brand-50 hover:text-brand-700" title="Edit document" aria-label="Edit document">
                                                        <i class="fa-solid fa-pen-to-square"></i>
                                                    </a>
                                                @else
                                                    <span class="inline-flex h-8 w-8 cursor-not-allowed items-center justify-center rounded-lg border border-slate-200 text-slate-300" title="Edit unavailable" aria-label="Edit unavailable">
                                                        <i class="fa-solid fa-pen-to-square"></i>
                                                    </span>
                                                @endif
                                            </div>
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

    <template x-teleport="body">
        <div x-show="showUploadModal" x-cloak class="fixed inset-0 z-[100] flex items-center justify-center p-4 overflow-y-auto">
            <div class="absolute inset-0 bg-black/50" @click="closeUploadModal()"></div>
            <div class="relative z-10 w-full max-w-2xl rounded-2xl bg-white p-6 shadow-xl">
                <div class="mb-4 flex items-center justify-between">
                    <h3 class="text-lg font-bold text-slate-900">Upload Document</h3>
                    <button type="button" class="text-xl leading-none text-slate-500 hover:text-slate-700" @click="closeUploadModal()" aria-label="Close">&times;</button>
                </div>
                <form method="POST" :action="uploadUrl" enctype="multipart/form-data" class="space-y-4">
                    <input type="hidden" name="_token" :value="csrf">
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-xs font-semibold">Upload Type <span class="text-rose-600">*</span></label>
                            <select name="upload_type_id" x-model="form.upload_type_id" required class="w-full rounded border border-slate-300 bg-slate-50 px-2 py-1.5 text-sm focus:border-brand-500 focus:outline-none">
                                <option value="">Select type</option>
                                <template x-for="type in uploadTypes" :key="type.id">
                                    <option :value="String(type.id)" x-text="type.name"></option>
                                </template>
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold">File <span class="text-rose-600">*</span></label>
                            <input type="file" name="document" required class="w-full rounded border border-slate-300 bg-slate-50 px-2 py-1.5 text-sm focus:border-brand-500 focus:outline-none">
                        </div>
                    </div>

                    <div x-show="requiresExpiry" class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-xs font-semibold">Effective Start Date</label>
                            <input type="date" name="effective_start_date" x-model="form.effective_start_date" class="w-full rounded border border-slate-300 bg-slate-50 px-2 py-1.5 text-sm focus:border-brand-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold">Expires At <span class="text-rose-600">*</span></label>
                            <input type="date" name="expires_at" x-model="form.expires_at" :required="requiresExpiry" :min="form.effective_start_date || null" class="w-full rounded border border-slate-300 bg-slate-50 px-2 py-1.5 text-sm focus:border-brand-500 focus:outline-none">
                        </div>
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-semibold">Comments</label>
                        <textarea name="comments" rows="2" x-model="form.comments" class="w-full rounded border border-slate-300 bg-slate-50 px-2 py-1.5 text-sm focus:border-brand-500 focus:outline-none"></textarea>
                    </div>

                    <div class="flex justify-end gap-2">
                        <button type="button" @click="closeUploadModal()" class="rounded bg-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-300">Cancel</button>
                        <button type="submit" class="rounded bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">Upload</button>
                    </div>
                </form>
            </div>
        </div>
    </template>

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

<script>
    function memberDocumentsCenter(config) {
        return {
            tab: 'mine',
            facilityFilter: '',
            showUploadModal: false,
            uploadUrl: config.uploadUrl,
            uploadTypes: config.uploadTypes || [],
            csrf: config.csrf,
            form: {
                upload_type_id: '',
                effective_start_date: '',
                expires_at: '',
                comments: '',
            },
            get requiresExpiry() {
                const selected = this.uploadTypes.find((type) => String(type.id) === String(this.form.upload_type_id));
                return !!(selected && selected.requires_expiry);
            },
            openUploadModal(uploadTypeId = null) {
                this.form.upload_type_id = uploadTypeId ? String(uploadTypeId) : '';
                this.form.effective_start_date = '';
                this.form.expires_at = '';
                this.form.comments = '';
                this.showUploadModal = true;
            },
            closeUploadModal() {
                this.showUploadModal = false;
            },
        };
    }
</script>
