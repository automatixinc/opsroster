<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Tenants\Billing;

use App\Livewire\Tenant\SubscriptionBanner as TenantSubscriptionBanner;
use App\Models\Tenant;

class SubscriptionBanner extends TenantSubscriptionBanner
{
    public string $tenantId;

    protected function getTenant(): Tenant
    {
        return Tenant::find($this->tenantId);
    }
}
