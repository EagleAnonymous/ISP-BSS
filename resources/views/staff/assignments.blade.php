<x-staff-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-900">My Assigned Tasks</h2>
                <p class="mt-1 text-sm text-gray-500">Tasks assigned to you by the admin.</p>
            </div>
        </div>
    </x-slot>

    {{-- Assignments Table --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        @if ($assignments->isEmpty())
            <div class="p-10 text-center">
                <div class="flex h-14 w-14 items-center justify-center rounded-full bg-gray-100 text-gray-400 mx-auto">
                    <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25z" />
                    </svg>
                </div>
                <h3 class="mt-4 text-sm font-semibold text-gray-900">No assigned tasks</h3>
                <p class="mt-1 text-sm text-gray-500">You don't have any assigned tasks yet.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ticket #</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subject</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Priority</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Assigned At</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach ($assignments as $assignment)
                            @php
                                $ticket = $assignment->ticket;
                            @endphp
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    <a href="{{ route('staff.tickets.show', $ticket) }}" class="hover:text-blue-700">{{ $ticket->ticket_number }}</a>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600">{{ $ticket->subject }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                    {{ ucfirst(str_replace('_', ' ', $ticket->category ?? '—')) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    @php
                                        $priorityStyles = match($ticket->priority ?? '') {
                                            'urgent' => 'bg-red-50 text-red-700',
                                            'high' => 'bg-orange-50 text-orange-700',
                                            'medium' => 'bg-amber-50 text-amber-700',
                                            'low' => 'bg-green-50 text-green-700',
                                            default => 'bg-gray-100 text-gray-600',
                                        };
                                    @endphp
                                    <span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium {{ $priorityStyles }}">
                                        {{ ucfirst($ticket->priority ?? '—') }}
                                    </span>
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
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $assignment->assigned_at?->format('M d, Y g:i A') ?? '—' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <a href="{{ route('staff.tickets.show', $ticket) }}" class="text-blue-600 hover:text-blue-700">View</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    {{-- Pagination --}}
    @if ($assignments->hasPages())
        <div class="mt-4">
            {{ $assignments->links() }}
        </div>
    @endif
</x-staff-layout>
