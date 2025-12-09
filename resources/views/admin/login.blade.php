<x-guest-layout title="Admin Login">
    <div>
        <x-authentication-card>
            <x-slot name="logo">
                <x-authentication-card-logo />
            </x-slot>

            <x-validation-errors class="mb-4" />

            <form action="{{ route('admin.login.submit') }}" method="POST">
                @csrf
                <div>
                    <x-label for="email" value="{{ __('Email') }}" />
                    <x-input class="block mt-1 w-full" value="{{ old('email', '') }}" id="email" name="email" type="email" required />
                </div>

                <div class="mt-4">
                    <x-label for="password" value="{{ __('Password') }}" />
                    <x-input class="block mt-1 w-full" value="{{ old('password', '') }}" id="password" name="password" type="password" required autocomplete="off" />
                </div>

                <div class="flex items-center justify-end mt-5">
                    <span class="rounded-md shadow-sm">
                        <x-button class="block mt-1">{{ __('Login') }}</x-button>
                    </span>
                </div>
            </form>
        </x-authentication-card>
    </div>
</x-guest-layout>
