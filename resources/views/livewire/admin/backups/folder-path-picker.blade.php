<div>
    <div class="flex flex-col gap-2 sm:flex-row">
        <input id="{{ $inputId }}"
               type="text"
               wire:model="path"
               class="w-full flex-1 rounded-xl border border-slate-300 px-4 py-2.5 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500"
               placeholder="{{ $placeholder }}">
        <button type="button"
                wire:click="openBrowser"
                class="inline-flex shrink-0 items-center justify-center gap-2 rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50">
            <svg class="h-4 w-4 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 7a2 2 0 012-2h4l2 2h8a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V7z"/>
            </svg>
            Browse…
        </button>
    </div>

    @if($showBrowser)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4" x-data x-cloak>
        <div class="absolute inset-0 bg-slate-900/60" wire:click="closeBrowser"></div>
        <div class="relative flex max-h-[85vh] w-full max-w-2xl flex-col overflow-hidden rounded-2xl bg-white shadow-2xl">
            <div class="border-b border-slate-100 px-5 py-4">
                <h3 class="text-lg font-black text-slate-900">Select folder</h3>
                <p class="mt-1 text-sm text-slate-600">Browse drives and folders on this server, then choose where backups should be stored.</p>
            </div>

            <div class="border-b border-slate-100 bg-slate-50 px-5 py-3">
                <div class="flex items-center gap-2">
                    <button type="button"
                            wire:click="browseUp"
                            @disabled(! $browseCurrent)
                            class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-100 disabled:cursor-not-allowed disabled:opacity-50">
                        Up
                    </button>
                    <p class="min-w-0 flex-1 truncate rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-sm font-medium text-slate-800">
                        {{ $browseCurrent ?? 'This PC' }}
                    </p>
                </div>
            </div>

            @if($browserError !== '')
            <div class="mx-5 mt-3 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">{{ $browserError }}</div>
            @endif

            <div class="flex-1 overflow-y-auto p-3">
                @if($browseRoots !== [])
                <div class="space-y-1">
                    @foreach($browseRoots as $root)
                    <button type="button"
                            wire:click="browseTo(@js($root['path']))"
                            class="flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-left transition hover:bg-blue-50">
                        <span class="text-lg" aria-hidden="true">💽</span>
                        <span class="min-w-0 flex-1">
                            <span class="block text-sm font-semibold text-slate-900">{{ $root['name'] }}</span>
                            <span class="block text-xs text-slate-500">{{ $root['writable'] ? 'Writable' : 'Read only' }}</span>
                        </span>
                        <span class="text-xs font-semibold text-slate-400">Open</span>
                    </button>
                    @endforeach
                </div>
                @else
                <div class="space-y-1">
                    @forelse($browseEntries as $entry)
                    <div class="flex items-center gap-2 rounded-xl px-2 py-1 transition hover:bg-slate-50">
                        <button type="button"
                                wire:click="browseTo(@js($entry['path']))"
                                class="flex min-w-0 flex-1 items-center gap-3 rounded-lg px-2 py-2 text-left">
                            <span class="text-lg" aria-hidden="true">📁</span>
                            <span class="min-w-0 flex-1">
                                <span class="block truncate text-sm font-semibold text-slate-900">{{ $entry['name'] }}</span>
                                <span class="block text-xs text-slate-500">{{ $entry['writable'] ? 'Writable' : 'Read only' }}</span>
                            </span>
                        </button>
                        <button type="button"
                                wire:click="selectFolder(@js($entry['path']))"
                                class="shrink-0 rounded-lg border border-blue-200 bg-blue-50 px-3 py-1.5 text-xs font-bold text-blue-700 hover:bg-blue-100">
                            Select
                        </button>
                    </div>
                    @empty
                    <p class="px-3 py-6 text-center text-sm text-slate-500">This folder has no subfolders.</p>
                    @endforelse
                </div>
                @endif
            </div>

            <div class="flex justify-end gap-3 border-t border-slate-100 px-5 py-4">
                <button type="button" wire:click="closeBrowser" class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Cancel</button>
                <button type="button"
                        wire:click="selectCurrentFolder"
                        @disabled(! $browseCurrent)
                        class="rounded-xl bg-blue-600 px-4 py-2 text-sm font-bold text-white hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-50">
                    Select this folder
                </button>
            </div>
        </div>
    </div>
    @endif
</div>
