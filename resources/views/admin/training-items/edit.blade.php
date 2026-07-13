@extends('layouts.dashboard', ['title' => 'Edit Training Module'])

@section('content')
<div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
    <div>
        <a href="{{ route('admin.training-items.index') }}" class="inline-flex items-center gap-1 text-sm font-semibold text-brand-600 hover:text-brand-700">
            ← Back to Training Configuration
        </a>
        <h1 class="mt-2 text-2xl font-black text-slate-900">Edit training module</h1>
    </div>
    <form method="POST" action="{{ route('admin.training-items.destroy', $trainingItem) }}" onsubmit="return confirm('Delete this training? Completions will also be removed.');">
        @csrf
        @method('DELETE')
        <button type="submit" class="rounded-xl bg-rose-600 px-4 py-2 text-sm font-semibold text-white hover:bg-rose-700">Delete</button>
    </form>
</div>
<div class="max-w-4xl rounded-2xl border border-slate-200 bg-white p-8">
    <form action="{{ route('admin.training-items.update', $trainingItem) }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')
        @include('admin.training-items._form')
        <div class="flex gap-4 pt-4">
            <button type="submit" class="rounded-xl bg-brand-600 px-6 py-2 font-semibold text-white hover:bg-brand-700">Update</button>
            <a href="{{ route('admin.training-items.index') }}" class="rounded-xl bg-slate-200 px-6 py-2 font-semibold text-slate-900 hover:bg-slate-300">Cancel</a>
        </div>
    </form>
</div>
@endsection
