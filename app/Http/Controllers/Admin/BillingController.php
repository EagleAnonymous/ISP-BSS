<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\GenerateInvoicesRequest;
use App\Http\Requests\Admin\MarkInvoicePaidRequest;
use App\Http\Requests\Admin\StoreAdjustmentRequest;
use App\Models\ActivityLog;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Subscription;
use App\Notifications\OverdueReminder;
use App\Services\BillingSummary;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class BillingController extends Controller
{
    /**
     * Show every invoice system-wide, with optional filters.
     */
    public function index(Request $request, BillingSummary $summary): View
    {
        $invoices = $this->filteredInvoices($request)->paginate(15)->withQueryString();

        return view('admin.billing.index', [
            'invoices' => $invoices,
            'summary' => $summary->currentPeriod(),
            'filters' => $request->only(['status', 'from', 'to', 'search']),
            'tab' => 'all',
        ]);
    }

    /**
     * Show only invoices that are unpaid and past their due date.
     */
    public function overdue(Request $request, BillingSummary $summary): View
    {
        $invoices = $this->filteredInvoices($request)->overdue()->paginate(15)->withQueryString();

        return view('admin.billing.index', [
            'invoices' => $invoices,
            'summary' => $summary->currentPeriod(),
            'filters' => $request->only(['search']),
            'tab' => 'overdue',
        ]);
    }

    /**
     * Create one invoice per active subscription for a billing period.
     *
     * Safe to click more than once: any subscriber who already has an
     * invoice covering (or overlapping) the chosen period is skipped
     * instead of being billed twice for the same days.
     */
    public function generate(GenerateInvoicesRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $start = isset($validated['period_start'])
            ? Carbon::parse($validated['period_start'])
            : Carbon::now()->startOfMonth();

        $end = isset($validated['period_end'])
            ? Carbon::parse($validated['period_end'])
            : Carbon::now()->endOfMonth();

        $generated = 0;
        $skipped = 0;

        DB::transaction(function () use ($start, $end, &$generated, &$skipped) {
            $subscriptions = Subscription::where('status', 'active')->get();

            foreach ($subscriptions as $subscription) {
                // "Overlapping" (not just an exact date match) so a slightly
                // shifted period can't sneak past this check and double-bill
                // the same days.
                $alreadyBilled = Invoice::where('subscriber_id', $subscription->subscriber_id)
                    ->whereDate('billing_period_start', '<=', $end->toDateString())
                    ->whereDate('billing_period_end', '>=', $start->toDateString())
                    ->exists();

                if ($alreadyBilled) {
                    $skipped++;

                    continue;
                }

                Invoice::create([
                    'subscriber_id' => $subscription->subscriber_id,
                    'subscription_id' => $subscription->id,
                    'invoice_number' => $this->nextInvoiceNumber(),
                    'amount' => $subscription->locked_price,
                    'billing_period_start' => $start->toDateString(),
                    'billing_period_end' => $end->toDateString(),
                    'due_date' => $end->copy()->addDays(7)->toDateString(),
                    'status' => 'unpaid',
                ]);

                $generated++;
            }
        });

        return redirect()
            ->route('admin.billing.index')
            ->with('status', 'invoices-generated')
            ->with('generated_count', $generated)
            ->with('skipped_count', $skipped);
    }

    /**
     * Record a manual payment and mark the invoice paid.
     *
     * The invoice row is locked for the duration of this check-then-update,
     * so if two admins click "Mark Paid" on the same invoice at the same
     * moment, the second one is safely told it's already paid instead of
     * both payments going through.
     */
    public function markPaid(MarkInvoicePaidRequest $request, Invoice $invoice): RedirectResponse
    {
        $validated = $request->validated();

        DB::transaction(function () use ($validated, $invoice) {
            $locked = Invoice::where('id', $invoice->id)->lockForUpdate()->firstOrFail();

            abort_if($locked->status === 'paid', 422, 'This invoice is already paid.');

            Payment::create([
                'invoice_id' => $invoice->id,
                'amount' => $invoice->amount_due,
                'method' => $validated['method'],
                'reference_number' => $validated['reference_number'] ?? null,
                'status' => 'successful',
                'paid_at' => now(),
                'created_by' => Auth::id(),
            ]);

            $locked->update(['status' => 'paid', 'paid_at' => now()]);

            // The subscriber dashboard (and billing page) caches the
            // outstanding balance and unpaid count for several minutes.
            // Forget those keys now so the subscriber immediately sees the
            // cleared balance instead of a stale value until the cache expires.
            $subscriberUserId = $locked->subscriber?->user_id;

            if ($subscriberUserId) {
                Cache::forget("subscriber.{$subscriberUserId}.outstanding_balance");
                Cache::forget("subscriber.{$subscriberUserId}.unpaid_count");
            }

            ActivityLog::record(
                'invoice.marked_paid',
                $invoice,
                'Marked invoice '.$invoice->invoice_number.' as paid via '.$validated['method']
            );
        });

        return redirect()->route('admin.billing.index')->with('status', 'invoice-marked-paid');
    }

    public function storeAdjustment(StoreAdjustmentRequest $request, Invoice $invoice): RedirectResponse
    {
        $validated = $request->validated();

        $invoice->adjustments()->create([
            'type' => $validated['type'],
            'amount' => $validated['amount'],
            'reason' => $validated['reason'],
            'created_by' => Auth::id(),
        ]);

        // An adjustment (charge/credit) changes the amount due, so refresh
        // the subscriber's cached outstanding balance and unpaid count.
        $subscriberUserId = $invoice->subscriber?->user_id;

        if ($subscriberUserId) {
            Cache::forget("subscriber.{$subscriberUserId}.outstanding_balance");
            Cache::forget("subscriber.{$subscriberUserId}.unpaid_count");
        }

        return redirect()->route('admin.billing.index')->with('status', 'adjustment-added');
    }

    public function adjustmentHistory(Invoice $invoice): View
    {
        $invoice->load(['adjustments.creator']);

        return view('admin.billing.adjustment-history', ['invoice' => $invoice]);
    }

    public function remind(Invoice $invoice): RedirectResponse
    {
        $invoice->subscriber->user->notify(new OverdueReminder($invoice));

        return redirect()->route('admin.billing.index')->with('status', 'reminder-sent');
    }

    public function remindAll(): RedirectResponse
    {
        $overdueInvoices = Invoice::overdue()->with('subscriber.user')->get();

        foreach ($overdueInvoices as $invoice) {
            $invoice->subscriber->user->notify(new OverdueReminder($invoice));
        }

        return redirect()
            ->route('admin.billing.overdue')
            ->with('status', 'reminders-sent')
            ->with('reminders_sent_count', $overdueInvoices->count());
    }

    private function filteredInvoices(Request $request): EloquentBuilder
    {
        $query = Invoice::query()->with(['subscriber.user', 'adjustments'])->latest();

        if ($request->filled('status')) {
            $query->status($request->string('status'));
        }

        if ($request->filled('from')) {
            $query->whereDate('billing_period_start', '>=', $request->date('from'));
        }

        if ($request->filled('to')) {
            $query->whereDate('billing_period_end', '<=', $request->date('to'));
        }

        if ($request->filled('search')) {
            $search = $request->string('search');

            $query->whereHas('subscriber', function ($q) use ($search) {
                $q->where('subscriber_id', 'like', "%{$search}%")
                    ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$search}%"));
            });
        }

        return $query;
    }

    /**
     * Work out the next invoice number, e.g. "INV-2026-000123".
     *
     * Uses PostgreSQL-compatible row locking on the latest record
     * instead of applying `lockForUpdate()` on an aggregate `count()`.
     */
    private function nextInvoiceNumber(): string
    {
        $lastInvoice = Invoice::orderBy('id', 'desc')->lockForUpdate()->first();

        $next = $lastInvoice ? ($lastInvoice->id + 1) : 1;

        return 'INV-'.now()->format('Y').'-'.str_pad((string) $next, 6, '0', STR_PAD_LEFT);
    }
}
