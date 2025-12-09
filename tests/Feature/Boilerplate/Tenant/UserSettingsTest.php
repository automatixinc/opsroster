<?php

declare(strict_types=1);

use App\Models\User;
use App\Exceptions\EmailOccupiedException;

test('owner cannot use a different tenants email', function() {
    $this->createTenant([
        'email' => 'second@tenant',
    ], createStripeCustomer: false);

    expect(fn () => User::find(1)->update(['email' => 'second@tenant']))->toThrow(EmailOccupiedException::class);
});

test('normal user can use a different tenants email', function() {
    $this->createTenant([
        'email' => 'second@tenant',
    ], createStripeCustomer: false);

    $user2 = User::factory()->create();

    // No exception should be thrown
    expect(fn () => $user2->update(['email' => 'second@tenant']))->not()->toThrow(Exception::class);
});
