@extends('layouts.member-portal')

@section('content')
<section class="mx-auto max-w-3xl px-4 py-4 sm:px-6 lg:py-5">
    <div class="mb-4">
        <a href="{{ route('member.galleries.show', $gallery) }}" class="text-sm font-semibold text-teal-700 hover:underline">← Back to gallery</a>
    </div>

    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-200 px-5 py-4">
            <h1 class="text-xl font-black text-slate-900">Edit gallery</h1>
            <p class="mt-1 text-sm text-slate-600">Only you (the creator) can update this gallery.</p>
        </div>
        <form method="POST" action="{{ route('member.galleries.update', $gallery) }}" class="space-y-6 p-5 sm:p-6">
            @csrf
            @method('PUT')
            @include('dashboard.member.galleries._form', ['gallery' => $gallery, 'events' => $events])
            <div class="flex flex-wrap justify-end gap-3 border-t border-slate-100 pt-4">
                <a href="{{ route('member.galleries.show', $gallery) }}" class="rounded-xl px-4 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-50">Cancel</a>
                <button type="submit" class="rounded-xl bg-teal-600 px-5 py-2.5 text-sm font-bold text-white hover:bg-teal-700">Save changes</button>
            </div>
        </form>
    </div>
</section>
@endsection
