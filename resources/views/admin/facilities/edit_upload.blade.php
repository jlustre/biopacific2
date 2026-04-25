@extends('layouts.dashboard')

@section('content')
<div class="container py-8">
    <h1 class="mb-4 text-2xl font-bold">Edit Upload</h1>
    <form method="POST" action="{{ route('admin.facility.uploads.update', ['facility' => $upload->facility_id, 'upload' => $upload->id]) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <div class="grid grid-cols-2 gap-4 mb-6">
            <div>
                <label class="block mb-1 text-xs font-semibold">Employee</label>
                <input type="text" value="{{ $upload->employee_name ?? '-' }}" class="form-input w-full px-2 py-1 bg-teal-50 border-teal-300 rounded border-1" disabled>
            </div>
            <div>
                <label class="block mb-1 text-xs font-semibold">Upload Type</label>
                <select name="upload_type_id" class="form-select w-full px-2 py-1 bg-teal-50 border-teal-300 rounded border-1">
                    @foreach($uploadTypes as $type)
                        <option value="{{ $type->id }}" @if($upload->upload_type_id == $type->id) selected @endif>{{ $type->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block mb-1 text-xs font-semibold">Effective Start Date</label>
                <input type="date" name="effective_start_date" value="{{ $upload->effective_start_date }}" class="form-input w-full px-2 py-1 bg-teal-50 border-teal-300 rounded border-1">
            </div>
            <div>
                <label class="block mb-1 text-xs font-semibold">Expires At</label>
                <input type="date" name="expires_at" value="{{ $upload->expires_at }}" class="form-input w-full px-2 py-1 bg-teal-50 border-teal-300 rounded border-1">
            </div>
            <div class="col-span-2">
                <label class="block mb-1 text-xs font-semibold">Comments</label>
                <textarea name="comments" rows="2" class="form-input w-full px-2 py-1 bg-teal-50 border-teal-300 rounded border-1">{{ $upload->comments }}</textarea>
            </div>
        </div>
        <button type="submit" class="px-4 py-2 bg-teal-600 text-white rounded hover:bg-teal-700">Update</button>
        <a href="{{ url()->previous() }}" class="ml-4 px-4 py-2 bg-gray-300 text-gray-800 rounded hover:bg-gray-400">Cancel</a>
    </form>
</div>
@endsection
