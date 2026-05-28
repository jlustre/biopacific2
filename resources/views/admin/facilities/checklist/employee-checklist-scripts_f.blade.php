<script>
    function bpDenySelfAssessmentAction() {
        if (window.bpEvaluatorActionsDisabled) {
            alert(window.bpSelfAssessmentDeniedMessage || 'You cannot perform this assessment on yourself.');
            return true;
        }
        return false;
    }

    function getHierarchyRowIndentLevel(row) {
        if (!row) {
            return 0;
        }

        var levelAttr = row.getAttribute('data-indent-level');
        if (levelAttr !== null && levelAttr !== '') {
            return Number(levelAttr || 0);
        }

        var cell = row.querySelector('td[style*="padding-left"]');
        if (cell && cell.style.paddingLeft) {
            var match = cell.style.paddingLeft.match(/calc\(0\.5rem \+ (\d+)px\)/);
            if (match) {
                return Math.round(Number(match[1]) / 20);
            }
        }

        return 0;
    }

    function setHierarchyRowExpansion(parentRow, expanded) {
        if (!parentRow) {
            return;
        }

        var toggle = parentRow.querySelector('.hierarchy-toggle');
        var parentIndentLevel = getHierarchyRowIndentLevel(parentRow);
        var nextRow = parentRow.nextElementSibling;

        while (nextRow) {
            if (!nextRow.querySelector('td')) {
                nextRow = nextRow.nextElementSibling;
                continue;
            }

            var nextIndentLevel = getHierarchyRowIndentLevel(nextRow);
            if (nextIndentLevel <= parentIndentLevel) {
                break;
            }

            nextRow.classList.toggle('hidden', !expanded);
            nextRow = nextRow.nextElementSibling;
        }

        if (toggle) {
            toggle.setAttribute('data-expanded', expanded ? '1' : '0');
            toggle.setAttribute('aria-label', expanded ? 'Collapse child items' : 'Expand child items');
            toggle.textContent = expanded ? '▲' : '▼';
        }
    }

    function getSectionBodyRows(sectionHeaderRow) {
        var rows = [];
        if (!sectionHeaderRow) {
            return rows;
        }

        var nextRow = sectionHeaderRow.nextElementSibling;
        while (nextRow) {
            if (nextRow.getAttribute('data-section-header') === '1') {
                break;
            }

            rows.push(nextRow);
            nextRow = nextRow.nextElementSibling;
        }

        return rows;
    }

    function setSectionRowExpansion(sectionHeaderRow, expanded) {
        if (!sectionHeaderRow) {
            return;
        }

        var toggle = sectionHeaderRow.querySelector('.section-toggle');
        var sectionRows = getSectionBodyRows(sectionHeaderRow);

        sectionRows.forEach(function(row) {
            row.classList.toggle('hidden', !expanded);
        });

        if (expanded) {
            sectionRows.forEach(function(row) {
                if (row.getAttribute('data-has-child-items') === '1' && row.getAttribute('data-indent-level') === '0') {
                    var hierarchyToggle = row.querySelector('.hierarchy-toggle');
                    var isExpanded = !hierarchyToggle || hierarchyToggle.getAttribute('data-expanded') !== '0';
                    setHierarchyRowExpansion(row, isExpanded);
                }
            });
        }

        if (toggle) {
            toggle.setAttribute('data-expanded', expanded ? '1' : '0');
            toggle.setAttribute('aria-label', expanded ? 'Collapse section items' : 'Expand section items');
            toggle.textContent = expanded ? '▲' : '▼';
        }
    }

    window.initializeHierarchyToggles = function initializeHierarchyToggles(container) {
        if (!container) {
            return;
        }

        if (container.id !== 'partGTableContainer') {
            container.querySelectorAll('tr[data-section-header="1"]').forEach(function(row) {
                setSectionRowExpansion(row, true);
            });
        }

        container.querySelectorAll('tr[data-has-child-items="1"][data-indent-level="0"]').forEach(function(row) {
            setHierarchyRowExpansion(row, true);
        });
    }

    function showAssessmentFeedback(message, type) {
        var banner = document.getElementById('assessmentActionFeedback');
        if (!banner) {
            banner = document.createElement('div');
            banner.id = 'assessmentActionFeedback';
            banner.className = 'fixed top-4 right-4 z-50 rounded px-4 py-2 text-sm shadow-lg';
            banner.style.display = 'none';
            document.body.appendChild(banner);
        }

        banner.textContent = message;
        banner.classList.remove('bg-green-600', 'bg-red-600', 'text-white');
        banner.classList.add(type === 'error' ? 'bg-red-600' : 'bg-green-600', 'text-white');
        banner.style.display = 'block';

        clearTimeout(window.assessmentActionFeedbackTimer);
        window.assessmentActionFeedbackTimer = setTimeout(function() {
            banner.style.display = 'none';
        }, 2500);
    }

    function getPartFRowRating(row) {
        if (!row) {
            return '';
        }

        var checked = row.querySelector('input[type="radio"][name^="partf-response-"]:checked');
        if (checked && checked.value) {
            return String(checked.value).trim();
        }

        return String(row.getAttribute('data-partf-rating') || '').trim();
    }

    function updatePartFSummaryScores() {
        var totalScoreField = document.getElementById('partFTotalScore');
        var averageScoreField = document.getElementById('partFAverageScore');
        var overallRatingField = document.getElementById('partFOverallRating');
        var overallRatingValueField = document.getElementById('partFOverallRatingValue');
        var overallRatingCard = document.getElementById('partFOverallRatingCard');
        var tableContainer = document.getElementById('partFTableContainer');
        var unsatisfactoryReasonWrapper = document.getElementById('partFUnsatisfactoryReasonWrapper');
        var unsatisfactoryReasonField = document.getElementById('partFUnsatisfactoryReason');
        var overallRatingCodeField = document.getElementById('partFOverallRatingCode');

        if (!tableContainer || !totalScoreField || !averageScoreField || !overallRatingField || !overallRatingCard) {
            return;
        }

        function parsePerformanceRating(rawRating) {
            var rating = (rawRating || '').trim().toUpperCase();
            if (rating === 'E' || rating === 'EXCEEDS' || rating === 'EXCEEDS EXPECTATIONS' || rating === 'EXCELLENT' || rating === '3') return 3;
            if (rating === 'M' || rating === 'MEETS' || rating === 'MEETS EXPECTATIONS' || rating === 'SATISFACTORY' || rating === 'S' || rating === '2') return 2;
            if (rating === 'B' || rating === 'BELOW' || rating === 'BELOW EXPECTATIONS' || rating === 'UNSATISFACTORY' || rating === 'U' || rating === '1') return 1;
            return null;
        }

        function syncOverallEvaluation(average) {
            var overallLabel = 'Not Rated';
            var cardClassList = overallRatingCard.classList;

            cardClassList.remove('border-teal-400', 'bg-teal-100', 'border-slate-400', 'bg-white', 'border-amber-300', 'bg-amber-100');

            if (average >= 2.51) {
                overallLabel = 'Exceeds Expectations';
                cardClassList.add('border-teal-400', 'bg-teal-100');
            } else if (average >= 1.75) {
                overallLabel = 'Meets Expectations';
                cardClassList.add('border-slate-400', 'bg-white');
            } else if (average > 0) {
                overallLabel = 'Below Expectations';
                cardClassList.add('border-amber-300', 'bg-amber-100');
            } else {
                cardClassList.add('border-slate-400', 'bg-white');
            }

            overallRatingField.value = overallLabel;

            if (overallRatingValueField) {
                overallRatingValueField.value = overallLabel;
            }

            if (overallRatingCodeField) {
                var overallCode = '';
                if (average >= 2.51) {
                    overallCode = 'E';
                } else if (average >= 1.75) {
                    overallCode = 'M';
                } else if (average > 0) {
                    overallCode = 'B';
                }
                overallRatingCodeField.textContent = overallCode;
            }

            if (unsatisfactoryReasonWrapper && unsatisfactoryReasonField) {
                var showUnsatisfactoryReason = overallLabel === 'Below Expectations' || overallLabel === 'Unsatisfactory';
                unsatisfactoryReasonWrapper.classList.toggle('hidden', !showUnsatisfactoryReason);
                unsatisfactoryReasonField.disabled = !showUnsatisfactoryReason;
                unsatisfactoryReasonField.required = showUnsatisfactoryReason;

                if (!showUnsatisfactoryReason) {
                    unsatisfactoryReasonField.value = '';
                }
            }
        }

        var hasCheckedRating = tableContainer.querySelector('tr[data-partf-scorable="1"] input[type="radio"][name^="partf-response-"]:checked');
        if (!hasCheckedRating && totalScoreField.value !== '' && averageScoreField.value !== '') {
            if (overallRatingValueField && overallRatingValueField.value === '' && overallRatingField.value !== '') {
                overallRatingValueField.value = overallRatingField.value;
            }
            syncOverallEvaluation(parseFloat(averageScoreField.value) || 0);
            return;
        }

        var total = 0;
        var count = 0;

        var radioRows = tableContainer.querySelectorAll('tr[data-partf-scorable="1"]');
        if (radioRows.length) {
            radioRows.forEach(function(row) {
                if (row.getAttribute('data-summary-exclude') === '1') {
                    return;
                }

                var code = getPartFRowRating(row);
                row.setAttribute('data-partf-rating', code);
                var numericRating = parsePerformanceRating(code);
                if (numericRating === null) {
                    return;
                }

                total += numericRating;
                count += 1;
            });
        } else {
            tableContainer.querySelectorAll('tbody tr').forEach(function(row) {
                var cells = row.querySelectorAll('td');
                if (cells.length !== 5 && cells.length !== 4) {
                    return;
                }

                if (row.getAttribute('data-summary-exclude') === '1') {
                    return;
                }

                var numericRating = parsePerformanceRating(cells[2].textContent);
                if (numericRating === null) {
                    return;
                }

                total += numericRating;
                count += 1;
            });
        }

        totalScoreField.value = String(total);
        var average = count ? (total / count) : 0;
        averageScoreField.value = average.toFixed(2);
        syncOverallEvaluation(average);
    }

    window.updatePartFSummaryScores = updatePartFSummaryScores;

    function bindPartFRatingSummaryListeners() {
        var partFRoot = document.getElementById('partF');
        if (!partFRoot || partFRoot.getAttribute('data-partf-summary-bound') === '1') {
            return;
        }

        partFRoot.setAttribute('data-partf-summary-bound', '1');
        partFRoot.addEventListener('change', function(e) {
            var radio = e.target;
            if (!radio || radio.type !== 'radio' || !radio.name || radio.name.indexOf('partf-response-') !== 0) {
                return;
            }

            var row = radio.closest('tr[data-partf-scorable="1"]');
            if (row) {
                row.setAttribute('data-partf-rating', radio.value || '');
            }

            updatePartFSummaryScores();
        });
    }

    window.bindPartFRatingSummaryListeners = bindPartFRatingSummaryListeners;

    function getPartFPerformanceAreasComponent() {
        var container = document.getElementById('partFTableContainer');
        if (!container || typeof Livewire === 'undefined' || typeof Livewire.find !== 'function') {
            return null;
        }

        var root = container.closest('[wire\\:id]');
        if (!root) {
            return null;
        }

        var wireId = root.getAttribute('wire:id');
        return wireId ? Livewire.find(wireId) : null;
    }

    function syncPartFRatingsBeforeSave() {
        var component = getPartFPerformanceAreasComponent();
        if (!component || typeof component.call !== 'function') {
            return Promise.resolve();
        }

        return component.call('syncAllRatings').catch(function() {
            return null;
        });
    }

    window.syncPartFRatingsBeforeSave = syncPartFRatingsBeforeSave;

    function registerPartFSummaryUpdatedListener() {
        if (typeof Livewire === 'undefined' || typeof Livewire.on !== 'function') {
            return;
        }

        Livewire.on('partf-summary-updated', function() {
            updatePartFSummaryScores();
        });
    }

    // Binds click events for PART F checklist action links (Verify/View)
    function bindChecklistLinksF() {
        var periodSelect = document.querySelector('.js-assessment-period-select');
        if (periodSelect) {
            window.selectedAssessmentPeriodId = periodSelect.value;
            var apidField = document.getElementById('verifyAssessmentPeriodIdF');
            if (apidField) {
                apidField.value = periodSelect.value;
            }
        }
    }

     // Opens the PART F modal and populates its fields for the selected item/employee
    function openVerifyModalF(itemKey, empId, viewOnly = false, effDate = null, forceNew = false) {
        var empIdField = document.getElementById('verifyEmpIdF');
        var itemKeyField = document.getElementById('verifyItemKeyF');
        var itemLabelField = document.getElementById('verifyItemLabelF');
        var sourceItemIdField = document.getElementById('verifySourceItemIdF');
        var ratingField = document.getElementById('verifyRatingF');
        var assessmentDateField = document.getElementById('verifyAssessmentDateF');
        var commentsField = document.getElementById('verifyCommentsF');
        var commentsError = document.getElementById('commentsFError');
        var assessedByField = document.getElementById('verifyAssessedByF');
        var assessedByIdField = document.getElementById('verifyAssessedByIdF');
        var apidField = document.getElementById('verifyAssessmentPeriodIdF');
        var saveBtn = document.getElementById('verifySaveBtnF');

        function syncUnsatisfactoryCommentRequirement() {
            // No longer require comments for Unsatisfactory (No) rating for any item
            if (commentsField) {
                commentsField.required = false;
                commentsField.placeholder = '';
            }

            if (commentsError) {
                commentsError.classList.add('hidden');
            }
        }
        // Find the row for this itemKey/empId to get any existing data
        var link = itemKey ? Array.from(document.querySelectorAll('.verify-link, .view-link')).find(
            l => l.getAttribute('data-item-key') === itemKey && l.getAttribute('data-emp-id') == empId
        ) : null;
        var row = link ? link.closest('tr') : null;
        // Set hidden fields for employee and item
        empIdField.value = empId;
        itemKeyField.value = itemKey || '';
        if (itemLabelField) itemLabelField.value = link ? (link.getAttribute('data-item-label') || '') : '';
        if (sourceItemIdField) sourceItemIdField.value = link ? (link.getAttribute('data-source-item-id') || '') : '';
        // Try to get rating from data attribute, or from the Blade-rendered table cell
        var ratingValue = '';
        if (link && link.getAttribute('data-rating')) {
            ratingValue = link.getAttribute('data-rating');
        } else if (row && row.children[2]) {
            // Try to map text to value if user refreshed page
            var cellText = row.children[2].textContent.trim().toLowerCase();
            if (cellText === 'excellent' || cellText === 'exceeds' || cellText === 'e') ratingValue = 'E';
            else if (cellText === 'satisfactory' || cellText === 'meets' || cellText === 'm' || cellText === 's') ratingValue = 'M';
            else if (cellText === 'unsatisfactory' || cellText === 'below' || cellText === 'b' || cellText === 'u') ratingValue = 'B';
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
        commentsField.value = link && link.getAttribute('data-comments') ? link.getAttribute('data-comments') : '';
        // Set assessed by fields to current user
        if (window.currentUserName) {
            assessedByField.value = window.currentUserName;
        }
        if (window.currentUserId) {
            assessedByIdField.value = window.currentUserId;
        }
        ratingField.disabled = !!viewOnly;
        assessmentDateField.readOnly = !!viewOnly;
        commentsField.readOnly = !!viewOnly;
        if (ratingField) {
            ratingField.onchange = syncUnsatisfactoryCommentRequirement;
        }
        if (commentsField) {
            commentsField.oninput = syncUnsatisfactoryCommentRequirement;
        }
        syncUnsatisfactoryCommentRequirement();
        if (saveBtn) {
            saveBtn.classList.toggle('hidden', !!viewOnly);
        }

        var historyList = document.getElementById('assessmentHistoryListF');
        var historyEmpty = document.getElementById('assessmentHistoryEmptyF');
        if (historyList && historyEmpty) {
            historyList.innerHTML = '';
            var historyEntries = (window.assessmentItemHistories && itemKey && window.assessmentItemHistories[itemKey])
                ? window.assessmentItemHistories[itemKey]
                : [];
            if (!historyEntries.length) {
                historyEmpty.classList.remove('hidden');
            } else {
                historyEmpty.classList.add('hidden');
                historyEntries.forEach(function(entry) {
                    var li = document.createElement('li');
                    var status = entry.revoked_at ? 'revoked' : 'active';
                    var assessedByName = entry.verified_by_name || (window.users && entry.verified_by ? window.users[entry.verified_by] : '') || entry.verified_by || '';
                    var periodLabel = entry.period_label ? entry.period_label : 'Current period';
                    var selectedFlag = entry.is_selected_period ? ' | selected period' : '';
                    li.className = 'border rounded px-3 py-2 bg-gray-50';
                    li.textContent = periodLabel + ' | ' + (entry.verified_dt || 'N/A') + ' | ' + (entry.rating || '') + ' | ' + assessedByName + (entry.comments ? ' | ' + entry.comments : '') + ' | ' + status + selectedFlag;
                    historyList.appendChild(li);
                });
            }
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
    function updateAssessmentRow(itemKey, empId, item, history) {
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
        if (row.children.length < 5) {
            console.error('updatePartFRow: Row does not have enough columns:', row, row.children);
            return;
        }
        var ratingIndex = row.children.length === 6 ? 2 : 1;
        var assessedDateIndex = row.children.length === 6 ? 3 : 2;
        var assessedByIndex = row.children.length === 6 ? 4 : 3;
        var actionIndex = row.children.length === 6 ? 5 : 4;
        var itemLabel = '';
        var sourceItemId = '';
        var existingLink = row.querySelector('[data-item-key]');
        if (existingLink) {
            itemLabel = existingLink.getAttribute('data-item-label') || '';
            sourceItemId = existingLink.getAttribute('data-source-item-id') || '';
        }
        // Update Rating as word
        var isCompetencyRow = row.children.length === 5;
        var ratingText = '';
        if (item && item.rating) {
            if (isCompetencyRow) {
                ratingText = item.rating;
            } else if (item.rating === 'E') ratingText = 'Exceeds Expectations';
            else if (item.rating === 'M') ratingText = 'Meets Expectations';
            else if (item.rating === 'B') ratingText = 'Below Expectations';
        }
        row.setAttribute('data-current-rating', item && item.rating ? item.rating : '');
        if (row.hasAttribute('data-current-comments')) {
            row.setAttribute('data-current-comments', item && item.comments ? item.comments : '');
        }
        row.children[ratingIndex].textContent = ratingText;
        // Update Assessed Date and Assessed By
        if (item && item.rating) {
            row.children[assessedDateIndex].textContent = item.verified_dt ? item.verified_dt : '';
            var assessedByCell = row.children[assessedByIndex];
            if (!assessedByCell) {
                console.error('updatePartFRow: assessedByCell missing', row, row.children);
                return;
            }
            while (assessedByCell.firstChild) { assessedByCell.removeChild(assessedByCell.firstChild); }
            var assessedByName = '';
            if (item.verified_by && window.users && window.users[item.verified_by]) {
                assessedByName = window.users[item.verified_by];
            } else if (window.currentUserName && window.currentUserId == item.verified_by) {
                assessedByName = window.currentUserName;
            } else {
                assessedByName = item.verified_by || '';
            }
            assessedByCell.appendChild(document.createTextNode(assessedByName));
        } else {
            // Clear Assessed Date and Assessed By columns if revoked
            row.children[assessedDateIndex].textContent = '';
            var assessedByCell = row.children[assessedByIndex];
            if (assessedByCell) {
                while (assessedByCell.firstChild) { assessedByCell.removeChild(assessedByCell.firstChild); }
            }
        }
        // Update Actions: show Revoke & View if assessed, else Assess
        var actionCell = row.children[actionIndex];
        if (!actionCell) {
            console.error('updatePartFRow: actionCell missing', row, row.children);
            return;
        }
        var isAssessableItem = row.getAttribute('data-assessable-item') !== '0';
        while (actionCell.firstChild) { actionCell.removeChild(actionCell.firstChild); }
        if (item && item.rating) {
            // Show Revoke | View
            var revokeLink = document.createElement('a');
            revokeLink.href = '#';
            revokeLink.className = 'text-red-600 underline mr-1 unverify-link cursor-pointer text-xs';
            revokeLink.setAttribute('data-item-key', itemKey);
            revokeLink.setAttribute('data-emp-id', empId);
            revokeLink.setAttribute('data-item-label', itemLabel);
            revokeLink.setAttribute('data-source-item-id', sourceItemId);
            revokeLink.textContent = 'Revoke';
            revokeLink.title = 'Revoke Assessment';
            actionCell.appendChild(revokeLink);
            var sep = document.createElement('span');
            sep.textContent = ' | ';
            actionCell.appendChild(sep);
            var viewLink = document.createElement('a');
            viewLink.href = '#';
            viewLink.className = 'text-teal-600 underline ml-1 view-link cursor-pointer text-xs';
            viewLink.setAttribute('data-item-key', itemKey);
            viewLink.setAttribute('data-emp-id', empId);
            viewLink.setAttribute('data-item-label', itemLabel);
            viewLink.setAttribute('data-source-item-id', sourceItemId);
            viewLink.setAttribute('data-rating', item.rating || '');
            viewLink.setAttribute('data-assessment-date', item.verified_dt || '');
            viewLink.setAttribute('data-comments', item.comments || '');
            viewLink.setAttribute('data-assessed-by-id', item.verified_by || '');
            viewLink.textContent = 'View';
            viewLink.title = 'View Assessment Details';
            actionCell.appendChild(viewLink);
        } else if (isAssessableItem) {
            // Show Assess
            var assessLink = document.createElement('a');
            assessLink.href = '#';
            assessLink.className = 'text-teal-600 underline verify-link cursor-pointer';
            assessLink.setAttribute('data-item-key', itemKey);
            assessLink.setAttribute('data-emp-id', empId);
            assessLink.setAttribute('data-item-label', itemLabel);
            assessLink.setAttribute('data-source-item-id', sourceItemId);
            assessLink.textContent = 'Assess';
            assessLink.title = 'Assess Item';
            actionCell.appendChild(assessLink);
        }
        if (window.assessmentItemHistories) {
            window.assessmentItemHistories[itemKey] = history || [];
        }
        if (isCompetencyRow && typeof window.updatePartGSummaryScores === 'function') {
            if (typeof window.syncPartGExcludedRows === 'function') {
                window.syncPartGExcludedRows();
            }
            if (typeof window.syncPartGMedicationSelections === 'function') {
                window.syncPartGMedicationSelections();
            }
            window.updatePartGSummaryScores();
        } else {
            updatePartFSummaryScores();
        }
        // Re-bind PART F links after row update
        bindChecklistLinksF();
    }

    // Use event delegation for 'Revoke' links in PART F and PART G
    [document.querySelector('#partFTableContainer'), document.querySelector('#partGTableContainer')].filter(Boolean).forEach(function(container) {
    container.addEventListener('click', function(e) {
        var target = e.target;
        var sectionToggleButton = target.closest('.section-toggle');
        if (sectionToggleButton) {
            if (container.id === 'partGTableContainer') {
                return;
            }

            e.preventDefault();
            var sectionHeaderRow = sectionToggleButton.closest('tr');
            var sectionExpanded = sectionToggleButton.getAttribute('data-expanded') !== '1';
            setSectionRowExpansion(sectionHeaderRow, sectionExpanded);
            return;
        }
        var toggleButton = target.closest('.hierarchy-toggle');
        if (toggleButton) {
            e.preventDefault();
            e.stopPropagation();
            var parentRow = toggleButton.closest('tr');
            var expanded = toggleButton.getAttribute('data-expanded') !== '1';
            setHierarchyRowExpansion(parentRow, expanded);
            return;
        }
        if (target.classList.contains('unverify-link') && target.hasAttribute('data-item-key')) {
            e.preventDefault();
            var itemKey = target.getAttribute('data-item-key');
            var empId = target.getAttribute('data-emp-id');
            var assessmentPeriodId = (window.selectedAssessmentPeriodId || '');
            if (!confirm('Are you sure you want to revoke this assessment?')) return;
            if (bpDenySelfAssessmentAction()) {
                return;
            }
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
                if (data.success && data.data) {
                    updateAssessmentRow(itemKey, empId, data.data.latest, data.data.history || []);
                    showAssessmentFeedback('Assessment revoked successfully.');
                } else {
                    var message = 'Revoke failed.2';
                    if (data && data.message) {
                        message += '\n' + data.message;
                    }
                    alert(message);
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
    });

    // Use event delegation for .verify-link and .view-link in PART F and PART G
    [document.querySelector('#partFTableContainer'), document.querySelector('#partGTableContainer')].filter(Boolean).forEach(function(container) {
    container.addEventListener('click', function(e) {
        var target = e.target;
        if ((target.classList.contains('verify-link') || target.classList.contains('view-link')) && target.hasAttribute('data-item-key')) {
            e.preventDefault();
            if (target.classList.contains('verify-link') && target.getAttribute('aria-disabled') === 'true') {
                return;
            }
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
    });

     // PART F: Handles the form submission for performance assessment verification (modal)
    document.getElementById('verifyFormF').onsubmit = function(e) {
        e.preventDefault(); // Prevent default form submission (page reload)

        if (bpDenySelfAssessmentAction()) {
            return;
        }

        // Get the employee ID, item key, and assessment_period_id from hidden fields
        var empId = document.getElementById('verifyEmpIdF').value;
        var itemKey = document.getElementById('verifyItemKeyF').value;
        var assessmentPeriodId = document.getElementById('verifyAssessmentPeriodIdF').value;

        // Get the selected rating and the error display element
        var rating = document.getElementById('verifyRatingF').value;
        var ratingError = document.getElementById('ratingFError');
        var commentsField = document.getElementById('verifyCommentsF');
        var commentsError = document.getElementById('commentsFError');

        // Validate that a rating is selected; if not, show error and focus the field
        if (!rating) {
            if (ratingError) ratingError.classList.remove('hidden');
            document.getElementById('verifyRatingF').focus();
            return;
        } else {
            if (ratingError) ratingError.classList.add('hidden');
        }

        if (rating === 'B' && (!commentsField || !commentsField.value.trim())) {
            if (commentsError) commentsError.classList.remove('hidden');
            if (commentsField) commentsField.focus();
            return;
        } else if (commentsError) {
            commentsError.classList.add('hidden');
        }

        // Get other form values: assessment date, comments, and assessed by (user ID)
        var assessmentDate = document.getElementById('verifyAssessmentDateF').value;
        var comments = commentsField ? commentsField.value : '';
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
            item_label: document.getElementById('verifyItemLabelF').value,
            source_item_id: document.getElementById('verifySourceItemIdF').value || null,
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
            if (data.success && data.data) {
                closeVerifyModalF();
                updateAssessmentRow(itemKey, empId, data.data.latest, data.data.history || []);
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

    function registerPartFLivewireHooks() {
        if (window.__partFLivewireHooksRegistered) {
            return;
        }

        if (typeof Livewire === 'undefined' || typeof Livewire.hook !== 'function') {
            return;
        }

        window.__partFLivewireHooksRegistered = true;
        registerPartFSummaryUpdatedListener();

        Livewire.hook('morph.updated', function (payload) {
            var el = payload.el;
            if (!el || typeof el.closest !== 'function') {
                return;
            }

            if (el.id === 'partFTableContainer' || el.closest('#partFTableContainer')) {
                window.initializeHierarchyToggles(document.getElementById('partFTableContainer'));
                updatePartFSummaryScores();
            }

            if (el.id === 'partG' || el.closest('#partG')) {
                window.initializeHierarchyToggles(document.getElementById('partG'));
            }
        });
    }

    document.addEventListener('livewire:init', registerPartFLivewireHooks);

    if (window.Livewire) {
        registerPartFLivewireHooks();
    }

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
        bindPartFRatingSummaryListeners();
        initializeHierarchyToggles(document.querySelector('#partFTableContainer'));
        initializeHierarchyToggles(document.querySelector('#partGTableContainer'));
        updatePartFSummaryScores();

        // PART F: Section comment save logic
        document.querySelectorAll('.section-comment-save-btn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                var docTypeId = btn.getAttribute('data-doc-type-id');
                var empId = btn.getAttribute('data-emp-id');
                var assessmentPeriodId = btn.getAttribute('data-assessment-period-id');
                var sectionWrap = btn.closest('section') || btn.closest('.rounded-md.border.border-slate-400');
                var textarea = sectionWrap
                    ? sectionWrap.querySelector('.section-comment-textarea[data-doc-type-id="' + docTypeId + '"]')
                    : document.querySelector('.section-comment-textarea[data-doc-type-id="' + docTypeId + '"]');
                var statusSpan = btn.parentElement ? btn.parentElement.querySelector('.section-comment-status') : null;
                if (!statusSpan) {
                    var sectionContainer = btn.closest('.rounded-md');
                    statusSpan = sectionContainer ? sectionContainer.querySelector('.section-comment-status') : null;
                }
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
                syncPartFRatingsBeforeSave().then(function() {
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
                            var statusText = 'Saved!';
                            if (data.action === 'deleted') {
                                statusText = 'Cleared';
                            } else if (data.action === 'noop') {
                                statusText = 'OK';
                            }
                            statusSpan.textContent = statusText;
                            statusSpan.style.color = 'green';
                        }
                        var feedbackMsg = data.message || 'Section comment saved successfully.';
                        showAssessmentFeedback(feedbackMsg);
                        setTimeout(function() {
                                if (statusSpan) {
                                    statusSpan.textContent = '';
                                }
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
    });


</script>