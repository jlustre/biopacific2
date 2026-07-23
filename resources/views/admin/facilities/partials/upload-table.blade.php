@php
    use Illuminate\Support\Facades\Auth;

    $tableScope = $tableScope ?? 'facility';
    $isEmployeeTable = $tableScope === 'employee';
    $compactEmployeeTable = ($isSelfService ?? false) && $isEmployeeTable && isset($employee);
    $isFacilityTable = ! $isEmployeeTable;
    $user = Auth::user();
    $canChooseFacility = ! $isEmployeeTable && $user && ($user->hasRole('admin') || $user->hasRole('rdhr'));
    $userFacility = ! $isEmployeeTable && $user && $user->facility ? $user->facility : null;
    $showFacilityColumn = ! $compactEmployeeTable && ! ($userFacility && ! $canChooseFacility);
    $facilityTableColspan = $showFacilityColumn ? 7 : 6;
    $employeeTableColspan = $compactEmployeeTable ? 6 : ($showFacilityColumn ? 9 : 8);
    $tableColspan = $isEmployeeTable ? $employeeTableColspan : $facilityTableColspan;
    $filterFormAction = $isEmployeeTable && isset($employee)
        ? (($isSelfService ?? false)
            ? route('employment.portal', ['tab' => 'documents'])
            : route('admin.employees.edit', $employee))
        : (isset($facility) ? route('admin.facility.documents', ['facility' => $facility->slug ?? $facility->id]) : null);
    $clearFilterUrl = $isEmployeeTable && isset($employee)
        ? (($isSelfService ?? false)
            ? route('employment.portal', array_filter([
                'tab' => 'documents',
                'facility' => request('facility'),
            ]))
            : route('admin.employees.edit', $employee) . '?tab=documents')
        : (isset($facility) ? route('admin.facility.documents', ['facility' => $facility->slug ?? $facility->id]) : url()->current());

    $documentsViewTemplate = isset($employee)
        ? ($employeeFormRoutes['documents_view'] ?? route('admin.employees.documents.view', [$employee->id, '__ID__']))
        : null;
    $documentsDeleteTemplate = isset($employee)
        ? ($employeeFormRoutes['documents_delete'] ?? route('admin.employees.documents.delete', [$employee->id, '__ID__']))
        : null;

    $filterFacilityId = null;
    if ($isFacilityTable) {
        if ($canChooseFacility && request('facility_id')) {
            $filterFacilityId = (int) request('facility_id');
        } elseif ($userFacility) {
            $filterFacilityId = (int) $userFacility->id;
        } elseif (isset($facility) && $facility) {
            $filterFacilityId = (int) $facility->id;
        }
    }

    $filterOptionsQuery = \App\Models\Upload::query()->current();
    if ($isEmployeeTable && isset($employee)) {
        $filterOptionsQuery->where('employee_num', $employee->employee_num);
    } elseif ($filterFacilityId) {
        $filterOptionsQuery->where('facility_id', $filterFacilityId);
    }

    $uploaderFilterOptions = \App\Models\User::query()
        ->whereIn('id', (clone $filterOptionsQuery)->whereNotNull('user_id')->distinct()->pluck('user_id'))
        ->orderBy('name')
        ->get(['id', 'name']);

    $approverFilterOptions = \App\Models\User::query()
        ->whereIn('id', (clone $filterOptionsQuery)
            ->where('verification_status', \App\Models\Upload::VERIFICATION_APPROVED)
            ->whereNotNull('verified_by_user_id')
            ->distinct()
            ->pluck('verified_by_user_id'))
        ->orderBy('name')
        ->get(['id', 'name']);

    $employeeFilterOptions = collect($employees ?? []);
    if ($isFacilityTable && $employeeFilterOptions->isEmpty() && $filterFacilityId) {
        $employeeFilterOptions = \App\Models\BPEmployee::query()
            ->whereHas('assignments', fn ($q) => $q->where('facility_id', $filterFacilityId))
            ->orderedByName()
            ->get();
    }

    $displayFacilityName = null;
    if ($isFacilityTable) {
        if ($canChooseFacility) {
            if (request('facility_id')) {
                $displayFacilityName = \App\Models\Facility::find(request('facility_id'))?->name;
            } elseif (isset($facility) && $facility) {
                $displayFacilityName = $facility->name;
            } else {
                $displayFacilityName = 'All Facilities';
            }
        } elseif ($userFacility) {
            $displayFacilityName = $userFacility->name;
        } elseif (isset($facility) && $facility) {
            $displayFacilityName = $facility->name;
        }
    }

    if ($compactEmployeeTable) {
        $documentsFacility = $employee->currentAssignment?->facility;

        if (! $documentsFacility && request('facility')) {
            $documentsFacility = \App\Models\Facility::find(request('facility'));
        }

        if (! $documentsFacility && $employee->employee_num) {
            $documentsFacility = \App\Models\Upload::query()
                ->where('employee_num', $employee->employee_num)
                ->whereNotNull('facility_id')
                ->with('facility')
                ->latest('uploaded_at')
                ->first()
                ?->facility;
        }
    }
@endphp
<div id="upload-table" class="p-6 bg-white rounded shadow" tabindex="-1">
    @if($isFacilityTable && $displayFacilityName)
    <div class="mb-4 pb-3 border-b border-teal-200">
        <p class="text-xs font-semibold uppercase tracking-wide text-teal-700">Facility</p>
        <h2 class="text-xl font-bold text-teal-900">{{ $displayFacilityName }}</h2>
    </div>
    @endif
    <form id="upload-table-filter-form" method="GET" @if($filterFormAction) action="{{ $filterFormAction }}" @endif class="mb-4">
        @if($isEmployeeTable)
            <input type="hidden" name="tab" value="documents">
        @endif
        @if($compactEmployeeTable && request('facility'))
            <input type="hidden" name="facility" value="{{ request('facility') }}">
        @endif
        @if($isFacilityTable && $userFacility && ! $canChooseFacility)
            <input type="hidden" name="facility_id" value="{{ $userFacility->id }}">
        @endif
        <div x-data="{
            selectedType: '{{ request('upload_type_id') }}',
            showExpiry: true,
            updateShowExpiry() {
                if (this.selectedType === '' || this.selectedType === null) {
                    this.showExpiry = true;
                } else {
                    const sel = document.querySelector('[name=upload_type_id]');
                    const opt = sel ? sel.querySelector('option:checked') : null;
                    this.showExpiry = !opt || opt.dataset.requiresExpiry === '1';
                }
            }
        }"
        x-init="updateShowExpiry(); $watch('selectedType', () => updateShowExpiry())"
        class="flex flex-wrap items-end gap-4 w-full">
            <div class="flex-1 min-w-[180px]">
                <label class="block mb-1 text-xs font-semibold">Search by file name</label>
                <input type="text" name="search" value="{{ request('search') }}" class="w-full px-2 py-1 border-teal-300 rounded border-1 focus:border-teal-600 form-input" placeholder="Search document file name...">
            </div>
            <div>
                <label class="block mb-1 text-xs font-semibold">{{ config('documents.labels.type') }}</label>
                <select name="upload_type_id" x-model="selectedType" @change="updateShowExpiry()" class="px-2 py-1 border-teal-300 rounded border-1 focus:border-teal-600 form-select">
                    <option value="">All Types</option>
                    @foreach(App\Models\UploadType::query()->orderedForDisplay()->get() as $utype)
                        <option value="{{ $utype->id }}" data-requires-expiry="{{ $utype->requires_expiry ? '1' : '0' }}" @if(request('upload_type_id')==$utype->id) selected @endif>
                            {{ $utype->name }}@if($utype->requires_expiry) **@endif
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block mb-1 text-xs font-semibold">Expires In</label>
                <select name="expiry_range" class="px-2 py-1 border-teal-300 rounded border-1 focus:border-teal-600 form-select">
                    <option value="" @if(request('expiry_range')=='') selected @endif>All Tracked</option>
                    <option value="expired" @if(request('expiry_range')=='expired') selected @endif class="bg-red-100 text-red-700 font-bold">Expired</option>
                    <option value="30" @if(request('expiry_range')=='30') selected @endif class="bg-yellow-100 text-yellow-800 font-bold">0-30 days</option>
                    <option value="60" @if(request('expiry_range')=='60') selected @endif class="bg-orange-100 text-orange-800 font-bold">31-60 days</option>
                    <option value="90" @if(request('expiry_range')=='90') selected @endif class="bg-blue-100 text-blue-800 font-bold">61-90 days</option>
                    <option value="120" @if(request('expiry_range')=='120') selected @endif class="bg-purple-100 text-purple-800 font-bold">91-120 days</option>
                    <option value="121" @if(request('expiry_range')=='121') selected @endif class="bg-green-100 text-green-800 font-bold">120+ days</option>
                </select>
            </div>
        </div>
        <div class="flex flex-wrap items-end gap-4 w-full mt-3 pt-3 border-t border-gray-100">
            @if($canChooseFacility)
            <div>
                <label class="block mb-1 text-xs font-semibold">Filter by Facility</label>
                <select name="facility_id" class="px-2 py-1 border-teal-300 rounded border-1 focus:border-teal-600 form-select min-w-[180px]">
                    <option value="">All Facilities</option>
                    @foreach(App\Models\Facility::orderBy('name')->get() as $fac)
                    <option value="{{ $fac->id }}" @if(request('facility_id')==$fac->id) selected @endif>{{ $fac->name }}</option>
                    @endforeach
                </select>
            </div>
            @endif
            @if($isFacilityTable)
            <div>
                <label class="block mb-1 text-xs font-semibold">Employee</label>
                <select name="employee_num" class="px-2 py-1 border-teal-300 rounded border-1 focus:border-teal-600 form-select min-w-[180px]">
                    <option value="">All employees</option>
                    @foreach($employeeFilterOptions as $emp)
                        <option value="{{ $emp->employee_num ?? $emp['employee_num'] }}" @if(request('employee_num') == ($emp->employee_num ?? $emp['employee_num'])) selected @endif>
                            {{ ($emp instanceof \App\Models\BPEmployee ? $emp->formalName() : ($emp['name'] ?? '')) }} ({{ $emp->employee_num ?? $emp['employee_num'] }})
                        </option>
                    @endforeach
                </select>
            </div>
            @endif
            <div>
                <label class="block mb-1 text-xs font-semibold">Uploaded By</label>
                <select name="uploaded_by" class="px-2 py-1 border-teal-300 rounded border-1 focus:border-teal-600 form-select min-w-[160px]">
                    <option value="">All uploaders</option>
                    @foreach($uploaderFilterOptions as $uploader)
                        <option value="{{ $uploader->id }}" @if((string) request('uploaded_by') === (string) $uploader->id) selected @endif>{{ $uploader->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block mb-1 text-xs font-semibold">Upload Date From</label>
                <input type="date" name="uploaded_from" value="{{ request('uploaded_from') }}" class="px-2 py-1 border-teal-300 rounded border-1 focus:border-teal-600 form-input">
            </div>
            <div>
                <label class="block mb-1 text-xs font-semibold">Upload Date To</label>
                <input type="date" name="uploaded_to" value="{{ request('uploaded_to') }}" class="px-2 py-1 border-teal-300 rounded border-1 focus:border-teal-600 form-input">
            </div>
            <div>
                <label class="block mb-1 text-xs font-semibold">Approved By</label>
                <select name="approved_by" class="px-2 py-1 border-teal-300 rounded border-1 focus:border-teal-600 form-select min-w-[160px]">
                    <option value="">All approvers</option>
                    @foreach($approverFilterOptions as $approver)
                        <option value="{{ $approver->id }}" @if((string) request('approved_by') === (string) $approver->id) selected @endif>{{ $approver->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block mb-1 text-xs font-semibold">Approved Date From</label>
                <input type="date" name="approved_from" value="{{ request('approved_from') }}" class="px-2 py-1 border-teal-300 rounded border-1 focus:border-teal-600 form-input">
            </div>
            <div>
                <label class="block mb-1 text-xs font-semibold">Approved Date To</label>
                <input type="date" name="approved_to" value="{{ request('approved_to') }}" class="px-2 py-1 border-teal-300 rounded border-1 focus:border-teal-600 form-input">
            </div>
            @if($isEmployeeTable)
            <div>
                <label class="block mb-1 text-xs font-semibold">Status</label>
                <select name="verification_status" class="px-2 py-1 border-teal-300 rounded border-1 focus:border-teal-600 form-select min-w-[160px]">
                    <option value="">All statuses</option>
                    <option value="none" @if(request('verification_status') === 'none') selected @endif>Not submitted</option>
                    <option value="{{ \App\Models\Upload::VERIFICATION_PENDING }}" @if(request('verification_status') === \App\Models\Upload::VERIFICATION_PENDING) selected @endif>Pending for Approval</option>
                    <option value="{{ \App\Models\Upload::VERIFICATION_APPROVED }}" @if(request('verification_status') === \App\Models\Upload::VERIFICATION_APPROVED) selected @endif>Approved</option>
                    <option value="{{ \App\Models\Upload::VERIFICATION_REJECTED }}" @if(request('verification_status') === \App\Models\Upload::VERIFICATION_REJECTED) selected @endif>Rejected</option>
                </select>
            </div>
            @endif
            <div class="flex items-end gap-2 pb-0.5">
                <button type="submit" class="px-3 py-1 font-semibold text-white bg-teal-600 rounded cursor-pointer hover:bg-teal-700">Filter</button>
                <a href="{{ $clearFilterUrl }}" class="px-3 py-1 font-semibold text-gray-700 bg-gray-100 rounded hover:bg-gray-200">Clear Filters</a>
            </div>
        </div>
    </form>
    <!-- Expiry Color Legend -->
    <div class="w-full flex justify-center">
        <div class="flex flex-wrap items-center gap-4 mb-4 text-xs">
            <span class="text-teal-700 font-bold">Expiring In:</span>
            <span class="bg-red-100 text-red-700 font-bold px-2 py-1 rounded">Expired</span>
            <span class="bg-yellow-100 text-yellow-800 font-bold px-2 py-1 rounded">0-30 days</span>
            <span class="bg-orange-100 text-orange-800 font-bold px-2 py-1 rounded">31-60 days</span>
            <span class="bg-blue-100 text-blue-800 font-bold px-2 py-1 rounded">61-90 days</span>
            <span class="bg-purple-100 text-purple-800 font-bold px-2 py-1 rounded">91-120 days</span>
            <span class="bg-green-100 text-green-800 font-bold px-2 py-1 rounded">120+ days</span>
        </div>
    </div>
    @if($compactEmployeeTable)
    <div class="mb-4 rounded-lg border border-teal-200 bg-teal-50 px-4 py-3 text-sm text-gray-800">
        <dl class="grid grid-cols-1 gap-2 sm:grid-cols-3">
            <div>
                <dt class="text-xs font-semibold uppercase tracking-wide text-teal-800">Employee</dt>
                <dd class="font-medium">{{ $employee->formalName() }}</dd>
            </div>
            <div>
                <dt class="text-xs font-semibold uppercase tracking-wide text-teal-800">Employee #</dt>
                <dd class="font-medium">{{ $employee->employee_num }}</dd>
            </div>
            <div>
                <dt class="text-xs font-semibold uppercase tracking-wide text-teal-800">Facility</dt>
                <dd class="font-medium">{{ $documentsFacility->name ?? '—' }}</dd>
            </div>
        </dl>
    </div>
    @endif
    <table class="min-w-full border border-gray-200 table-auto">
        <thead>
            <tr class="bg-gray-100">
                <th class="px-3 py-2 border text-sm">Type</th>
                @unless($compactEmployeeTable)
                @if($showFacilityColumn)
                <th class="px-3 py-2 border text-sm">Facility</th>
                @endif
                @if($isEmployeeTable)
                <th class="px-3 py-2 border text-sm">Employee #</th>
                <th class="px-3 py-2 border text-sm">Employee Name</th>
                @else
                <th class="px-3 py-2 border text-sm">Employee</th>
                <th class="px-3 py-2 border text-sm">Uploaded By / Date</th>
                <th class="px-3 py-2 border text-sm">Approved By / Date</th>
                @endif
                @endunless
                @if($isEmployeeTable)
                <th class="px-3 py-2 border text-sm">Uploaded By / Date</th>
                <th class="px-3 py-2 border text-sm">Status</th>
                <th class="px-3 py-2 border text-sm">Approved By / Date</th>
                @endif
                <th class="px-3 py-2 border text-sm">Expires</th>
                <th class="px-3 py-2 border text-sm">Actions</th>
            </tr>
        </thead>
        <tbody>
            @php
            $query = App\Models\Upload::with(['facility','user','uploadType','employee','verifiedBy'])
                ->current();

            if ($isEmployeeTable && isset($employee)) {
                $query->where('employee_num', $employee->employee_num);
            } elseif($canChooseFacility) {
                if(request('facility_id')) $query->where('facility_id', request('facility_id'));
            } elseif (isset($facility) && $facility) {
                $query->where('facility_id', $facility->id);
            } elseif($userFacility) {
                $query->where('facility_id', $userFacility->id);
            }

            if(request('search')) {
                $search = trim(request('search'));
                $like = '%' . mb_strtolower($search) . '%';
                if ($isEmployeeTable) {
                    $query->whereRaw('LOWER(original_filename) LIKE ?', [$like]);
                } else {
                    $query->whereRaw('LOWER(original_filename) LIKE ?', [$like]);
                }
            }

            if ($isFacilityTable && request('employee_num')) {
                $query->where('employee_num', request('employee_num'));
            }

            if (request('uploaded_by')) {
                $query->where('user_id', request('uploaded_by'));
            }

            if (request('uploaded_from')) {
                $query->whereDate('uploaded_at', '>=', request('uploaded_from'));
            }

            if (request('uploaded_to')) {
                $query->whereDate('uploaded_at', '<=', request('uploaded_to'));
            }

            if (request('approved_by')) {
                $query->where('verified_by_user_id', request('approved_by'))
                    ->where('verification_status', \App\Models\Upload::VERIFICATION_APPROVED);
            }

            if (request('approved_from')) {
                $query->where('verification_status', \App\Models\Upload::VERIFICATION_APPROVED)
                    ->whereDate('verified_at', '>=', request('approved_from'));
            }

            if (request('approved_to')) {
                $query->where('verification_status', \App\Models\Upload::VERIFICATION_APPROVED)
                    ->whereDate('verified_at', '<=', request('approved_to'));
            }

            if ($isEmployeeTable && request('verification_status')) {
                if (request('verification_status') === 'none') {
                    $query->where(function ($statusQuery) {
                        $statusQuery->whereNull('verification_status')
                            ->orWhere('verification_status', '');
                    });
                } else {
                    $query->where('verification_status', request('verification_status'));
                }
            }

            if(request('upload_type_id')) $query->where('upload_type_id', request('upload_type_id'));
            $now = now();
            $expiryRange = request('expiry_range');
            $selectedTypeId = request('upload_type_id');
            $showExpiry = false;
            if ($selectedTypeId) {
                $utype = App\Models\UploadType::find($selectedTypeId);
                if ($utype && $utype->requires_expiry) {
                    $showExpiry = true;
                }
            } else {
                $showExpiry = true; // All Types
            }
            if ($showExpiry && $expiryRange !== null) {
                if ($expiryRange === 'expired') {
                    $query->whereNotNull('expires_at')->whereDate('expires_at', '<', $now->toDateString());
                } elseif ($expiryRange === '30') {
                    $query->whereNotNull('expires_at')
                        ->whereDate('expires_at', '>=', $now->toDateString())
                        ->whereDate('expires_at', '<=', $now->copy()->addDays(30)->toDateString());
                } elseif ($expiryRange === '60') {
                    $query->whereNotNull('expires_at')
                        ->whereDate('expires_at', '>', $now->copy()->addDays(30)->toDateString())
                        ->whereDate('expires_at', '<=', $now->copy()->addDays(60)->toDateString());
                } elseif ($expiryRange === '90') {
                    $query->whereNotNull('expires_at')
                        ->whereDate('expires_at', '>', $now->copy()->addDays(60)->toDateString())
                        ->whereDate('expires_at', '<=', $now->copy()->addDays(90)->toDateString());
                } elseif ($expiryRange === '120') {
                    $query->whereNotNull('expires_at')
                        ->whereDate('expires_at', '>', $now->copy()->addDays(90)->toDateString())
                        ->whereDate('expires_at', '<=', $now->copy()->addDays(120)->toDateString());
                } elseif ($expiryRange === '121') {
                    $query->whereNotNull('expires_at')
                        ->whereDate('expires_at', '>', $now->copy()->addDays(120)->toDateString());
                } elseif ($expiryRange === '') {
                    $query->whereNotNull('expires_at');
                }
            }
            $uploads = $query->latest()->paginate(15);
            @endphp
            @forelse($uploads as $upload)
            @if($upload && $upload->id && $upload->uploadType && ($upload->facility || $isEmployeeTable))
            <tr>
                <td class="px-3 py-2 border text-xs">{{ $upload->uploadType->name ?? '-' }}</td>
                @unless($compactEmployeeTable)
                @if($showFacilityColumn)
                <td class="px-3 py-2 border text-xs">{{ $upload->facility->name ?? '-' }}</td>
                @endif
                @if($isEmployeeTable)
                <td class="px-3 py-2 border text-xs">{{ $upload->employee_num ?? '-' }}</td>
                <td class="px-3 py-2 border text-xs">
                    @if($upload->employee)
                        {{ $upload->employee->formalName() }}
                    @endif
                </td>
                @else
                <td class="px-3 py-2 border text-xs">
                    @if($upload->employee)
                        <div>{{ $upload->employee->formalName() }}</div>
                        <div class="text-gray-500 mt-0.5">{{ $upload->employee_num }}</div>
                    @else
                        {{ $upload->employee_num ?? '—' }}
                    @endif
                </td>
                <td class="px-3 py-2 border text-xs">
                    <div>{{ $upload->user->name ?? '—' }}</div>
                    @if($upload->uploaded_at)
                        <div class="text-gray-500 mt-0.5 whitespace-nowrap">{{ $upload->uploaded_at->timezone(config('app.timezone'))->format('Y-m-d g:i A') }}</div>
                    @endif
                </td>
                <td class="px-3 py-2 border text-xs">
                    @if($upload->verification_status === \App\Models\Upload::VERIFICATION_APPROVED)
                        <div>{{ $upload->verifiedBy->name ?? '—' }}</div>
                        @if($upload->verified_at)
                            <div class="text-gray-500 mt-0.5 whitespace-nowrap">{{ $upload->verified_at->timezone(config('app.timezone'))->format('Y-m-d g:i A') }}</div>
                        @endif
                    @else
                        <span class="text-gray-400">—</span>
                    @endif
                </td>
                @endif
                @endunless
                @if($isEmployeeTable)
                <td class="px-3 py-2 border text-xs">
                    <div>{{ $upload->user->name ?? '—' }}</div>
                    @if($upload->uploaded_at)
                        <div class="text-gray-500 mt-0.5 whitespace-nowrap">{{ $upload->uploaded_at->timezone(config('app.timezone'))->format('Y-m-d g:i A') }}</div>
                    @else
                        <div class="text-gray-400 mt-0.5 italic">—</div>
                    @endif
                </td>
                <td class="px-3 py-2 border text-xs">
                    @if($upload->verification_status)
                        @php
                            $statusClass = match ($upload->verification_status) {
                                \App\Models\Upload::VERIFICATION_APPROVED => 'bg-green-100 text-green-800',
                                \App\Models\Upload::VERIFICATION_REJECTED => 'bg-red-100 text-red-800',
                                default => 'bg-yellow-100 text-yellow-800',
                            };
                        @endphp
                        <span class="inline-block px-2 py-0.5 rounded text-xs font-semibold {{ $statusClass }}">
                            {{ $upload->verificationStatusLabel() }}
                        </span>
                        @if($upload->submissionReasonLabel())
                            <div class="text-gray-500 mt-1">{{ $upload->submissionReasonLabel() }}</div>
                        @endif
                        @if($upload->verification_status === \App\Models\Upload::VERIFICATION_REJECTED && $upload->verification_notes)
                            <div class="text-red-600 mt-1" title="{{ $upload->verification_notes }}">Note: {{ \Illuminate\Support\Str::limit($upload->verification_notes, 60) }}</div>
                        @endif
                    @else
                        <span class="text-gray-500 italic">Not submitted</span>
                    @endif
                </td>
                <td class="px-3 py-2 border text-xs">
                    @if($upload->verification_status === \App\Models\Upload::VERIFICATION_APPROVED)
                        <div>{{ $upload->verifiedBy->name ?? '—' }}</div>
                        @if($upload->verified_at)
                            <div class="text-gray-500 mt-0.5 whitespace-nowrap">{{ $upload->verified_at->timezone(config('app.timezone'))->format('Y-m-d g:i A') }}</div>
                        @endif
                    @else
                        <span class="text-gray-400">—</span>
                    @endif
                </td>
                @endif
                <td class="px-1 py-1 border text-xs text-center text-nowrap">
                    @if($upload->expires_at)
                        @php
                            $expires = \Carbon\Carbon::parse($upload->expires_at);
                            $now = now();
                            $days = $now->diffInDays($expires, false); // positive if future, negative if past
                            if ($expires->isPast()) {
                                $color = 'bg-red-100 text-red-700 font-semibold px-1 py-1 rounded';
                            } elseif ($days <= 30) {
                                $color = 'bg-yellow-100 text-yellow-800 font-semibold px-1 py-1 rounded';
                            } elseif ($days <= 60) {
                                $color = 'bg-orange-100 text-orange-800 font-semibold px-1 py-1 rounded';
                            } elseif ($days <= 90) {
                                $color = 'bg-blue-100 text-blue-800 font-semibold px-1 py-1 rounded';
                            } elseif ($days <= 120) {
                                $color = 'bg-purple-100 text-purple-800 font-semibold px-1 py-1 rounded';
                            } else {
                                $color = 'bg-green-100 text-green-800 font-semibold px-1 py-1 rounded';
                            }
                        @endphp
                        <span class="{{ $color }} text-xs">{{ $expires->format('Y-m-d') }}</span>
                    @else
                        <span class="text-gray-500 italic text-xs">N/A</span>
                    @endif
                </td>
                <td class="px-2 py-2 border text-xs flex items-center space-x-2">
                    @if($isEmployeeTable && isset($employee))
                    @php
                        $employeeDocumentViewUrl = str_replace(
                            '__ID__',
                            (string) $upload->id,
                            $documentsViewTemplate
                                ?? route('admin.employees.documents.view', [$employee->id, '__ID__'])
                        );
                        $isPdfDocument = str_ends_with(strtolower((string) ($upload->original_filename ?? '')), '.pdf');
                    @endphp
                    <a href="{{ ($isSelfService ?? false) ? route('employment.documents.download', ['document' => $upload->id]) : route('admin.employees.documents.download', [$employee->id, $upload->id]) }}" title="Download" class="text-blue-600 hover:text-blue-800">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5m0 0l5-5m-5 5V4" /></svg>
                    </a>
                    <a href="{{ $employeeDocumentViewUrl }}" title="{{ $isPdfDocument ? 'View PDF' : 'View' }}" target="_blank" rel="noopener noreferrer" class="{{ $isPdfDocument ? 'text-rose-600 hover:text-rose-800' : 'text-green-600 hover:text-green-800' }}">
                        @if($isPdfDocument)
                            <i class="fas fa-file-pdf text-lg leading-none" aria-hidden="true"></i>
                            <span class="sr-only">View PDF</span>
                        @else
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                        @endif
                    </a>
                    @if($upload->isOwnedBy($user) && ((!($isSelfService ?? false)) || $upload->employeeCanModify($user)))
                    <button type="button" title="Edit" class="text-green-600 hover:text-green-800 bg-transparent border-none p-0 m-0" @click="startEdit({
                        id: {{ $upload->id }},
                        upload_type_id: '{{ $upload->upload_type_id }}',
                        effective_start_date: '{{ $upload->effective_start_date ?? '' }}',
                        expires_at: '{{ $upload->expires_at ?? '' }}',
                        comments: @js($upload->comments),
                    })">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                    </button>
                    @elseif(($isSelfService ?? false) && $upload->isApproved())
                    <span title="Approved documents are read-only. Upload a new version to renew." class="text-gray-400 cursor-not-allowed">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
                    </span>
                    @endif
                    @else
                    <a href="{{ route('admin.facility.uploads.download', ['facility' => $upload->facility_id, 'upload' => $upload->id]) }}" title="Download" class="text-blue-600 hover:text-blue-800">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5m0 0l5-5m-5 5V4" /></svg>
                    </a>
                    <a href="{{ route('admin.facility.uploads.view', ['facility' => $upload->facility_id, 'upload' => $upload->id]) }}" title="View" target="_blank" class="text-green-600 hover:text-green-800">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                    </a>
                    <a href="{{ route('admin.facility.documents', ['facility' => $facility->id, 'edit' => $upload->id]) }}" title="Edit" class="text-green-600 hover:text-green-800">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                    </a>
                    @endif
                    @php
                        $isSubmissionNotify = ($isSelfService ?? false) && $isEmployeeTable && isset($employee) && $upload->isOwnedBy($user) && $upload->canSubmitForVerification();
                        $isExpiryNotify = ! $isSubmissionNotify && $upload->canSendExpiryNotification();
                        $showNotifyButton = $isSubmissionNotify || $isExpiryNotify;
                    @endphp
                    @if($showNotifyButton)
                        @php
                            $notifyEmail = null;
                            if ($isSubmissionNotify) {
                                $reviewerEmails = $upload->facility
                                    ? \App\Support\UploadNotificationContext::facilityDocumentReviewerEmails($upload->facility)
                                    : [];
                                $notifyEmail = $reviewerEmails !== [] ? implode(', ', $reviewerEmails) : null;
                            } elseif ($upload->employee) {
                                foreach ([$upload->employee->email, $upload->employee->user?->email] as $candidate) {
                                    $candidate = trim((string) $candidate);
                                    if ($candidate !== '' && filter_var($candidate, FILTER_VALIDATE_EMAIL)) {
                                        $notifyEmail = $candidate;
                                        break;
                                    }
                                }
                            }
                            $notifyPreviewUrl = ($isEmployeeTable ?? false) && isset($employee)
                                ? (($isSelfService ?? false)
                                    ? route('employment.documents.notify.preview', ['document' => $upload->id])
                                    : route('admin.employees.documents.notify.preview', [$employee->id, $upload->id]))
                                : route('admin.facility.uploads.notify.preview', ['facility' => $upload->facility_id, 'upload' => $upload->id]);
                            $notifySendUrl = ($isEmployeeTable ?? false) && isset($employee)
                                ? (($isSelfService ?? false)
                                    ? route('employment.documents.notify', ['document' => $upload->id])
                                    : route('admin.employees.documents.notify', [$employee->id, $upload->id]))
                                : route('admin.facility.uploads.notify', ['facility' => $upload->facility_id, 'upload' => $upload->id]);
                        @endphp
                        <button type="button"
                            title="{{ $isSubmissionNotify ? ($notifyEmail ? 'Submit for leadership review (' . $notifyEmail . ')' : 'No DSD, DON, or administrator email on file') : ($notifyEmail ? 'Preview and send notification to ' . $notifyEmail : 'Employee has no email on file') }}"
                            class="text-teal-600 hover:text-teal-800 bg-transparent border-none p-0 m-0 disabled:opacity-40 disabled:cursor-not-allowed"
                            @disabled(!$notifyEmail)
                            data-upload-notify-mode="{{ $isSubmissionNotify ? 'submission' : 'expiry' }}"
                            data-upload-notify-preview="{{ $notifyPreviewUrl }}"
                            data-upload-notify-send="{{ $notifySendUrl }}"
                            onclick="openUploadNotifyModal(this)">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                            </svg>
                            <span class="sr-only">{{ $isSubmissionNotify ? 'Submit for review' : 'Send notification' }}</span>
                        </button>
                    @endif
                    @if($isEmployeeTable && isset($employee) && $upload->canVerifyDocument($user))
                        <form action="{{ route('admin.employees.documents.approve', [$employee->id, $upload->id]) }}" method="POST" class="inline" onsubmit="return confirm('Approve this document?');">
                            @csrf
                            <button type="submit" title="Approve document" class="text-green-700 hover:text-green-900 bg-transparent border-none p-0 m-0">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            </button>
                        </form>
                        <button type="button" title="Reject document" class="text-red-700 hover:text-red-900 bg-transparent border-none p-0 m-0" onclick="openUploadRejectModal('{{ route('admin.employees.documents.reject', [$employee->id, $upload->id]) }}', '{{ addslashes($upload->original_filename) }}')">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        </button>
                    @endif
                    @if($isEmployeeTable && isset($employee) && $upload->isOwnedBy($user) && ((!($isSelfService ?? false)) || $upload->employeeCanModify($user)))
                    <form action="{{ str_replace('__ID__', (string) $upload->id, $documentsDeleteTemplate ?? '#') }}" method="POST" class="inline" onsubmit="return confirm('Delete this document?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" title="Delete" class="text-red-600 hover:text-red-800 bg-transparent border-none p-0 m-0">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                    </form>
                    @elseif(! $isEmployeeTable)
                    <form action="{{ route('admin.facility.uploads.destroy', ['facility' => $upload->facility_id, 'upload' => $upload->id]) }}" method="POST" class="inline" onsubmit="return confirm('Delete this upload?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" title="Delete" class="text-red-600 hover:text-red-800 bg-transparent border-none p-0 m-0">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                    </form>
                    @endif
                </td>
            </tr>
            @endif
            @empty
            <tr>
                <td colspan="{{ $tableColspan }}" class="py-6 text-center text-gray-500">No uploads found.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
    <div class="mt-4">{{ $uploads->withQueryString()->links() }}</div>

    <div id="uploadNotifyModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4" role="dialog" aria-modal="true" aria-labelledby="uploadNotifyModalTitle">
        <div class="bg-white rounded-xl shadow-xl w-full max-w-2xl max-h-[90vh] overflow-y-auto p-6 relative">
            <button type="button" onclick="closeUploadNotifyModal()" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 text-xl leading-none" aria-label="Close">&times;</button>
            <h3 id="uploadNotifyModalTitle" class="text-xl font-bold text-gray-900 mb-1">Send document notification</h3>
            <p id="uploadNotifyModalMeta" class="text-sm text-gray-500 mb-4"></p>
            <div id="uploadNotifyModalLoading" class="hidden py-8 text-center text-teal-700 font-semibold">Loading preview…</div>
            <div id="uploadNotifyModalError" class="hidden mb-4 rounded border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700"></div>
            <form id="uploadNotifyModalForm" method="POST" class="hidden space-y-4">
                @csrf
                <div id="uploadNotifyReasonWrap" class="hidden">
                    <label for="uploadNotifyReason" class="block mb-1 text-xs font-semibold text-gray-700">Reason for upload <span class="text-red-600">*</span></label>
                    <select name="submission_reason" id="uploadNotifyReason" class="w-full px-3 py-2 border border-teal-300 rounded focus:border-teal-600 focus:ring-teal-500 text-sm">
                        <option value="">Select a reason…</option>
                    </select>
                    <p class="mt-1 text-xs text-gray-500">Your DSD will use this reason when verifying and approving the document.</p>
                </div>
                <div>
                    <label for="uploadNotifyTo" class="block mb-1 text-xs font-semibold text-gray-700">To</label>
                    <input type="text" id="uploadNotifyTo" readonly class="w-full px-3 py-2 bg-gray-100 border border-gray-300 rounded text-sm text-gray-700 cursor-not-allowed">
                </div>
                <div id="uploadNotifySubjectWrap">
                    <label for="uploadNotifySubject" class="block mb-1 text-xs font-semibold text-gray-700">Subject</label>
                    <input type="text" name="subject" id="uploadNotifySubject" maxlength="255" class="w-full px-3 py-2 border border-teal-300 rounded focus:border-teal-600 focus:ring-teal-500 text-sm">
                </div>
                <div>
                    <label for="uploadNotifyMessage" class="block mb-1 text-xs font-semibold text-gray-700">Message</label>
                    <textarea name="message" id="uploadNotifyMessage" rows="6" maxlength="10000" class="w-full px-3 py-2 border border-teal-300 rounded focus:border-teal-600 focus:ring-teal-500 text-sm"></textarea>
                    <p id="uploadNotifyMessageHelp" class="mt-1 text-xs text-gray-500">Document details and a link to the member portal are included automatically below this message in the email.</p>
                </div>
                <div class="flex flex-wrap justify-end gap-2 pt-2">
                    <button type="button" onclick="closeUploadNotifyModal()" class="px-4 py-2 text-sm font-semibold text-gray-700 bg-gray-100 rounded hover:bg-gray-200">Cancel</button>
                    <button type="submit" id="uploadNotifySendBtn" class="px-4 py-2 text-sm font-semibold text-white bg-teal-600 rounded hover:bg-teal-700 disabled:opacity-50">Send notification</button>
                </div>
            </form>
        </div>
    </div>

    <div id="uploadRejectModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4" role="dialog" aria-modal="true" aria-labelledby="uploadRejectModalTitle">
        <div class="bg-white rounded-xl shadow-xl w-full max-w-lg p-6 relative">
            <button type="button" onclick="closeUploadRejectModal()" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 text-xl leading-none" aria-label="Close">&times;</button>
            <h3 id="uploadRejectModalTitle" class="text-xl font-bold text-gray-900 mb-1">Return document for correction</h3>
            <p id="uploadRejectModalMeta" class="text-sm text-gray-500 mb-4"></p>
            <form id="uploadRejectModalForm" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label for="uploadRejectNotes" class="block mb-1 text-xs font-semibold text-gray-700">Reason for rejection <span class="text-red-600">*</span></label>
                    <textarea name="verification_notes" id="uploadRejectNotes" required rows="4" maxlength="1000" class="w-full px-3 py-2 border border-red-300 rounded focus:border-red-600 focus:ring-red-500 text-sm" placeholder="Explain what needs to be corrected…"></textarea>
                    <p class="mt-1 text-xs text-gray-500">The uploader will receive a My Tasks item and email with these notes.</p>
                </div>
                <div class="flex flex-wrap justify-end gap-2 pt-2">
                    <button type="button" onclick="closeUploadRejectModal()" class="px-4 py-2 text-sm font-semibold text-gray-700 bg-gray-100 rounded hover:bg-gray-200">Cancel</button>
                    <button type="submit" class="px-4 py-2 text-sm font-semibold text-white bg-red-600 rounded hover:bg-red-700">Return to uploader</button>
                </div>
            </form>
        </div>
    </div>

    <script>
            function openUploadNotifyModal(button) {
                var previewUrl = button.getAttribute('data-upload-notify-preview');
                var sendUrl = button.getAttribute('data-upload-notify-send');
                var notifyMode = button.getAttribute('data-upload-notify-mode') || 'expiry';
                var modal = document.getElementById('uploadNotifyModal');
                var form = document.getElementById('uploadNotifyModalForm');
                var loading = document.getElementById('uploadNotifyModalLoading');
                var errorBox = document.getElementById('uploadNotifyModalError');
                var meta = document.getElementById('uploadNotifyModalMeta');
                var sendBtn = document.getElementById('uploadNotifySendBtn');
                var title = document.getElementById('uploadNotifyModalTitle');
                var reasonWrap = document.getElementById('uploadNotifyReasonWrap');
                var reasonSelect = document.getElementById('uploadNotifyReason');
                var subjectInput = document.getElementById('uploadNotifySubject');
                var messageInput = document.getElementById('uploadNotifyMessage');
                var messageHelp = document.getElementById('uploadNotifyMessageHelp');

                if (!previewUrl || !sendUrl || !modal) {
                    return;
                }

                form.action = sendUrl;
                form.classList.add('hidden');
                loading.classList.remove('hidden');
                errorBox.classList.add('hidden');
                errorBox.textContent = '';
                meta.textContent = '';
                sendBtn.disabled = true;
                reasonWrap.classList.add('hidden');
                reasonSelect.innerHTML = '<option value="">Select a reason…</option>';
                reasonSelect.required = false;
                subjectInput.required = notifyMode !== 'submission';
                messageInput.required = notifyMode !== 'submission';
                title.textContent = notifyMode === 'submission' ? 'Submit document for DSD review' : 'Send document notification';
                sendBtn.textContent = notifyMode === 'submission' ? 'Submit for review' : 'Send notification';
                messageHelp.textContent = notifyMode === 'submission'
                    ? 'Document details and a link to review this document are included automatically in the email to your DSD.'
                    : 'Document details and a link to the member portal are included automatically below this message in the email.';

                modal.classList.remove('hidden');
                modal.classList.add('flex');

                fetch(previewUrl, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    credentials: 'same-origin'
                })
                    .then(function (res) {
                        return res.text().then(function (body) {
                            var data = {};
                            if (body) {
                                try {
                                    data = JSON.parse(body);
                                } catch (parseError) {
                                    if (!res.ok) {
                                        throw new Error('Unable to load notification preview.');
                                    }
                                }
                            }
                            if (!res.ok) {
                                throw new Error(data.error || data.message || 'Unable to load notification preview.');
                            }
                            return data;
                        });
                    })
                    .then(function (data) {
                        var mode = data.mode || notifyMode;
                        document.getElementById('uploadNotifyTo').value = data.to || '';
                        document.getElementById('uploadNotifySubject').value = data.subject || '';
                        document.getElementById('uploadNotifyMessage').value = data.message || '';

                        if (mode === 'submission') {
                            reasonWrap.classList.remove('hidden');
                            reasonSelect.required = true;
                            subjectInput.required = false;
                            messageInput.required = false;

                            (data.submission_reasons || []).forEach(function (reason) {
                                var option = document.createElement('option');
                                option.value = reason.key;
                                option.textContent = reason.label;
                                if (data.current_submission_reason && data.current_submission_reason === reason.key) {
                                    option.selected = true;
                                }
                                reasonSelect.appendChild(option);
                            });
                        }

                        var tierLabel = data.expiry_tier_label || data.expiry_tier || '';
                        var docParts = [data.document_type, data.file_name].filter(Boolean).join(' — ');
                        meta.textContent = [docParts, tierLabel, data.facility_name].filter(Boolean).join(' · ');

                        loading.classList.add('hidden');
                        form.classList.remove('hidden');
                        sendBtn.disabled = false;
                    })
                    .catch(function (err) {
                        loading.classList.add('hidden');
                        errorBox.textContent = err.message || 'Unable to load notification preview.';
                        errorBox.classList.remove('hidden');
                    });
            }

            function closeUploadNotifyModal() {
                var modal = document.getElementById('uploadNotifyModal');
                if (!modal) {
                    return;
                }
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }

            function openUploadRejectModal(actionUrl, fileName) {
                var modal = document.getElementById('uploadRejectModal');
                var form = document.getElementById('uploadRejectModalForm');
                var meta = document.getElementById('uploadRejectModalMeta');
                var notes = document.getElementById('uploadRejectNotes');

                if (!modal || !form) {
                    return;
                }

                form.action = actionUrl;
                meta.textContent = fileName ? 'Document: ' + fileName : '';
                notes.value = '';
                modal.classList.remove('hidden');
                modal.classList.add('flex');
            }

            function closeUploadRejectModal() {
                var modal = document.getElementById('uploadRejectModal');
                if (!modal) {
                    return;
                }
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }

            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape') {
                    closeUploadNotifyModal();
                    closeUploadRejectModal();
                }
            });

            // Focus upload-table after filter change or submit
            document.addEventListener('DOMContentLoaded', function() {
                var filterForm = document.getElementById('upload-table-filter-form');
                if (filterForm) {
                    // On submit, set flag
                    filterForm.addEventListener('submit', function() {
                        sessionStorage.setItem('focus-upload-table', '1');
                    });
                    // On any select or input change, submit and set flag
                    filterForm.querySelectorAll('select, input').forEach(function(el) {
                        el.addEventListener('change', function(e) {
                            // Only auto-submit for selects except search box
                            if (el.tagName === 'SELECT' && el.name !== 'search') {
                                sessionStorage.setItem('focus-upload-table', '1');
                                filterForm.submit();
                            }
                        });
                    });
                }
                // On page load, focus table if flag set
                if (sessionStorage.getItem('focus-upload-table')) {
                    var tableDiv = document.getElementById('upload-table');
                    if (tableDiv) {
                        tableDiv.setAttribute('tabindex', '-1');
                        tableDiv.focus({preventScroll: false});
                        setTimeout(function() { tableDiv.scrollIntoView({behavior: 'smooth', block: 'center'}); }, 10);
                    }
                    sessionStorage.removeItem('focus-upload-table');
                }
            });
    </script>
</div>