<x-app-layout title="Create Post">
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('New post') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <form method="POST" action="{{ route('tenant.posts.store') }}">
                @csrf
                <div>
                    <div>
                        <div class="grid grid-cols-1 sm:grid-cols-6 gap-6">
                            <div class="sm:col-span-3">
                                <x-label for="title" value="Title"/>

                                <div class="mt-1 flex rounded-md">
                                    <x-input id="title" name="title" type="text" value="{{ old('title') }}" />
                                </div>

                                <x-input-error for="title" />
                            </div>

                            <div class="sm:col-span-6">
                                <x-label for="body" value="Body"/>
                                <div class="mt-1 rounded-md">
                                    <textarea id="body" name="body" rows="5" class="
                                        focus:ring focus:ring-opacity-50 placeholder-gray-400 rounded-md shadow-sm mt-1 block w-full @error('body') border-red-500 @enderror
                                        border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600
                                    ">{{ old('body') }}</textarea>
                                </div>
                                <x-input-error for="body" />
                            </div>

                        </div>
                    </div>
                </div>

                <div class="mt-8 pt-5">
                    <div class="flex justify-end">
                        <span class="inline-flex rounded-md shadow-sm">
                            <a href="{{ route('tenant.posts.index') }}">
                                <x-secondary-button>Cancel</x-secondary-button>
                            </a>
                        </span>
                        <span class="ml-3 inline-flex rounded-md shadow-sm">
                            <x-button>Post</x-button>
                        </span>
                    </div>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
