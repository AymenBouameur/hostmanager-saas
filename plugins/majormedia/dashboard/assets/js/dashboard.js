/**
 * Dashboard Statistics JavaScript for Vacaloc
 *
 * Improvements over boussole-pro version:
 * - Better error handling
 * - Loading states management
 * - Cleaner event handling
 * - Chart management utilities
 */

(function() {
    'use strict';

    // Chart instances storage
    var charts = {};

    // Current active tab
    var currentTab = 'overview';
    var currentRequest = 'onLoadOverview';

    // Loading state
    var isLoading = false;

    // Initialize dashboard
    document.addEventListener('DOMContentLoaded', function() {
        initTabNavigation();
        initPeriodFilter();
        initResetButton();
        initApplyButton();

        // Load initial tab content
        loadTabContent('overview', 'onLoadOverview');
    });

    /**
     * Initialize tab navigation
     */
    function initTabNavigation() {
        var tabLinks = document.querySelectorAll('.dashboard-tabs .nav-link');

        tabLinks.forEach(function(link) {
            link.addEventListener('click', function(e) {
                e.preventDefault();

                if (isLoading) return;

                // Update active state
                tabLinks.forEach(function(l) { l.classList.remove('active'); });
                this.classList.add('active');

                // Load tab content
                var tab = this.getAttribute('data-tab');
                var request = this.getAttribute('data-request');

                // Store current tab info
                currentTab = tab;
                currentRequest = request;

                if (request) {
                    loadTabContent(tab, request);
                }
            });
        });
    }

    /**
     * Load tab content via AJAX
     */
    function loadTabContent(tab, request) {
        var tabContent = document.getElementById('tab-content');

        if (!tabContent || isLoading) return;

        isLoading = true;

        // Show loading
        tabContent.innerHTML = '<div class="text-center py-5"><i class="icon-spinner icon-spin" style="font-size: 2rem;"></i><p class="mt-2">Chargement...</p></div>';

        // Destroy existing charts before loading new content
        destroyAllCharts();

        // Get current filter values
        var formData = getFilterFormData();

        // Make AJAX request (October CMS)
        if (typeof $.request !== 'undefined' && request) {
            $.request(request, {
                data: formData,
                success: function(data) {
                    isLoading = false;
                },
                error: function(xhr, status, error) {
                    isLoading = false;
                    tabContent.innerHTML = '<div class="alert alert-danger"><i class="icon-warning"></i> Erreur lors du chargement des données. Veuillez réessayer.</div>';
                    console.error('Dashboard AJAX Error:', error);
                }
            });
        } else {
            isLoading = false;
        }
    }

    /**
     * Initialize period filter
     */
    function initPeriodFilter() {
        var periodSelect = document.getElementById('periodSelect');
        var customDateFields = document.querySelectorAll('.custom-date-field');

        if (periodSelect) {
            periodSelect.addEventListener('change', function() {
                var isCustom = this.value === 'custom';
                customDateFields.forEach(function(field) {
                    field.style.display = isCustom ? 'block' : 'none';
                });
            });
        }
    }

    /**
     * Initialize reset button
     */
    function initResetButton() {
        var resetBtn = document.getElementById('resetFiltersBtn');

        if (resetBtn) {
            resetBtn.addEventListener('click', function(e) {
                e.preventDefault();
                // Reload the page to reset all filters
                window.location.href = window.location.pathname;
            });
        }
    }

    /**
     * Initialize apply button
     */
    function initApplyButton() {
        var form = document.getElementById('filterForm');

        if (form) {
            form.addEventListener('submit', function(e) {
                e.preventDefault();

                if (isLoading) return;

                // Refresh KPIs
                refreshKpis();

                // Refresh current tab
                if (currentRequest) {
                    loadTabContent(currentTab, currentRequest);
                }
            });
        }
    }

    /**
     * Refresh KPIs
     */
    function refreshKpis() {
        var formData = getFilterFormData();

        if (typeof $.request !== 'undefined') {
            $.request('onRefreshKpis', {
                data: formData,
                update: { 'kpis': '#kpi-cards' },
                error: function(xhr, status, error) {
                    console.error('KPI Refresh Error:', error);
                }
            });
        }
    }

    /**
     * Get filter form data
     */
    function getFilterFormData() {
        var form = document.getElementById('filterForm');
        var formData = {};

        if (form) {
            var inputs = form.querySelectorAll('input, select');
            inputs.forEach(function(input) {
                if (input.name && !input.disabled) {
                    formData[input.name] = input.value;
                }
            });
        }

        return formData;
    }

    /**
     * Destroy all chart instances
     */
    function destroyAllCharts() {
        Object.keys(charts).forEach(function(key) {
            if (charts[key]) {
                charts[key].destroy();
                delete charts[key];
            }
        });
    }

    /**
     * Register a chart instance
     */
    window.registerChart = function(id, chart) {
        if (charts[id]) {
            charts[id].destroy();
        }
        charts[id] = chart;
    };

    /**
     * Get chart by ID
     */
    window.getChart = function(id) {
        return charts[id] || null;
    };

    /**
     * Format currency
     */
    window.formatCurrency = function(value) {
        return new Intl.NumberFormat('fr-FR', {
            style: 'currency',
            currency: 'EUR'
        }).format(value);
    };

    /**
     * Format number
     */
    window.formatNumber = function(value) {
        return new Intl.NumberFormat('fr-FR').format(value);
    };

    /**
     * Get chart data via AJAX
     */
    window.fetchChartData = function(metric, callback) {
        var formData = getFilterFormData();
        formData.metric = metric;

        if (typeof $.request !== 'undefined') {
            $.request('onGetChartData', {
                data: formData,
                success: function(response) {
                    if (callback && typeof callback === 'function') {
                        callback(response.data);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Chart Data Error:', error);
                }
            });
        }
    };

    /**
     * Update chart with new data
     */
    window.updateChart = function(chartId, labels, data, datasetIndex) {
        var chart = charts[chartId];
        if (!chart) return;

        datasetIndex = datasetIndex || 0;

        chart.data.labels = labels;
        chart.data.datasets[datasetIndex].data = data;
        chart.update();
    };

    /**
     * Export functionality
     */
    window.exportDashboardData = function(type) {
        var formData = getFilterFormData();
        formData.export_type = type;

        if (typeof $.request !== 'undefined') {
            $.request('onExportCsv', {
                data: formData,
                success: function(response) {
                    // Download will be handled by the response
                }
            });
        }
    };

    /**
     * Show loading overlay
     */
    window.showLoading = function(containerId) {
        var container = document.getElementById(containerId);
        if (!container) return;

        var overlay = document.createElement('div');
        overlay.className = 'loading-overlay';
        overlay.innerHTML = '<i class="icon-spinner icon-spin" style="font-size: 2rem;"></i>';
        overlay.id = containerId + '-loading';

        container.style.position = 'relative';
        container.appendChild(overlay);
    };

    /**
     * Hide loading overlay
     */
    window.hideLoading = function(containerId) {
        var overlay = document.getElementById(containerId + '-loading');
        if (overlay) {
            overlay.remove();
        }
    };

})();
