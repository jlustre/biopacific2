@props(['vendor' => null])
<form action="{{ $vendor ? route('admin.baa-registry.update', $vendor) : route('admin.baa-registry.store') }}"
    method="POST" enctype="multipart/form-data" class="max-w-lg mx-auto bg-white p-6 rounded shadow">
    @csrf
    @if($vendor)
    @method('PUT')
    @endif
    <div class="mb-4">
        <label class="block font-bold mb-1">Facility</label>
        <select name="facility_id" class="w-full border px-2 py-1" required>
            <option value="">Select Facility</option>
            @foreach(App\Models\Facility::orderBy('name')->get() as $facility)
            <option value="{{ $facility->id }}" {{ old('facility_id', $vendor->facility_id ?? '') == $facility->id ?
                'selected' : '' }}>{{ $facility->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="mb-4">
        <label class="block font-bold mb-1">Vendor/Service</label>
        <input type="text" name="vendor_service" class="w-full border px-2 py-1"
            value="{{ old('vendor_service', $vendor->vendor_service ?? '') }}" required>
    </div>
    <div class="mb-4">
        <label class="block font-bold mb-1">Type</label>
        <select name="type" class="w-full border px-2 py-1" required>
            <option value="">Select Type</option>
            <option value="Hosting/DB/Backup" {{ old('type', $vendor->type ?? '') == 'Hosting/DB/Backup' ? 'selected' :
                '' }}>Hosting/DB/Backup</option>
            <option value="Mail" {{ old('type', $vendor->type ?? '') == 'Mail' ? 'selected' : '' }}>Mail</option>
            <option value="Chat/SMS" {{ old('type', $vendor->type ?? '') == 'Chat/SMS' ? 'selected' : '' }}>Chat/SMS
            </option>
            <option value="Logs" {{ old('type', $vendor->type ?? '') == 'Logs' ? 'selected' : '' }}>Logs</option>
            <option value="Backup" {{ old('type', $vendor->type ?? '') == 'Backup' ? 'selected' : '' }}>Backup</option>
            <option value="Other" {{ old('type', $vendor->type ?? '') == 'Other' ? 'selected' : '' }}>Other</option>
        </select>
    </div>
    <div class="mb-4">
        <label class="block font-bold mb-1">ePHI Access</label>
        <select name="ephi_access" class="w-full border px-2 py-1" required>
            <option value="">Select Access</option>
            <option value="Yes" {{ old('ephi_access', $vendor->ephi_access ?? '') == 'Yes' ? 'selected' : '' }}>Yes
            </option>
            <option value="No" {{ old('ephi_access', $vendor->ephi_access ?? '') == 'No' ? 'selected' : '' }}>No
            </option>
        </select>
    </div>
    <div class="mb-4">
        <label class="block font-bold mb-1">BAA Status</label>
        <select name="baa_status" class="w-full border px-2 py-1" required>
            <option value="">Select Status</option>
            <option value="Signed" {{ old('baa_status', $vendor->baa_status ?? '') == 'Signed' ? 'selected' : ''
                }}>Signed</option>
            <option value="Pending" {{ old('baa_status', $vendor->baa_status ?? '') == 'Pending' ? 'selected' : ''
                }}>Pending</option>
            <option value="Not Required" {{ old('baa_status', $vendor->baa_status ?? '') == 'Not Required' ? 'selected'
                : '' }}>Not Required</option>
        </select>
    </div>
    <div class="mb-4">
        <label class="block font-bold mb-1">Notes</label>
        <textarea name="notes" class="w-full border px-2 py-1"
            rows="3">{{ old('notes', $vendor->notes ?? '') }}</textarea>
    </div>
    <div class="mb-4">
        <label class="block font-bold mb-1">BAA Form (PDF, DOC, DOCX)</label>
        <input type="file" name="baa_form" class="w-full border px-2 py-1" accept=".pdf,.doc,.docx">
        @if(isset($vendor) && $vendor->baa_form_path)
        <div class="mt-2">
            <a href="{{ asset('storage/' . $vendor->baa_form_path) }}" target="_blank"
                class="text-teal-600 underline">View current BAA form</a>
        </div>
        @endif
    </div>
    <button type="submit" class="bg-teal-600 text-white px-4 py-2 rounded">{{ $vendor ? 'Update Vendor' : 'Add Vendor'
        }}</button>
    <a href="{{ route('admin.baa-registry.index') }}" class="ml-4 text-gray-600">Cancel</a>
</form>