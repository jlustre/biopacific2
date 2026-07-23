@extends('layouts.dashboard', ['title' => 'Edit Performance Item'])

@section('content')
<div class="mb-6">
    <a href="{{ route('admin.performances.show', $sectionKey) }}" class="inline-flex items-center gap-1 text-sm font-semibold text-brand-600 hover:text-brand-700">
        ← Back to {{ $section }}
    </a>
    <h1 class="mt-2 text-2xl font-black text-slate-900">Edit performance item</h1>
</div>

<div class="max-w-3xl rounded-2xl border border-slate-200 bg-white p-8">
    <form action="{{ route('admin.performances.items.update', [$sectionKey, $item]) }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')
        <div>
            <label for="item" class="mb-2 block text-sm font-semibold text-gray-900">Item text <span class="text-red-500">*</span></label>
            <textarea name="item" id="item" rows="6" required
                class="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm focus:border-transparent focus:ring-2 focus:ring-blue-500">{{ old('item', $item->item) }}</textarea>
            @error('item')<p class="mt-1 text-sm text-red-500">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="order" class="mb-2 block text-sm font-semibold text-gray-900">Display order</label>
            <input type="number" min="0" name="order" id="order" value="{{ old('order', $item->order) }}"
                class="w-full rounded-lg border border-gray-300 px-4 py-2">
        </div>
        <div class="flex gap-4 pt-4">
            <button type="submit" class="rounded-xl bg-brand-600 px-6 py-2 font-semibold text-white hover:bg-brand-700">Update</button>
            <a href="{{ route('admin.performances.show', $sectionKey) }}" class="rounded-xl bg-slate-200 px-6 py-2 font-semibold text-slate-900 hover:bg-slate-300">Cancel</a>
        </div>
    </form>
</div>
@endsection
