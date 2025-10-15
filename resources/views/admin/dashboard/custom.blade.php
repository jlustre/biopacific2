@extends('layouts.dashboard')

@section('content')
<div class="flex h-full w-full flex-1 flex-col gap-6 rounded-xl">
    <!-- Custom Dashboard Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Custom Admin Dashboard</h1>
            <p class="text-gray-600 dark:text-gray-400">This is a custom dashboard page for comparison.</p>
        </div>
        <div class="flex items-center gap-3">
            <flux:button href="{{ route('admin.facilities.create') }}" icon="plus" variant="primary">
                Add New Facility
            </flux:button>
        </div>
    </div>

    <!-- Custom Content -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-8">
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
            <h2 class="text-lg font-bold mb-2">Section 1</h2>
            <p>This is a custom section for demonstration.</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
            <h2 class="text-lg font-bold mb-2">Section 2</h2>
            <p>This is another custom section for demonstration.</p>
        </div>
    </div>
</div>
@endsection