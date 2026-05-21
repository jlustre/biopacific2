@extends('layouts.dashboard', ['title' => 'Admin Dashboard'])

@section('content')
@php
    $adminName = auth()->user()->name ?? 'Admin';
    $activeCount = $facilities->where('is_active', true)->count();
    $todayLabel = now()->format('l, F j, Y');
@endphp

<div class="mb-6">
@include('dashboard.member.partials.portal-page-hero', [
    'badge' => 'System Administration · ' . $todayLabel,
    'title' => 'Welcome back, ' . $adminName,
    'subtitle' => 'Overview of facilities, users, and recent system activity.',
])
</div>

@include('admin.dashboard.partials.stats')

<div class="mt-6 grid gap-6 md:grid-cols-2">
    <section class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-card">
        <div class="flex items-start justify-between gap-4">
            <div>
                <div class="inline-flex items-center gap-2 rounded-full bg-teal-50 px-3 py-1 text-xs font-bold uppercase tracking-wide text-teal-800">
                    <i class="fa-solid fa-building"></i> Facilities
                </div>
                <p class="mt-3 text-4xl font-black text-slate-950">{{ $facilities->count() }}</p>
                <p class="mt-1 text-sm text-slate-500">Total registered facilities</p>
            </div>
            <span class="rounded-2xl bg-teal-50 p-3 text-2xl text-teal-700"><i class="fa-solid fa-building"></i></span>
        </div>
        <a href="{{ route('admin.facilities.index') }}" class="mt-5 inline-flex items-center gap-2 rounded-2xl bg-teal-600 px-4 py-2.5 text-sm font-bold text-white hover:bg-teal-700">
            Manage Facilities <i class="fa-solid fa-arrow-right text-xs"></i>
        </a>
    </section>

    <section class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-card">
        <div class="flex items-start justify-between gap-4">
            <div>
                <div class="inline-flex items-center gap-2 rounded-full bg-emerald-50 px-3 py-1 text-xs font-bold uppercase tracking-wide text-emerald-800">
                    <i class="fa-solid fa-circle-check"></i> Active
                </div>
                <p class="mt-3 text-4xl font-black text-slate-950">{{ $activeCount }}</p>
                <p class="mt-1 text-sm text-slate-500">Currently active facilities</p>
            </div>
            <span class="rounded-2xl bg-emerald-50 p-3 text-2xl text-emerald-700"><i class="fa-solid fa-circle-check"></i></span>
        </div>
        <a href="{{ route('admin.facilities.index', ['status' => 'active']) }}" class="mt-5 inline-flex items-center gap-2 rounded-2xl border border-teal-200 px-4 py-2.5 text-sm font-bold text-teal-700 hover:bg-teal-50">
            View Active <i class="fa-solid fa-arrow-right text-xs"></i>
        </a>
    </section>
</div>

<div class="mt-6">
    @include('admin.dashboard.partials.quick_actions')
</div>

<section class="mt-6 overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-card">
    <div class="flex items-center justify-between border-b border-slate-200 bg-teal-50 px-6 py-4">
        <h2 class="text-lg font-bold text-slate-950">Recent Activity</h2>
        <a href="#" class="text-sm font-semibold text-teal-700 hover:text-teal-800">View all</a>
    </div>
    <div class="divide-y divide-slate-100 px-6">
        @forelse($recentActivities ?? [] as $activity)
            @if(is_object($activity) && isset($activity->description, $activity->created_at))
            <div class="flex items-center gap-3 py-4">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-slate-100 text-slate-500">
                    <i class="fa-regular fa-clock"></i>
                </span>
                <div class="min-w-0 flex-1">
                    <p class="font-semibold text-slate-950">{{ $activity->description }}</p>
                    <p class="text-xs text-slate-500">{{ $activity->created_at->diffForHumans() }}</p>
                </div>
            </div>
            @endif
        @empty
            <div class="py-12 text-center text-slate-400">
                <i class="fa-regular fa-clock mb-2 text-3xl"></i>
                <p>No recent activity to display.</p>
            </div>
        @endforelse
    </div>
</section>
@endsection
