<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-900">Billing</h2>
                <p class="mt-1 text-sm text-gray-500">System-wide invoice oversight, payments, and reminders.</p>
            </div>
            <button type="button" @click="$dispatch('open-modal', 'generate-invoices')"
                class="inline-flex items-center rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-blue-700 transition">
                Generate Invoices
            </button>
        </div>
    </x-slot>

    {{-- Flash messages --}}
    @if (session('status') === 'invoices-generated')
        <div class="mb-6 rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-700">
            Generated {{ session('generated_count') }} invoice(s), skipped {{ session('skipped_count') }} (already had one for this period).
        </div>
    @elseif (session('status') === 'invoice-marked-paid')
        <div class="mb-6 rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-700">
            Invoice marked as paid.
        </div>
    @elseif (session('status') === 'adjustment-added')
        <div class="mb-6 rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-700">
            Adjustment added.
        </div>
    @elseif (session('status') === 'reminder-sent')
        <div class="mb-6 rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-700">
            Reminder sent.
        </div>
    @elseif (session('status') === 'reminders-sent')
        <div class="mb-6 rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-700">
            Sent {{ session('reminders_sent_count') }} reminder(s) to overdue subscribers.
        </div>
    @endif

    {{-- Billed vs Collected (kept as two separate figures, never blended) --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Billed (this month)</p>
            <p class="mt-1.5 text-2xl font-semibold text-gray-900">₱{{ number_format($summary['billed'], 2) }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Collected (this month)</p>
            <p class="mt-1.5 text-2xl font-semibold text-green-700">₱{{ number_format($summary['collected'], 2) }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Outstanding</p>
            <p class="mt-1.5 text-2xl font-semibold text-amber-700">₱{{ number_format($summary['outstanding'], 2) }}</p>
        </div>
    </div>

    {{-- Tabs --}}
    <div class="mb-6 border-b border-gray-200">
        <nav class="flex gap-6 -mb-px">
            <a href="{{ route('admin.billing.index') }}"
               class="border-b-2 px-1 py-3 text-sm font-medium {{ $tab === 'all' ? 'border-blue-600 text-blue-700' : 'border-transparent text-gray-500 hover:text-gray-700' }}">
                All Invoices
            </a>
            <a href="{{ route('admin.billing.overdue') }}"
               class="border-b-2 px-1 py-3 text-sm font-medium {{ $tab === 'overdue' ? 'border-blue-600 text-blue-700' : 'border-transparent text-gray-500 hover:text-gray-700' }}">
                Overdue
            </a>
        </nav>
    </div>

    {{-- Filters --}}
    <form method="GET" action="{{ $tab === 'overdue' ? route('admin.billing.overdue') : route('admin.billing.index') }}"
        class="mb-6 flex flex-wrap items-end gap-3">
        @if ($tab !== 'overdue')
            <div>
                <label for="status" class="block text-xs font-medium text-gray-600 mb-1">Status</label>
                <select id="status" name="status"
                    class="rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-600 focus:outline-none focus:ring-1 focus:ring-blue-600">
                    <option value="">All statuses</option>
                    @foreach (['unpaid' => 'Unpaid', 'paid' => 'Paid', 'overdue' => 'Overdue'] as $value => $label)
                        <option value="{{ $value }}" {{ ($filters['status'] ?? '') === $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        @endif

        <div>
            <label for="from" class="block text-xs font-medium text-gray-600 mb-1">From</label>
            <input id="from" type="date" name="from" value="{{ $filters['from'] ?? '' }}"
                class="rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-600 focus:outline-none focus:ring-1 focus:ring-blue-600">
        </div>

        <div>
            <label for="to" class="block text-xs font-medium text-gray-600 mb-1">To</label>
            <input id="to" type="date" name="to" value="{{ $filters['to'] ?? '' }}"
                class="rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-600 focus:outline-none focus:ring-1 focus:ring-blue-600">
        </div>

        <div class="flex-1 min-w-[200px]">
            <label for="search" class="block text-xs font-medium text-gray-600 mb-1">Search</label>
            <input id="search" type="text" name="search" value="{{ $filters['search'] ?? '' }}"
                placeholder="Subscriber name or account number"
                class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-900 placeholder:text-gray-400 shadow-sm focus:border-blue-600 focus:outline-none focus:ring-1 focus:ring-blue-600">
        </div>

        <button type="submit"
            class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-gray-700 transition">
            Filter
        </button>

        @if ($tab === 'overdue' && $invoices->total() > 0)
            <form method="POST" action="{{ route('admin.billing.remind-all') }}" class="ml-auto">
                @csrf
                <button type="submit"
                    class="rounded-lg bg-amber-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-amber-700 transition">
                    Remind All Overdue ({{ $invoices->total() }})
                </button>
            </form>
        @endif
    </form>

    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        @if ($invoices->isEmpty())
            <div class="p-10 text-center">
                <p class="text-sm text-gray-500">No invoices match these filters.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Invoice #</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subscriber</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Period</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount Due</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Adjustments</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach ($invoices as $invoice)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $invoice->invoice_number }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                    {{ $invoice->subscriber->user->name }}
                                    <span class="block text-xs text-gray-400">{{ $invoice->subscriber->subscriber_id }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                    {{ $invoice->billing_period_start->format('M j') }} - {{ $invoice->billing_period_end->format('M j, Y') }}
                                </td>
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
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <a href="{{ route('admin.billing.adjustments.history', $invoice) }}" class="text-blue-600 hover:text-blue-700 font-medium">
                                        {{ $invoice->adjustments->count() }} adjustment{{ $invoice->adjustments->count() === 1 ? '' : 's' }}
                                    </a>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                                    <div class="flex items-center justify-end gap-3">
                                        @if ($invoice->status !== 'paid')
                                            <button type="button" @click="$dispatch('open-modal', 'mark-paid-{{ $invoice->id }}')"
                                                class="text-green-700 hover:text-green-800 font-medium">Mark Paid</button>
                                        @endif
                                        <button type="button" @click="$dispatch('open-modal', 'adjustment-{{ $invoice->id }}')"
                                            class="text-gray-600 hover:text-gray-900 font-medium">Adjust</button>
                                        @if ($invoice->effective_status === 'overdue')
                                            <form method="POST" action="{{ route('admin.billing.remind', $invoice) }}">
                                                @csrf
                                                <button type="submit" class="text-amber-700 hover:text-amber-800 font-medium">Remind</button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <div class="mt-4">
        {{ $invoices->links() }}
    </div>

    {{-- Generate Invoices modal --}}
    <x-modal name="generate-invoices">
        <form method="POST" action="{{ route('admin.billing.generate') }}" class="p-6">
            @csrf
            <h3 class="text-lg font-semibold text-gray-900">Generate Invoices</h3>
            <p class="mt-1 text-sm text-gray-500">
                Creates one invoice per active subscription for the selected period. Subscribers who already have an invoice for that period are skipped automatically.
            </p>

            <div class="mt-5 grid grid-cols-2 gap-4">
                <div>
                    <label for="period_start" class="block text-sm font-medium text-gray-700 mb-1.5">Period start</label>
                    <input id="period_start" type="date" name="period_start" value="{{ now()->startOfMonth()->toDateString() }}"
                        class="block w-full rounded-lg border border-gray-300 px-3.5 py-2.5 text-sm text-gray-900 shadow-sm focus:border-blue-600 focus:outline-none focus:ring-1 focus:ring-blue-600">
                </div>
                <div>
                    <label for="period_end" class="block text-sm font-medium text-gray-700 mb-1.5">Period end</label>
                    <input id="period_end" type="date" name="period_end" value="{{ now()->endOfMonth()->toDateString() }}"
                        class="block w-full rounded-lg border border-gray-300 px-3.5 py-2.5 text-sm text-gray-900 shadow-sm focus:border-blue-600 focus:outline-none focus:ring-1 focus:ring-blue-600">
                </div>
            </div>

            <div class="mt-6 flex items-center gap-3">
                <button type="submit" class="rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-blue-700 transition">
                    Generate
                </button>
                <button type="button" @click="$dispatch('close-modal', 'generate-invoices')" class="text-sm font-medium text-gray-600 hover:text-gray-900">
                    Cancel
                </button>
            </div>
        </form>
    </x-modal>

    {{-- Per-invoice Mark Paid / Adjustment modals --}}
    @foreach ($invoices as $invoice)
        <x-modal :name="'mark-paid-'.$invoice->id">
            <form method="POST" action="{{ route('admin.billing.mark-paid', $invoice) }}" class="p-6">
                @csrf
                <h3 class="text-lg font-semibold text-gray-900">Mark {{ $invoice->invoice_number }} as Paid</h3>
                <p class="mt-1 text-sm text-gray-500">Records a payment of ₱{{ number_format($invoice->amount_due, 2) }} and logs this action.</p>

                <div class="mt-5">
                    <label for="method-{{ $invoice->id }}" class="block text-sm font-medium text-gray-700 mb-1.5">Payment method</label>
                    <select id="method-{{ $invoice->id }}" name="method" required
                        class="block w-full rounded-lg border border-gray-300 px-3.5 py-2.5 text-sm text-gray-900 shadow-sm focus:border-blue-600 focus:outline-none focus:ring-1 focus:ring-blue-600">
                        <option value="cash">Cash</option>
                        <option value="bank_transfer">Bank Transfer</option>
                    </select>
                </div>

                <div class="mt-4">
                    <label for="reference-{{ $invoice->id }}" class="block text-sm font-medium text-gray-700 mb-1.5">Reference number (optional)</label>
                    <input id="reference-{{ $invoice->id }}" type="text" name="reference_number"
                        class="block w-full rounded-lg border border-gray-300 px-3.5 py-2.5 text-sm text-gray-900 shadow-sm focus:border-blue-600 focus:outline-none focus:ring-1 focus:ring-blue-600">
                </div>

                <div class="mt-6 flex items-center gap-3">
                    <button type="submit" class="rounded-lg bg-green-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-green-700 transition">
                        Confirm Payment
                    </button>
                    <button type="button" @click="$dispatch('close-modal', 'mark-paid-{{ $invoice->id }}')" class="text-sm font-medium text-gray-600 hover:text-gray-900">
                        Cancel
                    </button>
                </div>
            </form>
        </x-modal>

        <x-modal :name="'adjustment-'.$invoice->id">
            <form method="POST" action="{{ route('admin.billing.adjustments.store', $invoice) }}" class="p-6">
                @csrf
                <h3 class="text-lg font-semibold text-gray-900">Adjust {{ $invoice->invoice_number }}</h3>
                <p class="mt-1 text-sm text-gray-500">Adjustments are appended to the invoice's history and never overwrite the original amount.</p>

                <div class="mt-5">
                    <label for="type-{{ $invoice->id }}" class="block text-sm font-medium text-gray-700 mb-1.5">Type</label>
                    <select id="type-{{ $invoice->id }}" name="type" required
                        class="block w-full rounded-lg border border-gray-300 px-3.5 py-2.5 text-sm text-gray-900 shadow-sm focus:border-blue-600 focus:outline-none focus:ring-1 focus:ring-blue-600">
                        <option value="credit">Credit (reduces amount due)</option>
                        <option value="charge">Charge (increases amount due)</option>
                    </select>
                </div>

                <div class="mt-4">
                    <label for="amount-{{ $invoice->id }}" class="block text-sm font-medium text-gray-700 mb-1.5">Amount</label>
                    <input id="amount-{{ $invoice->id }}" type="number" step="0.01" min="0.01" name="amount" required
                        class="block w-full rounded-lg border border-gray-300 px-3.5 py-2.5 text-sm text-gray-900 shadow-sm focus:border-blue-600 focus:outline-none focus:ring-1 focus:ring-blue-600">
                </div>

                <div class="mt-4">
                    <label for="reason-{{ $invoice->id }}" class="block text-sm font-medium text-gray-700 mb-1.5">Reason</label>
                    <input id="reason-{{ $invoice->id }}" type="text" name="reason" required
                        class="block w-full rounded-lg border border-gray-300 px-3.5 py-2.5 text-sm text-gray-900 shadow-sm focus:border-blue-600 focus:outline-none focus:ring-1 focus:ring-blue-600">
                </div>

                <div class="mt-6 flex items-center gap-3">
                    <button type="submit" class="rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-blue-700 transition">
                        Add Adjustment
                    </button>
                    <button type="button" @click="$dispatch('close-modal', 'adjustment-{{ $invoice->id }}')" class="text-sm font-medium text-gray-600 hover:text-gray-900">
                        Cancel
                    </button>
                </div>
            </form>
        </x-modal>
    @endforeach
</x-admin-layout>