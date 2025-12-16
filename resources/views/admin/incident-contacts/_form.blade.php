<div>
    <label class="block font-semibold mb-1">Role</label>
    <select name="role"
        class="w-full border-1 border-blue-300 px-3 py-2 rounded focus:border-blue-700 focus:ring-2 focus:ring-blue-200"
        required>
        <option value="">Select a role</option>
        <option value="Incident Response Lead" {{ (old('role', $contact->role ?? null) == 'Incident Response Lead') ?
            'selected' : '' }}>Incident Response Lead</option>
        <option value="IT/Security" {{ (old('role', $contact->role ?? null) == 'IT/Security') ? 'selected' : ''
            }}>IT/Security</option>
        <option value="Legal/Compliance" {{ (old('role', $contact->role ?? null) == 'Legal/Compliance') ? 'selected' :
            '' }}>Legal/Compliance</option>
        <option value="President" {{ (old('role', $contact->role ?? null) == 'President') ? 'selected' :
            '' }}>President</option>
        <option value="Chief Operating Officer" {{ (old('role', $contact->role ?? null) == 'Chief Operating Officer') ?
            'selected' :
            '' }}>Chief Operating Officer</option>
        <option value="Webmaster" {{ (old('role', $contact->role ?? null) == 'Webmaster') ? 'selected' :
            '' }}>Webmaster</option>
    </select>
    @error('role')<div class="text-red-600">{{ $message }}</div>@enderror
</div>
<div>
    <label class="block font-semibold mb-1">Name</label>
    <input type="text" name="name"
        class="w-full border-1 border-blue-300 px-3 py-2 rounded focus:border-blue-700 focus:ring-2 focus:ring-blue-200"
        required value="{{ old('name', $contact->name ?? '') }}">
    @error('name')<div class="text-red-600">{{ $message }}</div>@enderror
</div>
<div>
    <label class="block font-semibold mb-1">Title</label>
    <input type="text" name="title"
        class="w-full border-1 border-blue-300 px-3 py-2 rounded focus:border-blue-700 focus:ring-2 focus:ring-blue-200"
        value="{{ old('title', $contact->title ?? '') }}">
</div>
<div>
    <label class="block font-semibold mb-1">Email</label>
    <input type="email" name="email"
        class="w-full border-1 border-blue-300 px-3 py-2 rounded focus:border-blue-700 focus:ring-2 focus:ring-blue-200"
        required value="{{ old('email', $contact->email ?? '') }}">
    @error('email')<div class="text-red-600">{{ $message }}</div>@enderror
</div>
<div>
    <label class="block font-semibold mb-1">Phone</label>
    <input type="text" name="phone"
        class="w-full border-1 border-blue-300 px-3 py-2 rounded focus:border-blue-700 focus:ring-2 focus:ring-blue-200"
        value="{{ old('phone', $contact->phone ?? '') }}">
</div>