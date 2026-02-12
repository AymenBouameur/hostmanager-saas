<?php
namespace MajorMedia\Listings\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;

class GeneratePdfJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $listingId;

    public function __construct($listingId)
    {
        $this->listingId = $listingId;
    }

    public function handle()
    {
        try {

            $baseUrl = config('app.url');
            $url = "{$baseUrl}/getApi/v1/endpoint/reports/generate-pdf/{$this->listingId}";

            $response = Http::get($url);

            if ($response->failed()) {
                \Log::error("PDF generation failed for listing ID {$this->listingId}. HTTP status: " . $response->status());
            }
        } catch (\Exception $e) {
            \Log::error("Error generating PDF for listing ID {$this->listingId}: " . $e->getMessage());
        }
    }
}
