<x-app-layout title="Posts">
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Posts') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="overflow-hidden sm:rounded-lg">
                <div class="max-w-7xl m-5">
                    <div class="w-full flex justify-end">
                        <a href="{{ route('tenant.posts.create') }}">
                            <x-button>New post</x-button>
                        </a>
                    </div>
                    <div>
                        @foreach($posts as $post)
                            <a href="{{ route('tenant.posts.show', $post) }}">
                                <div class="block mt-8 rounded-lg shadow overflow-hidden dark:bg-gray-800">
                                    <div class="bg-white dark:bg-gray-700 p-6">
                                        <h3 class="text-xl font-semibold text-gray-900 dark:text-white">
                                            {{ $post->title }}
                                        </h3>
                                        <p class="mt-3 text-base text-gray-800 dark:text-gray-200">
                                            {{ Str::limit($post->body, 160) }}
                                        </p>
                                        <div class="mt-6 flex items-center">
                                            <div class="">
                                                <a href="#">
                                                    <img class="h-10 w-10 rounded-full" src="{{ $post->author->profile_photo_url }}" alt="{{ $post->author->name }}" />
                                                </a>
                                            </div>
                                            <div class="ml-3">
                                                <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                    <a href="#" class="">
                                                        {{ $post->author->name }}
                                                    </a>
                                                </p>
                                                <div class="flex text-sm text-gray-500 dark:text-gray-300">
                                                    <time datetime="{{ $post->created_at->format('Y-m-d') }}">
                                                        {{ $post->created_at->format('M d, Y') }}
                                                    </time>
                                                    <span class="mx-1">
                                                        &middot;
                                                    </span>
                                                    <span>
                                                        {{ count(explode(' ', $post->body)) }} words
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
