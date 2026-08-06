@php $initial = strtoupper(substr($user->name, 0, 1)); @endphp

<x-subscriber-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ __('Subscriber Dashboard') }}
                </h2>
            </div>
            <div class="flex items-center gap-3">
                {{-- PFP icon - display only, links to My Account page --}}
                <a href="{{ route('subscriber.account') }}"
                   class="relative flex h-10 w-10 cursor-pointer items-center justify-center overflow-hidden rounded-full bg-blue-600 text-base font-semibold text-white shadow-md border-2 border-white"
                   title="My Account">
@if ($user->avatar_path)
                         <img src="{{ asset('storage/'.$user->avatar_path) }}" alt="Avatar" class="absolute inset-0 h-full w-full object-cover">
                     @else
                         <img src="{{ asset('image/icon.png') }}" alt="Default Avatar" class="h-full w-full object-cover text-white">
                     @endif
                 </a>
            </div>
        </div>
    </x-slot>

    <div class="py-2" x-data="{ loading: true }" x-init="setTimeout(() => loading = false, 600)">
        <div class="max-w-7xl mx-auto">
            {{-- Welcome banner with no background --}}
            <div class="mb-8">
                <h1 class="text-4xl font-extrabold tracking-tight text-gray-900 sm:text-5xl">
                    {{ __('Welcome, :name', ['name' => $user->name]) }}
                </h1>
                <p class="mt-3 text-lg text-gray-600">
                    {{ __('You are signed in as') }}
                    <span class="ml-1 inline-flex items-center rounded-full bg-blue-100 px-3 py-1 text-sm font-semibold text-blue-700">
                        subscriber
                    </span>
                </p>
            </div>

            {{-- Quick summary metric card display --}}
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-stretch mb-6">
                {{-- Skeleton loaders --}}
                <template x-if="loading">
                    @include('partials.skeleton-card')
                    @include('partials.skeleton-card')
                    @include('partials.skeleton-card')
                    @include('partials.skeleton-card')
                </template>

                {{-- Actual content --}}
                <template x-if="!loading">
                    <div class="contents">
                        <div class="bg-white p-5 rounded-xl border border-gray-100 shadow-sm h-full flex items-center justify-between min-h-[130px]">
                            <div class="flex flex-col justify-center min-w-0 pr-2">
                                <span class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Current Plan</span>
                                <h4 class="text-sm font-semibold text-gray-900 truncate">{{ $plan?->name ?? '—' }}</h4>
                                <p class="text-[11px] text-gray-500 mt-0.5">{{ $plan?->speed ?? 'No active plan' }}</p>
                            </div>
                            <div class="flex-shrink-0 w-12 h-12 rounded-full flex items-center justify-center bg-green-50 text-green-600">
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.288 15.038a5.25 5.25 0 017.424 0M5.106 11.856c3.807-3.808 9.98-3.808 13.788 0M1.924 8.674c5.565-5.565 14.587-5.565 20.152 0M12.53 18.22l-.53.53-.53-.53a.75.75 0 011.06 0z" />
                                </svg>
                            </div>
                        </div>

                        <div class="bg-white p-5 rounded-xl border border-gray-100 shadow-sm h-full flex items-center justify-between min-h-[130px]">
                            <div class="flex flex-col justify-center min-w-0 pr-2">
                                <span class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Account Status</span>
                                <h4 class="text-sm font-semibold {{ $subscriber?->status === 'active' ? 'text-green-700' : 'text-red-700' }}">
                                    {{ ucfirst($subscriber?->status ?? 'inactive') }}
                                </h4>
                                <p class="text-[11px] text-gray-500 mt-0.5">
                                    {{ $subscriber?->status === 'active' ? 'Service is active' : 'Service is inactive' }}
                                </p>
                            </div>
                            <div class="relative flex-shrink-0 w-12 h-12 rounded-full flex items-center justify-center {{ $subscriber?->status === 'active' ? 'bg-green-50 text-green-600' : 'bg-red-50 text-red-600' }}">
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                                </svg>
                                @if ($subscriber?->status === 'active')
                                    <span class="absolute -right-1 -top-1 flex h-5 w-5 items-center justify-center rounded-full bg-green-500 text-white">
                                        <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                                        </svg>
                                    </span>
                                @else
                                    <span class="absolute -right-1 -top-1 flex h-5 w-5 items-center justify-center rounded-full bg-red-500 text-white">
                                        <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="bg-white p-5 rounded-xl border border-gray-100 shadow-sm h-full flex items-center justify-between min-h-[130px]">
                            <div class="flex flex-col justify-center min-w-0 pr-2">
                                <span class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Outstanding Balance</span>
                                <h4 class="text-sm font-semibold {{ $outstandingBalance > 0 ? 'text-amber-700' : 'text-green-700' }}">
                                    ₱{{ number_format($outstandingBalance, 2) }}
                                </h4>
                                <p class="text-[11px] text-gray-500 mt-0.5">{{ $unpaidCount }} unpaid invoice(s)</p>
                            </div>
                            <div class="flex-shrink-0 w-12 h-12 rounded-full flex items-center justify-center bg-blue-50 text-blue-600">
                                <img src="{{ asset('image/Staff/wallet-svgrepo-com.svg') }}" alt="Outstanding Balance" class="h-6 w-6 object-contain">
                            </div>
                        </div>

                        <div class="bg-white p-5 rounded-xl border border-gray-100 shadow-sm h-full flex items-center justify-between min-h-[130px]">
                            <div class="flex flex-col justify-center min-w-0 pr-2">
                                <span class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Next Billing Date</span>
                                <h4 class="text-sm font-semibold text-gray-900 whitespace-nowrap">
                                    {{ $nextBillingDate?->format('F d, Y') ?? '—' }}
                                </h4>
                                <p class="text-[11px] text-gray-500 mt-0.5">
                                    {{ $daysUntilBilling !== null ? $daysUntilBilling.' days remaining until due' : '—' }}
                                </p>
                            </div>
                            <div class="flex-shrink-0 w-12 h-12 rounded-full flex items-center justify-center bg-orange-50 text-orange-600">
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                                </svg>
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            {{-- Recent support tickets table card --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden mb-6">
                <template x-if="loading">
                    @include('partials.skeleton-table')
                </template>

                <template x-if="!loading">
                    <div>
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h3 class="text-sm font-semibold text-gray-900">Recent Support Tickets</h3>
                        </div>

                        @if ($recentTickets->isEmpty())
                            <div class="p-10 text-center">
                                <p class="text-sm text-gray-500">No support tickets yet.</p>
                            </div>
                        @else
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ticket #</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subject</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reported</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200">
                                        @foreach ($recentTickets as $ticket)
                                        {{-- Show ticket status and summary --}}
                                            <tr class="hover:bg-gray-50">
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $ticket->ticket_number }}</td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $ticket->subject }}</td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ ucfirst(str_replace('_', ' ', $ticket->category)) }}</td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                                    <span @class([
                                                        'inline-flex items-center rounded-md px-2 py-1 text-xs font-medium',
                                                        'bg-blue-50 text-blue-700' => $ticket->status === 'open',
                                                        'bg-amber-50 text-amber-700' => $ticket->status === 'in_progress',
                                                        'bg-green-50 text-green-700' => $ticket->status === 'resolved',
                                                        'bg-gray-100 text-gray-600' => $ticket->status === 'closed',
                                                    ])>
                                                        {{ ucfirst(str_replace('_', ' ', $ticket->status)) }}
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $ticket->created_at->format('M d, Y') }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </template>
            </div>

        </div>
    </div>
</x-subscriber-layout>
