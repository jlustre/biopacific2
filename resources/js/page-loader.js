(function initGlobalPageLoader() {
    const LOADER_ID = 'globalPageLoader';
    const MIN_DISPLAY_MS = 280;
    let hideTimeout = null;
    let shownAt = 0;

    function getLoader() {
        return document.getElementById(LOADER_ID);
    }

    function setMessage(message) {
        const el = getLoader();
        if (!el || !message) {
            return;
        }

        const msg = el.querySelector('.global-page-loader__message');
        if (msg) {
            msg.textContent = message;
        }
    }

    function setActive(active) {
        const el = getLoader();
        if (!el) {
            return;
        }

        el.classList.toggle('global-page-loader--hidden', !active);
        el.setAttribute('aria-hidden', active ? 'false' : 'true');
        el.setAttribute('aria-busy', active ? 'true' : 'false');
        document.documentElement.classList.toggle('page-loader-active', active);
    }

    window.showPageLoader = function showPageLoader(message) {
        clearTimeout(hideTimeout);
        setMessage(message || 'Loading…');
        shownAt = Date.now();
        setActive(true);
    };

    window.hidePageLoader = function hidePageLoader() {
        const elapsed = Date.now() - shownAt;
        const delay = Math.max(0, MIN_DISPLAY_MS - elapsed);

        clearTimeout(hideTimeout);
        hideTimeout = setTimeout(() => setActive(false), delay);
    };

    function shouldHandleLink(anchor, event) {
        if (!anchor || anchor.tagName !== 'A') {
            return false;
        }

        if (event.defaultPrevented || event.button !== 0) {
            return false;
        }

        if (event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) {
            return false;
        }

        if (anchor.target === '_blank' || anchor.hasAttribute('download') || anchor.hasAttribute('data-no-loader') || anchor.hasAttribute('data-assessment-pdf-link')) {
            return false;
        }

        const href = anchor.getAttribute('href');
        if (!href || href.startsWith('#') || href.startsWith('javascript:') || href.startsWith('mailto:') || href.startsWith('tel:')) {
            return false;
        }

        try {
            const url = new URL(href, window.location.href);
            return url.origin === window.location.origin;
        } catch {
            return false;
        }
    }

    document.addEventListener('click', (event) => {
        const anchor = event.target.closest('a[href]');
        if (shouldHandleLink(anchor, event)) {
            window.showPageLoader();
        }
    }, true);

    document.addEventListener('submit', (event) => {
        const form = event.target;
        if (!form || form.tagName !== 'FORM' || form.hasAttribute('data-no-loader')) {
            return;
        }

        if (form.getAttribute('data-ajax') === 'true') {
            return;
        }

        window.setTimeout(() => {
            if (!event.defaultPrevented) {
                window.showPageLoader();
            }
        }, 0);
    }, false);

    document.addEventListener('livewire:navigate', () => window.showPageLoader());
    document.addEventListener('livewire:navigated', () => window.hidePageLoader());

    window.addEventListener('pageshow', (event) => {
        if (event.persisted) {
            window.hidePageLoader();
        }
    });

    window.addEventListener('load', () => window.hidePageLoader());

    if (document.readyState === 'complete') {
        window.hidePageLoader();
    }
})();
