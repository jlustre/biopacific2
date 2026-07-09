<script>
    @php
        $partGDeepLinkAccordionKey = filled(request('checklist_section'))
            ? app(\App\Services\CompetencySectionWorkflowService::class)->accordionKeyForSectionLabel((string) request('checklist_section'))
            : null;
    @endphp
    window.partGDeepLinkAccordionKey = @json($partGDeepLinkAccordionKey);
    window.partGDeepLinkAcknowledgementKey = @json($partGDeepLinkAccordionKey);

    window.partGOverallRatingFromAverage = function partGOverallRatingFromAverage(points, ratedCount) {
        if (!ratedCount || ratedCount === 0) {
            return '—';
        }

        var avg = points / ratedCount;
        if (avg >= 2.51) {
            return 'Exceeds Expectations';
        }
        if (avg >= 1.75) {
            return 'Meets Expectations';
        }
        if (avg > 0) {
            return 'Below Expectations';
        }

        return '—';
    };

    window.partGSectionSummary = function partGSectionSummary(items) {
        return {
            items: Array.isArray(items) ? items : [],
            summary: {
                totalItems: 0,
                checkedOfTotal: '',
                totalPoints: 0,
                pointsOfTotal: '',
                average: '—',
                overallRating: '—',
            },
            init: function() {
                this.updateSummary();
            },
            syncResponses: function(detail) {
                var responses = detail;
                if (detail && typeof detail === 'object' && detail.responses) {
                    responses = detail.responses;
                }
                if (!responses || typeof responses !== 'object') {
                    return;
                }
                this.items.forEach(function(item) {
                    item.response = responses[item.id] ?? responses[String(item.id)] ?? null;
                });
                this.updateSummary();
            },
            setResponse: function(itemId, rating) {
                var item = this.items.find(function(i) { return i.id == itemId; });
                if (item) {
                    item.response = rating;
                }
                this.updateSummary();
            },
            updateSummary: function() {
                var total = 0;
                var rated = 0;
                var points = 0;
                var self = this;
                this.items.forEach(function(item) {
                    if (!item.isParent) {
                        total++;
                        if (!item.response) {
                            return;
                        }
                        var score = self.responseToPoints(item.response);
                        if (score <= 0) {
                            return;
                        }
                        rated++;
                        points += score;
                    }
                });
                this.summary.totalItems = total;
                this.summary.checkedOfTotal = rated + ' of ' + total + ' rated';
                this.summary.totalPoints = points;
                this.summary.pointsOfTotal = points + ' of ' + (total * 3) + ' points';
                this.summary.average = rated > 0 ? (points / rated).toFixed(2) : '—';
                this.summary.overallRating = window.partGOverallRatingFromAverage(points, rated);
            },
            responseToPoints: function(val) {
                if (val === 'E') return 3;
                if (val === 'M' || val === 'S') return 2;
                if (val === 'B' || val === 'U') return 1;
                return 0;
            },
            getOverallRating: function(points, ratedCount) {
                return window.partGOverallRatingFromAverage(points, ratedCount);
            },
        };
    };

    window.partGTrachSummary = function partGTrachSummary(procedures) {
        return {
            procedures: Array.isArray(procedures) ? procedures : [],
            summary: {
                totalItems: 0,
                checkedOfTotal: '',
                totalPoints: 0,
                pointsOfTotal: '',
                average: '—',
                overallRating: '—',
            },
            init: function() {
                this.updateSummary();
            },
            syncReviews: function(reviews) {
                if (!reviews || typeof reviews !== 'object') {
                    return;
                }
                this.procedures.forEach(function(row) {
                    row.response = reviews[row.key] ?? reviews[String(row.key)] ?? null;
                });
                this.updateSummary();
            },
            updateSummary: function() {
                var total = this.procedures.length;
                var rated = 0;
                var points = 0;
                this.procedures.forEach(function(row) {
                    if (!row.response) {
                        return;
                    }
                    if (['E', 'M', 'B', 'S', 'U'].indexOf(row.response) === -1) {
                        return;
                    }
                    rated++;
                    points += row.response === 'E' ? 3 : row.response === 'S' ? 2 : 1;
                });
                this.summary.totalItems = total;
                this.summary.checkedOfTotal = rated + ' of ' + total + ' rated';
                this.summary.totalPoints = points;
                this.summary.pointsOfTotal = points + ' of ' + (total * 3) + ' points';
                this.summary.average = rated > 0 ? (points / rated).toFixed(2) : '—';
                this.summary.overallRating = window.partGOverallRatingFromAverage(points, rated);
            },
            getOverallRating: function(points, ratedCount) {
                return window.partGOverallRatingFromAverage(points, ratedCount);
            },
        };
    };

    window.partGAccordionPersist = {
        storageKey: function () {
            var employeeNum = window.currentEmployeeNum || 'unknown';
            return 'partGOpenSection:' + String(employeeNum);
        },
        read: function () {
            try {
                return sessionStorage.getItem(this.storageKey());
            } catch (error) {
                return null;
            }
        },
        write: function (openSection) {
            try {
                var key = this.storageKey();
                if (openSection) {
                    sessionStorage.setItem(key, openSection);
                } else {
                    sessionStorage.removeItem(key);
                }
            } catch (error) {
                // Ignore storage failures in restricted browser modes.
            }
        },
        bind: function (store) {
            if (!store || store.__partGPersistenceBound || typeof Alpine === 'undefined' || typeof Alpine.effect !== 'function') {
                return;
            }

            store.__partGPersistenceBound = true;

            Alpine.effect(function () {
                window.partGAccordionPersist.write(store.openSection);
            });
        },
        setOpenSection: function (openSection) {
            if (typeof Alpine === 'undefined') {
                return;
            }

            var store = Alpine.store('partGAccordion');
            if (!store) {
                return;
            }

            store.openSection = openSection;
        },
        toggleOpenSection: function (accordionKey) {
            if (typeof Alpine === 'undefined') {
                return;
            }

            var store = Alpine.store('partGAccordion');
            if (!store) {
                return;
            }

            store.openSection = store.openSection === accordionKey ? null : accordionKey;
        },
    };

    window.registerPartGAlpineStore = function registerPartGAlpineStore() {
        if (typeof Alpine === 'undefined') {
            return;
        }

        var persisted = window.partGAccordionPersist.read();
        var store = Alpine.store('partGAccordion');

        if (!store) {
            Alpine.store('partGAccordion', {
                openSection: persisted || null,
            });
            store = Alpine.store('partGAccordion');
        } else if (!store.openSection && persisted) {
            store.openSection = persisted;
        }

        window.partGAccordionPersist.bind(store);
    };

    function registerPartGAlpineStoreLocal() {
        window.registerPartGAlpineStore();
    }

    document.addEventListener('alpine:init', registerPartGAlpineStoreLocal);
    if (window.Alpine) {
        registerPartGAlpineStoreLocal();
    }

    function initPartGHierarchyToggles() {
        if (typeof window.initializeHierarchyToggles === 'function') {
            window.initializeHierarchyToggles(document.getElementById('partG'));
        }
    }

    function runWhenDomReady(callback) {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', callback);
            return;
        }

        callback();
    }

    runWhenDomReady(initPartGHierarchyToggles);

    function normalizePartGEventPayload(detail) {
        if (Array.isArray(detail) && detail.length === 1 && typeof detail[0] === 'object') {
            detail = detail[0];
        }

        if (!detail || typeof detail !== 'object') {
            return {};
        }

        if (detail.detail && typeof detail.detail === 'object') {
            return Object.assign({}, detail.detail, detail);
        }

        return detail;
    }

    window.updatePartGSummaryScores = function updatePartGSummaryScores(detail) {
        var totalEl = document.getElementById('partGTotalScore');
        var averageEl = document.getElementById('partGAverageScore');
        var overallEl = document.getElementById('partGOverallRating');
        var overallValueEl = document.getElementById('partGOverallRatingValue');

        if (!totalEl || !averageEl || !overallEl) {
            return;
        }

        var payload = normalizePartGEventPayload(detail);
        var totalScore = payload.totalScore ?? payload.total_score ?? totalEl.value ?? 0;
        var averageScore = payload.averageScore ?? payload.average_score ?? averageEl.value ?? '';
        var overallRating = payload.overallRating ?? payload.overall_rating ?? overallEl.value ?? '—';

        totalEl.value = totalScore === '' ? '' : String(totalScore);
        averageEl.value = averageScore === '' || averageScore === null
            ? ''
            : Number(averageScore).toFixed(2);
        overallEl.value = overallRating === '' ? '—' : String(overallRating);

        if (overallValueEl) {
            overallValueEl.value = overallEl.value;
        }
    };

    function registerPartGLivewireSummaryListeners() {
        if (window.__partGSummaryListenersRegistered) {
            return;
        }

        if (typeof Livewire === 'undefined' || typeof Livewire.on !== 'function') {
            return;
        }

        window.__partGSummaryListenersRegistered = true;

        Livewire.on('partg-summary-updated', function (event) {
            window.updatePartGSummaryScores(event);
        });

        // NOTE: The legacy `lnemar-responses-updated` Alpine bridge was removed.
        // The LICENSED NURSE eMAR competency is now a pure Livewire component
        // that owns its own section + global summary cards via #[Computed]
        // properties, so it does not dispatch this event anymore.
    }

    document.addEventListener('livewire:init', registerPartGLivewireSummaryListeners);
    if (typeof Livewire !== 'undefined' && typeof Livewire.on === 'function') {
        registerPartGLivewireSummaryListeners();
    }

    document.addEventListener('partg-summary-updated', function (event) {
        window.updatePartGSummaryScores(event.detail || {});
    });

    runWhenDomReady(function () {
        window.updatePartGSummaryScores();
        window.openPartGDeepLinkedSection();
    });

    window.openPartGDeepLinkedSection = function openPartGDeepLinkedSection() {
        var accordionKey = window.partGDeepLinkAccordionKey;
        if (!accordionKey) {
            return;
        }

        var openSection = function () {
            if (typeof window.registerPartGAlpineStore === 'function') {
                window.registerPartGAlpineStore();
            }

            window.partGAccordionPersist.setOpenSection(accordionKey);

            var acknowledgementKey = window.partGDeepLinkAcknowledgementKey || accordionKey;
            var workflowForm = document.getElementById('partGCompetencyWorkflowForm-' + acknowledgementKey);
            if (workflowForm) {
                workflowForm.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        };

        if (typeof Alpine !== 'undefined') {
            openSection();
            return;
        }

        document.addEventListener('alpine:init', openSection, { once: true });
    };
</script>
