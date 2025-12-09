<x-guest-layout title="Login">
    <div>
        <x-authentication-card>
            <x-slot name="logo">
                <x-authentication-card-logo />
            </x-slot>

            <form action="{{ route('central.login.submit') }}" method="POST">
                @csrf
                <div>
                    <x-label for="email" value="{{ __('Email') }}" />
                    <x-input class="block mt-1 w-full" value="{{ old('email', '') }}" id="email" name="email" type="email" required autocomplete="off" />
                    <x-input-error class="mt-2" for="email" />
                </div>

                <div class="flex items-center justify-end mt-5">
                    <a class="flex underline mt-2 mr-4 text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800" href="{{ route('central.register') }}">
                        {{ __('Not registered yet?') }}
                    </a>

                    <span class="rounded-md shadow-sm">
                        <x-button class="block mt-1">{{ __('Login') }}</x-button>
                    </span>
                </div>
            </form>
        </x-authentication-card>
    </div>
</x-guest-layout>
