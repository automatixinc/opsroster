<?php

declare(strict_types=1);

namespace Tests\Feature\Tenant;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Route;

test('url bootstrapper changes url with context', function() {
    Route::get('/foo/{bar}', [
        'as' => 'foo',
        'action' => function ($bar) {
            return $bar;
        },
    ]);

    $appUrl = 'http://localhost';
    $routeUrl = 'http://localhost/foo/xyz';

    config(['app.url' => $appUrl]);

    $tenant = $this->createTenant();

    // Subdomain
    $subdomain = $tenant->createDomain([
        'domain' => 'subdomain',
    ]);

    $subdomain->makePrimary();

    $tenantAppUrl = 'http://subdomain.localhost';
    $tenantRouteUrl = 'http://subdomain.localhost/foo/xyz';

    expect(route('foo', 'xyz'))->toBe($routeUrl);
    expect(URL::to('/'))->toBe($appUrl);
    expect(url('/'))->toBe($appUrl);

    tenancy()->initialize($tenant);

    expect(route('foo', 'xyz'))->toBe($tenantRouteUrl);
    expect(URL::to('/'))->toBe($tenantAppUrl);
    expect(url('/'))->toBe($tenantAppUrl);
    expect(config('app.url'))->toBe($tenantAppUrl);

    tenancy()->end();

    expect(route('foo', 'xyz'))->toBe($routeUrl);
    expect(URL::to('/'))->toBe($appUrl);
    expect(url('/'))->toBe($appUrl);
    expect(config('app.url'))->toBe($appUrl);

    // Domain
    $domain = $tenant->createDomain([
        'domain' => 'tenant_app.com',
    ]);

    $domain->makePrimary();

    $tenantAppUrl = 'http://tenant_app.com';
    $tenantRouteUrl = 'http://tenant_app.com/foo/xyz';

    tenancy()->initialize($tenant);

    expect(route('foo', 'xyz'))->toBe($tenantRouteUrl);
    expect(URL::to('/'))->toBe($tenantAppUrl);
    expect(url('/'))->toBe($tenantAppUrl);
    expect(config('app.url'))->toBe($tenantAppUrl);

    tenancy()->end();

    expect(route('foo', 'xyz'))->toBe($routeUrl);
    expect(URL::to('/'))->toBe($appUrl);
    expect(url('/'))->toBe($appUrl);
    expect(config('app.url'))->toBe($appUrl);
});
