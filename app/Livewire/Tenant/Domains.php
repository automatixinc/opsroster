<?php

declare(strict_types=1);

namespace App\Livewire\Tenant;

use Illuminate\Contracts\View\View;
use Livewire\Component;
use App\Models\Domain;
use App\DomainManager;
use App\Models\Tenant;

class Domains extends Component
{
    protected $listeners = ['updated' => '$refresh'];

    public function makePrimary(Domain $domain): void
    {
        abort_unless($domain->tenant->is($this->getTenant()), 403);

        DomainManager::makePrimary($domain);

        $this->dispatch('updated');
    }

    public function delete(Domain $domain): void
    {
        abort_unless($domain->tenant->is($this->getTenant()), 403);

        DomainManager::delete($domain);

        $this->dispatch('updated');
    }

    public function requestCertificate(Domain $domain): void
    {
        abort_unless($domain->tenant->is($this->getTenant()), 403);

        DomainManager::requestCertificate($domain);
    }

    public function revokeCertificate(Domain $domain): void
    {
        abort_unless($domain->tenant->is($this->getTenant()), 403);

        DomainManager::revokeCertificate($domain);
    }

    protected function getTenant(): Tenant
    {
        return tenant();
    }

    public function render(): View
    {
        return view('livewire.tenant.domains', [
            'domains' => DomainManager::getDomains($this->getTenant()),
        ]);
    }
}
