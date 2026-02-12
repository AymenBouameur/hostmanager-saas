<?php namespace MajorMedia\Stripe\Services;

use Stripe\StripeClient;
use October\Rain\Support\Facades\Config;

class PaymentService
{
    protected $stripe;

    public function __construct()
    {
        $this->stripe = new StripeClient(Config::get('majormedia.stripe::stripe.secret'));
    }

    /**
     * Create a PaymentIntent for a subscription payment.
     *
     * @param \Backend\Models\User $backendUser  The admin paying for the company
     * @param \MajorMedia\Billing\Models\Subscription $subscription
     * @param float $amount
     * @return \Stripe\PaymentIntent
     */
    public function createSubscriptionPaymentIntent($backendUser, $subscription, $amount)
    {
        $stripeCustomerId = $backendUser->getOrCreateStripeCustomer();

        return $this->stripe->paymentIntents->create([
            'amount'   => (int) ($amount * 100),
            'currency' => Config::get('majormedia.stripe::stripe.currency', 'eur'),
            'customer' => $stripeCustomerId,
            'metadata' => [
                'backend_user_id' => $backendUser->id,
                'company_id'      => $backendUser->company_id,
                'subscription_id' => $subscription->id,
            ],
        ]);
    }

    /**
     * Retrieve a Stripe customer by ID.
     */
    public function getCustomer($stripeCustomerId)
    {
        return $this->stripe->customers->retrieve($stripeCustomerId);
    }
}
