<?php

declare(strict_types=1);

namespace App;

use App\Models\Tenant;
use Carbon\Carbon;
use TypeError;
use Laravel\Cashier\Invoice;

class BillingManager
{
    public static function stripeKeySet(): bool
    {
        return (bool) config('saas.stripe_key');
    }

    public static function tenantHasStripeId(Tenant $tenant): bool
    {
        return $tenant->hasStripeId();
    }

    public static function tenantCanUseStripe(Tenant $tenant): bool
    {
        return static::stripeKeySet() && static::tenantHasStripeId($tenant);
    }

    /**
     * The $address parameter contains validated address data.
     * The keys that should be present are:
     * - line1
     * - line2
     * - city
     * - country
     * - postal_code
     * - state
     */
    public static function updateAddress(Tenant $tenant, array $address): void
    {
        $tenant->updateStripeCustomer(['address' => $address]);
    }

    public static function adjustCredit(Tenant $tenant, float $amount): void
    {
        $amount = (int) ($amount * 100);

        if ($amount > 0) {
            $tenant->creditBalance($amount, 'Credit added by admin');
        } else {
            $tenant->debitBalance(abs($amount), 'Credit removed by admin');
        }
    }

    public static function formatInvoices(array $invoices): array
    {
        return array_map(fn (Invoice $invoice) => [
            'id' => $invoice->id,
            'number' => $invoice->number,
            'paid' => $invoice->isPaid(),
            'date' => static::formatDate($invoice->date()),
            'total' => $invoice->total(),
            'download' => route('tenant.invoice.download', $invoice->id),
        ], $invoices);
    }

    public static function billingAddressValidationRules(): array
    {
        return [
            'line1' => 'nullable|string',
            'city' => 'nullable|string',
            'country' => 'nullable|string|size:2',
            'line2' => 'nullable|string',
            'postal_code' => 'nullable|numeric',
            'state' => 'nullable|string',
        ];
    }

    public static function creditBalanceValidationRules(): array
    {
        return ['amount' => 'required|numeric'];
    }

    protected static function getCreditCardInfo(Tenant $tenant): array|null
    {
        $paymentMethod = $tenant->hasDefaultPaymentMethod() ? $tenant->defaultPaymentMethod() : null;

        if ($paymentMethod) {
            $card = $paymentMethod->asStripePaymentMethod()->card;

            return [
                'brand' => ucfirst($card->brand),
                'last4' => $card->last4,
            ];
        }

        return null;
    }

    protected static function getPaymentMethodMessage(Tenant $tenant): string
    {
        if (! static::stripeKeySet()) {
            return "No Stripe keys set. Set your Stripe keys to enable payment processing.";
        }

        if (! static::tenantHasStripeId($tenant)) {
            return "Stripe customer doesn't exist. Create it using \$tenant->createAsStripeCustomer(), or create a new tenant.";
        }

        if ($creditCardInfo = static::getCreditCardInfo($tenant)) {
            return "{$creditCardInfo['brand']} ending in {$creditCardInfo['last4']}";
        }

        return "No payment method set yet. Please add one below.";
    }

    protected static function formatDate(Carbon|null $date): array
    {
        return [
            'datetime' => $date?->format('Y-m-d'),
            'text' => $date?->format('M d, Y'),
        ];
    }

    public static function getSubscriptionBannerProps(Tenant $tenant): array
    {
        $subscription = $tenant->subscription();
        $activeSubscription = false;

        if (! static::stripeKeySet()) {
            $htmlMessage = "No Stripe keys have been set.";
        } else if (! static::tenantHasStripeId($tenant)) {
            $htmlMessage = "Stripe customer doesn't exist. Create it using \$tenant->createAsStripeCustomer(), or create a new tenant.";
        } else if (! $tenant->subscribed()) {
            // Not subscribed
            if ($tenant->onGenericTrial()) {
                $endsAt = static::formatDate($tenant->trial_ends_at);

                $htmlMessage = "You're on trial until <time datetime=\"{$endsAt['datetime']}\">{$endsAt['text']}</time>,
                    <strong>but you haven't subscribed to any plan yet</strong>. Please do so now to continue using the application even after your trial ends.";
            } else {
                $htmlMessage = "You're <strong>not subscribed</strong>. If you wish to keep using the application, please <a href=\"#subscription\">choose a subscription plan below</a>.";
            }
        } else {
            // Subscribed
            if ($subscription?->onGracePeriod()) {
                $endsAt = static::formatDate($subscription->ends_at);

                $htmlMessage = "You're on a <strong>grace period</strong> until
                    <time datetime=\"{$endsAt['datetime']}\">{$endsAt['text']}</time>. If you wish to continue using the application after that date, please <strong>resubscribe</strong>.";
            } else {
                // Active subscription
                $activeSubscription = true;
                $htmlMessage = "You're subscribed to the <strong>{$tenant->plan_name}</strong> plan.";

                if ($subscription?->onTrial()) {
                    $endsAt = static::formatDate($subscription->trial_ends_at);

                    $htmlMessage .= " You're also on trial until <time datetime=\"{$endsAt['datetime']}\">({$endsAt['text']})</time>.";
                }
            }
        }

        return [
            'htmlMessage' => $htmlMessage,
            'activeSubscription' => $activeSubscription,
        ];
    }

    public static function getUpcomingPaymentProps(Tenant $tenant): array
    {
        try {
            $invoice = static::tenantCanUseStripe($tenant) ? $tenant->upcomingInvoice() : null;
        } catch (TypeError $th) {
            $invoice = null;
        }

        return [
            'upcomingInvoice' => $invoice,
            'planName' => $tenant->plan_name,
            'creditCard' => static::getCreditCardInfo($tenant),
            'invoiceTotal' => $invoice?->total(),
            'invoiceDate' => static::formatDate($invoice?->date()),
        ];
    }

    public static function getSubscriptionPlanProps(Tenant $tenant): array
    {
        $subscription = $tenant->subscription();

        return [
            'plans' => $plans = config('saas.plans'),
            'currentPlan' => [
                'price' => $price = $subscription?->stripe_price,
                'name' => $price ? $plans[$price] : null,
                'canceled' => $subscription?->canceled() ?? true
            ],
            'cancelationReasons' => config('saas.cancelation_reasons'),
            'subscribed' => $tenant->subscribed(),
            'onActiveSubscription' => $tenant->on_active_subscription,
            'tenantCanUseStripe' => static::tenantCanUseStripe($tenant),
        ];
    }

    public static function getPaymentMethodProps(Tenant $tenant): array
    {
        return [
            'paymentMethodMessage' => static::getPaymentMethodMessage($tenant),
            'tenantCanUseStripe' => $tenantCanUseStripe = static::tenantCanUseStripe($tenant),
            'intent' => $tenantCanUseStripe ? $tenant->createSetupIntent() : null,
            'stripeKey' => config('saas.stripe_key') ?? null,
        ];
    }

    /**
     * Example:
     * [
     *    'amount' => '2000',
     *    'currency' => 'USD',
     *    'formatted' => '20.00 USD',
     *    'tenantCanUseStripe' => true,
     * ]
     */
    public static function getCreditBalanceProps(Tenant $tenant): array
    {
        return [
            ...$tenant->getCreditBalance(returnFormatted: false, uppercaseCurrency: true),
            'tenantCanUseStripe' => static::tenantCanUseStripe($tenant),
        ];
    }
}
