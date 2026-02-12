<?php
namespace MajorMedia\Listings\Classes;
class PdfSaver
{
    public function save($pdf, $pdfPath)
    {
        file_put_contents($pdfPath, $pdf->output());
    }
}
