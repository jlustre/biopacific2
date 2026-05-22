@extends('layouts.dashboard', ['title' => 'Import Log #' . $importLog->id])

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
        <div>
            <a href="{{ route('admin.import-logs.index') }}" class="text-sm font-semibold text-teal-700 hover:text-teal-900">&larr; Import history</a>
            <h1 class="mt-3 text-3xl font-bold text-slate-900">Import #{{ $importLog->id }}</h1>
            <p class="mt-2 text-slate-600">{{ $importLog->created_at?->format('l, F j, Y g:i A') }}</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            @if($importLog->canBeReverted())
            <form method="POST" action="{{ route('admin.import-logs.revert', $importLog) }}"
                  onsubmit="return confirm('Revert this import? Records that were updated will be restored to their previous values. Records that were inserted will be deleted.');">
                @csrf
                <button type="submit"
                        class="inline-flex items-center gap-2 rounded-xl border border-amber-400 bg-amber-50 px-5 py-2.5 text-sm font-semibold text-amber-900 hover:bg-amber-100">
                    <i class="fas fa-undo"></i>
                    Revert this import
                </button>
            </form>
            @elseif($importLog->isReverted())
            <span class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-slate-100 px-4 py-2 text-sm font-medium text-slate-600">
                <i class="fas fa-check"></i> Already reverted
            </span>
            @else
            <span class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-slate-50 px-4 py-2 text-sm text-slate-500"
                  title="{{ $importLog->changes()->count() === 0 ? 'No database changes were recorded for this import.' : 'Revert is only available for completed or partial imports with recorded changes.' }}">
                <i class="fas fa-info-circle"></i>
                Revert not available
            </span>
            @endif
            @if(($canDeleteImportLogs ?? false) && $importLog->status !== 'running')
            <form method="POST" action="{{ route('admin.import-logs.destroy', $importLog) }}"
                  onsubmit="return confirm('Delete this import history record? This removes the audit trail only — it does not undo changes made to employee data.');">
                @csrf
                @method('DELETE')
                <button type="submit"
                        class="inline-flex items-center gap-2 rounded-xl border border-red-300 bg-red-50 px-5 py-2.5 text-sm font-semibold text-red-800 hover:bg-red-100">
                    <i class="fas fa-trash"></i>
                    Delete history
                </button>
            </form>
            @endif
        </div>
    </div>

    @if(session('success'))
    <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('success') }}</div>
    @endif
    @if(session('error'))
    <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">{{ session('error') }}</div>
    @endif

    <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        <h2 class="mb-4 text-lg font-semibold text-slate-900">Summary</h2>
        <dl class="grid gap-4 text-sm sm:grid-cols-2 xl:grid-cols-3">
            <div>
                <dt class="font-medium text-slate-500">Status</dt>
                <dd class="mt-0.5">
                    @php
                        $statusClass = match($importLog->status) {
                            'completed' => 'bg-emerald-100 text-emerald-800',
                            'partial' => 'bg-amber-100 text-amber-800',
                            'failed' => 'bg-red-100 text-red-800',
                            'reverted' => 'bg-slate-200 text-slate-700',
                            default => 'bg-slate-100 text-slate-700',
                        };
                    @endphp
                    <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $statusClass }}">{{ $importLog->statusLabel() }}</span>
                </dd>
            </div>
            <div>
                <dt class="font-medium text-slate-500">Imported by</dt>
                <dd class="mt-0.5 text-slate-900">{{ $importLog->user?->name ?? '—' }}</dd>
                <dd class="text-xs text-slate-400">{{ $importLog->user?->email }}</dd>
            </div>
            <div>
                <dt class="font-medium text-slate-500">Facility</dt>
                <dd class="mt-0.5 text-slate-900">{{ $importLog->facility?->name ?? 'Facility #' . $importLog->facility_id }}</dd>
            </div>
            <div>
                <dt class="font-medium text-slate-500">Preset</dt>
                <dd class="mt-0.5 text-slate-900">
                    @if($importLog->preset)
                    <a href="{{ route('admin.import-mapping-presets.show', $importLog->preset) }}" class="font-semibold text-teal-700 hover:text-teal-900">{{ $importLog->preset->name }}</a>
                    @else
                    —
                    @endif
                </dd>
            </div>
            <div>
                <dt class="font-medium text-slate-500">Source file</dt>
                <dd class="mt-0.5 text-slate-900">{{ $importLog->source_filename ?? '—' }}</dd>
            </div>
            <div>
                <dt class="font-medium text-slate-500">Duration</dt>
                <dd class="mt-0.5 text-slate-900">
                    @if($importLog->started_at && $importLog->completed_at)
                    {{ $importLog->started_at->diffInSeconds($importLog->completed_at) }}s
                    @else
                    —
                    @endif
                </dd>
            </div>
            @if($importLog->isReverted())
            <div class="sm:col-span-2 xl:col-span-3">
                <dt class="font-medium text-slate-500">Reverted</dt>
                <dd class="mt-0.5 text-slate-900">
                    {{ $importLog->reverted_at?->format('M j, Y g:i A') }}
                    @if($importLog->revertedByUser)
                    by {{ $importLog->revertedByUser->name }}
                    @endif
                </dd>
            </div>
            @endif
        </dl>

        @if($importLog->error_message)
        <div class="mt-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
            <strong>Error:</strong> {{ $importLog->error_message }}
        </div>
        @endif

        @if($importLog->summary)
        <div class="mt-4 grid gap-3 sm:grid-cols-4">
            <div class="rounded-lg bg-slate-50 px-3 py-2">
                <p class="text-xs text-slate-500">Inserted</p>
                <p class="text-lg font-bold text-emerald-700">{{ $importLog->summary['rows_inserted'] ?? 0 }}</p>
            </div>
            <div class="rounded-lg bg-slate-50 px-3 py-2">
                <p class="text-xs text-slate-500">Updated</p>
                <p class="text-lg font-bold text-teal-700">{{ $importLog->summary['rows_updated'] ?? 0 }}</p>
            </div>
            <div class="rounded-lg bg-slate-50 px-3 py-2">
                <p class="text-xs text-slate-500">Skipped / errors</p>
                <p class="text-lg font-bold text-amber-700">{{ ($importLog->summary['rows_skipped'] ?? 0) + ($importLog->summary['rows_error'] ?? 0) }}</p>
            </div>
            <div class="rounded-lg bg-slate-50 px-3 py-2">
                <p class="text-xs text-slate-500">DB changes logged</p>
                <p class="text-lg font-bold text-slate-800">{{ $importLog->summary['changes_recorded'] ?? $importLog->changes->count() }}</p>
            </div>
        </div>
        @endif
    </div>

    <div class="rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-200 px-6 py-4">
            <h2 class="font-semibold text-slate-900">Tables affected</h2>
        </div>
        <div class="px-6 py-4">
            <div class="flex flex-wrap gap-2">
                @forelse($importLog->tables_affected ?? [] as $table)
                <span class="rounded-lg bg-teal-50 px-3 py-1 font-mono text-sm text-teal-800">{{ $table }}</span>
                @empty
                <span class="text-slate-500">No database changes were recorded.</span>
                @endforelse
            </div>
            @if($changeStats->isNotEmpty())
            <table class="mt-4 min-w-full text-sm">
                <thead>
                    <tr class="text-left text-slate-500">
                        <th class="pb-2 pr-4">Table</th>
                        <th class="pb-2 pr-4">Inserted</th>
                        <th class="pb-2">Updated</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $byTable = $changeStats->groupBy('table_name');
                    @endphp
                    @foreach($byTable as $table => $stats)
                    <tr>
                        <td class="py-1 font-mono text-xs">{{ $table }}</td>
                        <td class="py-1">{{ $stats->firstWhere('action', 'inserted')?->total ?? 0 }}</td>
                        <td class="py-1">{{ $stats->firstWhere('action', 'updated')?->total ?? 0 }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @endif
        </div>
    </div>

    @include('admin.import-logs.partials.changes-by-table', [
        'changesByTable' => $changesByTable ?? $importLog->changes->groupBy('table_name'),
        'totalChanges' => $importLog->changes->count(),
    ])
</div>
@endsection
