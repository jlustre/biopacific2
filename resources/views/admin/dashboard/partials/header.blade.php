<div class="flex items-center justify-between">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Admin Dashboard</h1>
        <p class="text-gray-600 dark:text-gray-400">Manage your facilities and website content</p>
    </div>
    <div class="flex items-center gap-3">
        <flux:button href="{{ route('admin.facilities.create') }}" icon="plus" variant="primary">
            Add New Facility
        </flux:button>
    </div>
</div>