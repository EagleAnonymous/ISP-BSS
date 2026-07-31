<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-900">Subscribers</h2>
                <p class="mt-1 text-sm text-gray-500">Manage subscriber accounts and their login credentials.</p>
            </div>
            <a href="{{ route('admin.subscribers.create') }}"
               class="inline-flex items-center rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-blue-700 transition">
                + Add Subscriber
            </a>
        </div>
    </x-slot>

    @if (session('status') === 'subscriber-created')
        <div class="mb-6 rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-700">
            Subscriber account created successfully.
        </div>
    @endif

    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        @if ($subscribers->isEmpty())
            <div class="p-10 text-center">
                <p class="text-sm text-gray-500">No subscriber accounts yet.</p>
                <a href="{{ route('admin.subscribers.create') }}" class="mt-2 inline-block text-sm font-medium text-blue-600 hover:text-blue-700">
                    Add the first one &rarr;
                </a>
            </div>
        @else
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subscriber ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contact</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Plan</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Joined Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach ($subscribers as $subscriber)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $subscriber->subscriber_id }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $subscriber->user->name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $subscriber->contact ?? '—' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $subscriber->plan->name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <span @class([
                                    'inline-flex items-center rounded-md px-2 py-1 text-xs font-medium',
                                    'bg-green-50 text-green-700' => $subscriber->status === 'active',
                                    'bg-gray-100 text-gray-600' => $subscriber->status === 'inactive',
                                    'bg-red-50 text-red-700' => $subscriber->status === 'suspended',
                                ])>
                                    {{ ucfirst($subscriber->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $subscriber->joined_date->format('M j, Y') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</x-admin-layout>
