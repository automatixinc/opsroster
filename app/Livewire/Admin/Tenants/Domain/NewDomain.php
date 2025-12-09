<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Tenants\Domain;

use Illuminate\Contracts\View\View;
use App\Livewire\Tenant\NewDomain as TenantNewDomain;
use App\Models\Tenant;

class NewDomain extends TenantNewDomain
{
    public string $tenantId;

    protected function getTenant(): Tenant
    {
        return Tenant::find($this->tenantId);
    }

    public function render(): View
    {
        return view('livewire.admin.tenants.domain.new-domain');
    }
}
