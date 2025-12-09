<?php

declare(strict_types=1);

namespace App\Livewire\Tenant;

use Livewire\Component;
use App\BillingManager;

class Invoices extends Component
{
    protected $listeners = ['saved' => '$refresh'];

    public function render()
    {
        $tenant = tenant();
        $invoices = BillingManager::tenantCanUseStripe($tenant) ? $tenant->invoicesIncludingPending()->all() : [];

        return view('livewire.tenant.invoices', [
            'invoices' => BillingManager::formatInvoices($invoices),
        ]);
    }
}
