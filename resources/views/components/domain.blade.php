<li @if(! $isFirstDomain) class="border-t border-gray-200 dark:border-gray-700" @endif
    x-data="{ open: false }"
    x-init="open = false"
>
    <div class="focus:outline-none focus:bg-gray-50 transition duration-150 ease-in-out">
        <div class="py-4">
            <div class="flex items-center justify-between">
                <div class="text-sm font-medium text-indigo-500">
                    {{ $domain->domain }}
                </div>

                <div class="flex items-center space-x-4">
                    <!-- Badges -->
                    <div class="flex items-center space-x-2">
                        @if($domain->is_fallback)
                            <x-badge variant="primary">Fallback</x-badge>
                        @endif
                        @if($domain->certificate_status === 'issued')
                            <x-badge variant="success">Certificate issued</x-badge>
                        @elseif($domain->certificate_status === 'pending')
                            <x-badge variant="warning">Pending</x-badge>
                        @elseif($domain->certificate_status === 'revoked')
                            <x-badge variant="danger">Certificate revoked</x-badge>
                        @endif
                        @if($domain->is_primary)
                            <x-badge variant="success">Primary</x-badge>
                        @endif
                    </div>

                    <!-- Desktop Action Buttons -->
                    <div class="hidden lg:flex items-center space-x-2">
                        @if($domain->certificate_status === 'issued')
                            <x-secondary-button class="px-4 py-2 tracking-widest" wire:click="revokeCertificate({{ $domain->id }})">
                                Revoke certificate
                            </x-secondary-button>
                        @else
                            <x-secondary-button class="px-4 py-2 tracking-widest" wire:click="requestCertificate({{ $domain->id }})">
                                Request certificate
                            </x-secondary-button>
                        @endif
                        @if(!$domain->is_primary)
                            <x-secondary-button class="px-4 py-2 tracking-widest" wire:click="makePrimary({{ $domain->id }})">
                                Make primary
                            </x-secondary-button>
                        @endif
                        @if(!$domain->is_primary && !$domain->is_fallback)
                            <x-danger-button class="px-4 py-2 tracking-widest" wire:click="delete({{ $domain->id }})">
                                Delete
                            </x-danger-button>
                        @endif
                    </div>

                    <!-- Mobile Menu -->
                    <div class="lg:hidden relative">
                        <button @click="open = !open" type="button" class="inline-flex items-center justify-center p-2 rounded-md
                            text-gray-400 dark:text-gray-500 hover:text-gray-500 dark:hover:text-gray-400 hover:bg-gray-100
                            dark:hover:bg-gray-900 focus:outline-none focus:bg-gray-100 dark:focus:bg-gray-900 focus:text-gray-500
                            dark:focus:text-gray-400 transition duration-150 ease-in-out"
                        >
                            <svg x-show="!open" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                              <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 12a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0ZM12.75 12a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0ZM18.75 12a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z" />
                            </svg>
                            <svg x-show="open" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                              <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                        <div
                            x-show="open"
                            @click.away="open = false"
                            @keydown.escape.window="open = false"
                            x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="transform opacity-0 scale-95"
                            x-transition:enter-end="transform opacity-100 scale-100"
                            x-transition:leave="transition ease-in duration-75"
                            x-transition:leave-start="transform opacity-100 scale-100"
                            x-transition:leave-end="transform opacity-0 scale-95"
                            class="{{ $isLastDomain && ! $isFirstDomain  ? 'mb-2 bottom-full' : 'mt-2 top-full' }} right-0 absolute z-50 w-48 rounded-md shadow-lg bg-white dark:bg-gray-900 ring-1 ring-black ring-opacity-5 py-1"
                        >
                            @if($domain->certificate_status === 'issued')
                                <button type="button" class="block w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700" wire:click="revokeCertificate({{ $domain->id }})" @click="open = false">
                                    Revoke certificate
                                </button>
                            @else
                                <button type="button" class="block w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700" wire:click="requestCertificate({{ $domain->id }})" @click="open = false">
                                    Request certificate
                                </button>
                            @endif
                            @if(!$domain->is_primary)
                                <button type="button" class="block w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700" wire:click="makePrimary({{ $domain->id }})" @click="open = false">
                                    Make primary
                                </button>
                            @endif
                            @if(!$domain->is_primary && !$domain->is_fallback)
                                <button type="button" class="block w-full text-left px-4 py-2 text-sm text-red-600 dark:text-red-400 hover:bg-gray-100 dark:hover:bg-gray-700" wire:click="delete({{ $domain->id }})" @click="open = false">
                                    Delete
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            <div class="mt-2 sm:flex sm:justify-between">
                <div>
                    <div class="mr-6 flex items-center text-sm text-gray-500 dark:text-gray-400">
                        <svg class="h-5 w-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                        </svg>
                        <div class="ml-1">
                            {{ ucfirst($domain->type) }}
                        </div>
                    </div>
                </div>
                <div class="mt-2 flex items-center text-sm text-gray-500 dark:text-gray-400 sm:mt-0">
                    <svg class="h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/>
                    </svg>
                    <div class="ml-1">
                        Added on
                        <time datetime="{{ $domain->created_at->format('Y-m-d') }}">
                            {{ $domain->created_at->format('M d, Y') }}
                        </time>
                    </div>
                </div>
            </div>
        </div>
    </div>
</li>
