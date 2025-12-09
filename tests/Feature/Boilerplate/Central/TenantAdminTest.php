<?php

declare(strict_types=1);

use App\Models\Tenant;
use App\Models\User;

test('updating tenant admins email changes the email in the tenant too', function() {
    $tenant = $this->createTenant([
        'name' => 'Super Admin',
        'email' => 'foo@admin.test',
        'password' => 'password',
    ]);

    $tenant->run(function () {
        User::first()->update([
            'email' => 'bar@email.test',
        ]);
    });

    expect(Tenant::first()->email)->toBe('bar@email.test');
});

test('updating tenant email also updates the admin user email', function() {
    $tenant = $this->createTenant();

    expect($tenant->email)->toBe($tenant->getAdmin()->email);

    $tenant->update(['email' => $newEmail = 'new@email.test']);

    expect($tenant->email)
        ->toBe($newEmail)
        ->toBe($tenant->getAdmin()->email);
});
