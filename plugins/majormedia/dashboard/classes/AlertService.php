<?php

namespace Majormedia\Dashboard\Classes;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use MajorMedia\Bookings\Models\Booking;
use MajorMedia\Listings\Models\Listing;
use MajorMedia\Listings\Models\Expense;
use MajorMedia\Listings\Models\Statement;
use MajorMedia\Listings\Models\Invoice;

/**
 * AlertService - Alert detection and management for Vacaloc
 *
 * Improvements over boussole-pro version:
 * - Better threshold configuration
 * - More actionable alerts specific to rental business
 * - Improved performance with optimized queries
 */
class AlertService
{
    const LEVEL_CRITICAL = 'critical';   // Red - Requires immediate action
    const LEVEL_WARNING = 'warning';     // Orange - Needs attention soon
    const LEVEL_INFO = 'info';           // Blue - Informational

    /**
     * Alert thresholds (configurable)
     */
    protected array $thresholds = [
        'properties_no_bookings_days' => 30,
        'high_cancellation_rate' => 15,
        'revenue_drop_percentage' => 20,
        'statement_missing_months' => 1,
        'invoice_overdue_days' => 0,
        'upcoming_checkin_days' => 2,
    ];

    /**
     * Get all active alerts
     */
    public function getActiveAlerts(): Collection
    {
        return collect([
            $this->checkPropertiesWithoutBookings(),
            $this->checkHighCancellationRate(),
            $this->checkRevenueDrop(),
            $this->checkMissingStatements(),
            $this->checkOverdueInvoices(),
            $this->checkUpcomingCheckins(),
            $this->checkLowOccupancyProperties(),
        ])->filter()->sortBy(function ($alert) {
            // Sort by severity: critical first, then warning, then info
            $order = [self::LEVEL_CRITICAL => 1, self::LEVEL_WARNING => 2, self::LEVEL_INFO => 3];
            return $order[$alert['level']] ?? 4;
        })->values();
    }

    /**
     * Get alerts count by level
     */
    public function getAlertsCounts(): array
    {
        $alerts = $this->getActiveAlerts();

        return [
            'total' => $alerts->count(),
            'critical' => $alerts->where('level', self::LEVEL_CRITICAL)->count(),
            'warning' => $alerts->where('level', self::LEVEL_WARNING)->count(),
            'info' => $alerts->where('level', self::LEVEL_INFO)->count(),
        ];
    }

    /**
     * Check for properties without bookings
     */
    protected function checkPropertiesWithoutBookings(): ?array
    {
        $days = $this->thresholds['properties_no_bookings_days'];

        $listingIdsWithBookings = Booking::where('is_canceled', 0)
            ->where('check_out', '>=', now()->subDays($days))
            ->distinct()
            ->pluck('listing_id');

        $properties = Listing::where('is_active', 1)
            ->whereNotIn('id', $listingIdsWithBookings)
            ->get();

        if ($properties->isEmpty()) {
            return null;
        }

        $level = $properties->count() > 3 ? self::LEVEL_CRITICAL : self::LEVEL_WARNING;

        return [
            'level' => $level,
            'type' => 'properties_no_bookings',
            'title' => 'Propriétés sans réservation',
            'message' => "{$properties->count()} bien(s) sans réservation depuis {$days} jours",
            'count' => $properties->count(),
            'items' => $properties->map(fn($p) => [
                'id' => $p->id,
                'title' => $p->title,
            ])->toArray(),
            'link' => '#properties',
            'icon' => 'icon-home',
        ];
    }

    /**
     * Check for high cancellation rate
     */
    protected function checkHighCancellationRate(): ?array
    {
        $threshold = $this->thresholds['high_cancellation_rate'];

        $startOfMonth = now()->startOfMonth();
        $endOfMonth = now()->endOfMonth();

        $total = Booking::whereBetween('check_out', [$startOfMonth, $endOfMonth])->count();
        $canceled = Booking::where('is_canceled', 1)
            ->whereBetween('check_out', [$startOfMonth, $endOfMonth])
            ->count();

        if ($total === 0) {
            return null;
        }

        $rate = round(($canceled / $total) * 100, 1);

        if ($rate < $threshold) {
            return null;
        }

        return [
            'level' => $rate > 25 ? self::LEVEL_CRITICAL : self::LEVEL_WARNING,
            'type' => 'high_cancellation_rate',
            'title' => 'Taux d\'annulation élevé',
            'message' => "{$rate}% d'annulations ce mois ({$canceled}/{$total} réservations)",
            'count' => $canceled,
            'data' => [
                'rate' => $rate,
                'total' => $total,
                'canceled' => $canceled,
            ],
            'link' => '#bookings',
            'icon' => 'icon-times-circle',
        ];
    }

    /**
     * Check for revenue drop vs previous period
     */
    protected function checkRevenueDrop(): ?array
    {
        $threshold = $this->thresholds['revenue_drop_percentage'];

        // Current month
        $currentStart = now()->startOfMonth();
        $currentEnd = now();

        // Same period last month
        $previousStart = now()->subMonth()->startOfMonth();
        $previousEnd = now()->subMonth();

        $currentRevenue = Booking::where('is_canceled', 0)
            ->whereBetween('check_out', [$currentStart, $currentEnd])
            ->sum('owner_profit');

        $previousRevenue = Booking::where('is_canceled', 0)
            ->whereBetween('check_out', [$previousStart, $previousEnd])
            ->sum('owner_profit');

        if ($previousRevenue == 0) {
            return null;
        }

        $dropPercentage = round((($previousRevenue - $currentRevenue) / $previousRevenue) * 100, 1);

        if ($dropPercentage < $threshold) {
            return null;
        }

        return [
            'level' => $dropPercentage > 40 ? self::LEVEL_CRITICAL : self::LEVEL_WARNING,
            'type' => 'revenue_drop',
            'title' => 'Baisse de revenus',
            'message' => sprintf("-%d%% de revenus vs même période mois dernier", round($dropPercentage)),
            'count' => round($dropPercentage),
            'data' => [
                'current' => round($currentRevenue, 2),
                'previous' => round($previousRevenue, 2),
                'drop' => $dropPercentage,
            ],
            'link' => '#revenue',
            'icon' => 'icon-arrow-down',
        ];
    }

    /**
     * Check for missing statements
     */
    protected function checkMissingStatements(): ?array
    {
        $lastMonth = now()->subMonth();

        $listingsWithStatements = Statement::where('is_active', 1)
            ->whereYear('statement_date', $lastMonth->year)
            ->whereMonth('statement_date', $lastMonth->month)
            ->distinct()
            ->pluck('listing_id');

        $propertiesMissing = Listing::where('is_active', 1)
            ->whereNotIn('id', $listingsWithStatements)
            ->get();

        if ($propertiesMissing->isEmpty()) {
            return null;
        }

        return [
            'level' => self::LEVEL_WARNING,
            'type' => 'missing_statements',
            'title' => 'Relevés manquants',
            'message' => "{$propertiesMissing->count()} bien(s) sans relevé pour " . $lastMonth->locale('fr')->isoFormat('MMMM YYYY'),
            'count' => $propertiesMissing->count(),
            'items' => $propertiesMissing->map(fn($p) => [
                'id' => $p->id,
                'title' => $p->title,
            ])->toArray(),
            'link' => '#documents',
            'icon' => 'icon-file-text-o',
        ];
    }

    /**
     * Check for overdue invoices
     */
    protected function checkOverdueInvoices(): ?array
    {
        $overdueInvoices = Invoice::where('due_date', '<', now())->get();

        if ($overdueInvoices->isEmpty()) {
            return null;
        }

        return [
            'level' => self::LEVEL_CRITICAL,
            'type' => 'overdue_invoices',
            'title' => 'Factures en retard',
            'message' => "{$overdueInvoices->count()} facture(s) dépassée(s)",
            'count' => $overdueInvoices->count(),
            'items' => $overdueInvoices->map(function ($invoice) {
                return [
                    'id' => $invoice->id,
                    'number' => $invoice->invoice_number,
                    'days_overdue' => now()->diffInDays(Carbon::parse($invoice->due_date)),
                ];
            })->toArray(),
            'link' => '#documents',
            'icon' => 'icon-warning',
        ];
    }

    /**
     * Check for upcoming check-ins (next 48h)
     */
    protected function checkUpcomingCheckins(): ?array
    {
        $days = $this->thresholds['upcoming_checkin_days'];

        $upcomingBookings = Booking::where('is_canceled', 0)
            ->whereBetween('check_in', [now(), now()->addDays($days)])
            ->with('listing:id,title')
            ->get();

        if ($upcomingBookings->isEmpty()) {
            return null;
        }

        return [
            'level' => self::LEVEL_INFO,
            'type' => 'upcoming_checkins',
            'title' => 'Arrivées imminentes',
            'message' => "{$upcomingBookings->count()} arrivée(s) dans les {$days} prochains jours",
            'count' => $upcomingBookings->count(),
            'items' => $upcomingBookings->map(function ($booking) {
                return [
                    'id' => $booking->id,
                    'customer' => $booking->customer,
                    'check_in' => Carbon::parse($booking->check_in)->format('d/m H:i'),
                    'listing' => $booking->listing->title ?? 'N/A',
                ];
            })->toArray(),
            'link' => '#bookings',
            'icon' => 'icon-calendar-check-o',
        ];
    }

    /**
     * Check for low occupancy properties (< 30% this month)
     */
    protected function checkLowOccupancyProperties(): ?array
    {
        $startOfMonth = now()->startOfMonth();
        $today = now();
        $daysInPeriod = $startOfMonth->diffInDays($today) + 1;

        $listings = Listing::where('is_active', 1)->get();
        $lowOccupancy = [];

        foreach ($listings as $listing) {
            $bookedDays = Booking::where('listing_id', $listing->id)
                ->where('is_canceled', 0)
                ->where(function ($q) use ($startOfMonth, $today) {
                    $q->whereBetween('check_in', [$startOfMonth, $today])
                        ->orWhereBetween('check_out', [$startOfMonth, $today])
                        ->orWhere(function ($q2) use ($startOfMonth, $today) {
                            $q2->where('check_in', '<=', $startOfMonth)
                                ->where('check_out', '>=', $today);
                        });
                })
                ->get()
                ->reduce(function ($carry, $booking) use ($startOfMonth, $today) {
                    $checkIn = Carbon::parse($booking->check_in)->max($startOfMonth);
                    $checkOut = Carbon::parse($booking->check_out)->min($today);
                    return $carry + max(0, $checkIn->diffInDays($checkOut));
                }, 0);

            $occupancyRate = $daysInPeriod > 0 ? ($bookedDays / $daysInPeriod) * 100 : 0;

            if ($occupancyRate < 30 && $daysInPeriod > 7) {
                $lowOccupancy[] = [
                    'id' => $listing->id,
                    'title' => $listing->title,
                    'occupancy' => round($occupancyRate, 1),
                ];
            }
        }

        if (empty($lowOccupancy)) {
            return null;
        }

        return [
            'level' => self::LEVEL_INFO,
            'type' => 'low_occupancy',
            'title' => 'Occupation faible',
            'message' => count($lowOccupancy) . " bien(s) avec moins de 30% d'occupation ce mois",
            'count' => count($lowOccupancy),
            'items' => $lowOccupancy,
            'link' => '#properties',
            'icon' => 'icon-bed',
        ];
    }

    /**
     * Get summary for dashboard banner
     */
    public function getAlertSummary(): array
    {
        $alerts = $this->getActiveAlerts();

        if ($alerts->isEmpty()) {
            return [
                'has_alerts' => false,
                'message' => 'Aucune alerte active',
                'level' => 'success',
            ];
        }

        $critical = $alerts->where('level', self::LEVEL_CRITICAL);
        $warnings = $alerts->where('level', self::LEVEL_WARNING);

        $messages = [];
        if ($critical->count() > 0) {
            $messages[] = $critical->count() . ' alerte(s) critique(s)';
        }
        if ($warnings->count() > 0) {
            $messages[] = $warnings->count() . ' avertissement(s)';
        }

        return [
            'has_alerts' => true,
            'total' => $alerts->count(),
            'message' => implode(' • ', $messages),
            'level' => $critical->count() > 0 ? 'danger' : 'warning',
            'items' => $alerts->take(5)->map(fn($a) => $a['title'] . ': ' . $a['message'])->toArray(),
        ];
    }

    /**
     * Set custom thresholds
     */
    public function setThresholds(array $thresholds): self
    {
        $this->thresholds = array_merge($this->thresholds, $thresholds);
        return $this;
    }
}
