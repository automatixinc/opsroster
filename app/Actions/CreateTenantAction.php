<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Tenant;
use App\Models\User;
use Laravel\Jetstream\Jetstream;

/**
 * Create a tenant with the necessary information for the application.
 *
 * We don't use a listener here, because we want to be able to create "simplified" tenants in tests.
 * This action is only used when we need to create the tenant properly (with billing logic etc).
 */
class CreateTenantAction
{
    public function __invoke(
        array $data,
        string|null $domain = null,
        bool $createStripeCustomer = true
    ): Tenant
    {
        if (app()->runningUnitTests() && env('DB_DATABASE') === ':memory:') {
            // Tenancy substitutes this with a *named* in-memory database
            $data['tenancy_db_name'] = ':memory:';
        }

        /** @var Tenant $tenant */
        $tenant = Tenant::pullPending(array_merge($data, [
            'ready' => false,
            'trial_ends_at' => now()->addDays(config('saas.trial_days')),
        ]));

        // Populate the dummy tenant admin user with actual data
        // Only has an effect if a pending tenant was used
        $tenant->run(function ($tenant) use ($data) {
            if ($admin = User::first()) {
                $admin->update(array_merge($tenant->only(['name', 'email', 'password']), $data));

                // If the teams feature is enabled, also update the admin's team name
                if (Jetstream::hasTeamFeatures()) {
                    $admin->currentTeam?->update([
                        'name' => "{$admin->name}'s Team",
                    ]);
                }
            }
        });

        $tenant->createDomain([
            'domain' => $domain,
        ])->makePrimary()->makeFallback();

        if ($createStripeCustomer && config('saas.stripe_key')) {
            $tenant->createAsStripeCustomer();
        }

        $tenant->update([
            'ready' => true,
        ]);

        return $tenant;
    }
}
