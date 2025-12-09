<?php

declare(strict_types=1);

namespace App\Livewire\Tenant;

use App\BillingManager;
use Livewire\Component;

class UpcomingPayment extends Component
{
    protected $listeners = ['saved' => '$refresh'];

    public function render()
    {
        return view('livewire.tenant.upcoming-payment', BillingManager::getUpcomingPaymentProps(tenant()));
    }
}
