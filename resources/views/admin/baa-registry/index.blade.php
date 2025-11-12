@extends('layouts.dashboard')

@section('content')
<h1 class="text-teal-600 text-2xl font-bold text-center mb-4">BAA Vendor Registry</h1>
<a href="{{ route('admin.baa-registry.create') }}"
    class="bg-teal-600 text-white px-4 py-2 rounded mb-4 inline-block">Add Vendor</a>
@if(session('success'))
<div class="bg-green-100 text-green-800 p-2 mb-4 rounded">{{ session('success') }}</div>
@endif
<table class="table-auto w-full border">
    <thead>
        <tr>
            <th>Facility</th>
            <th>Vendor/Service</th>
            <th>Type</th>
            <th class="text-center text-sm">ePHI<br />Access</th>
            <th class="text-center text-sm">BAA<br />Status</th>
            <th>BAA Form</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        @foreach($vendors as $vendor)
        <tr>
            <td class="border px-2 py-1 text-sm">{{ optional($vendor->facility)->name ?? 'N/A' }}</td>
            <td class="border px-2 py-1 text-sm">{{ $vendor->vendor_service }}</td>
            <td class="border px-2 py-1 text-sm">{{ $vendor->type }}</td>
            <td class="border px-2 py-1 text-sm">{{ $vendor->ephi_access }}</td>
            <td class="border px-2 py-1 text-sm">{{ $vendor->baa_status }}</td>
            <td class="border px-2 py-1 text-sm">
                @if($vendor->baa_form_path)
                <button type="button" class="text-teal-600 underline"
                    onclick="openBaaModal('{{ asset('storage/' . $vendor->baa_form_path) }}')">View BAA Form</button>
                @else
                <span class="text-gray-400">No file</span>
                @endif
            </td>
            <td class="border px-2 py-1">
                <a href="{{ route('admin.baa-registry.edit', $vendor) }}" title="Edit Vendor"
                    class="text-blue-600 hover:text-blue-800 mr-2">
                    <i class="fas fa-edit"></i>
                </a>
                <form action="{{ route('admin.baa-registry.destroy', $vendor) }}" method="POST"
                    style="display:inline-block;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" title="Delete Vendor" class="text-red-600 hover:text-red-800 ml-2"
                        onclick="return confirm('Delete this vendor?')">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                </form>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
<!-- Modal for BAA Form Preview -->
<div id="baaModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded shadow-lg p-6 max-w-2xl w-full relative">
        <button onclick="closeBaaModal()" class="absolute top-2 right-2 text-gray-600">&times;</button>
        <h2 class="text-xl font-bold mb-4">BAA Form Preview</h2>
        <iframe id="baaFormFrame" src="" class="w-full h-96 border" frameborder="0"></iframe>
    </div>
</div>
<script>
    function openBaaModal(url) {
            document.getElementById('baaFormFrame').src = url;
            document.getElementById('baaModal').classList.remove('hidden');
        }
        function closeBaaModal() {
            document.getElementById('baaFormFrame').src = '';
            document.getElementById('baaModal').classList.add('hidden');
        }
</script>
@endsection