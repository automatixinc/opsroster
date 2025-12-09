<x-app-layout title="Subscription Expired">
    <div class="flex justify-center text-xl leading-loose text-center">
        <div class="dark:text-gray-100">
            <h1 class="mt-6 text-2xl font-bold">Application subscription expired.</h1>

            <p>The app will become available once its administrator renews the subscription.</p>

            <a href="javascript:window.location.reload()" class="inline-flex mt-4 rounded-md shadow-sm">
                <x-button type="button" class="uppercase">Retry</x-button>
            </a>
        </div>
    </div>
</x-app-layout>
