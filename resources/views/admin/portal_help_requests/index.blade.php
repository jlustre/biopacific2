@extends('layouts.dashboard')

@section('content')
<div class="mx-auto max-w-6xl py-8">
    <h1 class="mb-2 text-2xl font-bold">Portal Help Requests</h1>
    <p class="mb-6 text-sm text-slate-600">HR inquiries and support requests submitted from the member portal sidebar.</p>

    <form method="GET" class="mb-6 flex flex-wrap gap-3 items-end rounded-xl border border-slate-200 bg-white p-4">
        <div>
            <label class="mb-1 block text-xs font-semibold text-slate-600">Type</label>
            <select name="type" class="rounded border-slate-300 text-xs py-1 px-2">
                <option value="">All</option>
                <option value="hr_inquiry" @selected(request('type')==='hr_inquiry')>HR inquiry</option>
                <option value="support" @selected(request('type')==='support')>Support request</option>
            </select>
        </div>
        <div>
            <label class="mb-1 block text-xs font-semibold text-slate-600">Status</label>
            <select name="status" class="rounded border-slate-300 text-xs py-1 px-2">
                <option value="">All</option>
                @foreach(['open','in_progress','resolved'] as $status)
                <option value="{{ $status }}" @selected(request('status')===$status)>{{ ucfirst(str_replace('_',' ', $status)) }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="mb-1 block text-xs font-semibold text-slate-600">Facility</label>
            <select name="facility_id" class="rounded border-slate-300 text-xs py-1 px-2">
                <option value="">All</option>
                @foreach($facilities as $facility)
                <option value="{{ $facility->id }}" @selected((string)request('facility_id')===(string)$facility->id)>{{ $facility->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="mb-1 block text-xs font-semibold text-slate-600">Search</label>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Name, email, subject…" class="rounded border-slate-300 text-xs py-1 px-2">
        </div>
        <button type="submit" class="rounded bg-blue-600 px-4 py-1.5 text-xs font-semibold text-white hover:bg-blue-700">Filter</button>
        <a href="{{ route('admin.portal-help-requests.index') }}" class="text-xs text-slate-500 underline">Reset</a>
    </form>

    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-3 py-2 text-left text-xs font-semibold text-slate-600">Date</th>
                    <th class="px-3 py-2 text-left text-xs font-semibold text-slate-600">Ref</th>
                    <th class="px-3 py-2 text-left text-xs font-semibold text-slate-600">Type</th>
                    <th class="px-3 py-2 text-left text-xs font-semibold text-slate-600">Category</th>
                    <th class="px-3 py-2 text-left text-xs font-semibold text-slate-600">From</th>
                    <th class="px-3 py-2 text-left text-xs font-semibold text-slate-600">Subject</th>
                    <th class="px-3 py-2 text-left text-xs font-semibold text-slate-600">Status</th>
                    <th class="px-3 py-2"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($requests as $item)
                <tr class="{{ !$item->is_read ? 'bg-blue-50/50' : '' }}">
                    <td class="px-3 py-2 text-xs text-slate-500">{{ $item->created_at->format('Y-m-d H:i') }}</td>
                    <td class="px-3 py-2 font-mono text-xs">{{ $item->referenceCode() }}</td>
                    <td class="px-3 py-2 text-xs">{{ $item->typeLabel() }}</td>
                    <td class="px-3 py-2 text-xs">{{ $item->categoryLabel() }}</td>
                    <td class="px-3 py-2">{{ $item->name }}</td>
                    <td class="px-3 py-2">{{ Str::limit($item->subject, 40) }}</td>
                    <td class="px-3 py-2 text-xs">{{ ucfirst(str_replace('_', ' ', $item->status)) }}</td>
                    <td class="px-3 py-2 text-right"><a href="{{ route('admin.portal-help-requests.show', $item) }}" class="text-blue-600 hover:underline text-xs">View</a></td>
                </tr>
                @empty
                <tr><td colspan="8" class="px-3 py-8 text-center text-slate-400">No requests found.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="border-t border-slate-100 px-4 py-3">{{ $requests->links() }}</div>
    </div>
</div>
@endsection
