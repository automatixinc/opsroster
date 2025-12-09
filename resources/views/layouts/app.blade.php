<x-layouts.app {{ $attributes }}>
    @isset($header)
        <x-slot name="header">
            {{ $header }}
        </x-slot>
    @endisset

    {{ $slot }}
</x-layouts.app>
