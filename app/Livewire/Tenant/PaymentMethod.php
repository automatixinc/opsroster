<?php

declare(strict_types=1);

namespace App\Livewire\Tenant;

use App\BillingManager;
use Livewire\Component;
use Illuminate\Contracts\View\View;

class PaymentMethod extends Component
{
    protected $listeners = ['saved' => '$refresh'];

    public string $paymentMethod = '';

    public function mount(): void
    {
        $this->paymentMethod = tenant()->hasDefaultPaymentMethod() ? tenant()->defaultPaymentMethod()->id : '';
    }

    public function save(): void
    {
        $tenant = tenant();

        if (! BillingManager::tenantCanUseStripe($tenant)) {
            return;
        }

        $this->validate([
            'paymentMethod' => 'required|string|regex:/^pm/',
        ]);

        $tenant->updateDefaultPaymentMethod($this->paymentMethod);

        $this->dispatch('saved');
    }

    public function render(): View
    {
        return view('livewire.tenant.payment-method', BillingManager::getPaymentMethodProps(tenant()));
    }
}
