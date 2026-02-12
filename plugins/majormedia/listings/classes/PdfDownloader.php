<?php
namespace MajorMedia\Listings\Classes;
class PdfDownloader
{
    public function download($pdf, $fileName)
    {
        return $pdf->download($fileName);
    }
}
