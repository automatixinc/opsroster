<?php

declare(strict_types=1);

namespace App\Livewire\Tenant;

use Illuminate\Contracts\View\View;
use Livewire\Component;
use App\Models\Tenant;
use App\BillingManager;

class SubscriptionBanner extends Component
{
    protected $listeners = ['saved' => '$refresh'];

    protected function getTenant(): Tenant
    {
        return tenant();
    }

    public function render(): View
    {
        return view('components.subscription-banner', BillingManager::getSubscriptionBannerProps($this->getTenant()));
    }
}
