<?php

declare(strict_types=1);

namespace App\Livewire\Tenant;

use Illuminate\Contracts\View\View;
use Livewire\Component;
use App\DomainManager;
use App\Models\Tenant;

class NewDomain extends Component
{
    protected $listeners = ['updated' => '$refresh'];

    public $domain = '';

    public function save(): void
    {
        $tenant = $this->getTenant();

        $this->validate(DomainManager::domainValidationRules($tenant));

        DomainManager::createDomain($this->domain, $tenant);

        $this->dispatch('updated');

        $this->domain = '';
    }

    protected function getTenant(): Tenant
    {
        return tenant();
    }

    public function render(): View
    {
        return view('livewire.tenant.new-domain');
    }
}
