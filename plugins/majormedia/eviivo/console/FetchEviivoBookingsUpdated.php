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
class FetchEviivoBookingsUpdated extends Command
{
    protected $signature = 'fetch:eviivo-bookings-v2';
    protected $description = 'Fetch bookings from Eviivo API for a specified property version 2';

    public function handle()
    {
        $startMonth = Carbon::now()->startOfMonth();

        // Loop over current month and next 6 months
        for ($i = 0; $i <= 6; $i++) {
            $monthDate = $startMonth->copy()->addMonths($i);
            $month = $monthDate->month;
            $year = $monthDate->year;

            $stayFrom = $monthDate->copy()->startOfMonth()->toDateString();
            $stayTo = $monthDate->copy()->endOfMonth()->toDateString();

            $this->info(
                "Starting to fetch bookings: " .
                "month=" . $month . ", " .
                "year=" . $year . ", " .
                "from=" . $stayFrom . ", " .
                "to=" . $stayTo
            );

            $api = new DashboardApi();
            try {
                $properties = Listing::pluck('shortName')->toArray();

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

                            // $tax = 0;
                            // if (!empty($bookingDetails['Booking']['Total']['Taxes'])) {
                            //     foreach ($bookingDetails['Booking']['Total']['Taxes'] as $taxes) {
                            //         if ($taxes['TaxTypeCode'] === 'TAX') {
                            //             $tax = $taxes['Amount']['Value'] ?? 0;
                            //         }
                            //     }
                            // } else {
                            //     $tax = $bookingInfo['Total']['NetAmount']['Value'] - $bookingInfo['Total']['GrossAmount']['Value'];
                            // }

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

            // try {
            //     $properties = Listing::pluck('shortName')->toArray();

            //     foreach ($properties as $property) {
            //         if ($property == null) {
            //             Log::warning('Skipping property due to null value.');
            //             continue;
            //         }
            //         try {
            //             $bookingsResponse = $api->request('get', "v2/property/$property/bookings", [
            //                 'stayFrom' => $stayFrom,
            //                 'stayTo' => $stayTo,
            //             ]);
            //         } catch (\Exception $e) {
            //             Log::error("Error fetching bookings for property $property: " . $e->getMessage());
            //             $this->error("Error fetching bookings for property: $property");
            //             continue;
            //         }

            //         if (!isset($bookingsResponse['Bookings'])) {
            //             $this->error("No bookings found for property: $property");
            //             continue;
            //         }

            //         foreach ($bookingsResponse['Bookings'] as $bookingData) {
            //             $bookingInfo = $bookingData['Booking'] ?? null;
            //             \Log::info('Processing booking with reference: ' . $bookingInfo['BookingReference']);

            //             if (!$bookingInfo) {
            //                 Log::warning('Missing booking info for property: ' . $property);
            //                 continue;
            //             }

            //             $orderReference = $bookingInfo['OrderReference'] ?? null;
            //             if (!$orderReference) {
            //                 Log::warning('Missing order reference for booking: ' . $bookingInfo['BookingReference']);
            //                 continue;
            //             }

            //             try {
            //                 // Fetch order details to get the amount
            //                 $orderResponse = $api->request('get', "v2/property/$property/orders/$orderReference");
            //                 $totalOrderAmount = $orderResponse['TotalAmount']['Value'] ?? 0;
            //                 $bookingDetails = $api->request('get', "v2/property/$property/bookings/bookingref/$bookingInfo[BookingReference]");
            //                 $tax = 0;
            //                 if (!isset($bookingDetails['Booking']['Total']['Taxes'])) {
            //                     Log::warning("No taxes found for booking: " . $bookingInfo['BookingReference']);
            //                     $tax = $bookingInfo['Total']['NetAmount']['Value'] - $bookingInfo['Total']['GrossAmount']['Value'];
            //                 } else {
            //                     foreach ($bookingDetails['Booking']['Total']['Taxes'] as $taxes) {
            //                         if ($taxes['TaxTypeCode'] == 'TAX') {
            //                             $tax = $taxes['Amount']['Value'] ?? 0;
            //                         }
            //                     }
            //                 }

            //                 $clearingFee = 0;
            //                 if (array_key_exists('GuestCharges', $bookingDetails)) {
            //                     foreach ($bookingDetails['GuestCharges'] as $charge) {
            //                         if ($charge['ChargeTypeCode'] == 'CLEANING_FEE') {
            //                             $clearingFee = $charge['GrossAmount']['Value'] ?? 0;
            //                         }
            //                     }
            //                 }
            //                 // else {
            //                 //     Log::warning("GuestCharges not found for booking: " . $bookingInfo['BookingReference']);
            //                 // }
            //             } catch (\Exception $e) {
            //                 Log::error("Error fetching order details for $orderReference: " . $e->getMessage());
            //                 continue;
            //             }

            //             $listing = Listing::where('shortName', $property)->first();
            //             if (!$listing) {
            //                 Log::warning("Listing not found for property: $property");
            //                 continue;
            //             }

            //             try {
            //                 $canal = 1;
            //                 $ota_comission = 0;
            //                 $ota_comission_value = 0;

            //                 switch ($bookingInfo['OTAShortname']) {
            //                     case 'Airbnb':
            //                         $canal = 2;
            //                         $ota_comission = Settings::get('airbnb_commission') ?? 15;
            //                         $ota_comission_value = ($bookingInfo['Total']['NetAmount']['Value'] * $ota_comission) / 100;
            //                         break;
            //                     case 'booking_com':
            //                         $canal = 3;
            //                         $ota_comission = Settings::get('booking_commission') ?? 17;
            //                         $ota_comission_value = ($bookingInfo['Total']['NetAmount']['Value'] * $ota_comission) / 100;
            //                         break;
            //                     case 'myweb':
            //                         $canal = 1;
            //                         break;
            //                     case 'HomeAwayInc':
            //                         $canal = 4;
            //                         $ota_comission = Settings::get('vrbo_commission') ?? 15;
            //                         $ota_comission_value = ($bookingInfo['Total']['NetAmount']['Value'] * $ota_comission) / 100;
            //                         break;
            //                     case 'Vrbo':
            //                         $canal = 4;
            //                         $ota_comission = Settings::get('vrbo_commission') ?? 15;
            //                         $ota_comission_value = ($bookingInfo['Total']['NetAmount']['Value'] * $ota_comission) / 100;
            //                         break;
            //                     case null:
            //                         $canal = 1;
            //                         break;
            //                     default:
            //                         $canal = 1;
            //                 }
            //                 $vacaloc_commission = $listing->commission ?? Settings::get('vacaloc_commission') ?? 22;
            //                 if ($vacaloc_commission > 0) {
            //                     $vacaloc_commission_value = (($bookingInfo['Total']['NetAmount']['Value'] - $clearingFee) * $vacaloc_commission) / 100;
            //                 } else {
            //                     $vacaloc_commission_value = 0;
            //                 }
            //                 $owner_profit = $bookingInfo['Total']['NetAmount']['Value'] - ($clearingFee + $vacaloc_commission_value + $ota_comission_value);
            //                 // Check if a booking with this reference already exists
            //                 $booking = Booking::where('reference', $bookingInfo['BookingReference'])->first();

            //                 if ($booking) {
            //                     $booking->update([
            //                         'order_reference' => $bookingInfo['OrderReference'],
            //                         'listing_id' => $listing->id,
            //                         'customer' => $bookingData['Guests'][0]['FirstName'] . ' ' . $bookingData['Guests'][0]['Surname'],
            //                         'check_in' => $bookingInfo['CheckinDate'],
            //                         'check_out' => $bookingInfo['CheckoutDate'],
            //                         'canal' => $canal,
            //                         'net_amount' => $bookingInfo['Total']['NetAmount']['Value'],
            //                         'gross_amount' => $bookingInfo['Total']['GrossAmount']['Value'],
            //                         'tax' => $tax,
            //                         'clearning_fee' => $clearingFee,
            //                         'ota_commission' => $ota_comission_value,
            //                         'vacaloc_commission' => $vacaloc_commission_value,
            //                         'total_amount_order' => $totalOrderAmount,
            //                         'owner_profit' => $owner_profit,
            //                         'is_canceled' => $bookingInfo['Cancelled'],
            //                         'updated_at' => $bookingInfo['BookingUpdatedDateTime'], 
            //                     ]);
            //                     Log::info('Booking updated', [
            //                         'reference' => $bookingInfo['BookingReference'],
            //                         'order_reference' => $bookingInfo['OrderReference']
            //                     ]);
            //                 } else {
            //                     $booking = Booking::create([
            //                         'reference' => $bookingInfo['BookingReference'],
            //                         'order_reference' => $bookingInfo['OrderReference'],
            //                         'listing_id' => $listing->id,
            //                         'customer' => $bookingData['Guests'][0]['FirstName'] . ' ' . $bookingData['Guests'][0]['Surname'],
            //                         'check_in' => $bookingInfo['CheckinDate'],
            //                         'check_out' => $bookingInfo['CheckoutDate'],
            //                         'canal' => $canal,
            //                         'net_amount' => $bookingInfo['Total']['NetAmount']['Value'],
            //                         'gross_amount' => $bookingInfo['Total']['GrossAmount']['Value'],
            //                         'tax' => $tax,
            //                         'clearning_fee' => $clearingFee,
            //                         'ota_commission' => $ota_comission_value,
            //                         'vacaloc_commission' => $vacaloc_commission_value,
            //                         'total_amount_order' => $totalOrderAmount,
            //                         'owner_profit' => $owner_profit,
            //                         'is_canceled' => $bookingInfo['Cancelled'],
            //                         'created_at' => $bookingInfo['BookedDateTime'],
            //                         'updated_at' => $bookingInfo['BookingUpdatedDateTime'],
            //                     ]);

            //                     Log::info('Booking created', [
            //                         'reference' => $bookingInfo['BookingReference'],
            //                         'order_reference' => $bookingInfo['OrderReference']
            //                     ]);
            //                 }

            //             } catch (\Exception $e) {
            //                 Log::error("Error inserting or updating booking for reference: " . $bookingInfo['BookingReference'] . " - " . $e->getMessage());
            //                 continue;
            //             }
            //             $this->info('Booking Fetched and stored successfully  ' . $bookingInfo['BookingReference']);
            //         }
            //     }
            //     $this->info('✅ Completed fetching bookings for month=' . $month . ', year=' . $year . ' at ' . now()->toDateTimeString());
            //     Log::info("Bookings fetched and stored successfully.");
            // } catch (\Exception $e) {
            //     Log::error('General error fetching bookings: ' . $e->getMessage());
            //     $this->error('Failed to fetch bookings.');
            // }
        }
    }
}