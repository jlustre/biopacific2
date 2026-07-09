@extends('layouts.member-portal')

@section('content')
<section class="mx-auto max-w-6xl px-4 py-4 sm:px-6 lg:py-5">
    <div class="flex flex-wrap items-start justify-between gap-3 rounded-2xl border border-slate-200 bg-white px-4 py-3 shadow-sm sm:px-5">
        <div class="min-w-0">
            <p class="text-[11px] font-bold uppercase tracking-wide text-slate-500">Work queue</p>
            <h1 class="mt-0.5 text-lg font-black text-slate-900 sm:text-xl">My Tasks</h1>
            <p class="mt-1 max-w-3xl text-xs leading-relaxed text-slate-600 sm:text-sm">
                System-assigned work items plus personal tasks you create. Complete system items in their linked screens;
                personal tasks assigned to others need your confirmation once marked done.
            </p>
        </div>
        <a href="{{ route('dashboard.index') }}"
           class="rounded-full border border-slate-200 bg-white px-3 py-1.5 text-xs font-bold text-slate-700 shadow-sm transition hover:border-teal-300 hover:bg-teal-50">
            Back to dashboard
        </a>
    </div>

    <div class="mt-4">
        <livewire:member.personal-tasks-table />
    </div>
</section>
@endsection
