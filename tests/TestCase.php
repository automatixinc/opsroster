<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use App\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\ParallelTesting;

abstract class TestCase extends BaseTestCase
{
    use CreatesTenants, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Configure a specific prefix for tenant databases so that they can be deleted easily
        // if some remain despite all the cleanup logic here.
        // We also use the parallel testing token here in case a tenant is being created with a specific ID.
        $parallelToken = ParallelTesting::token();

        if ($parallelToken) {
            $parallelCachePath = storage_path("framework/cache/data_{$parallelToken}");

            if (! is_dir($parallelCachePath)) {
                mkdir($parallelCachePath, 0755, true);
            }

            config([
                'cache.stores.parallel' => [
                    'driver' => 'file',
                    'path' => $parallelCachePath,
                    'lock_path' => $parallelCachePath,
                ],
                'cache.default' => 'parallel',
            ]);

            app()->forgetInstance('cache');
        }

        // Cache persists between the tests when using the file CACHE_STORE.
        // Here, we clear the cache so that it doesn't persist.
        Cache::flush();

        config(['tenancy.database.prefix' => 'testing_tenant_' . ($parallelToken ? ($parallelToken . '_') : '')]);

        static::setUpTenantCreationListener();
    }

    protected function tearDown(): void
    {
        if (Event::isFake()) {
            // Revert Event::fake() so tenancy()->end() works
            Event::swap($real = Event::getFacadeRoot()->dispatcher);
            Model::setEventDispatcher($real);
            Cache::refreshEventDispatcher();
        }

        // Return to central context for the teardown
        tenancy()->end();

        static::deleteTenantDatabases();

        parent::tearDown();
    }

    public static function deleteTenantDatabases(): void
    {
        // If *any other* place deleted the testing tenant (e.g. by deleting all tenants)
        // detect that here so that a new testing tenant is created.
        $db = static::$testingTenant?->database();
        if (static::$testingTenant && ! $db->manager()->databaseExists($db->getName())) {
            static::$testingTenant = null;
            static::$testingTenantDomains = null;
        }

        if (env('DB_DATABASE') !== ':memory:' && static::$tenantsCreated) {
            // Even though the central database's records revert by running in a transaction, tenant DBs remain
            // Therefore, when not using :memory: for tenant databases, we have to clean them up here. We do that
            // by calling delete() on each Tenant model.
            foreach (Tenant::cursor() as $tenant) {
                try {
                    if (! $tenant->is(static::$testingTenant)) {
                        $tenant->delete();
                    }
                } catch (\Throwable) {}
            }
        }

        // Note: The cleanup above *may not be sufficient* in all cases. For instance, if the test that has created
        // a particular tenant fails, the cleanup might not run correctly, leaving behind a database/tenant* file.
        // That will generally not be a problem, however if you have a test where you create a tenant *with a
        // specific ID*, some future test runs may fail due to an existing database file.
        //
        // To address those cases, you can run `rm database/testing_tenant*` manually. Or the equivalent for your
        // database driver of choice.

        static::$tenantsCreated = false;
    }
}
