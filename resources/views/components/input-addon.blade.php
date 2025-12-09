@props(['disabled' => false, 'addonText' => '', 'wireModel' => null])

<input @if($wireModel) wire:model="{{ $wireModel }}" @endif {{ $disabled ? 'disabled' : '' }} {!! $attributes->merge(['class' => "block w-full rounded-none rounded-l-md border-gray-300 dark:border-gray-700 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 sm:text-sm dark:bg-gray-900 dark:text-gray-300"]) !!}>
<span class="relative -ml-px inline-flex items-center space-x-2 rounded-r-md border border-gray-300 dark:border-gray-700 bg-gray-50 px-4 py-2 text-sm font-medium text-gray-500 dark:bg-gray-900 dark:text-gray-300">
    {{ $addonText }}
</span>
