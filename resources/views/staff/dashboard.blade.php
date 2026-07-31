<x-staff-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-900">
            {{ __('Technical Staff Dashboard') }}
        </h2>
    </x-slot>

    <div class="bg-white overflow-hidden shadow-sm rounded-xl border border-gray-200 mb-6">
        <div class="p-6 text-gray-900">
            <p class="text-lg font-semibold">{{ __('Welcome, :name', ['name' => $user->name]) }}</p>
            <p class="mt-1 text-sm text-gray-500">
                {{ __('You are signed in as') }}
                <span class="inline-flex items-center rounded-md bg-blue-50 px-2 py-1 text-xs font-medium text-blue-700">technical_staff</span>
            </p>
        </div>
    </div>

    <div>
        <div class="flex items-center justify-between mb-3">
            <h3 class="text-sm font-semibold text-gray-900">Tickets</h3>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <a href="{{ route('staff.tickets.index', ['tab' => 'queue']) }}"
                class="bg-white rounded-xl border border-gray-200 shadow-sm p-5 hover:border-blue-300 transition">
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Open Queue</p>
                <p class="mt-1.5 text-2xl font-semibold text-gray-900">{{ $openQueueCount }}</p>
                <p class="mt-1 text-xs text-gray-500">Unclaimed tickets waiting to be picked up</p>
            </a>
            <a href="{{ route('staff.tickets.index', ['tab' => 'mine']) }}"
                class="bg-white rounded-xl border border-gray-200 shadow-sm p-5 hover:border-blue-300 transition">
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">My Tickets</p>
                <p class="mt-1.5 text-2xl font-semibold text-gray-900">{{ $myTicketsCount }}</p>
                <p class="mt-1 text-xs text-gray-500">Assigned to you, not yet resolved</p>
            </a>
        </div>
    </div>
</x-staff-layout>
