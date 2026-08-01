<?php

namespace App\Http\Controllers\Subscriber;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Ticket;
use App\Notifications\NewTicketNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
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
        $subscriber?->load(['plan', 'invoices.adjustments']);

        $invoices = $subscriber?->invoices()->latest()->limit(5)->get() ?? collect();

        $outstandingBalance = $subscriber?->invoices
            ->filter(fn ($invoice) => $invoice->effective_status !== 'paid')
            ->sum('amount_due') ?? 0;

        $unpaidCount = $subscriber?->invoices
            ->filter(fn ($invoice) => $invoice->effective_status !== 'paid')
            ->count() ?? 0;

        $openTicketCount = $subscriber?->tickets()
            ->whereNotIn('status', ['resolved', 'closed'])
            ->count() ?? 0;

        return view('subscriber.dashboard', [
            'user' => $user,
            'subscriber' => $subscriber,
            'plan' => $subscriber?->plan,
            'invoices' => $invoices,
            'outstandingBalance' => $outstandingBalance,
            'unpaidCount' => $unpaidCount,
            'openTicketCount' => $openTicketCount,
        ]);
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

    /**
     * Show the subscriber's billing and invoices.
     */
    public function billing(Request $request): View
    {
        $user = $request->user();
        $subscriber = $user->subscriber;
        $subscriber?->load(['invoices.adjustments', 'payments']);

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

        return view('subscriber.billing', [
            'user' => $user,
            'subscriber' => $subscriber,
            'invoices' => $invoices,
            'outstandingBalance' => $outstandingBalance,
            'unpaidCount' => $unpaidCount,
            'paidThisMonth' => $paidThisMonth,
        ]);
    }

    /*
      Show the subscriber's AI chatbot panel.
     */
    public function chatbot(Request $request): View
    {
        $user = $request->user();
        $subscriber = $user->subscriber;

        return view('subscriber.chatbot', [
            'user' => $user,
            'subscriber' => $subscriber,
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

        $system = 'You are the Smart ISP AI Customer Support Assistant. '
            .'Answer in a friendly, concise, helpful tone. Keep replies short '
            .'(a few sentences unless a step-by-step is clearly needed). '
            .'You may use plain text bullet lists. '
            .'You can only help with topics related to this ISP: internet '
            .'troubleshooting, plans, billing, installation, and equipment. '
            .'If the subscriber needs a technician or a formal record, tell '
            .'them they can ask the assistant to create a support ticket.';

        if ($subscriber) {
            $subscriber->load('plan');

            $system .= "\n\nSubscriber context:\n"
                .'- Name: '.$user->name."\n"
                .'- Account number: '.($subscriber->subscriber_id ?? 'N/A')."\n"
                .'- Contact: '.($subscriber->contact ?? 'Not provided')."\n"
                .'- Plan: '.($subscriber->plan?->name ?? 'N/A')
                .($subscriber->plan?->speed ? ' ('.$subscriber->plan->speed.')' : '');
        }

        $messages = [
            ['role' => 'system', 'content' => $system],
        ];

        // Include the recent back-and-forth so the reply is contextual.
        foreach (array_slice($validated['history'] ?? [], -12) as $turn) {
            $messages[] = ['role' => $turn['role'], 'content' => $turn['content']];
        }

        $messages[] = ['role' => 'user', 'content' => $validated['message']];

        try {
            $reply = app(\App\Services\GroqService::class)->chat($messages);
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'The AI assistant is temporarily unavailable. Please try again in a moment.',
            ], 502);
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
            $ticket = Ticket::create([
                'ticket_number' => Ticket::nextNumber(),
                'subscriber_id' => $subscriber->id,
                'category' => $validated['category'],
                'subject' => $validated['subject'],
                'description' => $validated['description'],
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
            $recipients = \App\Models\User::role(['admin', 'technical_staff'])->get();
            \Illuminate\Support\Facades\Notification::send($recipients, new NewTicketNotification($ticket));
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

