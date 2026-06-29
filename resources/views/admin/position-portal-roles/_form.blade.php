<div class="bg-white rounded-lg border border-gray-200 p-6">
    @if ($errors->any())
    <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
        <ul class="text-red-700 list-disc list-inside">
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form method="POST" action="{{ $action }}" class="space-y-6">
        @csrf
        @if($method !== 'POST')
            @method($method)
        @endif

        @if($mapping)
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Position</label>
            <div class="rounded-lg border border-gray-200 bg-gray-50 px-4 py-3 text-sm text-gray-900">
                {{ $mapping->position?->title }}
                @if($mapping->position?->department)
                <span class="text-gray-500">({{ $mapping->position->department->name }})</span>
                @endif
            </div>
        </div>
        @else
        <div>
            <label for="position_id" class="block text-sm font-medium text-gray-700 mb-1">Position <span class="text-red-500">*</span></label>
            <select name="position_id" id="position_id" required
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                <option value="">Select a position...</option>
                @foreach($positions as $position)
                <option value="{{ $position->id }}" @selected(old('position_id') == $position->id)>
                    {{ $position->title }}@if($position->department) ({{ $position->department->name }})@endif
                </option>
                @endforeach
            </select>
            @if($positions->isEmpty())
            <p class="mt-2 text-sm text-amber-700">All positions already have mappings. Edit an existing mapping or delete one first.</p>
            @endif
        </div>
        @endif

        <div>
            <label for="role_name" class="block text-sm font-medium text-gray-700 mb-1">Portal Role <span class="text-red-500">*</span></label>
            <select name="role_name" id="role_name" required
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                <option value="">Select a role...</option>
                @foreach($roles as $role)
                <option value="{{ $role->name }}" @selected(old('role_name', $mapping?->role_name) === $role->name)>
                    {{ \App\Models\User::roleDisplayLabel($role->name) }} ({{ $role->name }})
                </option>
                @endforeach
            </select>
            <p class="mt-1 text-xs text-gray-500">Assigned automatically when an employee with this position completes self-registration.</p>
        </div>

        <div>
            <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" name="is_active" value="1"
                    class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                    @checked(old('is_active', $mapping?->is_active ?? true))>
                Active
            </label>
            <p class="mt-1 text-xs text-gray-500">Inactive mappings are ignored during employee registration.</p>
        </div>

        <div class="flex items-center gap-3 pt-2">
            <button type="submit"
                class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition font-semibold"
                @disabled(!$mapping && $positions->isEmpty())>
                {{ $mapping ? 'Save Changes' : 'Create Mapping' }}
            </button>
            <a href="{{ route('admin.position-portal-roles.index') }}" class="text-gray-600 hover:text-gray-900 font-semibold">Cancel</a>
        </div>
    </form>
</div>
