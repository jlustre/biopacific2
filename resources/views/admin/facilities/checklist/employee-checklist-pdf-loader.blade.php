<script>
(function () {
    function resetAllPdfLinks(root) {
        root.querySelectorAll('a[data-assessment-pdf-link]').forEach(function (link) {
            delete link.dataset.pdfLoading;
            link.removeAttribute('aria-busy');
            link.classList.remove('pointer-events-none', 'opacity-70');
        });
    }

    function isPdfResponse(response) {
        var contentType = (response.headers.get('Content-Type') || '').toLowerCase();

        return response.ok && (
            contentType.indexOf('application/pdf') !== -1
            || contentType.indexOf('application/octet-stream') !== -1
        );
    }

    function writePdfViewerPage(pdfWindow, options) {
        var title = options.title || 'Loading PDF';
        var message = options.message || 'Generating PDF, please wait…';
        var isError = !!options.isError;

        pdfWindow.document.open();
        pdfWindow.document.write(
            '<!DOCTYPE html>'
            + '<html lang="en">'
            + '<head>'
            + '<meta charset="UTF-8">'
            + '<meta name="viewport" content="width=device-width, initial-scale=1">'
            + '<title>' + title + '</title>'
            + '<style>'
            + 'html, body { height: 100%; margin: 0; }'
            + 'body {'
            + '  display: flex; align-items: center; justify-content: center;'
            + '  font-family: system-ui, -apple-system, Segoe UI, Roboto, sans-serif;'
            + '  background: #f8fafc; color: #0f172a;'
            + '}'
            + '.panel { text-align: center; padding: 2rem; }'
            + '.spinner {'
            + '  width: 3rem; height: 3rem; margin: 0 auto 1rem;'
            + '  border: 4px solid #cbd5e1; border-top-color: #2563eb;'
            + '  border-radius: 50%; animation: spin 0.8s linear infinite;'
            + '}'
            + '@keyframes spin { to { transform: rotate(360deg); } }'
            + 'h1 { margin: 0 0 0.5rem; font-size: 1.125rem; font-weight: 600; }'
            + 'p { margin: 0; font-size: 0.95rem; color: #475569; }'
            + '.error { color: #b91c1c; }'
            + '</style>'
            + '</head>'
            + '<body>'
            + '<div class="panel">'
            + (isError ? '' : '<div class="spinner" role="status" aria-label="Loading"></div>')
            + '<h1>' + title + '</h1>'
            + '<p class="' + (isError ? 'error' : '') + '">' + message + '</p>'
            + '</div>'
            + '</body>'
            + '</html>'
        );
        pdfWindow.document.close();
    }

    function showPdfInViewer(pdfWindow, blob) {
        var blobUrl = URL.createObjectURL(blob);
        pdfWindow.location.replace(blobUrl);
        window.setTimeout(function () {
            URL.revokeObjectURL(blobUrl);
        }, 120000);
    }

    function openAssessmentPdf(link) {
        if (link.dataset.pdfLoading === '1') {
            return;
        }

        var url = link.getAttribute('href');
        if (!url) {
            return;
        }

        link.dataset.pdfLoading = '1';

        // Do not use noopener — the opener must load the PDF into this tab after fetch completes.
        var pdfWindow = window.open('', '_blank');
        if (!pdfWindow) {
            delete link.dataset.pdfLoading;
            window.location.href = url;

            return;
        }

        writePdfViewerPage(pdfWindow, {
            title: 'Loading PDF',
            message: 'Generating PDF, please wait…',
        });

        fetch(url, {
            method: 'GET',
            credentials: 'same-origin',
            headers: {
                Accept: 'application/pdf',
            },
        })
            .then(function (response) {
                if (!isPdfResponse(response)) {
                    throw new Error('PDF request failed with status ' + response.status);
                }

                return response.blob();
            })
            .then(function (blob) {
                showPdfInViewer(pdfWindow, blob);
            })
            .catch(function () {
                writePdfViewerPage(pdfWindow, {
                    title: 'Unable to load PDF',
                    message: 'Opening the PDF directly…',
                    isError: true,
                });

                window.setTimeout(function () {
                    pdfWindow.location.href = url;
                }, 400);
            })
            .finally(function () {
                delete link.dataset.pdfLoading;
            });
    }

    function handlePdfClick(event) {
        var link = event.target.closest('a[data-assessment-pdf-link]');
        if (!link) {
            return;
        }

        event.preventDefault();
        openAssessmentPdf(link);
    }

    function bindPdfLoader() {
        var root = document.getElementById('employee-checklist-root');
        if (!root) {
            return;
        }

        if (root.dataset.pdfLoaderBound !== '1') {
            root.dataset.pdfLoaderBound = '1';
            root.addEventListener('click', handlePdfClick);
        }

        resetAllPdfLinks(root);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', bindPdfLoader);
    } else {
        bindPdfLoader();
    }

    document.addEventListener('livewire:navigated', bindPdfLoader);

    document.addEventListener('livewire:init', function () {
        if (typeof Livewire === 'undefined' || typeof Livewire.hook !== 'function') {
            return;
        }

        Livewire.hook('morph.updated', function () {
            bindPdfLoader();
        });
    });
})();
</script>
