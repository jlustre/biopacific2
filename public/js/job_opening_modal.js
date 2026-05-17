(function () {
    const TINYMCE_SELECTOR = '#modal-description';

    window.jobOpeningTemplates = window.jobOpeningTemplates || {};

    function isDescriptionEmpty(html) {
        if (!html) {
            return true;
        }
        const text = html
            .replace(/<[^>]*>/g, ' ')
            .replace(/&nbsp;/gi, ' ')
            .replace(/\s+/g, ' ')
            .trim();
        return text === '';
    }

    function getDescriptionContent() {
        syncJobOpeningDescription();
        if (typeof tinymce !== 'undefined' && tinymce.get('modal-description')) {
            return tinymce.get('modal-description').getContent();
        }
        const textarea = document.getElementById('modal-description');
        return textarea ? textarea.value : '';
    }

    function setDescriptionContent(html) {
        if (typeof tinymce !== 'undefined' && tinymce.get('modal-description')) {
            tinymce.get('modal-description').setContent(html || '');
            return;
        }
        const textarea = document.getElementById('modal-description');
        if (textarea) {
            textarea.value = html || '';
        }
    }

    function syncJobOpeningDescription() {
        if (typeof tinymce === 'undefined') {
            return;
        }
        const editor = tinymce.get('modal-description');
        if (editor) {
            editor.save();
        }
    }

    function getAlpineRoot() {
        return document.querySelector('[x-data*="showSaveTemplateModal"]');
    }

    function getAlpineData(el) {
        if (!el) {
            return null;
        }
        if (typeof Alpine !== 'undefined' && typeof Alpine.$data === 'function') {
            return Alpine.$data(el);
        }
        if (el._x_dataStack && el._x_dataStack[0]) {
            return el._x_dataStack[0];
        }
        return null;
    }

    function hideTemplateSaveMessages() {
        const errorEl = document.getElementById('job-opening-template-save-error');
        const successEl = document.getElementById('job-opening-template-save-success');
        if (errorEl) {
            errorEl.classList.add('hidden');
            errorEl.textContent = '';
        }
        if (successEl) {
            successEl.classList.add('hidden');
            successEl.textContent = '';
        }
    }

    function showTemplateSaveError(message) {
        const errorEl = document.getElementById('job-opening-template-save-error');
        if (errorEl) {
            errorEl.textContent = message;
            errorEl.classList.remove('hidden');
        }
    }

    function showTemplateSaveSuccess(message) {
        const successEl = document.getElementById('job-opening-template-save-success');
        if (successEl) {
            successEl.textContent = message;
            successEl.classList.remove('hidden');
        }
    }

    function populateTemplateSelect(templates) {
        const templateSelect = document.getElementById('modal-template');
        if (!templateSelect) {
            return;
        }

        window.jobOpeningTemplates = {};
        templateSelect.innerHTML = '<option value="">Select a template to load</option>';

        (templates || []).forEach((template) => {
            if (!template || !template.id) {
                return;
            }
            window.jobOpeningTemplates[String(template.id)] = {
                name: template.name || 'Template',
                contents: template.contents || '',
            };
            const option = document.createElement('option');
            option.value = String(template.id);
            option.textContent = template.name || 'Template';
            templateSelect.appendChild(option);
        });
    }

    function appendTemplateToSelect(template) {
        if (!template || !template.id) {
            return;
        }
        const templateSelect = document.getElementById('modal-template');
        if (!templateSelect) {
            return;
        }

        const id = String(template.id);
        window.jobOpeningTemplates[id] = {
            name: template.name || 'Template',
            contents: template.contents || '',
        };

        let exists = false;
        for (let i = 0; i < templateSelect.options.length; i++) {
            if (templateSelect.options[i].value === id) {
                exists = true;
                break;
            }
        }

        if (!exists) {
            const option = document.createElement('option');
            option.value = id;
            option.textContent = template.name || 'Template';
            templateSelect.appendChild(option);
        }

        templateSelect.value = id;
    }

    window.initJobOpeningTinyMCE = function () {
        if (typeof tinymce === 'undefined' || !document.getElementById('modal-description')) {
            return;
        }

        const existing = tinymce.get('modal-description');
        if (existing) {
            existing.show();
            return;
        }

        tinymce.init({
            selector: TINYMCE_SELECTOR,
            menubar: false,
            plugins: 'lists link table code',
            toolbar: 'undo redo | bold italic underline | bullist numlist | link table | code',
            min_height: 200,
            max_height: 360,
            branding: false,
            promotion: false,
            resize: true,
            setup: function (editor) {
                editor.on('change keyup', function () {
                    editor.save();
                });
            },
        });
    };

    window.openJobOpeningSaveTemplateModal = function () {
        hideTemplateSaveMessages();

        const titleSelect = document.getElementById('modal-title');
        const positionTitle = titleSelect && titleSelect.value && titleSelect.value !== 'Other'
            ? titleSelect.value.trim()
            : '';

        if (!positionTitle) {
            alert('Please select a job title before saving a template.');
            return;
        }

        const content = getDescriptionContent();
        if (isDescriptionEmpty(content)) {
            alert('Please enter a description before saving it as a template.');
            return;
        }

        const nameInput = document.getElementById('job-opening-template-name');
        const positionSelect = document.getElementById('job-opening-template-position');
        if (nameInput) {
            nameInput.value = positionTitle + ' Description';
        }
        if (positionSelect) {
            positionSelect.value = positionTitle;
        }

        const alpineData = getAlpineData(getAlpineRoot());
        if (alpineData) {
            alpineData.showSaveTemplateModal = true;
        }
    };

    window.submitJobOpeningSaveTemplate = async function () {
        hideTemplateSaveMessages();

        const config = window.jobOpeningConfig || {};
        if (!config.templateSaveUrl) {
            showTemplateSaveError('Template save is not configured for this page.');
            return;
        }

        const nameInput = document.getElementById('job-opening-template-name');
        const positionSelect = document.getElementById('job-opening-template-position');
        const saveBtn = document.getElementById('job-opening-template-save-btn');

        const templateName = nameInput ? nameInput.value.trim() : '';
        const positionTitle = positionSelect ? positionSelect.value.trim() : '';
        const contents = getDescriptionContent();

        if (!templateName) {
            showTemplateSaveError('Please enter a template name.');
            return;
        }
        if (!positionTitle) {
            showTemplateSaveError('Please select a position for this template.');
            return;
        }
        if (isDescriptionEmpty(contents)) {
            showTemplateSaveError('Description cannot be empty.');
            return;
        }

        if (saveBtn) {
            saveBtn.disabled = true;
            saveBtn.textContent = 'Saving...';
        }

        try {
            const resp = await fetch(config.templateSaveUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': config.csrfToken || '',
                },
                credentials: 'same-origin',
                body: JSON.stringify({
                    template_name: templateName,
                    template_position_title: positionTitle,
                    template_contents: contents,
                }),
            });

            const data = await resp.json().catch(() => ({}));

            if (!resp.ok) {
                let message = data.message || 'Could not save template.';
                if (data.errors) {
                    const firstKey = Object.keys(data.errors)[0];
                    if (firstKey && data.errors[firstKey][0]) {
                        message = data.errors[firstKey][0];
                    }
                }
                showTemplateSaveError(message);
                return;
            }

            if (data.template) {
                appendTemplateToSelect(data.template);
            }

            showTemplateSaveSuccess(data.message || 'Template saved successfully.');

            setTimeout(() => {
                const alpineData = getAlpineData(getAlpineRoot());
                if (alpineData) {
                    alpineData.showSaveTemplateModal = false;
                }
                hideTemplateSaveMessages();
            }, 1200);
        } catch (e) {
            showTemplateSaveError('Could not save template. Please try again.');
        } finally {
            if (saveBtn) {
                saveBtn.disabled = false;
                saveBtn.textContent = 'Save Template';
            }
        }
    };

    function setSelectValue(select, value) {
        if (!select || !value) {
            return false;
        }

        for (let i = 0; i < select.options.length; i++) {
            if (select.options[i].value === value) {
                select.selectedIndex = i;
                select.dispatchEvent(new Event('change', { bubbles: true }));
                return true;
            }
        }

        const opt = document.createElement('option');
        opt.value = value;
        opt.textContent = value;
        select.appendChild(opt);
        select.value = value;
        select.dispatchEvent(new Event('change', { bubbles: true }));
        return true;
    }

    function applyFromOption(option, departmentSelect, reportingSelect) {
        if (!option || !option.value || option.value === 'Other') {
            return;
        }

        const department = option.getAttribute('data-department') || '';
        const reportingTo = option.getAttribute('data-reporting-to') || '';

        if (department) {
            setSelectValue(departmentSelect, department);
        }
        if (reportingTo) {
            setSelectValue(reportingSelect, reportingTo);
        }
    }

    async function applyFromApi(title, departmentSelect, reportingSelect) {
        if (!title || title === 'Other') {
            return;
        }

        try {
            const resp = await fetch(`/admin/positions/lookup?title=${encodeURIComponent(title)}`, {
                headers: { Accept: 'application/json' },
                credentials: 'same-origin',
            });
            if (!resp.ok) {
                return;
            }
            const data = await resp.json();
            if (data.department) {
                setSelectValue(departmentSelect, data.department);
            }
            if (data.reporting_to) {
                setSelectValue(reportingSelect, data.reporting_to);
            }
        } catch (e) {
            // User can still set fields manually.
        }
    }

    function applyFromDefaultsMap(title, departmentSelect, reportingSelect) {
        const defaults = window.jobOpeningPositionDefaults?.[title];
        if (!defaults) {
            return;
        }
        if (defaults.department) {
            setSelectValue(departmentSelect, defaults.department);
        }
        if (defaults.reporting_to) {
            setSelectValue(reportingSelect, defaults.reporting_to);
        }
    }

    async function loadTemplatesForTitle(title) {
        const templateSelect = document.getElementById('modal-template');
        if (!templateSelect) {
            return;
        }

        if (!title || title === 'Other') {
            populateTemplateSelect([]);
            return;
        }

        try {
            const resp = await fetch(`/admin/job-description-templates?title=${encodeURIComponent(title)}`, {
                credentials: 'same-origin',
                headers: { Accept: 'application/json' },
            });
            if (!resp.ok) {
                return;
            }
            const data = await resp.json();
            populateTemplateSelect(data);
        } catch (e) {
            populateTemplateSelect([]);
        }
    }

    async function onTitleChanged(event) {
        const titleInput = event?.target || document.getElementById('modal-title');
        if (!titleInput) {
            return;
        }

        const departmentSelect = document.getElementById('modal-department');
        const reportingSelect = document.getElementById('modal-reporting-to');

        const option = titleInput.options[titleInput.selectedIndex];
        const title = titleInput.value.trim();

        if (!title || title === 'Other') {
            await loadTemplatesForTitle('');
            return;
        }

        applyFromDefaultsMap(title, departmentSelect, reportingSelect);
        applyFromOption(option, departmentSelect, reportingSelect);
        await applyFromApi(title, departmentSelect, reportingSelect);
        await loadTemplatesForTitle(title);
    }

    function bindJobOpeningForm() {
        const form = document.getElementById('job-opening-create-form')
            || document.querySelector('form[action*="job_openings.store"]');
        if (!form || form.dataset.jobOpeningFormBound) {
            return;
        }
        form.dataset.jobOpeningFormBound = '1';

        form.addEventListener('submit', function (e) {
            syncJobOpeningDescription();
            const textarea = document.getElementById('modal-description');
            if (textarea && isDescriptionEmpty(textarea.value)) {
                e.preventDefault();
                alert('Please enter a job description.');
            }
        });
    }

    function initJobOpeningModal() {
        const titleInput = document.getElementById('modal-title');
        const templateSelect = document.getElementById('modal-template');

        if (titleInput && !titleInput.dataset.positionAutofillBound) {
            titleInput.dataset.positionAutofillBound = '1';
            titleInput.addEventListener('change', onTitleChanged);
        }

        if (templateSelect && !templateSelect.dataset.templateBound) {
            templateSelect.dataset.templateBound = '1';
            templateSelect.addEventListener('change', function () {
                const template = window.jobOpeningTemplates[templateSelect.value];
                if (template && template.contents) {
                    setDescriptionContent(template.contents);
                }
            });
        }

        bindJobOpeningForm();
    }

    window.applyJobOpeningPositionDefaults = onTitleChanged;

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initJobOpeningModal);
    } else {
        initJobOpeningModal();
    }
})();
