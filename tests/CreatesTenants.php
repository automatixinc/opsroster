<?php

namespace Tests;

use App\Models\Tenant;
use App\Actions\CreateTenantAction;
use Illuminate\Support\Str;
use App\Models\Domain;
use Illuminate\Database\Eloquent\Collection;

trait CreatesTenants
{
    /** Only used with SQLite files */
    protected static string|null $testingTenantDatabase = null;
    protected static bool $testingTenantDeletionScheduled = false;

    /**
     * This becomes true when a tenant that's NOT a TenantTestCase
     * "test tenant" is created. On tearDown(), this will trigger
     * tenant database deletion.
     */
    public static bool $tenantsCreated = false;

    public static Tenant|null $testingTenant = null;
    public static Collection|null $testingTenantDomains = null;

    public function createTenant(
        array $data = [],
        string|null $domain = null,
        bool $createStripeCustomer = true
    ): Tenant
    {
        $domain ??= Str::random(10);

        if (! env('STRIPE_KEY')) {
            $createStripeCustomer = false;
        }

        $tenant = (new CreateTenantAction)(array_merge([
            'company' => 'Acme Corporation',
            'name' => 'John Doe',
            'email' => 'foo@tenant.localhost',
            'password' => bcrypt('password'),
            'tenancy_db_name' => ':memory:',
        ], $data), $domain, $createStripeCustomer);

        return $tenant;
    }

    public static function setUpTenantCreationListener(): void
    {
        Tenant::created(static fn () => static::$tenantsCreated = true);
    }

    public function getTestingTenant(): Tenant
    {
        $tenant = Tenant::firstOr(function () {
            $tenantsCreated = static::$tenantsCreated;

            $tenant = $this->createTenant();

            static::$tenantsCreated = $tenantsCreated ?: false;

            return $tenant;
        });

        $dbDriver = config('database.connections.' . env('DB_CONNECTION') . '.driver');

        if ($dbDriver === 'sqlite' && ! str($tenant->getInternal('db_name'))->contains('_tenancy_inmemory_')) {
            // Needs to be a primitive value since all of Laravel is cleaned up by then
            static::$testingTenantDatabase = database_path($tenant->getInternal('db_name'));

            if (! static::$testingTenantDeletionScheduled) {
                // Since the same tenant database is reused for ALL tests, we only delete it when
                // the entire process is shutting down. At that point, Laravel is not accessible
                // so we can only store primitive values like the database path and use simple
                // logic like unlink(). This COULD be done with MySQL too (you could just rewrite
                // this code to connect via PDO and delete the database) but there's no trivial
                // way to do that generically, like using $tenant->delete(), so it's recommended
                // to just keep the loop in TestCase::tearDown() for deleting each tenant when
                // using MySQL.
                register_shutdown_function(static fn () => @unlink(static::$testingTenantDatabase));

                static::$testingTenantDeletionScheduled = true;
            }
        }

        // We clone these so that they're independent instances that won't be affected by any changes made at runtime
        // and preserve a perfect copy of the tenant's original state
        static::$testingTenant = clone $tenant;
        static::$testingTenantDomains = $tenant->domains?->map(fn (Domain $domain) => clone $domain);

        return $tenant;
    }

    public function initializeTenant(): void
    {
        tenancy()->initialize($this->getTestingTenant());
    }
}
