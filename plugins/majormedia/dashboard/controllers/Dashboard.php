<?php

namespace Majormedia\Dashboard\Controllers;

use Backend\Classes\Controller;
use BackendMenu;
use Carbon\Carbon;
use Majormedia\Dashboard\Classes\StatsService;
use Majormedia\Dashboard\Classes\AlertService;

/**
 * Dashboard Controller - Statistics & Analytics for Vacaloc
 *
 * Improvements over boussole-pro version:
 * - Cleaner filter handling
 * - Better error handling
 * - Optimized partial loading
 * - More granular AJAX endpoints
 */
class Dashboard extends Controller
{
    /**
     * Required permissions
     */
    public $requiredPermissions = ['majormedia.dashboard.access'];

    /**
     * Page title
     */
    public $pageTitle = 'Dashboard Statistiques';

    /**
     * Stats service instance
     */
    protected StatsService $statsService;

    /**
     * Alert service instance
     */
    protected AlertService $alertService;

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('Majormedia.Dashboard', 'dashboard', 'overview');

        $this->statsService = new StatsService();
        $this->alertService = new AlertService();

        // Add custom CSS and JS
        $this->addCss('/plugins/majormedia/dashboard/assets/css/dashboard.css');
        $this->addJs('/plugins/majormedia/dashboard/assets/js/dashboard.js');
    }

    /**
     * Main dashboard view
     */
    public function index()
    {
        $this->pageTitle = 'Dashboard Statistiques';

        // Default filters
        $filters = $this->getFiltersFromRequest();
        $this->statsService->setFilters($filters);

        // Pass data to view
        $this->vars['filters'] = $filters;
        $this->vars['listings'] = $this->statsService->getAvailableListings();
        $this->vars['channels'] = $this->statsService->getAvailableChannels();
        $this->vars['kpis'] = $this->statsService->getMainKpis();
        $this->vars['alerts'] = $this->alertService->getAlertSummary();
        $this->vars['activeTab'] = 'overview';
    }

    /**
     * Get filters from request or defaults
     */
    protected function getFiltersFromRequest(): array
    {
        $period = post('period', 'month');

        // Only use custom dates when period is 'custom'
        // For preset periods (today, week, month, year), always calculate fresh dates
        if ($period === 'custom') {
            $dateFrom = post('date_from');
            $dateTo = post('date_to');

            if ($dateFrom && $dateTo) {
                $dateFrom = Carbon::parse($dateFrom)->startOfDay();
                $dateTo = Carbon::parse($dateTo)->endOfDay();
            } else {
                // Fallback to current month if custom dates are missing
                $dateFrom = now()->startOfMonth();
                $dateTo = now()->endOfMonth();
            }
        } else {
            // Calculate dates based on period preset
            switch ($period) {
                case 'today':
                    $dateFrom = now()->startOfDay();
                    $dateTo = now()->endOfDay();
                    break;
                case 'week':
                    $dateFrom = now()->startOfWeek();
                    $dateTo = now()->endOfWeek();
                    break;
                case 'year':
                    $dateFrom = now()->startOfYear();
                    $dateTo = now()->endOfYear();
                    break;
                case 'month':
                default:
                    $dateFrom = now()->startOfMonth();
                    $dateTo = now()->endOfMonth();
                    break;
            }
        }

        return [
            'period' => $period,
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'granularity' => post('granularity', 'daily'),
            'listing_id' => post('listing_id') ? (int) post('listing_id') : null,
            'channel_id' => post('channel_id') ? (int) post('channel_id') : null,
        ];
    }

    // ========================================
    // AJAX HANDLERS
    // ========================================

    /**
     * Refresh KPIs
     */
    public function onRefreshKpis()
    {
        $filters = $this->getFiltersFromRequest();
        $this->statsService->setFilters($filters);

        $this->vars['kpis'] = $this->statsService->getMainKpis();
        $this->vars['filters'] = $filters;

        return [
            '#kpi-cards' => $this->makePartial('kpis'),
        ];
    }

    /**
     * Refresh alerts
     */
    public function onRefreshAlerts()
    {
        $this->vars['alerts'] = $this->alertService->getAlertSummary();

        return [
            '#alert-banner' => $this->makePartial('alerts'),
        ];
    }

    /**
     * Load Overview tab content
     */
    public function onLoadOverview()
    {
        $filters = $this->getFiltersFromRequest();
        $this->statsService->setFilters($filters);

        $this->vars['revenue'] = $this->statsService->getRevenueStats();
        $this->vars['bookings'] = $this->statsService->getBookingsStats();
        $this->vars['chartData'] = [
            'bookings' => $this->statsService->getTimeSeriesData('bookings'),
            'revenue' => $this->statsService->getTimeSeriesData('revenue'),
        ];

        return [
            '#tab-content' => $this->makePartial('tabs/overview'),
        ];
    }

    /**
     * Load Revenue tab content
     */
    public function onLoadRevenue()
    {
        $filters = $this->getFiltersFromRequest();
        $this->statsService->setFilters($filters);

        $this->vars['revenue'] = $this->statsService->getRevenueStats();
        $this->vars['chartData'] = [
            'revenue' => $this->statsService->getTimeSeriesData('revenue'),
            'monthly' => $this->statsService->getRevenueStats()['monthly_revenue_trend'],
        ];

        return [
            '#tab-content' => $this->makePartial('tabs/revenue'),
        ];
    }

    /**
     * Load Bookings tab content
     */
    public function onLoadBookings()
    {
        $filters = $this->getFiltersFromRequest();
        $this->statsService->setFilters($filters);

        $this->vars['bookings'] = $this->statsService->getBookingsStats();
        $this->vars['chartData'] = [
            'bookings' => $this->statsService->getTimeSeriesData('bookings'),
        ];

        return [
            '#tab-content' => $this->makePartial('tabs/bookings'),
        ];
    }

    /**
     * Load Properties tab content
     */
    public function onLoadProperties()
    {
        $filters = $this->getFiltersFromRequest();
        $this->statsService->setFilters($filters);

        $this->vars['properties'] = $this->statsService->getPropertiesStats();

        return [
            '#tab-content' => $this->makePartial('tabs/properties'),
        ];
    }

    /**
     * Load Expenses tab content
     */
    public function onLoadExpenses()
    {
        $filters = $this->getFiltersFromRequest();
        $this->statsService->setFilters($filters);

        $this->vars['expenses'] = $this->statsService->getExpensesStats();
        $this->vars['chartData'] = [
            'expenses' => $this->statsService->getTimeSeriesData('expenses'),
            'monthly' => $this->statsService->getExpensesStats()['monthly_expenses_trend'],
        ];

        return [
            '#tab-content' => $this->makePartial('tabs/expenses'),
        ];
    }

    /**
     * Load Channels tab content
     */
    public function onLoadChannels()
    {
        $filters = $this->getFiltersFromRequest();
        $this->statsService->setFilters($filters);

        $this->vars['channels'] = $this->statsService->getChannelsStats();

        return [
            '#tab-content' => $this->makePartial('tabs/channels'),
        ];
    }

    /**
     * Get chart data via AJAX
     */
    public function onGetChartData()
    {
        $filters = $this->getFiltersFromRequest();
        $this->statsService->setFilters($filters);

        $metric = post('metric', 'bookings');

        return [
            'data' => $this->statsService->getTimeSeriesData($metric),
        ];
    }

    /**
     * Get listings for filter (when channel changes)
     */
    public function onGetListings()
    {
        $this->vars['listings'] = $this->statsService->getAvailableListings();

        return [
            '#listingSelect' => $this->makePartial('listings_select'),
            'listings' => $this->vars['listings'],
        ];
    }

    /**
     * Export data to CSV
     */
    public function onExportCsv()
    {
        $type = post('export_type', 'kpis');
        $filters = $this->getFiltersFromRequest();
        $this->statsService->setFilters($filters);

        $data = match ($type) {
            'revenue' => $this->statsService->getRevenueStats(),
            'bookings' => $this->statsService->getBookingsStats(),
            'properties' => $this->statsService->getPropertiesStats(),
            'expenses' => $this->statsService->getExpensesStats(),
            'channels' => $this->statsService->getChannelsStats(),
            default => $this->statsService->getMainKpis(),
        };

        $filename = "dashboard_{$type}_" . now()->format('Y-m-d') . '.csv';

        return \Response::make($this->arrayToCsv($data), 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    /**
     * Convert array to CSV string
     */
    protected function arrayToCsv(array $data): string
    {
        $output = fopen('php://temp', 'r+');

        // Flatten nested arrays and write
        $this->writeCsvRows($output, $data);

        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        return $csv;
    }

    /**
     * Recursively write CSV rows
     */
    protected function writeCsvRows($output, array $data, string $prefix = ''): void
    {
        foreach ($data as $key => $value) {
            $rowKey = $prefix ? "{$prefix}.{$key}" : $key;

            if (is_array($value)) {
                if (isset($value[0]) && is_array($value[0])) {
                    // Array of objects - write as table
                    if (!empty($value[0])) {
                        $headers = array_keys($value[0]);
                        fputcsv($output, array_merge([$rowKey], $headers));
                        foreach ($value as $row) {
                            fputcsv($output, array_merge([''], array_values($row)));
                        }
                    }
                } elseif (isset($value['value'])) {
                    // KPI object
                    fputcsv($output, [$rowKey, $value['value'], $value['evolution'] ?? '', $value['trend'] ?? '']);
                } else {
                    $this->writeCsvRows($output, $value, $rowKey);
                }
            } else {
                fputcsv($output, [$rowKey, $value]);
            }
        }
    }

    /**
     * Get all alerts detail
     */
    public function onGetAlertsDetail()
    {
        $this->vars['alertsList'] = $this->alertService->getActiveAlerts();

        return [
            '#alerts-modal-content' => $this->makePartial('alerts_detail'),
        ];
    }
}
