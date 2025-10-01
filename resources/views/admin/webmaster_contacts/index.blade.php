@extends('layouts.dashboard')

@section('content')
<div class="max-w-4xl mx-auto py-8">
    <h1 class="text-2xl font-bold mb-6">Webmaster Contact Submissions</h1>
    <div class="bg-white rounded-xl shadow p-6">
        <table class="min-w-full divide-y divide-slate-200">
            <thead>
                <tr>
                    <th class="px-3 py-2 text-left text-xs font-semibold text-slate-600">Date</th>
                    <th class="px-3 py-2 text-left text-xs font-semibold text-slate-600">Name</th>
                    <th class="px-3 py-2 text-left text-xs font-semibold text-slate-600">Subject</th>
                    <th class="px-3 py-2 text-left text-xs font-semibold text-slate-600">Urgent</th>
                    <th class="px-3 py-2"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($contacts as $contact)
                <tr>
                    <td class="px-3 py-2 text-xs text-slate-500">{{ $contact->created_at->format('Y-m-d H:i') }}</td>
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
                    <td class="px-3 py-2 text-right">
                        <a href="{{ route('admin.webmaster.contacts.show', $contact) }}"
                            class="text-blue-600 hover:underline text-xs">View</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center text-slate-400 py-8">No submissions found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        <div class="mt-4">{{ $contacts->links() }}</div>
    </div>
</div>
@endsection