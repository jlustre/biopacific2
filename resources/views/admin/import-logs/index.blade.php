@extends('layouts.dashboard', ['title' => 'Import History'])

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-3xl font-bold text-slate-900">Import History</h1>
            <p class="mt-2 text-slate-600">Audit trail of Excel data imports with status, affected tables, and revert support.</p>
        </div>
        <a href="{{ route('admin.import-mapping-presets.index') }}"
           class="text-sm font-semibold text-teal-700 hover:text-teal-900">&larr; Import presets</a>
    </div>

    @if(session('success'))
    <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('success') }}</div>
    @endif
    @if(session('error'))
    <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">{{ session('error') }}</div>
    @endif

    <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        <form method="GET" class="grid gap-4 md:grid-cols-4">
            <div class="md:col-span-2">
                <label for="search" class="mb-1 block text-sm font-medium text-slate-700">Search</label>
                <input type="text" name="search" id="search" value="{{ request('search') }}"
                       placeholder="File name, user, preset…"
                       class="w-full rounded-lg border border-slate-300 px-4 py-2">
            </div>
            <div>
                <label for="facility_id" class="mb-1 block text-sm font-medium text-slate-700">Facility</label>
                <select name="facility_id" id="facility_id" class="w-full rounded-lg border border-slate-300 px-4 py-2">
                    <option value="">All</option>
                    @foreach($facilities as $facility)
                        @if((int) $facility->id !== (int) $globalId)
                        <option value="{{ $facility->id }}" {{ request('facility_id') == $facility->id ? 'selected' : '' }}>{{ $facility->name }}</option>
                        @endif
                    @endforeach
                </select>
            </div>
            <div>
                <label for="status" class="mb-1 block text-sm font-medium text-slate-700">Status</label>
                <select name="status" id="status" class="w-full rounded-lg border border-slate-300 px-4 py-2">
                    <option value="">All</option>
                    @foreach(['completed', 'partial', 'failed', 'reverted', 'running'] as $status)
                    <option value="{{ $status }}" {{ request('status') === $status ? 'selected' : '' }}>{{ ucfirst($status) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end gap-2 md:col-span-4">
                <button type="submit" class="rounded-lg bg-slate-700 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800">Filter</button>
                <a href="{{ route('admin.import-logs.index') }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Reset</a>
            </div>
        </form>
    </div>

    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="border-b border-slate-200 bg-slate-50">
                    <tr>
                        <th class="px-4 py-3 text-left font-semibold text-slate-700">When</th>
                        <th class="px-4 py-3 text-left font-semibold text-slate-700">Status</th>
                        <th class="px-4 py-3 text-left font-semibold text-slate-700">User</th>
                        <th class="px-4 py-3 text-left font-semibold text-slate-700">Facility</th>
                        <th class="px-4 py-3 text-left font-semibold text-slate-700">Preset / file</th>
                        <th class="px-4 py-3 text-left font-semibold text-slate-700">Tables</th>
                        <th class="px-4 py-3 text-right font-semibold text-slate-700">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($logs as $log)
                    <tr class="hover:bg-slate-50/80">
                        <td class="px-4 py-3 text-slate-600">
                            <div>{{ $log->created_at?->format('M j, Y g:i A') }}</div>
                            @if($log->completed_at)
                            <div class="text-xs text-slate-400">Done {{ $log->completed_at->diffForHumans() }}</div>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            @php
                                $statusClass = match($log->status) {
                                    'completed' => 'bg-emerald-100 text-emerald-800',
                                    'partial' => 'bg-amber-100 text-amber-800',
                                    'failed' => 'bg-red-100 text-red-800',
                                    'reverted' => 'bg-slate-200 text-slate-700',
                                    default => 'bg-slate-100 text-slate-700',
                                };
                            @endphp
                            <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $statusClass }}">{{ $log->statusLabel() }}</span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="text-slate-900">{{ $log->user?->name ?? '—' }}</div>
                            <div class="text-xs text-slate-400">{{ $log->user?->email }}</div>
                        </td>
                        <td class="px-4 py-3 text-slate-700">{{ $log->facility?->name ?? 'Facility #' . $log->facility_id }}</td>
                        <td class="px-4 py-3">
                            @if($log->preset)
                            <div class="font-medium text-slate-900">{{ $log->preset->name }}</div>
                            @endif
                            <div class="text-xs text-slate-500">{{ $log->source_filename ?? '—' }}</div>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex flex-wrap gap-1">
                                @forelse($log->tables_affected ?? [] as $table)
                                <span class="rounded bg-slate-100 px-1.5 py-0.5 font-mono text-xs text-slate-600">{{ $table }}</span>
                                @empty
                                <span class="text-slate-400">—</span>
                                @endforelse
                            </div>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="inline-flex items-center justify-end gap-1.5">
                                @if($log->canBeReverted())
                                <form method="POST" action="{{ route('admin.import-logs.revert', $log) }}" class="inline"
                                      onsubmit="return confirm('Revert import #{{ $log->id }}? Updated records will be restored; inserted records will be deleted.');">
                                    @csrf
                                    <button type="submit" title="Revert import" aria-label="Revert import"
                                            class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-amber-300 text-amber-700 hover:bg-amber-50">
                                        <i class="fas fa-undo"></i>
                                    </button>
                                </form>
                                @endif
                                <a href="{{ route('admin.import-logs.show', $log) }}"
                                   title="View details" aria-label="View details"
                                   class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-50">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @if(($canDeleteImportLogs ?? false) && $log->status !== 'running')
                                <form method="POST" action="{{ route('admin.import-logs.destroy', $log) }}" class="inline"
                                      onsubmit="return confirm('Delete import history #{{ $log->id }}? This removes the audit record only — it does not undo changes made to employee data.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" title="Delete history record" aria-label="Delete history record"
                                            class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-red-200 text-red-700 hover:bg-red-50">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-4 py-12 text-center text-slate-500">No import history yet.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($logs->hasPages())
        <div class="border-t border-slate-200 px-4 py-3">{{ $logs->links() }}</div>
        @endif
    </div>
</div>
@endsection
