<x-staff-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-900">Tickets</h2>
                <p class="mt-1 text-sm text-gray-500">Claim a problem from the queue, or track ones you're already working.</p>
            </div>
            <a href="{{ route('staff.tickets.create') }}"
               class="inline-flex items-center rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-blue-700 transition">
                + Log Ticket
            </a>
        </div>
    </x-slot>

    @if (session('status') === 'ticket-created')
        <div class="mb-6 rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-700">
            Ticket logged successfully.
        </div>
    @elseif (session('status') === 'ticket-claimed')
        <div class="mb-6 rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-700">
            Ticket claimed — it's now yours to work.
        </div>
    @elseif (session('status') === 'ticket-started')
        <div class="mb-6 rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-700">
            Marked as in progress.
        </div>
    @elseif (session('status') === 'ticket-resolved')
        <div class="mb-6 rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-700">
            Marked resolved. An admin will give the final close.
        </div>
    @endif

    {{-- Tabs --}}
    <div class="mb-6 border-b border-gray-200">
        <nav class="flex gap-6 -mb-px">
            <a href="{{ route('staff.tickets.index', ['tab' => 'queue']) }}"
               class="border-b-2 px-1 py-3 text-sm font-medium {{ $tab === 'queue' ? 'border-blue-600 text-blue-700' : 'border-transparent text-gray-500 hover:text-gray-700' }}">
                Open Queue
            </a>
            <a href="{{ route('staff.tickets.index', ['tab' => 'mine']) }}"
               class="border-b-2 px-1 py-3 text-sm font-medium {{ $tab === 'mine' ? 'border-blue-600 text-blue-700' : 'border-transparent text-gray-500 hover:text-gray-700' }}">
                My Tickets
            </a>
        </nav>
    </div>

    @if ($tab === 'mine' && request('status'))
        <div class="mb-4">
            <a href="{{ route('staff.tickets.index', ['tab' => 'mine']) }}" class="text-sm text-blue-600 hover:text-blue-700">← Back to all my tickets</a>
        </div>
    @endif

<div x-data="{ loading: true }" x-init="setTimeout(() => loading = false, 600)">
        <template x-if="loading">
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                @include('partials.skeleton-table')
            </div>
        </template>

        <template x-if="!loading">
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        @if ($tickets->isEmpty())
            <div class="p-10 text-center">
                <p class="text-sm text-gray-500">
                    {{ $tab === 'queue' ? 'No open tickets right now.' : (request('status') ? 'No tickets with this status.' : "You haven't claimed any tickets yet.") }}
                </p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ticket #</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subscriber</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subject</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Priority</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach ($tickets as $ticket)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    <a href="{{ route('staff.tickets.show', $ticket) }}" class="hover:text-blue-700">{{ $ticket->ticket_number }}</a>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                    {{ $ticket->subscriber->user->name }}
                                    <span class="block text-xs text-gray-400">{{ $ticket->subscriber->subscriber_id }}</span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600">{{ $ticket->subject }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <span @class([
                                        'inline-flex items-center rounded-md px-2 py-1 text-xs font-medium',
                                        'bg-gray-100 text-gray-600' => $ticket->priority === 'low',
                                        'bg-blue-50 text-blue-700' => $ticket->priority === 'medium',
                                        'bg-amber-50 text-amber-700' => $ticket->priority === 'high',
                                        'bg-red-50 text-red-700' => $ticket->priority === 'urgent',
                                    ])>
                                        {{ ucfirst($ticket->priority) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <span @class([
                                        'inline-flex items-center rounded-md px-2 py-1 text-xs font-medium',
                                        'bg-gray-100 text-gray-600' => $ticket->status === 'open',
                                        'bg-blue-50 text-blue-700' => in_array($ticket->status, ['assigned', 'in_progress']),
                                        'bg-green-50 text-green-700' => $ticket->status === 'resolved',
                                        'bg-gray-800 text-white' => $ticket->status === 'closed',
                                    ])>
                                        {{ ucfirst(str_replace('_', ' ', $ticket->status)) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                                    @if ($tab === 'queue')
                                        <form method="POST" action="{{ route('staff.tickets.claim', $ticket) }}" 
                                              x-data="{ claiming: false }"
                                              @submit.prevent="claiming = true; $event.target.submit();"
                                              class="inline">
                                            @csrf
                                            <button type="submit" x-bind:disabled="claiming" 
                                                    class="text-blue-600 hover:text-blue-700 font-medium disabled:opacity-50 disabled:cursor-not-allowed">
                                                <span x-show="!claiming">Claim</span>
                                                <span x-show="claiming">Claiming...</span>
                                            </button>
                                        </form>
                                    @else
                                        <a href="{{ route('staff.tickets.show', $ticket) }}" class="text-blue-600 hover:text-blue-700 font-medium">View</a>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
@endif
        </div>
        </template>
    </div>

    <div class="mt-4">
        {{ $tickets->links() }}
    </div>
</x-staff-layout>
