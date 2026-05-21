@extends('layouts.dashboard', ['title' => 'Import Preset — ' . $preset->name])

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
        <div>
            <a href="{{ route('admin.import-mapping-presets.index') }}" class="text-sm font-semibold text-teal-700 hover:text-teal-900">&larr; Back to presets</a>
            <h1 class="mt-3 text-3xl font-bold text-slate-900">{{ $preset->name }}</h1>
            <p class="mt-2 text-slate-600">Preset details and mapping configuration.</p>
        </div>
        <div class="flex items-center gap-1.5">
            @if($canImport && $preset->mappingsCount() > 0)
            <button type="button"
                    title="Import data" aria-label="Import data"
                    class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-emerald-300 bg-white text-emerald-700 hover:bg-emerald-50"
                    onclick="openPresetImportModal(@js([
                        'id' => $preset->id,
                        'name' => $preset->name,
                        'isGlobal' => $preset->isGlobal(),
                        'facilityId' => $preset->facility_id,
                        'mappingsCount' => $preset->mappingsCount(),
                        'primaryWorksheet' => ($preset->mappings[0]['worksheet'] ?? ''),
                        'runImportUrl' => route('admin.import-mapping-presets.run-import', $preset),
                    ]))">
                <i class="fas fa-file-import"></i>
            </button>
            @endif
            <a href="{{ route('admin.import-mapping-presets.edit', $preset) }}"
               title="Edit preset" aria-label="Edit preset"
               class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-teal-600 text-white hover:bg-teal-700">
                <i class="fas fa-edit"></i>
            </a>
            <form method="POST" action="{{ route('admin.import-mapping-presets.duplicate', $preset) }}"
                  onsubmit="return confirm('Create a copy of this preset?');">
                @csrf
                <button type="submit" title="Duplicate preset" aria-label="Duplicate preset"
                        class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-indigo-300 bg-white text-indigo-700 hover:bg-indigo-50">
                    <i class="fas fa-copy"></i>
                </button>
            </form>
            <form method="POST" action="{{ route('admin.import-mapping-presets.destroy', $preset) }}"
                  onsubmit="return confirm('Delete this preset permanently?');">
                @csrf
                @method('DELETE')
                <button type="submit" title="Delete preset" aria-label="Delete preset"
                        class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-red-300 bg-white text-red-700 hover:bg-red-50">
                    <i class="fas fa-trash"></i>
                </button>
            </form>
        </div>
    </div>

    @if(session('success'))
    <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('success') }}</div>
    @endif

    <div class="space-y-6">
        <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="mb-4 text-lg font-semibold text-slate-900">Preset details</h2>
            <dl class="grid gap-4 text-sm sm:grid-cols-2 xl:grid-cols-3">
                <div>
                    <dt class="font-medium text-slate-500">Facility</dt>
                    <dd class="mt-0.5 text-slate-900">
                        @if($preset->isGlobal())
                        <span class="inline-flex rounded-full bg-indigo-100 px-2.5 py-0.5 text-xs font-semibold text-indigo-800">Global (ID {{ $globalId }})</span>
                        @else
                        {{ $preset->facility?->name ?? 'Facility #' . $preset->facility_id }}
                        @endif
                    </dd>
                </div>
                <div>
                    <dt class="font-medium text-slate-500">Owner</dt>
                    <dd class="mt-0.5 text-slate-900">{{ $preset->user?->name ?? '—' }}</dd>
                    <dd class="text-xs text-slate-400">{{ $preset->user?->email }}</dd>
                </div>
                <div>
                    <dt class="font-medium text-slate-500">Mappings</dt>
                    <dd class="mt-0.5"><span class="rounded-full bg-teal-100 px-2.5 py-0.5 text-xs font-semibold text-teal-800">{{ $preset->mappingsCount() }} rows</span></dd>
                </div>
                <div>
                    <dt class="font-medium text-slate-500">Created</dt>
                    <dd class="mt-0.5 text-slate-900">{{ $preset->created_at?->format('M j, Y g:i A') }}</dd>
                </div>
                <div>
                    <dt class="font-medium text-slate-500">Last updated</dt>
                    <dd class="mt-0.5 text-slate-900">{{ $preset->updated_at?->format('M j, Y g:i A') }}</dd>
                </div>
            </dl>
        </div>

        <div class="w-full">
            <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 bg-slate-50 px-4 py-3">
                    <h2 class="font-semibold text-slate-900">Column mappings</h2>
                    <span class="rounded-full bg-teal-100 px-3 py-1 text-xs font-semibold text-teal-800">{{ $preset->mappingsCount() }} mapping(s)</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="border-b border-slate-200 bg-white">
                            <tr>
                                <th class="px-4 py-3 text-left font-semibold text-slate-600">#</th>
                                <th class="px-4 py-3 text-left font-semibold text-slate-600">Worksheet</th>
                                <th class="px-4 py-3 text-left font-semibold text-slate-600">Source column</th>
                                <th class="px-4 py-3 text-left font-semibold text-slate-600">Target table</th>
                                <th class="px-4 py-3 text-left font-semibold text-slate-600">Target column</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($preset->mappings ?? [] as $index => $mapping)
                            <tr class="hover:bg-slate-50/80">
                                <td class="px-4 py-2.5 text-slate-400">{{ $index + 1 }}</td>
                                <td class="px-4 py-2.5">{{ $mapping['worksheet'] ?? '—' }}</td>
                                <td class="px-4 py-2.5">{{ $mapping['worksheet_column'] ?? '—' }}</td>
                                <td class="px-4 py-2.5 font-mono text-xs">{{ $mapping['table'] ?? '—' }}</td>
                                <td class="px-4 py-2.5 font-mono text-xs">{{ $mapping['table_column'] ?? '—' }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="px-4 py-8 text-center text-slate-500">No mappings defined.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@include('admin.import-mapping-presets.partials.import-modal', [
    'canImport' => $canImport,
    'importFacilities' => $importFacilities,
    'globalId' => $globalId,
    'parseWorkbookUrl' => $parseWorkbookUrl,
])
@endsection
