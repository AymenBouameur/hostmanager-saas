<?php
namespace Majormedia\Eviivo\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use MajorMedia\Listings\Models\Listing;
use MajorMedia\Eviivo\Classes\DashboardApi;

/**
 * FetchEviivoProperties Command
 *
 * @link https://docs.octobercms.com/3.x/extend/console-commands.html
 */
class FetchEviivoProperties extends Command
{
    protected $signature = 'fetch:eviivo-properties';
    protected $description = 'Fetch properties from Eviivo API and store them in the database';

    public function handle()
    {
        \Log::info('Start fetching Eviivo properties at ' . now());
        $this->info('🔍 Beginning Eviivo properties fetch process at ' . now()->format('Y-m-d H:i:s'));

        try {
            $api = new DashboardApi();
            $page = 0;
            $allProperties = [];

            do {
                $this->info("Fetching page: $page");
                \Log::info("Fetching Eviivo properties page: $page");

                $response = $api->request('get', '/v2/property/available', [
                    'page' => $page
                ]);

                $properties = $response['Results'] ?? [];

                if (!empty($properties)) {
                    $allProperties = array_merge($allProperties, $properties);
                    $page++;
                } else {
                    break; 
                }

            } while (!empty($properties));

            if (empty($allProperties)) {
                $this->error('No properties available.');
                return;
            }

            $listingsData = [];

            foreach ($allProperties as $property) {
                try {
                    $details = $api->request('get', "/v2/property/$property/overview");

                    if (!isset($details['Name'], $details['Addresss'], $details['PropertyStatus'])) {
                        Log::warning("Missing details for property: $property");
                        continue;
                    }

                    $isActive = $details['PropertyStatus'] === 'Active' ? 1 : 0;

                    if ($isActive == 1) {
                        $listingsData[] = [
                            'shortName' => $details['ShortName'],
                            'title' => $details['Name'],
                            'address' => $details['Addresss']['AddressLine1'] . ' ' . $details['Addresss']['City'] . ', ' . $details['Addresss']['PostCode'],
                            'is_active' => $isActive,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    }

                } catch (\Exception $e) {
                    Log::error("Error fetching property details for $property: " . $e->getMessage());
                    continue;
                }
            }

            if (!empty($listingsData)) {
                foreach ($listingsData as $data) {
                    Listing::updateOrCreate(
                        ['shortName' => $data['shortName']],
                        $data
                    );
                    $this->info('Properties Fetched And Stored Successfully  ' . $data['shortName']);
                }
            } else {
                $this->info('No properties to update or create.');
            }

            $this->info('🏁 Eviivo properties fetch completed successfully on ' . now()->format('Y-m-d H:i:s'));
            Log::info('Properties fetched and stored successfully.');

        } catch (\Exception $e) {
            Log::error('Error fetching properties: ' . $e->getMessage());
            $this->error('Failed to fetch properties.');
        }
    }


}
