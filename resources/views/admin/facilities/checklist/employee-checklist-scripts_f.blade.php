<script>
    // Binds click events for PART F checklist action links (Verify/View)
    function bindChecklistLinksF() {
        // New Period button logic
        var addNewPeriodBtn = document.getElementById('addNewPeriodBtn');
        if (addNewPeriodBtn) {
            addNewPeriodBtn.onclick = function() {
                openNewPeriodModal();
            };
        }
        // New Period modal logic
        var newPeriodModal = document.getElementById('newPeriodModal');
        var newPeriodForm = document.getElementById('newPeriodForm');
        if (newPeriodForm) {
            newPeriodForm.onsubmit = function(e) {
                e.preventDefault();
                var dateInput = document.getElementById('newEffDateInput');
                var errorSpan = document.getElementById('newEffDateError');
                if (!dateInput.value) {
                    errorSpan.classList.remove('hidden');
                    return;
                } else {
                    errorSpan.classList.add('hidden');
                }
                // Option 1: Submit via AJAX (recommended)
                var tokenMeta = document.querySelector('meta[name="csrf-token"]');
                var token = tokenMeta ? tokenMeta.getAttribute('content') : '';
                fetch('/admin/employees/performance-assessment/period', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        emp_id: window.currentEmpId,
                        eff_date: dateInput.value
                    })
                })
                .then(async response => {
                    let data;
                    let rawText = await response.text();
                    try {
                        data = JSON.parse(rawText);
                    } catch (err) {
                        alert('Failed to create assessment period.');
                        return;
                    }
                    if (data.success) {
                        // Reload page to show new period
                        window.location.reload();
                    } else {
                        alert('Failed to create assessment period.');
                    }
                })
                .catch(err => {
                    alert('Failed to create assessment period.');
                });
            };
        }
    }

    // Modal open/close helpers for new period
    function openNewPeriodModal() {
        var modal = document.getElementById('newPeriodModal');
        if (modal) modal.classList.remove('hidden');
        var dateInput = document.getElementById('newEffDateInput');
        if (dateInput) {
            var today = new Date();
            var yyyy = today.getFullYear();
            var mm = String(today.getMonth() + 1).padStart(2, '0');
            var dd = String(today.getDate()).padStart(2, '0');
            dateInput.value = yyyy + '-' + mm + '-' + dd;
        }
    }
    function closeNewPeriodModal() {
        var modal = document.getElementById('newPeriodModal');
        if (modal) modal.classList.add('hidden');
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
    // For each 'Revoke' link in PART F, bind click to revoke the assessment
    document.querySelectorAll('.unverify-link[data-item-key]').forEach(function(link) {
        link.onclick = function(e) {
            e.preventDefault();
            var itemKey = this.getAttribute('data-item-key');
            var empId = this.getAttribute('data-emp-id');
            var effDate = (window.selectedEffDate || '');
            if (!confirm('Are you sure you want to revoke this assessment?')) return;
            // Disable the link and show loading text
            var originalText = this.textContent;
            this.textContent = 'Revoking...';
            this.style.pointerEvents = 'none';
            this.style.opacity = '0.6';
            // Get CSRF token
            var tokenMeta = document.querySelector('meta[name="csrf-token"]');
            if (!tokenMeta) {
                alert('CSRF token missing.');
                // Restore link state
                this.textContent = originalText;
                this.style.pointerEvents = '';
                this.style.opacity = '';
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
                    emp_id: empId,
                    item_key: itemKey,
                    eff_date: effDate
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
                    link.textContent = originalText;
                    link.style.pointerEvents = '';
                    link.style.opacity = '';
                    return;
                }
                if (data.success && data.data && data.data.items) {
                    // Remove the item from the row
                    updatePartFRow(itemKey, empId, data.data.items[itemKey], data.data.assessed_by, data.data.assessment_date);
                } else {
                    alert('Revoke failed.2');
                    // Restore link state
                    link.textContent = originalText;
                    link.style.pointerEvents = '';
                    link.style.opacity = '';
                }
            })
            .catch(err => {
                alert('Revoke failed.3: ' + (err && err.message ? err.message : err));
                console.error('PART F revoke error:', err);
                // Restore link state
                link.textContent = originalText;
                link.style.pointerEvents = '';
                link.style.opacity = '';
            });
        };
    });
        // For each 'Verify' link in PART F, bind click to open the modal in edit mode
        document.querySelectorAll('.verify-link[data-item-key]').forEach(function(link) {
            link.onclick = function(e) {
                e.preventDefault();
                var itemKey = this.getAttribute('data-item-key');
                var empId = this.getAttribute('data-emp-id');
                openVerifyModalF(itemKey, empId, false); // false = not view-only
            };
        });
        // For each 'View' link in PART F, bind click to open the modal in view-only mode
        document.querySelectorAll('.view-link[data-item-key]').forEach(function(link) {
            link.onclick = function(e) {
                e.preventDefault();
                var itemKey = this.getAttribute('data-item-key');
                var empId = this.getAttribute('data-emp-id');
                openVerifyModalF(itemKey, empId, true); // true = view-only
            };
        });
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
        var effDateField = document.getElementById('verifyEffDateF');
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
        // Set eff_date field (assessment period)
        if (effDateField) {
            if (forceNew && effDate) {
                effDateField.value = effDate;
                effDateField.readOnly = false;
            } else if (window.selectedEffDate) {
                effDateField.value = window.selectedEffDate;
                effDateField.readOnly = true;
            } else {
                effDateField.value = effDate || '';
                effDateField.readOnly = false;
            }
        }
        // Populate comments if available
        commentsField.value = link && link.getAttribute('data-comments') ? link.getAttribute('data-comments') : '';
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

    // Closes the PART F modal
    function closeVerifyModalF() {
        var modal = document.getElementById('verifyModalF');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

     // PART F: Handles the form submission for performance assessment verification (modal)
    document.getElementById('verifyFormF').onsubmit = function(e) {
        e.preventDefault(); // Prevent default form submission (page reload)

        // Get the employee ID, item key, and eff_date from hidden fields
        var empId = document.getElementById('verifyEmpIdF').value;
        var itemKey = document.getElementById('verifyItemKeyF').value;
        var effDate = document.getElementById('verifyEffDateF').value;

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

        // Send AJAX POST request to save the assessment
        fetch('/admin/employees/performance-assessment', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                emp_id: empId,
                item_key: itemKey,
                rating: rating,
                assessment_date: assessmentDate,
                comments: comments,
                eff_date: effDate
            })
        })
        .then(async response => {
            let data;
            let rawText = await response.text();
            try {
                // Try to parse the JSON response
                data = JSON.parse(rawText);
            } catch (err) {
                // If parsing fails, show error
                alert('Save failed.1');
                return;
            }
            // If save is successful and expected data is present, close modal and update the table row
            if (data.success && data.data && data.data.items && data.data.assessed_by) {
                closeVerifyModalF();
                updatePartFRow(itemKey, empId, data.data.items[itemKey], data.data.assessed_by, data.data.assessment_date);
            } else {
                // Otherwise, show error
                alert('Save failed.2');
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
    });

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
        // Update Assessed Date
        row.children[3].textContent = item && item.verified_dt ? item.verified_dt : (assessmentDate || '');
        // Update Assessed By (try to get name from global user list if available)
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
            assessLink.className = 'text-teal-600 underline verify-link';
            assessLink.setAttribute('data-item-key', itemKey);
            assessLink.setAttribute('data-emp-id', empId);
            assessLink.textContent = 'Assess';
            assessLink.title = 'Assess Item';
            actionCell.appendChild(assessLink);
        }
        // Re-bind PART F links after row update
        bindChecklistLinksF();
    }
</script>