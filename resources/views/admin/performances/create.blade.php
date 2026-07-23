@extends('layouts.dashboard', ['title' => 'Add Performance Section'])

@section('content')
<div class="mb-6">
    <a href="{{ route('admin.performances.index') }}" class="inline-flex items-center gap-1 text-sm font-semibold text-brand-600 hover:text-brand-700">
        ← Back to Performance Management
    </a>
    <h1 class="mt-2 text-2xl font-black text-slate-900">Add performance section</h1>
    <p class="mt-1 text-sm text-slate-500">Create a Part F appraisal section, optionally seed rating items (one per line), and assign positions.</p>
</div>

@if(session('error'))
<div class="mb-4 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">{{ session('error') }}</div>
@endif

@php
    $sample = (object) ['position_ids' => old('position_ids', ['global'])];
@endphp

<div class="max-w-4xl rounded-2xl border border-slate-200 bg-white p-8">
    <form action="{{ route('admin.performances.store') }}" method="POST" class="space-y-6">
        @csrf
        <div>
            <label for="section" class="mb-2 block text-sm font-semibold text-gray-900">Section name <span class="text-red-500">*</span></label>
            <input type="text" name="section" id="section" value="{{ old('section') }}" required
                placeholder="e.g. Job Skills and Knowledge"
                class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-transparent focus:ring-2 focus:ring-blue-500">
            @error('section')<p class="mt-1 text-sm text-red-500">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="order" class="mb-2 block text-sm font-semibold text-gray-900">Starting display order</label>
            <input type="number" min="0" name="order" id="order" value="{{ old('order', 0) }}"
                class="w-full rounded-lg border border-gray-300 px-4 py-2">
        </div>

        <div>
            <label for="items_text" class="mb-2 block text-sm font-semibold text-gray-900">Rating items (optional)</label>
            <textarea name="items_text" id="items_text" rows="8"
                placeholder="One item per line"
                class="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm focus:border-transparent focus:ring-2 focus:ring-blue-500">{{ old('items_text') }}</textarea>
            <p class="mt-1 text-xs text-gray-500">Leave blank to start with a placeholder item you can edit next.</p>
            @error('items_text')<p class="mt-1 text-sm text-red-500">{{ $message }}</p>@enderror
        </div>

        @include('admin.performances._positions')

        <div class="flex gap-4 pt-4">
            <button type="submit" class="rounded-xl bg-brand-600 px-6 py-2 font-semibold text-white hover:bg-brand-700">Create</button>
            <a href="{{ route('admin.performances.index') }}" class="rounded-xl bg-slate-200 px-6 py-2 font-semibold text-slate-900 hover:bg-slate-300">Cancel</a>
        </div>
    </form>
</div>
@endsection
