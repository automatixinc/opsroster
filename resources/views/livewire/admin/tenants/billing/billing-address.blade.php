<div class="grid grid-cols-2 mt-2">
    <div class="col-span-2">
        <h3 class="text-lg font-semibold mb-3">Billing address</h3>
        <form wire:submit.prevent="save" class="grid grid-cols-2 gap-4">
            <div class="col-span-1">
                <x-label class="mb-1" for="line1" value="Line 1" />
                <x-input wire:model="line1" class="w-full" type="text" id="line1" name="line1" placeholder="123 Laravel Street" />
                <x-input-error for="line1" />
            </div>

            <div class="col-span-1">
                <x-label class="mb-1" for="line2" value="Line 2" />
                <x-input wire:model="line2" class="w-full" type="text" id="line2" name="line2" placeholder="Apartment B" />
                <x-input-error for="line2" />
            </div>

            <div class="col-span-1">
                <x-label class="mb-1" for="city" value="City" />
                <x-input wire:model="city" class="w-full" type="text" id="city" name="city" placeholder="San Francisco" />
                <x-input-error for="city" />
            </div>

            <div class="col-span-1">
                <x-label class="mb-1" for="postal_code" value="Postal Code" />
                <x-input wire:model="postal_code" class="w-full" type="text" id="postal_code" name="postal_code" placeholder="12345" />
                <x-input-error for="postal_code" />
            </div>

            <div class="col-span-1">
                <x-label class="mb-1" for="country" value="Country" />
                <select wire:model="country" class="mt-1 block w-full form-select dark:bg-gray-900 dark:text-gray-300 rounded-md border-gray-300 dark:border-gray-700" id="country" name="country">
                    @foreach (config('saas.countries') as $countryCode => $countryName)
                        <option value="{{ $countryCode }}">{{ $countryName }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-span-1">
                <x-label class="mb-1" for="state" value="State" />
                <x-input wire:model="state" class="w-full" type="text" id="state" name="state" placeholder="California" />
                <x-input-error for="state" />
            </div>

            <div class="flex justify-end items-center col-span-2">
                <x-action-message on="saved" class="me-3">
                    Updated.
                </x-action-message>
                <x-button :disabled="! $tenantCanUseStripe">Update</x-button>
            </div>
        </form>
    </div>
</div>
