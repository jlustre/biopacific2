<x-guest-layout>
    <div>
        <h2 class="text-xl font-bold mb-4">Multi-Factor Authentication</h2>
        <form method="POST" action="{{ route('admin.mfa.verify') }}">
            @csrf
            <div>
                <label for="one_time_password" class="block text-sm font-medium text-gray-700">Authentication
                    Code</label>
                <input id="one_time_password" name="one_time_password" type="text" required autofocus
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" />
                @error('one_time_password')
                    <span class="text-red-500 text-xs">{{ $message }}</span>
                @enderror
            </div>
            <div class="flex items-center justify-end mt-4" title="Verify Item">
                <button type="submit" class="btn btn-primary">Verify</button>
            </div>
        </form>
    </div>
</x-guest-layout>
