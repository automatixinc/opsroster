<?php

declare(strict_types=1);

use App\Models\User;

test('only owner can view application settings', function() {
    $owner = User::first();

    $this->actingAs($owner)->get(tenant()->route('tenant.settings.application'))
       ->assertSuccessful();

    $mortal = User::factory()->create();

    session()->flush();

    $this->actingAs($mortal)->get(tenant()->route('tenant.settings.application'))
       ->assertForbidden();
});
