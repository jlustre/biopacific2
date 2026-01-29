@extends('layouts.dashboard', ['title' => 'System Settings'])

@php
use App\Models\Setting;
$site_name = Setting::where('key', 'site_name')->value('value');
$site_email = Setting::where('key', 'site_email')->value('value');
$theme = Setting::where('key', 'theme')->value('value');
$enable_mfa = Setting::where('key', 'enable_mfa')->value('value');
@endphp

@section('content')
<div class="max-w-3xl mx-auto py-8">
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-8">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">System Settings</h1>
        <p class="text-gray-600 dark:text-gray-400 mb-6">Configure global settings for your application. Only
            administrators can access this page.</p>
        @if(session('status'))
        <div class="mb-4 text-green-600 text-center font-semibold">{{ session('status') }}</div>
        @endif
        <form method="POST" action="{{ route('admin.settings.update') }}" class="space-y-8">
            @csrf
            <div class="border-b pb-6 mb-6">
                <h2 class="text-lg font-semibold mb-2">General</h2>
                <div class="mb-4">
                    <label for="site_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Site
                        Name</label>
                    <input type="text" id="site_name" name="site_name"
                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white"
                        value="{{ $site_name }}" placeholder="BioPacific" />
                </div>
                <div class="mb-4">
                    <label for="site_email" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Contact
                        Email</label>
                    <input type="email" id="site_email" name="site_email"
                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white"
                        value="{{ $site_email }}" placeholder="admin@biopacificoperational.com" />
                </div>
            </div>
            <div class="border-b pb-6 mb-6">
                <h2 class="text-lg font-semibold mb-2">Appearance</h2>
                <div class="mb-4">
                    <label for="theme" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Theme</label>
                    <select id="theme" name="theme"
                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white">
                        <option value="light" @if($theme=="light" ) selected @endif>Light</option>
                        <option value="dark" @if($theme=="dark" ) selected @endif>Dark</option>
                        <option value="system" @if($theme=="system" ) selected @endif>System Default</option>
                    </select>
                </div>
            </div>
            <div>
                <h2 class="text-lg font-semibold mb-2">Security</h2>
                <div class="mb-4">
                    <label for="enable_mfa" class="inline-flex items-center">
                        <input type="checkbox" id="enable_mfa" name="enable_mfa"
                            class="rounded border-gray-300 dark:border-gray-700 text-indigo-600 focus:ring-indigo-500"
                            @if($enable_mfa=="1" ) checked @endif />
                        <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Enable Multi-Factor Authentication
                            (MFA)</span>
                    </label>
                </div>
            </div>
            <div class="pt-6">
                <button type="submit"
                    class="px-6 py-2 bg-indigo-600 text-white rounded-lg font-semibold shadow hover:bg-indigo-700 transition">Save
                    Settings</button>
            </div>
        </form>
    </div>
</div>
@endsection