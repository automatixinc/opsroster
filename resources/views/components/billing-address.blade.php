<x-form-section submit="save">
    <x-slot name="title">
        {{ __('Billing address') }}
    </x-slot>

    <x-slot name="description">
        {{ __('Edit your billing address.') }}
    </x-slot>

    <x-slot name="form">
        <div class="col-span-3">
            <x-label for="line1" value="Line 1"/>
            <x-input wire:model="line1" class="mt-1 block w-full" type="text" id="line1" name="line1" placeholder="123 Laravel Street" />
            <x-input-error for="line1" />
        </div>

        <div class="col-span-3">
            <x-label for="line2" value="Line 2"/>
            <x-input wire:model="line2" class="mt-1 block w-full" type="text" id="line2" name="line2" placeholder="Apartment B" />
            <x-input-error for="line2" />
        </div>

        <div class="col-span-3">
            <x-label for="city" value="City"/>
            <x-input wire:model="city" class="mt-1 block w-full" type="text" id="city" name="city" placeholder="San Francisco" />
            <x-input-error for="city" />
        </div>

        <div class="col-span-3">
            <x-label for="postal_code" value="Postal Code"/>
            <x-input wire:model="postal_code" class="mt-1 block w-full" type="text" id="postal_code" name="postal_code" placeholder="12345" />
            <x-input-error for="postal_code" />
        </div>

        <div class="col-span-3">
            <x-label for="country" value="Country"/>
            <select wire:model="country" class="mt-1 block w-full form-select dark:bg-gray-900 dark:text-gray-300 rounded-md border-gray-300 dark:border-gray-700" id="country" name="country" type="text">
                @foreach (config('saas.countries') as $countryCode => $countryName)
                    <option value="{{ $countryCode }}">{{ $countryName }}</option>
                @endforeach
            </select>
        </div>

        <div class="col-span-3">
            <x-label for="state" value="State"/>
            <x-input wire:model="state" class="mt-1 block w-full" type="text" id="state" name="state" placeholder="California" />
            <x-input-error for="state" />
        </div>
    </x-slot>

    <x-slot name="actions">
        <x-action-message on="saved" class="me-3">
            {{ __('Billing address updated.') }}
        </x-action-message>

        <x-button :disabled="! $tenantCanUseStripe">Save</x-button>
    </x-slot>
</x-form-section>
