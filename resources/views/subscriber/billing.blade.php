<x-subscriber-layout>
    <x-slot name="header">
        <div>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Billing') }}
            </h2>
            <p class="mt-1 text-sm text-gray-500">Your invoices, payments, and outstanding balance.</p>
        </div>
    </x-slot>

    <div class="py-2">
        <div class="max-w-7xl mx-auto">
            {{-- Summary cards --}}
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Outstanding Balance</p>
                    <p class="mt-1.5 text-2xl font-semibold {{ $outstandingBalance > 0 ? 'text-amber-700' : 'text-green-700' }}">
                        ₱{{ number_format($outstandingBalance, 2) }}
                    </p>
                </div>
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Paid This Month</p>
                    <p class="mt-1.5 text-2xl font-semibold text-green-700">₱{{ number_format($paidThisMonth, 2) }}</p>
                </div>
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Unpaid Invoices</p>
                    <p class="mt-1.5 text-2xl font-semibold text-gray-900">{{ $unpaidCount }}</p>
                </div>
            </div>

            {{-- Invoices table --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-sm font-semibold text-gray-900">Invoice History</h3>
                </div>

                @if ($invoices->isEmpty())
                    <div class="p-10 text-center">
                        <p class="text-sm text-gray-500">No invoices yet.</p>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Invoice #</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Period</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Due Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount Due</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @foreach ($invoices as $invoice)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $invoice->invoice_number }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                            {{ $invoice->billing_period_start->format('M j') }} - {{ $invoice->billing_period_end->format('M j, Y') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $invoice->due_date->format('M d, Y') }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">₱{{ number_format($invoice->amount_due, 2) }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            <span @class([
                                                'inline-flex items-center rounded-md px-2 py-1 text-xs font-medium',
                                                'bg-amber-50 text-amber-700' => $invoice->effective_status === 'unpaid',
                                                'bg-green-50 text-green-700' => $invoice->effective_status === 'paid',
                                                'bg-red-50 text-red-700' => $invoice->effective_status === 'overdue',
                                            ])>
                                                {{ ucfirst($invoice->effective_status) }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-subscriber-layout>

