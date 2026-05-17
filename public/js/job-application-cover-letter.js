(function () {
    const EDITOR_ID = 'job-application-cover-letter';
    const MAX_INIT_ATTEMPTS = 40;
    let initTimeout = null;

    function getJobApplicationComponent() {
        const root = document.getElementById('job-application-form');
        if (!root || !window.Livewire) {
            return null;
        }
        const wireId = root.getAttribute('wire:id');
        return wireId ? Livewire.find(wireId) : null;
    }

    function isEditorTargetVisible(textarea) {
        if (!textarea) {
            return false;
        }
        const style = window.getComputedStyle(textarea);
        if (style.display === 'none' || style.visibility === 'hidden') {
            return false;
        }
        return textarea.getBoundingClientRect().width > 0;
    }

    function getCoverLetterContent() {
        if (typeof tinymce !== 'undefined' && tinymce.get(EDITOR_ID)) {
            tinymce.get(EDITOR_ID).save();
            return tinymce.get(EDITOR_ID).getContent();
        }
        const textarea = document.getElementById(EDITOR_ID);
        return textarea ? textarea.value : '';
    }

    window.syncJobApplicationCoverLetter = function () {
        const content = getCoverLetterContent();
        const component = getJobApplicationComponent();
        if (component) {
            component.set('cover_letter', content);
        }
        return content;
    };

    window.destroyJobApplicationCoverLetter = function () {
        if (typeof tinymce === 'undefined') {
            return;
        }
        const existing = tinymce.get(EDITOR_ID);
        if (existing) {
            existing.remove();
        }
    };

    window.initJobApplicationCoverLetter = function (attempt) {
        const tryCount = typeof attempt === 'number' ? attempt : 0;

        if (typeof tinymce === 'undefined') {
            clearTimeout(initTimeout);
            initTimeout = setTimeout(function () {
                window.initJobApplicationCoverLetter(tryCount);
            }, 100);
            return;
        }

        const textarea = document.getElementById(EDITOR_ID);
        if (!textarea || !isEditorTargetVisible(textarea)) {
            if (tryCount < MAX_INIT_ATTEMPTS) {
                setTimeout(function () {
                    window.initJobApplicationCoverLetter(tryCount + 1);
                }, 120);
            }
            return;
        }

        if (tinymce.get(EDITOR_ID)) {
            return;
        }

        const component = getJobApplicationComponent();
        const initialContent = component ? (component.get('cover_letter') || '') : '';

        tinymce.init({
            selector: '#' + EDITOR_ID,
            menubar: false,
            plugins: 'lists link code',
            toolbar: 'undo redo | bold italic underline | bullist numlist | link | code',
            min_height: 200,
            max_height: 320,
            branding: false,
            promotion: false,
            resize: true,
            init_instance_callback: function (editor) {
                editor.setContent(initialContent);
            },
            setup: function (editor) {
                let updateTimeout;
                const pushToLivewire = function () {
                    clearTimeout(updateTimeout);
                    updateTimeout = setTimeout(function () {
                        editor.save();
                        const componentRef = getJobApplicationComponent();
                        if (componentRef) {
                            componentRef.set('cover_letter', editor.getContent());
                        }
                    }, 400);
                };
                editor.on('change', pushToLivewire);
                editor.on('blur', function () {
                    clearTimeout(updateTimeout);
                    editor.save();
                    const componentRef = getJobApplicationComponent();
                    if (componentRef) {
                        componentRef.set('cover_letter', editor.getContent());
                    }
                });
            },
        });
    };

    function bindSubmitSync() {
        const formRoot = document.getElementById('job-application-form');
        if (!formRoot || formRoot.dataset.coverLetterBound) {
            return;
        }
        formRoot.dataset.coverLetterBound = '1';

        formRoot.addEventListener(
            'click',
            function (event) {
                const submitBtn = event.target.closest('button[type="submit"]');
                if (submitBtn) {
                    window.syncJobApplicationCoverLetter();
                }
            },
            true
        );
    }

    function registerLivewireHooks() {
        if (!window.Livewire) {
            return;
        }

        Livewire.on('job-application-form-ready', function () {
            window.destroyJobApplicationCoverLetter();
            setTimeout(function () {
                window.initJobApplicationCoverLetter(0);
            }, 150);
        });

        Livewire.on('job-application-form-cleared', function () {
            window.destroyJobApplicationCoverLetter();
            const component = getJobApplicationComponent();
            if (component) {
                component.set('cover_letter', '');
            }
            setTimeout(function () {
                window.initJobApplicationCoverLetter(0);
            }, 200);
        });

        Livewire.on('scrollToTop', function () {
            document.getElementById('success-message')?.scrollIntoView({
                behavior: 'smooth',
                block: 'center',
            });
        });

        Livewire.hook('message.processed', function () {
            const textarea = document.getElementById(EDITOR_ID);
            if (!textarea) {
                window.destroyJobApplicationCoverLetter();
                return;
            }
            if (!tinymce.get(EDITOR_ID) && isEditorTargetVisible(textarea)) {
                setTimeout(function () {
                    window.initJobApplicationCoverLetter(0);
                }, 100);
            }
        });
    }

    document.addEventListener('livewire:init', function () {
        bindSubmitSync();
        registerLivewireHooks();
    });

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', bindSubmitSync);
    } else {
        bindSubmitSync();
    }

    if (window.Livewire) {
        registerLivewireHooks();
    }
})();
