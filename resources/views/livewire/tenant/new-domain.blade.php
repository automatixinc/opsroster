<x-form-section submit="save">
    <x-slot name="title">
        {{ __('Custom domain') }}
    </x-slot>

    <x-slot name="description">
        {{ __('Add a custom domain.') }}
    </x-slot>

    <x-slot name="form">
        <div class="col-span-6 sm:col-span-4">
            <x-label for="domain" value="Domain"/>
            <x-input id="domain" class="mt-1 block w-full" autocomplete="off" wire:model="domain" type="text" placeholder="mydomain.com"/>
            <x-input-error for="domain" />
        </div>
    </x-slot>

    <x-slot name="actions">
        <x-action-message on="updated" class="me-3">
            {{ __('Domain added.') }}
        </x-action-message>

        <x-button>{{ __('Add') }}</x-button>
    </x-slot>
</x-form-section>
