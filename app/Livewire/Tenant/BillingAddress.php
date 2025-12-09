<?php

declare(strict_types=1);

namespace App\Livewire\Tenant;

use App\BillingManager;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use App\Models\Tenant;

class BillingAddress extends Component
{
    public $line1;

    public $city;

    public $country;

    public $line2;

    public $postal_code;

    public $state;

    public $success;

    protected $listeners = ['saved' => '$refresh'];

    public function mount(): void
    {
        $tenant = $this->getTenant();

        if (! BillingManager::tenantCanUseStripe($tenant)) {
            return;
        }

        $address = $tenant->asStripeCustomer()?->address;

        if ($address) {
            $this->fill($address->toArray());
        }
    }

    public function save(): void
    {
        $tenant = $this->getTenant();

        if (! BillingManager::tenantCanUseStripe($tenant)) {
            return;
        }

        $address = $this->validate(BillingManager::billingAddressValidationRules());

        BillingManager::updateAddress($tenant, $address);

        $this->success = 'Address saved.';

        $this->dispatch('saved');
    }

    protected function getTenant(): Tenant
    {
        return tenant();
    }

    public function render(): View
    {
        return view('components.billing-address', ['tenantCanUseStripe' => BillingManager::tenantCanUseStripe($this->getTenant())]);
    }
}
