<?php
namespace Majormedia\UserPlus\Http;

use Backend\Classes\Controller;
use Stripe\StripeClient;
use Exception;
use Request;

class Cards extends Controller
{
    use \Majormedia\ToolBox\Traits\RetrieveUser;
    public function index()
    {
        $this->retrieveUser();

        try {
            $defaultPaymentMethod = $this->user->defaultPaymentMethod;
            $userCards = $this->user->cards;
            $userCards = array_map(fn($card) => [
                'id' => $card->id,
                'brand' => $card->card->brand,
                'last4' => $card->card->last4,
                'default' => $card->id == $defaultPaymentMethod, 
            ], $userCards);

        } catch (Exception $ex) {
            return $this->JsonAbort(
                [
                    'status' => 'error',
                    'message' => $ex->getMessage()
                ],
                400
            );
        }
        
        if (!empty($is_default = Request::input('is_default')) && $is_default == 1) {
            $userCards = array_filter($userCards, fn($card) => $card['default'] === true);
            return $this->JsonAbort([
                'status' => 'success',
                'data' => reset($userCards)
            ], 200);
        }
        return $this->JsonAbort([
            'status' => 'success',
            'data' => $userCards
        ], 200);
        
    }


    public function update(string $card)
    {
        $this->retrieveUser();

        $stripe = new StripeClient(env('STRIPE_API_SECRET'));

        try {
            $customer = $stripe->customers->update($this->user->stripe_customer_id, [
                'invoice_settings' => [
                    'default_payment_method' => $card
                ]
            ]);
            // unexpected error
        } catch (Exception $ex) {
            return $this->JsonAbort([
                'status' => 'error',
                'error' => "this card doesn't exist",
                'code' => 10215,
            ], 400);
        }

        return $this->JsonAbort([
            'status' => 'success',
            'new_default' => $customer->invoice_settings->default_payment_method,
        ], 200);
    }
    public function store()
    {
        $this->retrieveUser();
        try {
            $stripe = new StripeClient(env('STRIPE_API_SECRET'));

            $setupIntent = $stripe->setupIntents->create([
                'customer' => $this->user->stripe_customer_id,
            ]);
            return [
                'setup_intent' => $setupIntent->id,
                'client_secret' => $setupIntent->client_secret,
            ];
        } catch (Exception $ex) {
            return $this->JsonAbort();
        }
        return $this->JsonAbort([
            'status' => 'success',
            'redirect' => $checkout->url
        ], 200);
    }

    public function destroy(string $id)
    {
        $this->retrieveUser();

        $stripe = new StripeClient(env('STRIPE_API_SECRET'));

        try {
            // if the payment method doesn't belong to the customer return error
            $paymentMethod = $stripe->paymentMethods->retrieve($id);
            if ($paymentMethod->customer != $this->user->stripe_customer_id)
                $this->JsonAbort([
                    'status' => 'error',
                    'error' => 'forbidden action',
                ], 403);

            // detach the payment method
            $stripe->paymentMethods->detach($id);
        } catch (Exception $ex) {
            return $this->JsonAbort();
        }
        return $this->JsonAbort([
            'status' => 'success',
        ], 200);
    }
}
