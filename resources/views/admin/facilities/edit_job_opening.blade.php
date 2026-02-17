@extends('layouts.dashboard')

@section('content')
<div class="container py-4">
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-bold">Edit Job Opening</h1>
        <a href="{{ route('admin.facility.job_openings', $facility) }}"
            class="inline-block px-4 py-2 bg-gray-400 text-white rounded hover:bg-gray-500">Back to Listings</a>
    </div>
    <form method="POST" action="{{ route('admin.facility.job_openings.update', [$facility, $jobOpening]) }}"
        class="bg-white p-4 rounded shadow">
        @csrf
        @method('PUT')
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block font-semibold mb-1">Facility Name</label>
                <input type="text" class="form-input w-full" value="{{ $facility->name }}" disabled>
            </div>
            <div>
                <label class="block font-semibold mb-1">Title</label>
                <input type="text" name="title" class="form-input w-full" value="{{ $jobOpening->title }}" required>
            </div>
            <div>
                <label class="block font-semibold mb-1">Description</label>
                <textarea name="description" class="form-input w-full"
                    rows="2">{{ $jobOpening->description }}</textarea>
            </div>
            </select>
        </div>
        <div>
            <label class="block font-semibold mb-1">Employment Type</label>
            <select name="employment_type" class="form-select w-full">
                <option value="">Select Employment Type</option>
                <option value="Full-time" @if($jobOpening->employment_type=='Full-time') selected @endif>Full-time
                </option>
                <option value="Part-time" @if($jobOpening->employment_type=='Part-time') selected @endif>Part-time
                </option>
                <option value="Per Diem" @if($jobOpening->employment_type=='Per Diem') selected @endif>Per Diem</option>
                <option value="Temporary" @if($jobOpening->employment_type=='Temporary') selected @endif>Temporary
                </option>
                <option value="Contract" @if($jobOpening->employment_type=='Contract') selected @endif>Contract</option>
            </select>
        </div>
        <div>
            <label class="block font-semibold mb-1">Reporting To</label>
            <select name="reporting_to" class="form-select w-full">
                <option value="">Select Reporting To</option>
                @foreach($reportingToPositions as $position)
                <option value="{{ $position }}" @if($jobOpening->reporting_to==$position) selected @endif>{{ $position
                    }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block font-semibold mb-1">Posted At</label>
            <input type="date" name="posted_at" class="form-input w-full" value="{{ $jobOpening->posted_at }}">
        </div>
        <div>
            <label class="block font-semibold mb-1">Expires At</label>
            <input type="date" name="expires_at" class="form-input w-full" value="{{ $jobOpening->expires_at }}">
        </div>
        <div>
            <label class="block font-semibold mb-1">Active</label>
            <select name="active" class="form-select w-full">
                <option value="1" @if($jobOpening->active) selected @endif>Active</option>
                <option value="0" @if(!$jobOpening->active) selected @endif>Inactive</option>
            </select>
        </div>
        <div>
            <label class="block font-semibold mb-1">Status</label>
            <select name="status" class="form-select w-full">
                <option value="open" @if($jobOpening->status=='open') selected @endif>Open</option>
                <option value="closed" @if($jobOpening->status=='closed') selected @endif>Closed</option>
                <option value="pending" @if($jobOpening->status=='pending') selected @endif>Pending</option>
                <option value="filled" @if($jobOpening->status=='filled') selected @endif>Filled</option>
                <option value="cancelled" @if($jobOpening->status=='cancelled') selected @endif>Cancelled</option>
            </select>
        </div>
        <div>
            <label class="block font-semibold mb-1">Created By</label>
            <input type="text" class="form-input w-full" value="{{ $jobOpening->creator->name ?? 'N/A' }}" disabled>
        </div>
        })
        <div class="mt-4">
            <button type="submit"
                style="background-color: #2563eb; color: #fff; font-weight: bold; padding: 0.75rem 2rem; border-radius: 0.5rem; font-size: 1.1rem; box-shadow: 0 2px 6px #0001; border: none;">Update
                Job Opening</button>
        </div>
    </form>
</div>
.then(res => res.json())
.then(() => { fetchTemplates(); clearTemplateForm(); });
};

document.getElementById('delete-template-btn').onclick = function() {
const id = document.getElementById('template-id').value;
if (!id) return;
if (!confirm('Delete this template?')) return;
fetch(`/admin/job-description-templates/${id}`, {
method: 'DELETE',
headers: { 'X-CSRF-TOKEN': getCSRF() }
})
.then(res => res.json())
.then(() => { fetchTemplates(); clearTemplateForm(); });
};

document.getElementById('apply-template-btn').onclick = function() {
document.querySelector('textarea[name="description"]').value =
document.getElementById('template-description').value;
document.querySelector('textarea[name="detailed_description"]').value =
document.getElementById('template-detailed-description').value;
modal.classList.add('hidden');
clearTemplateForm();
};
</script>
@endpush
</div>
</form>
</div>

<div class="container py-4">
    <h2 class="text-xl font-bold mb-4">Description Templates</h2>
    <livewire:admin.description-templates-manager />
</div>
@endsection