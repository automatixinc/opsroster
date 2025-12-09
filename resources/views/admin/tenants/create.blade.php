<x-layouts.central title="Create Tenant">
    <div class="dark:text-white max-w-2xl mx-auto p-4">
        <form method="POST" action="{{ route('admin.tenants.store') }}">
            @csrf

            <div class="mb-4">
                <x-label class="block text-sm mb-1" for="domain">Domain</x-label>
                <div class="flex">
                    <x-input-addon addonText=".{{ $centralDomain }}" class="w-full" type="text" name="domain" id="domain" value="{{ old('domain') }}" required />
                </div>
                @error('domain')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>

            <div class="mb-4">
                <x-label class="block text-sm mb-1" for="company">Company</x-label>
                <x-input class="w-full" type="text" name="company" id="company" value="{{ old('company') }}" required />
                @error('company')
                    <x-input-error for="company" />
                @enderror
            </div>

            <div class="mb-4">
                <x-label class="block text-sm mb-1" for="name">Full name</x-label>
                <x-input class="w-full" type="text" name="name" id="name" value="{{ old('name') }}" required />
                @error('name')
                    <x-input-error for="name" />
                @enderror
            </div>

            <div class="mb-4">
                <x-label class="block text-sm mb-1" for="email">Email</x-label>
                <x-input class="w-full" type="email" name="email" id="email" value="{{ old('email') }}" required />
                @error('email')
                    <x-input-error for="email" />
                @enderror
            </div>

            <div class="mb-4">
                <x-label class="block text-sm mb-1" for="password">Password</x-label>
                <x-input class="w-full" type="password" name="password" id="password" value="{{ old('password') }}" required />
                @error('password')
                    <x-input-error for="password" />
                @enderror
            </div>

            <div class="mb-4">
                <x-label class="block text-sm mb-1" for="password_confirmation" value="Confirm password"/>
                <x-input class="w-full" autocomplete="off" value="{{ old('password_confirmation', '') }}" name="password_confirmation" id="password_confirmation" type="password" required/>
                @error('password_confirmation')
                    <x-input-error for="password_confirmation" />
                @enderror
            </div>

            <div class="mt-4 flex justify-between">
                <a href="{{ route('admin.tenants.index') }}">
                    <x-secondary-button>
                        Cancel
                    </x-secondary-button>
                </a>

                <x-button>
                    Create Tenant
                </x-button>
            </div>
        </form>
    </div>
</x-layouts.central>
