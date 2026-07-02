<div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
    <div class="border-b border-slate-100 px-5 py-4">
        <h2 class="text-lg font-black text-slate-900">Restore Backup</h2>
        <p class="mt-1 text-sm text-slate-600">Restore from a folder on this server, upload a backup archive, or use the history actions menu.</p>
    </div>

    <div class="space-y-5 p-5">
        <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">
            <p class="font-bold">Destructive action warning</p>
            <p class="mt-1">Restoring will overwrite existing data for the selected restore type. Always review the preview and confirm you have a recent backup.</p>
        </div>

        <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
            <h3 class="text-sm font-bold text-slate-900">Restore from folder</h3>
            <p class="mt-1 text-xs text-slate-600">Use the same folder path you chose when creating a backup (local drive, USB, or network path).</p>

            <div class="mt-3">
                <livewire:admin.backups.folder-path-picker wire:model="folderPath" input-id="restore-folder-path" placeholder="D:\BioPacific-Backups" :key="'restore-folder-picker'" />
            </div>
            @error('folderPath') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror

            <div class="mt-3">
                <button type="button"
                        wire:click="loadFolderBackups"
                        wire:loading.attr="disabled"
                        wire:target="loadFolderBackups"
                        class="inline-flex w-full items-center justify-center rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50 disabled:opacity-60 sm:w-auto">
                    <span wire:loading.remove wire:target="loadFolderBackups">Load backups from folder</span>
                    <span wire:loading wire:target="loadFolderBackups">Loading…</span>
                </button>
            </div>

            @if($folderArchives !== [])
            <div class="mt-3 max-h-48 space-y-2 overflow-y-auto rounded-xl border border-slate-200 bg-white p-2">
                @foreach($folderArchives as $archive)
                <label class="flex cursor-pointer items-center gap-3 rounded-lg px-3 py-2 transition {{ $selectedFolderFile === $archive['filename'] ? 'bg-blue-50 ring-1 ring-blue-200' : 'hover:bg-slate-50' }}">
                    <input type="radio"
                           name="folder-archive"
                           wire:click="selectFolderArchive('{{ $archive['filename'] }}')"
                           @checked($selectedFolderFile === $archive['filename'])
                           class="text-blue-600 focus:ring-blue-500">
                    <span class="min-w-0 flex-1">
                        <span class="block truncate text-sm font-semibold text-slate-900">{{ $archive['filename'] }}</span>
                        <span class="block text-xs text-slate-500">{{ $archive['size_label'] }} · {{ $archive['modified_at'] }}</span>
                    </span>
                </label>
                @endforeach
            </div>
            @endif
        </div>

        <div class="relative">
            <div class="absolute inset-0 flex items-center" aria-hidden="true">
                <div class="w-full border-t border-slate-200"></div>
            </div>
            <div class="relative flex justify-center">
                <span class="bg-white px-3 text-xs font-semibold uppercase tracking-wide text-slate-400">or upload file</span>
            </div>
        </div>

        <div>
            <label class="text-xs font-bold uppercase tracking-wide text-slate-500">Upload backup file (.zip)</label>
            <input type="file" wire:model="uploadedFile" accept=".zip"
                   class="mt-2 block w-full rounded-xl border border-slate-300 bg-white text-sm file:mr-4 file:rounded-lg file:border-0 file:bg-slate-100 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-slate-700">
            <div wire:loading wire:target="uploadedFile" class="mt-2 text-xs text-blue-600">Validating uploaded backup…</div>
            @error('uploadedFile') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
        </div>

        @if($errorMessage !== '')
        <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">{{ $errorMessage }}</div>
        @endif

        @if(!empty($preview))
        <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
            <h3 class="text-sm font-bold text-slate-900">Backup preview</h3>
            <dl class="mt-3 grid gap-2 text-sm sm:grid-cols-2">
                <div><dt class="text-slate-500">Type</dt><dd class="font-semibold text-slate-900">{{ $preview['backup_type'] ?? '—' }}</dd></div>
                <div><dt class="text-slate-500">Created</dt><dd class="font-semibold text-slate-900">{{ $preview['created_at'] ?? '—' }}</dd></div>
                <div><dt class="text-slate-500">Created by</dt><dd class="font-semibold text-slate-900">{{ $preview['created_by']['name'] ?? '—' }}</dd></div>
                <div><dt class="text-slate-500">Tables</dt><dd class="font-semibold text-slate-900">{{ count($preview['included_tables'] ?? []) }}</dd></div>
            </dl>
        </div>
        @endif

        <div>
            <label class="text-xs font-bold uppercase tracking-wide text-slate-500">Restore type</label>
            <select wire:model="restoreType" class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                @foreach($restoreTypes as $value => $label)
                <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <label class="flex items-start gap-3 rounded-xl border border-slate-200 p-3">
            <input type="checkbox" wire:model="createPreBackup" class="mt-1 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
            <span class="text-sm text-slate-700">Create an automatic full backup before restoring (recommended)</span>
        </label>

        @if(! $showModal)
        <label class="flex items-start gap-3 rounded-xl border border-rose-200 bg-rose-50 p-3">
            <input type="checkbox" wire:model="confirmed" class="mt-1 rounded border-rose-300 text-rose-600 focus:ring-rose-500">
            <span class="text-sm font-semibold text-rose-900">I understand this restore may overwrite live data and cannot be easily undone.</span>
        </label>
        @endif

        <button type="button"
                wire:click="restore"
                class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-rose-600 px-4 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-rose-700 disabled:cursor-not-allowed disabled:opacity-60"
                wire:loading.attr="disabled"
                wire:target="restore"
                @disabled($isRestoring || (! $backupId && ! $uploadedFile && $selectedFolderFile === ''))>
            <span wire:loading.remove wire:target="restore">Restore Backup</span>
            <span wire:loading wire:target="restore" class="inline-flex items-center gap-2">
                <svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                Restoring…
            </span>
        </button>
    </div>

    @if($showModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4" x-data x-cloak>
        <div class="absolute inset-0 bg-slate-900/60" wire:click="closeModal"></div>
        <div class="relative max-h-[90vh] w-full max-w-3xl overflow-y-auto rounded-2xl bg-white shadow-2xl">
            @if($isRestoring)
            <div class="absolute inset-0 z-10 flex items-center justify-center rounded-2xl bg-white/90">
                <div class="text-center">
                    <svg class="mx-auto h-8 w-8 animate-spin text-rose-600" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                    <p class="mt-3 text-sm font-bold text-slate-900">Restoring backup…</p>
                    <p class="mt-1 text-xs text-slate-500">This may take a minute. Do not close this window.</p>
                </div>
            </div>
            @endif

            <div class="border-b border-slate-100 px-6 py-4">
                <h3 class="text-xl font-black text-slate-900">Confirm Restore</h3>
                <p class="mt-1 text-sm text-slate-600">Review backup details before proceeding.</p>
            </div>
            <div class="space-y-4 p-6">
                <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">
                    This action is destructive. Selected restore type: <strong>{{ $restoreTypes[$restoreType] ?? $restoreType }}</strong>
                </div>

                @if($errorMessage !== '')
                <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-semibold text-rose-800">{{ $errorMessage }}</div>
                @endif

                @if(!empty($preview['record_differences']))
                <div class="overflow-hidden rounded-xl border border-slate-200">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-4 py-2 text-left font-bold text-slate-600">Table</th>
                                <th class="px-4 py-2 text-right font-bold text-slate-600">Backup</th>
                                <th class="px-4 py-2 text-right font-bold text-slate-600">Current</th>
                                <th class="px-4 py-2 text-right font-bold text-slate-600">Delta</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach(array_slice($preview['record_differences'], 0, 12, true) as $table => $diff)
                            <tr>
                                <td class="px-4 py-2 font-medium text-slate-900">{{ $table }}</td>
                                <td class="px-4 py-2 text-right text-slate-600">{{ $diff['backup'] }}</td>
                                <td class="px-4 py-2 text-right text-slate-600">{{ $diff['current'] }}</td>
                                <td class="px-4 py-2 text-right font-semibold {{ $diff['delta'] < 0 ? 'text-rose-600' : 'text-emerald-600' }}">{{ $diff['delta'] }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif

                <label class="flex items-start gap-3 rounded-xl border border-slate-200 p-3">
                    <input type="checkbox" wire:model="createPreBackup" class="mt-1 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                    <span class="text-sm text-slate-700">Create an automatic full backup before restoring (recommended)</span>
                </label>

                <label class="flex items-start gap-3 rounded-xl border border-rose-200 bg-rose-50 p-3">
                    <input type="checkbox" wire:model.live="confirmed" class="mt-1 rounded border-rose-300 text-rose-600 focus:ring-rose-500">
                    <span class="text-sm font-semibold text-rose-900">I confirm I want to restore this backup.</span>
                </label>
            </div>
            <div class="flex justify-end gap-3 border-t border-slate-100 px-6 py-4">
                <button type="button" wire:click="closeModal" class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50" @disabled($isRestoring)>Cancel</button>
                <button type="button"
                        wire:click="restore"
                        class="inline-flex items-center gap-2 rounded-xl bg-rose-600 px-4 py-2 text-sm font-bold text-white hover:bg-rose-700 disabled:cursor-not-allowed disabled:opacity-60"
                        wire:loading.attr="disabled"
                        wire:target="restore"
                        @disabled($isRestoring)>
                    <span wire:loading.remove wire:target="restore">Restore Now</span>
                    <span wire:loading wire:target="restore" class="inline-flex items-center gap-2">
                        <svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                        Restoring…
                    </span>
                </button>
            </div>
        </div>
    </div>
    @endif
</div>
