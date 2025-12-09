<?php

declare(strict_types=1);

use App\Exceptions\DomainCannotBeChangedException;
use App\Models\Domain;

test('domain attributes can be changed', function() {
    $tenant = $this->createTenant();

    $domain = $tenant->createDomain('foo.localhost');

    /** @var Domain $domain */
    $domain->update(['is_primary' => true]);

    expect($domain->is_primary)->toBeTrue();
});

test('domain columns cannot be changed', function() {
    $tenant = $this->createTenant();

    /** @var Domain $domain */
    $domain = $tenant->createDomain('foo.localhost');

    expect(fn () => $domain->update(['domain' => 'bar.localhost']))->toThrow(DomainCannotBeChangedException::class);
});
