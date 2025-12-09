<x-action-section>
    <x-slot name="title">Invoices</x-slot>
    <x-slot name="description">All issued invoices.</x-slot>
    <x-slot name="content">
        <div class="bg-white dark:bg-gray-800 overflow-hidden sm:rounded-md">
            @if($invoices)
            <ul x-data>
                @foreach($invoices as $invoice)
                <li
                @if(! $loop->first)
                class="border-t border-gray-200 dark:border-gray-700"
                @endif>
                <div class="block focus:outline-none focus:bg-gray-50 transition duration-150 ease-in-out">
                    <div class="py-4">
                        <div class="flex items-center justify-between">
                            <div class="text-sm leading-5 font-medium dark:text-indigo-500 text-indigo-600 truncate">
                                {{ $invoice['number'] }}
                            </div>
                            <div class="ml-2 flex shrink-0 items-center">
                                @if($invoice['paid'])
                                <x-badge variant="success">Paid</x-badge>
                                @else
                                <x-badge variant="warning">Pending</x-badge>
                                @endif
                                <span class="ml-2">
                                    <x-secondary-button @click="window.open('{{ route('tenant.invoice.download', ['id' => $invoice['id']]) }}', '_blank')" class="inline-flex items-center px-2.5 py-1.5 border border-gray-300 text-xs leading-4 font-medium rounded-md shadow-sm text-gray-700 bg-white hover:text-gray-500 focus:outline-none focus:border-blue-300 focus:ring-blue active:text-gray-800 active:bg-gray-50 transition ease-in-out duration-150">
                                        Download
                                    </x-secondary-button>
                                </span>
                            </div>
                        </div>
                        <div class="mt-2 sm:flex sm:justify-between">
                            <div class="sm:flex">
                                <div class="mr-6 flex items-center text-sm leading-5 text-gray-500 dark:text-gray-400">
                                    <svg class="shrink-0 mr-1.5 h-5 w-5 text-gray-500 dark:text-gray-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path></svg>
                                    {{ $invoice['total'] }}
                                </div>
                            </div>
                            <div class="mt-2 flex items-center text-sm leading-5 text-gray-500 dark:text-gray-400 sm:mt-0">
                                <svg class="shrink-0 mr-1.5 h-5 w-5 text-gray-500 dark:text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/>
                                </svg>
                                <span>
                                    Issued on
                                    <time datetime="{{ $invoice['date']['datetime'] }}">
                                        {{ $invoice['date']['text'] }}
                                    </time>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </li>
            @endforeach
            </ul>
            @else
            <p class="p-4 text-sm text-gray-500 dark:text-gray-400">
                No invoices issued yet.
            </p>
            @endif
        </div>
    </x-slot>
</x-action-section>
