@once
<div id="importDataLoader"
     class="fixed inset-0 z-[100] hidden items-center justify-center bg-slate-900/45"
     role="status"
     aria-live="polite"
     aria-busy="true"
     aria-label="Import in progress">
    <div class="mx-4 flex w-full max-w-sm flex-col items-center gap-4 rounded-2xl bg-white px-8 py-6 shadow-xl ring-1 ring-slate-200">
        <div class="h-12 w-12 animate-spin rounded-full border-4 border-teal-200 border-t-teal-600" aria-hidden="true"></div>
        <p id="importDataLoaderMessage" class="text-center text-sm font-semibold text-slate-700">Importing data…</p>
        <p class="text-center text-xs text-slate-500">Please wait — large files may take a minute.</p>
        <div id="importDataProgress" class="hidden w-full space-y-2">
            <div class="flex items-center justify-between text-xs font-semibold text-slate-600">
                <span id="importDataProgressCount">Imported 0 of 0 employees</span>
                <span id="importDataProgressPercent">0%</span>
            </div>
            <div class="h-2.5 overflow-hidden rounded-full bg-slate-200">
                <div id="importDataProgressBar" class="h-full rounded-full bg-teal-600 transition-all duration-300" style="width: 0%"></div>
            </div>
            <div class="flex justify-center gap-4 text-[11px] text-slate-500">
                <span id="importDataProgressSkipped">Skipped: 0</span>
                <span id="importDataProgressFailed">Failed: 0</span>
            </div>
        </div>
        <button id="importDataCancelButton" type="button"
                class="hidden rounded-lg border border-rose-300 bg-white px-4 py-2 text-sm font-semibold text-rose-700 hover:bg-rose-50 disabled:cursor-not-allowed disabled:opacity-60">
            Cancel import
        </button>
    </div>
</div>
<script>
(function () {
    if (typeof window.showImportDataLoader === 'function') {
        return;
    }

    let activeImportToken = 0;
    let cancelHandler = null;

    window.showImportDataLoader = function (message, options = {}) {
        const el = document.getElementById('importDataLoader');
        if (!el) {
            return;
        }
        const msg = document.getElementById('importDataLoaderMessage');
        if (msg) {
            msg.textContent = message || 'Importing data…';
        }
        document.getElementById('importDataProgress')?.classList.toggle('hidden', !options.progress);
        const cancelButton = document.getElementById('importDataCancelButton');
        cancelHandler = typeof options.onCancel === 'function' ? options.onCancel : null;
        if (cancelButton) {
            cancelButton.classList.toggle('hidden', !cancelHandler);
            cancelButton.disabled = false;
            cancelButton.textContent = 'Cancel import';
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
        cancelHandler = null;
    };

    window.updateImportDataLoader = function (progress = {}) {
        const total = Number(progress.total || 0);
        const processed = Number(progress.processed || 0);
        const imported = Number(progress.imported || 0);
        const skipped = Number(progress.skipped || 0);
        const failed = Number(progress.failed || 0);
        const percent = total > 0
            ? Math.min(100, Number(progress.percent ?? Math.floor((processed / total) * 100)))
            : 0;

        const message = document.getElementById('importDataLoaderMessage');
        if (message) {
            message.textContent = progress.cancel_requested
                ? 'Cancelling after the current employee…'
                : (progress.status_label || 'Importing employee data…');
        }
        const count = document.getElementById('importDataProgressCount');
        if (count) count.textContent = `Imported ${imported} of ${total || '…'} employees`;
        const percentLabel = document.getElementById('importDataProgressPercent');
        if (percentLabel) percentLabel.textContent = `${percent}%`;
        const bar = document.getElementById('importDataProgressBar');
        if (bar) bar.style.width = `${percent}%`;
        const skippedLabel = document.getElementById('importDataProgressSkipped');
        if (skippedLabel) skippedLabel.textContent = `Skipped: ${skipped}`;
        const failedLabel = document.getElementById('importDataProgressFailed');
        if (failedLabel) failedLabel.textContent = `Failed: ${failed}`;
    };

    window.monitorEmployeeImport = function (initialImport, handlers = {}) {
        const token = ++activeImportToken;
        let current = initialImport;
        const csrf = document.querySelector('meta[name="csrf-token"]')?.content
            || document.querySelector('[name=_token]')?.value
            || '';

        const cancel = async () => {
            if (!current?.cancel_url || !confirm('Stop this import after the current employee? Employees already imported will be kept.')) {
                return;
            }
            const button = document.getElementById('importDataCancelButton');
            if (button) {
                button.disabled = true;
                button.textContent = 'Cancelling…';
            }
            const response = await fetch(current.cancel_url, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrf,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });
            const payload = await response.json();
            if (payload.import) {
                current = payload.import;
                window.updateImportDataLoader(current);
            }
        };

        window.showImportDataLoader('Importing employee data…', {
            progress: true,
            onCancel: cancel,
        });
        window.updateImportDataLoader(current);

        const poll = async () => {
            if (token !== activeImportToken || !current?.status_url) return;
            try {
                const response = await fetch(current.status_url, {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                });
                const payload = await response.json();
                if (!response.ok || !payload.import) {
                    throw new Error(payload.message || `Progress request failed (${response.status}).`);
                }
                current = payload.import;
                window.updateImportDataLoader(current);

                if (current.status === 'awaiting_confirmation') {
                    window.hideImportDataLoader();
                    handlers.onAwaitingConfirmation?.(current);
                    return;
                }
                if (current.terminal) {
                    window.hideImportDataLoader();
                    handlers.onComplete?.(current);
                    return;
                }
                setTimeout(poll, 750);
            } catch (error) {
                window.hideImportDataLoader();
                handlers.onError?.(error);
            }
        };

        setTimeout(poll, 500);
    };

    document.getElementById('importDataCancelButton')?.addEventListener('click', () => cancelHandler?.());
})();
</script>
@endonce
