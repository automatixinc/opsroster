<x-form-section submit="save">
    <x-slot name="title">
        {{ __('Information') }}
    </x-slot>

    <x-slot name="description">
        {{ __("Customize your application's details.") }}
    </x-slot>

    <x-slot name="form">
        <div class="col-span-6 sm:col-span-4">
            <x-label for="company" value="Company"/>
            <x-input class="mt-1 block w-full" type="text" wire:model="company" id="company" name="company" placeholder="My company"/>
            <x-input-error for="company" />
        </div>
    </x-slot>

    <x-slot name="actions">
        <x-action-message on="saved" class="me-3">
            {{ __('Info updated.') }}
        </x-action-message>

        <x-button>Save</x-button>
    </x-slot>
</x-form-section>
