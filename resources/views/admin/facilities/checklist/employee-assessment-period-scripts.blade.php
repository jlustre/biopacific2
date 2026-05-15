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

        function navigateToAssessmentPeriod(periodId, periodYear) {
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

        window.openNewPeriodModal = function(period) {
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

            newPeriodForm.reset();
            periodIdInput.value = '';
            dateFromInput.value = '';
            dateToInput.value = '';
            reviewTypeInput.value = 'A';

            var dateFromError = document.getElementById('newPeriodDateFromError');
            var dateToError = document.getElementById('newPeriodDateToError');
            if (dateFromError) dateFromError.classList.add('hidden');
            if (dateToError) dateToError.classList.add('hidden');

            if (period) {
                periodIdInput.value = period.id || '';
                dateFromInput.value = period.date_from || '';
                dateToInput.value = period.date_to || '';
                reviewTypeInput.value = period.review_type || 'A';
                title.textContent = 'Edit Assessment Period';
                submitBtn.textContent = 'Update';
            } else {
                var today = new Date();
                var yyyy = today.getFullYear();
                var mm = String(today.getMonth() + 1).padStart(2, '0');
                var dd = String(today.getDate()).padStart(2, '0');
                var todayStr = yyyy + '-' + mm + '-' + dd;
                dateFromInput.value = todayStr;
                dateToInput.value = todayStr;
                title.textContent = 'Create Assessment Period';
                submitBtn.textContent = 'Create';
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
        };

        window.closeReviewedEmployeesModal = function() {
            var modal = document.getElementById('reviewedEmployeesModal');
            if (modal) {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }
        };

        window.closeAllAssessmentPeriodsModal = function() {
            var modal = document.getElementById('allAssessmentPeriodsModal');
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

        function openAllAssessmentPeriodsModal(root) {
            var modal = document.getElementById('allAssessmentPeriodsModal');
            var list = document.getElementById('allAssessmentPeriodsList');
            var description = document.getElementById('allAssessmentPeriodsDescription');
            var elements = getManagerElements(root);
            if (!modal || !list || !elements.periodSelect) {
                return;
            }

            var contextLabel = root.getAttribute('data-context-label') || 'employee assessment';
            if (description) {
                description.textContent = 'Select a period to load this employee\'s ' + contextLabel.toLowerCase() + ' for that assessment window.';
            }

            var periods = Array.isArray(window.assessmentPeriods) ? window.assessmentPeriods : [];
            if (!periods.length) {
                list.innerHTML = '<div class="text-sm text-gray-500">No assessment periods have been created yet.</div>';
            } else {
                var currentPeriodId = String(elements.periodSelect.value || '');
                var rows = periods.map(function(period) {
                    var isCurrent = String(period.id) === currentPeriodId;
                    var reviewTypeLabel = period.review_type === 'Q' ? 'Quarterly' : 'Annual';
                    var performanceState = getAssessmentStateMap('performance')[String(period.id)] || getAssessmentStateMap('performance')[Number(period.id)] || null;
                    var competencyState = getAssessmentStateMap('competency')[String(period.id)] || getAssessmentStateMap('competency')[Number(period.id)] || null;
                    var performanceLabel = performanceState ? performanceState.status_label : 'No Saved Submission';
                    var competencyLabel = competencyState ? competencyState.status_label : 'No Saved Submission';
                    return '<tr class="' + (isCurrent ? 'bg-amber-50' : '') + '">' +
                        '<td class="border px-3 py-2 text-xs md:text-sm">' + (period.date_from || '') + ' to ' + (period.date_to || '') + '</td>' +
                        '<td class="border px-3 py-2 text-xs md:text-sm">' + (period.period_year || '') + '</td>' +
                        '<td class="border px-3 py-2 text-xs md:text-sm">' + reviewTypeLabel + '</td>' +
                        '<td class="border px-3 py-2 text-center text-xs md:text-sm">' + (isCurrent ? '<span class="font-semibold text-amber-700">Current</span>' : '') + '</td>' +
                        '<td class="border px-3 py-2 text-center text-xs md:text-sm">' + performanceLabel + '</td>' +
                        '<td class="border px-3 py-2 text-center text-xs md:text-sm">' + competencyLabel + '</td>' +
                        '<td class="border px-3 py-2 text-center">' +
                            '<button type="button" class="load-assessment-period-btn px-2 py-1 bg-teal-600 text-white rounded text-xs" data-period-id="' + period.id + '" data-period-year="' + (period.period_year || '') + '">Load</button>' +
                        '</td>' +
                    '</tr>';
                }).join('');

                list.innerHTML = '<table class="min-w-full border text-xs md:text-sm"><thead><tr class="bg-gray-100">' +
                    '<th class="border px-3 py-2 text-left">Assessment Period</th>' +
                    '<th class="border px-3 py-2 text-left">Year</th>' +
                    '<th class="border px-3 py-2 text-left">Type</th>' +
                    '<th class="border px-3 py-2 text-center">Status</th>' +
                    '<th class="border px-3 py-2 text-center">Performance</th>' +
                    '<th class="border px-3 py-2 text-center">Competency</th>' +
                    '<th class="border px-3 py-2 text-center">Action</th>' +
                    '</tr></thead><tbody>' + rows + '</tbody></table>';

                list.querySelectorAll('.load-assessment-period-btn').forEach(function(button) {
                    button.addEventListener('click', function() {
                        var periodId = this.getAttribute('data-period-id');
                        var periodYear = this.getAttribute('data-period-year');
                        var assessmentState = getAssessmentState(root, periodId);
                        var contextLabel = String(root.getAttribute('data-context-label') || 'assessment').toLowerCase();

                        if (assessmentState) {
                            var warningMessage = assessmentState.is_completed
                                ? 'A ' + contextLabel + ' already exists for this employee in the selected period and its status is Completed. It will load in read-only mode. Continue?'
                                : 'A ' + contextLabel + ' already exists for this employee in the selected period with status "' + assessmentState.status_label + '". The reviewer can still make changes until it is completed. Continue loading it?';

                            if (!window.confirm(warningMessage)) {
                                return;
                            }
                        }

                        navigateToAssessmentPeriod(periodId, periodYear);
                    });
                });
            }

            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function requestDeleteAssessmentPeriod(periodId, token, forceDelete) {
            var url = '/admin/employees/performance-assessment/period/' + periodId + (forceDelete ? '?force=1' : '');
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

        window.showDeleteAffectedModal = function(affected, periodId, token) {
            var modal = document.getElementById('deleteAffectedModal');
            var list = document.getElementById('deleteAffectedList');
            var confirmBtn = document.getElementById('confirmDeletePeriodBtn');
            if (!modal || !list || !confirmBtn) {
                return;
            }

            list.innerHTML = '<ul class="list-disc pl-5">' + affected.map(function(item) {
                return '<li><strong>' + (item.employee_name || ('Employee ID: ' + item.employee_num)) + '</strong> (' + (item.assessment_date || 'No date') + ')</li>';
            }).join('') + '</ul>';
            modal.classList.remove('hidden');
            modal.classList.add('flex');

            confirmBtn.onclick = function() {
                requestDeleteAssessmentPeriod(periodId, token, true)
                    .then(function(data) {
                        if (data.success) {
                            alert('Assessment period deleted.');
                            window.location.reload();
                            return;
                        }
                        alert(data.message || 'Delete failed.');
                    })
                    .catch(function(err) {
                        alert(err && err.message ? err.message : 'Delete failed.');
                    });
            };
        };

        function bindAssessmentPeriodManager(root) {
            var elements = getManagerElements(root);
            if (!elements.form || !elements.periodSelect || !elements.yearSelect || !elements.yearHidden) {
                return;
            }

            function filterPeriodsByYear(selectedYear) {
                Array.from(elements.periodSelect.options).forEach(function(option) {
                    option.style.display = (String(option.getAttribute('data-year')) === String(selectedYear)) ? '' : 'none';
                });

                if (elements.periodSelect.selectedOptions.length && elements.periodSelect.selectedOptions[0].style.display === 'none') {
                    var firstVisible = Array.from(elements.periodSelect.options).find(function(option) {
                        return option.style.display !== 'none';
                    });
                    if (firstVisible) {
                        elements.periodSelect.value = firstVisible.value;
                    }
                }

                syncSelectedAssessmentPeriod(elements.periodSelect.value);
            }

            elements.yearSelect.addEventListener('change', function() {
                elements.yearHidden.value = this.value;
                filterPeriodsByYear(this.value);
                elements.form.submit();
            });

            elements.periodSelect.addEventListener('change', function() {
                syncSelectedAssessmentPeriod(this.value);
            });

            root.querySelectorAll('.js-assessment-period-action').forEach(function(button) {
                button.addEventListener('click', function() {
                    var action = this.getAttribute('data-action');
                    var selectedId = elements.periodSelect.value;

                    if (action === 'new-period') {
                        window.openNewPeriodModal();
                        return;
                    }

                    if (action === 'edit-period') {
                        if (!selectedId) {
                            alert('Please select an assessment period to edit.');
                            return;
                        }
                        var period = (window.assessmentPeriods || []).find(function(item) {
                            return String(item.id) === String(selectedId);
                        });
                        window.openNewPeriodModal(period || null);
                        return;
                    }

                    if (action === 'delete-period') {
                        if (!selectedId) {
                            alert('Please select an assessment period to delete.');
                            return;
                        }
                        if (!confirm('Are you sure you want to delete this assessment period? This action cannot be undone.')) {
                            return;
                        }
                        var token = getCsrfToken();
                        if (!token) {
                            alert('CSRF token missing.');
                            return;
                        }
                        requestDeleteAssessmentPeriod(selectedId, token, false)
                            .then(function(data) {
                                if (data.success) {
                                    alert('Assessment period deleted.');
                                    window.location.reload();
                                    return;
                                }
                                if (data.affected && Array.isArray(data.affected) && data.affected.length > 0) {
                                    window.showDeleteAffectedModal(data.affected, selectedId, token);
                                    return;
                                }
                                alert(data.message || 'Delete failed.');
                            })
                            .catch(function(err) {
                                alert(err && err.message ? err.message : 'Delete failed.');
                            });
                        return;
                    }

                    if (action === 'reviewed-employees') {
                        openReviewedEmployeesModal(root);
                        return;
                    }

                    if (action === 'all-periods') {
                        openAllAssessmentPeriodsModal(root);
                    }
                });
            });

            filterPeriodsByYear(elements.yearSelect.value);
        }

        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.assessment-period-manager').forEach(function(root) {
                bindAssessmentPeriodManager(root);
            });

            var newPeriodForm = document.getElementById('newPeriodForm');
            if (!newPeriodForm) {
                return;
            }

            newPeriodForm.addEventListener('submit', function(e) {
                e.preventDefault();

                var id = document.getElementById('newPeriodIdInput').value;
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
                    submitBtn.textContent = id ? 'Updating...' : 'Creating...';
                }

                var payload = {
                    date_from: dateFrom,
                    date_to: dateTo,
                    review_type: reviewType
                };
                if (id) {
                    payload.id = id;
                }

                function restoreSubmitButton() {
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.textContent = id ? 'Update' : 'Create';
                    }
                }

                function savePeriod(dataPayload) {
                    return fetch('/admin/employees/performance-assessment/period', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': token,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(dataPayload)
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

                savePeriod(payload)
                    .then(function(data) {
                        if (data.warning && data.message) {
                            if (!confirm(data.message)) {
                                restoreSubmitButton();
                                return null;
                            }
                            if (data.message.includes('overlaps')) {
                                payload.force = true;
                            } else if (data.message.includes('using this assessment period')) {
                                payload.force_edit = true;
                            }
                            return savePeriod(payload);
                        }
                        return data;
                    })
                    .then(function(data) {
                        if (!data) {
                            return;
                        }
                        if (data.success) {
                            window.closeNewPeriodModal();
                            window.location.reload();
                            return;
                        }
                        alert(data.message || 'Save failed.');
                        restoreSubmitButton();
                    })
                    .catch(function(err) {
                        alert(err && err.message ? err.message : 'Save failed.');
                        restoreSubmitButton();
                    });
            });
        });

        // Attach handler for New Period button to open modal
        // (Fixes: New Assessment Period popup not showing)
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.js-assessment-period-action[data-action="new-period"]').forEach(btn => {
                btn.addEventListener('click', function () {
                    window.openNewPeriodModal();
                });
            });
        });
    })();
</script>