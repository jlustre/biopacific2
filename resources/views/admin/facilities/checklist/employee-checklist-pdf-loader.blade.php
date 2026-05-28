<script>
(function () {
    function setPdfLinkLoading(link, loading) {
        var pdfIcon = link.querySelector('[data-pdf-icon]');
        var loaderIcon = link.querySelector('[data-pdf-loader]');

        if (loading) {
            link.dataset.pdfLoading = '1';
            link.setAttribute('aria-busy', 'true');
            link.classList.add('pointer-events-none', 'opacity-70');
            if (pdfIcon) {
                pdfIcon.classList.add('hidden');
            }
            if (loaderIcon) {
                loaderIcon.classList.remove('hidden');
                loaderIcon.classList.add('flex');
            }

            return;
        }

        delete link.dataset.pdfLoading;
        link.removeAttribute('aria-busy');
        link.classList.remove('pointer-events-none', 'opacity-70');
        if (pdfIcon) {
            pdfIcon.classList.remove('hidden');
        }
        if (loaderIcon) {
            loaderIcon.classList.add('hidden');
            loaderIcon.classList.remove('flex');
        }
    }

    function resetAllPdfLinks(root) {
        root.querySelectorAll('a[data-assessment-pdf-link]').forEach(function (link) {
            setPdfLinkLoading(link, false);
        });
    }

    function openAssessmentPdf(link) {
        if (link.dataset.pdfLoading === '1') {
            return;
        }

        var url = link.getAttribute('href');
        if (!url) {
            return;
        }

        setPdfLinkLoading(link, true);

        fetch(url, {
            method: 'GET',
            credentials: 'same-origin',
            headers: {
                Accept: 'application/pdf',
            },
        })
            .then(function (response) {
                if (!response.ok) {
                    throw new Error('PDF request failed with status ' + response.status);
                }

                return response.blob();
            })
            .then(function (blob) {
                var blobUrl = URL.createObjectURL(blob);
                var pdfWindow = window.open(blobUrl, '_blank', 'noopener,noreferrer');

                if (!pdfWindow) {
                    alert('Please allow pop-ups to view the PDF.');
                }

                window.setTimeout(function () {
                    URL.revokeObjectURL(blobUrl);
                }, 60000);
            })
            .catch(function () {
                alert('Unable to generate the PDF. Please try again.');
            })
            .finally(function () {
                setPdfLinkLoading(link, false);
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
