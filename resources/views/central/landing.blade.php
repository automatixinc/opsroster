<x-layouts.central title="Landing">
    <div class="text-center dark:text-white leading-loose mx-6 md:mx-0">
        <h1 class="font-bold text-4xl">Awesome SaaS</h1>
        <p>This is a sample landing page. Of course, replace it completely.</p>
        <p>It's meant to guide you towards the onboarding flow &mdash; the registration feature and the login feature.</p>
    </div>

    <div class="flex justify-center mt-4">
        <a href="{{ route('central.login') }}">
            <x-button>Login</x-button>
        </a>
        <a href="{{ route('central.register') }}">
            <x-button class="ml-2">Register</x-button>
        </a>
    </div>
</x-layouts.central>
