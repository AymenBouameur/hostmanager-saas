<?php

namespace MajorMedia\Listings\Classes;

use Carbon\Carbon;
use System\Models\File;
use Illuminate\Support\Facades\Log;
use MajorMedia\Listings\Classes\PdfSaver;
use MajorMedia\Listings\Models\Statement;
use MajorMedia\Listings\Classes\PdfGenerator;
use MajorMedia\Listings\Classes\PdfDownloader;
use MajorMedia\Listings\Classes\PdfDataProvider;

class PdfGeneratorFacade
{
    protected $dataProvider;
    protected $generator;
    protected $saver;
    protected $downloader;

    public function __construct(
        PdfDataProvider $dataProvider,
        PdfGenerator $generator,
        PdfSaver $saver,
        PdfDownloader $downloader
    ) {
        $this->dataProvider = $dataProvider;
        $this->generator = $generator;
        $this->saver = $saver;
        $this->downloader = $downloader;
    }

    public function generateAndSavePdf($listingId, $startAt = null, $endAt = null)
    {
        // Step 1: Get data
        $data = $this->dataProvider->getData($listingId, $startAt, $endAt);
        $start = $data['startDate'] ?? null;
        $end = $data['endDate'] ?? null;

        $ymdPattern = '/^\d{4}-\d{2}-\d{2}$/';

        if (!preg_match($ymdPattern, $start)) {
            $start = Carbon::createFromFormat('d/m/Y', $start)->format('Y-m-d');
        }

        if (!preg_match($ymdPattern, $end)) {
            $end = Carbon::createFromFormat('d/m/Y', $end)->format('Y-m-d');
        }


        // Step 2: Generate PDF for saving (separate instance)
        $pdfForSave = $this->generator->generateForSave($data);

        // Step 3: Save PDF
        $pdfName = "releve_reservations_{$listingId}_{$start}_{$end}.pdf";
        $pdfPath = storage_path("app/public/pdfs/{$pdfName}");
        $this->saver->save($pdfForSave, $pdfPath);

        Log::info("PDF saved successfully.", [
            'listing_id' => $listingId,
            'pdf_path' => $pdfPath,
        ]);

        // Step 4: Create Statement and Save Document
        $statement = Statement::create([
            'statement_date' => Carbon::now(),
            'listing_id' => $listingId
        ]);
        $statement->document = (new File)->fromFile($pdfPath);
        $statement->save();

        // Step 5: Generate PDF for downloading (separate instance)
        $pdfForDownload = $this->generator->generateForDownload($data);

        // Step 6: Download PDF
        return $this->downloader->download($pdfForDownload, $pdfName);
    }
}

