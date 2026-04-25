<script>
    // Opens the modal for creating or editing an assessment period
    function openNewPeriodModal(period = null) {
        var newPeriodModal = document.getElementById('newPeriodModal');
        var newPeriodForm = document.getElementById('newPeriodForm');
        var dateFromInput = document.getElementById('newPeriodDateFromInput');
        var dateToInput = document.getElementById('newPeriodDateToInput');
        var reviewTypeInput = document.getElementById('newPeriodReviewTypeInput');
        var periodIdInput = document.getElementById('newPeriodIdInput');
        var title = document.getElementById('periodModalTitle');
        var submitBtn = document.getElementById('periodModalSubmitBtn');
        if (!newPeriodModal || !newPeriodForm || !dateFromInput || !dateToInput || !reviewTypeInput || !periodIdInput || !title || !submitBtn) {
            alert('Modal elements missing.');
            return;
        }
        // Reset form fields
        newPeriodForm.reset();
        periodIdInput.value = '';
        dateFromInput.value = '';
        dateToInput.value = '';
        reviewTypeInput.value = 'A';
        // Hide error messages
        var dateFromError = document.getElementById('newPeriodDateFromError');
        var dateToError = document.getElementById('newPeriodDateToError');
        if (dateFromError) dateFromError.classList.add('hidden');
        if (dateToError) dateToError.classList.add('hidden');
        // If editing, populate fields
        if (period) {
            periodIdInput.value = period.id || '';
            dateFromInput.value = period.date_from || '';
            dateToInput.value = period.date_to || '';
            reviewTypeInput.value = period.review_type || 'A';
            if (title) title.textContent = 'Edit Assessment Period';
            if (submitBtn) submitBtn.textContent = 'Update';
        } else {
            var today = new Date();
            var yyyy = today.getFullYear();
            var mm = String(today.getMonth() + 1).padStart(2, '0');
            var dd = String(today.getDate()).padStart(2, '0');
            var todayStr = yyyy + '-' + mm + '-' + dd;
            if (dateFromInput) dateFromInput.value = todayStr;
            if (dateToInput) dateToInput.value = todayStr;
            if (reviewTypeInput) reviewTypeInput.value = 'A';
            if (title) title.textContent = 'Create Assessment Period';
            if (submitBtn) submitBtn.textContent = 'Create';
        }
        // Show the modal
        newPeriodModal.classList.remove('hidden');
        newPeriodModal.classList.add('flex');
    }
    // Binds click events for PART F checklist action links (Verify/View)
    function bindChecklistLinksF() {
        // No longer disables PART F links globally; handled in event delegation
        // Sync assessment period dropdown with window.selectedAssessmentPeriodId and hidden input
        var periodSelect = document.getElementById('assessmentPeriodSelect');
        if (periodSelect) {
            window.selectedAssessmentPeriodId = periodSelect.value;
            periodSelect.addEventListener('change', function() {
                window.selectedAssessmentPeriodId = this.value;
                var apidField = document.getElementById('verifyAssessmentPeriodIdF');
                if (apidField) apidField.value = this.value;
            });
        }
        // New Period button logic
        var addNewPeriodBtn = document.getElementById('addNewPeriodBtn');
        if (addNewPeriodBtn) {
            addNewPeriodBtn.onclick = function() {
                openNewPeriodModal();
            };
        }
        // Edit Period button logic
        var editPeriodBtn = document.getElementById('editPeriodBtn');
        if (editPeriodBtn) {
            editPeriodBtn.onclick = function() {
                var periodSelect = document.getElementById('assessmentPeriodSelect');
                if (!periodSelect) return;
                var selectedId = periodSelect.value;
                var selectedOption = periodSelect.options[periodSelect.selectedIndex];
                // Find the selected period's data from window.assessmentPeriods (to be injected)
                var periods = window.assessmentPeriods || [];
                var period = periods.find(function(p) { return p.id == selectedId; });
                openNewPeriodModal(period);
            };
        }

        // Modal submit logic for creating/updating assessment periods
        var newPeriodForm = document.getElementById('newPeriodForm');
        if (newPeriodForm) {
            newPeriodForm.onsubmit = function(e) {
                e.preventDefault();
                var id = document.getElementById('newPeriodIdInput').value;
                var dateFrom = document.getElementById('newPeriodDateFromInput').value;
                var dateTo = document.getElementById('newPeriodDateToInput').value;
                var reviewType = document.getElementById('newPeriodReviewTypeInput').value;
                var dateFromError = document.getElementById('newPeriodDateFromError');
                var dateToError = document.getElementById('newPeriodDateToError');
                var submitBtn = document.getElementById('periodModalSubmitBtn');
                // Basic validation
                var hasError = false;
                if (!dateFrom) {
                    if (dateFromError) dateFromError.classList.remove('hidden');
                    hasError = true;
                } else {
                    if (dateFromError) dateFromError.classList.add('hidden');
                }
                if (!dateTo) {
                    if (dateToError) dateToError.classList.remove('hidden');
                    hasError = true;
                } else {
                    if (dateToError) dateToError.classList.add('hidden');
                }
                if (hasError) return;
                // CSRF token
                var tokenMeta = document.querySelector('meta[name="csrf-token"]');
                if (!tokenMeta) {
                    alert('CSRF token missing.');
                    return;
                }
                var token = tokenMeta.getAttribute('content');
                // Disable submit button
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.textContent = id ? 'Updating...' : 'Creating...';
                }
                // Prepare payload
                var payload = {
                    date_from: dateFrom,
                    date_to: dateTo,
                    review_type: reviewType
                };
                if (id) payload.id = id;
                // Use the correct backend route for assessment period create/update
                fetch('/admin/employees/performance-assessment/period', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(payload)
                })
                .then(async response => {
                    let data;
                    let rawText = await response.text();
                    try {
                        data = JSON.parse(rawText);
                    } catch (err) {
                        alert('Save failed.1');
                        if (submitBtn) {
                            submitBtn.disabled = false;
                            submitBtn.textContent = id ? 'Update' : 'Create';
                        }
                        return;
                    }
                    // Handle warning (overlap or in-use)
                    if (data.warning && data.message) {
                        if (confirm(data.message)) {
                            // Add force or force_edit flag and re-submit
                            if (data.message.includes('overlaps')) {
                                payload.force = true;
                            } else if (data.message.includes('using this assessment period')) {
                                payload.force_edit = true;
                            }
                            fetch('/admin/employees/performance-assessment/period', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': token,
                                    'Accept': 'application/json'
                                },
                                body: JSON.stringify(payload)
                            })
                            .then(async response2 => {
                                let data2;
                                let rawText2 = await response2.text();
                                try {
                                    data2 = JSON.parse(rawText2);
                                } catch (err) {
                                    alert('Save failed.1');
                                    if (submitBtn) {
                                        submitBtn.disabled = false;
                                        submitBtn.textContent = id ? 'Update' : 'Create';
                                    }
                                    return;
                                }
                                if (data2.success) {
                                    closeNewPeriodModal();
                                    window.location.reload();
                                } else {
                                    alert(data2.message || 'Save failed.2');
                                    if (submitBtn) {
                                        submitBtn.disabled = false;
                                        submitBtn.textContent = id ? 'Update' : 'Create';
                                    }
                                }
                            })
                            .catch(err => {
                                alert('Save failed.3: ' + (err && err.message ? err.message : err));
                                if (submitBtn) {
                                    submitBtn.disabled = false;
                                    submitBtn.textContent = id ? 'Update' : 'Create';
                                }
                            });
                            return;
                        } else {
                            if (submitBtn) {
                                submitBtn.disabled = false;
                                submitBtn.textContent = id ? 'Update' : 'Create';
                            }
                            return;
                        }
                    }
                    if (data.success) {
                        closeNewPeriodModal();
                        window.location.reload();
                    } else {
                        alert(data.message || 'Save failed.2');
                        if (submitBtn) {
                            submitBtn.disabled = false;
                            submitBtn.textContent = id ? 'Update' : 'Create';
                        }
                    }
                })
                .catch(err => {
                    alert('Save failed.3: ' + (err && err.message ? err.message : err));
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.textContent = id ? 'Update' : 'Create';
                    }
                });
            };
        }
        // No modal logic here. All modal open/submit logic is handled by openNewPeriodModal().
    }

    function closeNewPeriodModal() {
        var modal = document.getElementById('newPeriodModal');
        if (modal) modal.classList.add('hidden');
        // Reset form fields
        var form = document.getElementById('newPeriodForm');
        if (form) form.reset();
    }

     // Opens the PART F modal and populates its fields for the selected item/employee
    function openVerifyModalF(itemKey, empId, viewOnly = false, effDate = null, forceNew = false) {
        var empIdField = document.getElementById('verifyEmpIdF');
        var itemKeyField = document.getElementById('verifyItemKeyF');
        var ratingField = document.getElementById('verifyRatingF');
        var assessmentDateField = document.getElementById('verifyAssessmentDateF');
        var commentsField = document.getElementById('verifyCommentsF');
        var assessedByField = document.getElementById('verifyAssessedByF');
        var assessedByIdField = document.getElementById('verifyAssessedByIdF');
        var apidField = document.getElementById('verifyAssessmentPeriodIdF');
        // Find the row for this itemKey/empId to get any existing data
        var link = itemKey ? Array.from(document.querySelectorAll('.verify-link, .view-link')).find(
            l => l.getAttribute('data-item-key') === itemKey && l.getAttribute('data-emp-id') == empId
        ) : null;
        var row = link ? link.closest('tr') : null;
        // Set hidden fields for employee and item
        empIdField.value = empId;
        itemKeyField.value = itemKey || '';
        // Try to get rating from data attribute, or from the Blade-rendered table cell
        var ratingValue = '';
        if (link && link.getAttribute('data-rating')) {
            ratingValue = link.getAttribute('data-rating');
        } else if (row && row.children[2]) {
            // Try to map text to value if user refreshed page
            var cellText = row.children[2].textContent.trim().toLowerCase();
            if (cellText === 'below' || cellText === '1') ratingValue = '1';
            else if (cellText === 'meets' || cellText === '2') ratingValue = '2';
            else if (cellText === 'exceeds' || cellText === '3') ratingValue = '3';
        }
        ratingField.value = ratingValue;
        // Set assessment date to today if not present
        if (link && link.getAttribute('data-assessment-date')) {
            assessmentDateField.value = link.getAttribute('data-assessment-date');
        } else {
            var today = new Date();
            var yyyy = today.getFullYear();
            var mm = String(today.getMonth() + 1).padStart(2, '0');
            var dd = String(today.getDate()).padStart(2, '0');
            assessmentDateField.value = yyyy + '-' + mm + '-' + dd;
        }
        // Set assessment_period_id field
        if (apidField) {
            if (window.selectedAssessmentPeriodId) {
                apidField.value = window.selectedAssessmentPeriodId;
                apidField.readOnly = true;
            } else {
                apidField.value = '';
                apidField.readOnly = false;
            }
        }
        // Populate comments if available, else fetch from backend if in view mode
        var docTypeId = null;
        if (link && link.getAttribute('data-doc-type-id')) {
            docTypeId = link.getAttribute('data-doc-type-id');
        } else if (row) {
            // Fallback: Try to get docTypeId from a textarea in the same section
            var sectionRow = row.closest('table');
            if (sectionRow) {
                var textarea = sectionRow.parentElement.querySelector('textarea.section-comment-textarea');
                if (textarea && textarea.dataset.docTypeId) {
                    docTypeId = textarea.dataset.docTypeId;
                }
            }
        }
        var assessmentPeriodId = apidField ? apidField.value : (window.selectedAssessmentPeriodId || '');
        if (viewOnly && docTypeId && empId && assessmentPeriodId && itemKey) {
            // Fetch comment from backend
            commentsField.value = '';
            fetch('/admin/employees/performance-section-comment?employee_num=' + encodeURIComponent(empId) + '&assessment_period_id=' + encodeURIComponent(assessmentPeriodId) + '&doc_type_id=' + encodeURIComponent(docTypeId) + '&item_key=' + encodeURIComponent(itemKey), {
                headers: { 'Accept': 'application/json' }
            })
            .then(resp => resp.json())
            .then(data => {
                if (data && data.success && data.data && data.data.comment) {
                    commentsField.value = data.data.comment;
                } else {
                    commentsField.value = '';
                }
            })
            .catch(() => { 
                commentsField.value = ''; 
            });
        } else {
            commentsField.value = link && link.getAttribute('data-comments') ? link.getAttribute('data-comments') : '';
        }
        // Set assessed by fields to current user
        if (window.currentUserName) {
            assessedByField.value = window.currentUserName;
        }
        if (window.currentUserId) {
            assessedByIdField.value = window.currentUserId;
        }
        // Show the modal
        var modal = document.getElementById('verifyModalF');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeVerifyModalF() {
        var modal = document.getElementById('verifyModalF');
        modal.classList.add('hidden');
        modal.classList.remove('flex');

        // Reset form fields
        document.getElementById('verifyFormF').reset();
    }

        // After PART F row update, always re-bind PART F links
    function updatePartFRow(itemKey, empId, item, assessedById, assessmentDate) {
        // Find the row by matching the action link with data-item-key and data-emp-id
        var row = null;
        document.querySelectorAll('.verify-link[data-item-key], .view-link[data-item-key]').forEach(function(link) {
            if (link.getAttribute('data-item-key') === itemKey && link.getAttribute('data-emp-id') == empId) {
                row = link.closest('tr');
            }
        });
        if (!row) {
            console.error('updatePartFRow: Row not found for itemKey:', itemKey, 'empId:', empId);
            return;
        }
        if (row.children.length < 6) {
            console.error('updatePartFRow: Row does not have enough columns:', row, row.children);
            return;
        }
        // Update Rating as word
        var ratingText = '';
        if (item && item.rating) {
            if (item.rating == 1 || item.rating === '1') ratingText = 'Below';
            else if (item.rating == 2 || item.rating === '2') ratingText = 'Meets';
            else if (item.rating == 3 || item.rating === '3') ratingText = 'Exceeds';
        }
        row.children[2].textContent = ratingText;
        // Update Assessed Date and Assessed By
        if (item && item.rating) {
            row.children[3].textContent = item.verified_dt ? item.verified_dt : (assessmentDate || '');
            var assessedByCell = row.children[4];
            if (!assessedByCell) {
                console.error('updatePartFRow: assessedByCell missing', row, row.children);
                return;
            }
            while (assessedByCell.firstChild) { assessedByCell.removeChild(assessedByCell.firstChild); }
            var assessedByName = '';
            if (window.users && window.users[assessedById]) {
                assessedByName = window.users[assessedById];
            } else if (window.currentUserName && window.currentUserId == assessedById) {
                assessedByName = window.currentUserName;
            } else {
                assessedByName = assessedById;
            }
            assessedByCell.appendChild(document.createTextNode(assessedByName));
        } else {
            // Clear Assessed Date and Assessed By columns if revoked
            row.children[3].textContent = '';
            var assessedByCell = row.children[4];
            if (assessedByCell) {
                while (assessedByCell.firstChild) { assessedByCell.removeChild(assessedByCell.firstChild); }
            }
        }
        // Update Actions: show Revoke & View if assessed, else Assess
        var actionCell = row.children[5];
        if (!actionCell) {
            console.error('updatePartFRow: actionCell missing', row, row.children);
            return;
        }
        while (actionCell.firstChild) { actionCell.removeChild(actionCell.firstChild); }
        if (item && item.rating) {
            // Show Revoke | View
            var revokeLink = document.createElement('a');
            revokeLink.href = '#';
            revokeLink.className = 'text-red-600 underline mr-1 unverify-link';
            revokeLink.setAttribute('data-item-key', itemKey);
            revokeLink.setAttribute('data-emp-id', empId);
            revokeLink.textContent = 'Revoke';
            revokeLink.title = 'Revoke Assessment';
            actionCell.appendChild(revokeLink);
            var sep = document.createElement('span');
            sep.textContent = ' | ';
            actionCell.appendChild(sep);
            var viewLink = document.createElement('a');
            viewLink.href = '#';
            viewLink.className = 'text-teal-600 underline ml-1 view-link';
            viewLink.setAttribute('data-item-key', itemKey);
            viewLink.setAttribute('data-emp-id', empId);
            viewLink.textContent = 'View';
            viewLink.title = 'View Assessment Details';
            actionCell.appendChild(viewLink);
        } else {
            // Show Assess
            var assessLink = document.createElement('a');
            assessLink.href = '#';
            assessLink.className = 'text-teal-600 underline verify-link cursor-pointer';
            assessLink.setAttribute('data-item-key', itemKey);
            assessLink.setAttribute('data-emp-id', empId);
            assessLink.textContent = 'Assess';
            assessLink.title = 'Assess Item';
            actionCell.appendChild(assessLink);
        }
        // Re-bind PART F links after row update
        bindChecklistLinksF();
    }

    // Add New Period button: open modal with empty/default fields and today's date as eff_date
    var addNewPeriodBtn = document.getElementById('addNewPeriodBtn');
    if (addNewPeriodBtn) {
        addNewPeriodBtn.onclick = function() {
            var today = new Date();
            var yyyy = today.getFullYear();
            var mm = String(today.getMonth() + 1).padStart(2, '0');
            var dd = String(today.getDate()).padStart(2, '0');
            var effDate = yyyy + '-' + mm + '-' + dd;
            openVerifyModalF(null, window.currentEmpId, false, effDate, true); // true = force new period
        };
    }

    // Assessment period dropdown: reload page on change
    var periodSelect = document.getElementById('assessmentPeriodSelect');
    if (periodSelect) {
        periodSelect.onchange = function() {
            var form = document.getElementById('assessmentPeriodForm');
            if (form) form.submit();
        };
    }


    // Use event delegation for 'Revoke' link in PART F
    var partFTable = document.querySelector('#partFTableContainer') || document;
    partFTable.addEventListener('click', function(e) {
        var target = e.target;
        if (target.classList.contains('unverify-link') && target.hasAttribute('data-item-key')) {
            e.preventDefault();
            var itemKey = target.getAttribute('data-item-key');
            var empId = target.getAttribute('data-emp-id');
            var assessmentPeriodId = (window.selectedAssessmentPeriodId || '');
            if (!confirm('Are you sure you want to revoke this assessment?')) return;
            // Disable the link and show loading text
            var originalText = target.textContent;
            target.textContent = 'Revoking...';
            target.style.pointerEvents = 'none';
            target.style.opacity = '0.6';
            // Get CSRF token
            var tokenMeta = document.querySelector('meta[name="csrf-token"]');
            if (!tokenMeta) {
                alert('CSRF token missing.');
                // Restore link state
                target.textContent = originalText;
                target.style.pointerEvents = '';
                target.style.opacity = '';
                return;
            }
            var token = tokenMeta.getAttribute('content');
            fetch('/admin/employees/performance-assessment/revoke', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    employee_num: empId,
                    item_key: itemKey,
                    assessment_period_id: assessmentPeriodId
                })
            })
            .then(async response => {
                let data;
                let rawText = await response.text();
                try {
                    data = JSON.parse(rawText);
                } catch (err) {
                    alert('Revoke failed.1');
                    // Restore link state
                    target.textContent = originalText;
                    target.style.pointerEvents = '';
                    target.style.opacity = '';
                    return;
                }
                if (data.success && data.data && data.data.items) {
                    // Remove the item from the row
                    updatePartFRow(itemKey, empId, data.data.items[itemKey], data.data.assessed_by, data.data.assessment_date);
                } else {
                    alert('Revoke failed.2');
                    // Restore link state
                    target.textContent = originalText;
                    target.style.pointerEvents = '';
                    target.style.opacity = '';
                }
            })
            .catch(err => {
                alert('Revoke failed.3: ' + (err && err.message ? err.message : err));
                console.error('PART F revoke error:', err);
                // Restore link state
                target.textContent = originalText;
                target.style.pointerEvents = '';
                target.style.opacity = '';
            });
        }
    });

    // For each 'Verify' link in PART F, bind click to open the modal in edit mode
    // Use event delegation for .verify-link and .view-link
    var partFTable = document.querySelector('#partFTableContainer') || document;
    partFTable.addEventListener('click', function(e) {
        var target = e.target;
        if ((target.classList.contains('verify-link') || target.classList.contains('view-link')) && target.hasAttribute('data-item-key')) {
            e.preventDefault();
            if (!window.selectedAssessmentPeriodId) {
                alert('Please select or create an assessment period before making changes.');
                return;
            }
            var itemKey = target.getAttribute('data-item-key');
            var empId = target.getAttribute('data-emp-id');
            var isView = target.classList.contains('view-link');
            openVerifyModalF(itemKey, empId, isView);
        }
    });

     // PART F: Handles the form submission for performance assessment verification (modal)
    document.getElementById('verifyFormF').onsubmit = function(e) {
        e.preventDefault(); // Prevent default form submission (page reload)

        // Get the employee ID, item key, and assessment_period_id from hidden fields
        var empId = document.getElementById('verifyEmpIdF').value;
        var itemKey = document.getElementById('verifyItemKeyF').value;
        var assessmentPeriodId = document.getElementById('verifyAssessmentPeriodIdF').value;

        // Get the selected rating and the error display element
        var rating = document.getElementById('verifyRatingF').value;
        var ratingError = document.getElementById('ratingFError');

        // Validate that a rating is selected; if not, show error and focus the field
        if (!rating) {
            if (ratingError) ratingError.classList.remove('hidden');
            document.getElementById('verifyRatingF').focus();
            return;
        } else {
            if (ratingError) ratingError.classList.add('hidden');
        }

        // Get other form values: assessment date, comments, and assessed by (user ID)
        var assessmentDate = document.getElementById('verifyAssessmentDateF').value;
        var comments = document.getElementById('verifyCommentsF').value;
        var assessedBy = document.getElementById('verifyAssessedByIdF').value;

        // Get CSRF token from meta tag for secure POST request
        var tokenMeta = document.querySelector('meta[name="csrf-token"]');
        if (!tokenMeta) {
            alert('CSRF token missing.');
            return;
        }
        var token = tokenMeta.getAttribute('content');

        // Debug: Log payload before sending
        const payload = {
            employee_num: empId,
            item_key: itemKey,
            rating: rating,
            assessment_date: assessmentDate,
            comments: comments,
            assessment_period_id: assessmentPeriodId
        };
        console.log('PART F Save payload:', payload);

        // Send AJAX POST request to save the assessment
        fetch('/admin/employees/performance-assessment', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token,
                'Accept': 'application/json'
            },
            body: JSON.stringify(payload)
        })
        .then(async response => {
            let data;
            let rawText = await response.text();
            try {
                data = JSON.parse(rawText);
            } catch (err) {
                alert('Save failed.1');
                return;
            }
            if (data.success && data.data && data.data.items && data.data.assessed_by) {
                closeVerifyModalF();
                updatePartFRow(itemKey, empId, data.data.items[itemKey], data.data.assessed_by, data.data.assessment_date);
            } else {
                // Show backend error message if present
                let msg = 'Save failed.2';
                if (data && data.message) msg += '\n' + data.message;
                if (data && data.errors) msg += '\n' + JSON.stringify(data.errors);
                alert(msg);
            }
        })
        .catch(err => {
            // Handle network or server errors
            alert('Save failed.3: ' + (err && err.message ? err.message : err));
            console.error('PART F fetch error:', err);
        });
    };


    document.addEventListener('DOMContentLoaded', function() {
        // Expiration Date Not Required checkbox logic (for A-E compatibility, safe to leave)
        var expDtNotRequired = document.getElementById('expDtNotRequired');
        var expDtInput = document.getElementById('verifyExpDt');
        if (expDtNotRequired && expDtInput) {
            expDtNotRequired.addEventListener('change', function() {
                if (this.checked) {
                    expDtInput.value = '';
                    expDtInput.disabled = true;
                } else {
                    expDtInput.disabled = false;
                }
            });
        }
        // Always bind PART F links on load
        bindChecklistLinksF();

        // PART F: Section comment save logic
        document.querySelectorAll('.section-comment-save-btn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                var docTypeId = btn.getAttribute('data-doc-type-id');
                var empId = btn.getAttribute('data-emp-id');
                var assessmentPeriodId = btn.getAttribute('data-assessment-period-id');
                var textarea = document.querySelector('.section-comment-textarea[data-doc-type-id="' + docTypeId + '"]');
                // Find the status span in the same .mb-2 container as the button
                var mb2Container = btn.closest('.mb-2');
                var statusSpan = mb2Container ? mb2Container.querySelector('.section-comment-status') : null;
                if (!docTypeId || !empId || !assessmentPeriodId) {
                    var debugMsg = 'Missing required data. ';
                    if (!docTypeId) debugMsg += '[docTypeId missing] ';
                    if (!empId) debugMsg += '[empId missing] ';
                    if (!assessmentPeriodId) debugMsg += '[assessmentPeriodId missing] ';
                    if (statusSpan) {
                        statusSpan.textContent = debugMsg;
                        statusSpan.style.color = 'red';
                    }
                    // Also log to console for developer
                    console.warn(debugMsg, {docTypeId, empId, assessmentPeriodId});
                    return;
                }
                var comment = textarea ? textarea.value : '';
                // Get CSRF token
                var tokenMeta = document.querySelector('meta[name="csrf-token"]');
                if (!tokenMeta) {
                    if (statusSpan) {
                        statusSpan.textContent = 'CSRF token missing.';
                        statusSpan.style.color = 'red';
                    }
                    return;
                }
                var token = tokenMeta.getAttribute('content');
                // Show saving status
                if (statusSpan) {
                    statusSpan.textContent = 'Saving...';
                    statusSpan.style.color = '#666';
                }
                fetch('/admin/employees/performance-section-comment', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        employee_num: empId,
                        assessment_period_id: assessmentPeriodId,
                        doc_type_id: docTypeId,
                        comment: comment
                    })
                })
                .then(async response => {
                    let data;
                    let rawText = await response.text();
                    try {
                        data = JSON.parse(rawText);
                    } catch (err) {
                        if (statusSpan) {
                            statusSpan.textContent = 'Save failed.';
                            statusSpan.style.color = 'red';
                        }
                        return;
                    }
                    if (data.success) {
                        if (statusSpan) {
                            statusSpan.textContent = 'Saved!';
                            statusSpan.style.color = 'green';
                        }
                        setTimeout(function() {
                            statusSpan.textContent = '';
                        }, 2000);
                    } else {
                        if (statusSpan) {
                            statusSpan.textContent = data.message || 'Save failed.';
                            statusSpan.style.color = 'red';
                        }
                    }
                })
                .catch(() => {
                    if (statusSpan) {
                        statusSpan.textContent = 'Save failed.';
                        statusSpan.style.color = 'red';
                    }
                });
            });
        });
    });


</script>