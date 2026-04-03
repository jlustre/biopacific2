@extends('layouts.dashboard')

@section('content')
<div class="container py-4">
    <h1 class="text-xl font-bold mb-6"><strong>{{ $facility->name }}</strong></h1>

    <!-- Modal and Button in shared Alpine.js scope -->
    <div x-data="{ showNewOpeningModal: false }" @keydown.escape.window="showNewOpeningModal = false">
        <!-- Button to open modal -->
        <button @click="showNewOpeningModal = true"
            class="mb-4 px-4 py-2 bg-teal-600 text-white rounded hover:bg-teal-700">New Job Opening</button>

        <!-- Modal -->
        <div x-show="showNewOpeningModal" style="display: none;"
            class="fixed inset-0 flex items-center justify-center z-50 bg-black bg-opacity-50">
            <div class="bg-white rounded-lg shadow-lg p-8 w-full max-w-2xl relative">
                <button @click="showNewOpeningModal = false"
                    class="absolute top-2 right-2 text-gray-500 hover:text-gray-700">&times;</button>
                <h3 class="text-xl font-bold mb-4">Create New Job Opening2</h3>
                <form method="POST" action="{{ route('admin.facility.job_openings.store', $facility) }}">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block font-semibold mb-1">Title</label>
                            <input id="modal-title" type="text" name="title" class="w-full border rounded px-3 py-2"
                                required>
                        </div>
                        <div>
                            <label class="block font-semibold mb-1">Employment Type</label>
                            <input type="text" name="employment_type" class="w-full border rounded px-3 py-2" required>
                        </div>
                        <div>
                            <label class="block font-semibold mb-1">Department</label>
                            <input type="text" name="department" class="w-full border rounded px-3 py-2">
                        </div>
                        <div>
                            <label class="block font-semibold mb-1">Reporting To</label>
                            <input type="text" name="reporting_to" class="w-full border rounded px-3 py-2" required>
                        </div>
                        <div>
                            <label class="block font-semibold mb-1">Posted At</label>
                            <input type="date" name="posted_at" class="w-full border rounded px-3 py-2" required>
                        </div>
                        <div>
                            <label class="block font-semibold mb-1">Status</label>
                            <select name="status" class="w-full border rounded px-3 py-2" required>
                                <option value="open">Open</option>
                                <option value="closed">Closed</option>
                                <option value="filled">Filled</option>
                            </select>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block font-semibold mb-1">Description Template</label>
                            <select id="modal-template" class="w-full border rounded px-3 py-2">
                                <option value="">Select a template</option>
                            </select>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block font-semibold mb-1">Description</label>
                            <textarea id="modal-description" name="description" class="w-full border rounded px-3 py-2"
                                rows="3" required></textarea>
                        </div>
                        <div class="flex items-center mt-2 md:col-span-2">
                            <input type="checkbox" name="active" value="1" id="modal-active">
                            <label for="modal-active" class="ml-2">Active</label>
                        </div>
                    </div>
                    <div class="mt-4 flex justify-end">
                        <button type="button" @click="showNewOpeningModal = false"
                            class="px-4 py-2 bg-gray-300 rounded mr-2">Cancel</button>
                        <button type="submit" class="px-6 py-2 bg-teal-600 text-white rounded hover:bg-teal-700">Create
                            Job Opening</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Existing job listing manager (temporarily removed for modal test) -->
    {{--
    <livewire:job-listing-manager :facility="$facility" /> --}}
</div>
@endsection

@push('scripts')
<script src="/js/job_opening_modal.js"></script>
@endpush