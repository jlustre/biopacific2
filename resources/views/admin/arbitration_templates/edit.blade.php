@extends('layouts.dashboard')

@section('content')
<div class="container py-8 max-w-2xl mx-auto">
    <h1 class="text-2xl font-bold mb-4">Edit Arbitration Template</h1>
    <form action="{{ route('admin.arbitration-templates.update', $template->id) }}" method="POST"
        enctype="multipart/form-data" class="bg-white p-6 rounded shadow">
        @csrf
        @method('PUT')
        <div class="mb-4">
            <label class="block font-semibold mb-1">Facility</label>
            <select name="facility_id" class="form-select w-full border border-teal-300 rounded-lg px-2 py-1" required>
                <option value="">Select Facility</option>
                @foreach($facilities as $facility)
                <option value="{{ $facility->id }}" @if($facility->id == $template->facility_id) selected @endif>{{
                    $facility->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="mb-4">
            <label class="block font-semibold mb-1">Template Type</label>
            <select id="template_type_select" name="template_type"
                class="form-select w-full border border-teal-300 rounded-lg px-2 py-1" required>
                <option value="docx" @if($template->template_type=='docx') selected @endif>Word (.docx)</option>
                <option value="pdf" @if(empty($template->template_type) || $template->template_type=='pdf') selected
                    @endif>PDF (.pdf)</option>
            </select>
        </div>
        <div class="mb-4">
            <label class="block font-semibold mb-1">Replace File (optional)</label>
            <input id="template_file_input" type="file" name="template_file"
                accept="{{ empty($template->template_type) || $template->template_type=='pdf' ? '.pdf' : '.docx' }}"
                class="form-input w-full border border-teal-300 rounded-lg px-2 py-1">
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                var fileInput = document.getElementById('template_file_input');
                var typeSelect = document.getElementById('template_type_select');
                function updateAccept() {
                    if (typeSelect.value === 'pdf') {
                        fileInput.setAttribute('accept', '.pdf');
                    } else {
                        fileInput.setAttribute('accept', '.docx');
                    }
                    fileInput.value = '';
                }
                typeSelect.addEventListener('change', updateAccept);
                updateAccept();
                if (fileInput) {
                    fileInput.addEventListener('change', function(e) {
                        var newInput = fileInput.cloneNode(true);
                        newInput.setAttribute('accept', fileInput.getAttribute('accept'));
                        fileInput.parentNode.replaceChild(newInput, fileInput);
                        fileInput = newInput;
                        typeSelect.addEventListener('change', updateAccept);
                    });
                        }
            });
            </script>
            <div class="text-xs mt-1">Current: <a href="{{ asset('storage/' . $template->template_path) }}"
                    target="_blank" class="text-blue-600 underline">Download</a></div>
        </div>
        <div class="flex justify-end">
            <a href="{{ route('admin.arbitration-templates.index') }}"
                class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300 mr-2">Cancel</a>
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Update</button>
        </div>
    </form>
</div>
@endsection