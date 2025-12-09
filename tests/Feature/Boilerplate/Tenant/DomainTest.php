<?php

declare(strict_types=1);

use Livewire\Livewire;
use App\Livewire\Tenant\NewDomain;
use App\Livewire\Tenant\FallbackDomain;
use App\Livewire\Tenant\Domains;

test('creating domains via the NewDomain livewire component works', function() {
    $domain = 'foobar.test';

    expect(tenant()->domains()->where('domain', $domain)->exists())->toBeFalse();

    Livewire::test(NewDomain::class)
        ->set('domain', $domain)
        ->call('save')
        ->assertHasNoErrors();

    expect(tenant()->domains()->where('domain', $domain)->exists())->toBeTrue();

    // The domain cannot be duplicated
    Livewire::test(NewDomain::class)
        ->set('domain', $domain)
        ->call('save')
        ->assertHasErrors(['domain']);

    expect(tenant()->domains()->where('domain', $domain)->get())->toHaveCount(1);
});

test('updating the fallback domain via the FallbackDomain livewire component works', function() {
    auth()->loginUsingId(1);

    $fallbackDomain = tenant()->fallback_domain;
    $originalFallbackDomainName = $fallbackDomain->domain;

    // The fallback domain shouldn't be updated
    // The domain name shouldn't contain a dot (because of the 'regex:/^[A-Za-z0-9-]+$/' rule)
    Livewire::test(FallbackDomain::class)
        ->set('domain', 'foo.test')
        ->call('save')
        ->assertHasErrors(['domain']);

    Livewire::test(FallbackDomain::class)
        ->set('domain', $newName = 'newfallbackname')
        ->call('save')
        ->assertOk();

    expect(tenant()->refresh()->fallback_domain->domain)
        ->not()->toBe($originalFallbackDomainName)
        ->toBe($newName);
});

test('deleting domains via the Domains livewire component works', function() {
    $domain = tenant()->createDomain('foobar.test');

    expect(tenant()->domains()->where('domain', $domain->domain)->exists())->toBeTrue();

    Livewire::test(Domains::class)
        ->call('delete', $domain)
        ->assertOk();

    expect(tenant()->domains()->where('domain', $domain->domain)->exists())->toBeFalse();
});

test('making domains primary via the Domains livewire component works', function() {
    $originalPrimaryDomain = tenant()->primary_domain;
    $domain = tenant()->createDomain('foobar.test');

    expect($domain)->not()->toBe(tenant()->primary_domain);

    // Make the non-primary domain primary
    Livewire::test(Domains::class)
        ->call('makePrimary', $domain)
        ->assertOk();

    tenant()->refresh();

    // The old primary domain is not primary anymore and the new domain is primary
    expect(tenant()->domains()->firstWhere('domain', $domain->domain)->domain)
        ->toBe(tenant()->primary_domain->domain);
    expect(tenant()->domains()->firstWhere('domain', $originalPrimaryDomain->domain)->domain)
        ->not()->toBe(tenant()->primary_domain->domain);
});

test('requesting and revoking certificates using the Domains livewire component works', function() {
    $domain = tenant()->createDomain('foobar.test');

    Livewire::test(Domains::class)
        ->call('requestCertificate', $domain)
        ->assertOk();

    Livewire::test(Domains::class)
        ->call('revokeCertificate', $domain)
        ->assertOk();
});
