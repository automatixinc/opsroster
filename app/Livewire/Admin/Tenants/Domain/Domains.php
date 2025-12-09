<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Tenants\Domain;

use App\Livewire\Tenant\Domains as TenantDomains;
use App\Models\Tenant;
use App\DomainManager;
use Illuminate\Contracts\View\View;

class Domains extends TenantDomains
{
    public string $tenantId;

    protected function getTenant(): Tenant
    {
        return Tenant::find($this->tenantId);
    }

    public function render(): View
    {
        return view('livewire.admin.tenants.domain.domains', [
            'domains' => DomainManager::getDomains($this->getTenant()),
        ]);
    }
}
