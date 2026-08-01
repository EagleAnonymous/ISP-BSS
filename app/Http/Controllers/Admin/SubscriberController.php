<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreSubscriberRequest;
use App\Models\Invoice;
use App\Models\Plan;
use App\Models\Subscriber;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class SubscriberController extends Controller
{
    /**
     * Show every subscriber in a table, newest first.
     */
    public function index(): View
    {
        $subscribers = Subscriber::with(['user', 'plan'])->latest()->get();

        return view('admin.subscribers.index', ['subscribers' => $subscribers]);
    }

    /**
     * Show the "add a subscriber" form. Only active plans are offered.
     */
    public function create(): View
    {
        $plans = Plan::where('is_active', true)->orderBy('name')->get();

        return view('admin.subscribers.create', ['plans' => $plans]);
    }

    /**
     * Create a subscriber's login, profile, starting plan, AND initial invoice together.
     */
    public function store(StoreSubscriberRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        DB::transaction(function () use ($validated) {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
            ]);

            $user->assignRole('subscriber');

            $subscriber = Subscriber::create([
                'user_id' => $user->id,
                'subscriber_id' => $this->nextSubscriberId(),
                'contact' => $validated['contact'] ?? null,
                'plan_id' => $validated['plan_id'],
                'status' => $validated['status'],
                'joined_date' => $validated['joined_date'],
            ]);

            $plan = Plan::findOrFail($validated['plan_id']);

            $subscription = Subscription::create([
                'subscriber_id' => $subscriber->id,
                'plan_id' => $plan->id,
                'locked_price' => $plan->price,
                'status' => 'active',
                'starts_at' => $validated['joined_date'],
            ]);

            // AWTO-GENERATE NG UNANG INVOICE PARA SA BAGONG SUBSCRIBER
            $start = Carbon::parse($validated['joined_date'])->startOfMonth();
            $end = Carbon::parse($validated['joined_date'])->endOfMonth();

            Invoice::create([
                'subscriber_id' => $subscriber->id,
                'subscription_id' => $subscription->id,
                'invoice_number' => $this->nextInvoiceNumber(),
                'amount' => $plan->price,
                'billing_period_start' => $start->toDateString(),
                'billing_period_end' => $end->toDateString(),
                'due_date' => $end->copy()->addDays(7)->toDateString(),
                'status' => 'unpaid',
            ]);
        });

        return redirect()
            ->route('admin.subscribers.index')
            ->with('status', 'subscriber-created');
    }

    /**
     * Work out the next subscriber ID, e.g. "SUB-00042".
     */
    private function nextSubscriberId(): string
    {
        $lastSubscriber = Subscriber::orderBy('id', 'desc')->lockForUpdate()->first();

        $next = $lastSubscriber ? ($lastSubscriber->id + 1) : 1;

        return 'SUB-'.str_pad((string) $next, 5, '0', STR_PAD_LEFT);
    }

    /**
     * Work out the next invoice number, e.g. "INV-2026-000123".
     */
    private function nextInvoiceNumber(): string
    {
        $lastInvoice = Invoice::orderBy('id', 'desc')->lockForUpdate()->first();

        $next = $lastInvoice ? ($lastInvoice->id + 1) : 1;

        return 'INV-'.now()->format('Y').'-'.str_pad((string) $next, 6, '0', STR_PAD_LEFT);
    }
}