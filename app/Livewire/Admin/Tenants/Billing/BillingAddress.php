<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Tenants\Billing;

use App\Models\Tenant;
use Illuminate\Contracts\View\View;
use App\BillingManager;
use App\Livewire\Tenant\BillingAddress as TenantBillingAddress;

class BillingAddress extends TenantBillingAddress
{
    public string $tenantId;

    protected function getTenant(): Tenant
    {
        return Tenant::find($this->tenantId);
    }

    public function render(): View
    {
        return view('livewire.admin.tenants.billing.billing-address', ['tenantCanUseStripe' => BillingManager::tenantCanUseStripe($this->getTenant())]);
    }
}
