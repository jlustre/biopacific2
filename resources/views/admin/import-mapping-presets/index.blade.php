@extends('layouts.dashboard', ['title' => 'Import Preset Management'])

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-3xl font-bold text-slate-900">Import Preset Management</h1>
            <p class="mt-2 text-slate-600">Create and maintain Excel column mapping presets used for facility data imports.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('admin.import-logs.index') }}"
               class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                <i class="fas fa-history mr-2"></i> Import history
            </a>
            <form method="POST" action="{{ route('admin.import-mapping-presets.sync-seeder') }}"
                  onsubmit="return confirm('Export all import presets into database/seeders/ImportMappingPresetsTableSeeder.php?\n\nThis overwrites that file. Commit it to git so migrate:fresh --seed restores your presets.');">
                @csrf
                <button type="submit"
                        class="inline-flex items-center justify-center rounded-xl border border-amber-300 bg-amber-50 px-5 py-2.5 text-sm font-semibold text-amber-900 hover:bg-amber-100">
                    <i class="fas fa-database mr-2"></i> Update seeder
                </button>
            </form>
            <a href="{{ route('admin.import-mapping-presets.create') }}"
               class="inline-flex items-center justify-center rounded-xl bg-teal-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-teal-700">
                <i class="fas fa-plus mr-2"></i> New preset
            </a>
        </div>
    </div>

    @if(session('success'))
    <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('success') }}</div>
    @endif
    @if(session('error'))
    <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">{{ session('error') }}</div>
    @endif

    <div class="grid gap-4 sm:grid-cols-3">
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm font-medium text-slate-500">Total presets</p>
            <p class="mt-1 text-3xl font-bold text-slate-900">{{ $stats['total'] }}</p>
        </div>
        <div class="rounded-xl border border-indigo-200 bg-indigo-50 p-5 shadow-sm">
            <p class="text-sm font-medium text-indigo-700">Global presets</p>
            <p class="mt-1 text-3xl font-bold text-indigo-900">{{ $stats['global'] }}</p>
        </div>
        <div class="rounded-xl border border-teal-200 bg-teal-50 p-5 shadow-sm">
            <p class="text-sm font-medium text-teal-700">Facility-specific</p>
            <p class="mt-1 text-3xl font-bold text-teal-900">{{ $stats['facility_specific'] }}</p>
        </div>
    </div>

    <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        <form method="GET" action="{{ route('admin.import-mapping-presets.index') }}" class="grid gap-4 md:grid-cols-4">
            <div class="md:col-span-2">
                <label for="search" class="mb-1 block text-sm font-medium text-slate-700">Search</label>
                <input type="text" name="search" id="search" value="{{ request('search') }}"
                       placeholder="Preset name or owner…"
                       class="w-full rounded-lg border border-slate-300 px-4 py-2 focus:border-teal-500 focus:ring-2 focus:ring-teal-200">
            </div>
            <div>
                <label for="facility_id" class="mb-1 block text-sm font-medium text-slate-700">Facility</label>
                <select name="facility_id" id="facility_id" class="w-full rounded-lg border border-slate-300 px-4 py-2">
                    <option value="">All facilities</option>
                    <option value="global" {{ request('facility_id') === 'global' ? 'selected' : '' }}>Global only</option>
                    @foreach($facilities as $facility)
                        @if((int) $facility->id !== (int) $globalId)
                        <option value="{{ $facility->id }}" {{ request('facility_id') == $facility->id ? 'selected' : '' }}>{{ $facility->name }}</option>
                        @endif
                    @endforeach
                </select>
            </div>
            <div>
                <label for="user_id" class="mb-1 block text-sm font-medium text-slate-700">Owner</label>
                <select name="user_id" id="user_id" class="w-full rounded-lg border border-slate-300 px-4 py-2">
                    <option value="">All owners</option>
                    @foreach($owners as $owner)
                    <option value="{{ $owner->id }}" {{ request('user_id') == $owner->id ? 'selected' : '' }}>{{ $owner->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end gap-2 md:col-span-4">
                <button type="submit" class="rounded-lg bg-slate-700 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800">Filter</button>
                <a href="{{ route('admin.import-mapping-presets.index') }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Reset</a>
            </div>
        </form>
    </div>

    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="border-b border-slate-200 bg-slate-50">
                    <tr>
                        <th class="px-4 py-3 text-left font-semibold text-slate-700">Name</th>
                        <th class="px-4 py-3 text-left font-semibold text-slate-700">Facility</th>
                        <th class="px-4 py-3 text-left font-semibold text-slate-700">Owner</th>
                        <th class="px-4 py-3 text-left font-semibold text-slate-700">Mappings</th>
                        <th class="px-4 py-3 text-left font-semibold text-slate-700">Updated</th>
                        <th class="px-4 py-3 text-right font-semibold text-slate-700">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($presets as $preset)
                    <tr class="hover:bg-slate-50/80">
                        <td class="px-4 py-3 font-semibold text-slate-900">{{ $preset->name }}</td>
                        <td class="px-4 py-3">
                            @if($preset->isGlobal())
                            <span class="inline-flex rounded-full bg-indigo-100 px-2.5 py-0.5 text-xs font-semibold text-indigo-800">Global</span>
                            @else
                            <span class="text-slate-700">{{ $preset->facility?->name ?? 'Facility #' . $preset->facility_id }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-slate-600">
                            <div>{{ $preset->user?->name ?? '—' }}</div>
                            <div class="text-xs text-slate-400">{{ $preset->user?->email }}</div>
                        </td>
                        <td class="px-4 py-3">
                            <span class="rounded-full bg-teal-100 px-2.5 py-0.5 text-xs font-semibold text-teal-800">{{ $preset->mappingsCount() }}</span>
                        </td>
                        <td class="px-4 py-3 text-slate-500">{{ $preset->updated_at?->format('M j, Y g:i A') }}</td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-end gap-1.5">
                                @if($canImport && $preset->mappingsCount() > 0)
                                <button type="button"
                                        title="Import data" aria-label="Import data"
                                        class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-emerald-200 text-emerald-700 hover:bg-emerald-50"
                                        onclick="openPresetImportModal(@js([
                                            'id' => $preset->id,
                                            'name' => $preset->name,
                                            'isGlobal' => $preset->isGlobal(),
                                            'facilityId' => $preset->facility_id,
                                            'mappingsCount' => $preset->mappingsCount(),
                                            'mappings' => $preset->mappings ?? [],
                                            'primaryWorksheet' => $preset->mappings[0]['worksheet'] ?? '',
                                            'runImportUrl' => route('admin.import-mapping-presets.run-import', $preset),
                                            'validateUrl' => route('admin.import-mapping-presets.validate', $preset),
                                        ]))">
                                    <i class="fas fa-file-import"></i>
                                </button>
                                @endif
                                <a href="{{ route('admin.import-mapping-presets.show', $preset) }}"
                                   title="View preset" aria-label="View preset"
                                   class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-50">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('admin.import-mapping-presets.edit', $preset) }}"
                                   title="Edit preset" aria-label="Edit preset"
                                   class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-teal-200 text-teal-700 hover:bg-teal-50">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form method="POST" action="{{ route('admin.import-mapping-presets.duplicate', $preset) }}" class="inline"
                                      onsubmit="return confirm('Create a copy of this preset?');">
                                    @csrf
                                    <button type="submit" title="Duplicate preset" aria-label="Duplicate preset"
                                            class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-indigo-200 text-indigo-700 hover:bg-indigo-50">
                                        <i class="fas fa-copy"></i>
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('admin.import-mapping-presets.destroy', $preset) }}" class="inline"
                                      onsubmit="return confirm('Delete this preset permanently?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" title="Delete preset" aria-label="Delete preset"
                                            class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-red-200 text-red-700 hover:bg-red-50">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-4 py-12 text-center text-slate-500">No import presets found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($presets->hasPages())
        <div class="border-t border-slate-200 px-4 py-3">{{ $presets->links() }}</div>
        @endif
    </div>
</div>

@include('admin.import-mapping-presets.partials.import-modal', [
    'canImport' => $canImport,
    'importFacilities' => $importFacilities,
    'globalId' => $globalId,
    'parseWorkbookUrl' => $parseWorkbookUrl,
])
@endsection
