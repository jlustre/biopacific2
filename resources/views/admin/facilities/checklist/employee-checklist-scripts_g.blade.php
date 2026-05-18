<script>
    window.partGSectionSummary = function partGSectionSummary(items) {
        return {
            items: Array.isArray(items) ? items : [],
            summary: {
                totalItems: 0,
                checkedOfTotal: '',
                totalPoints: 0,
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
                var notApplicable = 0;
                var points = 0;
                var self = this;
                this.items.forEach(function(item) {
                    if (!item.isParent) {
                        total++;
                        if (!item.response) {
                            return;
                        }
                        if (item.response === 'N') {
                            notApplicable++;
                            return;
                        }
                        rated++;
                        points += self.responseToPoints(item.response);
                    }
                });
                this.summary.totalItems = total;
                this.summary.checkedOfTotal = notApplicable > 0
                    ? rated + ' of ' + total + ' rated (' + notApplicable + ' N/A)'
                    : rated + ' of ' + total + ' rated';
                this.summary.totalPoints = points;
                this.summary.average = rated > 0 ? (points / rated).toFixed(2) : '—';
                this.summary.overallRating = this.getOverallRating(points, rated);
            },
            responseToPoints: function(val) {
                return val === 'E' ? 3 : val === 'S' ? 2 : val === 'U' ? 1 : 0;
            },
            getOverallRating: function(points, ratedCount) {
                if (ratedCount === 0) {
                    return '—';
                }
                var avg = points / ratedCount;
                if (avg >= 2.5) {
                    return 'Excellent';
                }
                if (avg >= 1.5) {
                    return 'Satisfactory';
                }
                if (avg > 0) {
                    return 'Unsatisfactory';
                }
                return 'Needs Improvement';
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
                    if (['E', 'S', 'U'].indexOf(row.response) === -1) {
                        return;
                    }
                    rated++;
                    points += row.response === 'E' ? 3 : row.response === 'S' ? 2 : 1;
                });
                this.summary.totalItems = total;
                this.summary.checkedOfTotal = rated + ' of ' + total + ' rated';
                this.summary.totalPoints = points;
                this.summary.average = rated > 0 ? (points / rated).toFixed(2) : '—';
                this.summary.overallRating = this.getOverallRating(points, rated);
            },
            getOverallRating: function(points, ratedCount) {
                if (ratedCount === 0) {
                    return '—';
                }
                var avg = points / ratedCount;
                if (avg >= 2.5) {
                    return 'Excellent';
                }
                if (avg >= 1.5) {
                    return 'Satisfactory';
                }
                if (avg > 0) {
                    return 'Unsatisfactory';
                }
                return 'Needs Improvement';
            },
        };
    };

    function registerPartGAlpineStore() {
        if (typeof Alpine === 'undefined') {
            return;
        }
        if (!Alpine.store('partGAccordion')) {
            Alpine.store('partGAccordion', { openSection: null });
        }
    }

    document.addEventListener('alpine:init', registerPartGAlpineStore);
    if (window.Alpine) {
        registerPartGAlpineStore();
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
    });
</script>
