@php
    $teamHistory = $teamDocumentHistory ?? ['can_access' => false];
    $showTeamHistory = !empty($teamHistory['can_access']);
    $teamQuery = $teamHistory['query'] ?? '';
    $teamResults = $teamHistory['search_results'] ?? [];
    $teamSelected = $teamHistory['selected_employee'] ?? null;
    $teamDocuments = $teamHistory['documents'] ?? [];
    $teamScopeLabel = $teamHistory['scope_label'] ?? 'facility staff';
    $facilityDocumentsUrl = route('admin.facility.documents', ['facility' => $facility->slug ?? $facility->id]);
@endphp

@if($showTeamHistory)
<div
    class="p-6 mb-6 bg-white rounded shadow"
    x-data="{
        showHistoryModal: false,
        historyContext: { name: '', type: '', current: null, history: [] },
        openHistoryModal(payload) {
            this.historyContext = {
                name: payload?.name || 'Document',
                type: payload?.type || 'Document',
                current: payload?.current || null,
                history: Array.isArray(payload?.history) ? payload.history : [],
            };
            this.showHistoryModal = true;
        },
        closeHistoryModal() {
            this.showHistoryModal = false;
        }
    }"
>
    <div class="mb-4">
        <h2 class="text-xl font-bold text-slate-900">Employee document history</h2>
        <p class="mt-1 text-sm text-slate-600">
            Search {{ $teamScopeLabel }} first, then open that employee’s documents and prior versions (view/download only).
        </p>
    </div>

    <form method="GET" action="{{ $facilityDocumentsUrl }}" class="mb-4 rounded border border-slate-200 bg-slate-50 p-4">
        <label for="team_q" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">Search employee</label>
        <div class="flex flex-col gap-2 sm:flex-row sm:items-end">
            <input
                id="team_q"
                type="search"
                name="team_q"
                value="{{ $teamQuery }}"
                minlength="2"
                required
                placeholder="Name or employee number (at least 2 characters)"
                class="w-full rounded border border-teal-300 bg-white px-3 py-2 text-sm focus:border-teal-600 focus:outline-none"
            >
            <button type="submit" class="inline-flex items-center justify-center rounded bg-teal-600 px-4 py-2 text-sm font-semibold text-white hover:bg-teal-700">
                Search
            </button>
            @if($teamQuery !== '' || $teamSelected)
                <a href="{{ $facilityDocumentsUrl }}" class="inline-flex items-center justify-center rounded border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100">Clear</a>
            @endif
        </div>
    </form>

    @if($teamSelected)
        <div class="mb-4 rounded border border-teal-200 bg-teal-50 px-4 py-3">
            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="text-xs font-bold uppercase tracking-wide text-teal-700">Selected employee</p>
                    <p class="mt-1 text-sm font-bold text-slate-900">{{ $teamSelected['name'] ?? '—' }}</p>
                    <p class="text-xs text-slate-600">
                        #{{ $teamSelected['employee_num'] ?? '—' }}
                        · {{ $teamSelected['position'] ?? '—' }}
                        · {{ $teamSelected['facility'] ?? '—' }}
                    </p>
                </div>
                <a href="{{ $teamSelected['clear_url'] ?? $facilityDocumentsUrl }}" class="text-sm font-semibold text-teal-700 hover:text-teal-900">Change employee</a>
            </div>
        </div>

        @if(count($teamDocuments) === 0)
            <p class="rounded border border-dashed border-slate-200 bg-slate-50 px-4 py-8 text-center text-sm text-slate-500">
                No active documents on file for this employee.
            </p>
        @else
            <div class="overflow-x-auto rounded border border-slate-200">
                <table class="w-full min-w-[720px] text-left text-sm">
                    <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                        <tr>
                            <th class="px-3 py-2">File</th>
                            <th class="px-3 py-2">Type</th>
                            <th class="px-3 py-2">Uploaded</th>
                            <th class="px-3 py-2">{{ config('documents.labels.expiration_date') }}</th>
                            <th class="px-3 py-2">{{ config('documents.labels.verification_status') }}</th>
                            <th class="px-3 py-2 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($teamDocuments as $upload)
                            @php
                                $expirationDate = $upload['expiration_date'] ?? $upload['expires_at'] ?? null;
                            @endphp
                            <tr>
                                <td class="px-3 py-2 font-semibold text-slate-900">
                                    <div>{{ $upload['name'] ?? '—' }}</div>
                                    @if(!empty($upload['history_count']))
                                        <button
                                            type="button"
                                            class="mt-1 text-xs font-semibold text-teal-700 hover:text-teal-900"
                                            @click="openHistoryModal(@js([
                                                'name' => $upload['name'] ?? 'Document',
                                                'type' => $upload['type'] ?? 'Document',
                                                'current' => [
                                                    'name' => $upload['name'] ?? 'Document',
                                                    'uploaded_at' => $upload['uploaded_at'] ?? null,
                                                    'expires_at' => $expirationDate,
                                                    'verification_status_label' => $upload['verification_status_label'] ?? null,
                                                    'view_url' => $upload['view_url'] ?? null,
                                                    'download_url' => $upload['download_url'] ?? null,
                                                ],
                                                'history' => $upload['history'] ?? [],
                                            ]))"
                                        >
                                            View history ({{ $upload['history_count'] }})
                                        </button>
                                    @else
                                        <p class="mt-1 text-xs text-slate-400">No prior versions</p>
                                    @endif
                                </td>
                                <td class="px-3 py-2 text-slate-600">{{ $upload['type'] ?? '—' }}</td>
                                <td class="px-3 py-2 text-slate-600">{{ $upload['uploaded_at'] ?? '—' }}</td>
                                <td class="px-3 py-2 text-slate-600">{{ $expirationDate ?: '—' }}</td>
                                <td class="px-3 py-2">
                                    <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-bold {{ $upload['verification_badge_class'] ?? 'bg-slate-100 text-slate-600' }}">
                                        {{ $upload['verification_status_label'] ?? '—' }}
                                    </span>
                                </td>
                                <td class="px-3 py-2 text-right">
                                    <div class="inline-flex items-center gap-2">
                                        @if(!empty($upload['view_url']))
                                            <a href="{{ $upload['view_url'] }}" target="_blank" rel="noopener" class="text-teal-700 hover:text-teal-900" title="View">View</a>
                                        @endif
                                        @if(!empty($upload['download_url']))
                                            <a href="{{ $upload['download_url'] }}" class="text-teal-700 hover:text-teal-900" title="Download">Download</a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    @elseif($teamQuery !== '')
        @if(count($teamResults) === 0)
            <p class="rounded border border-dashed border-slate-200 bg-slate-50 px-4 py-8 text-center text-sm text-slate-500">
                No matching {{ $teamScopeLabel }} for “{{ $teamQuery }}”.
            </p>
        @else
            <div class="overflow-hidden rounded border border-slate-200">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                        <tr>
                            <th class="px-3 py-2">Employee</th>
                            <th class="px-3 py-2">Position</th>
                            <th class="px-3 py-2">Facility</th>
                            <th class="px-3 py-2 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($teamResults as $row)
                            <tr>
                                <td class="px-3 py-2">
                                    <p class="font-semibold text-slate-900">{{ $row['name'] ?? '—' }}</p>
                                    <p class="text-xs text-slate-500">#{{ $row['employee_num'] ?? '—' }}</p>
                                </td>
                                <td class="px-3 py-2 text-slate-600">{{ $row['position'] ?? '—' }}</td>
                                <td class="px-3 py-2 text-slate-600">{{ $row['facility'] ?? '—' }}</td>
                                <td class="px-3 py-2 text-right">
                                    <a href="{{ $row['select_url'] }}" class="inline-flex rounded bg-teal-600 px-3 py-1.5 text-xs font-bold text-white hover:bg-teal-700">
                                        View documents
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    @else
        <p class="rounded border border-dashed border-slate-200 bg-slate-50 px-4 py-6 text-center text-sm text-slate-500">
            Enter an employee name or number to review document history.
        </p>
    @endif

    <div x-show="showHistoryModal" x-cloak class="fixed inset-0 z-[100] flex items-center justify-center p-4 overflow-y-auto">
        <div class="absolute inset-0 bg-black/50" @click="closeHistoryModal()"></div>
        <div class="relative z-10 w-full max-w-xl rounded-lg bg-white p-6 shadow-xl">
            <div class="mb-4 flex items-center justify-between gap-3">
                <div>
                    <h3 class="text-lg font-bold text-slate-900">Document history</h3>
                    <p class="mt-1 text-sm text-slate-500" x-text="historyContext.type + ' · ' + historyContext.name"></p>
                </div>
                <button type="button" class="text-xl leading-none text-slate-500 hover:text-slate-700" @click="closeHistoryModal()" aria-label="Close">&times;</button>
            </div>

            <div class="space-y-3">
                <template x-if="historyContext.current">
                    <div class="rounded border border-emerald-100 bg-emerald-50 px-4 py-3">
                        <p class="text-xs font-bold uppercase tracking-wide text-emerald-700">Current version</p>
                        <p class="mt-1 text-sm font-semibold text-slate-900" x-text="historyContext.current.name"></p>
                        <p class="mt-1 text-xs text-slate-600">
                            Uploaded <span x-text="historyContext.current.uploaded_at || '—'"></span>
                            · Expires <span x-text="historyContext.current.expires_at || '—'"></span>
                            · <span x-text="historyContext.current.verification_status_label || '—'"></span>
                        </p>
                        <div class="mt-2 flex gap-3 text-sm">
                            <a x-show="historyContext.current.view_url" :href="historyContext.current.view_url" target="_blank" rel="noopener" class="font-semibold text-teal-700 hover:text-teal-900">View</a>
                            <a x-show="historyContext.current.download_url" :href="historyContext.current.download_url" class="font-semibold text-teal-700 hover:text-teal-900">Download</a>
                        </div>
                    </div>
                </template>

                <template x-if="(historyContext.history || []).length === 0">
                    <p class="rounded border border-dashed border-slate-200 bg-slate-50 px-4 py-6 text-center text-sm text-slate-500">No prior versions preserved yet.</p>
                </template>

                <template x-for="prior in (historyContext.history || [])" :key="prior.id">
                    <div class="rounded border border-slate-200 px-4 py-3">
                        <p class="text-xs font-bold uppercase tracking-wide text-slate-500">Prior version</p>
                        <p class="mt-1 text-sm font-semibold text-slate-900" x-text="prior.name"></p>
                        <p class="mt-1 text-xs text-slate-600">
                            Uploaded <span x-text="prior.uploaded_at || '—'"></span>
                            · Expires <span x-text="prior.expires_at || '—'"></span>
                            · <span x-text="prior.verification_status_label || '—'"></span>
                        </p>
                        <div class="mt-2 flex gap-3 text-sm">
                            <a x-show="prior.view_url" :href="prior.view_url" target="_blank" rel="noopener" class="font-semibold text-teal-700 hover:text-teal-900">View</a>
                            <a x-show="prior.download_url" :href="prior.download_url" class="font-semibold text-teal-700 hover:text-teal-900">Download</a>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>
</div>
@endif
