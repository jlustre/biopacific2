@php
    $stats = $stats ?? [];
    $latest = $stats['latest_backup'] ?? null;
    $lastRestore = $stats['last_restore'] ?? null;
@endphp

<div @if(($stats['processing_backups'] ?? 0) > 0) wire:poll.5s @endif>
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-6">
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-xs font-bold uppercase tracking-wide text-slate-500">Total Backups</p>
            <p class="mt-2 text-3xl font-black text-slate-900">{{ $stats['total_backups'] ?? 0 }}</p>
        </div>
        <div class="rounded-2xl border border-blue-100 bg-gradient-to-br from-blue-50 to-white p-5 shadow-sm">
            <p class="text-xs font-bold uppercase tracking-wide text-blue-700">Latest Backup</p>
            <p class="mt-2 text-sm font-bold text-slate-900">{{ $latest?->backup_name ?? '—' }}</p>
            <p class="mt-1 text-xs text-slate-500">{{ $latest?->created_at?->diffForHumans() ?? 'No backups yet' }}</p>
        </div>
        <div class="rounded-2xl border border-emerald-100 bg-gradient-to-br from-emerald-50 to-white p-5 shadow-sm">
            <p class="text-xs font-bold uppercase tracking-wide text-emerald-700">Last Restore</p>
            <p class="mt-2 text-sm font-bold text-slate-900">{{ $lastRestore?->restored_at?->format('M j, Y g:i A') ?? '—' }}</p>
            <p class="mt-1 text-xs text-slate-500">{{ $lastRestore?->backup_name ?? 'No restores yet' }}</p>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-xs font-bold uppercase tracking-wide text-slate-500">Storage Used</p>
            <p class="mt-2 text-3xl font-black text-slate-900">{{ $stats['storage_used_label'] ?? '0 B' }}</p>
        </div>
        <div class="rounded-2xl border border-rose-100 bg-gradient-to-br from-rose-50 to-white p-5 shadow-sm">
            <p class="text-xs font-bold uppercase tracking-wide text-rose-700">Failed Backups</p>
            <p class="mt-2 text-3xl font-black text-rose-700">{{ $stats['failed_backups'] ?? 0 }}</p>
            @if(($stats['processing_backups'] ?? 0) > 0)
            <p class="mt-1 text-xs font-medium text-amber-700">{{ $stats['processing_backups'] }} processing…</p>
            @endif
        </div>
        <div class="rounded-2xl border border-indigo-100 bg-gradient-to-br from-indigo-50 to-white p-5 shadow-sm">
            <p class="text-xs font-bold uppercase tracking-wide text-indigo-700">Automation</p>
            <p class="mt-2 text-sm font-bold text-slate-900">
                {{ config('backup.schedule.enabled') ? 'Nightly backups enabled' : 'Nightly backups disabled' }}
            </p>
            <p class="mt-1 text-xs text-slate-500">
                @if(config('backup.schedule.enabled'))
                    Runs daily at {{ config('backup.schedule.time') }} ({{ config('backup.schedule.timezone') }})
                @else
                    Set BACKUP_SCHEDULE_ENABLED=true to enable
                @endif
            </p>
            <p class="mt-2 text-xs text-slate-500">
                Off-site mirror: {{ config('backup.remote_mirror_enabled') ? strtoupper((string) config('backup.remote_disk')) : 'Disabled' }}
            </p>
        </div>
    </div>
</div>
