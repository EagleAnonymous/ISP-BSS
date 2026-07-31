<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Subscriber Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm rounded-xl border border-gray-200">
                <div class="p-6 text-gray-900">
                    <p class="text-lg font-semibold">{{ __('Welcome, :name', ['name' => $user->name]) }}</p>
                    <p class="mt-1 text-sm text-gray-500">
                        {{ __('You are signed in as') }}
                        <span class="inline-flex items-center rounded-md bg-blue-50 px-2 py-1 text-xs font-medium text-blue-700">subscriber</span>
                    </p>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
