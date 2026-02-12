<?php

namespace Majormedia\Dashboard\Classes;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use RainLab\User\Models\User;
use MajorMedia\Bookings\Models\Booking;
use MajorMedia\Listings\Models\Listing;
use MajorMedia\Listings\Models\Expense;

/**
 * StatsService - Core statistics calculation service for Vacaloc
 *
 * Improvements over boussole-pro version:
 * - Better null handling and edge cases
 * - Optimized queries with proper indexing hints
 * - Consistent return types
 * - Separated concerns for each metric type
 * - Added caching support preparation
 */
class StatsService
{
    protected ?Carbon $dateFrom = null;
    protected ?Carbon $dateTo = null;
    protected ?Carbon $previousDateFrom = null;
    protected ?Carbon $previousDateTo = null;
    protected ?int $listingId = null;
    protected ?int $channelId = null;
    protected string $granularity = 'daily';

    /**
     * Channel constants matching Booking model
     */
    const CHANNEL_VACALOC = 1;
    const CHANNEL_AIRBNB = 2;
    const CHANNEL_BOOKINGS = 3;
    const CHANNEL_VRBO = 4;

    /**
     * Channel labels
     */
    protected array $channelLabels = [
        self::CHANNEL_VACALOC => 'Vacaloc',
        self::CHANNEL_AIRBNB => 'Airbnb',
        self::CHANNEL_BOOKINGS => 'Bookings.com',
        self::CHANNEL_VRBO => 'Vrbo',
    ];

    /**
     * Expense type labels
     */
    protected array $expenseTypeLabels = [
        1 => 'Remboursement',
        2 => 'Abonnement Wi-Fi',
        3 => 'Fournitures de bienvenue',
        4 => 'Assurance logement',
        5 => 'Frais d\'électricité',
        6 => 'Abonnement TV / Streaming',
        7 => 'Maintenance',
        8 => 'Autre',
    ];

    /**
     * Set filter parameters
     */
    public function setFilters(array $filters): self
    {
        $this->dateFrom = Carbon::parse($filters['date_from'] ?? now()->startOfMonth());
        $this->dateTo = Carbon::parse($filters['date_to'] ?? now());
        $this->listingId = isset($filters['listing_id']) ? (int) $filters['listing_id'] : null;
        $this->channelId = isset($filters['channel_id']) ? (int) $filters['channel_id'] : null;
        $this->granularity = $filters['granularity'] ?? 'daily';

        // Calculate previous period for comparison (same duration)
        $periodDays = $this->dateFrom->diffInDays($this->dateTo);
        $this->previousDateTo = $this->dateFrom->copy()->subDay();
        $this->previousDateFrom = $this->previousDateTo->copy()->subDays($periodDays);

        return $this;
    }

    /**
     * Get current filters
     */
    public function getFilters(): array
    {
        return [
            'date_from' => $this->dateFrom,
            'date_to' => $this->dateTo,
            'listing_id' => $this->listingId,
            'channel_id' => $this->channelId,
            'granularity' => $this->granularity,
        ];
    }

    /**
     * Apply listing filter to query
     */
    protected function applyListingFilter($query, string $field = 'listing_id')
    {
        if ($this->listingId) {
            $query->where($field, $this->listingId);
        }
        return $query;
    }

    /**
     * Apply channel filter to query
     */
    protected function applyChannelFilter($query, string $field = 'canal')
    {
        if ($this->channelId) {
            $query->where($field, $this->channelId);
        }
        return $query;
    }

    /**
     * Calculate evolution percentage between current and previous period
     */
    protected function calculateEvolution($current, $previous): array
    {
        $current = (float) $current;
        $previous = (float) $previous;

        if ($previous == 0) {
            $percentage = $current > 0 ? 100 : 0;
        } else {
            $percentage = round((($current - $previous) / $previous) * 100, 1);
        }

        return [
            'value' => $current,
            'previous' => $previous,
            'evolution' => $percentage,
            'trend' => $percentage >= 0 ? 'up' : 'down',
        ];
    }

    // ========================================
    // SECTION 1: MAIN KPIs
    // ========================================

    /**
     * Get main KPIs for overview cards
     */
    public function getMainKpis(): array
    {
        return [
            'total_bookings' => $this->getTotalBookingsKpi(),
            'total_revenue' => $this->getTotalRevenueKpi(),
            'active_properties' => $this->getActivePropertiesKpi(),
            'cancellation_rate' => $this->getCancellationRateKpi(),
            'avg_booking_value' => $this->getAvgBookingValueKpi(),
        ];
    }

    /**
     * Total bookings (confirmed) in period
     */
    public function getTotalBookingsKpi(): array
    {
        $query = Booking::where('is_canceled', 0);
        $this->applyListingFilter($query);
        $this->applyChannelFilter($query);

        $current = (clone $query)
            ->whereBetween('check_out', [$this->dateFrom, $this->dateTo])
            ->count();

        $previous = (clone $query)
            ->whereBetween('check_out', [$this->previousDateFrom, $this->previousDateTo])
            ->count();

        return $this->calculateEvolution($current, $previous);
    }

    /**
     * Total revenue (owner_profit) in period
     */
    public function getTotalRevenueKpi(): array
    {
        $query = Booking::where('is_canceled', 0);
        $this->applyListingFilter($query);
        $this->applyChannelFilter($query);

        $current = (clone $query)
            ->whereBetween('check_out', [$this->dateFrom, $this->dateTo])
            ->sum('owner_profit');

        $previous = (clone $query)
            ->whereBetween('check_out', [$this->previousDateFrom, $this->previousDateTo])
            ->sum('owner_profit');

        $result = $this->calculateEvolution($current, $previous);
        $result['formatted'] = number_format($result['value'], 2, ',', ' ') . ' €';
        $result['previous_formatted'] = number_format($result['previous'], 2, ',', ' ') . ' €';

        return $result;
    }

    /**
     * Active properties with bookings in period
     */
    public function getActivePropertiesKpi(): array
    {
        $current = Booking::where('is_canceled', 0)
            ->whereBetween('check_out', [$this->dateFrom, $this->dateTo])
            ->distinct('listing_id')
            ->count('listing_id');

        $previous = Booking::where('is_canceled', 0)
            ->whereBetween('check_out', [$this->previousDateFrom, $this->previousDateTo])
            ->distinct('listing_id')
            ->count('listing_id');

        $totalProperties = Listing::where('is_active', 1)->count();

        $result = $this->calculateEvolution($current, $previous);
        $result['total'] = $totalProperties;
        $result['percentage'] = $totalProperties > 0 ? round(($current / $totalProperties) * 100, 1) : 0;

        return $result;
    }

    /**
     * Cancellation rate
     */
    public function getCancellationRateKpi(): array
    {
        $query = Booking::query();
        $this->applyListingFilter($query);
        $this->applyChannelFilter($query);

        $totalCurrent = (clone $query)
            ->whereBetween('check_out', [$this->dateFrom, $this->dateTo])
            ->count();

        $canceledCurrent = (clone $query)
            ->where('is_canceled', 1)
            ->whereBetween('check_out', [$this->dateFrom, $this->dateTo])
            ->count();

        $currentRate = $totalCurrent > 0 ? round(($canceledCurrent / $totalCurrent) * 100, 1) : 0;

        $totalPrevious = (clone $query)
            ->whereBetween('check_out', [$this->previousDateFrom, $this->previousDateTo])
            ->count();

        $canceledPrevious = (clone $query)
            ->where('is_canceled', 1)
            ->whereBetween('check_out', [$this->previousDateFrom, $this->previousDateTo])
            ->count();

        $previousRate = $totalPrevious > 0 ? round(($canceledPrevious / $totalPrevious) * 100, 1) : 0;

        return [
            'value' => $currentRate,
            'previous' => $previousRate,
            'evolution' => $previousRate > 0 ? round($currentRate - $previousRate, 1) : 0,
            'trend' => $currentRate <= $previousRate ? 'up' : 'down', // Lower is better
            'count' => $canceledCurrent,
        ];
    }

    /**
     * Average booking value
     */
    public function getAvgBookingValueKpi(): array
    {
        $query = Booking::where('is_canceled', 0);
        $this->applyListingFilter($query);
        $this->applyChannelFilter($query);

        $current = (clone $query)
            ->whereBetween('check_out', [$this->dateFrom, $this->dateTo])
            ->avg('owner_profit') ?? 0;

        $previous = (clone $query)
            ->whereBetween('check_out', [$this->previousDateFrom, $this->previousDateTo])
            ->avg('owner_profit') ?? 0;

        $result = $this->calculateEvolution(round($current, 2), round($previous, 2));
        $result['formatted'] = number_format($result['value'], 2, ',', ' ') . ' €';

        return $result;
    }

    // ========================================
    // SECTION 2: REVENUE STATISTICS
    // ========================================

    /**
     * Get revenue statistics
     */
    public function getRevenueStats(): array
    {
        return [
            'gross_revenue' => $this->getGrossRevenue(),
            'net_revenue' => $this->getNetRevenue(),
            'owner_profit' => $this->getOwnerProfit(),
            'total_commissions' => $this->getTotalCommissions(),
            'vacaloc_commission' => $this->getVacalocCommission(),
            'ota_commission' => $this->getOtaCommission(),
            'cleaning_fees' => $this->getCleaningFees(),
            'taxes_collected' => $this->getTaxesCollected(),
            'revenue_by_channel' => $this->getRevenueByChannel(),
            'revenue_by_property' => $this->getRevenueByProperty(),
            'monthly_revenue_trend' => $this->getMonthlyRevenueTrend(),
        ];
    }

    protected function getGrossRevenue(): array
    {
        $query = Booking::where('is_canceled', 0);
        $this->applyListingFilter($query);
        $this->applyChannelFilter($query);

        $current = (clone $query)
            ->whereBetween('check_out', [$this->dateFrom, $this->dateTo])
            ->sum('gross_amount');

        $previous = (clone $query)
            ->whereBetween('check_out', [$this->previousDateFrom, $this->previousDateTo])
            ->sum('gross_amount');

        $result = $this->calculateEvolution($current, $previous);
        $result['formatted'] = number_format($result['value'], 2, ',', ' ') . ' €';
        return $result;
    }

    protected function getNetRevenue(): array
    {
        $query = Booking::where('is_canceled', 0);
        $this->applyListingFilter($query);
        $this->applyChannelFilter($query);

        $current = (clone $query)
            ->whereBetween('check_out', [$this->dateFrom, $this->dateTo])
            ->sum('net_amount');

        $previous = (clone $query)
            ->whereBetween('check_out', [$this->previousDateFrom, $this->previousDateTo])
            ->sum('net_amount');

        $result = $this->calculateEvolution($current, $previous);
        $result['formatted'] = number_format($result['value'], 2, ',', ' ') . ' €';
        return $result;
    }

    protected function getOwnerProfit(): array
    {
        $query = Booking::where('is_canceled', 0);
        $this->applyListingFilter($query);
        $this->applyChannelFilter($query);

        $current = (clone $query)
            ->whereBetween('check_out', [$this->dateFrom, $this->dateTo])
            ->sum('owner_profit');

        $previous = (clone $query)
            ->whereBetween('check_out', [$this->previousDateFrom, $this->previousDateTo])
            ->sum('owner_profit');

        $result = $this->calculateEvolution($current, $previous);
        $result['formatted'] = number_format($result['value'], 2, ',', ' ') . ' €';
        return $result;
    }

    protected function getTotalCommissions(): array
    {
        $query = Booking::where('is_canceled', 0);
        $this->applyListingFilter($query);
        $this->applyChannelFilter($query);

        $current = (clone $query)
            ->whereBetween('check_out', [$this->dateFrom, $this->dateTo])
            ->selectRaw('SUM(vacaloc_commission + ota_commission) as total')
            ->value('total') ?? 0;

        $previous = (clone $query)
            ->whereBetween('check_out', [$this->previousDateFrom, $this->previousDateTo])
            ->selectRaw('SUM(vacaloc_commission + ota_commission) as total')
            ->value('total') ?? 0;

        $result = $this->calculateEvolution($current, $previous);
        $result['formatted'] = number_format($result['value'], 2, ',', ' ') . ' €';
        return $result;
    }

    protected function getVacalocCommission(): array
    {
        $query = Booking::where('is_canceled', 0);
        $this->applyListingFilter($query);

        $current = (clone $query)
            ->whereBetween('check_out', [$this->dateFrom, $this->dateTo])
            ->sum('vacaloc_commission');

        $previous = (clone $query)
            ->whereBetween('check_out', [$this->previousDateFrom, $this->previousDateTo])
            ->sum('vacaloc_commission');

        $result = $this->calculateEvolution($current, $previous);
        $result['formatted'] = number_format($result['value'], 2, ',', ' ') . ' €';
        return $result;
    }

    protected function getOtaCommission(): array
    {
        $query = Booking::where('is_canceled', 0);
        $this->applyListingFilter($query);

        $current = (clone $query)
            ->whereBetween('check_out', [$this->dateFrom, $this->dateTo])
            ->sum('ota_commission');

        $previous = (clone $query)
            ->whereBetween('check_out', [$this->previousDateFrom, $this->previousDateTo])
            ->sum('ota_commission');

        $result = $this->calculateEvolution($current, $previous);
        $result['formatted'] = number_format($result['value'], 2, ',', ' ') . ' €';
        return $result;
    }

    protected function getCleaningFees(): array
    {
        $query = Booking::where('is_canceled', 0);
        $this->applyListingFilter($query);
        $this->applyChannelFilter($query);

        $current = (clone $query)
            ->whereBetween('check_out', [$this->dateFrom, $this->dateTo])
            ->sum('clearning_fee');

        $previous = (clone $query)
            ->whereBetween('check_out', [$this->previousDateFrom, $this->previousDateTo])
            ->sum('clearning_fee');

        $result = $this->calculateEvolution($current, $previous);
        $result['formatted'] = number_format($result['value'], 2, ',', ' ') . ' €';
        return $result;
    }

    protected function getTaxesCollected(): array
    {
        $query = Booking::where('is_canceled', 0);
        $this->applyListingFilter($query);
        $this->applyChannelFilter($query);

        $current = (clone $query)
            ->whereBetween('check_out', [$this->dateFrom, $this->dateTo])
            ->sum('tax');

        $previous = (clone $query)
            ->whereBetween('check_out', [$this->previousDateFrom, $this->previousDateTo])
            ->sum('tax');

        $result = $this->calculateEvolution($current, $previous);
        $result['formatted'] = number_format($result['value'], 2, ',', ' ') . ' €';
        return $result;
    }

    protected function getRevenueByChannel(): array
    {
        $query = Booking::where('is_canceled', 0)
            ->whereBetween('check_out', [$this->dateFrom, $this->dateTo]);
        $this->applyListingFilter($query);

        $results = $query
            ->selectRaw('canal, SUM(owner_profit) as revenue, COUNT(*) as bookings')
            ->groupBy('canal')
            ->get();

        $data = [];
        $totalRevenue = $results->sum('revenue');

        foreach ($this->channelLabels as $channelId => $label) {
            $channelData = $results->firstWhere('canal', $channelId);
            $revenue = $channelData->revenue ?? 0;
            $bookings = $channelData->bookings ?? 0;

            $data[] = [
                'channel_id' => $channelId,
                'channel' => $label,
                'revenue' => round($revenue, 2),
                'revenue_formatted' => number_format($revenue, 2, ',', ' ') . ' €',
                'bookings' => $bookings,
                'percentage' => $totalRevenue > 0 ? round(($revenue / $totalRevenue) * 100, 1) : 0,
            ];
        }

        return $data;
    }

    protected function getRevenueByProperty(int $limit = 10): array
    {
        $query = Booking::where('is_canceled', 0)
            ->whereBetween('check_out', [$this->dateFrom, $this->dateTo]);
        $this->applyChannelFilter($query);

        return $query
            ->selectRaw('listing_id, SUM(owner_profit) as revenue, COUNT(*) as bookings')
            ->groupBy('listing_id')
            ->orderByDesc('revenue')
            ->limit($limit)
            ->get()
            ->map(function ($item) {
                $listing = Listing::find($item->listing_id);
                return [
                    'listing_id' => $item->listing_id,
                    'title' => $listing->title ?? 'Logement #' . $item->listing_id,
                    'revenue' => round($item->revenue, 2),
                    'revenue_formatted' => number_format($item->revenue, 2, ',', ' ') . ' €',
                    'bookings' => $item->bookings,
                ];
            })
            ->toArray();
    }

    protected function getMonthlyRevenueTrend(): array
    {
        $months = collect(range(0, 11))->map(fn($i) => now()->startOfYear()->addMonths($i));

        return $months->map(function ($month) {
            $query = Booking::where('is_canceled', 0)
                ->whereBetween('check_out', [$month->copy()->startOfMonth(), $month->copy()->endOfMonth()]);
            $this->applyListingFilter($query);
            $this->applyChannelFilter($query);

            $revenue = $query->sum('owner_profit');

            return [
                'month' => $month->format('Y-m'),
                'label' => ucfirst($month->locale('fr')->isoFormat('MMM')),
                'revenue' => round($revenue, 2),
            ];
        })->toArray();
    }

    // ========================================
    // SECTION 3: BOOKINGS STATISTICS
    // ========================================

    /**
     * Get bookings statistics
     */
    public function getBookingsStats(): array
    {
        return [
            'bookings_by_channel' => $this->getBookingsByChannel(),
            'bookings_by_property' => $this->getBookingsByProperty(),
            'upcoming_checkins' => $this->getUpcomingCheckins(),
            'recent_checkouts' => $this->getRecentCheckouts(),
            'cancellation_analysis' => $this->getCancellationAnalysis(),
            'avg_stay_duration' => $this->getAvgStayDuration(),
            'peak_booking_months' => $this->getPeakBookingMonths(),
            'bookings_trend' => $this->getBookingsTrend(),
        ];
    }

    protected function getBookingsByChannel(): array
    {
        $query = Booking::where('is_canceled', 0)
            ->whereBetween('check_out', [$this->dateFrom, $this->dateTo]);
        $this->applyListingFilter($query);

        $results = $query
            ->selectRaw('canal, COUNT(*) as count')
            ->groupBy('canal')
            ->get();

        $total = $results->sum('count');
        $data = [];

        foreach ($this->channelLabels as $channelId => $label) {
            $count = $results->firstWhere('canal', $channelId)->count ?? 0;
            $data[] = [
                'channel_id' => $channelId,
                'channel' => $label,
                'count' => $count,
                'percentage' => $total > 0 ? round(($count / $total) * 100, 1) : 0,
            ];
        }

        return $data;
    }

    protected function getBookingsByProperty(int $limit = 10): array
    {
        $query = Booking::where('is_canceled', 0)
            ->whereBetween('check_out', [$this->dateFrom, $this->dateTo]);
        $this->applyChannelFilter($query);

        return $query
            ->selectRaw('listing_id, COUNT(*) as count')
            ->groupBy('listing_id')
            ->orderByDesc('count')
            ->limit($limit)
            ->get()
            ->map(function ($item) {
                $listing = Listing::find($item->listing_id);
                return [
                    'listing_id' => $item->listing_id,
                    'title' => $listing->title ?? 'Logement #' . $item->listing_id,
                    'count' => $item->count,
                ];
            })
            ->toArray();
    }

    protected function getUpcomingCheckins(int $days = 7): array
    {
        $query = Booking::where('is_canceled', 0)
            ->whereBetween('check_in', [now(), now()->addDays($days)])
            ->orderBy('check_in');
        $this->applyListingFilter($query);

        return $query
            ->with('listing:id,title')
            ->limit(20)
            ->get()
            ->map(fn($booking) => [
                'id' => $booking->id,
                'customer' => $booking->customer,
                'check_in' => Carbon::parse($booking->check_in)->format('d/m/Y'),
                'check_out' => Carbon::parse($booking->check_out)->format('d/m/Y'),
                'listing' => $booking->listing->title ?? 'N/A',
                'channel' => $this->channelLabels[$booking->canal] ?? 'N/A',
                'days_until' => now()->diffInDays(Carbon::parse($booking->check_in)),
            ])
            ->toArray();
    }

    protected function getRecentCheckouts(int $days = 7): array
    {
        $query = Booking::where('is_canceled', 0)
            ->whereBetween('check_out', [now()->subDays($days), now()])
            ->orderByDesc('check_out');
        $this->applyListingFilter($query);

        return $query
            ->with('listing:id,title')
            ->limit(20)
            ->get()
            ->map(fn($booking) => [
                'id' => $booking->id,
                'customer' => $booking->customer,
                'check_out' => Carbon::parse($booking->check_out)->format('d/m/Y'),
                'listing' => $booking->listing->title ?? 'N/A',
                'revenue' => number_format($booking->owner_profit, 2, ',', ' ') . ' €',
            ])
            ->toArray();
    }

    protected function getCancellationAnalysis(): array
    {
        $query = Booking::whereBetween('check_out', [$this->dateFrom, $this->dateTo]);
        $this->applyListingFilter($query);

        $total = (clone $query)->count();
        $canceled = (clone $query)->where('is_canceled', 1)->count();

        // Cancellations by channel
        $byChannel = Booking::where('is_canceled', 1)
            ->whereBetween('check_out', [$this->dateFrom, $this->dateTo])
            ->selectRaw('canal, COUNT(*) as count')
            ->groupBy('canal')
            ->get()
            ->mapWithKeys(fn($item) => [$this->channelLabels[$item->canal] ?? 'N/A' => $item->count])
            ->toArray();

        return [
            'total_bookings' => $total,
            'canceled' => $canceled,
            'confirmed' => $total - $canceled,
            'rate' => $total > 0 ? round(($canceled / $total) * 100, 1) : 0,
            'by_channel' => $byChannel,
        ];
    }

    protected function getAvgStayDuration(): array
    {
        $query = Booking::where('is_canceled', 0)
            ->whereBetween('check_out', [$this->dateFrom, $this->dateTo]);
        $this->applyListingFilter($query);
        $this->applyChannelFilter($query);

        $result = $query
            ->selectRaw('AVG(DATEDIFF(check_out, check_in)) as avg_nights')
            ->first();

        $avgNights = round($result->avg_nights ?? 0, 1);

        return [
            'value' => $avgNights,
            'label' => $avgNights . ' nuits',
        ];
    }

    protected function getPeakBookingMonths(): array
    {
        $query = Booking::where('is_canceled', 0)
            ->whereYear('check_out', now()->year);
        $this->applyListingFilter($query);

        return $query
            ->selectRaw('MONTH(check_out) as month, COUNT(*) as count')
            ->groupBy('month')
            ->orderByDesc('count')
            ->limit(3)
            ->get()
            ->map(fn($item) => [
                'month' => Carbon::create(null, $item->month)->locale('fr')->isoFormat('MMMM'),
                'count' => $item->count,
            ])
            ->toArray();
    }

    protected function getBookingsTrend(): array
    {
        $data = [];
        $currentDate = $this->dateFrom->copy();

        while ($currentDate <= $this->dateTo) {
            $periodEnd = $this->getNextPeriodDate($currentDate);

            $query = Booking::where('is_canceled', 0)
                ->whereBetween('check_out', [$currentDate, $periodEnd]);
            $this->applyListingFilter($query);
            $this->applyChannelFilter($query);

            $data[] = [
                'date' => $currentDate->format($this->getDateFormat()),
                'value' => $query->count(),
            ];

            $currentDate = $periodEnd->copy()->addDay();
        }

        return $data;
    }

    // ========================================
    // SECTION 4: PROPERTIES STATISTICS
    // ========================================

    /**
     * Get properties statistics
     */
    public function getPropertiesStats(): array
    {
        return [
            'total_active' => $this->getTotalActiveProperties(),
            'properties_by_owner' => $this->getPropertiesByOwner(),
            'occupancy_rates' => $this->getOccupancyRates(),
            'properties_without_bookings' => $this->getPropertiesWithoutBookings(),
            'property_performance' => $this->getPropertyPerformance(),
        ];
    }

    protected function getTotalActiveProperties(): int
    {
        return Listing::where('is_active', 1)->count();
    }

    protected function getPropertiesByOwner(int $limit = 10): array
    {
        return DB::table('majormedia_listings_user_listings')
            ->join('users', 'users.id', '=', 'majormedia_listings_user_listings.user_id')
            ->selectRaw('users.id, users.name, users.email, COUNT(*) as property_count')
            ->groupBy('users.id', 'users.name', 'users.email')
            ->orderByDesc('property_count')
            ->limit($limit)
            ->get()
            ->map(fn($item) => [
                'user_id' => $item->id,
                'name' => $item->name ?? $item->email,
                'email' => $item->email,
                'property_count' => $item->property_count,
            ])
            ->toArray();
    }

    protected function getOccupancyRates(): array
    {
        $listings = Listing::where('is_active', 1)->get();
        $periodDays = $this->dateFrom->diffInDays($this->dateTo) + 1;

        return $listings->map(function ($listing) use ($periodDays) {
            // Calculate booked days
            $bookedDays = Booking::where('listing_id', $listing->id)
                ->where('is_canceled', 0)
                ->where(function ($q) {
                    $q->whereBetween('check_in', [$this->dateFrom, $this->dateTo])
                        ->orWhereBetween('check_out', [$this->dateFrom, $this->dateTo])
                        ->orWhere(function ($q2) {
                            $q2->where('check_in', '<=', $this->dateFrom)
                                ->where('check_out', '>=', $this->dateTo);
                        });
                })
                ->get()
                ->reduce(function ($carry, $booking) {
                    $checkIn = Carbon::parse($booking->check_in)->max($this->dateFrom);
                    $checkOut = Carbon::parse($booking->check_out)->min($this->dateTo);
                    return $carry + max(0, $checkIn->diffInDays($checkOut));
                }, 0);

            $occupancyRate = $periodDays > 0 ? round(($bookedDays / $periodDays) * 100, 1) : 0;

            return [
                'listing_id' => $listing->id,
                'title' => $listing->title,
                'booked_days' => $bookedDays,
                'total_days' => $periodDays,
                'occupancy_rate' => $occupancyRate,
            ];
        })
        ->sortByDesc('occupancy_rate')
        ->values()
        ->toArray();
    }

    protected function getPropertiesWithoutBookings(int $days = 30): array
    {
        $listingIdsWithBookings = Booking::where('is_canceled', 0)
            ->where('check_out', '>=', now()->subDays($days))
            ->distinct()
            ->pluck('listing_id');

        return Listing::where('is_active', 1)
            ->whereNotIn('id', $listingIdsWithBookings)
            ->get()
            ->map(fn($listing) => [
                'id' => $listing->id,
                'title' => $listing->title,
                'owner' => $listing->owner_full_name ?? 'N/A',
            ])
            ->toArray();
    }

    protected function getPropertyPerformance(int $limit = 10): array
    {
        return Listing::where('is_active', 1)
            ->withCount(['bookings' => function ($q) {
                $q->where('is_canceled', 0)
                    ->whereBetween('check_out', [$this->dateFrom, $this->dateTo]);
            }])
            ->with(['bookings' => function ($q) {
                $q->where('is_canceled', 0)
                    ->whereBetween('check_out', [$this->dateFrom, $this->dateTo]);
            }])
            ->get()
            ->map(function ($listing) {
                $revenue = $listing->bookings->sum('owner_profit');
                return [
                    'id' => $listing->id,
                    'title' => $listing->title,
                    'bookings' => $listing->bookings_count,
                    'revenue' => round($revenue, 2),
                    'revenue_formatted' => number_format($revenue, 2, ',', ' ') . ' €',
                    'avg_per_booking' => $listing->bookings_count > 0
                        ? round($revenue / $listing->bookings_count, 2)
                        : 0,
                ];
            })
            ->sortByDesc('revenue')
            ->take($limit)
            ->values()
            ->toArray();
    }

    // ========================================
    // SECTION 5: EXPENSES STATISTICS
    // ========================================

    /**
     * Get expenses statistics
     */
    public function getExpensesStats(): array
    {
        return [
            'total_expenses' => $this->getTotalExpenses(),
            'expenses_by_type' => $this->getExpensesByType(),
            'expenses_by_property' => $this->getExpensesByProperty(),
            'monthly_expenses_trend' => $this->getMonthlyExpensesTrend(),
            'net_profit' => $this->getNetProfit(),
        ];
    }

    protected function getTotalExpenses(): array
    {
        $query = Expense::query();
        $this->applyListingFilter($query);

        $current = (clone $query)
            ->whereBetween('processed_at', [$this->dateFrom, $this->dateTo])
            ->sum('amount');

        $previous = (clone $query)
            ->whereBetween('processed_at', [$this->previousDateFrom, $this->previousDateTo])
            ->sum('amount');

        $result = $this->calculateEvolution($current, $previous);
        $result['formatted'] = number_format($result['value'], 2, ',', ' ') . ' €';

        // For expenses, lower is better (invert trend display)
        $result['trend'] = $result['evolution'] <= 0 ? 'up' : 'down';

        return $result;
    }

    protected function getExpensesByType(): array
    {
        $query = Expense::whereBetween('processed_at', [$this->dateFrom, $this->dateTo]);
        $this->applyListingFilter($query);

        $results = $query
            ->selectRaw('expenses_type, SUM(amount) as total, COUNT(*) as count')
            ->groupBy('expenses_type')
            ->get();

        $totalExpenses = $results->sum('total');

        return collect($this->expenseTypeLabels)->map(function ($label, $typeId) use ($results, $totalExpenses) {
            $typeData = $results->firstWhere('expenses_type', $typeId);
            $total = $typeData->total ?? 0;
            return [
                'type_id' => $typeId,
                'type' => $label,
                'total' => round($total, 2),
                'total_formatted' => number_format($total, 2, ',', ' ') . ' €',
                'count' => $typeData->count ?? 0,
                'percentage' => $totalExpenses > 0 ? round(($total / $totalExpenses) * 100, 1) : 0,
            ];
        })->values()->toArray();
    }

    protected function getExpensesByProperty(int $limit = 10): array
    {
        return Expense::whereBetween('processed_at', [$this->dateFrom, $this->dateTo])
            ->selectRaw('listing_id, SUM(amount) as total, COUNT(*) as count')
            ->groupBy('listing_id')
            ->orderByDesc('total')
            ->limit($limit)
            ->get()
            ->map(function ($item) {
                $listing = Listing::find($item->listing_id);
                return [
                    'listing_id' => $item->listing_id,
                    'title' => $listing->title ?? 'Logement #' . $item->listing_id,
                    'total' => round($item->total, 2),
                    'total_formatted' => number_format($item->total, 2, ',', ' ') . ' €',
                    'count' => $item->count,
                ];
            })
            ->toArray();
    }

    protected function getMonthlyExpensesTrend(): array
    {
        $months = collect(range(0, 11))->map(fn($i) => now()->startOfYear()->addMonths($i));

        return $months->map(function ($month) {
            $query = Expense::whereBetween('processed_at', [
                $month->copy()->startOfMonth(),
                $month->copy()->endOfMonth()
            ]);
            $this->applyListingFilter($query);

            $total = $query->sum('amount');

            return [
                'month' => $month->format('Y-m'),
                'label' => ucfirst($month->locale('fr')->isoFormat('MMM')),
                'total' => round($total, 2),
            ];
        })->toArray();
    }

    protected function getNetProfit(): array
    {
        // Revenue (owner profit)
        $revenueQuery = Booking::where('is_canceled', 0)
            ->whereBetween('check_out', [$this->dateFrom, $this->dateTo]);
        $this->applyListingFilter($revenueQuery);
        $this->applyChannelFilter($revenueQuery);
        $revenue = $revenueQuery->sum('owner_profit');

        // Expenses
        $expenseQuery = Expense::whereBetween('processed_at', [$this->dateFrom, $this->dateTo]);
        $this->applyListingFilter($expenseQuery);
        $expenses = $expenseQuery->sum('amount');

        $netProfit = $revenue - $expenses;

        return [
            'revenue' => round($revenue, 2),
            'revenue_formatted' => number_format($revenue, 2, ',', ' ') . ' €',
            'expenses' => round($expenses, 2),
            'expenses_formatted' => number_format($expenses, 2, ',', ' ') . ' €',
            'net_profit' => round($netProfit, 2),
            'net_profit_formatted' => number_format($netProfit, 2, ',', ' ') . ' €',
            'margin' => $revenue > 0 ? round(($netProfit / $revenue) * 100, 1) : 0,
        ];
    }

    // ========================================
    // SECTION 6: CHANNELS STATISTICS
    // ========================================

    /**
     * Get channels statistics
     */
    public function getChannelsStats(): array
    {
        return [
            'channel_performance' => $this->getChannelPerformance(),
            'channel_trends' => $this->getChannelTrends(),
            'best_channel' => $this->getBestChannel(),
        ];
    }

    protected function getChannelPerformance(): array
    {
        $query = Booking::where('is_canceled', 0)
            ->whereBetween('check_out', [$this->dateFrom, $this->dateTo]);
        $this->applyListingFilter($query);

        $results = $query
            ->selectRaw('canal, COUNT(*) as bookings, SUM(owner_profit) as revenue, AVG(owner_profit) as avg_value, SUM(vacaloc_commission) as vacaloc_comm, SUM(ota_commission) as ota_comm')
            ->groupBy('canal')
            ->get();

        return collect($this->channelLabels)->map(function ($label, $channelId) use ($results) {
            $data = $results->firstWhere('canal', $channelId);
            return [
                'channel_id' => $channelId,
                'channel' => $label,
                'bookings' => $data->bookings ?? 0,
                'revenue' => round($data->revenue ?? 0, 2),
                'revenue_formatted' => number_format($data->revenue ?? 0, 2, ',', ' ') . ' €',
                'avg_value' => round($data->avg_value ?? 0, 2),
                'avg_value_formatted' => number_format($data->avg_value ?? 0, 2, ',', ' ') . ' €',
                'commission' => round(($data->vacaloc_comm ?? 0) + ($data->ota_comm ?? 0), 2),
                'commission_formatted' => number_format(($data->vacaloc_comm ?? 0) + ($data->ota_comm ?? 0), 2, ',', ' ') . ' €',
            ];
        })->values()->toArray();
    }

    protected function getChannelTrends(): array
    {
        $months = collect(range(0, 5))->map(fn($i) => now()->subMonths($i)->startOfMonth());

        return $months->reverse()->map(function ($month) {
            $data = ['month' => ucfirst($month->locale('fr')->isoFormat('MMM YY'))];

            foreach ($this->channelLabels as $channelId => $label) {
                $query = Booking::where('is_canceled', 0)
                    ->where('canal', $channelId)
                    ->whereBetween('check_out', [$month->copy()->startOfMonth(), $month->copy()->endOfMonth()]);
                $this->applyListingFilter($query);

                $data[$label] = $query->count();
            }

            return $data;
        })->values()->toArray();
    }

    protected function getBestChannel(): array
    {
        $performance = $this->getChannelPerformance();
        $best = collect($performance)->sortByDesc('revenue')->first();

        return [
            'by_revenue' => $best,
            'by_bookings' => collect($performance)->sortByDesc('bookings')->first(),
            'by_avg_value' => collect($performance)->sortByDesc('avg_value')->first(),
        ];
    }

    // ========================================
    // SECTION 7: TIME SERIES DATA
    // ========================================

    /**
     * Get time series data for charts
     */
    public function getTimeSeriesData(string $metric): array
    {
        $data = [];
        $currentDate = $this->dateFrom->copy();

        while ($currentDate <= $this->dateTo) {
            $periodEnd = $this->getNextPeriodDate($currentDate);

            $value = $this->getMetricValue($metric, $currentDate, $periodEnd);

            $data[] = [
                'date' => $currentDate->format($this->getDateFormat()),
                'value' => $value,
            ];

            $currentDate = $periodEnd->copy()->addDay();
        }

        return $data;
    }

    protected function getNextPeriodDate(Carbon $date): Carbon
    {
        return match ($this->granularity) {
            'daily' => $date->copy()->endOfDay(),
            'weekly' => $date->copy()->endOfWeek(),
            'monthly' => $date->copy()->endOfMonth(),
            'yearly' => $date->copy()->endOfYear(),
            default => $date->copy()->endOfDay(),
        };
    }

    protected function getDateFormat(): string
    {
        return match ($this->granularity) {
            'daily' => 'Y-m-d',
            'weekly' => 'Y-W',
            'monthly' => 'Y-m',
            'yearly' => 'Y',
            default => 'Y-m-d',
        };
    }

    protected function getMetricValue(string $metric, Carbon $from, Carbon $to)
    {
        $query = match ($metric) {
            'bookings' => Booking::where('is_canceled', 0)->whereBetween('check_out', [$from, $to]),
            'revenue' => Booking::where('is_canceled', 0)->whereBetween('check_out', [$from, $to]),
            'expenses' => Expense::whereBetween('processed_at', [$from, $to]),
            default => null,
        };

        if (!$query) {
            return 0;
        }

        $this->applyListingFilter($query);

        if ($metric === 'bookings') {
            $this->applyChannelFilter($query);
            return $query->count();
        }

        if ($metric === 'revenue') {
            $this->applyChannelFilter($query);
            return round($query->sum('owner_profit'), 2);
        }

        return round($query->sum('amount'), 2);
    }

    // ========================================
    // SECTION 9: FILTER OPTIONS
    // ========================================

    /**
     * Get available listings for filter
     */
    public function getAvailableListings(): array
    {
        return Listing::where('is_active', 1)
            ->orderBy('title')
            ->get(['id', 'title', 'shortName'])
            ->map(fn($listing) => [
                'id' => $listing->id,
                'title' => $listing->title,
                'shortName' => $listing->shortName,
            ])
            ->toArray();
    }

    /**
     * Get available channels for filter
     */
    public function getAvailableChannels(): array
    {
        return collect($this->channelLabels)->map(fn($label, $id) => [
            'id' => $id,
            'label' => $label,
        ])->values()->toArray();
    }
}
