<?php

declare(strict_types=1);

use App\Models\Admin;
use App\Models\Tenant;
use App\Actions\CreateTenantAction;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Livewire\Livewire;
use App\Livewire\Admin\Tenants\Billing\BillingAddress;
use App\Livewire\Admin\Tenants\Billing\CreditBalance;

beforeEach(function() {
    // Make sure the admin guard is used by default in the central app
    config(['auth.defaults' => [
        'guard' => 'admin',
        'passwords' => 'admins',
    ]]);

    config(['cashier.currency' => 'usd']);
});

function createAdmin(): Admin
{
    return Admin::create([
        'name' => $name = str()->random(8),
        'email' => $name . '@test.com',
        'password' => $name,
    ]);
}

function createTenant(int $count = 1): Tenant|array
{
    $tenants = [];

    for ($count; $count > 0; $count--) {
        $tenants[] = (new CreateTenantAction)([
            'company' => str()->random(8),
            'name' => $name = str()->random(8),
            'email' => $name . '@test.com',
            'password' => str()->random(8),
        ], str()->random(10));
    }

    return count($tenants) === 1 ? $tenants[0] : $tenants;
}

function logInAsAdmin(): Admin
{
    auth()->guard('admin')->login($admin = createAdmin());

    return $admin;
}

test('the create admin command creates an admin user in the central database', function() {
    $this->artisan('app:create-admin')
        ->expectsQuestion('Name', 'foo')
        ->expectsQuestion('Email Address', $email = 'foo@bar.test')
        ->expectsQuestion('Password', 'foobar')
        ->assertOk();

    expect(Admin::firstWhere('email', $email))->not()->toBeNull();
});

test('logging in as the admin using invalid credentials throws a validation exception', function() {
    $admin = createAdmin();

    $this->post(route('admin.login.submit'), [
        'email' => $admin->email,
        'password' => 'wrong password',
    ])->assertInvalid();
});

test('logging in as the admin redirects the user to the tenant index', function() {
    $admin = createAdmin();
    $password = $admin->name;

    $this->withoutExceptionHandling()->post(route('admin.login.submit'), [
        'email' => $admin->email,
        'password' => $password,
    ])->assertRedirect(route('admin.tenants.index'));
});

test('accessing admin routes when not authenticated redirects the user to the admin login page', function() {
    $this->get(route('admin.tenants.index'))->assertRedirect(route('admin.login'));
});

test('creating tenants works correctly', function() {
    logInAsAdmin();

    $data = [
        'domain' => 'subdomain',
        'company' => 'foo',
        'name' => 'foo',
        'email' => 'foo@bar.test',
        'password' => 'foobar',
        'password_confirmation' => 'foobar',
    ];

    expect(Tenant::where('email', 'foo@bar.test')->exists())->toBeFalse();

    // After successfully creating a tenant, the admin should be redirected to the tenant index
    $this->post(route('admin.tenants.store', $data))->assertRedirectToRoute('admin.tenants.index');

    expect(Tenant::where('email', 'foo@bar.test')->exists())->toBeTrue();

    // The tenant cannot be duplicate
    $this->post(route('admin.tenants.store', $data))->assertSessionHasErrors();

    expect(Tenant::where('email', 'foo@bar.test')->exists())->toBeTrue();
});

test('updating tenants works correctly', function() {
    // Also test that the tenant cannot be duplicate
    logInAsAdmin();

    [$tenant, $tenant2] = createTenant(2);

    $newEmail = 'new@email.test';

    // After successfully updating a tenant, the admin should be redirected to the tenant show page
    $this->put(route('admin.tenants.update', $tenant), [
        'email' => $newEmail,
        'company' => $tenant->company,
    ])->assertRedirectToRoute('admin.tenants.edit', $tenant);

    expect(Tenant::find($tenant->id)->email)->toBe($newEmail);
});

test('deleting tenants works correctly', function() {
    logInAsAdmin();

    $tenant = createTenant();

    expect(Tenant::find($tenant->id))->not()->toBeNull();

    $this->withoutExceptionHandling()->get(route('admin.tenants.destroy', $tenant))->assertRedirectToRoute('admin.tenants.index');

    expect(Tenant::find($tenant->id))->toBeNull();
});

test('updating billing address on the tenant billing page works correctly', function() {
    logInAsAdmin();
    $tenant = createTenant();

    $newAddress = [
        'city' => 'new city',
        'country' => 'new country', // Should be 2 characters
        'line1' => 'new address',
        'state' => 'new state',
    ];

    // Address won't get updated because of a validation error
    Livewire::test(BillingAddress::class, array_merge($newAddress, ['tenantId' => $tenant->id]))
        ->call('save')
        ->assertHasErrors();

    expect($tenant->asStripeCustomer()->address)->toBeNull();

    $newAddress['country'] = 'US';

    // Address should be updated successfully
    Livewire::test(BillingAddress::class, array_merge($newAddress, ['tenantId' => $tenant->id]))
        ->call('save')
        ->assertOk();

    expect(array_filter($tenant->asStripeCustomer()->address->toArray()))->toEqual($newAddress);
})->skip(fn () => ! env('STRIPE_KEY'));

test('adjusting the credit balance on the billing page works correctly', function() {
    logInAsAdmin();
    $tenant = createTenant();

    expect($tenant->getCreditBalance())->toBe('0.00 USD');

    // The amount should be numeric
    Livewire::test(CreditBalance::class, ['tenantId' => $tenant->id])
        ->set('amount', 'foo')
        ->call('save')
        ->assertHasErrors(['amount' => 'numeric']);

    expect($tenant->getCreditBalance())->toBe('0.00 USD');

    Livewire::test(CreditBalance::class, ['tenantId' => $tenant->id])
        ->set('amount', 50.51)
        ->call('save')
        ->assertOk();

    expect($tenant->getCreditBalance())->toBe('50.51 USD');

    Livewire::test(CreditBalance::class, ['tenantId' => $tenant->id])
        ->set('amount', -100.51)
        ->call('save')
        ->assertOk();

    expect($tenant->getCreditBalance())->toBe('-50.00 USD');
})->skip(fn () => ! env('STRIPE_KEY'));

// Test that pages are accessible
test('admin login page is accessible', function() {
    $this->withoutExceptionHandling()->get(route('admin.login'))->assertOk();
});

test('admin tenants index page is accessible', function() {
    logInAsAdmin();

    $this->withoutExceptionHandling()->get(route('admin.tenants.index'))->assertOk();
});

test('admin tenants create page is accessible', function() {
    logInAsAdmin();

    $this->withoutExceptionHandling()->get(route('admin.tenants.create'))->assertOk();
});

test('admin tenants edit page is accessible', function() {
    logInAsAdmin();

    $tenant = createTenant();

    $this->withoutExceptionHandling()->get(route('admin.tenants.edit', $tenant))->assertOk();
});

test('admin tenants billing show page is accessible', function() {
    logInAsAdmin();

    $tenant = createTenant();

    $this->withoutExceptionHandling()->get(route('admin.tenants.billing.show', $tenant))->assertOk();
});

// View tests
test('the landing page recognizes that the current user is admin', function() {
    $this->withoutExceptionHandling()->get(route('home'))->assertSee('Admin login');

    logInAsAdmin();

    $this->withoutExceptionHandling()->get(route('home'))->assertSee('Log Out');
});

test('tenant index shows paginated tenants', function() {
    logInAsAdmin();

    createTenant(10);

    // Assume the tenants are paginated by 5
    $this->withoutExceptionHandling()->get(route('admin.tenants.index'))
        ->assertSee('tenant-0')
        ->assertSee('tenant-4')
        ->assertDontSee('tenant-5'); // The 6th tenant should not be visible on the first page

    $this->withoutExceptionHandling()->get(route('admin.tenants.index') . '?page=2')
        ->assertSee('tenant-0')
        ->assertSee('tenant-4');
});

test('tenant show page shows the correct tenant info', function() {
    logInAsAdmin();

    $tenant = createTenant();

    $this->withoutExceptionHandling()->get(route('admin.tenants.edit', $tenant))
        ->assertSee($tenant->id)
        ->assertSee($tenant->company)
        ->assertSee($tenant->email);
});

test('billing page shows correct credit balance', function() {
    // Positive if the tenant has credit, negative if the tenant owes money
    logInAsAdmin();
    $tenant = createTenant();

    $this->withoutExceptionHandling()->get(route('admin.tenants.billing.show', $tenant))
        ->assertSee('0.00 USD');

    $tenant->creditBalance(5041);

    $this->withoutExceptionHandling()->get(route('admin.tenants.billing.show', $tenant))
        ->assertSee('50.41 USD');

    $tenant->debitBalance(10041);

    $this->withoutExceptionHandling()->get(route('admin.tenants.billing.show', $tenant))
        ->assertSee('-50.00 USD');
})->skip(fn () => ! env('STRIPE_KEY'));

test('creating domains works correctly', function() {
    logInAsAdmin();
    $tenant = createTenant();
    $domain = 'foobar.test';

    expect($tenant->domains()->where('domain', $domain)->exists())->toBeFalse();

    $this->withoutExceptionHandling()->post(route('admin.tenants.domain.store', $tenant), ['domain' => $domain])
        ->assertOk();

    expect($tenant->domains()->where('domain', $domain)->exists())->toBeTrue();

    // The domain cannot be duplicated
    expect(fn () => $this->post(route('admin.tenants.domain.store', $tenant), ['domain' => $domain]))
        ->toThrow(ValidationException::class);

    expect($tenant->domains()->where('domain', $domain)->get())->toHaveCount(1);
});

test('updating the fallback domain works correctly', function() {
    logInAsAdmin();
    $tenant = createTenant();
    $fallbackDomain = $tenant->fallback_domain;
    $originalFallbackDomainName = $fallbackDomain->domain;

    $this->withoutExceptionHandling()
        ->post(route('admin.tenants.domain.store-fallback', $tenant), ['domain' => $newName = 'newfallbackname'])
        ->assertOk();

    // The fallback domain shouldn't be updated
    // The domain name shouldn't contain a dot (because of the 'regex:/^[A-Za-z0-9-]+$/' rule)
    expect(fn () => $this->withoutExceptionHandling()->post(route('admin.tenants.domain.store-fallback', $tenant), ['domain' => 'foo.test']))
        ->toThrow(ValidationException::class);

    expect($tenant->refresh()->fallback_domain->domain)
        ->not()->toBe($originalFallbackDomainName)
        ->toBe($newName);
});

test('deleting domains works correctly', function() {
    logInAsAdmin();
    $tenant = createTenant();
    $domain = $tenant->createDomain('foobar.test');

    expect($tenant->domains()->where('domain', $domain->domain)->exists())->toBeTrue();

    $this->withoutExceptionHandling()->post(route('admin.tenants.domain.destroy', $domain))
        ->assertOk();

    expect($tenant->domains()->where('domain', $domain->domain)->exists())->toBeFalse();
});

test('making a domain primary works correctly', function() {
    logInAsAdmin();
    $tenant = createTenant();
    $originalPrimaryDomain = $tenant->primary_domain;
    $domain = $tenant->createDomain('foobar.test');

    expect($domain)->not()->toBe($tenant->primary_domain);

    // Make the non-primary domain primary
    $this->withoutExceptionHandling()->post(route('admin.tenants.domain.make-primary', $domain));

    $tenant->refresh();

    // Check if the old primary domain is now non-primary and the new domain is primary
    expect($tenant->domains()->firstWhere('domain', $domain->domain)->domain)
        ->toBe($tenant->primary_domain->domain);
    expect($tenant->domains()->firstWhere('domain', $originalPrimaryDomain->domain)->domain)
        ->not()->toBe($tenant->primary_domain->domain);
});

test('primary and fallback domains cannot be deleted', function() {
    logInAsAdmin();
    $tenant = createTenant();

    // We'll make the primary domain just a fallback domain
    $fallbackDomain = $tenant->primary_domain;
    $primaryDomain = $tenant->createDomain('foobar.test');

    $domainCount = $tenant->domains()->count();

    // Make the non-primary domain primary
    $this->withoutExceptionHandling()->post(route('admin.tenants.domain.make-primary', $primaryDomain));

    // Try deleting the primary and fallback domains
    // Though this isn't possible using the admin panel UI, it should still be tested
    expect(fn() => $this->withoutExceptionHandling()->post(route('admin.tenants.domain.destroy', $primaryDomain)))
        ->toThrow(HttpException::class);

    expect(fn() => $this->withoutExceptionHandling()->post(route('admin.tenants.domain.destroy', $fallbackDomain)))
        ->toThrow(HttpException::class);

    expect($tenant->domains()->count())->toBe($domainCount);
});

test('tenant show page shows the tenants domains correctly', function() {
    // Create tenant with a domain
    logInAsAdmin();

    $tenant = createTenant();

    // The tenant has a primary domain that's also the fallback
    expect($tenant->domains()->count())->toBe(1);

    $primaryDomain = $tenant->primary_domain;
    $domainsProp = 'tenant.domains';

    // Check if the domain is shown on the tenant show page as the fallback AND primary domain
    $this->withoutExceptionHandling()->get(route('admin.tenants.edit', $tenant))
        ->assertSeeHtmlInOrder(['Fallback', 'Primary', $primaryDomain->domain]);

    // Then add some more domains and check if they are shown correctly
    $secondDomain = $tenant->createDomain('second.test');

    $this->withoutExceptionHandling()->get(route('admin.tenants.edit', $tenant))
        ->assertSeeHtmlInOrder([$secondDomain->domain, 'Domain']);

    $secondDomain->makePrimary();

    $this->withoutExceptionHandling()->get(route('admin.tenants.edit', $tenant))
        ->assertSeeHtmlInOrder([$secondDomain->domain, 'Primary']);
});
