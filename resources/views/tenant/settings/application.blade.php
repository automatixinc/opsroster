<x-app-layout title="Settings">
    <div>
        <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8 flex flex-col space-y-6">
            <x-slot name="header">
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    {{ __('Application settings') }}
                </h2>
            </x-slot>

            <!-- Application information -->
            <div>
                <livewire:tenant.application-info />
            </div>

            <x-section-border />

            <!-- Domain management -->
            <div>
                <livewire:tenant.domains />
            </div>

            <x-section-border />

            <div>
                <livewire:tenant.new-domain />
            </div>

            <x-section-border />

            <div>
                <livewire:tenant.fallback-domain />
            </div>
        </div>
    </div>
</x-app-layout>
