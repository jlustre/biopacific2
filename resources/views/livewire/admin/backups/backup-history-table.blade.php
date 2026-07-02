<div @if($hasProcessing) wire:poll.5s @endif class="rounded-2xl border border-slate-200 bg-white shadow-sm">
    <div class="flex flex-col gap-3 border-b border-slate-100 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-lg font-black text-slate-900">Backup History</h2>
            <p class="mt-1 text-sm text-slate-600">Download, inspect, restore, or delete previous backups.</p>
        </div>
        <div class="flex flex-col gap-2 sm:flex-row">
            <input type="search" wire:model.live.debounce.300ms="search" placeholder="Search backups…"
                   class="rounded-xl border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
            <select wire:model.live="typeFilter" class="rounded-xl border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                <option value="">All types</option>
                @foreach($backupTypes as $value => $label)
                <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>
            <select wire:model.live="statusFilter" class="rounded-xl border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                <option value="">All statuses</option>
                @foreach($backupStatuses as $value => $label)
                <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-5 py-3 text-left text-xs font-bold uppercase tracking-wide text-slate-500">Backup</th>
                    <th class="px-5 py-3 text-left text-xs font-bold uppercase tracking-wide text-slate-500">Type</th>
                    <th class="px-5 py-3 text-left text-xs font-bold uppercase tracking-wide text-slate-500">Size</th>
                    <th class="px-5 py-3 text-left text-xs font-bold uppercase tracking-wide text-slate-500">Status</th>
                    <th class="px-5 py-3 text-left text-xs font-bold uppercase tracking-wide text-slate-500">Created</th>
                    <th class="px-5 py-3 text-right text-xs font-bold uppercase tracking-wide text-slate-500">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($backups as $backup)
                @php
                    $statusClasses = match($backup->status) {
                        'completed' => 'bg-emerald-100 text-emerald-800',
                        'failed' => 'bg-rose-100 text-rose-800',
                        'processing', 'pending' => 'bg-amber-100 text-amber-800',
                        'restored' => 'bg-blue-100 text-blue-800',
                        default => 'bg-slate-100 text-slate-700',
                    };
                @endphp
                <tr class="hover:bg-slate-50/80">
                    <td class="px-5 py-4">
                        <p class="font-bold text-slate-900">{{ $backup->backup_name }}</p>
                        <p class="text-xs text-slate-500">by {{ $backup->creator?->name ?? 'System' }}</p>
                    </td>
                    <td class="px-5 py-4 text-slate-700">{{ $backup->typeLabel() }}</td>
                    <td class="px-5 py-4 text-slate-700">{{ $backup->formattedFileSize() }}</td>
                    <td class="px-5 py-4">
                        <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-bold {{ $statusClasses }}">{{ $backup->statusLabel() }}</span>
                    </td>
                    <td class="px-5 py-4 text-slate-700">
                        <div>{{ $backup->created_at?->format('M j, Y') }}</div>
                        <div class="text-xs text-slate-500">{{ $backup->created_at?->format('g:i A') }}</div>
                    </td>
                    <td class="px-5 py-4">
                        <div class="flex justify-end gap-2">
                            <button type="button" wire:click="showDetails({{ $backup->id }})" class="rounded-lg border border-slate-300 px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-white">View</button>
                            @if($backup->canDownload())
                            <a href="{{ route('admin.backups.download', $backup) }}" class="rounded-lg border border-blue-200 bg-blue-50 px-3 py-1.5 text-xs font-semibold text-blue-700 hover:bg-blue-100">Download</a>
                            @endif
                            @if($backup->canRestore())
                            <button type="button" wire:click="confirmRestore({{ $backup->id }})" class="rounded-lg border border-amber-200 bg-amber-50 px-3 py-1.5 text-xs font-semibold text-amber-800 hover:bg-amber-100">Restore</button>
                            @endif
                            <button type="button"
                                    x-data
                                    @click="if (confirm('Delete this backup permanently?')) { $wire.deleteBackup({{ $backup->id }}) }"
                                    class="rounded-lg border border-rose-200 bg-rose-50 px-3 py-1.5 text-xs font-semibold text-rose-700 hover:bg-rose-100">
                                Delete
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-5 py-10 text-center text-sm text-slate-500">No backups found yet. Create your first backup above.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="border-t border-slate-100 px-5 py-4">
        {{ $backups->links() }}
    </div>
</div>
