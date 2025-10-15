@extends('layouts.dashboard', ['title','Profile Settings'])
@section('content')
<div class="container mx-auto p-6">
    <h1 class="text-2xl font-bold mb-4">Profile Settings</h1>
    <form wire:submit="updateProfileInformation" class="my-6 w-full space-y-6">
        <flux:input wire:model="name" :label="__('Name')" type="text" required autofocus autocomplete="name" />

        <div>
            <flux:input wire:model="email" :label="__('Email')" type="email" required autocomplete="email" />

            @if (auth()->user() instanceof \Illuminate\Contracts\Auth\MustVerifyEmail &&!
            auth()->user()->hasVerifiedEmail())
            <div>
                <flux:text class="mt-4">
                    {{ __('Your email address is unverified.') }}

                    <flux:link class="text-sm cursor-pointer" wire:click.prevent="resendVerificationNotification">
                        {{ __('Click here to re-send the verification email.') }}
                    </flux:link>
                </flux:text>

                @if (session('status') === 'verification-link-sent')
                <flux:text class="mt-2 font-medium !dark:text-green-400 !text-green-600">
                    {{ __('A new verification link has been sent to your email address.') }}
                </flux:text>
                @endif
            </div>
            @endif
        </div>

        <div class="mb-4">
            <label class="block mb-1 font-semibold">Role</label>
            <select name="role" wire:model="role" class="w-full border px-3 py-2 rounded" required>
                @foreach($roles as $roleOption)
                <option value="{{ $roleOption }}" {{ ($role==$roleOption) ? 'selected' : '' }}>{{
                    ucfirst($roleOption) }}</option>
                @endforeach
            </select>
        </div>

        <div class="flex items-center gap-4">
            <div class="flex items-center justify-end">
                <flux:button variant="primary" type="submit" class="w-full">{{ __('Save') }}</flux:button>
            </div>

            <x-action-message class="me-3" on="profile-updated">
                {{ __('Saved.') }}
            </x-action-message>
        </div>
    </form>

    @if(auth()->user()->hasRole('admin'))
    <livewire:settings.delete-user-form />
    @endif
</div>
@endsection