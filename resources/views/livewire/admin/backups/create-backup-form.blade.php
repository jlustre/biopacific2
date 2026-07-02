<div class="rounded-2xl border border-slate-200 bg-white shadow-sm">

    <div class="border-b border-slate-100 px-5 py-4">

        <h2 class="text-lg font-black text-slate-900">Create Backup</h2>

        <p class="mt-1 text-sm text-slate-600">Choose backup type, folders, and where the archive should be saved.</p>

    </div>



    <form wire:submit="createBackup" class="space-y-5 p-5">

        <div>

            <label class="text-xs font-bold uppercase tracking-wide text-slate-500">Backup type</label>

            <div class="mt-2 grid gap-2">

                @foreach($backupTypes as $value => $label)

                <label class="flex cursor-pointer items-start gap-3 rounded-xl border p-3 transition {{ $backupType === $value ? 'border-blue-400 bg-blue-50 ring-1 ring-blue-200' : 'border-slate-200 hover:border-slate-300' }}">

                    <input type="radio" wire:model.live="backupType" value="{{ $value }}" class="mt-1 text-blue-600 focus:ring-blue-500">

                    <span>

                        <span class="block text-sm font-bold text-slate-900">{{ $label }}</span>

                    </span>

                </label>

                @endforeach

            </div>

            @error('backupType') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror

        </div>



        @if($backupType !== 'files')

        <div>

            <label class="text-xs font-bold uppercase tracking-wide text-slate-500">Included data sections</label>

            <div class="mt-2 max-h-56 space-y-2 overflow-y-auto rounded-xl border border-slate-200 p-3">

                @foreach($sections as $key => $section)

                <label class="flex items-start gap-3 rounded-lg px-2 py-1.5 hover:bg-slate-50">

                    <input type="checkbox" wire:model="selectedSections" value="{{ $key }}" class="mt-1 rounded border-slate-300 text-blue-600 focus:ring-blue-500">

                    <span>

                        <span class="block text-sm font-semibold text-slate-900">{{ $section['label'] }}</span>

                        <span class="block text-xs text-slate-500">{{ $section['description'] }}</span>

                    </span>

                </label>

                @endforeach

            </div>

        </div>

        @endif



        @if(in_array($backupType, ['full', 'files'], true))

        <div>

            <label class="text-xs font-bold uppercase tracking-wide text-slate-500">Folders to include</label>

            <p class="mt-1 text-xs text-slate-500">Select which application storage folders should be included in the backup archive.</p>

            <div class="mt-2 max-h-64 space-y-3 overflow-y-auto rounded-xl border border-slate-200 p-3">

                @php

                    $foldersByGroup = collect($folders)->groupBy('group_label');

                @endphp

                @foreach($foldersByGroup as $groupLabel => $groupFolders)

                <div>

                    <p class="px-2 text-[11px] font-bold uppercase tracking-wide text-slate-400">{{ $groupLabel }}</p>

                    <div class="mt-1 space-y-1">

                        @foreach($groupFolders as $folder)

                        <label class="flex items-start gap-3 rounded-lg px-2 py-1.5 hover:bg-slate-50">

                            <input type="checkbox" wire:model="selectedFolders" value="{{ $folder['key'] }}" class="mt-1 rounded border-slate-300 text-blue-600 focus:ring-blue-500">

                            <span>

                                <span class="block text-sm font-semibold text-slate-900">{{ $folder['label'] }}</span>

                                <span class="block text-xs text-slate-500">{{ $folder['disk'] }} / {{ $folder['path'] }}</span>

                            </span>

                        </label>

                        @endforeach

                    </div>

                </div>

                @endforeach

            </div>

            @error('selectedFolders') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror

        </div>

        @endif



        <div>

            <label class="text-xs font-bold uppercase tracking-wide text-slate-500">Backup destination</label>

            <p class="mt-1 text-xs text-slate-500">Choose where the completed backup archive will be stored.</p>

            <div class="mt-2 grid gap-2">

                @foreach($destinations as $destinationOption)

                <label class="flex cursor-pointer items-start gap-3 rounded-xl border p-3 transition {{ $destination === $destinationOption['key'] ? 'border-blue-400 bg-blue-50 ring-1 ring-blue-200' : 'border-slate-200 hover:border-slate-300' }}">

                    <input type="radio" wire:model="destination" value="{{ $destinationOption['key'] }}" class="mt-1 text-blue-600 focus:ring-blue-500">

                    <span>

                        <span class="block text-sm font-bold text-slate-900">

                            @if($destinationOption['icon'] === 'cloud') ☁️

                            @elseif($destinationOption['icon'] === 'usb') 💾

                            @elseif($destinationOption['icon'] === 'folder') 📁

                            @else 🖥️

                            @endif

                            {{ $destinationOption['label'] }}

                        </span>

                        <span class="block text-xs text-slate-500">{{ $destinationOption['description'] }}</span>

                    </span>

                </label>

                @endforeach

            </div>

            @error('destination') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror

            @if($destination === 'custom')

            <div class="mt-3">

                <label for="custom-path" class="text-xs font-bold uppercase tracking-wide text-slate-500">Folder</label>

                <p class="mt-1 text-xs text-slate-500">Browse to choose a folder on this server, or type a path manually. The folder will be created if it does not exist.</p>

                <div class="mt-2">
                    <livewire:admin.backups.folder-path-picker wire:model="customPath" input-id="custom-path" placeholder="D:\BioPacific-Backups" :key="'create-folder-picker'" />
                </div>

                @error('customPath') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror

            </div>

            @endif

            @if(count($destinations) === 2)

            <p class="mt-2 text-xs text-slate-500">Enable cloud or external storage in <code class="rounded bg-slate-100 px-1">.env</code> to see more destinations.</p>

            @endif

        </div>



        <div>

            <label for="backup-name" class="text-xs font-bold uppercase tracking-wide text-slate-500">Backup name (optional)</label>

            <input id="backup-name" type="text" wire:model="backupName" class="mt-1 w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" placeholder="e.g. Pre-migration snapshot">

        </div>



        <div>

            <label for="backup-notes" class="text-xs font-bold uppercase tracking-wide text-slate-500">Notes</label>

            <textarea id="backup-notes" wire:model="notes" rows="3" class="mt-1 w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" placeholder="Optional notes for this backup"></textarea>

        </div>



        <button type="submit"

                class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-blue-600 px-4 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-60"

                wire:loading.attr="disabled"

                wire:target="createBackup">

            <span wire:loading.remove wire:target="createBackup">Create Backup</span>

            <span wire:loading wire:target="createBackup" class="inline-flex items-center gap-2">

                <svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>

                Queuing backup…

            </span>

        </button>

    </form>

</div>


