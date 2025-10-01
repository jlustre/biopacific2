@extends('layouts.dashboard')

@section('content')
<div class="max-w-2xl mx-auto py-8">
    <h1 class="text-2xl font-bold mb-6">Webmaster Contact Details</h1>
    <div class="bg-white rounded-xl shadow p-6">
        <div class="mb-4">
            <div class="text-xs text-slate-400 mb-1">Submitted at: {{ $contact->created_at->format('Y-m-d H:i') }}</div>
            <div class="text-xs text-slate-400 mb-1">
                Facility:
                @if($contact->facility)
                <a href="{{ route('facility.public', $contact->facility->slug) }}"
                    class="underline text-blue-700 hover:text-blue-900" target="_blank">
                    {{ $contact->facility->name }}
                </a>
                @else
                <span class="text-slate-400">—</span>
                @endif
            </div>
            <div class="text-xs text-slate-400 mb-1">
                <span>Status:</span>
                <span class="font-semibold">{{ ucfirst($contact->status) }}</span>
                @if($contact->resolved_at)
                <span class="ml-2">| Resolved at: {{ $contact->resolved_at->format('Y-m-d H:i') }}</span>
                @endif
            </div>
            <div class="text-lg font-semibold">{{ $contact->subject }}</div>
            <div class="text-sm text-slate-600">From: {{ $contact->name }} ({{ $contact->email }})</div>
            <div class="mt-2">
                <span
                    class="inline-block px-2 py-1 rounded text-xs font-semibold {{ $contact->urgent ? 'bg-red-100 text-red-700' : 'bg-slate-100 text-slate-500' }}">
                    {{ $contact->urgent ? 'Urgent' : 'Normal' }}
                </span>
            </div>
        </div>
        <div class="mb-6">
            <div class="font-semibold mb-1">Message:</div>
            <div class="whitespace-pre-line text-slate-800">{{ $contact->message }}</div>
        </div>
        @if($contact->screenshots && count($contact->screenshots))
        <div class="mb-6">
            <div class="font-semibold mb-2">Screenshots:</div>
            <div class="flex flex-wrap gap-4">
                @foreach($contact->screenshots as $screenshot)
                <a href="{{ asset('storage/' . $screenshot) }}" target="_blank" class="block">
                    <img src="{{ asset('storage/' . $screenshot) }}" alt="Screenshot"
                        class="h-24 rounded shadow border border-slate-200 hover:scale-105 transition" />
                </a>
                @endforeach
            </div>
        </div>
        @endif
        <form method="POST" action="{{ route('admin.webmaster.contacts.update', ['contact' => $contact->id]) }}"
            class="inline-block align-middle mr-2">
            @csrf
            <label for="status" class="text-xs mr-1">Status:</label>
            <select name="status" id="status" class="rounded border-slate-300 text-xs py-1 px-2">
                <option value="open" @if($contact->status=='open') selected @endif>Open</option>
                <option value="in_progress" @if($contact->status=='in_progress') selected @endif>In Progress</option>
                <option value="resolved" @if($contact->status=='resolved') selected @endif>Resolved</option>
            </select>
            <button type="submit"
                class="ml-2 px-2 py-1 rounded bg-blue-600 text-white text-xs hover:bg-blue-700">Save</button>
        </form>
        <a href="{{ route('admin.webmaster.contacts.index') }}"
            class="inline-block mt-4 text-blue-600 hover:underline text-sm">&larr; Back to all submissions</a>
    </div>
</div>
@endsection