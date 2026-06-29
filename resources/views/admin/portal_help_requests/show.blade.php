@extends('layouts.dashboard')

@section('content')
<div class="mx-auto max-w-3xl py-8">
    <h1 class="mb-6 text-2xl font-bold">{{ $request->typeLabel() }} details</h1>

    @if(session('success'))
    <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-800">{{ session('success') }}</div>
    @endif

    <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        <p class="font-mono text-sm text-teal-700">{{ $request->referenceCode() }}</p>
        <h2 class="mt-2 text-xl font-bold text-slate-900">{{ $request->subject }}</h2>

        <dl class="mt-4 grid gap-2 text-sm sm:grid-cols-2">
            <div><dt class="text-slate-500">Submitted</dt><dd>{{ $request->created_at->format('M j, Y g:i A') }}</dd></div>
            <div><dt class="text-slate-500">Category</dt><dd>{{ $request->categoryLabel() }}</dd></div>
            <div><dt class="text-slate-500">Priority</dt><dd>{{ ucfirst($request->priority) }}</dd></div>
            <div><dt class="text-slate-500">Status</dt><dd>{{ ucfirst(str_replace('_', ' ', $request->status)) }}</dd></div>
            <div><dt class="text-slate-500">From</dt><dd>{{ $request->name }} ({{ $request->email }})</dd></div>
            @if($request->phone)<div><dt class="text-slate-500">Phone</dt><dd>{{ $request->phone }}</dd></div>@endif
            @if($request->employee_num)<div><dt class="text-slate-500">Employee #</dt><dd>{{ $request->employee_num }}</dd></div>@endif
            @if($request->facility)<div><dt class="text-slate-500">Facility</dt><dd>{{ $request->facility->name }}</dd></div>@endif
            <div><dt class="text-slate-500">Preferred contact</dt><dd>{{ config('portal-help.preferred_contact_options.' . $request->preferred_contact, $request->preferred_contact) }}</dd></div>
            @if($request->best_time_to_reach)<div><dt class="text-slate-500">Best time</dt><dd>{{ config('portal-help.best_time_options.' . $request->best_time_to_reach, $request->best_time_to_reach) }}</dd></div>@endif
        </dl>

        <div class="mt-6">
            <h3 class="text-sm font-bold text-slate-700">Message</h3>
            <p class="mt-2 whitespace-pre-line text-sm text-slate-800">{{ $request->message }}</p>
        </div>

        @if($request->steps_to_reproduce)
        <div class="mt-4">
            <h3 class="text-sm font-bold text-slate-700">Steps to reproduce</h3>
            <p class="mt-2 whitespace-pre-line text-sm text-slate-800">{{ $request->steps_to_reproduce }}</p>
        </div>
        @endif

        @if(!empty($request->attachments))
        <div class="mt-4">
            <h3 class="text-sm font-bold text-slate-700">Attachments</h3>
            <div class="mt-2 flex flex-wrap gap-3">
                @foreach($request->attachments as $attachment)
                <a href="{{ asset('storage/' . $attachment) }}" target="_blank" class="block">
                    <img src="{{ asset('storage/' . $attachment) }}" alt="Attachment" class="h-24 rounded border border-slate-200 object-cover" onerror="this.style.display='none'; this.nextElementSibling.style.display='inline'">
                    <span style="display:none" class="text-sm text-blue-600 underline">Download attachment</span>
                </a>
                @endforeach
            </div>
        </div>
        @endif

        <form method="POST" action="{{ route('admin.portal-help-requests.update', $request) }}" class="mt-6 inline-flex items-center gap-2">
            @csrf
            <label for="status" class="text-xs font-semibold text-slate-600">Status</label>
            <select name="status" id="status" class="rounded border-slate-300 text-xs py-1 px-2">
                @foreach(['open','in_progress','resolved'] as $status)
                <option value="{{ $status }}" @selected($request->status===$status)>{{ ucfirst(str_replace('_',' ', $status)) }}</option>
                @endforeach
            </select>
            <button type="submit" class="rounded bg-blue-600 px-3 py-1 text-xs font-semibold text-white hover:bg-blue-700">Save</button>
        </form>

        <a href="{{ route('admin.portal-help-requests.index') }}" class="mt-6 inline-block text-sm text-blue-600 hover:underline">&larr; Back to all requests</a>
    </div>
</div>
@endsection
