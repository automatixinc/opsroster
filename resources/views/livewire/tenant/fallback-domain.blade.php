<x-form-section submit="save">
    <x-slot name="title">
        {{ __('Fallback subdomain') }}
    </x-slot>

    <x-slot name="description">
        {{ __('Change fallback domain.') }}
    </x-slot>

    <x-slot name="form">
        <div class="col-span-6">
            <x-label for="fallbackDomain" value="Fallback domain"/>

            <div class="flex">
                <x-input-addon addonText=".{{ config('tenancy.identification.central_domains')[0] }}" wire:model="domain" type="text" name="fallbackDomain" id="fallbackDomain"/>
            </div>

            <x-input-error for="domain" />
        </div>
    </x-slot>

    <x-slot name="actions">
        <x-action-message on="saved" class="me-3">
            {{ __('Fallback domain updated.') }}
        </x-action-message>

        <x-button>Save</x-button>
    </x-slot>
</x-form-section>
