@extends('layouts.member-portal')

@section('content')
<section class="mx-auto max-w-5xl space-y-6 px-4 py-6 sm:px-6 lg:px-8">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <p class="text-sm font-semibold uppercase tracking-wide text-teal-600">Help & Feedback</p>
            <h1 class="text-2xl font-black text-slate-900">Report Issue or Idea</h1>
            <p class="mt-2 max-w-2xl text-sm text-slate-600">
                Report portal errors, broken behavior, or submit wish-list and enhancement ideas for the HR portal and facility tools.
            </p>
        </div>
        <a href="{{ route('member.feedback.create') }}"
           class="inline-flex items-center justify-center rounded-xl bg-teal-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-teal-700">
            <i class="fa-solid fa-plus mr-2"></i> New submission
        </a>
    </div>

    @if(session('success'))
    <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('success') }}</div>
    @endif

    @if(session('error'))
    <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">{{ session('error') }}</div>
    @endif

    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-100 px-5 py-4">
            <h2 class="text-sm font-bold text-slate-900">Your submissions</h2>
        </div>

        @if($submissions->isEmpty())
        <div class="px-5 py-12 text-center">
            <p class="text-sm text-slate-500">You have not submitted any feedback yet.</p>
            <a href="{{ route('member.feedback.create') }}" class="mt-4 inline-flex text-sm font-semibold text-teal-700 hover:text-teal-900">
                Submit your first issue or idea →
            </a>
        </div>
        @else
        <div class="divide-y divide-slate-100">
            @foreach($submissions as $submission)
            <div class="px-5 py-4">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="rounded-full px-2.5 py-0.5 text-[11px] font-semibold {{ $submission->isEnhancement() ? 'bg-violet-100 text-violet-800' : 'bg-sky-100 text-sky-800' }}">
                                {{ $submission->categoryLabel() }}
                            </span>
                            <span class="rounded-full px-2.5 py-0.5 text-[11px] font-semibold {{ $submission->status === 'resolved' ? 'bg-emerald-100 text-emerald-800' : ($submission->status === 'in_progress' ? 'bg-amber-100 text-amber-800' : 'bg-slate-100 text-slate-700') }}">
                                {{ ucfirst(str_replace('_', ' ', $submission->status)) }}
                            </span>
                            @if($submission->urgent)
                            <span class="rounded-full bg-rose-100 px-2.5 py-0.5 text-[11px] font-semibold text-rose-800">Urgent</span>
                            @endif
                        </div>
                        <h3 class="mt-2 text-base font-bold text-slate-900">{{ $submission->subject }}</h3>
                        <p class="mt-1 line-clamp-2 text-sm text-slate-600">{{ $submission->message }}</p>
                        <p class="mt-2 text-xs text-slate-500">
                            Submitted {{ $submission->created_at->format('M j, Y g:i A') }}
                            @if($submission->facility)
                                · {{ $submission->facility->name }}
                            @endif
                            @if(($submission->comments_count ?? 0) > 0)
                                · {{ $submission->comments_count }} comment{{ $submission->comments_count === 1 ? '' : 's' }}
                            @endif
                        </p>
                    </div>
                    <a href="{{ route('member.feedback.show', $submission) }}"
                       class="shrink-0 text-sm font-semibold text-teal-700 hover:text-teal-900">View →</a>
                </div>
            </div>
            @endforeach
        </div>
        @if($submissions->hasPages())
        <div class="border-t border-slate-100 px-5 py-4">{{ $submissions->links() }}</div>
        @endif
        @endif
    </div>
</section>
@endsection
