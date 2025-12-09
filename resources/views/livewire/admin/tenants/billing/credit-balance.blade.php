<div class="col-span-2 my-6 border-t-2 border-gray-300 dark:border-gray-700">
    <h3 class="text-lg font-semibold my-3">Credit balance</h3>
    <form wire:submit="save">
        <div class="grid grid-cols-2 gap-4">
            <div class="col-span-1 flex flex-col">
                <x-label class="mb-1" value="Current balance" />
                <p>{{ $formatted }}</p>
            </div>

            <div class="col-span-1">
                <div class="flex flex-col">
                    <x-label class="mb-1" for="amount" value="Adjust credit balance (add or subtract)" />
                    <div class="flex">
                        <x-input-addon addonText="{{ $currency }}" wireModel="amount" class="w-full" type="text" name="amount" id="amount" value="0" />
                    </div>
                    <x-input-error for="amount" />
                </div>
            </div>

            <div class="col-span-2 flex justify-between items-center mt-4">
                <a href="{{ route('admin.tenants.edit', $tenantId) }}">
                    <x-secondary-button>
                        <span class="flex items-center">
                            <svg class="size-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                            Back
                        </span>
                    </x-secondary-button>
                </a>
                <x-button :disabled="! $tenantCanUseStripe">
                    Update
                </x-button>
            </div>
        </div>
    </form>
</div>
