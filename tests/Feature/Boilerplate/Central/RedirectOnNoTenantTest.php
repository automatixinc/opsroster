<?php

declare(strict_types=1);

use Stancl\Tenancy\Contracts\TenantCouldNotBeIdentifiedException;

test('exception is thrown', function() {
    expect(fn () => $this->withoutExceptionHandling()->get('http://foo.localhost'))
        ->toThrow(TenantCouldNotBeIdentifiedException::class);
});

test('exception is handled', function() {
    $this->get('http://foo.localhost')
        ->assertRedirect('http://' . config('tenancy.identification.central_domains')[0]);
});
