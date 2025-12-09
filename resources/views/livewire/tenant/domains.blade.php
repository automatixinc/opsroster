<x-action-section>
    <x-slot name="title">
        {{ __('Domains') }}
    </x-slot>

    <x-slot name="description">
        {{ __('Manage your application domains.') }}
    </x-slot>

    <x-slot name="content">
        <div>
            <div class="bg-white dark:bg-gray-800 sm:rounded-md">
                <ul>
                    @foreach($domains as $domain)
                        <x-domain :domain="$domain" :is-first-domain="$loop->first" :is-last-domain="$loop->last" wire:key="domain-{{ $domain->id }}" />
                    @endforeach
                </ul>
            </div>
        </div>
    </x-slot>
</x-action-section>
