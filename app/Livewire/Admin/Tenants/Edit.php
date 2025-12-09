<?php

namespace App\Livewire\Admin\Tenants;

use App\Models\Tenant;
use Livewire\Component;
use Illuminate\Validation\Rule;

class Edit extends Component
{
    public string|null $message = null;
    public Tenant $tenant;
    public bool $editing = false;
    public string $originalEmail;
    public string $originalCompany;
    public string $email;
    public string $company;

    public function mount(Tenant $tenant)
    {
        abort_if($tenant->pending(), 403);

        $this->tenant = $tenant;
        $this->message = null;

        $this->originalEmail = $tenant->email;
        $this->originalCompany = $tenant->company;

        $this->email = $this->originalEmail;
        $this->company = $this->originalCompany;
    }

    public function edit(): void
    {
        $this->message = null;
        $this->editing = true;
    }

    public function cancel(): void
    {
        $this->email = $this->originalEmail;
        $this->company = $this->originalCompany;
        $this->editing = false;
    }

    public function save()
    {
        $validated = $this->validate();

        $this->tenant->update($validated);

        $this->editing = false;

        $this->originalEmail = $this->email;
        $this->originalCompany = $this->company;
        $this->message = 'Tenant updated successfully';
    }

    protected function rules()
    {
        return [
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('tenants', 'email')->ignore($this->tenant->id),
            ],
            'company' => 'required|string|max:255',
        ];
    }

    public function impersonate()
    {
        return redirect($this->tenant->impersonationUrl($this->tenant->getAdmin()->id));
    }

    public function render()
    {
        return view('livewire.admin.tenants.edit', [
            'tenantId' => $this->tenant->id,
            'created_at' => $this->tenant->created_at->format('d M Y'),
            'updated_at' => $this->tenant->updated_at->format('d M Y'),
        ])->layout(
            'layouts.central',
            ['title' => 'Edit']
        );
    }
}
