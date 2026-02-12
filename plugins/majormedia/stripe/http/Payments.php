<?php namespace Majormedia\Stripe\Http;

use Exception;
use Backend\Classes\Controller;
use Backend\Models\User as BackendUser;
use Illuminate\Support\Facades\DB;
use October\Rain\Support\Facades\Config;
use MajorMedia\Stripe\Services\PaymentService;
use MajorMedia\Billing\Models\Subscription;
use MajorMedia\Billing\Models\PricingPlan;
use MajorMedia\Companies\Models\Company;
use Majormedia\ToolBox\Traits\GetValidatedInput;

class Payments extends Controller
{
    use GetValidatedInput;

    /**
     * Create a PaymentIntent for a subscription payment.
     * Called by backend_user (company admin) to pay for their subscription.
     *
     * POST /getApi/v1/endpoint/subscription/pay
     * Body: { subscription_id: int }
     */
    public function paySubscription()
    {
        DB::beginTransaction();
        try {
            $backendUser = BackendUser::find(post('backend_user_id'));
            if (!$backendUser || !$backendUser->company_id) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Backend user not found or not linked to a company',
                ], 400);
            }

            $subscription = Subscription::find(post('subscription_id'));
            if (!$subscription || $subscription->company_id !== $backendUser->company_id) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Subscription not found or does not belong to your company',
                ], 400);
            }

            // Determine amount based on billing cycle
            $plan = $subscription->pricing_plan;
            $amount = $subscription->billing_cycle === 2
                ? $plan->annual_price
                : $plan->monthly_price;

            if ($amount <= 0) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Invalid plan price',
                ], 400);
            }

            $paymentService = new PaymentService();
            $paymentIntent = $paymentService->createSubscriptionPaymentIntent(
                $backendUser,
                $subscription,
                $amount
            );

            DB::commit();

            return response()->json([
                'status'         => 'success',
                'payment_intent' => $paymentIntent->id,
                'client_secret'  => $paymentIntent->client_secret,
                'amount'         => $amount,
            ]);

        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Sync all backend_users with Stripe (create customer IDs).
     *
     * GET /getApi/v1/endpoint/sync-stripe-admins
     */
    public function syncStripeAdmins()
    {
        $stripe = new \Stripe\StripeClient(Config::get('majormedia.stripe::stripe.secret'));

        BackendUser::whereNotNull('company_id')
            ->whereNull('stripe_customer_id')
            ->chunk(50, function ($users) use ($stripe) {
                foreach ($users as $user) {
                    try {
                        $customer = $stripe->customers->create([
                            'email' => $user->email,
                            'name'  => trim($user->first_name . ' ' . $user->last_name),
                            'metadata' => [
                                'backend_user_id' => $user->id,
                                'company_id'      => $user->company_id,
                            ],
                        ]);

                        $user->stripe_customer_id = $customer->id;
                        $user->save();

                        \Log::info("Stripe customer synced: {$user->email}");
                    } catch (Exception $e) {
                        \Log::error("Stripe sync failed for {$user->email}: " . $e->getMessage());
                    }
                }
            });

        return response()->json(['status' => 'success', 'message' => 'Sync completed']);
    }
}
