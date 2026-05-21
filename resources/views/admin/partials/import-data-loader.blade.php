@once
<div id="importDataLoader"
     class="fixed inset-0 z-[100] hidden items-center justify-center bg-slate-900/45"
     role="status"
     aria-live="polite"
     aria-busy="true"
     aria-label="Import in progress">
    <div class="mx-4 flex max-w-sm flex-col items-center gap-4 rounded-2xl bg-white px-8 py-6 shadow-xl ring-1 ring-slate-200">
        <div class="h-12 w-12 animate-spin rounded-full border-4 border-teal-200 border-t-teal-600" aria-hidden="true"></div>
        <p id="importDataLoaderMessage" class="text-center text-sm font-semibold text-slate-700">Importing data…</p>
        <p class="text-center text-xs text-slate-500">Please wait — large files may take a minute.</p>
    </div>
</div>
<script>
(function () {
    if (typeof window.showImportDataLoader === 'function') {
        return;
    }

    window.showImportDataLoader = function (message) {
        const el = document.getElementById('importDataLoader');
        if (!el) {
            return;
        }
        const msg = document.getElementById('importDataLoaderMessage');
        if (msg) {
            msg.textContent = message || 'Importing data…';
        }
        el.classList.remove('hidden');
        el.classList.add('flex');
        document.body.classList.add('overflow-hidden');
    };

    window.hideImportDataLoader = function () {
        const el = document.getElementById('importDataLoader');
        if (!el) {
            return;
        }
        el.classList.add('hidden');
        el.classList.remove('flex');
        document.body.classList.remove('overflow-hidden');
    };
})();
</script>
@endonce
