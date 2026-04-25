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
                <input type="text" name="search" value="{{ request('search') }}" class="px-2 py-1 border-teal-300 rounded border-1 focus:border-teal-600 form-input" placeholder="File name...">
            </div>
            <div>
                <label class="block mb-1 text-xs font-semibold">Filter by Facility</label>
                <select name="facility_id" class="px-2 py-1 border-teal-300 rounded border-1 focus:border-teal-600 form-select">
                    <option value="">All Facilities</option>
                    @foreach(App\Models\Facility::orderBy('name')->get() as $fac)
                    <option value="{{ $fac->id }}" @if(request('facility_id')==$fac->id) selected @endif>{{ $fac->name }}</option>
                    @endforeach
                </select>
            </div>
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
                <th class="px-3 py-2 border text-sm">Employee Name</th>
                <th class="px-3 py-2 border text-sm">Expires</th>
                <th class="px-3 py-2 border text-sm">Actions</th>
            </tr>
        </thead>
        <tbody>
            @php
            $query = App\Models\Upload::with(['facility','user','uploadType']);
            if(request('facility_id')) $query->where('facility_id', request('facility_id'));
            if(request('search')) $query->where('original_filename', 'like', '%'.request('search').'%');
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
    <script>
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