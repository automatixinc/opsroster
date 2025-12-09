<?php

declare(strict_types=1);

use App\Exceptions\NoPrimaryDomainException;
use App\Models\Tenant;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Events\TenantCreated;

beforeEach(function () {
    Event::fake([TenantCreated::class]);

    Route::get('/foo/{bar}', [
        'as' => 'foo',
        'action' => function ($bar) {
            return $bar;
        },
    ]);
});

test('tenant has one primary domain', function() {
    $tenant = Tenant::factory()->create();
    $domain = $tenant->createDomain([
        'domain' => 'acme',
    ]);

    expect($tenant->primary_domain)->toBeNull();

    $domain->makePrimary();

    expect($tenant->primary_domain->is($domain))->toBeTrue();
});

test('making a domain primary will make previous primary domains secondary', function() {
    $tenant = Tenant::factory()->create();
    $foo = $tenant->createDomain([
        'domain' => 'foo',
        'is_primary' => true,
    ]);

    $bar = $tenant->createDomain([
        'domain' => 'bar',
    ]);

    expect($tenant->primary_domain->domain)->toBe('foo');

    $bar->makePrimary();

    expect($tenant->primary_domain->domain)->toBe('bar');
    expect($foo->refresh()->is_primary)->toBeFalse();
    expect($bar->refresh()->is_primary)->toBeTrue();
});

test('tenant routes are generated using the primary domain', function() {
    $tenant = Tenant::factory()->create();
    $domain = $tenant->createDomain([
        'domain' => 'acme.localhost',
    ]);

    $domain->makePrimary();

    expect($tenant->route('foo', ['bar' => 'xyz']))->toBe('http://acme.localhost/foo/xyz');
});

test('a primary domain is needed to generate a tenant route', function() {
    $tenant = Tenant::factory()->create();

    $tenant->createDomain([
        'domain' => 'acme.localhost',
    ]);

    // Not called: $domain->makePrimary();
    expect(fn () => $tenant->route('foo', ['bar' => 'xyz']))->toThrow(NoPrimaryDomainException::class);
});

test('subdomains are converted to domains when generating a tenant route', function() {
    config(['tenancy.identification.central_domains' => [
        'localhost',
    ]]);

    $tenant = Tenant::factory()->create();
    $domain = $tenant->createDomain([
        'domain' => 'acme',
    ]);

    $domain->makePrimary();

    expect($tenant->route('foo', ['bar' => 'xyz']))->toBe('http://acme.localhost/foo/xyz');
});
