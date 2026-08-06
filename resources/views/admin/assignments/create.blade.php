<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-900">Assign Ticket</h2>
                <p class="mt-1 text-sm text-gray-500">Assign an open or unassigned ticket to a technical staff member.</p>
            </div>
        </div>
    </x-slot>

    @if (session('status') === 'ticket-created')
        <div class="mb-6 rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-700">
            Ticket logged successfully.
        </div>
    @endif

    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="p-6">
            <form method="POST" action="{{ route('admin.assignments.store') }}">
                @csrf

                <div class="grid grid-cols-1 gap-6">
                    <div>
                        <label for="ticket_id" class="block text-sm font-medium text-gray-700 mb-1">Ticket</label>
                        <select id="ticket_id" name="ticket_id" required
                            class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-600 focus:outline-none focus:ring-1 focus:ring-blue-600">
                            <option value="">Select a ticket...</option>
                            @foreach ($unassignedTickets as $t)
                                <option value="{{ $t->id }}" {{ old('ticket_id', $ticket?->id) == $t->id ? 'selected' : '' }}>
                                    {{ $t->ticket_number }} — {{ $t->subject }} ({{ ucfirst($t->status) }})
                                </option>
                            @endforeach
                        </select>
                        @error('ticket_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="staff_id" class="block text-sm font-medium text-gray-700 mb-1">Assign To</label>
                        <select id="staff_id" name="staff_id" required
                            class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-600 focus:outline-none focus:ring-1 focus:ring-blue-600">
                            <option value="">Select staff member...</option>
                            @foreach ($staff as $s)
                                <option value="{{ $s->id }}" {{ old('staff_id') == $s->id ? 'selected' : '' }}>
                                    {{ $s->user->name }} — {{ $s->position ?? 'Staff' }}
                                </option>
                            @endforeach
                        </select>
                        @error('staff_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">Assignment Notes <span class="text-gray-400">(optional)</span></label>
                        <textarea id="notes" name="notes" rows="4"
                            class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-600 focus:outline-none focus:ring-1 focus:ring-blue-600"
                            placeholder="Add any instructions or context for the staff member...">{{ old('notes') }}</textarea>
                        @error('notes')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="mt-6 flex items-center justify-end gap-3">
                    <a href="{{ route('admin.assignments.index') }}"
                       class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 transition">
                        Cancel
                    </a>
                    <button type="submit" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-700 transition">
                        Assign Ticket
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-admin-layout>
