@extends('layouts.member-portal')

@section('content')
<section class="mx-auto max-w-3xl space-y-6 px-4 py-6 sm:px-6 lg:px-8">
    <a href="{{ route('member.help.index') }}" class="text-sm font-semibold text-teal-700 hover:text-teal-900">&larr; All help requests</a>

    <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-100 bg-slate-50 px-6 py-5">
            <div class="flex flex-wrap items-center gap-2">
                <span class="rounded-full px-2.5 py-0.5 text-[11px] font-semibold {{ $helpRequest->isHrInquiry() ? 'bg-teal-100 text-teal-800' : 'bg-indigo-100 text-indigo-800' }}">{{ $helpRequest->typeLabel() }}</span>
                <span class="rounded-full bg-slate-200 px-2.5 py-0.5 text-[11px] font-semibold text-slate-700">{{ $helpRequest->categoryLabel() }}</span>
                <span class="rounded-full px-2.5 py-0.5 text-[11px] font-semibold {{ $helpRequest->status === 'resolved' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">{{ ucfirst(str_replace('_', ' ', $helpRequest->status)) }}</span>
            </div>
            <h1 class="mt-3 text-2xl font-black text-slate-900">{{ $helpRequest->subject }}</h1>
            <p class="mt-1 text-sm text-slate-500">{{ $helpRequest->referenceCode() }} · Submitted {{ $helpRequest->created_at->format('M j, Y g:i A') }}</p>
        </div>

        <div class="space-y-5 px-6 py-6">
            <dl class="grid gap-3 sm:grid-cols-2 text-sm">
                <div><dt class="text-slate-500">Contact</dt><dd class="font-semibold text-slate-900">{{ $helpRequest->name }} · {{ $helpRequest->email }}</dd></div>
                @if($helpRequest->phone)<div><dt class="text-slate-500">Phone</dt><dd class="font-semibold text-slate-900">{{ $helpRequest->phone }}</dd></div>@endif
                @if($helpRequest->facility)<div><dt class="text-slate-500">Facility</dt><dd class="font-semibold text-slate-900">{{ $helpRequest->facility->name }}</dd></div>@endif
                <div><dt class="text-slate-500">Preferred contact</dt><dd class="font-semibold text-slate-900">{{ config('portal-help.preferred_contact_options.' . $helpRequest->preferred_contact, $helpRequest->preferred_contact) }}</dd></div>
            </dl>

            <div>
                <h2 class="text-sm font-bold uppercase tracking-wide text-slate-500">Message</h2>
                <p class="mt-2 whitespace-pre-line text-sm text-slate-800">{{ $helpRequest->message }}</p>
            </div>

            @if($helpRequest->steps_to_reproduce)
            <div>
                <h2 class="text-sm font-bold uppercase tracking-wide text-slate-500">Steps to reproduce</h2>
                <p class="mt-2 whitespace-pre-line text-sm text-slate-800">{{ $helpRequest->steps_to_reproduce }}</p>
            </div>
            @endif

            @if(!empty($helpRequest->attachments))
            <div>
                <h2 class="text-sm font-bold uppercase tracking-wide text-slate-500">Attachments</h2>
                <div class="mt-2 flex flex-wrap gap-3">
                    @foreach($helpRequest->attachments as $attachment)
                    <a href="{{ asset('storage/' . $attachment) }}" target="_blank" class="text-sm font-semibold text-teal-700 hover:underline">Download file</a>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </div>
</section>
@endsection
