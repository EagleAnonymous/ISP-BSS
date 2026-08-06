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
            {{-- Billing & Payments page header --}}
            <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Billing &amp; Payments</h1>
                    <p class="mt-1 text-sm text-gray-500">View your billing summary, payment history, and manage payment methods.</p>
                </div>
            </div>

            {{-- Billing summary metric cards --}}
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-stretch mb-6">
                {{-- Outstanding balance --}}
                <div class="bg-white p-5 rounded-xl border border-gray-100 shadow-sm h-full flex items-center justify-between min-h-[130px]">
                    <div class="flex flex-col justify-center min-w-0 pr-2">
                        <span class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Outstanding Balance</span>
                        <h4 class="text-sm font-semibold {{ $outstandingBalance > 0 ? 'text-amber-700' : 'text-green-700' }}">
                            ₱{{ number_format($outstandingBalance, 2) }}
                        </h4>
                        <p class="text-[11px] text-gray-500 mt-0.5">
                            {{ $outstandingBalance > 0 ? $unpaidCount.' unpaid invoice(s)' : 'You have no outstanding balance' }}
                        </p>
                    </div>
                    <div class="flex-shrink-0 w-12 h-12 rounded-full flex items-center justify-center bg-blue-50 text-blue-600">
                        <img src="{{ asset('image/Staff/wallet-svgrepo-com.svg') }}" alt="Outstanding Balance" class="h-6 w-6 object-contain">
                    </div>
                </div>

                {{-- Paid this month --}}
                <div class="bg-white p-5 rounded-xl border border-gray-100 shadow-sm h-full flex items-center justify-between min-h-[130px]">
                    <div class="flex flex-col justify-center min-w-0 pr-2">
                        <span class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Paid This Month</span>
                        <h4 class="text-sm font-semibold text-green-700">₱{{ number_format($paidThisMonth, 2) }}</h4>
                        <p class="text-[11px] text-gray-500 mt-0.5">Successful payments this month</p>
                    </div>
                    <div class="flex-shrink-0 w-12 h-12 rounded-full flex items-center justify-center bg-green-50 text-green-600">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>

                {{-- Next billing date --}}
                <div class="bg-white p-5 rounded-xl border border-gray-100 shadow-sm h-full flex items-center justify-between min-h-[130px]">
                    <div class="flex flex-col justify-center min-w-0 pr-2">
                        <span class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Next Billing Date</span>
                        <h4 class="text-sm font-semibold text-gray-900 whitespace-nowrap">
                            {{ $nextBillingDate?->format('F d, Y') ?? '—' }}
                        </h4>
                        <p class="text-[11px] text-gray-500 mt-0.5">
                            {{ $daysUntilBilling !== null ? $daysUntilBilling.' days remaining' : '—' }}
                        </p>
                    </div>
                    <div class="flex-shrink-0 w-12 h-12 rounded-full flex items-center justify-center bg-orange-50 text-orange-600">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                        </svg>
                    </div>
                </div>

                {{-- Last payment --}}
                <div class="bg-white p-5 rounded-xl border border-gray-100 shadow-sm h-full flex items-center justify-between min-h-[130px]">
                    <div class="flex flex-col justify-center min-w-0 pr-2">
                        <span class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Last Payment</span>
                        <h4 class="text-sm font-semibold text-gray-900">
                            ₱{{ number_format($lastPayment?->amount ?? 0, 2) }}
                        </h4>
                        <p class="text-[11px] text-gray-500 mt-0.5">
                            {{ $lastPayment ? 'Paid on '.$lastPayment->paid_at->format('F j, Y') : 'No payment recorded yet' }}
                        </p>
                    </div>
                    <div class="flex-shrink-0 w-12 h-12 rounded-full flex items-center justify-center bg-purple-50 text-purple-600">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.288 15.038a5.25 5.25 0 017.424 0M5.106 11.856c3.807-3.808 9.98-3.808 13.788 0M1.924 8.674c5.565-5.565 14.587-5.565 20.152 0M12.53 18.22l-.53.53-.53-.53a.75.75 0 011.06 0z" />
                        </svg>
                    </div>
                </div>
            </div>

            {{-- Full-width invoice history table --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                    <h3 class="text-base font-semibold text-gray-900">Billing History</h3>
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700">
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        {{ $totalInvoices }} Total Invoice(s)
                    </span>
                </div>

                @if ($invoices->isEmpty())
                    <div class="p-10 text-center">
                        <p class="text-sm text-gray-500">No invoices yet.</p>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            {{-- Billing history table header --}}
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Invoice #</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Period</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Due Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @foreach ($invoices as $invoice)
                                    {{-- Invoice row --}}
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

                    {{-- Pagination footer --}}
                    <div class="flex flex-col gap-3 sm:flex-row items-center justify-between px-6 py-4 border-t border-gray-200">
                        <p class="text-sm text-gray-500">
                            Showing 1 to {{ $invoices->count() }} of {{ $totalInvoices }} invoices
                        </p>
                        <nav class="flex items-center gap-1">
                            <button type="button" class="flex h-8 w-8 items-center justify-center rounded-lg text-sm font-medium text-gray-500 hover:bg-gray-100 transition" title="Previous Page">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
                                </svg>
                            </button>
                            <button type="button" class="flex h-8 w-8 items-center justify-center rounded-lg text-sm font-semibold text-white bg-blue-600 transition">1</button>
                            @if ($totalInvoices > 5)
                                <button type="button" class="flex h-8 w-8 items-center justify-center rounded-lg text-sm font-medium text-gray-500 hover:bg-gray-100 transition">2</button>
                            @endif
                            @if ($totalInvoices > 10)
                                <button type="button" class="flex h-8 w-8 items-center justify-center rounded-lg text-sm font-medium text-gray-500 hover:bg-gray-100 transition">3</button>
                            @endif
                            <button type="button" class="flex h-8 w-8 items-center justify-center rounded-lg text-sm font-medium text-gray-500 hover:bg-gray-100 transition" title="Next Page">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                                </svg>
                            </button>
                        </nav>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-subscriber-layout>
