<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Models\Tenant;
use App\BillingManager;

class BillingController
{
    public function show(Tenant $tenant)
    {
        return view('admin.tenants.billing.show', [
            'tenantId' => $tenant->id,
            'tenantCanUseStripe' => BillingManager::tenantCanUseStripe($tenant)
        ]);
    }
}
