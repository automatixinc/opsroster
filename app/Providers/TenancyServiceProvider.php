<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Routing\Route;
use Stancl\Tenancy\Jobs;
use Stancl\Tenancy\Events;
use Stancl\Tenancy\ResourceSyncing;
use Stancl\Tenancy\Listeners;
use Stancl\Tenancy\Middleware;
use Stancl\JobPipeline\JobPipeline;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Stancl\Tenancy\Actions\CloneRoutesAsTenant;
use Stancl\Tenancy\Overrides\TenancyUrlGenerator;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Route as RouteFacade;
use Stancl\Tenancy\Middleware\InitializeTenancyByPath;
use Stancl\Tenancy\Middleware\InitializeTenancyByRequestData;
use Stancl\Tenancy\Bootstrappers\Integrations\FortifyRouteBootstrapper;

/**
 * Tenancy for Laravel.
 *
 * Documentation: https://tenancyforlaravel.com
 *
 * We can sustainably develop Tenancy for Laravel thanks to our sponsors.
 * Big thanks to everyone listed here: https://github.com/sponsors/stancl
 *
 * You can also support us, and save time, by purchasing these products:
 *   Exclusive content for sponsors: https://sponsors.tenancyforlaravel.com
 *   Multi-Tenant SaaS boilerplate: https://portal.archte.ch/boilerplate
 *   Multi-Tenant Laravel in Production e-book: https://portal.archte.ch/book
 *
 * All of these products can also be accessed at https://portal.archte.ch
 */
class TenancyServiceProvider extends ServiceProvider
{
    // By default, no namespace is used to support the callable array syntax.
    public static string $controllerNamespace = '';

    public function events()
    {
        return [
            // Tenant events
            Events\CreatingTenant::class => [],
            Events\TenantCreated::class => [
                JobPipeline::make([
                    Jobs\CreateDatabase::class,
                    Jobs\MigrateDatabase::class,
                    \App\Jobs\CreateTenantAdmin::class,
                    Jobs\SeedDatabase::class,
                    // Jobs\CreateStorageSymlinks::class,

                    // Your own jobs to prepare the tenant.
                    // Provision API keys, create S3 buckets, anything you want!
                ])->send(function (Events\TenantCreated $event) {
                    return $event->tenant;
                })->shouldBeQueued(false),

                Listeners\CreateTenantStorage::class,
            ],
            Events\SavingTenant::class => [],
            Events\TenantSaved::class => [],
            Events\UpdatingTenant::class => [],
            Events\TenantUpdated::class => [],
            Events\DeletingTenant::class => [
                JobPipeline::make([
                    Jobs\DeleteDomains::class,
                    // Jobs\RemoveStorageSymlinks::class,
                ])->send(function (Events\DeletingTenant $event) {
                    return $event->tenant;
                })->shouldBeQueued(false),

                Listeners\DeleteTenantStorage::class,
            ],
            Events\TenantDeleted::class => [
                JobPipeline::make([
                    Jobs\DeleteDatabase::class,
                ])->send(function (Events\TenantDeleted $event) {
                    return $event->tenant;
                })->shouldBeQueued(false),
            ],

            Events\TenantMaintenanceModeEnabled::class => [],
            Events\TenantMaintenanceModeDisabled::class => [],

            // Pending tenant events
            Events\CreatingPendingTenant::class => [],
            Events\PendingTenantCreated::class => [],
            Events\PullingPendingTenant::class => [],
            Events\PendingTenantPulled::class => [],

            // Domain events
            Events\CreatingDomain::class => [],
            Events\DomainCreated::class => [
                JobPipeline::make([
                    \App\Jobs\AddDomainToPloi::class,
                ])->send(function (Events\DomainCreated $event) {
                    return $event->domain;
                })->shouldBeQueued(false),
            ],
            Events\SavingDomain::class => [],
            Events\DomainSaved::class => [],
            Events\UpdatingDomain::class => [],
            Events\DomainUpdated::class => [],
            Events\DeletingDomain::class => [],
            Events\DomainDeleted::class => [
                JobPipeline::make([
                    \App\Jobs\RemoveDomainFromPloi::class,
                ])->send(function (Events\DomainDeleted $event) {
                    return $event->domain;
                })->shouldBeQueued(false),
            ],

            // Database events
            Events\DatabaseCreated::class => [
                JobPipeline::make([
                    \App\Jobs\AddDatabaseToPloi::class,
                ])->send(function (Events\DatabaseCreated $event) {
                    return $event->tenant;
                })->shouldBeQueued(false),
            ],
            Events\DatabaseMigrated::class => [],
            Events\DatabaseSeeded::class => [],
            Events\DatabaseRolledBack::class => [],
            Events\DatabaseDeleted::class => [
                JobPipeline::make([
                    \App\Jobs\RemoveDatabaseFromPloi::class,
                ])->send(function (Events\DatabaseDeleted $event) {
                    return $event->tenant;
                })->shouldBeQueued(false),
            ],

            // Tenancy events
            Events\InitializingTenancy::class => [],
            Events\TenancyInitialized::class => [
                Listeners\BootstrapTenancy::class,
            ],

            Events\EndingTenancy::class => [],
            Events\TenancyEnded::class => [
                Listeners\RevertToCentralContext::class,
            ],

            Events\BootstrappingTenancy::class => [],
            Events\TenancyBootstrapped::class => [],
            Events\RevertingToCentralContext::class => [],
            Events\RevertedToCentralContext::class => [],

            // Resource syncing
            ResourceSyncing\Events\SyncedResourceSaved::class => [
                ResourceSyncing\Listeners\UpdateOrCreateSyncedResource::class,
            ],
            ResourceSyncing\Events\SyncMasterDeleted::class => [
                ResourceSyncing\Listeners\DeleteResourcesInTenants::class,
            ],
            ResourceSyncing\Events\SyncMasterRestored::class => [
                ResourceSyncing\Listeners\RestoreResourcesInTenants::class,
            ],
            ResourceSyncing\Events\CentralResourceAttachedToTenant::class => [
                ResourceSyncing\Listeners\CreateTenantResource::class,
            ],
            ResourceSyncing\Events\CentralResourceDetachedFromTenant::class => [
                ResourceSyncing\Listeners\DeleteResourceInTenant::class,
            ],
            // Fired only when a synced resource is changed in a different DB than the origin DB (to avoid infinite loops)
            ResourceSyncing\Events\SyncedResourceSavedInForeignDatabase::class => [],

            // Storage symlinks
            Events\CreatingStorageSymlink::class => [],
            Events\StorageSymlinkCreated::class => [],
            Events\RemovingStorageSymlink::class => [],
            Events\StorageSymlinkRemoved::class => [],
        ];
    }

    /**
     * Set \Stancl\Tenancy\Bootstrappers\RootUrlBootstrapper::$rootUrlOverride here
     * to override the root URL used in CLI while in tenant context.
     *
     * @see \Stancl\Tenancy\Bootstrappers\RootUrlBootstrapper
     */
    protected function overrideUrlInTenantContext(): void
    {
        \Stancl\Tenancy\Bootstrappers\RootUrlBootstrapper::$rootUrlOverride = function (\App\Models\Tenant $tenant, string $originalRootUrl) {
            $tenantDomain = $tenant->primary_domain;
            $tenantDomainName = $tenantDomain?->domain;
            $scheme = str($originalRootUrl)->before('://');

            if ($tenantDomain?->isSubdomain()) {
                   return $scheme . '://' . $tenantDomainName . '.' . config('tenancy.identification.central_domains')[0];
            }

            return $scheme . '://' . $tenantDomainName;
        };
    }

    public function register()
    {
        //
    }

    public function boot()
    {
        \Laravel\Cashier\Cashier::useCustomerModel(\App\Models\Tenant::class);

        $this->bootEvents();
        $this->mapRoutes();

        $this->makeTenancyMiddlewareHighestPriority();
        $this->overrideUrlInTenantContext();

        \Stancl\Tenancy\Bootstrappers\UrlGeneratorBootstrapper::$addTenantParameterToDefaults = false;

        TenancyUrlGenerator::$overrides = [
            'team-invitations.accept' => 'tenant.team-invitations.accept', // Used in Jetstream's Mail/TeamInvitation
            'home' => 'tenant.home', // Used in the authentication card logo component
            'login' => 'tenant.login', // Used in Fortify's Http/Responses/PasswordResetResponse
            'password.reset' => 'tenant.password.reset', // Used in Laravel's Illuminate/Auth/Notifications/ResetPassword
            'verification.verify' => 'tenant.verification.verify', // Used in Laravel's Illuminate/Auth/Notifications/VerifyEmail
            'two-factor.login' => 'tenant.two-factor.login', // Used in Fortify's Actions/RedirectIfTwoFactorAuthenticatable
            'password.confirm' => 'tenant.password.confirm',
            'profile.show' => 'tenant.profile.show', // Used in Jetstream's Http/Livewire/UpdateProfileInformationForm
        ];

        \Stancl\Tenancy\Database\TenantDatabaseManagers\SQLiteDatabaseManager::$persistInMemoryConnectionUsing = static fn (\PDO $conn) => register_shutdown_function(static fn () => $conn);

        \Illuminate\Auth\Middleware\RedirectIfAuthenticated::redirectUsing(fn () => config('fortify.home'));

        \Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::except([
            'stripe/*',
            'ploi/webhook/*',
        ]);

        // // Include soft deleted resources in synced resource queries.
        // ResourceSyncing\Listeners\UpdateOrCreateSyncedResource::$scopeGetModelQuery = function (Builder $query) {
        //     if ($query->hasMacro('withTrashed')) {
        //         $query->withTrashed();
        //     }
        // };

        // Livewire tenancy integration
        \Livewire\Livewire::setUpdateRoute(function ($handle) {
            return RouteFacade::post('/livewire/update', $handle)
                ->middleware(
                    'web',
                    'universal',
                    \Stancl\Tenancy\Middleware\InitializeTenancyByDomainOrSubdomain::class,
                    \Stancl\Tenancy\Middleware\PreventAccessFromUnwantedDomains::class,
                    \Stancl\Tenancy\Middleware\ScopeSessions::class,
                );
        });

        \Livewire\Features\SupportFileUploads\FilePreviewController::$middleware = [
            'web',
            'universal',
            \Stancl\Tenancy\Middleware\InitializeTenancyByDomainOrSubdomain::class,
            \Stancl\Tenancy\Middleware\PreventAccessFromUnwantedDomains::class,
            \Stancl\Tenancy\Middleware\ScopeSessions::class,
        ];
    }

    protected function bootEvents()
    {
        foreach ($this->events() as $event => $listeners) {
            foreach ($listeners as $listener) {
                if ($listener instanceof JobPipeline) {
                    $listener = $listener->toListener();
                }

                Event::listen($event, $listener);
            }
        }
    }

    protected function mapRoutes()
    {
        $this->app->booted(function () {
            if (file_exists(base_path('routes/tenant.php'))) {
                RouteFacade::namespace(static::$controllerNamespace)
                    ->middleware('tenant')
                    ->group(base_path('routes/tenant.php'));
            }

            // $this->cloneRoutes();
        });
    }

    /**
     * Clone routes as tenant.
     *
     * This is used primarily for integrating packages.
     *
     * @see CloneRoutesAsTenant
     */
    protected function cloneRoutes(): void
    {
        /** @var CloneRoutesAsTenant $cloneRoutes */
        $cloneRoutes = $this->app->make(CloneRoutesAsTenant::class);

        // The cloning action has two modes:
        // 1. Clone all routes that have the middleware present in the action's $cloneRoutesWithMiddleware property.
        // You can customize the middleware that triggers cloning by using cloneRoutesWithMiddleware() on the action.
        //
        // By default, the middleware is ['clone'], but using $cloneRoutes->cloneRoutesWithMiddleware(['clone', 'universal'])->handle()
        // will clone all routes that have either 'clone' or 'universal' middleware (mentioning 'universal' since that's a common use case).
        //
        // Also, you can use the shouldClone() method to provide a custom closure that determines if a route should be cloned.
        //
        // 2. Clone only the routes that were manually added to the action using cloneRoute().
        //
        // Regardless of the mode, you can provide a custom closure for defining the cloned route, e.g.:
        // $cloneRoutesAction->cloneUsing(function (Route $route) {
        //     RouteFacade::get('/cloned/' . $route->uri(), fn () => 'cloned route')->name('cloned.' . $route->getName());
        // })->handle();
        // This will make all cloned routes use the custom closure to define the cloned route instead of the default behavior.
        // See Stancl\Tenancy\Actions\CloneRoutesAsTenant for more details.

        $cloneRoutes->handle();
    }

    protected function makeTenancyMiddlewareHighestPriority()
    {
        // PreventAccessFromUnwantedDomains has even higher priority than the identification middleware
        $tenancyMiddleware = array_merge([Middleware\PreventAccessFromUnwantedDomains::class], config('tenancy.identification.middleware'));

        foreach (array_reverse($tenancyMiddleware) as $middleware) {
            $this->app[\Illuminate\Contracts\Http\Kernel::class]->prependToMiddlewarePriority($middleware);
        }
    }
}