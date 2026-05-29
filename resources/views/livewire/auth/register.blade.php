<div class="flex flex-col gap-6">
    <x-auth-header :title="__('Create an account')"
        :description="__('Enter your registration code and employee details to create your account')" />

    <!-- Session Status -->
    <x-auth-session-status class="text-center" :status="session('status')" />

    <form method="POST" wire:submit="register" class="flex flex-col gap-6">
        <flux:input wire:model.live="registrationCode" :label="__('Registration code')" type="text" required autofocus
            autocomplete="off" placeholder="E-XXXXXX or T-XXXXXX" />

        <flux:input wire:model="name" :label="__('Name')" type="text" required autocomplete="name"
            :placeholder="__('Full name')" />

        <flux:input wire:model="email" :label="__('Email address')" type="email" required autocomplete="email"
            placeholder="email@example.com" />

        @if($requiresIdentityVerification)
            <flux:input wire:model="identityVerification" :label="__('Employee # or SSN last 4')" type="text" required
                autocomplete="off" placeholder="EMP001 or 1234" />
        @endif

        <flux:input wire:model="password" :label="__('Password')" type="password" required autocomplete="new-password"
            :placeholder="__('Password')" viewable />

        <flux:input wire:model="password_confirmation" :label="__('Confirm password')" type="password" required
            autocomplete="new-password" :placeholder="__('Confirm password')" viewable />

        <div class="flex items-center justify-end">
            <flux:button type="submit" variant="primary"
                class="w-full bg-teal-500 hover:bg-teal-600 focus:ring-teal-500">
                {{ __('Create Account') }}
            </flux:button>
        </div>
    </form>

    <div class="space-x-1 rtl:space-x-reverse text-center text-sm text-zinc-600 dark:text-zinc-400">
        <span>{{ __('Already have an account?') }}</span>
        <flux:link :href="route('login', $registrationCode ? ['code' => $registrationCode] : [])" wire:navigate
            class="text-teal-500">{{ __('Log in') }}</flux:link>
    </div>
</div>
