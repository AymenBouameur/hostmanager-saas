<?php
namespace Majormedia\Eviivo\Console;

use RainLab\User\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use MajorMedia\Listings\Models\Listing;
use MajorMedia\Eviivo\Classes\DashboardApi;

/**
 * FetchPropertyContact Command
 *
 * @link https://docs.octobercms.com/3.x/extend/console-commands.html
 */
class FetchPropertyContact extends Command
{
    protected $signature = 'fetch:eviivo-properties-contact';
    protected $description = 'Fetch Properties Contact from Eviivo API and store them in the database';

    public function handle()
    {
        $api = new DashboardApi();

        try {
            $properties = Listing::whereNotNull('shortName')
                ->where('shortName', '!=', '')
                ->pluck('shortName')
                ->toArray();

            foreach ($properties as $property) {
                try {
                    // Fetch property contacts
                    $propertyContact = $api->request('get', "/v3/property/{$property}/property-contacts");

                    if (empty($propertyContact['Contacts'])) {
                        Log::warning("No contact found for property: {$property}");
                        continue;
                    }

                    $usersToAttach = [];

                    foreach ($propertyContact['Contacts'] as $contactData) {
                        $contactInfo = $contactData ?? null;

                        if (!$contactInfo || empty($contactInfo['Email'])) {
                            Log::warning("Missing or invalid contact info for property: {$property}");
                            continue;
                        }

                        // Check if user exists, otherwise create
                        $password = bcrypt('password');
                        $user = User::firstOrCreate(
                            ['email' => $contactInfo['Email']],
                            [
                                'name' => $contactInfo['FirstName'],
                                'surname' => $contactInfo['LastName'],
                                'username' => $contactInfo['Email'],
                                'phone' => $contactInfo['Telephone'],
                                'password' => $password,
                                'password_confirmation' => $password,
                            ]
                        );
                        $usersToAttach[$property] = $user->id;
                    }

                    if (!empty($usersToAttach)) {
                        Listing::where('shortName', $property)->update(['user_id' => $user->id]);
                    }

                } catch (\Exception $e) {
                    Log::error("Error fetching contact for property {$property}: " . $e->getMessage());
                    $this->error("Error fetching contact for property: {$property}");
                }
            }

            $this->info("Property contacts fetched and linked successfully.");

        } catch (\Exception $e) {
            Log::error("Error fetching properties: " . $e->getMessage());
            $this->error("Error fetching properties: " . $e->getMessage());
        }
    }

}
