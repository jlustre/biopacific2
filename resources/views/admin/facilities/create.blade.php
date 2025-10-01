@extends('layouts.dashboard')

@section('content')
<div class="max-w-2xl mx-auto py-10">
    <h1 class="text-2xl font-bold mb-6 text-gray-900 dark:text-white">Add New Facility</h1>
    <form method="POST" action="{{ route('admin.facilities.store') }}" enctype="multipart/form-data"
        class="space-y-6 bg-white dark:bg-gray-900 p-8 rounded-xl shadow">
        @csrf
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Facility Name</label>
            <input type="text" name="name"
                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white shadow-sm focus:border-primary focus:ring focus:ring-primary/20"
                required>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tagline</label>
            <input type="text" name="tagline"
                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white shadow-sm">
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">City</label>
                <input type="text" name="city"
                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white shadow-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">State</label>
                <input type="text" name="state"
                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white shadow-sm">
            </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Beds</label>
                <input type="number" name="beds" min="0"
                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white shadow-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Domain</label>
                <input type="text" name="domain"
                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white shadow-sm">
            </div>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Active?</label>
            <select name="is_active"
                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white shadow-sm">
                <option value="1">Active</option>
                <option value="0">Inactive</option>
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Photo (optional)</label>
            <input type="file" name="photo" class="mt-1 block w-full text-gray-700 dark:text-gray-300">
        </div>
        <div class="flex justify-end">
            <div class="flex items-center mb-4">
                <input id="no-phi" name="no_phi" type="checkbox" required
                    class="h-4 w-4 text-primary focus:ring-primary border-gray-300 rounded">
                <label for="no-phi" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">
                    I confirm that I will not include any Protected Health Information (PHI) in this form.
                </label>
            </div>
            <a href="{{ route('admin.dashboard.index') }}"
                class="inline-flex items-center px-6 py-2 mr-3 rounded-lg bg-gray-200 text-gray-800 font-semibold shadow hover:bg-gray-300 transition dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600">
                Cancel
            </a>
            <button type="submit"
                class="inline-flex items-center px-6 py-2 rounded-lg bg-teal-600 text-white font-semibold shadow hover:bg-teal-500 transition">Create
                Facility</button>
        </div>
    </form>
</div>
@endsection