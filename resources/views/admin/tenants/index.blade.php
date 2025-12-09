<x-layouts.central title="Tenants">
    <div class="dark:text-white max-w-2xl mx-auto">
        @if (count($tenants))
        <div class="min-w-full grid grid-cols-3 gap-4 p-2">
            <div class="col-span-3 grid grid-cols-3 p-2 font-bold w-full border-b dark:border-gray-600 border-gray-300">
                <div class="col-span-1">E-mail</div>
                <div class="col-span-1">Created at</div>
            </div>
            @foreach ($tenants as $tenant)
                <div id="tenant-{{ $loop->index }}" class="col-span-3 grid grid-cols-3 p-4 w-full @if(! $loop->last) border-b dark:border-gray-700 border-gray-300 @endif">
                    <div class="col-span-1 overflow-hidden text-ellipsis whitespace-nowrap">{{ $tenant->email }}</div>
                    <div class="col-span-1">{{ $tenant->created_at->format('d M Y') }}</div>
                    <div class="col-span-1 flex justify-end space-x-2">
                        @if ($tenant->pending())
                            <x-secondary-button disabled>
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                                </svg>
                                <span class="sr-only">View</span>
                            </x-secondary-button>
                        @else
                            <a href="{{ route('admin.tenants.edit', $tenant->id) }}">
                                <x-secondary-button>
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                                    </svg>
                                    <span class="sr-only">View</span>
                                </x-secondary-button>
                            </a>
                        @endif

                        <a href="{{ route('admin.tenants.destroy', $tenant->id) }}">
                            <x-danger-button>
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                </svg>
                                <span class="sr-only">Delete</span>
                            </x-danger-button>
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
        @else
            <div class="text-center pt-8">
                Your app has no tenants yet.
            </div>
        @endif

        <div class="w-full flex justify-end pt-8 px-6">
            <a href="{{ route('admin.tenants.create') }}">
                <x-button>
                    Create tenant
                </x-button>
            </a>
        </div>

        <!-- Pagination Links -->
        <div class="mt-12">
            {{ $tenants->links() }}
        </div>
    </div>
</x-layouts.central>
