<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-900">{{ $ticket->ticket_number }}</h2>
                <p class="mt-1 text-sm text-gray-500">{{ $ticket->subscriber->user->name }} ({{ $ticket->subscriber->subscriber_id }})</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.tickets.print', $ticket) }}" target="_blank"
                    class="inline-flex items-center rounded-lg bg-gray-900 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-gray-700 transition">
                    <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0l.229 2.523a1.125 1.125 0 01-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0021 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 00-1.913-.247M6.34 18H5.25A2.25 2.25 0 013 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 011.913-.247m10.5 0a48.536 48.536 0 00-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5z" />
                    </svg>
                    Download PDF
                </a>
                <a href="{{ route('admin.tickets.index') }}" class="text-sm font-medium text-gray-600 hover:text-gray-900">
                    &larr; Back to Tickets
                </a>
            </div>
        </div>
    </x-slot>

    <div class="max-w-3xl space-y-6">
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
            <div class="flex flex-wrap items-center gap-2 mb-4">
                <span @class([
                    'inline-flex items-center rounded-md px-2 py-1 text-xs font-medium',
                    'bg-gray-100 text-gray-600' => $ticket->status === 'open',
                    'bg-blue-50 text-blue-700' => in_array($ticket->status, ['assigned', 'in_progress']),
                    'bg-green-50 text-green-700' => $ticket->status === 'resolved',
                    'bg-gray-800 text-white' => $ticket->status === 'closed',
                ])>
                    {{ ucfirst(str_replace('_', ' ', $ticket->status)) }}
                </span>
                <span @class([
                    'inline-flex items-center rounded-md px-2 py-1 text-xs font-medium',
                    'bg-gray-100 text-gray-600' => $ticket->priority === 'low',
                    'bg-blue-50 text-blue-700' => $ticket->priority === 'medium',
                    'bg-amber-50 text-amber-700' => $ticket->priority === 'high',
                    'bg-red-50 text-red-700' => $ticket->priority === 'urgent',
                ])>
                    {{ ucfirst($ticket->priority) }} priority
                </span>
                <span class="inline-flex items-center rounded-md bg-gray-100 px-2 py-1 text-xs font-medium text-gray-600">
                    {{ ucfirst(str_replace('_', ' ', $ticket->category)) }}
                </span>
            </div>

            <h3 class="text-lg font-semibold text-gray-900">{{ $ticket->subject }}</h3>
            <p class="mt-2 text-sm text-gray-600 whitespace-pre-line">{{ $ticket->description }}</p>

            <dl class="mt-6 grid grid-cols-2 gap-4 text-sm border-t border-gray-200 pt-4">
                <div>
                    <dt class="text-gray-500">Assigned to</dt>
                    <dd class="mt-0.5 font-medium text-gray-900">{{ $ticket->assignee->user->name ?? '— Unclaimed' }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500">Logged by</dt>
                    <dd class="mt-0.5 font-medium text-gray-900">{{ $ticket->creator->name ?? '—' }}</dd>
                </div>
            </dl>

            @if ($ticket->status === 'resolved' || $ticket->status === 'closed')
                <div class="mt-4 rounded-lg bg-green-50 border border-green-200 p-4">
                    <p class="text-xs font-medium text-green-700 uppercase tracking-wider">Resolution notes</p>
                    <p class="mt-1 text-sm text-green-900 whitespace-pre-line">{{ $ticket->resolution_notes }}</p>
                </div>
            @endif

            @if ($ticket->status === 'resolved')
                <form method="POST" action="{{ route('admin.tickets.close', $ticket) }}" class="mt-5">
                    @csrf
                    <button type="submit" class="rounded-lg bg-gray-900 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-gray-700 transition">
                        Close Ticket
                    </button>
                </form>
            @endif
        </div>

        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-sm font-semibold text-gray-900">History</h3>
            </div>
            @if ($activity->isEmpty())
                <div class="p-6 text-sm text-gray-500">No activity recorded yet.</div>
            @else
                <ul class="divide-y divide-gray-200">
                    @foreach ($activity as $entry)
                        <li class="px-6 py-3 text-sm">
                            <p class="text-gray-900">{{ $entry->description }}</p>
                            <p class="mt-0.5 text-xs text-gray-500">{{ $entry->created_at->format('M j, Y g:i A') }}</p>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>
</x-admin-layout>
