@php
    $groupItems = $group['items'] ?? [];
    $historyDocuments = $group['history_documents'] ?? [];
    $isInteractive = ! empty($group['interactive']) && ! empty($group['employee_can_act']);
    $isReadOnly = ! empty($group['read_only']) || ! $isInteractive;
    $sectionKey = $group['section'] ?? $group['key'] ?? 'other';
    $groupKey = $group['key'] ?? $sectionKey;
    $itemCount = count($groupItems);
    $historyCount = count($historyDocuments);
@endphp

<div id="checklist-{{ $bucketKey }}-{{ $groupKey }}"
     class="rounded-2xl border border-slate-200 bg-white"
     data-section="{{ $sectionKey }}"
     data-group="{{ $groupKey }}"
     data-bucket="{{ $bucketKey }}">
    <button type="button"
        class="flex w-full items-start gap-3 px-4 py-3 text-left hover:bg-slate-50"
        @click="toggle('{{ $groupKey }}')"
        :aria-expanded="isOpen('{{ $groupKey }}') ? 'true' : 'false'">
        <span class="mt-0.5 inline-flex h-7 w-7 shrink-0 items-center justify-center rounded-lg border border-slate-200 bg-slate-50 text-slate-600 transition-transform"
              :class="isOpen('{{ $groupKey }}') ? 'rotate-0' : '-rotate-90'">
            <i class="fa-solid fa-chevron-down text-xs"></i>
        </span>
        <span class="min-w-0 flex-1">
            <span class="flex flex-wrap items-center gap-2">
                <span class="text-base font-bold text-slate-950">{{ $group['label'] ?? 'Checklist' }}</span>
                @if($isReadOnly)
                <span class="rounded-full bg-slate-100 px-2.5 py-0.5 text-[11px] font-bold uppercase tracking-wide text-slate-600">Read only</span>
                @elseif($isInteractive)
                <span class="rounded-full bg-teal-100 px-2.5 py-0.5 text-[11px] font-bold uppercase tracking-wide text-teal-800">You can act</span>
                @endif
                <span class="rounded-full bg-slate-50 px-2 py-0.5 text-[11px] font-semibold text-slate-500">{{ $itemCount }} {{ \Illuminate\Support\Str::plural('item', $itemCount) }}</span>
                @if($historyCount > 0)
                <span class="rounded-full bg-indigo-50 px-2 py-0.5 text-[11px] font-semibold text-indigo-700">{{ $historyCount }} history</span>
                @endif
            </span>
            @if(!empty($group['description']))
                <span class="mt-1 block text-sm text-slate-500">{{ $group['description'] }}</span>
            @endif
        </span>
    </button>

    <div x-show="isOpen('{{ $groupKey }}')" x-cloak class="border-t border-slate-100 p-4" x-data="{ showHistory: false }">
        @if($itemCount === 0 && $historyCount === 0)
            <p class="rounded-2xl border border-dashed border-slate-200 bg-slate-50 px-4 py-6 text-center text-sm text-slate-500">No items in this section yet.</p>
        @else
            @if($itemCount > 0)
            <div class="overflow-x-auto rounded-2xl border border-slate-200">
                <table class="w-full min-w-[720px] text-left text-sm">
                    <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                        <tr>
                            <th class="px-4 py-3">Item</th>
                            @if($isInteractive)
                            <th class="px-4 py-3">Module</th>
                            @else
                            <th class="px-4 py-3">Progress</th>
                            @endif
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3 text-right">{{ $isInteractive ? 'Action' : 'Document' }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($groupItems as $training)
                            @php
                                $rowClass = match ($training['status'] ?? '') {
                                    'overdue', 'pending_signature', 'rejected' => 'bg-amber-50/40',
                                    'not_started' => 'bg-slate-50/60',
                                    'submitted' => 'bg-amber-50/30',
                                    default => '',
                                };
                                $daysLabel = isset($training['days_until'])
                                    ? ($training['days_until'] < 0
                                        ? abs($training['days_until']) . 'd overdue'
                                        : ($training['days_until'] === 0 ? 'Due today' : $training['days_until'] . 'd left'))
                                    : null;
                            @endphp
                            <tr class="{{ $rowClass }} align-top">
                                <td class="px-4 py-3">
                                    <p class="font-semibold text-slate-950">{{ $training['title'] ?? '—' }}</p>
                                    @if(!empty($training['subtitle']))
                                        <p class="mt-0.5 text-xs text-slate-500">{{ $training['subtitle'] }}</p>
                                    @endif
                                    @if(!empty($training['rejection_reason']))
                                        <p class="mt-2 rounded-lg border border-rose-200 bg-rose-50 px-2 py-1 text-xs text-rose-900">
                                            <span class="font-semibold">Returned:</span> {{ $training['rejection_reason'] }}
                                        </p>
                                    @endif
                                </td>
                                @if($isInteractive)
                                <td class="px-4 py-3">
                                    @if(!empty($training['module_url']))
                                        <a href="{{ $training['module_url'] }}" target="_blank" rel="noopener noreferrer"
                                           class="inline-flex rounded-lg bg-sky-600 px-2.5 py-1 text-xs font-bold text-white hover:bg-sky-700">Open</a>
                                    @else
                                        <span class="text-slate-400">—</span>
                                    @endif
                                </td>
                                @else
                                <td class="px-4 py-3 text-slate-600">
                                    @if(!empty($training['history']))
                                        <p class="text-xs text-slate-600">{{ $training['history'] }}</p>
                                    @elseif(!empty($training['due_at_formatted']))
                                        <span>Due {{ $training['due_at_formatted'] }}</span>
                                        @if($daysLabel)
                                            <span class="mt-0.5 block text-xs font-semibold text-slate-500">{{ $daysLabel }}</span>
                                        @endif
                                    @else
                                        <span class="text-slate-400">Selected period</span>
                                    @endif
                                </td>
                                @endif
                                <td class="px-4 py-3">
                                    <span class="rounded-full px-3 py-1 text-xs font-bold {{ $training['badge_class'] ?? 'bg-slate-100 text-slate-700' }}">
                                        {{ $training['status_label'] ?? '—' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    @if($isInteractive && !empty($training['interactive']))
                                        <div class="inline-flex flex-col items-end gap-2">
                                            @if(!empty($training['period_required']))
                                                <span class="text-xs text-amber-700">Select a period above</span>
                                            @endif
                                            @if(!empty($training['can_start']))
                                                <form method="POST" action="{{ $training['start_url'] }}">
                                                    @csrf
                                                    @if(!empty($training['assessment_period_id']))
                                                    <input type="hidden" name="assessment_period_id" value="{{ $training['assessment_period_id'] }}">
                                                    @endif
                                                    <button type="submit" class="rounded-lg bg-sky-700 px-3 py-1.5 text-xs font-bold text-white hover:bg-sky-800">
                                                        Start training
                                                    </button>
                                                </form>
                                            @endif
                                            @if(!empty($training['can_submit']))
                                                <form method="POST" action="{{ $training['submit_url'] }}" class="flex flex-col items-end gap-1">
                                                    @csrf
                                                    @if(!empty($training['assessment_period_id']))
                                                    <input type="hidden" name="assessment_period_id" value="{{ $training['assessment_period_id'] }}">
                                                    @endif
                                                    <input type="text" name="notes" maxlength="1000" placeholder="Optional note"
                                                           class="w-44 rounded-lg border-slate-200 text-xs">
                                                    <button type="submit" class="rounded-lg bg-emerald-700 px-3 py-1.5 text-xs font-bold text-white hover:bg-emerald-800">
                                                        Mark complete &amp; submit
                                                    </button>
                                                </form>
                                            @endif
                                            @if(empty($training['can_start']) && empty($training['can_submit']) && empty($training['period_required']))
                                                @if(($training['status'] ?? '') === 'submitted')
                                                    <span class="text-xs font-semibold text-amber-800">Awaiting approval</span>
                                                @elseif(($training['status'] ?? '') === 'completed')
                                                    <span class="text-xs font-semibold text-emerald-700">Approved complete</span>
                                                @else
                                                    <span class="text-slate-400">—</span>
                                                @endif
                                            @endif
                                        </div>
                                    @elseif(!empty($training['can_view_pdf']) && !empty($training['pdf_url']))
                                        @include('admin.facilities.checklist.partials.assessment-pdf-link', [
                                            'href' => $training['pdf_url'],
                                            'title' => 'View PDF',
                                            'ariaLabel' => 'View PDF for ' . ($training['title'] ?? 'checklist item'),
                                            'class' => 'relative inline-flex h-8 w-8 items-center justify-center rounded border border-slate-300 bg-white text-red-700 hover:bg-red-50',
                                        ])
                                    @else
                                        <span class="inline-flex items-center gap-1 text-xs font-semibold text-slate-400">
                                            <i class="fa-solid fa-file-circle-xmark"></i>
                                            No PDF yet
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif

            @if($historyCount > 0)
            <div class="@if($itemCount > 0) mt-4 @endif rounded-2xl border border-indigo-100 bg-indigo-50/50 p-4">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="text-sm font-bold text-indigo-950">History documents</p>
                        <p class="mt-0.5 text-xs text-indigo-800">{{ $historyCount }} prior {{ \Illuminate\Support\Str::plural('record', $historyCount) }} from earlier assessment periods.</p>
                    </div>
                    <button type="button"
                            @click="showHistory = !showHistory"
                            class="inline-flex items-center justify-center gap-2 rounded-xl border border-indigo-200 bg-white px-4 py-2 text-xs font-bold text-indigo-800 hover:bg-indigo-50">
                        <i class="fa-solid" :class="showHistory ? 'fa-folder-open' : 'fa-folder'"></i>
                        <span x-text="showHistory ? 'Hide history documents' : 'View history documents'"></span>
                    </button>
                </div>

                <div x-show="showHistory" x-cloak class="mt-4 overflow-x-auto rounded-xl border border-indigo-100 bg-white">
                    <table class="w-full min-w-[640px] text-left text-sm">
                        <thead class="bg-indigo-50/80 text-xs uppercase tracking-wide text-indigo-700">
                            <tr>
                                <th class="px-4 py-3">Document</th>
                                <th class="px-4 py-3">Period</th>
                                <th class="px-4 py-3">Status</th>
                                <th class="px-4 py-3 text-right">View</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($historyDocuments as $historyDoc)
                            <tr>
                                <td class="px-4 py-3">
                                    <p class="font-semibold text-slate-950">{{ $historyDoc['title'] ?? '—' }}</p>
                                    @if(!empty($historyDoc['subtitle']))
                                    <p class="mt-0.5 text-xs text-slate-500">{{ $historyDoc['subtitle'] }}</p>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-slate-600">{{ $historyDoc['period_label'] ?? '—' }}</td>
                                <td class="px-4 py-3">
                                    <span class="rounded-full px-3 py-1 text-xs font-bold {{ $historyDoc['badge_class'] ?? 'bg-slate-100 text-slate-700' }}">
                                        {{ $historyDoc['status_label'] ?? '—' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    @if(!empty($historyDoc['pdf_url']))
                                        @php
                                            $historyHref = $historyDoc['pdf_url'];
                                            $historyLooksLikePdf = str_contains(strtolower(parse_url($historyHref, PHP_URL_PATH) ?? ''), '/pdf')
                                                || str_ends_with(strtolower(parse_url($historyHref, PHP_URL_PATH) ?? ''), '.pdf');
                                        @endphp
                                        @if($historyLooksLikePdf)
                                            @include('admin.facilities.checklist.partials.assessment-pdf-link', [
                                                'href' => $historyHref,
                                                'title' => 'View history PDF',
                                                'ariaLabel' => 'View history PDF for ' . ($historyDoc['title'] ?? 'document'),
                                                'class' => 'relative inline-flex h-8 w-8 items-center justify-center rounded border border-slate-300 bg-white text-red-700 hover:bg-red-50',
                                            ])
                                        @else
                                            <a href="{{ $historyHref }}" target="_blank" rel="noopener noreferrer"
                                               class="inline-flex rounded-lg bg-sky-600 px-2.5 py-1 text-xs font-bold text-white hover:bg-sky-700">Open</a>
                                        @endif
                                    @else
                                        <span class="text-xs text-slate-400">No PDF</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif
        @endif
    </div>
</div>
