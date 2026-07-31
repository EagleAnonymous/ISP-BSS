<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-900">Tickets</h2>
                <p class="mt-1 text-sm text-gray-500">System-wide view of every subscriber problem reported.</p>
            </div>
            <a href="{{ route('admin.tickets.create') }}"
               class="inline-flex items-center rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-blue-700 transition">
                + Log Ticket
            </a>
        </div>
    </x-slot>

    @if (session('status') === 'ticket-created')
        <div class="mb-6 rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-700">
            Ticket logged successfully.
        </div>
    @elseif (session('status') === 'ticket-closed')
        <div class="mb-6 rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-700">
            Ticket closed.
        </div>
    @endif

    {{-- Filters --}}
    <form method="GET" action="{{ route('admin.tickets.index') }}" class="mb-6 flex flex-wrap items-end gap-3">
        <div>
            <label for="status" class="block text-xs font-medium text-gray-600 mb-1">Status</label>
            <select id="status" name="status"
                class="rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-600 focus:outline-none focus:ring-1 focus:ring-blue-600">
                <option value="">All statuses</option>
                @foreach (['open' => 'Open', 'assigned' => 'Assigned', 'in_progress' => 'In Progress', 'resolved' => 'Resolved', 'closed' => 'Closed'] as $value => $label)
                    <option value="{{ $value }}" {{ ($filters['status'] ?? '') === $value ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="priority" class="block text-xs font-medium text-gray-600 mb-1">Priority</label>
            <select id="priority" name="priority"
                class="rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-600 focus:outline-none focus:ring-1 focus:ring-blue-600">
                <option value="">All priorities</option>
                @foreach (['low' => 'Low', 'medium' => 'Medium', 'high' => 'High', 'urgent' => 'Urgent'] as $value => $label)
                    <option value="{{ $value }}" {{ ($filters['priority'] ?? '') === $value ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <div class="flex-1 min-w-[200px]">
            <label for="search" class="block text-xs font-medium text-gray-600 mb-1">Search</label>
            <input id="search" type="text" name="search" value="{{ $filters['search'] ?? '' }}"
                placeholder="Ticket #, subscriber name or account number"
                class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-900 placeholder:text-gray-400 shadow-sm focus:border-blue-600 focus:outline-none focus:ring-1 focus:ring-blue-600">
        </div>

        <button type="submit" class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-gray-700 transition">
            Filter
        </button>
    </form>

    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        @if ($tickets->isEmpty())
            <div class="p-10 text-center">
                <p class="text-sm text-gray-500">No tickets match these filters.</p>
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
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Assigned To</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Logged</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach ($tickets as $ticket)
                            <tr class="hover:bg-gray-50 cursor-pointer" onclick="window.location='{{ route('admin.tickets.show', $ticket) }}'">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $ticket->ticket_number }}</td>
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
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                    {{ $ticket->assignee->user->name ?? '— Unclaimed' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $ticket->created_at->format('M j, Y') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <div class="mt-4">
        {{ $tickets->links() }}
    </div>
</x-admin-layout>
