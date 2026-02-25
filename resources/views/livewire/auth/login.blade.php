@php use Illuminate\Support\Facades\Route; @endphp


@php
$isAdminLogin = request()->routeIs('admin.login');
@endphp

<div class="flex flex-col gap-6">
    <x-auth-header :title="$isAdminLogin ? __('Log in to the Admin Site') : __('Log in to your account')"
        :description="$isAdminLogin ? __('Enter your admin email and password below to log in') : __('Enter your email and password below to log in')" />

    <!-- Session Status -->
    <x-auth-session-status class="text-center" :status="session('status')" />

    <form method="POST" wire:submit="login" class="flex flex-col gap-6">
        <!-- Email Address -->
        <flux:input wire:model="email" :label="__('Email address')" type="email" required autofocus autocomplete="email"
            placeholder="email@example.com" />

        <!-- Password -->
        <div class="relative">
            <flux:input wire:model="password" :label="__('Password')" type="password" required
                autocomplete="current-password" :placeholder="__('Password')" viewable />

            @if (Route::has('password.request'))
            <flux:link class="absolute end-0 top-0 text-sm text-teal-500" :href="route('password.request')"
                wire:navigate>
                {{ __('Forgot your password?') }}
            </flux:link>
            @endif
        </div>

        <!-- Remember Me -->
        <flux:checkbox wire:model="remember" :label="__('Remember me')" />

        <div class="flex items-center justify-end">
            <flux:button variant="primary" type="submit"
                class="w-full bg-teal-500 hover:bg-teal-600 focus:ring-teal-500">{{ __('Log In') }}</flux:button>
        </div>
    </form>

    @if (Route::has('register'))
    <div class="space-x-1 rtl:space-x-reverse text-center text-sm text-zinc-600 dark:text-zinc-400">
        <span>{{ __('Don\'t have an account?') }}</span>
        <flux:link :href="route('register', $applicantCode ? ['code' => $applicantCode] : [])" wire:navigate
            class="text-teal-500">{{ __('Sign up') }}</flux:link>
    </div>
    @endif
</div>