<ul class="bg-white dark:bg-gray-800 sm:rounded-md col-span-6 p-4 mt-4">
    @foreach ($domains as $domain)
        <x-domain :domain="$domain" :is-first-domain="$loop->first" :is-last-domain="$loop->last" wire:key="domain-{{ $domain->id }}" />
    @endforeach
</ul>
