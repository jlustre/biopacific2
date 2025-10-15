<div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Quick Actions</h2>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <flux:button href="{{ route('admin.facilities.index') }}" icon="building-office-2" variant="outline"
            class="justify-start">
            <div class="text-left">
                <div class="font-medium">Manage Facilities</div>
                <div class="text-sm text-gray-500">View, edit, and organize all facilities</div>
            </div>
        </flux:button>

        <flux:button href="{{ route('admin.facilities.create') }}" icon="plus" variant="outline" class="justify-start">
            <div class="text-left">
                <div class="font-medium">Add New Facility</div>
                <div class="text-sm text-gray-500">Create a new facility profile</div>
            </div>
        </flux:button>

        <flux:button icon="cog" variant="outline" class="justify-start">
            <div class="text-left">
                <div class="font-medium">System Settings</div>
                <div class="text-sm text-gray-500">Configure global settings</div>
            </div>
        </flux:button>
    </div>
</div>