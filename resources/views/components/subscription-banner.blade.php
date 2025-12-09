@php
$redBannerStyle = 'bg-red-100 dark:bg-red-900 dark:opacity-90 dark:bg-opacity-50';
$redBannerText = 'text-red-800 dark:text-red-100';
$greenBannerStyle = 'bg-green-100 dark:bg-green-900 dark:opacity-90 dark:bg-opacity-50';
$greenBannerText = 'text-green-800 dark:text-green-100';
@endphp

<div class="mb-5">
    <div class="rounded-md p-4 {{ $activeSubscription ? $greenBannerStyle : $redBannerStyle }}">
        <div class="flex">
            <div>
                <svg class="h-5 w-5 {{ $activeSubscription ? 'text-green-400' : 'text-red-400' }}" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                </svg>
            </div>
            <div class="ml-3 text-sm font-medium {{ $activeSubscription ? $greenBannerText : $redBannerText }}">
                {!! $htmlMessage !!}
            </div>
        </div>
    </div>
</div>
