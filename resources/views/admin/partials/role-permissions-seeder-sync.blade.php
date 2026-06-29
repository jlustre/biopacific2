@php
    $compact = $compact ?? false;
    $confirmMessage = "Export all roles and their permission assignments to database/seeders/data/role_permissions.json?\n\nCommit that file so migrate:fresh --seed restores your role configuration.";
@endphp

@if ($compact)
    <form method="POST" action="{{ route('admin.roles.sync-seeder') }}"
        onsubmit="return confirm(@json($confirmMessage));">
        @csrf
        <button type="submit"
            class="inline-flex items-center justify-center whitespace-nowrap rounded-lg border border-amber-300 bg-amber-50 px-5 py-2 font-semibold text-amber-900 hover:bg-amber-100 transition">
            <i class="fas fa-database mr-2"></i> Update Seeder
        </button>
    </form>
@else
    <div class="bg-white rounded-lg shadow p-6 {{ $class ?? '' }}">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h3 class="text-lg font-medium text-gray-900">Update Seeder File</h3>
                <p class="text-sm text-gray-600">
                    {{ $description ?? 'Export the current database roles and permission assignments to the seeder JSON file.' }}
                </p>
            </div>
            <form method="POST" action="{{ route('admin.roles.sync-seeder') }}"
                onsubmit="return confirm(@json($confirmMessage));">
                @csrf
                <button type="submit"
                    class="inline-flex items-center whitespace-nowrap rounded-lg bg-amber-600 px-4 py-2 text-sm font-semibold text-white hover:bg-amber-700">
                    <i class="fas fa-sync mr-2"></i> Update Seeder
                </button>
            </form>
        </div>
    </div>
@endif
