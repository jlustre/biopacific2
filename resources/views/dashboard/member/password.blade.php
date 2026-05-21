@extends('layouts.member-portal')

@section('header-actions')
<button type="submit" form="password-form" class="rounded-2xl bg-teal-600 px-4 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-teal-700">
    Save Password
</button>
@endsection

@section('content')
<section class="px-4 py-6 sm:px-6 lg:px-8">
    @include('dashboard.member.partials.portal-page-hero', [
        'badge' => 'Account Security',
        'title' => 'Change Password',
        'subtitle' => 'Use a long, random password to keep your account secure.',
    ])

    <div class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-card sm:p-8">
        @if(session('status') === 'password-updated')
        <div class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">
            Password updated successfully.
        </div>
        @endif

        <form id="password-form" method="POST" action="{{ route('settings.password.update') }}" class="mx-auto max-w-lg space-y-5">
            @csrf
            @method('PUT')

            <div>
                <label for="current_password" class="mb-1.5 block text-sm font-bold text-slate-700">Current password</label>
                <input type="password" id="current_password" name="current_password" required autocomplete="current-password"
                    class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm outline-none ring-teal-500/20 focus:border-teal-400 focus:ring-4" />
                @error('current_password', 'updatePassword')
                <p class="mt-1 text-sm text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password" class="mb-1.5 block text-sm font-bold text-slate-700">New password</label>
                <input type="password" id="password" name="password" required autocomplete="new-password"
                    class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm outline-none ring-teal-500/20 focus:border-teal-400 focus:ring-4" />
                @error('password', 'updatePassword')
                <p class="mt-1 text-sm text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password_confirmation" class="mb-1.5 block text-sm font-bold text-slate-700">Confirm new password</label>
                <input type="password" id="password_confirmation" name="password_confirmation" required autocomplete="new-password"
                    class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm outline-none ring-teal-500/20 focus:border-teal-400 focus:ring-4" />
                @error('password_confirmation', 'updatePassword')
                <p class="mt-1 text-sm text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex flex-wrap items-center gap-3 pt-2">
                <button type="submit" class="rounded-2xl bg-teal-600 px-5 py-3 text-sm font-bold text-white hover:bg-teal-700">Update Password</button>
                <a href="{{ route('settings.profile') }}" class="rounded-2xl border border-slate-200 px-5 py-3 text-sm font-bold text-slate-700 hover:bg-slate-50">Back to Profile</a>
            </div>
        </form>
    </div>
</section>
@endsection
