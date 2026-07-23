@extends('layouts.dashboard')

@section('content')
<div class="mx-auto max-w-3xl px-4 py-10">
    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex items-start gap-4">
            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-teal-50 text-teal-700">
                <i id="report-export-icon" class="fa-solid fa-file-pdf text-xl"></i>
            </div>
            <div class="min-w-0 flex-1">
                <p class="text-xs font-bold uppercase tracking-[0.18em] text-teal-700">Background PDF export</p>
                <h1 class="mt-1 text-2xl font-bold text-slate-900">{{ $reportExport->report->name }}</h1>
                <p id="report-export-message" class="mt-2 text-sm text-slate-600">
                    The report contains {{ number_format($reportExport->row_count) }} rows. It is being generated in the background, so you can safely leave this page.
                </p>

                <div class="mt-6 rounded-xl border border-slate-200 bg-slate-50 p-4">
                    <div class="flex items-center gap-3">
                        <span id="report-export-spinner" class="h-5 w-5 animate-spin rounded-full border-2 border-teal-600 border-r-transparent"></span>
                        <span id="report-export-status" class="font-semibold text-slate-800">Waiting for the report worker…</span>
                    </div>
                </div>

                <div class="mt-6 flex flex-wrap gap-3">
                    <a id="report-export-download"
                       href="{{ route('admin.reports.exports.download', $reportExport) }}"
                       class="hidden rounded-lg bg-teal-700 px-4 py-2 text-sm font-semibold text-white hover:bg-teal-800">
                        <i class="fa-solid fa-download mr-2"></i>Download PDF
                    </a>
                    <a href="{{ route('admin.reports.index') }}"
                       class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                        Back to reports
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const statusUrl = @json(route('admin.reports.exports.status', $reportExport));
    const statusLabel = document.getElementById('report-export-status');
    const message = document.getElementById('report-export-message');
    const spinner = document.getElementById('report-export-spinner');
    const download = document.getElementById('report-export-download');
    let timer = null;

    async function pollExport() {
        try {
            const response = await fetch(statusUrl, {
                headers: { 'Accept': 'application/json' },
                credentials: 'same-origin',
            });
            if (!response.ok) {
                throw new Error('Could not read export status.');
            }

            const exportState = await response.json();
            if (exportState.status === 'completed') {
                clearInterval(timer);
                spinner.classList.add('hidden');
                statusLabel.textContent = 'PDF ready';
                message.textContent = `Generated ${Number(exportState.row_count || 0).toLocaleString()} report rows.`;
                download.href = exportState.download_url;
                download.classList.remove('hidden');
                window.location.assign(exportState.download_url);
                return;
            }

            if (exportState.status === 'failed') {
                clearInterval(timer);
                spinner.classList.add('hidden');
                statusLabel.className = 'font-semibold text-rose-700';
                statusLabel.textContent = 'PDF generation failed';
                message.textContent = exportState.error_message || 'The report worker could not generate this PDF.';
                return;
            }

            statusLabel.textContent = exportState.status === 'processing'
                ? 'Generating PDF…'
                : 'Waiting for the report worker…';
        } catch (error) {
            statusLabel.textContent = error.message || 'Could not read export status.';
        }
    }

    pollExport();
    timer = setInterval(pollExport, 2500);
});
</script>
@endsection
