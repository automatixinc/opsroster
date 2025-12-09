<x-layouts.central title="Billing">
    <div class="dark:text-white max-w-2xl mx-auto p-4">
        <livewire:admin.tenants.billing.subscription-banner :tenantId="$tenantId" />

        <livewire:admin.tenants.billing.billing-address :tenantId="$tenantId" />

        <livewire:admin.tenants.billing.credit-balance :tenantId="$tenantId" />
    </div>
</x-layouts.central>
