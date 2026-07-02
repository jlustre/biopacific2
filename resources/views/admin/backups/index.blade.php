@extends('layouts.dashboard', ['title' => $title ?? 'Backup & Restore Management'])

@section('content')
<div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
    <div class="mb-8">
        <p class="text-xs font-bold uppercase tracking-wider text-blue-600">System Administration</p>
        <h1 class="mt-1 text-3xl font-black text-slate-900">Backup & Restore Management</h1>
        <p class="mt-2 max-w-3xl text-sm text-slate-600">
            Create secure backups, download archives, and restore application data with confirmation safeguards and audit logging.
        </p>
    </div>

    @if (session('backup_success'))
    <div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800 shadow-sm">
        {{ session('backup_success') }}
    </div>
    @endif

    @if (session('backup_error'))
    <div class="mb-6 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-medium text-rose-800 shadow-sm">
        {{ session('backup_error') }}
    </div>
    @endif

    <div class="space-y-6">
        <livewire:admin.backups.backup-dashboard />

        <div class="grid gap-6 xl:grid-cols-12">
            <div class="xl:col-span-5">
                <livewire:admin.backups.create-backup-form />
            </div>
            <div class="xl:col-span-7">
                <livewire:admin.backups.restore-backup-form />
            </div>
        </div>

        <livewire:admin.backups.backup-history-table />
    </div>

    <livewire:admin.backups.backup-details-modal />
</div>
@endsection
