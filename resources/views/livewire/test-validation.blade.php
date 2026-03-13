<div class="max-w-md mx-auto mt-10">
    <form wire:submit.prevent="submit" class="bg-white p-6 rounded shadow">
        <div class="mb-4">
            <label class="block font-semibold mb-2">Name *</label>
            <input type="text" wire:model="name"
                class="w-full border rounded p-2 @error('name') border-red-500 @enderror">
            @error('name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>
        @if($errors->any())
        <div class="p-2 bg-red-100 text-red-800 rounded mb-4">
            <ul>
                @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif
        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Submit</button>
    </form>
</div>