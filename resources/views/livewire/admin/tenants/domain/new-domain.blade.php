<form wire:submit="save" class="bg-white dark:bg-gray-800 sm:rounded-md p-3">
    <div class="col-span-6 sm:col-span-4 py-4">
        <x-label for="name" value="New domain"/>
        <x-input id="domain" class="mt-1 block w-full" autocomplete="off" wire:model="domain" type="text" placeholder="mydomain.com"/>
        <x-input-error for="domain" />
    </div>
    <div class="col-span-6 sm:col-span-4 flex justify-between items-center pt-4">
        <div>
            <x-action-message on="updated" class="me-3">
                Added.
            </x-action-message>
        </div>
        <x-button>
            Add
        </x-button>
    </div>
</form>
