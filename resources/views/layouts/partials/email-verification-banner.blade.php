@php
    $bannerUser = $user ?? auth()->user();
@endphp
@if($bannerUser && method_exists($bannerUser, 'hasVerifiedEmail') && ! $bannerUser->hasVerifiedEmail())
<div class="border-b border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-950">
    <div class="mx-auto flex max-w-7xl flex-wrap items-center justify-between gap-3">
        <p>
            <span class="font-semibold">Verify your email address</span>
            to unlock HR tools and facility management. Check your inbox or
            <a href="{{ route('verification.notice') }}" class="font-semibold text-teal-700 underline hover:text-teal-900">resend the verification link</a>.
        </p>
    </div>
</div>
@endif
