@csrf
@if(isset($user))
@method('PUT')
@endif
<div class="mb-4">
    <label class="block mb-1 font-semibold">Name</label>
    <input type="text" name="name" class="w-full border px-3 py-2 rounded" required
        value="{{ old('name', isset($user) ? $user->name : '') }}">
    @error('name')<div class="text-red-600">{{ $message }}</div>@enderror
</div>
<div class="mb-4">
    <label class="block mb-1 font-semibold">Email</label>
    <input type="email" name="email" class="w-full border px-3 py-2 rounded" required
        value="{{ old('email', isset($user) ? $user->email : '') }}">
    @error('email')<div class="text-red-600">{{ $message }}</div>@enderror
</div>
<div class="mb-4">
    <label class="block mb-1 font-semibold">Role</label>
    <select name="role" class="w-full border px-3 py-2 rounded" required>
        @foreach(\Spatie\Permission\Models\Role::all() as $role)
        <option value="{{ $role->name }}" {{ old('role', (isset($user) ? $user->roles->pluck('name')->first() : '')) ==
            $role->name ?
            'selected' : '' }}>{{ ucfirst($role->name) }}</option>
        @endforeach
    </select>
    @error('role')<div class="text-red-600">{{ $message }}</div>@enderror
</div>
<div class="mb-4">
    <label class="block mb-1 font-semibold">Facility</label>
    <select name="facility_id" class="w-full border px-3 py-2 rounded">
        <option value="">Bio-Pacific Corporate (Default)</option>
        @foreach($facilities as $facility)
        <option value="{{ $facility->id }}" {{ old('facility_id', (isset($user) ? $user->facility_id : '')) ==
            $facility->id ? 'selected' : '' }}>
            {{ $facility->name }}
        </option>
        @endforeach
    </select>
    @error('facility_id')<div class="text-red-600">{{ $message }}</div>@enderror
</div>
<div class="mb-4">
    <label class="block mb-1 font-semibold">Password{!! isset($user) ? ' <span class="text-gray-500">(leave blank to
            keep current)</span>' : '' !!}</label>
    <input type="password" name="password" class="w-full border px-3 py-2 rounded" {{ isset($user) ? '' : 'required' }}>
    @error('password')<div class="text-red-600">{{ $message }}</div>@enderror
</div>
<div class="mb-4">
    <label class="block mb-1 font-semibold">Confirm Password</label>
    <input type="password" name="password_confirmation" class="w-full border px-3 py-2 rounded" {{ isset($user) ? ''
        : 'required' }}>
</div>
<button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">{{ isset($user) ? 'Update User' : 'Create User'
    }}</button>
<a href="{{ route('admin.users.index') }}" class="ml-4 text-gray-600">Cancel</a>