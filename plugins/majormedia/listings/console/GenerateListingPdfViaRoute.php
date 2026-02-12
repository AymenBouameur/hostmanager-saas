<?php
namespace Majormedia\Listings\Console;

use parallel\Runtime;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use MajorMedia\Listings\Models\Listing;
use MajorMedia\Listings\Jobs\GeneratePdfJob;
/**
 * GenerateListingPdfViaRoute Command
 *
 * @link https://docs.octobercms.com/3.x/extend/console-commands.html
 */
class GenerateListingPdfViaRoute extends Command
{
    protected $signature = 'generate:listing-pdf-via-route';
    protected $description = 'Generate PDF for each listing by hitting the route';
    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $listings = Listing::all();

        foreach ($listings as $listing) {
            $this->info('Start Pdf generate for listing ID: ' . $listing->id);
            dispatch(new GeneratePdfJob($listing->id));
        }
        $this->info('PDF generation jobs dispatched for all listings.');
    }
}
