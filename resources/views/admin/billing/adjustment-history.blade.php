<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-900">Adjustment History &middot; {{ $invoice->invoice_number }}</h2>
                <p class="mt-1 text-sm text-gray-500">{{ $invoice->subscriber->user->name }} ({{ $invoice->subscriber->subscriber_id }})</p>
            </div>
            <a href="{{ route('admin.billing.index') }}" class="text-sm font-medium text-gray-600 hover:text-gray-900">
                &larr; Back to Billing
            </a>
        </div>
    </x-slot>

    <div class="max-w-3xl bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="p-6 border-b border-gray-200 flex items-center justify-between">
            <span class="text-sm text-gray-500">Original amount</span>
            <span class="text-sm font-medium text-gray-900">₱{{ number_format($invoice->amount, 2) }}</span>
        </div>

        @if ($invoice->adjustments->isEmpty())
            <div class="p-10 text-center">
                <p class="text-sm text-gray-500">No adjustments have been made to this invoice.</p>
            </div>
        @else
            <ul class="divide-y divide-gray-200">
                @foreach ($invoice->adjustments->sortBy('created_at') as $adjustment)
                    <li class="p-6 flex items-start justify-between gap-4">
                        <div>
                            <p class="text-sm font-medium text-gray-900">
                                <span @class([
                                    'inline-flex items-center rounded-md px-2 py-0.5 text-xs font-medium mr-2',
                                    'bg-red-50 text-red-700' => $adjustment->type === 'charge',
                                    'bg-green-50 text-green-700' => $adjustment->type === 'credit',
                                ])>
                                    {{ ucfirst($adjustment->type) }}
                                </span>
                                {{ $adjustment->reason }}
                            </p>
                            <p class="mt-1 text-xs text-gray-500">
                                {{ $adjustment->created_at->format('M j, Y g:i A') }} &middot; by {{ $adjustment->creator->name }}
                            </p>
                        </div>
                        <span class="text-sm font-semibold {{ $adjustment->type === 'charge' ? 'text-red-700' : 'text-green-700' }} whitespace-nowrap">
                            {{ $adjustment->type === 'charge' ? '+' : '-' }}₱{{ number_format($adjustment->amount, 2) }}
                        </span>
                    </li>
                @endforeach
            </ul>
        @endif

        <div class="p-6 bg-gray-50 flex items-center justify-between">
            <span class="text-sm font-semibold text-gray-900">Amount due</span>
            <span class="text-lg font-semibold text-gray-900">₱{{ number_format($invoice->amount_due, 2) }}</span>
        </div>
    </div>
</x-admin-layout>