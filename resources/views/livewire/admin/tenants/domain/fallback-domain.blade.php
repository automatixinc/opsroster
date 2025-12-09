<form wire:submit="save" class="bg-white dark:bg-gray-800 sm:rounded-md p-3">
    <div class="col-span-6 sm:col-span-4 py-4">
        <x-label for="fallbackDomain" value="Fallback subdomain"/>
        <div class="flex mt-1">
            <x-input-addon addonText=".{{ config('tenancy.identification.central_domains')[0] }}" wire:model="domain" type="text" name="fallbackDomain" id="fallbackDomain" />
        </div>
        <x-input-error for="domain" class="mt-2" />
    </div>

    <div class="col-span-6 sm:col-span-4 flex justify-between items-center pt-4">
        <div>
            <x-action-message on="updated" class="me-3">
                Saved.
            </x-action-message>
        </div>
        <x-button>
            Save
        </x-button>
    </div>
</form>
