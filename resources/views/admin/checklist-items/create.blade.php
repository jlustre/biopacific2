@extends('layouts.dashboard', ['title' => 'Create Employee File Item'])

@section('content')
<div class="mb-6">
    <a href="{{ route('admin.upload-types.index', ['tab' => 'items']) }}" class="inline-flex items-center gap-1 text-sm font-semibold text-brand-600 hover:text-brand-700">
        <i class="fa-solid fa-arrow-left text-xs"></i>
        Back to Documents Management
    </a>
    <h1 class="mt-2 text-2xl font-black text-slate-900">Create employee file item</h1>
    <p class="text-sm text-slate-500">Add a PART A–D item and optionally restrict it to specific positions.</p>
</div>
<div class="max-w-4xl">
    <div class="rounded-2xl border border-slate-200 bg-white p-8">
        <form action="{{ route('admin.checklist-items.store') }}" method="POST" class="space-y-6">
            @csrf
            @include('admin.checklist-items._form')
            <div class="flex gap-4 pt-6">
                <button type="submit" class="rounded-xl bg-brand-600 px-6 py-2 font-semibold text-white hover:bg-brand-700 transition">
                    <i class="fas fa-save mr-2"></i> Create item
                </button>
                <a href="{{ route('admin.upload-types.index', ['tab' => 'items']) }}" class="rounded-xl bg-slate-200 px-6 py-2 font-semibold text-slate-900 hover:bg-slate-300 transition">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
