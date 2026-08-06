<?php

namespace App\Http\Controllers\Subscriber;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Subscriber;
use App\Models\Ticket;
use App\Models\User;
use App\Notifications\NewTicketNotification;
use App\Services\GroqService;
use App\Services\KnowledgeBaseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Show the subscriber's dashboard overview.
     */
    public function dashboard(Request $request): View
    {
        $user = $request->user();
        $subscriber = $user->subscriber;
        $subscriber?->load(['plan', 'invoices.adjustments', 'payments']);

        $invoices = $subscriber?->invoices()->latest()->limit(5)->get() ?? collect();

        $outstandingBalance = Cache::remember("subscriber.{$user->id}.outstanding_balance", 300, function () use ($subscriber) {
            return $subscriber?->invoices
                ->filter(fn ($invoice) => $invoice->effective_status !== 'paid')
                ->sum('amount_due') ?? 0;
        });

        $unpaidCount = Cache::remember("subscriber.{$user->id}.unpaid_count", 300, function () use ($subscriber) {
            return $subscriber?->invoices
                ->filter(fn ($invoice) => $invoice->effective_status !== 'paid')
                ->count() ?? 0;
        });

        // Pull tickets for recent list.
        $recentTickets = $subscriber?->tickets()->latest()->limit(5)->get() ?? collect();

        // Estimate upcoming monthly billing date.
        $nextBillingDate = $this->nextBillingDate($subscriber);

        // Calculate days until next billing.
        $daysUntilBilling = $nextBillingDate
            ? (int) Carbon::today()->startOfDay()->diffInDays($nextBillingDate)
            : null;

        // Resolve the most recent successful payment.
        $lastPayment = $subscriber?->payments
            ->where('status', 'successful')
            ->sortByDesc('paid_at')
            ->first();

        // Count total invoices for pagination footer.
        $totalInvoices = $subscriber?->invoices()->count() ?? 0;

        return view('subscriber.dashboard', [
            'user' => $user,
            'subscriber' => $subscriber,
            'plan' => $subscriber?->plan,
            'invoices' => $invoices,
            'outstandingBalance' => $outstandingBalance,
            'unpaidCount' => $unpaidCount,
            'recentTickets' => $recentTickets,
            'nextBillingDate' => $nextBillingDate,
            'daysUntilBilling' => $daysUntilBilling,
            'lastPayment' => $lastPayment,
            'totalInvoices' => $totalInvoices,
        ]);
    }

    /* Estimate upcoming monthly billing date based on last successful payment. */
    private function nextBillingDate(?Subscriber $subscriber): ?Carbon
    {
        if (! $subscriber) {
            return null;
        }

        $anchor = $subscriber->payments()
            ->where('payments.status', 'successful')
            ->orderByDesc('payments.paid_at')
            ->value('payments.paid_at');

        if (! $anchor) {
            $anchor = $subscriber->invoices()
                ->whereNotNull('paid_at')
                ->latest('paid_at')
                ->value('paid_at');
        }

        if (! $anchor) {
            $anchor = $subscriber->subscriptions()
                ->where('status', 'active')
                ->orderByDesc('starts_at')
                ->value('starts_at');
        }

        if (! $anchor) {
            $anchor = $subscriber->joined_date;
        }

        if (! $anchor) {
            return null;
        }

        try {
            $next = Carbon::parse($anchor)->addMonthNoOverflow()->startOfDay();
        } catch (\Throwable $e) {
            return null;
        }

        while ($next->lte(Carbon::today())) {
            $next->addMonthNoOverflow();
        }

        return $next;
    }

    /*
      Show the subscriber's account details.
     */
    public function account(Request $request): View
    {
        $user = $request->user();
        $subscriber = $user->subscriber;
        $subscriber?->load('plan');

        return view('subscriber.account', [
            'user' => $user,
            'subscriber' => $subscriber,
        ]);
    }

    /* Update one account information field. */
    public function updateAccount(Request $request): RedirectResponse|JsonResponse
    {
        $user = $request->user();
        $subscriber = $user->subscriber;

        abort_unless($subscriber, 422, 'No subscriber record is linked to this account.');

        $field = $request->input('field', 'name');

        if ($field === 'name') {
            $validated = $request->validate([
                'name' => ['required', 'string', 'max:255'],
            ]);

            $user->name = $validated['name'];
            $user->save();
        } elseif ($field === 'email') {
            $validated = $request->validate([
                'email' => [
                    'required',
                    'string',
                    'lowercase',
                    'email',
                    'max:255',
                    Rule::unique('users')->ignore($user->id),
                ],
            ]);

            $user->email = $validated['email'];
            $user->save();
        } elseif ($field === 'contact' || $field === 'phone') {
            $validated = $request->validate([
                'contact' => ['nullable', 'string', 'max:30'],
                'phone' => ['nullable', 'string', 'max:30'],
            ]);

            $subscriber->contact = $validated['contact'] ?? $validated['phone'] ?? null;
            $subscriber->save();
        } elseif ($field === 'service_address') {
            $validated = $request->validate([
                'service_address' => ['nullable', 'string', 'max:1000'],
            ]);

            $subscriber->service_address = $validated['service_address'];
            $subscriber->save();
        }

        if ($request->expectsJson()) {
            return response()->json(['success' => true]);
        }

        // Return to the account panel.
        return redirect()->route('subscriber.account')->with('status', 'account-updated');
    }

    /**
     * Show the subscriber's billing and invoices.
     */
    public function billing(Request $request): View
    {
        $user = $request->user();
        $subscriber = $user->subscriber;
        $subscriber?->load(['plan', 'invoices.adjustments', 'payments']);

        $invoices = $subscriber?->invoices()->latest()->get() ?? collect();

        $outstandingBalance = $invoices
            ->filter(fn ($invoice) => $invoice->effective_status !== 'paid')
            ->sum('amount_due');

        $unpaidCount = $invoices
            ->filter(fn ($invoice) => $invoice->effective_status !== 'paid')
            ->count();

        $paidThisMonth = $subscriber?->payments
            ->filter(fn ($payment) => $payment->status === 'successful' && $payment->paid_at?->isSameMonth(Carbon::now()))
            ->sum('amount') ?? 0;

        // Resolve the most recent successful payment.
        $lastPayment = $subscriber?->payments
            ->where('status', 'successful')
            ->sortByDesc('paid_at')
            ->first();

        // Estimate upcoming monthly billing date.
        $nextBillingDate = $this->nextBillingDate($subscriber);

        // Calculate days until next billing.
        $daysUntilBilling = $nextBillingDate
            ? (int) Carbon::today()->startOfDay()->diffInDays($nextBillingDate)
            : null;

        // Count total invoices for pagination footer.
        $totalInvoices = $subscriber?->invoices()->count() ?? 0;

        return view('subscriber.billing', [
            'user' => $user,
            'subscriber' => $subscriber,
            'plan' => $subscriber?->plan,
            'invoices' => $invoices,
            'outstandingBalance' => $outstandingBalance,
            'unpaidCount' => $unpaidCount,
            'paidThisMonth' => $paidThisMonth,
            'lastPayment' => $lastPayment,
            'nextBillingDate' => $nextBillingDate,
            'daysUntilBilling' => $daysUntilBilling,
            'totalInvoices' => $totalInvoices,
        ]);
    }

    /*
      Show the subscriber's AI chatbot panel.
     */
    public function chatbot(Request $request): View
    {
        $user = $request->user();
        $subscriber = $user->subscriber;

        // Pull tickets for the sidebar widget.
        $recentTickets = $subscriber?->tickets()->latest()->limit(3)->get() ?? collect();

        return view('subscriber.chatbot', [
            'user' => $user,
            'subscriber' => $subscriber,
            'recentTickets' => $recentTickets,
        ]);
    }

    /**
     * Send a message to Groq AI and return the assistant's reply.
     *
     * Accepts the current user message plus the most recent messages from
     * the conversation so Groq has context. A short system prompt personalizes
     * the assistant with the subscriber's name, account number and plan.
     */
    public function chat(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'max:2000'],
            'history' => ['sometimes', 'array'],
            'history.*.role' => ['required', 'string', 'in:user,assistant'],
            'history.*.content' => ['required', 'string', 'max:2000'],
        ]);

        $user = $request->user();
        $subscriber = $user->subscriber;

        $system = 'You are the ISP BSS AI Customer Support Assistant, a helpful, accurate, and friendly support agent for our Internet Service Provider. Your goal is to provide personalized and precise answers by using the detailed customer information provided below. Always be professional, warm, and concise. Match the language the user uses (English or Filipino/Tagalog).';

        $kb = App::make(KnowledgeBaseService::class);

        if ($subscriber) {
            $subscriber->load(['plan', 'invoices', 'payments', 'tickets']);

            $outstandingBalance = $subscriber->invoices
                ->whereNotIn('effective_status', ['paid', 'cancelled'])
                ->sum('amount_due');

            $unpaidCount = $subscriber->invoices
                ->whereNotIn('effective_status', ['paid', 'cancelled'])
                ->count();

            $nextBillingDate = $this->nextBillingDate($subscriber);

            $lastPayment = $subscriber->payments
                ->where('status', 'successful')
                ->sortByDesc('paid_at')
                ->first();

            $openTickets = $subscriber->tickets
                ->whereNotIn('status', ['resolved', 'closed'])
                ->map(fn ($t) => "Ticket #{$t->ticket_number} ({$t->subject}) - Status: {$t->status}")
                ->implode(', ');

            $system .= "\n\n"
                .'Use the following real-time information about the subscriber to answer their questions accurately. Do not make up information. If the data is not available, say so.'
                ."\n\n"
                .'--- SUBSCRIBER INFORMATION ---'
                ."\n"
                .'- Name: '.$user->name."\n"
                .'- Account Number: '.($subscriber->subscriber_id ?? 'N/A')."\n"
                .'- Account Status: '.ucfirst($subscriber->status ?? 'N/A')."\n"
                .'- Contact Number: '.($subscriber->contact ?? 'Not provided')."\n"
                .'- Service Address: '.($subscriber->service_address ?? 'Not provided')."\n"
                .'- Joined Date: '.($subscriber->joined_date ? $subscriber->joined_date->format('F d, Y') : 'N/A')."\n"
                ."\n"
                .'--- SERVICE & PLAN DETAILS ---'."\n"
                .'- Current Plan: '.($subscriber->plan?->name ?? 'N/A')."\n"
                .'- Plan Speed: '.($subscriber->plan?->speed ?? 'N/A')."\n"
                .'- Monthly Cost: ₱'.number_format($subscriber->plan?->price ?? 0, 2)."\n"
                ."\n"
                .'--- BILLING & PAYMENT SUMMARY ---'."\n"
                .'- Outstanding Balance: ₱'.number_format($outstandingBalance, 2)."\n"
                .'- Unpaid Invoices: '.$unpaidCount."\n"
                .'- Next Billing Date: '.($nextBillingDate ? $nextBillingDate->format('F d, Y') : 'N/A')."\n"
                .'- Last Payment Made: '.($lastPayment ? '₱'.number_format($lastPayment->amount, 2).' on '.$lastPayment->paid_at->format('F d, Y') : 'No recent payments')."\n"
                ."\n"
                .'--- SUPPORT TICKET SUMMARY ---'."\n"
                .'- Open Tickets: '.($openTickets ?: 'None')."\n"
                ."\n"
                .'--- RESPONSE GUIDELINES ---'."\n"
                .'1. For billing questions, use the billing summary. Example: "Your outstanding balance is ₱1,299.00 from 1 unpaid invoice.".'."\n"
                 .'2. For connection problems ("no internet", "slow connection"), provide basic troubleshooting (restart router, check cables) first. Only create a support ticket AFTER the user explicitly asks (e.g., "create a ticket") or confirms "yes" when asked. Never create a ticket automatically without the user saying "yes" first. After troubleshooting steps, ask "Would you like me to create a support ticket?" and wait for their response.'."\n"
                .'3. If asked about plan details, use the service & plan details. Example: "You are on the FiberX 1500 plan, which has a speed of up to 300 Mbps.".'."\n"
                 .'4. If the user asks about something unrelated to ISP support, politely decline and steer the conversation back to relevant topics.';

            $system .= "\n\n".$kb->referenceBlock();
        }

        $messages = [
            ['role' => 'system', 'content' => $system],
        ];

        // Include the recent back-and-forth so the reply is contextual.
        foreach (array_slice($validated['history'] ?? [], -12) as $turn) {
            $messages[] = ['role' => $turn['role'], 'content' => $turn['content']];
        }

        $messages[] = ['role' => 'user', 'content' => $validated['message']];

        // Fast local fallback for common everyday questions.
        // Answering without an API call helps when the user is offline
        // or just needs a quick factual answer (balance, due date, etc.).
        $faq = $kb->searchFaq($validated['message']);
        if ($faq !== null) {
            $userMessage = strtolower(trim($validated['message']));

            if (str_contains($userMessage, 'outstanding balance') ||
                str_contains($userMessage, 'balance') ||
                str_contains($userMessage, 'ow') ||
                str_contains($userMessage, 'nagkano') ||
                str_contains($userMessage, 'amount due') ||
                str_contains($userMessage, 'how much')) {
                if ($subscriber && $subscriber->relationLoaded('invoices')) {
                    $outstanding = $subscriber->invoices
                        ->whereNotIn('effective_status', ['paid', 'cancelled'])
                        ->sum('amount_due');
                    $reply = 'Your outstanding balance is **₱'.number_format($outstanding, 2).'** '.
                        '('.$subscriber->invoices->whereNotIn('effective_status', ['paid', 'cancelled'])->count().
                        ' unpaid invoice(s)). '.$faq['answer'];

                    return response()->json(['success' => true, 'reply' => $reply, 'from_cache' => true]);
                }
            }

            return response()->json(['success' => true, 'reply' => $faq['answer'], 'from_cache' => true]);
        }

        try {
            $reply = app(GroqService::class)->chat($messages);
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'The AI assistant is temporarily unavailable. Please try again in a moment.',
            ]);
        }

        return response()->json([
            'success' => true,
            'reply' => $reply,
        ]);
    }

    /*
     Create a support ticket from the AI chatbot escalation flow.
     *
     * Runs inside a transaction so the ticket row and its activity log are
     * written atomically. Admin + technical staff are notified best-effort
     * after the commit — a mail failure must never roll back a ticket.
     */
    public function storeTicket(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'category' => ['required', 'string', 'in:no_connection,slow_connection,billing_concern,installation_request,equipment_issue,other'],
            'subject' => ['required', 'string', 'max:150'],
            'description' => ['required', 'string'],
        ]);

        $user = $request->user();
        $subscriber = $user->subscriber;

        abort_unless($subscriber, 422, 'No subscriber record is linked to this account.');

        $priority = match ($validated['category']) {
            'no_connection' => 'urgent',
            'slow_connection' => 'high',
            default => 'medium',
        };

        $ticket = DB::transaction(function () use ($validated, $subscriber, $user, $priority) {
            $description = $validated['description'];

            if ($subscriber && $subscriber->service_address) {
                $description .= "\n\n--- Service Address ---\n".$subscriber->service_address;
            }

            $ticket = Ticket::create([
                'ticket_number' => Ticket::nextNumber(),
                'subscriber_id' => $subscriber->id,
                'category' => $validated['category'],
                'subject' => $validated['subject'],
                'description' => $description,
                'priority' => $priority,
                'status' => 'open',
                'created_by' => $user->id,
            ]);

            ActivityLog::record(
                'ticket.created',
                $ticket,
                $user->name.' logged this ticket through the AI assistant.'
            );

            return $ticket;
        });

        // Best-effort notification to admins and technical staff.
        try {
            $recipients = User::role(['admin', 'technical_staff'])->get();
            Notification::send($recipients, new NewTicketNotification($ticket));
        } catch (\Throwable) {
            // Notifications are fire-and-forget; never fail the request.
        }

        return response()->json([
            'success' => true,
            'ticket' => [
                'id' => $ticket->id,
                'ticket_number' => $ticket->ticket_number,
                'status' => $ticket->status,
                'priority' => $ticket->priority,
                'category' => $ticket->category,
                'subject' => $ticket->subject,
                'reported_at' => $ticket->created_at->format('M j, Y g:i A'),
            ],
        ]);
    }
}
