<x-guest-layout title="Sign Up">
    <div>
        <x-authentication-card>
            <x-slot name="logo">
                <x-authentication-card-logo />
            </x-slot>

            <x-validation-errors class="mb-4" />

            <form action="{{ route('central.register.submit') }}" method="POST">
                @csrf

                <div>
                    <x-label for="company" value="Company" />
                    <x-input id="company" class="block mt-1 w-full" type="text" name="company" :value="old('company')" required autofocus autocomplete="name" />
                </div>

                <div class="mt-6">
                    <x-label for="name" value="Full name"/>

                    <x-input class="block mt-1 w-full" autocomplete="off" value="{{ old('name', '') }}" name="name" id="name" type="text" required />
                </div>

                <div class="mt-6">
                    <x-label for="domain" value="Domain"/>

                    <div class="mt-1 flex rounded-md shadow-sm">
                        <x-input-addon addonText=".{{ $centralDomain }}" autocomplete="off" value="{{ old('domain', '') }}" name="domain" id="domain" type="text" required autofocus/>
                    </div>

                    <x-label for="domain" class="text-xs text-gray-200 mt-1" value="You'll be able to add a custom branded domain after you sign up."/>

                    <x-input-error for="domain" />
                </div>

                <div class="mt-6">
                    <x-label for="email" value="Email address"/>

                    <x-input class="block mt-1 w-full" autocomplete="off" value="{{ old('email', '') }}" name="email" id="email" type="email" required/>

                    <x-input-error for="email" />
                </div>

                <div class="mt-6">
                    <x-label for="password" value="Password"/>

                    <x-input class="block mt-1 w-full" autocomplete="off" value="{{ old('password', '') }}" name="password" id="password" type="password" required/>

                    <x-input-error for="password" />
                </div>

                <div class="mt-6">
                    <x-label for="password_confirmation" value="Confirm password"/>

                    <x-input class="block mt-1 w-full" autocomplete="off" value="{{ old('password_confirmation', '') }}" name="password_confirmation" id="password_confirmation" type="password" required/>
                </div>

                <div class="flex items-center justify-end mt-5">
                    <a class="flex underline mt-2 mr-4 text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800" href="{{ route('central.login') }}">
                        {{ __('Already registered?') }}
                    </a>

                    <span class="rounded-md shadow-sm">
                        <x-button class="block mt-1">{{ __('Register') }}</x-button>
                    </span>
                </div>
            </form>
        </x-authentication-card>
    </div>
</x-guest-layout>
