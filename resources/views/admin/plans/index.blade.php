<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-900">Plans</h2>
                <p class="mt-1 text-sm text-gray-500">Manage the internet plans subscribers can be assigned to.</p>
            </div>
            <a href="{{ route('admin.plans.create') }}"
               class="inline-flex items-center rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-blue-700 transition">
                + Add Plan
            </a>
        </div>
    </x-slot>

    @if (session('status') === 'plan-created')
        <div class="mb-6 rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-700">
            Plan created successfully.
        </div>
    @elseif (session('status') === 'plan-deleted')
        <div class="mb-6 rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-700">
            Plan deleted.
        </div>
    @elseif (session('status') === 'plan-has-subscribers')
        <div class="mb-6 rounded-lg bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-700">
            This plan can't be deleted because it still has subscribers on it.
        </div>
    @endif

    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        @if ($plans->isEmpty())
            <div class="p-10 text-center">
                <p class="text-sm text-gray-500">No plans yet.</p>
                <a href="{{ route('admin.plans.create') }}" class="mt-2 inline-block text-sm font-medium text-blue-600 hover:text-blue-700">
                    Add the first one &rarr;
                </a>
            </div>
        @else
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Speed</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Billing Cycle</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Active Subscribers</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach ($plans as $plan)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $plan->name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $plan->speed }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">₱{{ number_format($plan->price, 2) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ ucfirst($plan->billing_cycle) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <span @class([
                                    'inline-flex items-center rounded-md px-2 py-1 text-xs font-medium',
                                    'bg-green-50 text-green-700' => $plan->is_active,
                                    'bg-gray-100 text-gray-600' => ! $plan->is_active,
                                ])>
                                    {{ $plan->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $plan->active_subscribers_count }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                                <form method="POST" action="{{ route('admin.plans.destroy', $plan) }}"
                                    onsubmit="return confirm('Delete the {{ $plan->name }} plan? This cannot be undone.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-700 font-medium">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</x-admin-layout>
