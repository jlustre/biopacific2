@extends('layouts.member-portal')

@section('content')
<section class="mx-auto max-w-5xl space-y-6 px-4 py-6 sm:px-6 lg:px-8">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <p class="text-sm font-semibold uppercase tracking-wide text-teal-600">Help & Support</p>
            <h1 class="text-2xl font-black text-slate-900">My help requests</h1>
            <p class="mt-2 text-sm text-slate-600">HR inquiries and support tickets you submitted from the portal.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('member.help.hr') }}" class="inline-flex items-center rounded-xl bg-teal-600 px-4 py-2 text-sm font-semibold text-white hover:bg-teal-700">Email HR</a>
            <a href="{{ route('member.help.support') }}" class="inline-flex items-center rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Support request</a>
        </div>
    </div>

    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        @if($helpRequests->isEmpty())
        <div class="px-6 py-12 text-center">
            <p class="text-sm text-slate-500">No help requests yet.</p>
            <div class="mt-4 flex flex-wrap justify-center gap-3">
                <a href="{{ route('member.help.hr') }}" class="text-sm font-semibold text-teal-700">Email HR →</a>
                <a href="{{ route('member.help.support') }}" class="text-sm font-semibold text-indigo-700">Submit support request →</a>
            </div>
        </div>
        @else
        <div class="divide-y divide-slate-100">
            @foreach($helpRequests as $item)
            <a href="{{ route('member.help.show', $item) }}" class="block px-5 py-4 transition hover:bg-slate-50">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="rounded-full px-2.5 py-0.5 text-[11px] font-semibold {{ $item->isHrInquiry() ? 'bg-teal-100 text-teal-800' : 'bg-indigo-100 text-indigo-800' }}">
                                {{ $item->typeLabel() }}
                            </span>
                            <span class="rounded-full bg-slate-100 px-2.5 py-0.5 text-[11px] font-semibold text-slate-700">{{ $item->categoryLabel() }}</span>
                            <span class="rounded-full px-2.5 py-0.5 text-[11px] font-semibold {{ $item->status === 'resolved' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                {{ ucfirst(str_replace('_', ' ', $item->status)) }}
                            </span>
                            @if($item->priority === 'urgent')
                            <span class="rounded-full bg-rose-100 px-2.5 py-0.5 text-[11px] font-semibold text-rose-800">Urgent</span>
                            @endif
                        </div>
                        <h3 class="mt-2 font-bold text-slate-900">{{ $item->subject }}</h3>
                        <p class="mt-1 text-xs text-slate-500">{{ $item->referenceCode() }} · {{ $item->created_at->format('M j, Y g:i A') }}</p>
                    </div>
                    <span class="text-sm font-semibold text-teal-700">View →</span>
                </div>
            </a>
            @endforeach
        </div>
        @if($helpRequests->hasPages())
        <div class="border-t border-slate-100 px-5 py-4">{{ $helpRequests->links() }}</div>
        @endif
        @endif
    </div>
</section>
@endsection
