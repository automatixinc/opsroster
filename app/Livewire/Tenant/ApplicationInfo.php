<?php

declare(strict_types=1);

namespace App\Livewire\Tenant;

use Illuminate\Contracts\View\View;
use Livewire\Component;

class ApplicationInfo extends Component
{
    public $company;

    public function mount(): void
    {
        $this->company = tenant()->company;
    }

    public function save(): void
    {
        $validated = $this->validate([
            'company' => 'required|string|max:255',
        ]);

        tenant()->update($validated);

        $this->dispatch('saved');
    }

    public function render(): View
    {
        return view('livewire.tenant.application-info');
    }
}
