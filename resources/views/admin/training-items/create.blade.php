@extends('layouts.dashboard', ['title' => 'Create Training Module'])

@section('content')
<div class="mb-6">
    <a href="{{ route('admin.training-items.index') }}" class="inline-flex items-center gap-1 text-sm font-semibold text-brand-600 hover:text-brand-700">
        ← Back to Training Configuration
    </a>
    <h1 class="mt-2 text-2xl font-black text-slate-900">Create training module</h1>
    <p class="mt-1 text-sm text-slate-500">Define the module, link, frequency, and which positions it applies to.</p>
</div>
<div class="max-w-4xl rounded-2xl border border-slate-200 bg-white p-8">
    <form action="{{ route('admin.training-items.store') }}" method="POST" class="space-y-6">
        @csrf
        @include('admin.training-items._form')
        <div class="flex gap-4 pt-4">
            <button type="submit" class="rounded-xl bg-brand-600 px-6 py-2 font-semibold text-white hover:bg-brand-700">Save</button>
            <a href="{{ route('admin.training-items.index') }}" class="rounded-xl bg-slate-200 px-6 py-2 font-semibold text-slate-900 hover:bg-slate-300">Cancel</a>
        </div>
    </form>
</div>
@endsection
