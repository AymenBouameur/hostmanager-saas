<?php
namespace Majormedia\Eviivo\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use MajorMedia\Bookings\Models\Booking;
use MajorMedia\Listings\Models\Listing;
use MajorMedia\Eviivo\Classes\DashboardApi;
use Majormedia\InCore\Models\Settings;
use Illuminate\Bus\Batchable;


class ProcessEviivoPropertyBookings implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, Batchable;


    protected $property;
    protected $stayFrom;
    protected $stayTo;
    protected $monthKey;

     public function __construct($property, $stayFrom, $stayTo, $monthKey)
    {
        $this->property = $property;
        $this->stayFrom = $stayFrom;
        $this->stayTo = $stayTo;
        $this->monthKey = $monthKey;
    }

    public function handle()
    {

        if ($this->batch() && $this->batch()->cancelled()) {
            return;
        }
        $property = $this->property;
        $api = new DashboardApi();

        try {
            $bookingsResponse = $api->request('get', "v2/property/$property/bookings", [
                'stayFrom' => $this->stayFrom,
                'stayTo' => $this->stayTo,
            ]);

            if (!isset($bookingsResponse['Bookings'])) {
                Log::warning("No bookings found for property $property");
                return;
            }

            foreach ($bookingsResponse['Bookings'] as $bookingData) {
                $bookingInfo = $bookingData['Booking'] ?? null;
                if (!$bookingInfo) continue;

                $orderReference = $bookingInfo['OrderReference'] ?? null;
                if (!$orderReference) continue;

                // 🎯 Récupération des détails de la réservation et ordre
                $orderResponse = $api->request('get', "v2/property/$property/orders/$orderReference");
                $totalOrderAmount = $orderResponse['TotalAmount']['Value'] ?? 0;

                $bookingDetails = $api->request('get', "v2/property/$property/bookings/bookingref/$bookingInfo[BookingReference]");
                $tax = 0;
                if (!isset($bookingDetails['Booking']['Total']['Taxes'])) {
                    $tax = $bookingInfo['Total']['NetAmount']['Value'] - $bookingInfo['Total']['GrossAmount']['Value'];
                } else {
                    foreach ($bookingDetails['Booking']['Total']['Taxes'] as $taxes) {
                        if ($taxes['TaxTypeCode'] == 'TAX') {
                            $tax = $taxes['Amount']['Value'] ?? 0;
                        }
                    }
                }

                $clearingFee = 0;
                if (array_key_exists('GuestCharges', $bookingDetails)) {
                    foreach ($bookingDetails['GuestCharges'] as $charge) {
                        if ($charge['ChargeTypeCode'] == 'CLEANING_FEE') {
                            $clearingFee = $charge['GrossAmount']['Value'] ?? 0;
                        }
                    }
                }

                $listing = Listing::where('shortName', $property)->first();
                if (!$listing) continue;

                // Canal & commissions
                $canal = 1;
                $ota_comission = 0;
                $ota_comission_value = 0;

                switch ($bookingInfo['OTAShortname']) {
                    case 'Airbnb':
                        $canal = 2;
                        $ota_comission = Settings::get('airbnb_commission') ?? 15;
                        break;
                    case 'booking_com':
                        $canal = 3;
                        $ota_comission = Settings::get('booking_commission') ?? 17;
                        break;
                    case 'Vrbo':
                    case 'HomeAwayInc':
                        $canal = 4;
                        $ota_comission = Settings::get('vrbo_commission') ?? 15;
                        break;
                }

                $ota_comission_value = ($bookingInfo['Total']['NetAmount']['Value'] * $ota_comission) / 100;

                $vacaloc_commission = $listing->commission ?? Settings::get('vacaloc_commission') ?? 22;
                $vacaloc_commission_value = (($bookingInfo['Total']['NetAmount']['Value'] - $clearingFee) * $vacaloc_commission) / 100;

                $owner_profit = $bookingInfo['Total']['NetAmount']['Value'] - ($clearingFee + $vacaloc_commission_value + $ota_comission_value);

                Booking::updateOrCreate(
                    ['reference' => $bookingInfo['BookingReference']],
                    [
                        'order_reference' => $orderReference,
                        'listing_id' => $listing->id,
                        'customer' => $bookingData['Guests'][0]['FirstName'] . ' ' . $bookingData['Guests'][0]['Surname'],
                        'check_in' => $bookingInfo['CheckinDate'],
                        'check_out' => $bookingInfo['CheckoutDate'],
                        'canal' => $canal,
                        'net_amount' => $bookingInfo['Total']['NetAmount']['Value'],
                        'gross_amount' => $bookingInfo['Total']['GrossAmount']['Value'],
                        'tax' => $tax,
                        'cleaning_fee' => $clearingFee,
                        'ota_commission' => $ota_comission_value,
                        'vacaloc_commission' => $vacaloc_commission_value,
                        'total_amount_order' => $totalOrderAmount,
                        'owner_profit' => $owner_profit,
                        'is_canceled' => $bookingInfo['Cancelled'],
                        'created_at' => $bookingInfo['BookedDateTime'],
                        'updated_at' => $bookingInfo['BookingUpdatedDateTime'],
                    ]
                );
            }

        } catch (\Exception $e) {
            Log::error("Error in booking job for property $property: " . $e->getMessage());
        }
    }
}
