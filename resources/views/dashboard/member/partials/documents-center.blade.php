@php
    $documentsCenter = $documentsCenter ?? [];
    $facilityReport = $facilityComplianceReport ?? null;
    $showFacilityTab = ($isFacilityDocumentsAdmin ?? false) && !empty($facilityReport);
    $initialDocsTab = request('docs_tab') === 'facility' && $showFacilityTab ? 'facility' : 'mine';
    $documentsPaginator = $documentsCenter['documents_paginator'] ?? null;
    $documents = $documentsPaginator ? $documentsPaginator->items() : ($documentsCenter['documents'] ?? $documentsCenter['uploads'] ?? []);
    $documentFilters = $documentsCenter['document_filters'] ?? ['search' => '', 'type' => '', 'expiry' => '', 'status' => '', 'sort' => 'uploaded_desc', 'per_page' => 10];
    $documentTypeOptions = $documentsCenter['document_type_options'] ?? [];
    $complianceMissing = $documentsCenter['compliance_missing'] ?? [];
    $complianceComplete = $documentsCenter['compliance_complete'] ?? [];
    $requiredDocuments = $documentsCenter['required_documents'] ?? [];
    $requiredDocumentsPaginator = $documentsCenter['required_documents_paginator'] ?? null;
    if ($requiredDocuments === [] && ! $requiredDocumentsPaginator && ($complianceMissing !== [] || $complianceComplete !== [])) {
        $requiredDocuments = collect($complianceComplete)
            ->map(fn ($doc) => array_merge($doc, ['is_uploaded' => true, 'title' => $doc['title'] ?? $doc['name'] ?? 'Required document']))
            ->concat($complianceMissing)
            ->sortBy(fn ($doc) => mb_strtolower((string) ($doc['title'] ?? '')))
            ->values()
            ->all();
    }
    $requiredDocumentFilters = $documentsCenter['required_document_filters'] ?? ['search' => '', 'status' => '', 'required' => '', 'sort' => 'name_asc', 'per_page' => 10];
    $requiredDocumentsCatalogTotal = (int) ($documentsCenter['required_documents_catalog_total'] ?? count($requiredDocuments));
    $requiredDocumentsFilteredTotal = $requiredDocumentsPaginator
        ? (int) $requiredDocumentsPaginator->total()
        : (int) ($documentsCenter['required_documents_total'] ?? count($requiredDocuments));
    $hasActiveRequiredFilters = !empty($requiredDocumentFilters['search'])
        || !empty($requiredDocumentFilters['status'])
        || !empty($requiredDocumentFilters['required'])
        || ($requiredDocumentFilters['sort'] ?? 'name_asc') !== 'name_asc'
        || (int) ($requiredDocumentFilters['per_page'] ?? 10) !== 10;
    $signatures = $documentsCenter['signatures'] ?? [];
    $verifiedPercent = $documentsCenter['verified_percent'] ?? ($stats['employee_file_verified'] ?? null);
    $hasEmployeeRecord = $documentsCenter['has_employee_record'] ?? false;
    $missingCount = count($complianceMissing);
    $requiredDocumentsCount = $requiredDocumentsCatalogTotal;
    $signatureCount = count($signatures);
    $uploadCount = (int) ($documentsCenter['documents_total'] ?? ($documentsPaginator?->total() ?? count($documents)));
    $filteredCount = $documentsPaginator ? $documentsPaginator->total() : count($documents);
    $hasActiveDocumentFilters = !empty($documentFilters['search'])
        || !empty($documentFilters['type'])
        || !empty($documentFilters['expiry'])
        || !empty($documentFilters['status'])
        || ($documentFilters['sort'] ?? 'uploaded_desc') !== 'uploaded_desc'
        || (int) ($documentFilters['per_page'] ?? 10) !== 10;
    $completeCount = count($complianceComplete);
    $requiredNotOnFileCount = (int) ($documentsCenter['required_not_on_file_count'] ?? collect($complianceMissing)->filter(fn ($doc) => in_array($doc['status'] ?? '', ['missing', 'not_on_file'], true))->count());
    $expiringIn60DaysCount = (int) ($documentsCenter['expiring_in_60_days_count'] ?? 0);
    $employmentPortalUrl = \Illuminate\Support\Facades\Route::has('employment.portal') ? route('employment.portal') : '#';
    $requiredUploadTypes = collect($documentsCenter['required_upload_types'] ?? [])->values();
    $positionTitle = $documentsCenter['position_title'] ?? null;
    $submissionReasonOptions = $documentsCenter['submission_reason_options'] ?? \App\Support\UploadSubmissionReason::options();
    $certificationsUrl = \Illuminate\Support\Facades\Route::has('member.certifications') ? route('member.certifications') : null;
@endphp

@if(session('success') || session('error'))
    <div class="mb-4">
        @if(session('success'))
            <p class="rounded-2xl border border-emerald-100 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">{{ session('success') }}</p>
        @endif
        @if(session('error'))
            <p class="rounded-2xl border border-rose-100 bg-rose-50 px-4 py-3 text-sm font-semibold text-rose-800">{{ session('error') }}</p>
        @endif
    </div>
@endif

<section id="documents" class="overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-card" x-data="memberDocumentsCenter({ uploadUrl: @js(route('employment.documents.upload')), uploadTypes: @js($requiredUploadTypes), submissionReasons: @js($submissionReasonOptions), csrf: @js(csrf_token()), initialTab: @js($initialDocsTab) })" x-init="initFromQuery()">
    <div class="border-b border-slate-200 bg-teal-50 p-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <div class="inline-flex items-center gap-2 rounded-full bg-cyan-100/80 px-3 py-1 text-xs font-bold uppercase tracking-wide text-cyan-800">
                    <i class="fa-solid fa-folder-open"></i>
                    Document Center
                </div>
                <h2 class="mt-3 text-lg font-bold text-slate-950 sm:text-xl">Documents & compliance</h2>
                <p class="mt-1 text-sm text-slate-500">
                    Your employee documents, file checklist, and signatures
                    <span class="block mt-1 text-xs text-teal-700">
                        Shows documents required for all employees
                        @if($positionTitle)
                            and your position: <strong>{{ $positionTitle }}</strong>
                        @endif
                    </span>
                    <span class="block mt-1 text-xs text-slate-500">{{ config('documents.labels.upload_review_notice') }}
                        @if($certificationsUrl)
                            <a href="{{ $certificationsUrl }}" class="font-semibold text-brand-600 hover:text-brand-700">{{ config('documents.labels.certifications_subset_note') }}</a>
                        @endif
                    </span>
                </p>
            </div>
            @if($hasEmployeeRecord)
                <div class="flex flex-wrap items-center gap-2">
                    <button type="button" @click="openUploadModal()" class="inline-flex items-center justify-center gap-2 rounded-2xl border border-brand-200 bg-white px-4 py-2.5 text-sm font-bold text-brand-700 hover:bg-brand-50">
                        <i class="fa-solid fa-upload"></i>
                        Upload document
                    </button>
                    <a href="{{ $employmentPortalUrl }}" class="inline-flex items-center justify-center gap-2 rounded-2xl bg-brand-600 px-4 py-2.5 text-sm font-bold text-white hover:bg-brand-700">
                        <i class="fa-solid fa-briefcase"></i>
                        Employment portal
                    </a>
                </div>
            @endif
        </div>

        <div class="mt-2 flex flex-wrap gap-2 border-b border-slate-200 pb-0">
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
                <div class="rounded-2xl border border-amber-100 bg-amber-50 px-4 py-3">
                    <p class="text-xs font-medium uppercase tracking-wide text-amber-700">{{ config('documents.labels.expiring_in_60_days') }}</p>
                    <p class="mt-1 text-2xl font-black text-amber-900">{{ $expiringIn60DaysCount }}</p>
                </div>
                <div class="rounded-2xl border border-orange-100 bg-orange-50 px-4 py-3">
                    <p class="text-xs font-medium uppercase tracking-wide text-orange-700">{{ config('documents.labels.required_not_on_file') }}</p>
                    <p class="mt-1 text-2xl font-black text-orange-950">{{ $requiredNotOnFileCount }}</p>
                </div>
                <div class="rounded-2xl border border-amber-100 bg-amber-50 px-4 py-3">
                    <p class="text-xs font-medium uppercase tracking-wide text-amber-700">Signatures</p>
                    <p class="mt-1 text-2xl font-black text-amber-900">{{ $signatureCount }}</p>
                </div>
                <div class="rounded-2xl border border-emerald-100 bg-emerald-50 px-4 py-3">
                    <p class="text-xs font-medium uppercase tracking-wide text-emerald-700">{{ config('documents.labels.on_file') }}</p>
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

            @if($requiredDocumentsCount > 0 || $hasActiveRequiredFilters)
                <div class="mb-8">
                    <div class="mb-3 flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
                        <h3 class="flex items-center gap-2 text-sm font-bold uppercase tracking-wide text-slate-500">
                            <i class="fa-solid fa-clipboard-list text-brand-600"></i>
                            Required documents
                        </h3>
                        <p class="text-xs text-slate-500">
                            {{ $requiredDocumentsCount }} required
                            @if($completeCount > 0)
                                · {{ $completeCount }} on file
                            @endif
                            @if($missingCount > 0)
                                · {{ $missingCount }} need attention
                            @endif
                            <span class="block sm:inline sm:before:content-['·_']">Sorted alphabetically</span>
                        </p>
                    </div>

                    <form method="GET" action="{{ route('member.documents') }}" class="mb-4 grid gap-3 rounded-2xl border border-slate-200 bg-slate-50 p-4 sm:grid-cols-2 lg:grid-cols-6" id="required-documents-filter-form">
                        {{-- Preserve My documents filters when filtering required docs --}}
                        @foreach(['q' => 'search', 'type' => 'type', 'status' => 'status', 'expiry' => 'expiry', 'sort' => 'sort', 'per_page' => 'per_page'] as $queryKey => $filterKey)
                            @if(!empty($documentFilters[$filterKey]) && (($filterKey === 'sort' && $documentFilters[$filterKey] !== 'uploaded_desc') || ($filterKey === 'per_page' && (int) $documentFilters[$filterKey] !== 10) || !in_array($filterKey, ['sort', 'per_page'], true)))
                                <input type="hidden" name="{{ $queryKey }}" value="{{ $documentFilters[$filterKey] }}">
                            @endif
                        @endforeach
                        <div class="lg:col-span-2">
                            <label for="required-documents-search" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">Search</label>
                            <input type="search" id="required-documents-search" name="rq" value="{{ $requiredDocumentFilters['search'] ?? '' }}"
                                placeholder="Document name…"
                                class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm focus:border-brand-400 focus:outline-none focus:ring-2 focus:ring-brand-100">
                        </div>
                        <div>
                            <label for="required-documents-required" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">Required</label>
                            <select id="required-documents-required" name="rrequired" onchange="this.form.submit()" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm focus:border-brand-400 focus:outline-none focus:ring-2 focus:ring-brand-100">
                                <option value="" @selected(empty($requiredDocumentFilters['required']))>All</option>
                                <option value="yes" @selected(($requiredDocumentFilters['required'] ?? '') === 'yes')>Yes</option>
                                <option value="no" @selected(($requiredDocumentFilters['required'] ?? '') === 'no')>No</option>
                                <option value="all_employees" @selected(($requiredDocumentFilters['required'] ?? '') === 'all_employees')>All employees</option>
                                <option value="position" @selected(($requiredDocumentFilters['required'] ?? '') === 'position')>Position</option>
                            </select>
                        </div>
                        <div>
                            <label for="required-documents-status" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">Status</label>
                            <select id="required-documents-status" name="rstatus" onchange="this.form.submit()" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm focus:border-brand-400 focus:outline-none focus:ring-2 focus:ring-brand-100">
                                <option value="" @selected(empty($requiredDocumentFilters['status']))>All statuses</option>
                                <option value="complete" @selected(($requiredDocumentFilters['status'] ?? '') === 'complete')>On file</option>
                                <option value="missing" @selected(($requiredDocumentFilters['status'] ?? '') === 'missing')>Not on file</option>
                                <option value="pending_review" @selected(($requiredDocumentFilters['status'] ?? '') === 'pending_review')>Pending review</option>
                                <option value="expired" @selected(($requiredDocumentFilters['status'] ?? '') === 'expired')>Expired</option>
                                <option value="rejected" @selected(($requiredDocumentFilters['status'] ?? '') === 'rejected')>Rejected</option>
                            </select>
                        </div>
                        <div>
                            <label for="required-documents-sort" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">Sort by</label>
                            <select id="required-documents-sort" name="rsort" onchange="this.form.submit()" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm focus:border-brand-400 focus:outline-none focus:ring-2 focus:ring-brand-100">
                                <option value="name_asc" @selected(($requiredDocumentFilters['sort'] ?? 'name_asc') === 'name_asc')>Name (A–Z)</option>
                                <option value="name_desc" @selected(($requiredDocumentFilters['sort'] ?? '') === 'name_desc')>Name (Z–A)</option>
                                <option value="uploaded_first" @selected(($requiredDocumentFilters['sort'] ?? '') === 'uploaded_first')>Uploaded first</option>
                            </select>
                        </div>
                        <div>
                            <label for="required-documents-per-page" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">Per page</label>
                            <select id="required-documents-per-page" name="rper_page" onchange="this.form.submit()" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm focus:border-brand-400 focus:outline-none focus:ring-2 focus:ring-brand-100">
                                @foreach([10, 25, 50] as $size)
                                    <option value="{{ $size }}" @selected((int) ($requiredDocumentFilters['per_page'] ?? 10) === $size)>{{ $size }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="flex flex-wrap items-center gap-2 sm:col-span-2 lg:col-span-6">
                            <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-brand-600 px-4 py-2 text-sm font-bold text-white hover:bg-brand-700">
                                <i class="fa-solid fa-filter text-xs" aria-hidden="true"></i>
                                Apply
                            </button>
                            @if($hasActiveRequiredFilters)
                                <a href="{{ route('member.documents', request()->except(['rq', 'rstatus', 'rrequired', 'rsort', 'rper_page', 'rpage'])) }}" class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-100">Reset</a>
                            @endif
                            @if($requiredDocumentsFilteredTotal > 0)
                                <p class="text-xs text-slate-500 sm:ml-auto">
                                    @if($requiredDocumentsPaginator)
                                        Showing {{ $requiredDocumentsPaginator->firstItem() }}–{{ $requiredDocumentsPaginator->lastItem() }} of {{ $requiredDocumentsFilteredTotal }}
                                    @else
                                        {{ $requiredDocumentsFilteredTotal }} document{{ $requiredDocumentsFilteredTotal === 1 ? '' : 's' }}
                                    @endif
                                    @if($hasActiveRequiredFilters)
                                        matching
                                        @if($requiredDocumentsCatalogTotal > 0)
                                            ({{ $requiredDocumentsCatalogTotal }} total)
                                        @endif
                                    @endif
                                </p>
                            @endif
                        </div>
                    </form>

                    @if($requiredDocumentsFilteredTotal === 0)
                        <p class="rounded-2xl border border-dashed border-slate-200 bg-slate-50 px-4 py-8 text-center text-sm text-slate-500">
                            No required documents match your search or filters.
                            <a href="{{ route('member.documents', request()->except(['rq', 'rstatus', 'rrequired', 'rsort', 'rper_page', 'rpage'])) }}" class="font-bold text-brand-600 hover:text-brand-700">Clear filters</a>
                        </p>
                    @else
                        <div class="overflow-x-auto rounded-2xl border border-slate-200">
                            <table class="w-full min-w-[720px] text-left text-sm">
                                <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                                    <tr>
                                        <th class="px-3 py-2">Document</th>
                                        <th class="w-24 whitespace-nowrap px-3 py-2">Required</th>
                                        <th class="w-40 min-w-[9rem] whitespace-nowrap px-3 py-2">Status</th>
                                        <th class="px-3 py-2">Uploaded</th>
                                        <th class="px-3 py-2">Expires</th>
                                        <th class="w-28 whitespace-nowrap px-3 py-2 text-right">Action</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    @foreach($requiredDocuments as $doc)
                                        @php
                                            $badgeClass = match ($doc['status'] ?? '') {
                                                'complete' => 'bg-emerald-50 text-emerald-700',
                                                'not_on_file', 'missing' => 'bg-rose-50 text-rose-700',
                                                'rejected' => 'bg-rose-100 text-rose-800',
                                                'expired', 'expiry_missing' => 'bg-amber-50 text-amber-700',
                                                'pending_review' => 'bg-sky-50 text-sky-700',
                                                default => 'bg-slate-100 text-slate-700',
                                            };
                                            $isUploaded = !empty($doc['is_uploaded']);
                                            $isRequired = array_key_exists('required', $doc) ? !empty($doc['required']) : true;
                                        @endphp
                                        <tr class="{{ $isUploaded ? 'bg-white' : 'bg-rose-50/20' }}">
                                            <td class="px-3 py-2 font-semibold text-slate-950">
                                                {{ $doc['title'] ?? '—' }}
                                                @if(!empty($doc['upload_name']))
                                                    <span class="mt-0.5 block text-xs font-normal text-slate-500">{{ $doc['upload_name'] }}</span>
                                                @endif
                                                @if(!empty($doc['verification_notes']))
                                                    <span class="mt-0.5 block text-xs font-normal text-rose-700">{{ $doc['verification_notes'] }}</span>
                                                @endif
                                            </td>
                                            <td class="whitespace-nowrap px-3 py-2 text-slate-600">
                                                {{ $isRequired ? 'Yes' : 'No' }}
                                                @if($isRequired && !empty($doc['required_for']))
                                                    <span class="mt-0.5 block text-[11px] font-normal text-slate-400">{{ $doc['required_for'] }}</span>
                                                @endif
                                            </td>
                                            <td class="whitespace-nowrap px-3 py-2">
                                                <span class="inline-flex whitespace-nowrap rounded-full px-3 py-1 text-xs font-bold {{ $badgeClass }}">{{ $doc['status_label'] ?? 'Needs attention' }}</span>
                                            </td>
                                            <td class="px-3 py-2 text-slate-500">
                                                @if(!empty($doc['latest_uploaded_at']))
                                                    {{ \Carbon\Carbon::parse($doc['latest_uploaded_at'])->format('M j, Y') }}
                                                @else
                                                    <span class="text-slate-400">—</span>
                                                @endif
                                            </td>
                                            <td class="px-3 py-2 text-slate-500">
                                                @if(!empty($doc['latest_expires_at']))
                                                    {{ \Carbon\Carbon::parse($doc['latest_expires_at'])->format('M j, Y') }}
                                                @else
                                                    <span class="text-slate-400">—</span>
                                                @endif
                                            </td>
                                            <td class="px-3 py-2 text-right">
                                                <div class="inline-flex items-center justify-end gap-2">
                                                    @if(!empty($doc['view_url']))
                                                        <a href="{{ $doc['view_url'] }}" target="_blank" rel="noopener" class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-brand-200 text-brand-600 hover:bg-brand-50 hover:text-brand-700" title="View document" aria-label="View document">
                                                            <i class="fa-solid fa-eye"></i>
                                                        </a>
                                                    @endif
                                                    @if(($doc['status'] ?? '') !== 'pending_review' && ($doc['status'] ?? '') !== 'complete')
                                                        <button type="button" @click="openUploadModal(@js($doc['upload_type_id'] ?? null))" class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-brand-200 text-brand-600 hover:bg-brand-50 hover:text-brand-700" title="Upload document" aria-label="Upload document">
                                                            <i class="fa-solid fa-upload"></i>
                                                        </button>
                                                    @elseif(($doc['status'] ?? '') === 'complete' && empty($doc['view_url']))
                                                        <span class="text-xs font-semibold text-emerald-700">Up to date</span>
                                                    @elseif(($doc['status'] ?? '') === 'pending_review')
                                                        <span class="inline-flex h-8 w-8 cursor-not-allowed items-center justify-center rounded-lg border border-slate-200 text-slate-300" title="Awaiting leadership review" aria-label="Upload unavailable">
                                                            <i class="fa-solid fa-upload"></i>
                                                        </span>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        @if($requiredDocumentsPaginator)
                            <div class="mt-4 flex flex-col gap-3 rounded-2xl border border-slate-200 bg-white px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
                                <p class="text-sm text-slate-600">
                                    Showing
                                    <span class="font-semibold text-slate-900">{{ $requiredDocumentsPaginator->firstItem() }}</span>
                                    –
                                    <span class="font-semibold text-slate-900">{{ $requiredDocumentsPaginator->lastItem() }}</span>
                                    of
                                    <span class="font-semibold text-slate-900">{{ $requiredDocumentsFilteredTotal }}</span>
                                    documents
                                </p>
                                @if($requiredDocumentsPaginator->hasPages())
                                    <div class="documents-pagination">
                                        {{ $requiredDocumentsPaginator->onEachSide(1)->links() }}
                                    </div>
                                @endif
                            </div>
                        @endif
                    @endif

                    @if($missingCount === 0 && $completeCount > 0 && ! $hasActiveRequiredFilters)
                        <p class="mt-3 rounded-2xl border border-emerald-100 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">
                            <i class="fa-solid fa-circle-check mr-1"></i>
                            All applicable required documents are on file and verified.
                        </p>
                    @endif
                </div>
            @elseif($hasEmployeeRecord)
                <p class="mb-6 rounded-2xl border border-dashed border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-600">
                    No required document types are configured yet
                    @if($positionTitle)
                        for position <span class="font-semibold text-slate-900">{{ $positionTitle }}</span>
                    @endif.
                </p>
            @endif

            <div>
                <div class="mb-3 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                    <h3 class="flex items-center gap-2 text-sm font-bold uppercase tracking-wide text-slate-500">
                        <i class="fa-solid fa-cloud-arrow-up text-brand-600"></i>
                        {{ config('documents.labels.my_documents') }}
                    </h3>
                    @if($uploadCount > 0 && $documentsPaginator)
                        <p class="text-xs text-slate-500">
                            @if($hasActiveDocumentFilters)
                                Showing {{ $documentsPaginator->firstItem() ?? 0 }}–{{ $documentsPaginator->lastItem() ?? 0 }} of {{ $filteredCount }} matching
                                ({{ $uploadCount }} total on file)
                            @else
                                {{ $uploadCount }} document{{ $uploadCount === 1 ? '' : 's' }} on file
                            @endif
                        </p>
                    @endif
                </div>

                @if($hasEmployeeRecord)
                    <form method="GET" action="{{ route('member.documents') }}" class="mb-4 grid gap-3 rounded-2xl border border-slate-200 bg-slate-50 p-4 sm:grid-cols-2 lg:grid-cols-6" id="documents-filter-form">
                        @if(!empty($requiredDocumentFilters['search']))
                            <input type="hidden" name="rq" value="{{ $requiredDocumentFilters['search'] }}">
                        @endif
                        @if(!empty($requiredDocumentFilters['status']))
                            <input type="hidden" name="rstatus" value="{{ $requiredDocumentFilters['status'] }}">
                        @endif
                        @if(!empty($requiredDocumentFilters['required']))
                            <input type="hidden" name="rrequired" value="{{ $requiredDocumentFilters['required'] }}">
                        @endif
                        @if(($requiredDocumentFilters['sort'] ?? 'name_asc') !== 'name_asc')
                            <input type="hidden" name="rsort" value="{{ $requiredDocumentFilters['sort'] }}">
                        @endif
                        @if((int) ($requiredDocumentFilters['per_page'] ?? 10) !== 10)
                            <input type="hidden" name="rper_page" value="{{ $requiredDocumentFilters['per_page'] }}">
                        @endif
                        <div class="lg:col-span-2">
                            <label for="documents-search" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">Search</label>
                            <input type="search" id="documents-search" name="q" value="{{ $documentFilters['search'] ?? '' }}"
                                placeholder="File name, type, or comments…"
                                class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm focus:border-brand-400 focus:outline-none focus:ring-2 focus:ring-brand-100">
                        </div>
                        <div>
                            <label for="documents-type" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">Type</label>
                            <select id="documents-type" name="type" onchange="this.form.submit()" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm focus:border-brand-400 focus:outline-none focus:ring-2 focus:ring-brand-100">
                                <option value="">All types</option>
                                @foreach($documentTypeOptions as $option)
                                    <option value="{{ $option['value'] }}" @selected(($documentFilters['type'] ?? '') === $option['value'])>{{ $option['label'] }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="documents-status" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">Status</label>
                            <select id="documents-status" name="status" onchange="this.form.submit()" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm focus:border-brand-400 focus:outline-none focus:ring-2 focus:ring-brand-100">
                                <option value="" @selected(empty($documentFilters['status']))>All statuses</option>
                                <option value="pending" @selected(($documentFilters['status'] ?? '') === 'pending')>Pending review</option>
                                <option value="approved" @selected(($documentFilters['status'] ?? '') === 'approved')>Approved</option>
                                <option value="rejected" @selected(($documentFilters['status'] ?? '') === 'rejected')>Rejected</option>
                            </select>
                        </div>
                        <div>
                            <label for="documents-expiry" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">Expiry</label>
                            <select id="documents-expiry" name="expiry" onchange="this.form.submit()" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm focus:border-brand-400 focus:outline-none focus:ring-2 focus:ring-brand-100">
                                <option value="" @selected(empty($documentFilters['expiry']))>All</option>
                                <option value="valid" @selected(($documentFilters['expiry'] ?? '') === 'valid')>Valid</option>
                                <option value="expiring" @selected(($documentFilters['expiry'] ?? '') === 'expiring')>Expiring soon (30 days)</option>
                                <option value="expired" @selected(($documentFilters['expiry'] ?? '') === 'expired')>Expired</option>
                                <option value="none" @selected(($documentFilters['expiry'] ?? '') === 'none')>No expiry date</option>
                            </select>
                        </div>
                        <div>
                            <label for="documents-sort" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">Sort by</label>
                            <select id="documents-sort" name="sort" onchange="this.form.submit()" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm focus:border-brand-400 focus:outline-none focus:ring-2 focus:ring-brand-100">
                                <option value="uploaded_desc" @selected(($documentFilters['sort'] ?? 'uploaded_desc') === 'uploaded_desc')>Uploaded (newest)</option>
                                <option value="uploaded_asc" @selected(($documentFilters['sort'] ?? '') === 'uploaded_asc')>Uploaded (oldest)</option>
                                <option value="name_asc" @selected(($documentFilters['sort'] ?? '') === 'name_asc')>File name (A–Z)</option>
                                <option value="name_desc" @selected(($documentFilters['sort'] ?? '') === 'name_desc')>File name (Z–A)</option>
                                <option value="expires_asc" @selected(($documentFilters['sort'] ?? '') === 'expires_asc')>Expires (soonest)</option>
                                <option value="expires_desc" @selected(($documentFilters['sort'] ?? '') === 'expires_desc')>Expires (latest)</option>
                            </select>
                        </div>
                        <div>
                            <label for="documents-per-page" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">Per page</label>
                            <select id="documents-per-page" name="per_page" onchange="this.form.submit()" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm focus:border-brand-400 focus:outline-none focus:ring-2 focus:ring-brand-100">
                                @foreach([10, 25, 50] as $size)
                                    <option value="{{ $size }}" @selected((int) ($documentFilters['per_page'] ?? 10) === $size)>{{ $size }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="flex flex-wrap items-center gap-2 sm:col-span-2 lg:col-span-6">
                            <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-brand-600 px-4 py-2 text-sm font-bold text-white hover:bg-brand-700">
                                <i class="fa-solid fa-filter text-xs" aria-hidden="true"></i>
                                Apply
                            </button>
                            @if($hasActiveDocumentFilters)
                                <a href="{{ route('member.documents') }}" class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-100">Reset</a>
                            @endif
                            @if($documentsPaginator && $filteredCount > 0)
                                <p class="text-xs text-slate-500 sm:ml-auto">
                                    Showing {{ $documentsPaginator->firstItem() }}–{{ $documentsPaginator->lastItem() }} of {{ $filteredCount }}
                                    @if($hasActiveDocumentFilters)
                                        matching
                                    @endif
                                    @if($uploadCount > 0 && $hasActiveDocumentFilters)
                                        ({{ $uploadCount }} total)
                                    @endif
                                </p>
                            @endif
                        </div>
                    </form>
                @endif

                @if($uploadCount === 0)
                    <div class="rounded-2xl border border-dashed border-slate-200 bg-slate-50 px-4 py-8 text-center text-sm text-slate-500">
                        <p>No documents on your employee file yet.</p>
                        <button type="button" @click="openUploadModal()" class="mt-3 inline-flex items-center gap-2 rounded-xl bg-brand-600 px-4 py-2 text-sm font-bold text-white hover:bg-brand-700">
                            <i class="fa-solid fa-upload"></i>
                            Upload your first document
                        </button>
                    </div>
                @elseif($filteredCount === 0)
                    <p class="rounded-2xl border border-dashed border-slate-200 bg-slate-50 px-4 py-8 text-center text-sm text-slate-500">
                        No documents match your search or filters.
                        <a href="{{ route('member.documents') }}" class="font-bold text-brand-600 hover:text-brand-700">Clear filters</a>
                    </p>
                @else
                    <div class="overflow-x-auto rounded-2xl border border-slate-200">
                        <table class="w-full min-w-[760px] text-left text-sm">
                            <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                                <tr>
                                    <th class="px-4 py-3">File</th>
                                    <th class="px-4 py-3">Type</th>
                                    <th class="px-4 py-3">Uploaded</th>
                                    <th class="px-4 py-3">{{ config('documents.labels.expiration_date') }}</th>
                                    <th class="px-4 py-3">{{ config('documents.labels.need_tracking') }}</th>
                                    <th class="px-4 py-3">{{ config('documents.labels.verification_status') }}</th>
                                    <th class="px-4 py-3 text-right">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach($documents as $upload)
                                    @php
                                        $expiryBadgeClass = match ($upload['expiry_status'] ?? '') {
                                            'expired' => 'bg-rose-50 text-rose-700',
                                            'expiring' => 'bg-amber-50 text-amber-700',
                                            'valid' => 'bg-emerald-50 text-emerald-700',
                                            default => '',
                                        };
                                        $expirationDate = $upload['expiration_date'] ?? $upload['expires_at'] ?? null;
                                        $needTracking = (bool) ($upload['need_tracking'] ?? false);
                                    @endphp
                                    <tr>
                                        <td class="px-4 py-3 font-semibold text-slate-950">
                                            <div class="flex flex-wrap items-center gap-2">
                                                <span>{{ $upload['name'] ?? '—' }}</span>
                                                @if(!empty($upload['is_approved']) && !empty($upload['is_read_only']))
                                                    <span class="inline-flex items-center rounded-full bg-emerald-50 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-emerald-700" title="Approved documents are view-only. Upload a new file to renew.">Read-only</span>
                                                @endif
                                            </div>
                                            @if(!empty($upload['history_count']))
                                                <button
                                                    type="button"
                                                    class="mt-1 text-xs font-semibold text-brand-600 hover:text-brand-700"
                                                    @click="openHistoryModal(@js([
                                                        'name' => $upload['name'] ?? 'Document',
                                                        'type' => $upload['type'] ?? 'Document',
                                                        'current' => [
                                                            'name' => $upload['name'] ?? 'Document',
                                                            'uploaded_at' => $upload['uploaded_at'] ?? null,
                                                            'expires_at' => $upload['expiration_date'] ?? $upload['expires_at'] ?? null,
                                                            'verification_status_label' => $upload['verification_status_label'] ?? null,
                                                            'view_url' => $upload['view_url'] ?? null,
                                                            'download_url' => $upload['download_url'] ?? null,
                                                        ],
                                                        'history' => $upload['history'] ?? [],
                                                    ]))"
                                                >
                                                    View history ({{ $upload['history_count'] }})
                                                </button>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-slate-500">{{ $upload['type'] ?? '—' }}</td>
                                        <td class="px-4 py-3 text-slate-500">{{ $upload['uploaded_at'] ?? '—' }}</td>
                                        <td class="px-4 py-3">
                                            @if(!empty($expirationDate))
                                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-bold {{ $expiryBadgeClass ?: 'bg-slate-100 text-slate-700' }}">
                                                    {{ $expirationDate }}
                                                </span>
                                            @else
                                                <span class="text-slate-400">—</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3">
                                            @if($needTracking)
                                                <span class="inline-flex items-center rounded-full bg-sky-50 px-2.5 py-0.5 text-xs font-bold text-sky-700">Yes</span>
                                            @else
                                                <span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-bold text-slate-600">No</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3">
                                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-bold {{ $upload['verification_badge_class'] ?? 'bg-slate-100 text-slate-600' }}">
                                                {{ $upload['verification_status_label'] ?? '—' }}
                                            </span>
                                            @if(!empty($upload['verification_notes']) && ($upload['verification_status'] ?? '') === \App\Models\Upload::VERIFICATION_REJECTED)
                                                <p class="mt-1 max-w-xs text-xs text-rose-600">{{ $upload['verification_notes'] }}</p>
                                            @endif
                                        </td>
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

                                                @if(!empty($upload['download_url']))
                                                    <a href="{{ $upload['download_url'] }}" class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-brand-200 text-brand-600 hover:bg-brand-50 hover:text-brand-700" title="Download document" aria-label="Download document">
                                                        <i class="fa-solid fa-download"></i>
                                                    </a>
                                                @endif

                                                @if(!empty($upload['edit_url']) && !empty($upload['can_modify']))
                                                    <a href="{{ $upload['edit_url'] }}" class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-brand-200 text-brand-600 hover:bg-brand-50 hover:text-brand-700" title="Edit document" aria-label="Edit document">
                                                        <i class="fa-solid fa-pen-to-square"></i>
                                                    </a>
                                                @elseif(!empty($upload['is_approved']))
                                                    <span class="inline-flex h-8 w-8 cursor-not-allowed items-center justify-center rounded-lg border border-slate-200 text-slate-300" title="Approved documents are read-only. Upload a new version to renew." aria-label="Edit unavailable">
                                                        <i class="fa-solid fa-lock"></i>
                                                    </span>
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

                    @if($documentsPaginator)
                        <div class="mt-4 flex flex-col gap-3 rounded-2xl border border-slate-200 bg-white px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
                            <p class="text-sm text-slate-600">
                                @if($filteredCount > 0)
                                    Showing
                                    <span class="font-semibold text-slate-900">{{ $documentsPaginator->firstItem() }}</span>
                                    –
                                    <span class="font-semibold text-slate-900">{{ $documentsPaginator->lastItem() }}</span>
                                    of
                                    <span class="font-semibold text-slate-900">{{ $filteredCount }}</span>
                                    documents
                                @else
                                    No documents on this page
                                @endif
                            </p>
                            @if($documentsPaginator->hasPages())
                                <div class="documents-pagination">
                                    {{ $documentsPaginator->onEachSide(1)->links() }}
                                </div>
                            @endif
                        </div>
                    @endif
                @endif
            </div>
        @endunless
    </div>

    <template x-teleport="body">
        <div x-show="showHistoryModal" x-cloak class="fixed inset-0 z-[100] flex items-center justify-center p-4 overflow-y-auto">
            <div class="absolute inset-0 bg-black/50" @click="closeHistoryModal()"></div>
            <div class="relative z-10 w-full max-w-xl rounded-2xl bg-white p-6 shadow-xl">
                <div class="mb-4 flex items-center justify-between gap-3">
                    <div>
                        <h3 class="text-lg font-bold text-slate-900">Document history</h3>
                        <p class="mt-1 text-sm text-slate-500" x-text="historyContext.type + ' · ' + historyContext.name"></p>
                    </div>
                    <button type="button" class="text-xl leading-none text-slate-500 hover:text-slate-700" @click="closeHistoryModal()" aria-label="Close">&times;</button>
                </div>

                <div class="space-y-3">
                    <template x-if="historyContext.current">
                        <div class="rounded-xl border border-emerald-100 bg-emerald-50/60 px-4 py-3">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="text-xs font-bold uppercase tracking-wide text-emerald-700">Current version</p>
                                    <p class="mt-1 text-sm font-semibold text-slate-900" x-text="historyContext.current.name"></p>
                                    <p class="mt-1 text-xs text-slate-600">
                                        Uploaded <span x-text="historyContext.current.uploaded_at || '—'"></span>
                                        · Expires <span x-text="historyContext.current.expires_at || '—'"></span>
                                        · <span x-text="historyContext.current.verification_status_label || '—'"></span>
                                    </p>
                                </div>
                                <div class="flex shrink-0 items-center gap-2">
                                    <a x-show="historyContext.current.view_url" :href="historyContext.current.view_url" target="_blank" rel="noopener" class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-brand-200 text-brand-600 hover:bg-white" title="View">
                                        <i class="fa-solid fa-eye text-xs"></i>
                                    </a>
                                    <a x-show="historyContext.current.download_url" :href="historyContext.current.download_url" class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-brand-200 text-brand-600 hover:bg-white" title="Download">
                                        <i class="fa-solid fa-download text-xs"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </template>

                    <template x-if="(historyContext.history || []).length === 0">
                        <p class="rounded-xl border border-dashed border-slate-200 bg-slate-50 px-4 py-6 text-center text-sm text-slate-500">No prior versions preserved yet.</p>
                    </template>

                    <template x-for="prior in (historyContext.history || [])" :key="prior.id">
                        <div class="rounded-xl border border-slate-200 bg-white px-4 py-3">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="text-xs font-bold uppercase tracking-wide text-slate-500">Prior version</p>
                                    <p class="mt-1 text-sm font-semibold text-slate-900" x-text="prior.name"></p>
                                    <p class="mt-1 text-xs text-slate-600">
                                        Uploaded <span x-text="prior.uploaded_at || '—'"></span>
                                        · Expires <span x-text="prior.expires_at || '—'"></span>
                                        · <span x-text="prior.verification_status_label || '—'"></span>
                                    </p>
                                </div>
                                <div class="flex shrink-0 items-center gap-2">
                                    <a x-show="prior.view_url" :href="prior.view_url" target="_blank" rel="noopener" class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-50" title="View prior version">
                                        <i class="fa-solid fa-eye text-xs"></i>
                                    </a>
                                    <a x-show="prior.download_url" :href="prior.download_url" class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-50" title="Download prior version">
                                        <i class="fa-solid fa-download text-xs"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>

                <p class="mt-4 text-xs text-slate-500">Prior versions are preserved with their original expiration dates. Approved documents stay read-only; upload a new file to renew.</p>
            </div>
        </div>
    </template>

    <template x-teleport="body">
        <div x-show="showUploadModal" x-cloak class="fixed inset-0 z-[100] flex items-center justify-center p-4 overflow-y-auto">
            <div class="absolute inset-0 bg-black/50" @click="closeUploadModal()"></div>
            <div class="relative z-10 w-full max-w-2xl rounded-2xl bg-white p-6 shadow-xl">
                <div class="mb-4 flex items-center justify-between">
                    <h3 class="text-lg font-bold text-slate-900">{{ config('documents.labels.upload_modal_title') }}</h3>
                    <button type="button" class="text-xl leading-none text-slate-500 hover:text-slate-700" @click="closeUploadModal()" aria-label="Close">&times;</button>
                </div>
                <form method="POST" :action="uploadUrl" enctype="multipart/form-data" class="space-y-4">
                    <input type="hidden" name="_token" :value="csrf">
                    <input type="hidden" name="redirect_to" value="member.documents">
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-xs font-semibold">{{ config('documents.labels.type') }} <span class="text-rose-600">*</span></label>
                            <select name="upload_type_id" x-model="form.upload_type_id" required class="w-full rounded border border-slate-300 bg-slate-50 px-2 py-1.5 text-sm focus:border-brand-500 focus:outline-none">
                                <option value="">{{ config('documents.labels.select_type') }}</option>
                                <template x-for="group in uploadTypeGroups" :key="group.section">
                                    <optgroup :label="group.section">
                                        <template x-for="type in group.items" :key="type.id">
                                            <option :value="String(type.id)" x-text="type.name"></option>
                                        </template>
                                    </optgroup>
                                </template>
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold">File <span class="text-rose-600">*</span></label>
                            <input type="file" name="document" required class="w-full rounded border border-slate-300 bg-slate-50 px-2 py-1.5 text-sm focus:border-brand-500 focus:outline-none">
                        </div>
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-semibold">{{ config('documents.labels.submission_reason') }} <span class="text-rose-600">*</span></label>
                        <select name="submission_reason" x-model="form.submission_reason" required class="w-full rounded border border-slate-300 bg-slate-50 px-2 py-1.5 text-sm focus:border-brand-500 focus:outline-none">
                            <option value="">Select a reason…</option>
                            <template x-for="(label, key) in submissionReasons" :key="key">
                                <option :value="key" x-text="label"></option>
                            </template>
                        </select>
                        <p class="mt-1 text-xs text-slate-500">{{ config('documents.labels.upload_review_notice') }}</p>
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
            tab: config.initialTab || 'mine',
            facilityFilter: '',
            showUploadModal: false,
            showHistoryModal: false,
            historyContext: {
                name: '',
                type: '',
                current: null,
                history: [],
            },
            uploadUrl: config.uploadUrl,
            uploadTypes: config.uploadTypes || [],
            submissionReasons: config.submissionReasons || {},
            csrf: config.csrf,
            form: {
                upload_type_id: '',
                submission_reason: '',
                effective_start_date: '',
                expires_at: '',
                comments: '',
            },
            get uploadTypeGroups() {
                const groups = {};
                for (const type of this.uploadTypes) {
                    const section = type.section || 'Other';
                    if (!groups[section]) {
                        groups[section] = { section, items: [] };
                    }
                    groups[section].items.push(type);
                }
                return Object.values(groups);
            },
            get requiresExpiry() {
                const selected = this.uploadTypes.find((type) => String(type.id) === String(this.form.upload_type_id));
                return !!(selected && selected.requires_expiry);
            },
            openUploadModal(uploadTypeId = null) {
                this.form.upload_type_id = uploadTypeId ? String(uploadTypeId) : '';
                this.form.submission_reason = '';
                this.form.effective_start_date = '';
                this.form.expires_at = '';
                this.form.comments = '';
                this.showUploadModal = true;
            },
            initFromQuery() {
                const uploadTypeId = new URLSearchParams(window.location.search).get('upload_type_id');
                if (uploadTypeId) {
                    this.openUploadModal(uploadTypeId);
                }
            },
            closeUploadModal() {
                this.showUploadModal = false;
            },
            openHistoryModal(payload) {
                this.historyContext = {
                    name: payload?.name || 'Document',
                    type: payload?.type || 'Document',
                    current: payload?.current || null,
                    history: Array.isArray(payload?.history) ? payload.history : [],
                };
                this.showHistoryModal = true;
            },
            closeHistoryModal() {
                this.showHistoryModal = false;
            },
        };
    }
</script>
