@extends('layouts.member-portal')

@section('content')
<section class="px-4 py-6 sm:px-6 lg:px-8">
    @include('dashboard.member.partials.portal-page-hero', [
        'badge' => 'Employment Onboarding',
        'title' => 'Welcome, ' . ($firstNameOnly ?? 'there') . '!',
        'subtitle' => 'Complete your employment forms and policy acknowledgements to finish onboarding.',
    ])

    <div class="grid gap-4 sm:grid-cols-3">
        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-card">
            <p class="text-xs font-bold uppercase tracking-wide text-slate-500">Checklist items</p>
            <p class="mt-2 text-3xl font-black text-slate-950">{{ count($checklistDefaults) }}</p>
        </div>
        <div class="rounded-3xl border border-emerald-200 bg-emerald-50 p-5 shadow-card">
            <p class="text-xs font-bold uppercase tracking-wide text-emerald-700">Completed</p>
            <p class="mt-2 text-3xl font-black text-emerald-900">0</p>
        </div>
        <div class="rounded-3xl border border-amber-200 bg-amber-50 p-5 shadow-card">
            <p class="text-xs font-bold uppercase tracking-wide text-amber-700">Remaining</p>
            <p class="mt-2 text-3xl font-black text-amber-900">{{ count($checklistDefaults) }}</p>
        </div>
    </div>

    <section class="mt-6 overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-card">
        <div class="border-b border-slate-200 bg-teal-50 p-6">
            <h2 class="text-lg font-bold text-slate-950">Onboarding Checklist</h2>
            <p class="mt-1 text-sm text-slate-500">Required steps for your employment record</p>
        </div>
        <ul class="divide-y divide-slate-100 p-6">
            @foreach($checklistDefaults as $item)
            <li class="flex items-center gap-4 py-4 first:pt-0 last:pb-0">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-slate-100 text-slate-400">
                    <i class="fa-regular fa-circle"></i>
                </span>
                <div class="min-w-0 flex-1">
                    <p class="font-bold text-slate-950">{{ $item['label'] }}</p>
                    <p class="text-xs text-slate-500">Not started</p>
                </div>
                <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-600">Pending</span>
            </li>
            @endforeach
        </ul>
    </section>

    <div class="mt-6 rounded-2xl border border-slate-200 bg-slate-50 p-5 text-sm text-slate-600">
        <p class="font-bold text-slate-900">Need help?</p>
        <p class="mt-1">Contact your facility HR team for assistance with employment forms and benefits enrollment.</p>
    </div>
</section>
@endsection
