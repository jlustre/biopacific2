@php use Illuminate\Support\Facades\Auth; @endphp
<div id="upload-table" class="p-6 bg-white rounded shadow" tabindex="-1">
    <form id="upload-table-filter-form" method="GET" class="flex flex-wrap items-end gap-4 mb-4">
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
            <div>
                <label class="block mb-1 text-xs font-semibold">Search by Name</label>
                <input type="text" name="search" value="{{ request('search') }}" class="px-2 py-1 border-teal-300 rounded border-1 focus:border-teal-600 form-input" placeholder="Employee or file name...">
            </div>
            @php
                $user = Auth::user();
                $canChooseFacility = $user && ($user->hasRole('admin') || $user->hasRole('rdhr'));
                $userFacility = $user && $user->facility ? $user->facility : null;
            @endphp
            @if($canChooseFacility)
                <div>
                    <label class="block mb-1 text-xs font-semibold">Filter by Facility</label>
                    <select name="facility_id" class="px-2 py-1 border-teal-300 rounded border-1 focus:border-teal-600 form-select">
                        <option value="">All Facilities</option>
                        @foreach(App\Models\Facility::orderBy('name')->get() as $fac)
                        <option value="{{ $fac->id }}" @if(request('facility_id')==$fac->id) selected @endif>{{ $fac->name }}</option>
                        @endforeach
                    </select>
                </div>
            @elseif($userFacility)
                <div>
                    <label class="block mb-1 text-xs font-semibold">Facility</label>
                    <span class="text-teal-700 font-semibold">{{ $userFacility->name }}</span>
                    <input type="hidden" name="facility_id" value="{{ $userFacility->id }}">
                </div>
            @endif
            <div>
                <label class="block mb-1 text-xs font-semibold">Filter by Upload Type</label>
                <select name="upload_type_id" x-model="selectedType" @change="updateShowExpiry()" class="px-2 py-1 border-teal-300 rounded border-1 focus:border-teal-600 form-select">
                    <option value="">All Types</option>
                    @foreach(App\Models\UploadType::orderBy('name')->get() as $utype)
                        <option value="{{ $utype->id }}" data-requires-expiry="{{ $utype->requires_expiry ? '1' : '0' }}" @if(request('upload_type_id')==$utype->id) selected @endif>
                            {{ $utype->name }}@if($utype->requires_expiry) **@endif
                        </option>
                    @endforeach
                </select>
            </div>
            {{-- <template> --}}
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
            {{-- </template> --}}
            <button type="submit" class="px-2 py-1 ml-1 font-semibold text-white bg-teal-600 rounded cursor-pointer hover:bg-teal-700">Filter</button>
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
    <table class="min-w-full border border-gray-200 table-auto">
        <thead>
            <tr class="bg-gray-100">
                <th class="px-3 py-2 border text-sm">Type</th>
                <th class="px-3 py-2 border text-sm">Facility</th>
                <th class="px-3 py-2 border text-sm">Employee #</th>
                <th class="px-3 py-2 border text-sm">Employee Name</th>
                <th class="px-3 py-2 border text-sm">Expires</th>
                <th class="px-3 py-2 border text-sm">Actions</th>
            </tr>
        </thead>
        <tbody>
            @php
            $query = App\Models\Upload::with(['facility','user','uploadType','employee']);
            $user = Auth::user();
            $canChooseFacility = $user && ($user->hasRole('admin') || $user->hasRole('rdhr'));
            $userFacility = $user && $user->facility ? $user->facility : null;
            if($canChooseFacility) {
                if(request('facility_id')) $query->where('facility_id', request('facility_id'));
            } elseif($userFacility) {
                $query->where('facility_id', $userFacility->id);
            }
            if(request('search')) {
                $search = trim(request('search'));
                $like = '%' . mb_strtolower($search) . '%';
                $query->where(function ($q) use ($like) {
                    $q->whereRaw('LOWER(original_filename) LIKE ?', [$like])
                        ->orWhereRaw('LOWER(employee_num) LIKE ?', [$like])
                        ->orWhereHas('employee', function ($employeeQuery) use ($like) {
                            $employeeQuery->where(function ($nameQuery) use ($like) {
                                $nameQuery->whereRaw('LOWER(first_name) LIKE ?', [$like])
                                    ->orWhereRaw('LOWER(last_name) LIKE ?', [$like])
                                    ->orWhereRaw("LOWER(CONCAT(COALESCE(last_name, ''), ', ', COALESCE(first_name, ''))) LIKE ?", [$like])
                                    ->orWhereRaw("LOWER(CONCAT(COALESCE(first_name, ''), ' ', COALESCE(last_name, ''))) LIKE ?", [$like]);
                            });
                        });
                });
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
            @if($upload && $upload->id && $upload->uploadType && $upload->facility)
            <tr>
                <td class="px-3 py-2 border text-xs">{{ $upload->uploadType->name ?? '-' }}</td>
                <td class="px-3 py-2 border text-xs">{{ $upload->facility->name ?? '-' }}</td>
                <td class="px-3 py-2 border text-xs">{{ $upload->employee_num ?? '-' }}</td>
                <td class="px-3 py-2 border text-xs">
                    @if($upload->employee)
                        {{ $upload->employee->last_name }}, {{ $upload->employee->first_name }}
                    @endif
                </td>
                <!-- Removed Uploaded By column cell -->
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
                <!-- Removed Uploaded column cell -->
                <td class="px-2 py-2 border text-xs flex items-center space-x-2">
                    <a href="{{ route('admin.facility.uploads.download', ['facility' => $upload->facility_id, 'upload' => $upload->id]) }}" title="Download" class="text-blue-600 hover:text-blue-800">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5m0 0l5-5m-5 5V4" /></svg>
                    </a>
                    <a href="{{ route('admin.facility.uploads.view', ['facility' => $upload->facility_id, 'upload' => $upload->id]) }}" title="View" target="_blank" class="text-green-600 hover:text-green-800">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                    </a>
                    <a href="{{ route('admin.facility.documents', ['facility' => $facility->id, 'edit' => $upload->id]) }}" title="Edit" class="text-green-600 hover:text-green-800">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                    </a>
                    @if($upload->canSendExpiryNotification())
                        @php
                            $notifyEmail = null;
                            if ($upload->employee) {
                                foreach ([$upload->employee->email, $upload->employee->user?->email] as $candidate) {
                                    $candidate = trim((string) $candidate);
                                    if ($candidate !== '' && filter_var($candidate, FILTER_VALIDATE_EMAIL)) {
                                        $notifyEmail = $candidate;
                                        break;
                                    }
                                }
                            }
                        @endphp
                        <button type="button"
                            title="{{ $notifyEmail ? 'Preview and send notification to ' . $notifyEmail : 'Employee has no email on file' }}"
                            class="text-teal-600 hover:text-teal-800 bg-transparent border-none p-0 m-0 disabled:opacity-40 disabled:cursor-not-allowed"
                            @disabled(!$notifyEmail)
                            data-upload-notify-preview="{{ route('admin.facility.uploads.notify.preview', ['facility' => $upload->facility_id, 'upload' => $upload->id]) }}"
                            data-upload-notify-send="{{ route('admin.facility.uploads.notify', ['facility' => $upload->facility_id, 'upload' => $upload->id]) }}"
                            onclick="openUploadNotifyModal(this)">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                            </svg>
                            <span class="sr-only">Send notification</span>
                        </button>
                    @endif
                    <form action="{{ route('admin.facility.uploads.destroy', ['facility' => $upload->facility_id, 'upload' => $upload->id]) }}" method="POST" class="inline" onsubmit="return confirm('Delete this upload?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" title="Delete" class="text-red-600 hover:text-red-800 bg-transparent border-none p-0 m-0">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                    </form>
                </td>
            </tr>
            @endif
            @empty
            <tr>
                <td colspan="9" class="py-6 text-center text-gray-500">No uploads found.</td>
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
                <div>
                    <label for="uploadNotifyTo" class="block mb-1 text-xs font-semibold text-gray-700">To</label>
                    <input type="email" id="uploadNotifyTo" readonly class="w-full px-3 py-2 bg-gray-100 border border-gray-300 rounded text-sm text-gray-700 cursor-not-allowed">
                </div>
                <div>
                    <label for="uploadNotifySubject" class="block mb-1 text-xs font-semibold text-gray-700">Subject</label>
                    <input type="text" name="subject" id="uploadNotifySubject" required maxlength="255" class="w-full px-3 py-2 border border-teal-300 rounded focus:border-teal-600 focus:ring-teal-500 text-sm">
                </div>
                <div>
                    <label for="uploadNotifyMessage" class="block mb-1 text-xs font-semibold text-gray-700">Message</label>
                    <textarea name="message" id="uploadNotifyMessage" required rows="8" maxlength="10000" class="w-full px-3 py-2 border border-teal-300 rounded focus:border-teal-600 focus:ring-teal-500 text-sm"></textarea>
                    <p class="mt-1 text-xs text-gray-500">Document details and a link to the member portal are included automatically below this message in the email.</p>
                </div>
                <div class="flex flex-wrap justify-end gap-2 pt-2">
                    <button type="button" onclick="closeUploadNotifyModal()" class="px-4 py-2 text-sm font-semibold text-gray-700 bg-gray-100 rounded hover:bg-gray-200">Cancel</button>
                    <button type="submit" id="uploadNotifySendBtn" class="px-4 py-2 text-sm font-semibold text-white bg-teal-600 rounded hover:bg-teal-700 disabled:opacity-50">Send notification</button>
                </div>
            </form>
        </div>
    </div>

    <script>
            function openUploadNotifyModal(button) {
                var previewUrl = button.getAttribute('data-upload-notify-preview');
                var sendUrl = button.getAttribute('data-upload-notify-send');
                var modal = document.getElementById('uploadNotifyModal');
                var form = document.getElementById('uploadNotifyModalForm');
                var loading = document.getElementById('uploadNotifyModalLoading');
                var errorBox = document.getElementById('uploadNotifyModalError');
                var meta = document.getElementById('uploadNotifyModalMeta');
                var sendBtn = document.getElementById('uploadNotifySendBtn');

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
                        return res.json().then(function (data) {
                            if (!res.ok) {
                                throw new Error(data.error || 'Unable to load notification preview.');
                            }
                            return data;
                        });
                    })
                    .then(function (data) {
                        document.getElementById('uploadNotifyTo').value = data.to || '';
                        document.getElementById('uploadNotifySubject').value = data.subject || '';
                        document.getElementById('uploadNotifyMessage').value = data.message || '';

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

            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape') {
                    closeUploadNotifyModal();
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