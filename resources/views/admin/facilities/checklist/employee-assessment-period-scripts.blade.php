<script>
    (function() {
        function getSubjectSummary(root) {
            var managerId = root.getAttribute('data-manager-id') || 'default';
            return document.querySelector('.assessment-subject-summary[data-manager-id="' + managerId + '"]');
        }

        function getCsrfToken() {
            var tokenMeta = document.querySelector('meta[name="csrf-token"]');
            return tokenMeta ? tokenMeta.getAttribute('content') : '';
        }

        function getManagerElements(root) {
            var summaryRoot = getSubjectSummary(root);
            return {
                form: root.querySelector('.js-assessment-period-form'),
                periodSelect: root.querySelector('.js-assessment-period-select'),
                yearSelect: root.querySelector('.js-assessment-year-select'),
                yearHidden: root.querySelector('.js-assessment-year-hidden'),
                facilityId: root.querySelector('.js-assessment-facility-id') || (summaryRoot ? summaryRoot.querySelector('.js-assessment-facility-id') : null),
                facilityName: root.querySelector('.js-assessment-facility-name') || (summaryRoot ? summaryRoot.querySelector('.js-assessment-facility-name') : null)
            };
        }

        function getAssessmentStateMap(kind) {
            return kind === 'performance'
                ? (window.performanceAssessmentStatuses || {})
                : (window.competencyAssessmentStatuses || {});
        }

        function getAssessmentState(root, periodId) {
            var contextLabel = String(root.getAttribute('data-context-label') || '').toLowerCase();
            var states = getAssessmentStateMap(contextLabel.indexOf('performance') !== -1 ? 'performance' : 'competency');
            return states[String(periodId)] || states[Number(periodId)] || null;
        }

        function getCurrentCalendarYear() {
            return new Date().getFullYear();
        }

        function getLoadableYearRange() {
            return window.assessmentPeriodLoadableYearRange || {
                min: getCurrentCalendarYear() - (window.assessmentPeriodLoadYearWindow || 2),
                max: getCurrentCalendarYear() + (window.assessmentPeriodLoadYearWindow || 2),
                current: getCurrentCalendarYear()
            };
        }

        function resolvePeriodYear(periodYear, dateFrom, dateTo) {
            if (periodYear) {
                return parseInt(periodYear, 10);
            }
            if (dateTo) {
                return parseInt(String(dateTo).slice(0, 4), 10);
            }
            if (dateFrom) {
                return parseInt(String(dateFrom).slice(0, 4), 10);
            }
            return null;
        }

        function isPeriodYearLoadable(periodYear) {
            var year = resolvePeriodYear(periodYear);
            if (!year || isNaN(year)) {
                return false;
            }
            var range = getLoadableYearRange();
            return year >= range.min && year <= range.max;
        }

        function periodLoadBlockedMessage(periodYear) {
            var range = getLoadableYearRange();
            var year = resolvePeriodYear(periodYear);
            return 'Assessment periods outside ' + range.min + '\u2013' + range.max
                + ' cannot be loaded.'
                + (year ? ' This period is for year ' + year + '.' : '');
        }

        function assertPeriodLoadable(periodYear, dateFrom, dateTo) {
            var year = resolvePeriodYear(periodYear, dateFrom, dateTo);
            if (isPeriodYearLoadable(year)) {
                return true;
            }
            alert(periodLoadBlockedMessage(year));
            return false;
        }

        function navigateToAssessmentPeriod(periodId, periodYear, dateFrom, dateTo) {
            if (!assertPeriodLoadable(periodYear, dateFrom, dateTo)) {
                return;
            }
            var url = new URL(window.location.href);
            url.searchParams.set('assessment_period_id', periodId);
            if (periodYear) {
                url.searchParams.set('assessment_year', periodYear);
            }
            window.location.href = url.toString();
        }

        function syncSelectedAssessmentPeriod(periodId) {
            window.selectedAssessmentPeriodId = periodId || '';
            var verifyPeriodField = document.getElementById('verifyAssessmentPeriodIdF');
            if (verifyPeriodField) {
                verifyPeriodField.value = window.selectedAssessmentPeriodId;
            }
        }

        var activePeriodModalRoot = null;
        var periodModalPayload = null;

        function todayDateString() {
            var today = new Date();
            var yyyy = today.getFullYear();
            var mm = String(today.getMonth() + 1).padStart(2, '0');
            var dd = String(today.getDate()).padStart(2, '0');
            return yyyy + '-' + mm + '-' + dd;
        }

        function getReviewDateFromRoot(root) {
            if (!root) {
                return todayDateString();
            }
            var managerId = root.getAttribute('data-manager-id') || 'default';
            var input = document.getElementById('assessmentReviewDate-' + managerId);
            return (input && input.value) ? input.value : todayDateString();
        }

        function getPeriodModalReviewDate() {
            var input = document.getElementById('periodModalReviewDate');
            return (input && input.value) ? input.value : todayDateString();
        }

        function formatDateOnly(value) {
            if (!value) {
                return '';
            }
            return String(value).slice(0, 10);
        }

        function formatPeriodRange(from, to) {
            return formatDateOnly(from) + ' to ' + formatDateOnly(to);
        }

        function getAssessmentPeriodsList() {
            var periods = window.assessmentPeriods;
            if (Array.isArray(periods)) {
                return periods;
            }
            if (periods && typeof periods === 'object') {
                return Object.values(periods);
            }
            return [];
        }

        function fetchPeriodModalData(reviewDate) {
            var employeeId = window.currentEmployeeRecordId;
            if (!employeeId) {
                return Promise.reject(new Error('Employee record is missing. Save the employee profile first.'));
            }
            var url = '/admin/employees/' + encodeURIComponent(employeeId) + '/assessment-periods/modal-data';
            if (reviewDate) {
                url += '?review_date=' + encodeURIComponent(reviewDate);
            }
            return fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } })
                .then(function(resp) { return resp.json(); })
                .then(function(data) {
                    if (!data.success) {
                        throw new Error(data.message || 'Failed to load period options.');
                    }
                    return data.data;
                });
        }

        function renderPeriodModalData(data) {
            periodModalPayload = data;
            var anchorInfo = document.getElementById('periodModalAnchorInfo');
            var recommendedSection = document.getElementById('periodModalRecommendedSection');
            var recommendedRange = document.getElementById('periodModalRecommendedRange');
            var loadRecommendedBtn = document.getElementById('periodModalLoadRecommendedBtn');
            var createRecommendedBtn = document.getElementById('periodModalCreateRecommendedBtn');
            var recommendedMissing = document.getElementById('periodModalRecommendedMissing');
            var containingNote = document.getElementById('periodModalContainingNote');
            var historyEmpty = document.getElementById('periodModalHistoryEmpty');
            var historyWrap = document.getElementById('periodModalHistoryWrap');
            var historyBody = document.getElementById('periodModalHistoryBody');
            var errorBox = document.getElementById('periodModalError');

            if (errorBox) {
                errorBox.classList.add('hidden');
                errorBox.textContent = '';
            }

            if (anchorInfo) {
                if (data.has_anchor) {
                    anchorInfo.textContent = 'Anchor: ' + (data.anchor_source || 'Hire date') + ' — ' + (data.anchor_label || '');
                } else {
                    anchorInfo.textContent = 'Set Original Hire Date (or Rehire Date when Action is Rehire) on the Personal tab.';
                }
            }

            var recommended = data.recommended || null;
            if (recommendedSection) {
                if (recommended) {
                    recommendedSection.classList.remove('hidden');
                    if (recommendedRange) {
                        recommendedRange.textContent = formatPeriodRange(recommended.date_from, recommended.date_to)
                            + ' (Year ' + (recommended.period_year || '') + ')';
                    }
                    var recommendedLoadable = recommended.can_load !== false && isPeriodYearLoadable(recommended.period_year);
                    if (loadRecommendedBtn) {
                        loadRecommendedBtn.classList.toggle('hidden', !recommended.exists || !recommendedLoadable);
                        loadRecommendedBtn.disabled = !recommendedLoadable;
                    }
                    if (createRecommendedBtn) {
                        createRecommendedBtn.classList.toggle('hidden', recommended.exists || !recommendedLoadable);
                    }
                    if (recommendedMissing) {
                        recommendedMissing.classList.add('hidden');
                    }
                } else {
                    recommendedSection.classList.remove('hidden');
                    if (recommendedRange) {
                        recommendedRange.textContent = '';
                    }
                    if (loadRecommendedBtn) loadRecommendedBtn.classList.add('hidden');
                    if (createRecommendedBtn) createRecommendedBtn.classList.add('hidden');
                    if (recommendedMissing) {
                        recommendedMissing.classList.remove('hidden');
                        recommendedMissing.textContent = data.has_anchor
                            ? 'No prior completed employment year is available yet for this review date. Use a custom range below or choose another period from history.'
                            : 'Add hire or rehire dates on the Personal tab before the system can suggest an annual period.';
                    }
                }
            }

            if (containingNote) {
                var containing = data.containing || null;
                if (containing && recommended && containing.date_from !== recommended.date_from) {
                    containingNote.classList.remove('hidden');
                    containingNote.textContent = 'Note: The employment year containing this review date is '
                        + formatPeriodRange(containing.date_from, containing.date_to)
                        + ' (in progress). Assessments use the prior completed year above.';
                } else {
                    containingNote.classList.add('hidden');
                    containingNote.textContent = '';
                }
            }

            var history = Array.isArray(data.history) ? data.history : [];
            if (historyEmpty && historyWrap && historyBody) {
                if (!history.length) {
                    historyEmpty.classList.remove('hidden');
                    historyWrap.classList.add('hidden');
                    historyBody.innerHTML = '';
                } else {
                    historyEmpty.classList.add('hidden');
                    historyWrap.classList.remove('hidden');
                    historyBody.innerHTML = history.map(function(row) {
                        var badge = row.is_recommended
                            ? ' <span class="ml-1 rounded bg-teal-100 px-1 text-teal-800">Recommended</span>'
                            : '';
                        return '<tr class="' + (row.is_recommended ? 'bg-teal-50' : '') + '">' +
                            '<td class="border-b border-slate-200 px-3 py-2">' + formatPeriodRange(row.date_from, row.date_to) + badge + '</td>' +
                            '<td class="border-b border-slate-200 px-3 py-2">' + (row.period_year || '') + '</td>' +
                            '<td class="border-b border-slate-200 px-3 py-2">' + (row.review_type_label || '') + '</td>' +
                            '<td class="border-b border-slate-200 px-3 py-2 text-center">' + (row.performance_status || '') + '</td>' +
                            '<td class="border-b border-slate-200 px-3 py-2 text-center">' + (row.competency_status || '') + '</td>' +
                            '<td class="border-b border-slate-200 px-3 py-2 text-center">' +
                                renderPeriodHistoryActions(row) +
                            '</td>' +
                        '</tr>';
                    }).join('');
                }
            }
        }

        function setPeriodModalMode(mode) {
            var selectMode = document.getElementById('periodModalSelectMode');
            var editMode = document.getElementById('periodModalEditMode');
            if (selectMode) {
                selectMode.classList.toggle('hidden', mode !== 'select');
            }
            if (editMode) {
                editMode.classList.toggle('hidden', mode !== 'edit');
            }
        }

        function refreshPeriodModalData() {
            var loading = document.getElementById('periodModalLoading');
            var errorBox = document.getElementById('periodModalError');
            if (loading) loading.classList.remove('hidden');
            if (errorBox) errorBox.classList.add('hidden');

            return fetchPeriodModalData(getPeriodModalReviewDate())
                .then(function(data) {
                    renderPeriodModalData(data);
                    var customFrom = document.getElementById('newPeriodDateFromInput');
                    var customTo = document.getElementById('newPeriodDateToInput');
                    if (data.recommended && customFrom && customTo && !customFrom.value && !customTo.value) {
                        customFrom.value = formatDateOnly(data.recommended.date_from);
                        customTo.value = formatDateOnly(data.recommended.date_to);
                    }
                })
                .catch(function(err) {
                    if (errorBox) {
                        errorBox.textContent = err && err.message ? err.message : 'Failed to load period options.';
                        errorBox.classList.remove('hidden');
                    }
                })
                .finally(function() {
                    if (loading) loading.classList.add('hidden');
                });
        }

        function confirmThenLoadPeriod(periodId, periodYear, root, performanceLabel, competencyLabel, dateFrom, dateTo) {
            if (!assertPeriodLoadable(periodYear, dateFrom, dateTo)) {
                return;
            }

            var performanceState = getAssessmentStateMap('performance')[String(periodId)] || getAssessmentStateMap('performance')[Number(periodId)];
            var competencyState = getAssessmentStateMap('competency')[String(periodId)] || getAssessmentStateMap('competency')[Number(periodId)];
            var contextLabel = root ? String(root.getAttribute('data-context-label') || 'assessment').toLowerCase() : 'assessment';
            var state = performanceState || competencyState;

            if (!state && performanceLabel && performanceLabel !== 'No Saved Submission') {
                state = { status_label: performanceLabel, is_completed: performanceLabel === 'Completed' };
            }

            if (state) {
                var warningMessage = state.is_completed
                    ? 'A ' + contextLabel + ' already exists for this employee in the selected period and its status is Completed. It will load in read-only mode. Continue?'
                    : 'A ' + contextLabel + ' already exists for this employee in the selected period with status "' + state.status_label + '". Continue loading it?';
                if (!window.confirm(warningMessage)) {
                    return;
                }
            }

            window.closeNewPeriodModal();
            navigateToAssessmentPeriod(periodId, periodYear, dateFrom, dateTo);
        }

        function renderPeriodLoadAction(periodId, periodYear, dateFrom, dateTo, canLoad) {
            if (canLoad === false || !isPeriodYearLoadable(periodYear)) {
                return '';
            }
            return '<button type="button" class="period-modal-load-btn load-assessment-period-btn rounded bg-teal-600 px-2 py-1 text-xs text-white" ' +
                'data-period-id="' + periodId + '" data-period-year="' + (periodYear || '') + '" ' +
                'data-date-from="' + (dateFrom || '') + '" data-date-to="' + (dateTo || '') + '">Load</button>';
        }

        function renderPeriodDeleteAction(periodId, canDelete) {
            if (!canDelete) {
                return '';
            }
            return '<button type="button" class="period-modal-delete-btn rounded bg-red-600 px-2 py-1 text-xs text-white" ' +
                'data-period-id="' + periodId + '">Delete</button>';
        }

        function renderPeriodHistoryActions(row) {
            var actions = [];
            var loadBtn = renderPeriodLoadAction(row.id, row.period_year, row.date_from, row.date_to, row.can_load);
            if (loadBtn) {
                actions.push(loadBtn);
            }
            var deleteBtn = renderPeriodDeleteAction(row.id, row.can_delete);
            if (deleteBtn) {
                actions.push(deleteBtn);
            }
            if (!actions.length) {
                if (row.can_load === false && !row.can_delete) {
                    return '<span class="text-xs text-slate-500">Has submission</span>';
                }
                return '<span class="text-xs text-slate-500">—</span>';
            }
            return '<div class="flex flex-wrap items-center justify-center gap-1">' + actions.join('') + '</div>';
        }

        function confirmAndDeleteAssessmentPeriod(periodId) {
            if (!window.confirm('Delete this assessment period? This action cannot be undone.')) {
                return;
            }
            var token = getCsrfToken();
            if (!token) {
                alert('CSRF token missing.');
                return;
            }
            requestDeleteAssessmentPeriod(periodId, token)
                .then(function(data) {
                    if (data.success) {
                        window.closeNewPeriodModal();
                        alert('Assessment period deleted.');
                        window.location.reload();
                        return;
                    }
                    if (data.blocked) {
                        showAssessmentPeriodDeleteBlockedMessage(data);
                        return;
                    }
                    alert(data.message || 'Delete failed.');
                })
                .catch(function(err) {
                    alert(err && err.message ? err.message : 'Delete failed.');
                });
        }

        function saveAssessmentPeriod(payload, token) {
            return fetch('/admin/employees/performance-assessment/period', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token,
                    'Accept': 'application/json'
                },
                body: JSON.stringify(payload)
            }).then(function(response) {
                return response.text().then(function(rawText) {
                    try {
                        return JSON.parse(rawText);
                    } catch (err) {
                        throw new Error('Save failed.');
                    }
                });
            });
        }

        function postPeriodWithConfirm(payload, token, onDone) {
            function attempt(dataPayload) {
                return saveAssessmentPeriod(dataPayload, token).then(function(data) {
                    if (data.warning && data.message) {
                        if (!confirm(data.message)) {
                            return null;
                        }
                        if (data.message.includes('overlaps')) {
                            dataPayload.force = true;
                        } else if (data.message.includes('using this assessment period')) {
                            dataPayload.force_edit = true;
                        }
                        return attempt(dataPayload);
                    }
                    return data;
                });
            }

            return attempt(payload).then(function(data) {
                if (!data) {
                    return;
                }
                if (data.success) {
                    window.closeNewPeriodModal();
                    var savedPeriod = data.data || data.period || null;
                    if (savedPeriod && savedPeriod.id) {
                        var periodYear = savedPeriod.period_year || '';
                        var savedFrom = savedPeriod.date_from ? String(savedPeriod.date_from).slice(0, 10) : '';
                        var savedTo = savedPeriod.date_to ? String(savedPeriod.date_to).slice(0, 10) : '';
                        navigateToAssessmentPeriod(savedPeriod.id, periodYear, savedFrom, savedTo);
                    } else {
                        window.location.reload();
                    }
                    return;
                }
                alert(data.message || 'Save failed.');
                if (typeof onDone === 'function') {
                    onDone();
                }
            }).catch(function(err) {
                alert(err && err.message ? err.message : 'Save failed.');
                if (typeof onDone === 'function') {
                    onDone();
                }
            });
        }

        window.openNewPeriodModal = function(root, period) {
            var newPeriodModal = document.getElementById('newPeriodModal');
            var title = document.getElementById('periodModalTitle');
            var subtitle = document.getElementById('periodModalSubtitle');
            if (!newPeriodModal) {
                alert('Modal elements missing.');
                return;
            }

            activePeriodModalRoot = root || activePeriodModalRoot || document.querySelector('.assessment-period-manager');

            if (period) {
                setPeriodModalMode('edit');
                if (title) title.textContent = 'Edit Assessment Period';
                if (subtitle) subtitle.textContent = 'Update dates or review type for the selected period.';
                document.getElementById('editPeriodIdInput').value = period.id || '';
                document.getElementById('editPeriodDateFromInput').value = formatDateOnly(period.date_from);
                document.getElementById('editPeriodDateToInput').value = formatDateOnly(period.date_to);
                document.getElementById('editPeriodReviewTypeInput').value = period.review_type || 'A';
            } else {
                setPeriodModalMode('select');
                if (title) title.textContent = 'View Periods';
                if (subtitle) {
                    subtitle.textContent = 'Select an existing period, use the recommended prior-year window, or create a custom range.';
                }

                var newPeriodForm = document.getElementById('newPeriodForm');
                if (newPeriodForm) {
                    newPeriodForm.reset();
                }
                document.getElementById('newPeriodIdInput').value = '';
                document.getElementById('newPeriodReviewTypeInput').value = 'A';

                var reviewDateInput = document.getElementById('periodModalReviewDate');
                if (reviewDateInput) {
                    reviewDateInput.value = getReviewDateFromRoot(activePeriodModalRoot);
                }

                var dateFromError = document.getElementById('newPeriodDateFromError');
                var dateToError = document.getElementById('newPeriodDateToError');
                if (dateFromError) dateFromError.classList.add('hidden');
                if (dateToError) dateToError.classList.add('hidden');

                refreshPeriodModalData();
            }

            newPeriodModal.classList.remove('hidden');
            newPeriodModal.classList.add('flex');
        };

        window.closeNewPeriodModal = function() {
            var modal = document.getElementById('newPeriodModal');
            if (modal) {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }
            var form = document.getElementById('newPeriodForm');
            if (form) {
                form.reset();
            }
            periodModalPayload = null;
            setPeriodModalMode('select');
        };

        window.closeReviewedEmployeesModal = function() {
            var modal = document.getElementById('reviewedEmployeesModal');
            if (modal) {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }
        };

        window.closeDeleteAffectedModal = function() {
            var modal = document.getElementById('deleteAffectedModal');
            if (modal) {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }
        };

        function openReviewedEmployeesModal(root) {
            var elements = getManagerElements(root);
            var modal = document.getElementById('reviewedEmployeesModal');
            var listDiv = document.getElementById('reviewedEmployeesList');
            var facilityNameSpan = document.getElementById('reviewedEmployeesFacility');
            var periodSpan = document.getElementById('reviewedEmployeesPeriod');
            if (!modal || !listDiv || !elements.periodSelect) {
                return;
            }

            listDiv.innerHTML = '<div class="text-gray-500">Loading...</div>';
            var assessmentPeriodId = elements.periodSelect.value;
            var facilityId = elements.facilityId ? elements.facilityId.value : (window.currentFacilityId || '');

            if (facilityNameSpan) {
                facilityNameSpan.textContent = 'Facility: ' + (elements.facilityName ? elements.facilityName.value : '');
            }
            if (periodSpan) {
                var selectedPeriodOption = elements.periodSelect.selectedOptions[0];
                periodSpan.textContent = 'Assessment Period: ' + (selectedPeriodOption ? selectedPeriodOption.textContent : '');
            }

            if (!assessmentPeriodId || !facilityId) {
                listDiv.innerHTML = '<div class="text-red-600">Missing assessment period or facility.</div>';
                modal.classList.remove('hidden');
                modal.classList.add('flex');
                return;
            }

            fetch('/admin/employees/performance-assessment/reviewed-employees?assessment_period_id=' + encodeURIComponent(assessmentPeriodId) + '&facility_id=' + encodeURIComponent(facilityId), {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
                .then(function(resp) { return resp.json(); })
                .then(function(data) {
                    if (!data.success) {
                        listDiv.innerHTML = '<div class="text-red-600">' + (data.message || 'Failed to load employees.') + '</div>';
                        return;
                    }
                    if (!data.employees.length) {
                        listDiv.innerHTML = '<div class="text-gray-500">No employees reviewed for this period and facility.</div>';
                        return;
                    }

                    var html = '<table class="min-w-full border text-xs md:text-sm"><thead><tr class="bg-gray-100">' +
                        '<th class="border px-2 py-1">Employee Name</th>' +
                        '<th class="border px-2 py-1">Position</th>' +
                        '<th class="border px-2 py-1">Department</th>' +
                        '<th class="border px-2 py-1">Assessment Date</th>' +
                        '<th class="border px-2 py-1">Reviewed By</th>' +
                        '<th class="border px-2 py-1">Action</th>' +
                        '</tr></thead><tbody>';
                    data.employees.forEach(function(emp) {
                        html += '<tr>' +
                            '<td class="border px-2 py-1">' + emp.name + '</td>' +
                            '<td class="border px-2 py-1">' + (emp.position || '') + '</td>' +
                            '<td class="border px-2 py-1">' + (emp.department || '') + '</td>' +
                            '<td class="border px-2 py-1">' + (emp.assessment_date || '') + '</td>' +
                            '<td class="border px-2 py-1">' + (emp.reviewed_by || '') + '</td>' +
                            '<td class="border px-2 py-1 text-center">' +
                                '<button class="px-2 py-1 bg-blue-600 text-white rounded text-xs load-employee-btn" data-emp-id="' + emp.employee_num + '">Load</button>' +
                            '</td>' +
                            '</tr>';
                    });
                    html += '</tbody></table>';
                    listDiv.innerHTML = html;

                    listDiv.querySelectorAll('.load-employee-btn').forEach(function(button) {
                        button.addEventListener('click', function() {
                            var url = new URL(window.location.href);
                            url.searchParams.set('employee_num', this.getAttribute('data-emp-id'));
                            window.location.href = url.toString();
                        });
                    });
                })
                .catch(function() {
                    listDiv.innerHTML = '<div class="text-red-600">Failed to load employees.</div>';
                });

            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function requestDeleteAssessmentPeriod(periodId, token) {
            var url = '/admin/employees/performance-assessment/period/' + periodId;
            return fetch(url, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token,
                    'Accept': 'application/json'
                }
            }).then(function(response) {
                return response.text().then(function(rawText) {
                    try {
                        return JSON.parse(rawText);
                    } catch (err) {
                        throw new Error('Delete failed.');
                    }
                });
            });
        }

        function showAssessmentPeriodDeleteBlockedMessage(data) {
            var message = (data && data.message)
                ? data.message
                : 'This assessment period cannot be deleted because employees are already assigned to it.';
            if (data && Array.isArray(data.assigned_employees) && data.assigned_employees.length > 0) {
                var names = data.assigned_employees.map(function(item) {
                    return item.employee_name || item.employee_num;
                }).join(', ');
                message += '\n\nAssigned employees: ' + names;
            }
            alert(message);
        }

        function bindAssessmentPeriodManager(root) {
            var elements = getManagerElements(root);
            if (!elements.periodSelect) {
                return;
            }

            function filterPeriodsByYear(selectedYear) {
                var previousValue = elements.periodSelect.value;

                Array.from(elements.periodSelect.options).forEach(function(option) {
                    if (option.value === '') {
                        option.style.display = '';
                        return;
                    }
                    var optionYear = option.getAttribute('data-year');
                    option.style.display = (String(optionYear) === String(selectedYear)) ? '' : 'none';
                });

                var selectedOption = elements.periodSelect.options[elements.periodSelect.selectedIndex];
                if (previousValue !== '' && selectedOption && selectedOption.style.display === 'none') {
                    var firstVisible = Array.from(elements.periodSelect.options).find(function(option) {
                        return option.value !== '' && option.style.display !== 'none';
                    });
                    elements.periodSelect.value = firstVisible ? firstVisible.value : '';
                }

                syncSelectedAssessmentPeriod(elements.periodSelect.value);
            }

            if (elements.yearSelect && elements.yearHidden && elements.form) {
                elements.yearSelect.addEventListener('change', function() {
                    elements.yearHidden.value = this.value;
                    filterPeriodsByYear(this.value);
                    elements.form.submit();
                });
            }

            var lastPeriodSelectValue = elements.periodSelect.value;

            elements.periodSelect.addEventListener('change', function() {
                var selectedOption = this.selectedOptions[0];
                lastPeriodSelectValue = this.value;
                syncSelectedAssessmentPeriod(this.value);
                if (!selectedOption || !selectedOption.value) {
                    return;
                }
                if (selectedOption.getAttribute('data-loadable') === '0') {
                    return;
                }
                if (elements.form) {
                    elements.form.submit();
                }
            });

            if (elements.yearSelect) {
                filterPeriodsByYear(elements.yearSelect.value);
            }

            root.querySelectorAll('.js-assessment-period-action').forEach(function(button) {
                button.addEventListener('click', function() {
                    var action = this.getAttribute('data-action');
                    var selectedId = elements.periodSelect.value;

                    if (action === 'view-period') {
                        window.openNewPeriodModal(root);
                        return;
                    }

                    if (action === 'edit-period') {
                        if (!selectedId) {
                            alert('Please select an assessment period to edit.');
                            return;
                        }
                        var period = getAssessmentPeriodsList().find(function(item) {
                            return String(item.id) === String(selectedId);
                        });
                        window.openNewPeriodModal(root, period || null);
                        return;
                    }

                    if (action === 'delete-period') {
                        if (!selectedId) {
                            alert('Please select an assessment period to delete.');
                            return;
                        }
                        var selectedOption = elements.periodSelect.selectedOptions[0];
                        if (selectedOption && selectedOption.getAttribute('data-can-delete') === '0') {
                            alert('This assessment period cannot be deleted because a saved performance or competency submission exists for it.');
                            return;
                        }
                        confirmAndDeleteAssessmentPeriod(selectedId);
                        return;
                    }

                    if (action === 'reviewed-employees') {
                        openReviewedEmployeesModal(root);
                        return;
                    }

                });
            });

        }

        document.addEventListener('click', function(event) {
            var viewPeriodButton = event.target.closest('.js-assessment-period-action[data-action="view-period"]');
            if (!viewPeriodButton) {
                return;
            }
            event.preventDefault();
            event.stopPropagation();
            var root = viewPeriodButton.closest('.assessment-period-manager');
            window.openNewPeriodModal(root);
        }, true);

        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.assessment-period-manager').forEach(function(root) {
                bindAssessmentPeriodManager(root);
            });

            var refreshBtn = document.getElementById('periodModalRefreshBtn');
            if (refreshBtn) {
                refreshBtn.addEventListener('click', function() {
                    refreshPeriodModalData();
                });
            }

            var loadRecommendedBtn = document.getElementById('periodModalLoadRecommendedBtn');
            if (loadRecommendedBtn) {
                loadRecommendedBtn.addEventListener('click', function() {
                    var recommended = periodModalPayload && periodModalPayload.recommended;
                    if (!recommended || !recommended.period_id) {
                        return;
                    }
                    confirmThenLoadPeriod(
                        recommended.period_id,
                        recommended.period_year,
                        activePeriodModalRoot,
                        recommended.performance_status,
                        recommended.competency_status,
                        recommended.date_from,
                        recommended.date_to
                    );
                });
            }

            var createRecommendedBtn = document.getElementById('periodModalCreateRecommendedBtn');
            if (createRecommendedBtn) {
                createRecommendedBtn.addEventListener('click', function() {
                    var recommended = periodModalPayload && periodModalPayload.recommended;
                    if (recommended && !isPeriodYearLoadable(recommended.period_year)) {
                        alert(periodLoadBlockedMessage(recommended.period_year));
                        return;
                    }
                    var token = getCsrfToken();
                    if (!token) {
                        alert('CSRF token missing.');
                        return;
                    }
                    if (!window.currentEmployeeNum) {
                        alert('Employee number is missing. Save the employee profile first.');
                        return;
                    }
                    createRecommendedBtn.disabled = true;
                    postPeriodWithConfirm({
                        employee_num: window.currentEmployeeNum,
                        review_type: 'A',
                        review_date: getPeriodModalReviewDate(),
                        use_rule: true
                    }, token, function() {
                        createRecommendedBtn.disabled = false;
                    });
                });
            }

            var historyBody = document.getElementById('periodModalHistoryBody');
            if (historyBody) {
                historyBody.addEventListener('click', function(event) {
                    var deleteButton = event.target.closest('.period-modal-delete-btn');
                    if (deleteButton) {
                        confirmAndDeleteAssessmentPeriod(deleteButton.getAttribute('data-period-id'));
                        return;
                    }
                    var loadButton = event.target.closest('.period-modal-load-btn');
                    if (!loadButton) {
                        return;
                    }
                    confirmThenLoadPeriod(
                        loadButton.getAttribute('data-period-id'),
                        loadButton.getAttribute('data-period-year'),
                        activePeriodModalRoot,
                        loadButton.getAttribute('data-performance'),
                        loadButton.getAttribute('data-competency'),
                        loadButton.getAttribute('data-date-from'),
                        loadButton.getAttribute('data-date-to')
                    );
                });
            }

            var editSubmitBtn = document.getElementById('editPeriodSubmitBtn');
            if (editSubmitBtn) {
                editSubmitBtn.addEventListener('click', function() {
                    var token = getCsrfToken();
                    if (!token) {
                        alert('CSRF token missing.');
                        return;
                    }
                    if (!window.currentEmployeeNum) {
                        alert('Employee number is missing.');
                        return;
                    }
                    var periodId = document.getElementById('editPeriodIdInput').value;
                    var dateFrom = document.getElementById('editPeriodDateFromInput').value;
                    var dateTo = document.getElementById('editPeriodDateToInput').value;
                    var reviewType = document.getElementById('editPeriodReviewTypeInput').value;
                    if (!periodId || !dateFrom || !dateTo) {
                        alert('All fields are required.');
                        return;
                    }
                    editSubmitBtn.disabled = true;
                    postPeriodWithConfirm({
                        id: periodId,
                        employee_num: window.currentEmployeeNum,
                        date_from: dateFrom,
                        date_to: dateTo,
                        review_type: reviewType
                    }, token, function() {
                        editSubmitBtn.disabled = false;
                    });
                });
            }

            var newPeriodForm = document.getElementById('newPeriodForm');
            if (!newPeriodForm) {
                return;
            }

            newPeriodForm.addEventListener('submit', function(e) {
                e.preventDefault();

                var dateFrom = document.getElementById('newPeriodDateFromInput').value;
                var dateTo = document.getElementById('newPeriodDateToInput').value;
                var reviewType = document.getElementById('newPeriodReviewTypeInput').value;
                var dateFromError = document.getElementById('newPeriodDateFromError');
                var dateToError = document.getElementById('newPeriodDateToError');
                var submitBtn = document.getElementById('periodModalSubmitBtn');

                var hasError = false;
                if (!dateFrom) {
                    if (dateFromError) dateFromError.classList.remove('hidden');
                    hasError = true;
                } else if (dateFromError) {
                    dateFromError.classList.add('hidden');
                }
                if (!dateTo) {
                    if (dateToError) dateToError.classList.remove('hidden');
                    hasError = true;
                } else if (dateToError) {
                    dateToError.classList.add('hidden');
                }
                if (hasError) {
                    return;
                }

                var token = getCsrfToken();
                if (!token) {
                    alert('CSRF token missing.');
                    return;
                }

                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.textContent = 'Creating...';
                }

                if (!window.currentEmployeeNum) {
                    alert('Employee number is missing. Save the employee profile first.');
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.textContent = 'Create custom & load';
                    }
                    return;
                }

                postPeriodWithConfirm({
                    employee_num: window.currentEmployeeNum,
                    date_from: dateFrom,
                    date_to: dateTo,
                    review_type: reviewType,
                    review_date: getPeriodModalReviewDate()
                }, token, function() {
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.textContent = 'Create custom & load';
                    }
                });
            });
        });
    })();
</script>