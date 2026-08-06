<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-900">
            {{ __('Admin Dashboard') }}
        </h2>
    </x-slot>

    <div class="bg-white overflow-hidden shadow-sm rounded-xl border border-gray-200 mb-6">
        <div class="p-6 text-gray-900">
            <p class="text-lg font-semibold">{{ __('Welcome, :name', ['name' => $user->name]) }}</p>
            <p class="mt-1 text-sm text-gray-500">
                {{ __('You are signed in as') }}
                <span class="inline-flex items-center rounded-md bg-blue-50 px-2 py-1 text-xs font-medium text-blue-700">admin</span>
            </p>
        </div>
    </div>

    <div x-data="{ loading: true }" x-init="setTimeout(() => loading = false, 500)">
        <div class="flex items-center justify-between mb-3">
            <h3 class="text-sm font-semibold text-gray-900">Billing this month</h3>
            <a href="{{ route('admin.billing.index') }}" class="text-sm font-medium text-blue-600 hover:text-blue-700">View billing &rarr;</a>
        </div>

        <template x-if="loading">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                @include('partials.skeleton-card')
                @include('partials.skeleton-card')
                @include('partials.skeleton-card')
            </div>
        </template>

        <template x-if="!loading">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Billed</p>
                    <p class="mt-1.5 text-2xl font-semibold text-gray-900">₱{{ number_format($summary['billed'], 2) }}</p>
                </div>
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Collected</p>
                    <p class="mt-1.5 text-2xl font-semibold text-green-700">₱{{ number_format($summary['collected'], 2) }}</p>
                </div>
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Outstanding</p>
                    <p class="mt-1.5 text-2xl font-semibold text-amber-700">₱{{ number_format($summary['outstanding'], 2) }}</p>
                </div>
            </div>
        </template>
    </div>
</x-admin-layout>