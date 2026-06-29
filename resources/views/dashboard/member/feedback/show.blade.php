@extends('layouts.member-portal')

@section('content')
<section class="mx-auto max-w-3xl space-y-6 px-4 py-6 sm:px-6 lg:px-8">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <a href="{{ route('member.feedback.index') }}" class="text-sm font-semibold text-teal-700 hover:text-teal-900">&larr; All submissions</a>
            <h1 class="mt-3 text-2xl font-black text-slate-900">{{ $submission->subject }}</h1>
            <div class="mt-3 flex flex-wrap items-center gap-2">
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
        </div>
        @if($canEdit)
        <a href="{{ route('member.feedback.edit', $submission) }}"
           class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
            <i class="fa-solid fa-pen mr-2"></i> Edit submission
        </a>
        @endif
    </div>

    @if(session('success'))
    <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('success') }}</div>
    @endif
    @if(session('error'))
    <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">{{ session('error') }}</div>
    @endif

    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <p class="text-xs text-slate-500">
            Submitted {{ $submission->created_at->format('M j, Y g:i A') }}
            @if($submission->facility)
                · {{ $submission->facility->name }}
            @endif
            · {{ $submission->sourceLabel() }}
        </p>
        <div class="mt-4 whitespace-pre-line text-sm text-slate-800">{{ $submission->message }}</div>

        @if(!empty($submission->screenshots))
        <div class="mt-5">
            <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-slate-500">Screenshots</p>
            <div class="flex flex-wrap gap-3">
                @foreach($submission->screenshots as $screenshot)
                <a href="{{ asset('storage/' . $screenshot) }}" target="_blank" class="block">
                    <img src="{{ asset('storage/' . $screenshot) }}" alt="Screenshot"
                         class="h-24 rounded-lg border border-slate-200 object-cover shadow-sm hover:scale-105 transition">
                </a>
                @endforeach
            </div>
        </div>
        @endif
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-100 px-5 py-4">
            <h2 class="text-sm font-bold text-slate-900">Conversation</h2>
            <p class="mt-1 text-xs text-slate-500">Follow-up notes between you and the webmaster team.</p>
        </div>

        <div class="divide-y divide-slate-100">
            @forelse($submission->comments as $comment)
            <div class="px-5 py-4 {{ $comment->isFromAdmin() ? 'bg-sky-50/60' : '' }}">
                <div class="flex items-center justify-between gap-3">
                    <p class="text-sm font-semibold text-slate-900">
                        {{ $comment->displayName() }}
                        @if($comment->isFromAdmin())
                        <span class="ml-1 rounded bg-sky-100 px-1.5 py-0.5 text-[10px] font-bold uppercase text-sky-800">Team</span>
                        @endif
                    </p>
                    <p class="text-xs text-slate-500">{{ $comment->created_at->format('M j, Y g:i A') }}</p>
                </div>
                <p class="mt-2 whitespace-pre-line text-sm text-slate-700">{{ $comment->body }}</p>
            </div>
            @empty
            <div class="px-5 py-8 text-center text-sm text-slate-500">No comments yet.</div>
            @endforelse
        </div>

        @if($canEdit)
        <div class="border-t border-slate-100 px-5 py-4">
            <form method="POST" action="{{ route('member.feedback.comments.store', $submission) }}" class="space-y-3">
                @csrf
                <label for="body" class="block text-sm font-semibold text-slate-700">Add a comment</label>
                <textarea name="body" id="body" rows="4" required
                          class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm focus:border-teal-500 focus:outline-none"
                          placeholder="Add more detail, steps to reproduce, or updates since you filed this.">{{ old('body') }}</textarea>
                @error('body')
                <p class="text-xs text-rose-600">{{ $message }}</p>
                @enderror
                <button type="submit"
                        class="inline-flex items-center justify-center rounded-xl bg-teal-600 px-4 py-2 text-sm font-semibold text-white hover:bg-teal-700">
                    Post comment
                </button>
            </form>
        </div>
        @else
        <div class="border-t border-slate-100 px-5 py-4 text-sm text-slate-500">
            This submission is resolved and closed to new comments.
        </div>
        @endif
    </div>
</section>
@endsection
