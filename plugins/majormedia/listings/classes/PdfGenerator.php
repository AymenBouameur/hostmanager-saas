<?php
namespace MajorMedia\Listings\Classes;

use MajorMedia\Listings\Models\Listing;

use Barryvdh\DomPDF\Facade\Pdf as PDF;
class PdfGenerator
{
    public function generateForDownload($data)
    {
        $pdf = PDF::loadView('majormedia.listings::pdf.report', $data);
        $pdf->setOption([
            'fontDir' => storage_path('fonts'),
            'fontCache' => storage_path('fonts/cache'),
            'defaultFont' => 'Jost',
            'isHtml5ParserEnabled' => true,
            'isPhpEnabled' => true,
            'defaultPaperSize' => 'A4',
            'defaultPaperOrientation' => 'portrait',
            'dpi' => 96,
        ]);
        return $pdf;
    }

    public function generateForSave($data)
    {
        $pdf = PDF::loadView('majormedia.listings::pdf.report', $data);
        $pdf->setOption([
            'fontDir' => storage_path('fonts'),
            'fontCache' => storage_path('fonts/cache'),
            'defaultFont' => 'Jost',
            'isHtml5ParserEnabled' => true,
            'isPhpEnabled' => true,
            'defaultPaperSize' => 'A4',
            'defaultPaperOrientation' => 'portrait',
            'dpi' => 96,
        ]);
        return $pdf;
    }
}
