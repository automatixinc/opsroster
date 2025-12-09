<?php

declare(strict_types=1);

namespace App\Livewire\Tenant;

use Illuminate\Contracts\View\View;
use Livewire\Component;
use Livewire\Features\SupportValidation\HandlesValidation;
use Stancl\Tenancy\Resolvers\DomainTenantResolver;
use App\Models\Tenant;
use App\DomainManager;

class FallbackDomain extends Component
{
    use HandlesValidation;

    public string $domain;

    public function mount(): void
    {
        $this->domain = $this->getTenant()->fallback_domain->domain;
    }

    public function save()
    {
        $tenant = $this->getTenant();

        $oldFallback = $tenant->fallback_domain;

        $this->validate(DomainManager::fallbackValidationRules($oldFallback));

        DomainManager::storeFallback($this->domain, $tenant);

        $this->dispatch('updated');

        // If we were visiting the old fallback, which was deleted,
        // we'll redirect the user to the new fallback domain.
        if (tenancy()->initialized && $oldFallback->is(DomainTenantResolver::$currentDomain)) {
            return redirect($tenant->impersonationUrl(auth()->id(), 'tenant.settings.application'));
        }
    }

    protected function getTenant(): Tenant
    {
        return tenant();
    }

    public function render(): View
    {
        return view('livewire.tenant.fallback-domain');
    }
}
