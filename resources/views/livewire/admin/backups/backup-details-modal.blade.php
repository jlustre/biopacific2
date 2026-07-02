<div>
@if($showModal && $backup)
<div class="fixed inset-0 z-50 flex items-center justify-center p-4" x-data x-cloak>
    <div class="absolute inset-0 bg-slate-900/60" wire:click="close"></div>
    <div class="relative max-h-[90vh] w-full max-w-3xl overflow-y-auto rounded-2xl bg-white shadow-2xl">
        <div class="flex items-start justify-between border-b border-slate-100 px-6 py-4">
            <div>
                <p class="text-xs font-bold uppercase tracking-wide text-blue-600">Backup Details</p>
                <h3 class="mt-1 text-xl font-black text-slate-900">{{ $backup->backup_name }}</h3>
            </div>
            <button type="button" wire:click="close" class="rounded-lg p-2 text-slate-500 hover:bg-slate-100 hover:text-slate-700">&times;</button>
        </div>

        <div class="space-y-5 p-6">
            <dl class="grid gap-4 sm:grid-cols-2">
                <div class="rounded-xl bg-slate-50 p-4">
                    <dt class="text-xs font-bold uppercase tracking-wide text-slate-500">Type</dt>
                    <dd class="mt-1 text-sm font-bold text-slate-900">{{ $backup->typeLabel() }}</dd>
                </div>
                <div class="rounded-xl bg-slate-50 p-4">
                    <dt class="text-xs font-bold uppercase tracking-wide text-slate-500">Status</dt>
                    <dd class="mt-1 text-sm font-bold text-slate-900">{{ $backup->statusLabel() }}</dd>
                </div>
                <div class="rounded-xl bg-slate-50 p-4">
                    <dt class="text-xs font-bold uppercase tracking-wide text-slate-500">File size</dt>
                    <dd class="mt-1 text-sm font-bold text-slate-900">{{ $backup->formattedFileSize() }}</dd>
                </div>
                <div class="rounded-xl bg-slate-50 p-4">
                    <dt class="text-xs font-bold uppercase tracking-wide text-slate-500">Created by</dt>
                    <dd class="mt-1 text-sm font-bold text-slate-900">{{ $backup->creator?->name ?? '—' }}</dd>
                </div>
                <div class="rounded-xl bg-slate-50 p-4">
                    <dt class="text-xs font-bold uppercase tracking-wide text-slate-500">Created at</dt>
                    <dd class="mt-1 text-sm font-bold text-slate-900">{{ $backup->created_at?->format('M j, Y g:i A') }}</dd>
                </div>
                <div class="rounded-xl bg-slate-50 p-4">
                    <dt class="text-xs font-bold uppercase tracking-wide text-slate-500">Destination</dt>
                    <dd class="mt-1 text-sm font-bold text-slate-900">{{ $backup->destinationLabel() }}</dd>
                </div>
                <div class="rounded-xl bg-slate-50 p-4">
                    <dt class="text-xs font-bold uppercase tracking-wide text-slate-500">Restored</dt>
                    <dd class="mt-1 text-sm font-bold text-slate-900">
                        @if($backup->restored_at)
                            {{ $backup->restored_at->format('M j, Y g:i A') }} by {{ $backup->restorer?->name ?? '—' }}
                        @else
                            —
                        @endif
                    </dd>
                </div>
            </dl>

            @if($backup->notes)
            <div class="rounded-xl border border-slate-200 p-4">
                <p class="text-xs font-bold uppercase tracking-wide text-slate-500">Notes</p>
                <p class="mt-2 text-sm text-slate-700">{{ $backup->notes }}</p>
            </div>
            @endif

            @if($backup->error_message)
            <div class="rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-800">{{ $backup->error_message }}</div>
            @endif

            <div>
                <p class="text-xs font-bold uppercase tracking-wide text-slate-500">Included tables ({{ count($backup->included_tables ?? []) }})</p>
                <div class="mt-2 flex flex-wrap gap-2">
                    @forelse($backup->included_tables ?? [] as $table)
                    <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-800 ring-1 ring-blue-100">{{ $table }}</span>
                    @empty
                    <span class="text-sm text-slate-500">No database tables in this backup.</span>
                    @endforelse
                </div>
            </div>

            @if(!empty($preview['record_differences']))
            <div>
                <p class="text-xs font-bold uppercase tracking-wide text-slate-500">Record counts vs current database</p>
                <div class="mt-2 overflow-hidden rounded-xl border border-slate-200">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-4 py-2 text-left font-bold text-slate-600">Table</th>
                                <th class="px-4 py-2 text-right font-bold text-slate-600">Backup</th>
                                <th class="px-4 py-2 text-right font-bold text-slate-600">Current</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach(array_slice($preview['record_differences'], 0, 20, true) as $table => $diff)
                            <tr>
                                <td class="px-4 py-2 font-medium text-slate-900">{{ $table }}</td>
                                <td class="px-4 py-2 text-right text-slate-600">{{ $diff['backup'] }}</td>
                                <td class="px-4 py-2 text-right text-slate-600">{{ $diff['current'] }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif
        </div>

        <div class="flex justify-end gap-3 border-t border-slate-100 px-6 py-4">
            @if($backup->canDownload())
            <a href="{{ route('admin.backups.download', $backup) }}" class="rounded-xl border border-blue-200 bg-blue-50 px-4 py-2 text-sm font-semibold text-blue-700 hover:bg-blue-100">Download</a>
            @endif
            <button type="button" wire:click="close" class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Close</button>
        </div>
    </div>
</div>
@endif
</div>
