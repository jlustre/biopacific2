@extends('layouts.dashboard', ['title' => 'Document Type'])

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <a href="{{ route('admin.upload-types.index') }}" class="inline-flex items-center gap-1 text-sm font-semibold text-brand-600 hover:text-brand-700">
                <i class="fa-solid fa-arrow-left text-xs"></i>
                Back to Documents Management
            </a>
            <h1 class="mt-2 text-2xl font-black text-slate-900">{{ $uploadType->name }}</h1>
            <p class="text-sm text-slate-500">Document type details and usage</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('admin.upload-types.edit', $uploadType) }}" class="inline-flex items-center justify-center gap-2 rounded-xl bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">
                <i class="fa-solid fa-pen"></i>
                Edit
            </a>
        </div>
    </div>

    @if($uploadType->isEmployeeFileChecklistType())
        <div class="rounded-xl border border-blue-200 bg-blue-50 px-4 py-3 text-sm text-blue-900">
            <i class="fa-solid fa-link mr-1"></i>
            This document type is part of the unified catalog and syncs to Checklist {{ $uploadType->checklist_section }}.
            Edit the name here so Documents pages and the checklist stay identical.
        </div>
    @endif

    <div class="grid gap-6 lg:grid-cols-2">
        <div class="rounded-2xl border border-slate-200 bg-white p-6">
            <h2 class="text-lg font-bold text-slate-900">Details</h2>
            <dl class="mt-4 space-y-4 text-sm">
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Name</dt>
                    <dd class="mt-1 font-semibold text-slate-900">{{ $uploadType->name }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Description</dt>
                    <dd class="mt-1 text-slate-700">{{ $uploadType->description ?: '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Employee file section</dt>
                    <dd class="mt-1">
                        @if($uploadType->checklist_section)
                            <span class="rounded-full bg-blue-50 px-2 py-1 text-xs font-semibold text-blue-700">{{ $uploadType->checklist_section }}</span>
                        @else
                            <span class="rounded-full bg-slate-100 px-2 py-1 text-xs font-semibold text-slate-700">General</span>
                        @endif
                    </dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Requires expiry</dt>
                    <dd class="mt-1 text-slate-700">{{ $uploadType->requires_expiry ? 'Yes' : 'No' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">License / certification</dt>
                    <dd class="mt-1 text-slate-700">{{ $uploadType->is_license_or_certification ? 'Yes' : 'No' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Departments</dt>
                    <dd class="mt-1 text-slate-700">
                        @if($departmentNames->isEmpty())
                            All departments
                        @else
                            {{ $departmentNames->join(', ') }}
                        @endif
                    </dd>
                </div>
            </dl>
        </div>

        <div class="space-y-6">
            <div class="rounded-2xl border border-slate-200 bg-white p-6">
                <h2 class="text-lg font-bold text-slate-900">Usage</h2>
                <dl class="mt-4 grid gap-4 sm:grid-cols-2">
                    <div class="rounded-xl border border-slate-100 bg-slate-50 p-4">
                        <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Employee uploads</dt>
                        <dd class="mt-1 text-2xl font-black text-slate-900">{{ $uploadCount }}</dd>
                    </div>
                    <div class="rounded-xl border border-slate-100 bg-slate-50 p-4">
                        <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Required by positions</dt>
                        <dd class="mt-1 text-2xl font-black text-slate-900">{{ $positionCount }}</dd>
                    </div>
                </dl>
            </div>

            @if($uploadType->checklistItem)
                <div class="rounded-2xl border border-slate-200 bg-white p-6">
                    <h2 class="text-lg font-bold text-slate-900">Linked employee file item</h2>
                    <dl class="mt-4 space-y-3 text-sm">
                        <div>
                            <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Employee file item</dt>
                            <dd class="mt-1 font-semibold text-slate-900">{{ $uploadType->checklistItem->name }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Section</dt>
                            <dd class="mt-1 text-slate-700">{{ $uploadType->checklistItem->section ?? '—' }}</dd>
                        </div>
                    </dl>
                    <a href="{{ route('admin.checklist-items.edit', $uploadType->checklistItem) }}" class="mt-4 inline-flex items-center gap-2 text-sm font-bold text-brand-600 hover:text-brand-700">
                        Edit employee file item
                        <i class="fa-solid fa-arrow-right text-xs"></i>
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
