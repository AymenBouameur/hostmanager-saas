<?php
namespace Majormedia\Stripe;

use Stripe\StripeClient;
use System\Classes\PluginBase;
use Backend\Models\User as BackendUser;
use Illuminate\Support\Facades\DB;
use Majormedia\Stripe\Http\Webhook;
use MajorMedia\Billing\Models\Subscription;
use MajorMedia\Billing\Models\Payment;

class Plugin extends PluginBase
{
    public $require = ['MajorMedia.Companies', 'MajorMedia.Billing'];

    public function pluginDetails()
    {
        return [
            'name'        => 'Stripe',
            'description' => 'Stripe payment integration for SaaS subscriptions',
            'author'      => 'MajorMedia',
            'icon'        => 'icon-cc-stripe'
        ];
    }

    public function boot()
    {
        $this->extendBackendUserModel();
        $this->addWebhookListeners();
    }

    protected function extendBackendUserModel()
    {
        BackendUser::extend(function ($model) {
            $model->addFillable(['stripe_customer_id']);

            $model->addDynamicMethod('getOrCreateStripeCustomer', function () use ($model) {
                if ($model->stripe_customer_id) {
                    return $model->stripe_customer_id;
                }

                $stripe = new StripeClient(config('majormedia.stripe::stripe.secret'));

                $customer = $stripe->customers->create([
                    'email' => $model->email,
                    'name'  => trim($model->first_name . ' ' . $model->last_name),
                    'metadata' => [
                        'backend_user_id' => $model->id,
                        'company_id'      => $model->company_id ?? null,
                    ],
                ]);

                $model->stripe_customer_id = $customer->id;
                $model->save();

                return $customer->id;
            });
        });
    }

    private function addWebhookListeners()
    {
        // Successful subscription payment
        Webhook::addEventHandler('invoice.payment_succeeded', function ($invoice) {
            DB::beginTransaction();
            try {
                $subscriptionId = $invoice->metadata->subscription_id ?? null;
                $companyId = $invoice->metadata->company_id ?? null;

                if (!$subscriptionId || !$companyId) {
                    \Log::warning('Stripe webhook invoice.payment_succeeded missing metadata', [
                        'invoice_id' => $invoice->id,
                    ]);
                    DB::commit();
                    return;
                }

                $subscription = Subscription::find($subscriptionId);
                if (!$subscription) {
                    \Log::error('Subscription not found', ['subscription_id' => $subscriptionId]);
                    DB::commit();
                    return;
                }

                // Activate subscription
                $subscription->status = 2; // Active
                $subscription->save();

                // Record the payment
                Payment::create([
                    'company_id'           => $companyId,
                    'subscription_id'      => $subscriptionId,
                    'invoice_number'       => $invoice->number ?? $invoice->id,
                    'amount'               => $invoice->amount_paid / 100,
                    'tax'                  => ($invoice->tax ?? 0) / 100,
                    'total'                => $invoice->total / 100,
                    'status'               => 2, // Paid
                    'billing_period_start' => date('Y-m-d', $invoice->period_start),
                    'billing_period_end'   => date('Y-m-d', $invoice->period_end),
                    'paid_at'              => now(),
                ]);

                DB::commit();

                \Log::info('Subscription payment recorded', [
                    'subscription_id' => $subscriptionId,
                    'invoice'         => $invoice->id,
                ]);

            } catch (\Exception $e) {
                DB::rollback();
                \Log::error('Exception in invoice.payment_succeeded webhook', [
                    'error'   => $e->getMessage(),
                    'invoice' => $invoice->id,
                ]);
            }
        });

        // Failed payment — mark subscription as past due
        Webhook::addEventHandler('invoice.payment_failed', function ($invoice) {
            $subscriptionId = $invoice->metadata->subscription_id ?? null;
            if (!$subscriptionId) return;

            $subscription = Subscription::find($subscriptionId);
            if ($subscription) {
                $subscription->status = 3; // Past Due
                $subscription->save();
            }

            \Log::warning('Subscription payment failed', [
                'subscription_id' => $subscriptionId,
                'invoice'         => $invoice->id,
            ]);
        });
    }
}
