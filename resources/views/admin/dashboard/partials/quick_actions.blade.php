<div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Quick Actions</h2>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <a href="{{ route('admin.facilities.index') }}" class="group block rounded-xl bg-gradient-to-br from-blue-600 to-blue-900 text-white shadow-lg hover:from-blue-700 hover:to-blue-950 transition-all duration-200 p-5 focus:outline-none focus:ring-2 focus:ring-blue-500">
            <div class="flex items-center gap-3 mb-2">
                <flux:icon name="building-office-2" class="h-6 w-6 text-white opacity-90 group-hover:scale-110 transition" />
                <span class="font-semibold text-lg">Manage Facilities</span>
            </div>
            <div class="text-sm text-blue-100">View, edit, and organize all facilities</div>
        </a>

        <a href="{{ route('admin.facilities.create') }}" class="group block rounded-xl bg-gradient-to-br from-green-600 to-green-900 text-white shadow-lg hover:from-green-700 hover:to-green-950 transition-all duration-200 p-5 focus:outline-none focus:ring-2 focus:ring-green-500">
            <div class="flex items-center gap-3 mb-2">
                <flux:icon name="plus" class="h-6 w-6 text-white opacity-90 group-hover:scale-110 transition" />
                <span class="font-semibold text-lg">Add New Facility</span>
            </div>
            <div class="text-sm text-green-100">Create a new facility profile</div>
        </a>

        <a href="{{ route('admin.settings.index') }}" class="group block rounded-xl bg-gradient-to-br from-purple-600 to-purple-900 text-white shadow-lg hover:from-purple-700 hover:to-purple-950 transition-all duration-200 p-5 focus:outline-none focus:ring-2 focus:ring-purple-500">
            <div class="flex items-center gap-3 mb-2">
                <flux:icon name="cog" class="h-6 w-6 text-white opacity-90 group-hover:scale-110 transition" />
                <span class="font-semibold text-lg">System Settings</span>
            </div>
            <div class="text-sm text-purple-100">Configure global settings</div>
        </a>
    </div>
</div>