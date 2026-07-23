@extends('layouts.dashboard', ['title' => 'Manage Performance Section'])

@section('content')
<div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
    <div>
        <a href="{{ route('admin.performances.index') }}" class="inline-flex items-center gap-1 text-sm font-semibold text-brand-600 hover:text-brand-700">
            ← Back to Performance Management
        </a>
        <h1 class="mt-2 text-2xl font-black text-slate-900">{{ $section }}</h1>
        <p class="mt-1 text-sm text-slate-500">Rename this section, assign positions, and manage its rating items.</p>
    </div>
    <form method="POST" action="{{ route('admin.performances.destroy', $sectionKey) }}"
          onsubmit="return confirm('Delete this entire performance section and all {{ $items->count() }} item(s)?');">
        @csrf
        @method('DELETE')
        <button type="submit" class="rounded-xl bg-rose-600 px-4 py-2 text-sm font-semibold text-white hover:bg-rose-700">Delete section</button>
    </form>
</div>

@if(session('success'))
<div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('success') }}</div>
@endif
@if(session('error'))
<div class="mb-4 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">{{ session('error') }}</div>
@endif

<div class="mb-6 max-w-4xl rounded-2xl border border-slate-200 bg-white p-8">
    <h2 class="mb-4 text-lg font-bold text-slate-900">Section settings</h2>
    <form action="{{ route('admin.performances.update', $sectionKey) }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')
        <div>
            <label for="section" class="mb-2 block text-sm font-semibold text-gray-900">Section name <span class="text-red-500">*</span></label>
            <input type="text" name="section" id="section" value="{{ old('section', $section) }}" required
                class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-transparent focus:ring-2 focus:ring-blue-500">
            @error('section')<p class="mt-1 text-sm text-red-500">{{ $message }}</p>@enderror
        </div>

        @include('admin.performances._positions')

        <button type="submit" class="rounded-xl bg-brand-600 px-6 py-2 font-semibold text-white hover:bg-brand-700">Save settings</button>
    </form>
</div>

<div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
    <div class="flex flex-col gap-2 border-b border-slate-100 px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
        <h2 class="text-lg font-bold text-slate-900">Rating items ({{ $items->count() }})</h2>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-3 py-2 text-left font-semibold text-slate-600">Order</th>
                    <th class="px-3 py-2 text-left font-semibold text-slate-600">Item</th>
                    <th class="px-3 py-2 text-right font-semibold text-slate-600">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach($items as $item)
                <tr>
                    <td class="px-3 py-2 align-top">{{ $item->order }}</td>
                    <td class="px-3 py-2">
                        <div class="whitespace-pre-wrap text-sm text-slate-800">{{ $item->item }}</div>
                    </td>
                    <td class="px-3 py-2 text-right whitespace-nowrap align-top">
                        <a href="{{ route('admin.performances.items.edit', [$sectionKey, $item]) }}" class="font-semibold text-teal-700 hover:text-teal-900">Edit</a>
                        <form method="POST" action="{{ route('admin.performances.items.destroy', [$sectionKey, $item]) }}" class="ml-3 inline"
                              onsubmit="return confirm('Delete this item?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="font-semibold text-rose-700 hover:text-rose-900">Delete</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="border-t border-slate-100 p-4">
        <h3 class="mb-3 text-sm font-bold text-slate-900">Add item</h3>
        <form method="POST" action="{{ route('admin.performances.items.store', $sectionKey) }}" class="grid grid-cols-1 gap-3 md:grid-cols-12">
            @csrf
            <div class="md:col-span-8">
                <label class="block text-xs font-semibold text-slate-600">Item text</label>
                <textarea name="item" rows="2" required class="mt-1 w-full rounded-lg border-slate-300 text-sm" placeholder="Rating criterion">{{ old('item') }}</textarea>
                @error('item')<p class="mt-1 text-sm text-red-500">{{ $message }}</p>@enderror
            </div>
            <div class="md:col-span-2">
                <label class="block text-xs font-semibold text-slate-600">Order</label>
                <input type="number" min="0" name="order" value="{{ old('order', (int) $items->max('order') + 1) }}" class="mt-1 w-full rounded-lg border-slate-300 text-sm">
            </div>
            <div class="md:col-span-2 flex items-end">
                <button type="submit" class="w-full rounded-lg bg-teal-600 px-4 py-2 text-sm font-semibold text-white hover:bg-teal-700">Add</button>
            </div>
        </form>
    </div>
</div>
@endsection
