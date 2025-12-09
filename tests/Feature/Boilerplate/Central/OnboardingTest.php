<?php

declare(strict_types=1);

use App\Models\Tenant;

test('tenant can get created using the register route', function() {
    $name = str()->random(8);
    $email = $name . '@example.com';

    expect(Tenant::firstWhere('email', $email))->toBeNull();

    $this->withoutExceptionHandling()->post(route('central.register.submit'), [
        'name' => $name,
        'password' => $password = str()->random(12),
        'password_confirmation' => $password,
        'company' => $name . ' Company',
        'email' => $email,
        'domain' => str()->random(8),
    ]);

    // Tenant was created
    expect(Tenant::firstWhere('email', $email))->not()->toBeNull();
});

test('central login route redirects the user to the login page of the correct tenant', function() {
    $tenant = $this->createTenant(['email' => str()->random(8) . '@example.com']);

    $this->post(route('central.login.submit'), ['email' => $tenant->email])
        ->assertRedirect($tenant->route('tenant.login'));
});

test('registering tenants using the central register page is rate limitted correctly', function(bool $debugMode) {
    config(['app.debug' => $debugMode]);

    $getCredentials = fn () => [
        'name' => $name = str()->random(8),
        'password' => $password = str()->random(12),
        'password_confirmation' => $password,
        'company' => $name . ' Company',
        'email' => $name . '@example.com',
        'domain' => str()->random(8),
    ];

    // Create a tenant using the central register route (will always succeed)
    $this->post(route('central.register.submit'), $getCredentials());

    // Try creating a second tenant under one minute (should fail unless debug mode is enabled)
    $response = $this->post(route('central.register.submit'), $getCredentials());

    if ($debugMode) {
        // OK, redirecting to the tenant app
        $response->assertRedirect();
    } else {
        // Rate limited, should show the "too many requests" error page
        $response->assertStatus(429);
    }
})->with([
    [false],
    [true],
]);
