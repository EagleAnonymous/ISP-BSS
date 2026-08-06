@php
    // Safe fallback for the welcome banner when the user isn't fully loaded.
    $staffName = explode(' ', $user->name ?? 'Pablo')[0] ?? 'Pablo';
@endphp

<x-staff-layout>
<div class="py-2" x-data="{ loading: true }" x-init="setTimeout(() => loading = false, 600)">
         <div class="max-w-7xl mx-auto">
             {{-- ================= Welcome Banner ================= --}}
             <div class="mb-8">
                 <h1 class="text-4xl font-extrabold tracking-tight text-gray-900 sm:text-5xl">
                     {{ __('Welcome, :name!', ['name' => $staffName]) }}
                 </h1>
                 <p class="mt-3 text-lg text-gray-600">
                     {{ __("Here's an overview of your support operations") }}
                 </p>
             </div>

             {{-- ================= Top Metrics Overview (4 cards) ================= --}}
            <template x-if="loading">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
                    @include('partials.skeleton-card')
                    @include('partials.skeleton-card')
                    @include('partials.skeleton-card')
                    @include('partials.skeleton-card')
                </div>
            </template>

<template x-if="!loading">
                 <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
                {{-- Open Tickets --}}
                @if(Route::has('staff.tickets.index'))
                <a href="{{ route('staff.tickets.index', ['tab' => 'queue']) }}" class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 hover:border-blue-300 hover:shadow-md transition cursor-pointer">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Open Tickets</p>
                            <p class="mt-2 text-3xl font-semibold text-gray-900">{{ $openCount ?? 0 }}</p>
                        </div>
                        <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-full bg-blue-50 text-blue-600">
                            <img src="{{ asset('image/Staff/ticket-svgrepo-com.svg') }}" alt="Open Tickets" class="h-8 w-8">
                        </div>
                    </div>
                </a>
                @endif

                {{-- In Progress --}}
                @if(Route::has('staff.tickets.index'))
                <a href="{{ route('staff.tickets.index', ['tab' => 'mine', 'status' => 'in_progress']) }}" class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 hover:border-green-300 hover:shadow-md transition cursor-pointer block">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">In Progress</p>
                            <p class="mt-2 text-3xl font-semibold text-gray-900">{{ $inProgressCount ?? 0 }}</p>
                        </div>
                        <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-full bg-green-50 text-green-600">
                            <img src="{{ asset('image/Staff/progress-svgrepo-com.svg') }}" alt="In Progress" class="h-8 w-8">
                        </div>
                    </div>
                </a>
                @endif

                {{-- Pending --}}
                @if(Route::has('staff.tickets.index'))
                <a href="{{ route('staff.tickets.index', ['tab' => 'mine', 'status' => 'assigned']) }}" class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 hover:border-orange-300 hover:shadow-md transition cursor-pointer block">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Pending</p>
                            <p class="mt-2 text-3xl font-semibold text-gray-900">{{ $pendingCount ?? 0 }}</p>
                        </div>
                        <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-full bg-orange-50 text-orange-600">
                            {{-- Hourglass / pending icon (inline SVG) --}}
                            <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                </a>
                @endif

                {{-- Resolved Today --}}
                @if(Route::has('staff.tickets.index'))
                <a href="{{ route('staff.tickets.index', ['tab' => 'mine', 'status' => 'resolved']) }}" class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 hover:border-purple-300 hover:shadow-md transition cursor-pointer block">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Resolved Today</p>
                            <p class="mt-2 text-3xl font-semibold text-gray-900">{{ $resolvedTodayCount ?? 0 }}</p>
                        </div>
                        <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-full bg-purple-50 text-purple-600">
                            <img src="{{ asset('image/Staff/check-circle-svgrepo-com.svg') }}" alt="Resolved Today" class="h-8 w-8">
                        </div>
                    </div>
</a>
                @endif
                </div>
            </template>
{{-- ================= Two-Column Main Content Grid ================= --}}
<template x-if="loading">
                <div>
                    @include('partials.skeleton-table')
                </div>
            </template>

            <template x-if="!loading">
            <div>

                {{-- Recent Support Tickets table --}}
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                        <h3 class="text-sm font-semibold text-gray-900">Recent Support Tickets</h3>
                        @if(Route::has('staff.tickets.index'))
                        <a href="{{ route('staff.tickets.index', ['tab' => 'queue']) }}" class="text-sm font-medium text-blue-600 hover:text-blue-700">View All</a>
                        @endif
                    </div>

                    {{-- overflow-x-auto prevents horizontal scrollbar on small screens --}}
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ticket ID</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Issue</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Priority</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Updated</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @forelse ($recentTickets ?? [] as $ticket)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $ticket->ticket_number ?? '—' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $ticket->subscriber && $ticket->subscriber->user ? $ticket->subscriber->user->name : '—' }}</td>
                                        <td class="px-6 py-4 text-sm text-gray-600 max-w-xs truncate">{{ $ticket->subject ?? '—' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @php
                                                $statusStyles = match($ticket->status ?? '') {
                                                    'open' => 'bg-blue-50 text-blue-700',
                                                    'assigned' => 'bg-yellow-50 text-yellow-700',
                                                    'in_progress' => 'bg-green-50 text-green-700',
                                                    'resolved' => 'bg-purple-50 text-purple-700',
                                                    'closed' => 'bg-gray-100 text-gray-600',
                                                    default => 'bg-gray-100 text-gray-600',
                                                };
                                            @endphp
                                            <span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium {{ $statusStyles }}">
                                                {{ ucfirst(str_replace('_', ' ', $ticket->status ?? '—')) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @php
                                                $priorityStyles = match($ticket->priority ?? '') {
                                                    'urgent' => 'bg-red-50 text-red-700',
                                                    'high' => 'bg-orange-50 text-orange-700',
                                                    'medium' => 'bg-yellow-50 text-yellow-700',
                                                    'low' => 'bg-green-50 text-green-700',
                                                    default => 'bg-gray-100 text-gray-600',
                                                };
                                            @endphp
                                            <span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium {{ $priorityStyles }}">
                                                {{ ucfirst($ticket->priority ?? '—') }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $ticket->updated_at ? $ticket->updated_at->diffForHumans() : '—' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <div class="relative inline-block text-left">
                                                <button type="button" class="text-gray-400 hover:text-gray-600" onclick="document.getElementById('actions-{{ $ticket->id }}').classList.toggle('hidden')">
                                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.75a.75.75 0 110-1.5.75.75 0 010 1.5zM12 12.75a.75.75 0 110-1.5.75.75 0 010 1.5zM12 18.75a.75.75 0 110-1.5.75.75 0 010 1.5z" />
                                                    </svg>
                                                </button>
                                                <div id="actions-{{ $ticket->id }}" class="hidden absolute right-0 z-10 mt-2 w-40 origin-top-right rounded-md bg-white shadow-lg ring-1 ring-black ring-opacity-5">
                                                    <div class="py-1">
                                                        @if(Route::has('staff.tickets.show'))
                                                        <a href="{{ route('staff.tickets.show', $ticket) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">View</a>
                                                        @endif
                                                        @if(Route::has('staff.tickets.print'))
                                                        <a href="{{ route('staff.tickets.print', $ticket) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Print</a>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="px-6 py-12 text-center">
                                            <div class="flex flex-col items-center justify-center gap-2">
                                                <svg class="h-10 w-10 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 6v.75m0 3v.75m0 3v.75m0 3V18m-9-5.25h5.25M7.5 15h3M3.375 5.25c-.621 0-1.125.504-1.125 1.125v3.026a2.999 2.999 0 010 5.198v3.026c0 .621.504 1.125 1.125 1.125h17.25c.621 0 1.125-.504 1.125-1.125v-3.026a2.999 2.999 0 010-5.198V6.375c0-.621-.504-1.125-1.125-1.125H3.375z" />
                                                </svg>
                                                <p class="text-sm font-medium text-gray-500">No recent support tickets found</p>
                                                <p class="text-xs text-gray-400">Tickets assigned to you will appear here.</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
</div>
            </div>
            </template>
        </div>
    </div>
</x-staff-layout>
