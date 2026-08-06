<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-900">Ticket Assignments</h2>
                <p class="mt-1 text-sm text-gray-500">Manage and track all ticket assignments across staff.</p>
            </div>
            <a href="{{ route('admin.assignments.create') }}"
               class="inline-flex items-center rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-blue-700 transition">
                + Assign Ticket
            </a>
        </div>
    </x-slot>

    @if (session('status') === 'assignment-created')
        <div class="mb-6 rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-700">
            Ticket assigned successfully.
        </div>
    @elseif (session('status') === 'assignment-completed')
        <div class="mb-6 rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-700">
            Assignment marked as completed.
        </div>
    @elseif (session('status') === 'assignment-cancelled')
        <div class="mb-6 rounded-lg bg-amber-50 border border-amber-200 px-4 py-3 text-sm text-amber-700">
            Assignment cancelled. Ticket returned to open queue.
        </div>
    @endif

    {{-- Filters --}}
    <form method="GET" action="{{ route('admin.assignments.index') }}" class="mb-6 flex flex-wrap items-end gap-3">
        <div>
            <label for="staff_id" class="block text-xs font-medium text-gray-600 mb-1">Staff Member</label>
            <select id="staff_id" name="staff_id"
                class="rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-600 focus:outline-none focus:ring-1 focus:ring-blue-600">
                <option value="">All staff</option>
                @foreach ($staff as $s)
                    <option value="{{ $s->id }}" {{ ($filters['staff_id'] ?? '') == $s->id ? 'selected' : '' }}>
                        {{ $s->user->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="status" class="block text-xs font-medium text-gray-600 mb-1">Ticket Status</label>
            <select id="status" name="status"
                class="rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-600 focus:outline-none focus:ring-1 focus:ring-blue-600">
                <option value="">All statuses</option>
                @foreach (['open' => 'Open', 'assigned' => 'Assigned', 'in_progress' => 'In Progress', 'resolved' => 'Resolved', 'closed' => 'Closed'] as $value => $label)
                    <option value="{{ $value }}" {{ ($filters['status'] ?? '') === $value ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <div class="flex-1 min-w-[200px]">
            <label for="search" class="block text-xs font-medium text-gray-600 mb-1">Search</label>
            <input id="search" type="text" name="search" value="{{ $filters['search'] ?? '' }}"
                placeholder="Ticket #, subject, or subscriber name"
                class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-900 placeholder:text-gray-400 shadow-sm focus:border-blue-600 focus:outline-none focus:ring-1 focus:ring-blue-600">
        </div>

        <button type="submit" class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-gray-700 transition">
            Filter
        </button>
    </form>

    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        @if ($assignments->isEmpty())
            <div class="p-10 text-center">
                <p class="text-sm text-gray-500">No assignments match these filters.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ticket #</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subject</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subscriber</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Assigned To</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Assigned By</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Assigned At</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach ($assignments as $ticket)
                            @php
                                $activeAssignment = $ticket->assignments()->where('status', 'active')->first();
                            @endphp
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    <a href="{{ route('admin.tickets.show', $ticket) }}" class="hover:text-blue-700">{{ $ticket->ticket_number }}</a>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600">{{ $ticket->subject }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                    {{ $ticket->subscriber->user->name ?? '—' }}
                                    <span class="block text-xs text-gray-400">{{ $ticket->subscriber->subscriber_id ?? '' }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                    {{ $activeAssignment?->staff?->user?->name ?? '—' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                    {{ $activeAssignment?->assigner?->name ?? '—' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $activeAssignment?->assigned_at?->format('M d, Y g:i A') ?? '—' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    @php
                                        $statusStyles = match($ticket->status ?? '') {
                                            'open' => 'bg-gray-100 text-gray-600',
                                            'assigned' => 'bg-blue-50 text-blue-700',
                                            'in_progress' => 'bg-amber-50 text-amber-700',
                                            'resolved' => 'bg-green-50 text-green-700',
                                            'closed' => 'bg-gray-800 text-white',
                                            default => 'bg-gray-100 text-gray-600',
                                        };
                                    @endphp
                                    <span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium {{ $statusStyles }}">
                                        {{ ucfirst(str_replace('_', ' ', $ticket->status ?? '—')) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex items-center justify-end gap-2">
                                        @if ($activeAssignment && $activeAssignment->status === 'active')
                                            <form method="POST" action="{{ route('admin.assignments.complete', $activeAssignment) }}" class="inline">
                                                @csrf
                                                @method('POST')
                                                <button type="submit" class="text-green-600 hover:text-green-700">Complete</button>
                                            </form>
                                            <form method="POST" action="{{ route('admin.assignments.destroy', $activeAssignment) }}" class="inline" onsubmit="return confirm('Cancel this assignment? The ticket will return to the open queue.')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-700">Cancel</button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <div class="mt-4">
        {{ $assignments->links() }}
    </div>
</x-admin-layout>
