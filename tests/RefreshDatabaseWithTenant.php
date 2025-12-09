<?php

namespace Tests;

use App\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Domain;

trait RefreshDatabaseWithTenant
{
    use CreatesTenants, RefreshDatabase {
        beginDatabaseTransaction as parentBeginDatabaseTransaction;
    }

    public function beginDatabaseTransaction(): void
    {
        if (static::$testingTenant !== null && ! Tenant::find(static::$testingTenant->id)) {
            Tenant::withoutEvents(fn () => Tenant::create(static::$testingTenant->getAttributes()));
            Domain::withoutEvents(function () {
                foreach (static::$testingTenantDomains ?? [] as $domain) {
                    Domain::create($domain->getAttributes());
                }
            });
        }

        // create the testing tenant before starting a central tx if it doesn't exist yet
        $this->getTestingTenant();

        // Start a transaction for the central database
        $this->parentBeginDatabaseTransaction();

        $this->initializeTenant();

        // Start a transaction for the tenant database
        $this->parentBeginDatabaseTransaction();
    }
}
