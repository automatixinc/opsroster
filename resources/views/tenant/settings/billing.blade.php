<x-app-layout title="Billing">
    <div>
        <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8 flex flex-col space-y-6">
            <livewire:tenant.subscription-banner />

            <x-slot name="header">
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    {{ __('Billing') }}
                </h2>
            </x-slot>

            <livewire:tenant.upcoming-payment />

            <x-section-border />

            <livewire:tenant.billing-address />

            <x-section-border />

            <livewire:tenant.invoices />

            <x-section-border />

            <livewire:tenant.subscription-plan />

            <x-section-border />

            <livewire:tenant.payment-method />
        </div>
    </div>
</x-app-layout>
