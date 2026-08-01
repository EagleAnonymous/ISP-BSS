<x-subscriber-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('My Account') }}
        </h2>
    </x-slot>

    <div class="py-2">
        <div class="max-w-7xl mx-auto">
            <div class="bg-white overflow-hidden shadow-sm rounded-xl border border-gray-200">
                <div class="px-6 py-5 border-b border-gray-200 flex items-center justify-between">
                    <h3 class="text-sm font-semibold text-gray-900">Account Information</h3>
                    <a href="{{ route('profile.edit') }}"
                       class="inline-flex items-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-700 transition">
                        Edit Profile
                    </a>
                </div>

                <dl class="divide-y divide-gray-200">
                    <div class="px-6 py-4 grid grid-cols-1 sm:grid-cols-3 gap-2">
                        <dt class="text-sm font-medium text-gray-500">Name</dt>
                        <dd class="text-sm text-gray-900 sm:col-span-2">{{ $user->name }}</dd>
                    </div>
                    <div class="px-6 py-4 grid grid-cols-1 sm:grid-cols-3 gap-2">
                        <dt class="text-sm font-medium text-gray-500">Email</dt>
                        <dd class="text-sm text-gray-900 sm:col-span-2">{{ $user->email }}</dd>
                    </div>
                    <div class="px-6 py-4 grid grid-cols-1 sm:grid-cols-3 gap-2">
                        <dt class="text-sm font-medium text-gray-500">Subscriber ID</dt>
                        <dd class="text-sm text-gray-900 sm:col-span-2">{{ $subscriber->subscriber_id }}</dd>
                    </div>
                    <div class="px-6 py-4 grid grid-cols-1 sm:grid-cols-3 gap-2">
                        <dt class="text-sm font-medium text-gray-500">Contact Number</dt>
                        <dd class="text-sm text-gray-900 sm:col-span-2">{{ $subscriber->contact ?? '—' }}</dd>
                    </div>
                    <div class="px-6 py-4 grid grid-cols-1 sm:grid-cols-3 gap-2">
                        <dt class="text-sm font-medium text-gray-500">Status</dt>
                        <dd class="text-sm sm:col-span-2">
                            <span @class([
                                'inline-flex items-center rounded-md px-2 py-1 text-xs font-medium',
                                'bg-green-50 text-green-700' => $subscriber->status === 'active',
                                'bg-amber-50 text-amber-700' => $subscriber->status === 'suspended',
                                'bg-red-50 text-red-700' => $subscriber->status === 'terminated',
                                'bg-gray-100 text-gray-600' => !in_array($subscriber->status, ['active', 'suspended', 'terminated']),
                            ])>
                                {{ ucfirst($subscriber->status) }}
                            </span>
                        </dd>
                    </div>
                    <div class="px-6 py-4 grid grid-cols-1 sm:grid-cols-3 gap-2">
                        <dt class="text-sm font-medium text-gray-500">Joined Date</dt>
                        <dd class="text-sm text-gray-900 sm:col-span-2">{{ $subscriber->joined_date?->format('M d, Y') }}</dd>
                    </div>
                </dl>
            </div>

            {{-- Current plan card --}}
            <div class="mt-6 bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="px-6 py-5 border-b border-gray-200">
                    <h3 class="text-sm font-semibold text-gray-900">Current Plan</h3>
                </div>

                @if ($subscriber->plan)
                    <dl class="divide-y divide-gray-200">
                        <div class="px-6 py-4 grid grid-cols-1 sm:grid-cols-3 gap-2">
                            <dt class="text-sm font-medium text-gray-500">Plan Name</dt>
                            <dd class="text-sm text-gray-900 sm:col-span-2">{{ $subscriber->plan->name }}</dd>
                        </div>
                        <div class="px-6 py-4 grid grid-cols-1 sm:grid-cols-3 gap-2">
                            <dt class="text-sm font-medium text-gray-500">Speed</dt>
                            <dd class="text-sm text-gray-900 sm:col-span-2">{{ $subscriber->plan->speed }}</dd>
                        </div>
                        <div class="px-6 py-4 grid grid-cols-1 sm:grid-cols-3 gap-2">
                            <dt class="text-sm font-medium text-gray-500">Monthly Price</dt>
                            <dd class="text-sm text-gray-900 sm:col-span-2">₱{{ number_format((float) $subscriber->plan->price, 2) }}</dd>
                        </div>
                        <div class="px-6 py-4 grid grid-cols-1 sm:grid-cols-3 gap-2">
                            <dt class="text-sm font-medium text-gray-500">Billing Cycle</dt>
                            <dd class="text-sm text-gray-900 sm:col-span-2">{{ ucfirst($subscriber->plan->billing_cycle) }}</dd>
                        </div>
                    </dl>
                @else
                    <div class="p-10 text-center">
                        <p class="text-sm text-gray-500">No plan assigned yet.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-subscriber-layout>

