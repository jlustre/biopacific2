document.addEventListener('DOMContentLoaded', function () {
    const titleInput = document.getElementById('modal-title');
    const templateSelect = document.getElementById('modal-template');
    const descriptionTextarea = document.getElementById('modal-description');
    if (!titleInput || !templateSelect || !descriptionTextarea) return;

    // Fetch templates when title changes
    titleInput.addEventListener('blur', function () {
        const title = titleInput.value.trim();
        if (!title) return;
        fetch(`/admin/job-openings/templates?title=${encodeURIComponent(title)}`)
            .then(res => res.json())
            .then(data => {
                templateSelect.innerHTML = '<option value="">Select a template</option>';
                data.forEach(t => {
                    templateSelect.innerHTML += `<option value="${t.id}" data-contents="${t.contents || ''}">${t.name || t.title}</option>`;
                });
            });
    });

    // Fill description when template is selected
    templateSelect.addEventListener('change', function () {
        const selected = templateSelect.options[templateSelect.selectedIndex];
        const contents = selected.getAttribute('data-contents') || '';
        if (contents) {
            descriptionTextarea.value = contents;
        }
    });
});
