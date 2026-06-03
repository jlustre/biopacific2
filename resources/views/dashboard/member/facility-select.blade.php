@extends('layouts.member-portal')

@section('content')
<section class="mx-auto max-w-3xl px-4 py-8 sm:px-6">
    <h1 class="text-2xl font-black text-slate-900">Select a facility</h1>
    <p class="mt-2 text-sm text-slate-600">Choose a facility to open its dashboard.</p>

    @if($facilities->isEmpty())
    <p class="mt-6 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
        No facilities are available for your account.
    </p>
    @else
    <ul class="mt-6 divide-y divide-slate-200 rounded-2xl border border-slate-200 bg-white shadow-sm">
        @foreach($facilities as $f)
        <li>
            <a href="{{ route($facilitySwitchRoute ?? 'member.facility.dashboard', ['facility' => $f->slug ?? $f->id]) }}"
               class="flex items-center justify-between px-4 py-3 text-sm font-semibold text-teal-700 hover:bg-teal-50">
                <span>{{ $f->name }}</span>
                <span class="text-teal-500">→</span>
            </a>
        </li>
        @endforeach
    </ul>
    @endif
</section>
@endsection
