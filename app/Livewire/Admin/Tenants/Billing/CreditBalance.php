<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Tenants\Billing;

use App\BillingManager;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use App\Models\Tenant;

class CreditBalance extends Component
{
    protected $listeners = ['saved' => '$refresh'];

    public string $tenantId;
    public string $amount = '0';

    public function save(): void
    {
        $tenant = $this->getTenant();

        if (! BillingManager::tenantCanUseStripe($tenant)) {
            return;
        }

        $data = $this->validate(BillingManager::creditBalanceValidationRules());

        BillingManager::adjustCredit($tenant, (float) $data['amount']);

        $this->dispatch('saved');
    }

    protected function getTenant(): Tenant
    {
        return Tenant::find($this->tenantId);
    }

    public function render(): View
    {
        return view(
            'livewire.admin.tenants.billing.credit-balance',
            BillingManager::getCreditBalanceProps($this->getTenant())
        );
    }
}
