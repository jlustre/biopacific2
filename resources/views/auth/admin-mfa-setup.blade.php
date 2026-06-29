<x-guest-layout>
    <div>
        <h2 class="text-xl font-bold mb-4">Set Up Multi-Factor Authentication</h2>
        <p class="mb-4 text-sm text-gray-600">Scan the QR code below with your authenticator app (Google Authenticator,
            Authy, etc.), then enter the 6-digit code to enable MFA.</p>
        @if (!empty($qrCodeUrl))
            <div class="flex justify-center mb-4">
                <div>{!! $qrCodeUrl !!}</div>
            </div>
        @endif
        <div class="mb-4 rounded-lg border border-gray-200 bg-gray-50 px-4 py-3 text-sm text-gray-700">
            <p class="font-semibold text-gray-900">Can't scan the code?</p>
            <p class="mt-1">Add a manual entry in your app using this setup key:</p>
            <p class="mt-2 break-all font-mono text-base tracking-wider text-gray-900">{{ $secret }}</p>
        </div>
        @if (session('status'))
            <div class="mb-4 text-green-600 text-center font-semibold">{{ session('status') }}</div>
        @endif
        @if ($errors->any())
            <div class="mb-4 text-red-600 text-center font-semibold">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif
        <form method="POST" action="{{ route('admin.mfa.setup.store') }}">
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
            <div class="flex items-center justify-between mt-4">
                @if (!empty($cancelUrl))
                    <a href="{{ $cancelUrl }}" class="text-sm font-semibold text-gray-600 hover:text-gray-900">Cancel</a>
                @endif
                <button type="submit" class="btn btn-primary">Enable MFA</button>
            </div>
        </form>
    </div>
</x-guest-layout>
