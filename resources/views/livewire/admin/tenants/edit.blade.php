<div>
    @if ($message)
        <x-banner :message="$message" />
    @endif

    <div class="dark:text-white max-w-2xl mx-auto p-4">
        <form wire:submit.prevent="save" class="w-full">
            <!-- Header Section -->
            <div class="flex justify-between items-center mb-6 space-x-3">
                <div>
                    <x-button wire:click="impersonate" type="button">
                        <span class="flex items-center">
                            <svg class="size-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            Impersonate
                        </span>
                    </x-button>
                </div>

                <div class="flex space-x-3">
                    @if ($editing)
                        <x-secondary-button type="button" wire:click="cancel">
                            <span class="flex sm:space-x-1 items-center">
                                <svg class="size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                                <p class="hidden sm:block">
                                    Cancel
                                </p>
                            </span>
                        </x-secondary-button>
                        <x-button type="submit">
                            <span class="flex sm:space-x-1 items-center">
                                <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                <p class="hidden sm:block">
                                    Save
                                </p>
                            </span>
                        </x-button>
                    @else
                        <x-button type="button" wire:click="edit">
                            <span class="flex items-center">
                                <svg class="size-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                Edit
                            </span>
                        </x-button>
                    @endif
                </div>
            </div>

            <!-- Form Fields -->
            <div class="space-y-6 w-full">
                <div>
                    <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">ID</h3>
                    <p class="text-gray-600 dark:text-gray-400 p-2 px-3 bg-gray-50 dark:bg-gray-800 rounded-md w-full">{{ $tenantId }}</p>
                </div>

                <div>
                    <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Email</h3>
                    @if ($editing)
                        <x-input type="email" wire:model="email" class="w-full" />
                    @else
                        <p class="text-gray-600 dark:text-gray-400 p-2 px-3 bg-gray-50 dark:bg-gray-800 rounded-md w-full">{{ $tenant->email }}</p>
                    @endif
                </div>

                <div>
                    <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Company</h3>
                    @if ($editing)
                        <x-input type="text" wire:model="company" class="w-full" />
                    @else
                        <p class="text-gray-600 dark:text-gray-400 p-2 px-3 bg-gray-50 dark:bg-gray-800 rounded-md w-full">{{ $tenant->company }}</p>
                    @endif
                </div>

                <div>
                    <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Created at</h3>
                    <p class="text-gray-600 dark:text-gray-400 p-2 px-3 bg-gray-50 dark:bg-gray-800 rounded-md w-full">{{ $tenant->created_at->format('d M Y') }}</p>
                </div>

                <div>
                    <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Updated at</h3>
                    <p class="text-gray-600 dark:text-gray-400 p-2 px-3 bg-gray-50 dark:bg-gray-800 rounded-md w-full">{{ $tenant->updated_at->format('d M Y') }}</p>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="mt-8 pt-4 flex justify-between">
                <div class="flex space-x-3">
                    <a href="{{ route('admin.tenants.index') }}">
                        <x-secondary-button>
                            <span class="flex items-center">
                                <svg class="size-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                                Back
                            </span>
                        </x-secondary-button>
                    </a>

                    <a href="{{ route('admin.tenants.billing.show', $tenant) }}">
                        <x-button type="button">
                            <span class="flex items-center">
                                <svg class="size-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                                Billing
                            </span>
                        </x-button>
                    </a>
                </div>

                <a href="{{ route('admin.tenants.destroy', $tenant) }}">
                    <x-danger-button>
                        <span class="flex items-center">
                            <svg class="size-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                            Delete
                        </span>
                    </x-danger-button>
                </a>
            </div>
        </form>
    </div>

    <div class="mt-8 dark:text-white max-w-2xl mx-auto p-2">
        <livewire:admin.tenants.domain.domains :tenantId="$tenantId" />

        <x-section-border />

        <livewire:admin.tenants.domain.new-domain :tenantId="$tenantId" />

        <x-section-border />

        <livewire:admin.tenants.domain.fallback-domain :tenantId="$tenantId" />
    </div>
</div>
