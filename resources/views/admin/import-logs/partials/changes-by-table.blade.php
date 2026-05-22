@php
    $changesByTable = $changesByTable ?? collect();
    $totalChanges = $totalChanges ?? $changesByTable->flatten()->count();
@endphp

<div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm"
     x-data="{ openTable: null, openChange: null }">
    <div class="border-b border-slate-200 px-6 py-4">
        <h2 class="font-semibold text-slate-900">Before &amp; after data ({{ $totalChanges }} change{{ $totalChanges === 1 ? '' : 's' }})</h2>
        <p class="mt-1 text-xs text-slate-500">
            Expand a table, then each row, to compare values stored at import time.
            <span class="inline-flex items-center gap-1 rounded bg-amber-100 px-1.5 py-0.5 font-medium text-amber-900">Yellow</span> = updated rows;
            white = inserted rows.
        </p>
    </div>

    @if($changesByTable->isEmpty())
    <p class="px-6 py-10 text-center text-sm text-slate-500">No database changes were recorded for this import.</p>
    @else
    <div class="divide-y divide-slate-100">
        @foreach($changesByTable as $tableName => $tableChanges)
        @php
            $tableId = 'table-' . md5($tableName);
            $inserted = $tableChanges->where('action', 'inserted')->count();
            $updated = $tableChanges->where('action', 'updated')->count();
        @endphp
        <div>
            <button type="button"
                    class="flex w-full items-center justify-between gap-3 px-6 py-4 text-left hover:bg-slate-50"
                    @click="openTable = openTable === '{{ $tableId }}' ? null : '{{ $tableId }}'; openChange = null">
                <div class="min-w-0">
                    <span class="font-mono text-sm font-semibold text-teal-800">{{ $tableName }}</span>
                    <span class="mt-0.5 block text-xs text-slate-500">
                        {{ $tableChanges->count() }} row(s)
                        @if($updated > 0)<span class="text-amber-700">· {{ $updated }} updated</span>@endif
                        @if($inserted > 0)<span class="text-emerald-700">· {{ $inserted }} inserted</span>@endif
                    </span>
                </div>
                <i class="fas shrink-0 text-slate-400" :class="openTable === '{{ $tableId }}' ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
            </button>

            <div x-show="openTable === '{{ $tableId }}'" x-cloak class="border-t border-slate-100 bg-slate-50/50 px-4 pb-4">
                <div class="space-y-2 pt-3">
                    @foreach($tableChanges as $change)
                    @php
                        $changeId = 'change-' . $change->id;
                        $before = $change->displayData($change->before_data);
                        $after = $change->displayData($change->after_data);
                        $diffs = $change->fieldDiffs();
                        $recordKey = $change->record_key ?? [];
                    @endphp
                    <div class="overflow-hidden rounded-lg border {{ $change->action === 'updated' ? 'border-amber-300 bg-amber-50' : 'border-slate-200 bg-white' }}">
                        <button type="button"
                                class="flex w-full items-center gap-3 px-4 py-3 text-left text-xs {{ $change->action === 'updated' ? 'bg-amber-50 hover:bg-amber-100' : 'hover:bg-slate-50' }}"
                                @click="openChange = openChange === '{{ $changeId }}' ? null : '{{ $changeId }}'">
                            <span class="font-mono font-semibold text-slate-800">{{ $change->employee_num ?? '—' }}</span>
                            <span class="rounded-full px-2 py-0.5 font-semibold {{ $change->action === 'updated' ? 'bg-amber-100 text-amber-800' : 'bg-emerald-100 text-emerald-800' }}">
                                {{ ucfirst($change->action) }}
                            </span>
                            @if($change->action === 'updated' && count($diffs) > 0)
                            <span class="text-slate-500">{{ count($diffs) }} field(s) changed</span>
                            @endif
                            <i class="fas ml-auto text-slate-400" :class="openChange === '{{ $changeId }}' ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
                        </button>

                        <div x-show="openChange === '{{ $changeId }}'" x-cloak
                             class="border-t px-4 py-3 text-xs {{ $change->action === 'updated' ? 'border-amber-200 bg-amber-50/80' : 'border-slate-100 bg-white' }}">
                            @if(!empty($recordKey))
                            <div class="mb-3 overflow-x-auto rounded-lg border border-slate-200 bg-slate-50">
                                <p class="border-b border-slate-200 px-3 py-2 font-semibold text-slate-600">Record key</p>
                                <table class="min-w-full table-fixed text-xs">
                                    <colgroup>
                                        <col class="w-[44%]">
                                        <col class="w-[56%]">
                                    </colgroup>
                                    <tbody class="divide-y divide-slate-100">
                                        @foreach($recordKey as $key => $value)
                                        <tr>
                                            <td class="px-3 py-2 align-top font-mono text-slate-500 break-all pr-4">{{ $key }}</td>
                                            <td class="px-3 py-2 align-top font-mono text-slate-800 break-all pl-2">{{ is_scalar($value) ? $value : json_encode($value) }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            @endif

                            @if($change->action === 'updated' && count($diffs) > 0)
                            <p class="mb-2 font-semibold text-slate-700">Changed fields</p>
                            <div class="overflow-x-auto rounded-lg border border-amber-200 bg-amber-50">
                                <table class="min-w-full table-fixed">
                                    <colgroup>
                                        <col class="w-[32%]">
                                        <col class="w-[34%]">
                                        <col class="w-[34%]">
                                    </colgroup>
                                    <thead class="bg-amber-100">
                                        <tr>
                                            <th class="px-3 py-2 text-left align-top font-semibold text-slate-600">Field</th>
                                            <th class="px-3 py-2 text-left align-top font-semibold text-red-700">Before</th>
                                            <th class="px-3 py-2 text-left align-top font-semibold text-emerald-700">After</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-amber-100">
                                        @foreach($diffs as $field => $pair)
                                        <tr class="bg-yellow-50">
                                            <td class="px-3 py-2 align-top font-mono text-slate-600 break-all pr-3">{{ $field }}</td>
                                            <td class="px-3 py-2 align-top whitespace-pre-wrap break-all text-red-800/90 pl-2 pr-3">{{ $pair['before'] ?? '—' }}</td>
                                            <td class="px-3 py-2 align-top whitespace-pre-wrap break-all text-emerald-800/90 pl-2">{{ $pair['after'] ?? '—' }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            @endif

                            <div class="mt-3 grid gap-4 lg:grid-cols-2">
                                @if($change->action === 'updated' && count($before) > 0)
                                <div class="min-w-0">
                                    <p class="mb-2 font-semibold text-red-800">Before (full snapshot)</p>
                                    <div class="overflow-x-auto rounded-lg border border-amber-200 bg-yellow-50">
                                        <table class="min-w-full table-fixed text-xs">
                                            <colgroup>
                                                <col class="w-[44%]">
                                                <col class="w-[56%]">
                                            </colgroup>
                                            <thead class="border-b border-amber-200 bg-amber-100/80">
                                                <tr>
                                                    <th class="px-3 py-2 text-left align-top font-semibold text-slate-600">Field</th>
                                                    <th class="px-3 py-2 text-left align-top font-semibold text-slate-600">Value</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-amber-100">
                                                @foreach($before as $key => $value)
                                                <tr class="{{ array_key_exists($key, $diffs) ? 'bg-amber-100/70' : '' }}">
                                                    <td class="px-3 py-2 align-top font-mono text-slate-600 break-all pr-4">{{ $key }}</td>
                                                    <td class="px-3 py-2 align-top font-mono text-slate-800 break-all pl-2">{{ $value === '' || $value === null ? '—' : $value }}</td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                @endif

                                @if(count($after) > 0)
                                <div class="min-w-0 {{ $change->action === 'inserted' ? 'lg:col-span-2' : '' }}">
                                    <p class="mb-2 font-semibold text-emerald-800">
                                        {{ $change->action === 'inserted' ? 'Inserted data' : 'After (full snapshot)' }}
                                    </p>
                                    <div class="overflow-x-auto rounded-lg border {{ $change->action === 'updated' ? 'border-amber-200 bg-yellow-50' : 'border-emerald-100 bg-emerald-50/30' }}">
                                        <table class="min-w-full table-fixed text-xs">
                                            <colgroup>
                                                <col class="w-[44%]">
                                                <col class="w-[56%]">
                                            </colgroup>
                                            <thead class="border-b {{ $change->action === 'updated' ? 'border-amber-200 bg-amber-100/80' : 'border-emerald-200 bg-emerald-100/80' }}">
                                                <tr>
                                                    <th class="px-3 py-2 text-left align-top font-semibold text-slate-600">Field</th>
                                                    <th class="px-3 py-2 text-left align-top font-semibold text-slate-600">Value</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y {{ $change->action === 'updated' ? 'divide-amber-100' : 'divide-emerald-100' }}">
                                                @foreach($after as $key => $value)
                                                <tr class="{{ $change->action === 'updated' && array_key_exists($key, $diffs) ? 'bg-amber-100/70' : '' }}">
                                                    <td class="px-3 py-2 align-top font-mono text-slate-600 break-all pr-4">{{ $key }}</td>
                                                    <td class="px-3 py-2 align-top font-mono text-slate-800 break-all pl-2">{{ $value === '' || $value === null ? '—' : $value }}</td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                @endif
                            </div>

                            @if($change->action === 'updated' && count($diffs) === 0 && (count($before) > 0 || count($after) > 0))
                            <p class="mt-2 text-slate-500">No field differences detected between snapshots (values may match after normalization).</p>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @endif
</div>
