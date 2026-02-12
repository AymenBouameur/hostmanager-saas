<?php
namespace Majormedia\Eviivo\Console;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use MajorMedia\Bookings\Models\Booking;
use MajorMedia\Listings\Models\Listing;
use MajorMedia\Eviivo\Classes\DashboardApi;
use Majormedia\InCore\Models\Settings;
ini_set('max_execution_time', 300); 
ini_set('memory_limit', '512M');

/**
 * FetchEviivoBookings Command
 *
 * @link https://docs.octobercms.com/3.x/extend/console-commands.html
 */
class FetchEviivoBookings extends Command
{
    protected $signature = 'fetch:eviivo-bookings {month?} {listingId?}';
    protected $description = 'Fetch bookings from Eviivo API for a specified property. Add month and listing ID as optional parameters';

    public function handle()
    {
        $month = $this->argument('month');
        $listingId = $this->argument('listingId');
        $year = Carbon::now()->year;

        if ($month === null) {
            $date = Carbon::now()->subMonth();
            $month = $date->month;
            $year = $date->year;
        } else {
            $month = (int) $month;
            $year = Carbon::now()->year;

            if ($month < 1 || $month > 12) {
                $this->error('Invalid month. Please provide a value between 1 and 12.');
                return;
            }
        }

        $stayFrom = Carbon::create($year, $month, 1)->startOfMonth();
        $stayTo = $stayFrom->copy()->endOfMonth();

        $stayFrom = $stayFrom->toDateString();
        $stayTo = $stayTo->toDateString();

        Log::info(
            "Starting to fetch bookings: " .
            "month=" . $month . ", " .
            "year=" . $year . ", " .
            "from=" . $stayFrom . ", " .
            "to=" . $stayTo .
            ($listingId ? ", listingId=" . $listingId : ", all listings")
        );

        $api = new DashboardApi();
        try {
            // If listingId is provided, fetch only that listing, otherwise fetch all
            if ($listingId) {
                $listing = Listing::find($listingId);
                if (!$listing) {
                    $this->error("Listing with ID {$listingId} not found.");
                    Log::error("Listing with ID {$listingId} not found.");
                    return;
                }
                $properties = [$listing->shortName];
                $this->info("Fetching bookings for listing: {$listing->title}");
            } else {
                $properties = Listing::pluck('shortName')->toArray();
                $this->info("Fetching bookings for all listings");
            }

            foreach ($properties as $property) {
                if (!$property) {
                    Log::warning('Skipping property due to null value.');
                    continue;
                }

                try {
                    $bookingsResponse = $api->request('get', "v2/property/$property/bookings", [
                        'stayFrom' => $stayFrom,
                        'stayTo' => $stayTo,
                    ]);
                } catch (\Exception $e) {
                    Log::error("Error fetching bookings for property $property: " . $e->getMessage());
                    $this->error("Error fetching bookings for property: $property");
                    continue;
                }

                if (empty($bookingsResponse['Bookings'])) {
                    $this->error("No bookings found for property: $property");
                    continue;
                }

                foreach ($bookingsResponse['Bookings'] as $bookingData) {
                    $bookingInfo = $bookingData['Booking'] ?? null;
                    if (!$bookingInfo) {
                        Log::warning("Missing booking info for property: $property");
                        continue;
                    }

                    $orderReference = $bookingInfo['OrderReference'] ?? null;
                    if (!$orderReference) {
                        Log::warning("Missing order reference for booking: {$bookingInfo['BookingReference']}");
                        continue;
                    }

                    try {
                        $orderResponse = $api->request('get', "v2/property/$property/orders/$orderReference");
                        $totalOrderAmount = $orderResponse['TotalAmount']['Value'] ?? 0;

                        $bookingDetails = $api->request('get', "v2/property/$property/bookings/bookingref/{$bookingInfo['BookingReference']}");

                        // Calculate tax
                        $checkIn = Carbon::parse($bookingInfo['CheckinDate']);
                        $checkOut = Carbon::parse($bookingInfo['CheckoutDate']);
                        $numberOfNights = $checkOut->diffInDays($checkIn);
                        $tax = $bookingInfo['NumberOfAdults'] * $numberOfNights;
                        $calculated_net = $bookingInfo['Total']['GrossAmount']['Value'] - $tax;

                        // Calculate cleaning fee
                        $clearingFee = 0;
                        if (!empty($bookingDetails['GuestCharges'])) {
                            foreach ($bookingDetails['GuestCharges'] as $charge) {
                                if ($charge['ChargeTypeCode'] === 'CLEANING_FEE') {
                                    $clearingFee = $charge['GrossAmount']['Value'] ?? 0;
                                }
                            }
                        }

                    } catch (\Exception $e) {
                        Log::error("Error fetching order details for {$orderReference}: " . $e->getMessage());
                        continue;
                    }

                    $listing = Listing::where('shortName', $property)->first();
                    if (!$listing) {
                        Log::warning("Listing not found for property: $property");
                        continue;
                    }

                    try {
                        // Determine canal and OTA commission
                        $canal = 1;
                        $ota_comission = 0;
                        $ota_comission_value = 0;

                        switch ($bookingInfo['OTAShortname'] ?? null) {
                            case 'Airbnb':
                                $canal = 2;
                                $ota_comission = Settings::get('airbnb_commission') ?? 15;
                                break;
                            case 'booking_com':
                                $canal = 3;
                                $ota_comission = Settings::get('booking_commission') ?? 17;
                                break;
                            case 'HomeAwayInc':
                            case 'Vrbo':
                                $canal = 4;
                                $ota_comission = Settings::get('vrbo_commission') ?? 15;
                                break;
                            case 'myweb':
                            case null:
                            default:
                                $canal = 1;
                        }

                        $ota_comission_value = ($calculated_net * $ota_comission) / 100;

                        $vacaloc_commission = $listing->commission ?? Settings::get('vacaloc_commission') ?? 22;
                        $vacaloc_commission_value = $vacaloc_commission > 0
                            ? (($calculated_net - $clearingFee) * $vacaloc_commission) / 100
                            : 0;

                        $owner_profit = $calculated_net - ($clearingFee + $vacaloc_commission_value + $ota_comission_value);

                        $row = [
                            'reference' => $bookingInfo['BookingReference'],
                            'order_reference' => $bookingInfo['OrderReference'],
                            'listing_id' => $listing->id,
                            'customer' => ($bookingData['Guests'][0]['FirstName'] ?? '') . ' ' . ($bookingData['Guests'][0]['Surname'] ?? ''),
                            'check_in' => $bookingInfo['CheckinDate'] ?? null,
                            'check_out' => $bookingInfo['CheckoutDate'] ?? null,
                            'canal' => $canal,
                            'net_amount' => $calculated_net,
                            'gross_amount' => $bookingInfo['Total']['GrossAmount']['Value'] ?? null,
                            'tax' => $tax ?? 0,
                            'clearning_fee' => $clearingFee ?? 0,
                            'ota_commission' => $ota_comission_value,
                            'vacaloc_commission' => $vacaloc_commission_value,
                            'total_amount_order' => $totalOrderAmount ?? 0,
                            'owner_profit' => $owner_profit,
                            'is_canceled' => $bookingInfo['Cancelled'] ?? false,
                            'updated_at' => $bookingInfo['BookingUpdatedDateTime'] ?? now(),
                            'created_at' => $bookingInfo['BookedDateTime'] ?? now(),
                        ];

                        Booking::upsert([$row], ['reference'], [
                            'order_reference',
                            'listing_id',
                            'customer',
                            'check_in',
                            'check_out',
                            'canal',
                            'net_amount',
                            'gross_amount',
                            'tax',
                            'clearning_fee',
                            'ota_commission',
                            'vacaloc_commission',
                            'total_amount_order',
                            'owner_profit',
                            'is_canceled',
                            'updated_at'
                        ]);

                        Log::info("Booking upserted successfully for reference {$bookingInfo['BookingReference']}");
                        $this->info("Booking Fetched and stored successfully: {$bookingInfo['BookingReference']}");

                    } catch (\Exception $e) {
                        Log::error("Error upserting booking {$bookingInfo['BookingReference']}: " . $e->getMessage());
                        continue;
                    }
                }
            }

            $this->info("All bookings fetched and stored successfully.");
            Log::info("All bookings fetched and stored successfully.");

        } catch (\Throwable $e) {
            Log::error("General error in booking sync: " . $e->getMessage());
            $this->error("Failed to fetch bookings.");
        }
    }
}