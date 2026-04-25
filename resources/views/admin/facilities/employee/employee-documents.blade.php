 <div x-show="tab === 'documents'">
@if(empty($employee->employee_num))
    <div x-show="tab === 'documents'">
        <div class="p-6 mb-6 bg-white rounded shadow text-gray-600">
            <div class="mb-2 p-3 bg-yellow-100 border-l-4 border-yellow-500 text-yellow-800 rounded">
                <strong>Notice:</strong> Please complete and save the Personal tab form before continuing with the checklist.
            </div>
            <em>Save the employee record before uploading documents.</em>
        </div>
    </div>
@elseif(isset($employee->employee_num) && $employee->employee_num)
<div x-show="tab === 'documents'">
    <h2 class="text-xl font-bold mb-4">Employee Documents</h2>
    @if(session('success'))
        <div class="p-3 mb-4 text-green-800 bg-green-100 border border-green-400 rounded">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="p-3 mb-4 text-red-800 bg-red-100 border border-red-400 rounded">
            {{ session('error') }}
        </div>
    @endif
    @if($errors->any())
        <div class="p-3 mb-4 text-red-800 bg-red-100 border border-red-400 rounded">
            <ul class="pl-5 list-disc">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <div class="p-6 mb-6 bg-white rounded shadow" x-data='{
        uploadTypes: @json($uploadTypes->map(fn($t) => ["id"=>$t->id,"requires_expiry"=>$t->requires_expiry])->keyBy("id")),
        selectedType: "{{ old('upload_type_id') }}",
    }'>
        <form id="employee-upload-form" method="POST" action="{{ route('admin.employees.documents.upload', $employee->id) }}" enctype="multipart/form-data">
            @csrf
            <div class="grid grid-cols-2 gap-4 mb-6">
                <div>
                    <label class="block mb-1 text-xs font-semibold">Upload Type <span class="text-red-600">*</span></label>
                    <select name="upload_type_id" class="form-select w-full px-2 py-1 bg-teal-50 border-teal-300 rounded border-1 focus:border-teal-600" required x-model="selectedType">
                        <option value="">Select Type</option>
                        @foreach($uploadTypes as $type)
                            <option value="{{ $type->id }}" @if(old('upload_type_id') == $type->id) selected @endif>{{ $type->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block mb-1 text-xs font-semibold">File <span class="text-red-600">*</span></label>
                    <input type="file" name="document" class="form-input w-full px-2 py-1 bg-teal-50 border-teal-300 rounded border-1 focus:border-teal-600" required>
                </div>
                <template x-if="selectedType && uploadTypes[selectedType] && uploadTypes[selectedType].requires_expiry">
                    <div class="col-span-2 grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block mb-1 text-xs font-semibold">Effective Start Date</label>
                            <input type="date" name="effective_start_date" value="{{ old('effective_start_date') }}" class="px-2 py-1 border-teal-300 rounded border-1 focus:border-teal-600 form-input w-full">
                        </div>
                        <div>
                            <label class="block mb-1 text-xs font-semibold">Expires At <span class="text-red-600">*</span></label>
                            <input type="date" name="expires_at" value="{{ old('expires_at') }}" class="px-2 py-1 border-teal-300 rounded border-1 focus:border-teal-600 form-input w-full" :required="selectedType && uploadTypes[selectedType] && uploadTypes[selectedType].requires_expiry">
                        </div>
                    </div>
                </template>
                <div class="col-span-2 flex flex-col md:flex-row gap-4 items-end">
                    <div class="flex-1">
                        <label class="block mb-1 text-xs font-semibold">Comments</label>
                        <textarea name="comments" rows="2" class="px-2 py-1 bg-teal-50 border-teal-300 rounded border-1 focus:border-teal-600 form-input w-full min-w-[220px] resize-y">{{ old('comments') }}</textarea>
                    </div>
                    <div class="flex-shrink-0">
                        <button type="submit" class="px-4 py-2 bg-teal-600 text-white rounded hover:bg-teal-700">Upload</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endif
    <div class="p-6 bg-white rounded shadow">
        <table class="min-w-full border border-gray-200 table-auto">
            <thead>
                <tr class="bg-gray-100">
                    <th class="px-3 py-2 border text-sm">Type</th>
                    <th class="px-3 py-2 border text-sm">Uploaded By</th>
                    <th class="px-3 py-2 border text-sm">Size</th>
                    <th class="px-3 py-2 border text-sm">Effective Dates</th>
                    <th class="px-3 py-2 border text-sm">Expires</th>
                    <th class="px-3 py-2 border text-sm">Uploaded</th>
                    <th class="px-3 py-2 border text-sm">Comments</th>
                    <th class="px-3 py-2 border text-sm">Actions</th>
                </tr>
            </thead>
            <tbody>
                @php
                $uploads = \App\Models\Upload::with(['user','uploadType'])
                    ->where('employee_num', $employee->employee_num)
                    ->orderByDesc('uploaded_at')
                    ->get();
                @endphp
                @forelse($uploads as $upload)
                <tr>
                    <td class="px-3 py-2 border text-sm">{{ $upload->uploadType->name ?? '-' }}</td>
                    <td class="px-3 py-2 border text-sm">{{ $upload->user->name ?? '-' }}</td>
                    <td class="px-3 py-2 border text-sm">{{ number_format($upload->file_size / 1024, 2) }} KB</td>
                    <td class="px-3 py-2 border text-sm">{{ $upload->effective_start_date }} - {{ $upload->effective_end_date ?? 'Current' }}</td>
                    <td class="px-3 py-2 border text-sm">{{ $upload->expires_at ?? '-' }}</td>
                    <td class="px-3 py-2 border text-sm">{{ $upload->uploaded_at ? \Carbon\Carbon::parse($upload->uploaded_at)->format('M d, Y g:i A') : '-' }}</td>
                    <td class="px-3 py-2 border text-sm">{{ $upload->comments }}</td>
                    <td class="px-3 py-2 border text-sm flex items-center space-x-2">
                        <a href="{{ route('admin.employees.documents.download', [$employee->id, $upload->id]) }}" title="Download" class="text-blue-600 hover:text-blue-800">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5m0 0l5-5m-5 5V4" /></svg>
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="py-6 text-center text-gray-500">No uploads found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
