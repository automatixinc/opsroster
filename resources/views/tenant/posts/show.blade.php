<x-app-layout :title="$post->title">
    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <h2 class="text-gray-900 dark:text-white text-xl">{{ $post->title }}</h2>
            <p class="text-gray-800 dark:text-gray-200 py-3">{{ $post->body }}</p>
            @if($userCanDeletePost)
                <form method="POST" action="{{ route('tenant.posts.delete', $post) }}">
                    @csrf
                    <x-danger-button type="submit">
                        Delete
                    </x-danger-button>
                </form>
            @endif
        </div>
    </div>
</x-app-layout>
