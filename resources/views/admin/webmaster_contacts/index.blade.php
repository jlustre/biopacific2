@extends('layouts.dashboard')

@section('content')
<div class="max-w-4xl mx-auto py-8">
    <h1 class="text-2xl font-bold mb-6">Webmaster Contact Submissions</h1>
    <div class="bg-white rounded-xl shadow p-6">
        <!-- Filter Form -->
        <form method="GET" class="mb-6 flex flex-wrap gap-3 items-end">
            <div>
                <label for="status" class="block text-xs font-semibold text-slate-600 mb-1">Status</label>
                <select name="status" id="status" class="rounded border-slate-300 text-xs py-1 px-2">
                    <option value="">All</option>
                    <option value="open" @if(request('status')=='open' ) selected @endif>Open</option>
                    <option value="in_progress" @if(request('status')=='in_progress' ) selected @endif>In Progress
                    </option>
                    <option value="resolved" @if(request('status')=='resolved' ) selected @endif>Resolved</option>
                </select>
            </div>
            <div>
                <label for="urgent" class="block text-xs font-semibold text-slate-600 mb-1">Urgency</label>
                <select name="urgent" id="urgent" class="rounded border-slate-300 text-xs py-1 px-2">
                    <option value="">All</option>
                    <option value="1" @if(request('urgent','')==='1' ) selected @endif>Urgent</option>
                    <option value="0" @if(request('urgent','')==='0' ) selected @endif>Normal</option>
                </select>
            </div>
            <div>
                <label for="facility_id" class="block text-xs font-semibold text-slate-600 mb-1">Facility</label>
                <select name="facility_id" id="facility_id" class="rounded border-slate-300 text-xs py-1 px-2">
                    <option value="">All</option>
                    @foreach($facilities as $facility)
                    <option value="{{ $facility->id }}" @if(request('facility_id')==$facility->id) selected @endif>{{
                        $facility->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="search" class="block text-xs font-semibold text-slate-600 mb-1">Search</label>
                <input type="text" name="search" id="search" value="{{ request('search') }}"
                    placeholder="Name, email, subject..." class="rounded border-slate-300 text-xs py-1 px-2">
            </div>
            <div>
                <button type="submit"
                    class="bg-blue-600 text-white rounded px-4 py-1.5 text-xs font-semibold hover:bg-blue-700">Filter</button>
                <a href="{{ route('admin.webmaster.contacts.index') }}"
                    class="ml-2 text-xs text-slate-500 underline">Reset</a>
            </div>
        </form>
        <table class="min-w-full divide-y divide-slate-200">
            <thead>
                <tr>
                    <th class="px-3 py-2 text-left text-xs font-semibold text-slate-600">Date</th>
                    <th class="px-3 py-2 text-left text-xs font-semibold text-slate-600">Facility</th>
                    <th class="px-3 py-2 text-left text-xs font-semibold text-slate-600">Name</th>
                    <th class="px-3 py-2 text-left text-xs font-semibold text-slate-600">Subject</th>
                    <th class="px-3 py-2 text-left text-xs font-semibold text-slate-600">Urgent</th>
                    <th class="px-3 py-2 text-left text-xs font-semibold text-slate-600">Status</th>
                    <th class="px-3 py-2 text-left text-xs font-semibold text-slate-600">Resolved At</th>
                    <th class="px-3 py-2"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($contacts as $contact)
                <tr>
                    <td class="px-3 py-2 text-xs text-slate-500">{{ $contact->created_at->format('Y-m-d H:i') }}</td>
                    <td class="px-3 py-2 text-xs text-slate-500">
                        @if($contact->facility)
                        <a href="{{ route('facility.public', $contact->facility->slug) }}"
                            class="underline text-blue-700 hover:text-blue-900" target="_blank">
                            {{ $contact->facility->name }}
                        </a>
                        @else
                        <span class="text-slate-400">—</span>
                        @endif
                    </td>
                    <td class="px-3 py-2 text-sm">{{ $contact->name }}</td>
                    <td class="px-3 py-2 text-sm">{{ $contact->subject }}</td>
                    <td class="px-3 py-2 text-sm">
                        @if($contact->urgent)
                        <span
                            class="inline-block px-2 py-1 bg-red-100 text-red-700 rounded text-xs font-semibold">Urgent</span>
                        @else
                        <span class="inline-block px-2 py-1 bg-slate-100 text-slate-500 rounded text-xs">Normal</span>
                        @endif
                    </td>
                    <td class="px-3 py-2 text-xs">
                        <span
                            class="inline-block px-2 py-1 rounded text-xs font-semibold {{ $contact->status === 'resolved' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">
                            {{ ucfirst($contact->status) }}
                        </span>
                    </td>
                    <td class="px-3 py-2 text-xs text-slate-500">
                        @if($contact->resolved_at)
                        {{ $contact->resolved_at->format('Y-m-d H:i') }}
                        @else
                        &mdash;
                        @endif
                    </td>
                    <td class="px-3 py-2 text-right">
                        <a href="{{ route('admin.webmaster.contacts.show', $contact) }}"
                            class="text-blue-600 hover:underline text-xs">View</a>
                        <form method="POST"
                            action="{{ route('admin.webmaster.contacts.destroy', ['contact' => $contact->id]) }}"
                            style="display:inline;"
                            onsubmit="return confirm('Are you sure you want to delete this submission?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:underline text-xs ml-2">Delete</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center text-slate-400 py-8">No submissions found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        <div class="mt-4">{{ $contacts->links() }}</div>
    </div>
</div>
@endsection