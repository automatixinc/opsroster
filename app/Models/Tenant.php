<?php

declare(strict_types=1);

namespace App\Models;

use App\BillingManager;
use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;
use Stancl\Tenancy\Database\Concerns\HasDatabase;
use Stancl\Tenancy\Database\Concerns\HasPending;
use Laravel\Cashier\Billable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Stancl\Tenancy\Database\Contracts\TenantWithDatabase;
use App\Exceptions\EmailOccupiedException;
use Illuminate\Support\Carbon;
use App\Models\Domain;
use App\Exceptions\NoPrimaryDomainException;
use Stancl\Tenancy\Database\Concerns\HasDomains;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property-read Domain|null $primary_domain
 * @property-read Domain|null $fallback_domain
 * @property-read bool $on_active_subscription
 * @property-read bool $can_use_app
 * @property-read null|true $ready
 * @property-read string $password
 * @property-read string $company
 * @property-read string $tenancy_db_name
 * @property-read Carbon $trial_ends_at
 */
class Tenant extends BaseTenant implements TenantWithDatabase
{
    use HasDatabase,
        HasDomains,
        HasPending,
        Billable,
        HasFactory;

    protected $casts = [
        'trial_ends_at' => 'datetime',
    ];

    public static function booted(): void
    {
        static::updating(function (self $tenant) {
            if (array_key_exists('email', $tenant->getChanges()) &&
                static::where('email', $tenant->email)
                      ->where('id', '!=', $tenant->id)
                      ->exists()) {
                throw new EmailOccupiedException();
            }
        });

        static::updated(function (self $model) {
            if ($model->ready && array_key_exists('email', $model->getChanges())) {
                if ($model->hasStripeId()) {
                    $model->updateStripeCustomer([
                        'email' => $model->email,
                    ]);
                }

                $model->run(function ($tenant) {
                    $user = User::first();
                    $user::withoutEvents(fn () => $user->update(['email' => $tenant->email]));
                });
            }
        });
    }

    public static function getCustomColumns(): array
    {
        return [
            'id',
            'email',
            'stripe_id',
            'pm_type',
            'pm_last_four',
            'trial_ends_at',
        ];
    }

    public function primary_domain(): HasOne
    {
        return $this->hasOne(Domain::class)->where('is_primary', true);
    }

    public function fallback_domain(): HasOne
    {
        return $this->hasOne(Domain::class)->where('is_fallback', true);
    }

    public function route($route, $parameters = [], $absolute = true): string
    {
        $domain = $this->primary_domain?->domain;

        if(! $domain) {
            throw new NoPrimaryDomainException;
        }

        $parts = explode('.', $domain);

        if (count($parts) === 1) { // If subdomain
            $domain = Domain::domainFromSubdomain($domain);
        }

        return tenant_route($domain, $route, $parameters);
    }

    public function impersonationUrl(int $user_id, string $routeName = 'tenant.dashboard'): string
    {
        $token = tenancy()->impersonate($this, $user_id, $this->route($routeName), 'web')->token;

        return $this->route('tenant.impersonate', ['token' => $token]);
    }

    /**
     * Get the tenant's subscription plan name.
     */
    public function getPlanNameAttribute(): string|null
    {
        if ($subscription = $this->subscription()) {
            return config('saas.plans')[$subscription->stripe_price];
        }

        return null;
    }

    /**
     * Is the tenant actively subscribed (not on grace period).
     */
    public function getOnActiveSubscriptionAttribute(): bool
    {
        return $this->subscribed() && ! $this->subscription()->canceled();
    }

    /**
     * Can the tenant use the application (is on trial or subscription).
     */
    public function getCanUseAppAttribute(): bool
    {
        return $this->onTrial() || $this->subscribed();
    }

    public function getAdmin(): User|null
    {
        return $this->run(fn () => User::first());
    }

    public function getCreditBalance(bool $returnFormatted = true, bool $uppercaseCurrency = false): array|string
    {
        $tenantCanUseStripe = BillingManager::tenantCanUseStripe($this);

        $latestTransaction = $tenantCanUseStripe ? $this->balanceTransactions()->first() : null;
        $amount = $latestTransaction?->ending_balance ?? 0;
        $currency = $latestTransaction?->currency ?? config('cashier.currency');

        // The ending balance of the latest transaction shows the tenant's *balance*.
        // If the balance is positive, the tenant owes us money (= accrued subscription
        // cost). If the balance is negative, they have an extra credit applied that
        // will be used to cover their future balance.
        // Therefore, the balance is essentially opposite of any applied credit, and as
        // such we negate (*(-1)) the value when showing the tenant's remaining credit.
        $amount *= -1;
        $formattedBalance = number_format($amount / 100, 2) . ' ' . strtoupper($currency);

        if ($returnFormatted) {
            return $formattedBalance;
        }

        return [
            'amount' => $amount,
            'currency' => $uppercaseCurrency ? strtoupper($currency) : $currency,
            'formatted' => $formattedBalance,
        ];
    }
}
