<?php
namespace Majormedia\Eviivo\Http;

use Illuminate\Http\Request;
use Backend\Classes\Controller;
use Illuminate\Support\Facades\Log;

/**
 * Webhook Back-end Controller
 */
class Webhook extends Controller
{
    // Your shared secret from eviivo
    private $sharedSecret = 'Cxan93kXh3tvmgUxnvFq89QobZ20RWucvN4a5JqmnBUxNAzgfARQVxZmDLZ9ltQS';

    public function handle(Request $request)
    {
        // Retrieve headers
        $headerSignature = $request->header('x-wh-sig-header');
        $bodySignature = $request->header('x-wh-sig-body');
        $event = $request->header('x-wh-event');
        $requestId = $request->header('x-wh-requestid');
        $timestamp = $request->header('x-wh-timestamp');
        $tracingId = $request->header('x-wh-tracingid');
        $body = $request->getContent();

        // Calculate the expected header signature
        $calculatedHeaderSignature = $this->calculateHeaderSignature($event, $requestId, $timestamp, $tracingId);

        // Validate the header signature
        if ($calculatedHeaderSignature !== $headerSignature) {
            Log::error("Invalid header signature for event: $event");
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        // Optionally validate body signature (if implemented)
        $calculatedBodySignature = $this->calculateBodySignature($calculatedHeaderSignature, $body);
        if ($bodySignature && $calculatedBodySignature !== $bodySignature) {
            Log::error("Invalid body signature for event: $event");
            return response()->json(['error' => 'Invalid body signature'], 400);
        }

        switch ($event) {
            case 'pricing-rate-and-restriction':
                $this->handlePricingRateAndRestriction(json_decode($body, true));
                break;

            default:
                Log::warning("Unhandled event type: $event");
                break;
        }

        return response()->json(['status' => 'success'], 200);
    }

    private function calculateHeaderSignature($event, $requestId, $timestamp, $tracingId)
    {
        $headerComponents = "v1:eviivo:$event:$requestId:$timestamp:$tracingId";
        return 'v1.' . hash_hmac('sha256', $headerComponents, $this->sharedSecret);
    }

    private function calculateBodySignature($headerSignature, $body)
    {
        $bodyComponents = $headerSignature . $body;
        return hash_hmac('sha256', $bodyComponents, $this->sharedSecret);
    }

    private function handlePricingRateAndRestriction($data)
    {
        Log::info('Handling pricing-rate-and-restriction event', $data);
    }

}
