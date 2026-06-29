@extends('layouts.member-portal')

@section('content')
<section class="mx-auto max-w-3xl px-4 py-10 sm:px-6 lg:px-8">
    <div class="overflow-hidden rounded-3xl border border-emerald-200 bg-gradient-to-br from-emerald-50 to-white p-8 text-center shadow-sm">
        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-emerald-100 text-2xl text-emerald-700">
            <i class="fa-solid fa-circle-check"></i>
        </div>
        <h1 class="mt-5 text-2xl font-black text-slate-900">Request submitted successfully</h1>
        <p class="mt-2 text-sm text-slate-600">Your {{ strtolower($helpRequest->typeLabel()) }} has been sent to the team.</p>

        <div class="mx-auto mt-6 max-w-md rounded-2xl border border-slate-200 bg-white px-5 py-4 text-left">
            <p class="text-xs font-bold uppercase tracking-wide text-slate-500">Reference code</p>
            <p class="mt-1 font-mono text-lg font-black text-teal-800">{{ $helpRequest->referenceCode() }}</p>
            <dl class="mt-4 space-y-2 text-sm">
                <div class="flex justify-between gap-4"><dt class="text-slate-500">Type</dt><dd class="font-semibold text-slate-900">{{ $helpRequest->typeLabel() }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-slate-500">Category</dt><dd class="font-semibold text-slate-900">{{ $helpRequest->categoryLabel() }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-slate-500">Subject</dt><dd class="text-right font-semibold text-slate-900">{{ $helpRequest->subject }}</dd></div>
                @if($helpRequest->facility)
                <div class="flex justify-between gap-4"><dt class="text-slate-500">Facility</dt><dd class="font-semibold text-slate-900">{{ $helpRequest->facility->name }}</dd></div>
                @endif
            </dl>
        </div>

        <p class="mx-auto mt-6 max-w-lg text-sm leading-relaxed text-slate-600">
            We typically respond within one to two business days using your preferred contact method. You can track status anytime from your help request history.
        </p>

        <div class="mt-8 flex flex-wrap justify-center gap-3">
            <a href="{{ route('member.help.show', $helpRequest) }}" class="inline-flex items-center rounded-xl bg-teal-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-teal-700">View request details</a>
            <a href="{{ route('member.help.index') }}" class="inline-flex items-center rounded-xl border border-slate-300 px-5 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">All my requests</a>
            <a href="{{ route('dashboard.index') }}" class="inline-flex items-center rounded-xl border border-slate-300 px-5 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">Back to dashboard</a>
        </div>
    </div>
</section>
@endsection
