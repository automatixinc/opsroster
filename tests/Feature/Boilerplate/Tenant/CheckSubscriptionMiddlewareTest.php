<?php

declare(strict_types=1);

use Carbon\Carbon;

test('the tenant is taken to the billing screen if he doesnt have a subscription or trial', function() {
    auth()->loginUsingId(1);

    $this->withoutExceptionHandling()->get(tenant()->route('tenant.posts.index'))
        ->assertStatus(200);

    tenant()->update([
        'trial_ends_at' => Carbon::now()->subtract('30d'),
    ]);

    tenant()->refresh(); // Update model persisted on Tenancy singleton

    $this->withoutExceptionHandling()->get(tenant()->route('tenant.posts.index'))
        ->assertRedirect('/settings/billing');

    $this->withoutExceptionHandling()->get(tenant()->route('tenant.settings.billing'))
        ->assertOk();
});
