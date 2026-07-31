<x-staff-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-900">Log a Ticket</h2>
        <p class="mt-1 text-sm text-gray-500">Record a subscriber's problem you've spotted, so it's tracked in the queue.</p>
    </x-slot>

    <div class="max-w-2xl bg-white rounded-xl border border-gray-200 shadow-sm p-6 sm:p-8">
        <form method="POST" action="{{ route('staff.tickets.store') }}" class="space-y-5">
            @csrf

            <div>
                <label for="subscriber_id" class="block text-sm font-medium text-gray-700 mb-1.5">Subscriber</label>
                <select id="subscriber_id" name="subscriber_id" required
                    class="block w-full rounded-lg border border-gray-300 px-3.5 py-2.5 text-sm text-gray-900 shadow-sm transition focus:border-blue-600 focus:outline-none focus:ring-1 focus:ring-blue-600">
                    <option value="" disabled {{ old('subscriber_id') ? '' : 'selected' }}>Select a subscriber</option>
                    @foreach ($subscribers as $subscriber)
                        <option value="{{ $subscriber->id }}" {{ (int) old('subscriber_id') === $subscriber->id ? 'selected' : '' }}>
                            {{ $subscriber->user->name }} &middot; {{ $subscriber->subscriber_id }}
                        </option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('subscriber_id')" class="mt-1.5" />
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label for="category" class="block text-sm font-medium text-gray-700 mb-1.5">Category</label>
                    <select id="category" name="category" required
                        class="block w-full rounded-lg border border-gray-300 px-3.5 py-2.5 text-sm text-gray-900 shadow-sm transition focus:border-blue-600 focus:outline-none focus:ring-1 focus:ring-blue-600">
                        @foreach ([
                            'no_connection' => 'No Connection',
                            'slow_connection' => 'Slow Connection',
                            'billing_concern' => 'Billing Concern',
                            'installation_request' => 'Installation Request',
                            'equipment_issue' => 'Equipment Issue',
                            'other' => 'Other',
                        ] as $value => $label)
                            <option value="{{ $value }}" {{ old('category') === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('category')" class="mt-1.5" />
                </div>

                <div>
                    <label for="priority" class="block text-sm font-medium text-gray-700 mb-1.5">Priority</label>
                    <select id="priority" name="priority" required
                        class="block w-full rounded-lg border border-gray-300 px-3.5 py-2.5 text-sm text-gray-900 shadow-sm transition focus:border-blue-600 focus:outline-none focus:ring-1 focus:ring-blue-600">
                        @foreach (['low' => 'Low', 'medium' => 'Medium', 'high' => 'High', 'urgent' => 'Urgent'] as $value => $label)
                            <option value="{{ $value }}" {{ old('priority', 'medium') === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('priority')" class="mt-1.5" />
                </div>
            </div>

            <div>
                <label for="subject" class="block text-sm font-medium text-gray-700 mb-1.5">Subject</label>
                <input id="subject" type="text" name="subject" value="{{ old('subject') }}" required
                    placeholder="Short summary of the problem"
                    class="block w-full rounded-lg border border-gray-300 px-3.5 py-2.5 text-sm text-gray-900 placeholder:text-gray-400 shadow-sm transition focus:border-blue-600 focus:outline-none focus:ring-1 focus:ring-blue-600">
                <x-input-error :messages="$errors->get('subject')" class="mt-1.5" />
            </div>

            <div>
                <label for="description" class="block text-sm font-medium text-gray-700 mb-1.5">Description</label>
                <textarea id="description" name="description" rows="4" required
                    placeholder="What you observed, any troubleshooting already tried, etc."
                    class="block w-full rounded-lg border border-gray-300 px-3.5 py-2.5 text-sm text-gray-900 placeholder:text-gray-400 shadow-sm transition focus:border-blue-600 focus:outline-none focus:ring-1 focus:ring-blue-600">{{ old('description') }}</textarea>
                <x-input-error :messages="$errors->get('description')" class="mt-1.5" />
            </div>

            <div class="flex items-center gap-3 pt-2">
                <button type="submit"
                    class="rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-blue-700 transition">
                    Log Ticket
                </button>
                <a href="{{ route('staff.tickets.index') }}" class="text-sm font-medium text-gray-600 hover:text-gray-900">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</x-staff-layout>
