<?php
namespace Majormedia\Listings\Console;


use Carbon\Carbon;
use System\Models\File;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Spatie\Browsershot\Browsershot;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use Majormedia\InCore\Models\Settings;
use MajorMedia\Bookings\Models\Booking;
use MajorMedia\Listings\Models\Invoice;
use MajorMedia\Listings\Models\Listing;
/**
 * GenerateInvoiceViaCommand Command
 *
 * @link https://docs.octobercms.com/3.x/extend/console-commands.html
 */
class GenerateTestingPdfCommand extends Command
{
    protected $signature = 'listings:generateTestingPdf';
    protected $description = 'Generate a PDF Invoice for each testing, showing data for each month in the current year.';

    public function handle()
    {
        $coverLogoPath = \Media\Classes\MediaLibrary::url('logos/logo.png');
        $placeHolderPath = \Media\Classes\MediaLibrary::url('logos/placeholder.png');
        $greenShapePath = \Media\Classes\MediaLibrary::url('logos/green-shape.png');

        //page_one 
        $logoPageOne = \Media\Classes\MediaLibrary::url('logos/logo_page_one.png');
        $logoPageTwo = \Media\Classes\MediaLibrary::url('logos/logo_page_two.png');
        $image1 = \Media\Classes\MediaLibrary::url('images/image1.png');
        $image2 = \Media\Classes\MediaLibrary::url('images/image2.png');
        $image3 = \Media\Classes\MediaLibrary::url('images/image3.png');
        $image4 = \Media\Classes\MediaLibrary::url('images/image4.png');
        
        $cover = view('majormedia.listings::test.cover', [
            'address' => "63 ESPLANADE DU BELVEDERE<br>92130, ISSY LES MOULINEAUX",
            'surface' => "109,5 M²",
            'type' => "LOCAL COMMERCIAL À USAGE DE PHARMACIE",
            'logo_path' => $coverLogoPath,
            'place_holder_path' => $placeHolderPath,
            'green_shape_path' => $greenShapePath,
        ])->render();

        $pageOne = view('majormedia.listings::test.page_one', [
            'address' => "63 ESPLANADE DU BELVEDERE<br>92130, ISSY LES MOULINEAUX",
            'surface' => "109,5 M²",
            'type' => "LOCAL COMMERCIAL À USAGE DE PHARMACIE",
            'logo_page_one' => $logoPageOne,
            'place_holder_path' => $placeHolderPath,
            'green_shape_path' => $greenShapePath,
        ])->render();

        $pageTwo = view('majormedia.listings::test.page_two', [
            'address' => "63 ESPLANADE DU BELVEDERE<br>92130, ISSY LES MOULINEAUX",
            'surface' => "109,5 M²",
            'type' => "LOCAL COMMERCIAL À USAGE DE PHARMACIE",
            'logo_page_two' => $logoPageTwo,
            'image1' => $image1,
            'image2' => $image2,
            'image3' => $image3,
            'image4' => $image4,
        ])->render();

        $directory = storage_path("app/public/testingpdf");

        if (!file_exists($directory)) {
            mkdir($directory, 0755, true);
        }

        $path = $directory . '/cover.pdf';
        $path = $directory . '/page_one.pdf';
        $page_two = $directory . '/page_two.pdf';

        Browsershot::html($pageTwo)
            ->format('A4')
            ->showBackground()
            ->save($page_two);

        return $page_two;
    }


}
