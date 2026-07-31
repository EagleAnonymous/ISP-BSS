@php
    $isMine = $ticket->assigned_to === auth()->user()->technicalStaff->id;
@endphp

<x-staff-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-900">{{ $ticket->ticket_number }}</h2>
                <p class="mt-1 text-sm text-gray-500">{{ $ticket->subscriber->user->name }} ({{ $ticket->subscriber->subscriber_id }})</p>
            </div>
            <a href="{{ route('staff.tickets.index') }}" class="text-sm font-medium text-gray-600 hover:text-gray-900">
                &larr; Back to Tickets
            </a>
        </div>
    </x-slot>

    <div class="max-w-3xl space-y-6">
        @if (session('status') === 'ticket-claimed')
            <div class="rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-700">
                Ticket claimed — it's now yours to work.
            </div>
        @elseif (session('status') === 'ticket-started')
            <div class="rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-700">
                Marked as in progress.
            </div>
        @elseif (session('status') === 'ticket-resolved')
            <div class="rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-700">
                Marked resolved. An admin will give the final close.
            </div>
        @endif

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

            {{-- Actions --}}
            <div class="mt-5 border-t border-gray-200 pt-5">
                @if ($ticket->status === 'open')
                    <form method="POST" action="{{ route('staff.tickets.claim', $ticket) }}">
                        @csrf
                        <button type="submit" class="rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-blue-700 transition">
                            Claim This Ticket
                        </button>
                    </form>
                @elseif ($ticket->status === 'assigned' && $isMine)
                    <form method="POST" action="{{ route('staff.tickets.start', $ticket) }}">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-blue-700 transition">
                            Start Working
                        </button>
                    </form>
                @elseif ($ticket->status === 'in_progress' && $isMine)
                    <form method="POST" action="{{ route('staff.tickets.resolve', $ticket) }}" class="max-w-lg">
                        @csrf
                        @method('PATCH')
                        <label for="resolution_notes" class="block text-sm font-medium text-gray-700 mb-1.5">Resolution notes</label>
                        <textarea id="resolution_notes" name="resolution_notes" rows="3" required
                            placeholder="What was done to fix this?"
                            class="block w-full rounded-lg border border-gray-300 px-3.5 py-2.5 text-sm text-gray-900 placeholder:text-gray-400 shadow-sm transition focus:border-blue-600 focus:outline-none focus:ring-1 focus:ring-blue-600">{{ old('resolution_notes') }}</textarea>
                        <x-input-error :messages="$errors->get('resolution_notes')" class="mt-1.5" />
                        <button type="submit" class="mt-3 rounded-lg bg-green-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-green-700 transition">
                            Mark Resolved
                        </button>
                    </form>
                @elseif ($ticket->status === 'resolved' || $ticket->status === 'closed')
                    <div class="rounded-lg bg-green-50 border border-green-200 p-4">
                        <p class="text-xs font-medium text-green-700 uppercase tracking-wider">Resolution notes</p>
                        <p class="mt-1 text-sm text-green-900 whitespace-pre-line">{{ $ticket->resolution_notes }}</p>
                    </div>
                @else
                    <p class="text-sm text-gray-500">This ticket is claimed by another technical staff member.</p>
                @endif
            </div>
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
</x-staff-layout>
