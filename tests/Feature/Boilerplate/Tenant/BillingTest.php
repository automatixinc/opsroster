<?php

declare(strict_types=1);

use App\Models\User;
use Livewire\Livewire;
use App\Livewire\Tenant\PaymentMethod;
use App\Livewire\Tenant\SubscriptionPlan;

beforeEach(function () {
    config(['cashier.currency' => 'usd']);
})->skip(fn () => ! env('STRIPE_KEY'));

test('getCreditBalance retrieves the tenant credit balance correctly', function() {
    expect(tenant()->getCreditBalance())->toBe('0.00 USD');

    tenant()->creditBalance(5041);

    expect(tenant()->getCreditBalance())->toBe('50.41 USD');

    tenant()->debitBalance(10041);

    expect(tenant()->getCreditBalance())->toBe('-50.00 USD');
});

test('updating tenant email also updates the stripe customer email', function() {
    $tenant = tenant();

    $tenant->update([
        'email' => 'new@email.test'
    ]);

    expect($tenant->asStripeCustomer()->email)->toBe($tenant->email);
});

test('updating payment method via the livewire component works', function() {
    $owner = User::first();

    $this->actingAs($owner);

    expect(tenant()->hasDefaultPaymentMethod())->toBeFalse();

    // Create a Stripe customer for the tenant
    tenant()->createOrGetStripeCustomer();

    Livewire::test(PaymentMethod::class)
        ->set('paymentMethod', 'pm_card_visa')
        ->call('save')
        ->assertHasNoErrors();

    expect(tenant()->hasDefaultPaymentMethod())->toBeTrue();
});

test('tenant subscription can be managed using the SubscriptionPlan livewire component', function() {
    $owner = User::first();

    $this->actingAs($owner);

    expect(tenant()->subscription())->toBeNull();

    // Create a Stripe customer for the tenant, add payment method
    tenant()->createOrGetStripeCustomer();

    tenant()->updateDefaultPaymentMethod('pm_card_visa');

    // New subscription
    Livewire::test(SubscriptionPlan::class)
        ->set('plan', array_keys(config('saas.plans'))[0])
        ->call('updatePlan')
        ->assertHasNoErrors();

    tenant()->refresh();

    expect(tenant()->subscription()->stripe_price)->toBe(array_keys(config('saas.plans'))[0]);

    // Switch to another plan
    Livewire::test(SubscriptionPlan::class)
        ->set('plan', $latestPlan = array_keys(config('saas.plans'))[1])
        ->call('updatePlan')
        ->assertHasNoErrors();

    expect(tenant()->subscription()->stripe_price)->toBe($latestPlan);

    // Cancel subscription
    Livewire::test(SubscriptionPlan::class)
        ->set('cancelationReason', 'foo')
        ->call('cancel')
        ->assertHasNoErrors();

    expect(tenant()->subscription()->canceled())->toBeTrue();

    // Resume subscription
    Livewire::test(SubscriptionPlan::class)
        ->call('resume')
        ->assertHasNoErrors();

    $subscription = tenant()->subscription();

    expect($subscription->canceled())->toBeFalse();
    expect($subscription->stripe_price)->toBe($latestPlan);
});
