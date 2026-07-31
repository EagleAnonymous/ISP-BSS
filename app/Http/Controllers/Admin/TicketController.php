<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTicketRequest;
use App\Models\ActivityLog;
use App\Models\Subscriber;
use App\Models\Ticket;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class TicketController extends Controller
{
    /**
     * Show every ticket system-wide, with optional filters.
     */
    public function index(Request $request): View
    {
        $tickets = $this->filteredTickets($request)->paginate(15)->withQueryString();

        return view('admin.tickets.index', [
            'tickets' => $tickets,
            'filters' => $request->only(['status', 'priority', 'search']),
        ]);
    }

    /**
     * Show the "log a ticket" form. The admin picks which subscriber the
     * problem is about from a dropdown, same idea as picking a plan when
     * adding a subscriber.
     */
    public function create(): View
    {
        $subscribers = Subscriber::with('user')->get()->sortBy(fn ($s) => $s->user->name);

        return view('admin.tickets.create', ['subscribers' => $subscribers]);
    }

    /**
     * Log a new ticket. It starts unassigned ("open") in the shared queue —
     * any technical staff member can claim it from their side.
     */
    public function store(StoreTicketRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $ticket = DB::transaction(function () use ($validated) {
            $ticket = Ticket::create([
                ...$validated,
                'ticket_number' => Ticket::nextNumber(),
                'status' => 'open',
                'created_by' => Auth::id(),
            ]);

            ActivityLog::record('ticket.created', $ticket, Auth::user()->name.' logged this ticket.');

            return $ticket;
        });

        return redirect()->route('admin.tickets.show', $ticket)->with('status', 'ticket-created');
    }

    /**
     * Show one ticket's full details and its history of status changes.
     */
    public function show(Ticket $ticket): View
    {
        $ticket->load(['subscriber.user', 'assignee.user', 'creator']);

        $activity = ActivityLog::where('subject_type', Ticket::class)
            ->where('subject_id', $ticket->id)
            ->latest()
            ->get();

        return view('admin.tickets.show', ['ticket' => $ticket, 'activity' => $activity]);
    }

    /**
     * Give the final admin sign-off after a technical staff member has
     * marked the ticket resolved.
     */
    public function close(Ticket $ticket): RedirectResponse
    {
        abort_if($ticket->status !== 'resolved', 422, 'Only a resolved ticket can be closed.');

        DB::transaction(function () use ($ticket) {
            $ticket->update(['status' => 'closed', 'closed_at' => now()]);

            ActivityLog::record('ticket.closed', $ticket, Auth::user()->name.' closed this ticket.');
        });

        return redirect()->route('admin.tickets.show', $ticket)->with('status', 'ticket-closed');
    }

    private function filteredTickets(Request $request): Builder
    {
        $query = Ticket::query()->with(['subscriber.user', 'assignee.user'])->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->string('priority'));
        }

        if ($request->filled('search')) {
            $search = $request->string('search');

            $query->where(function ($q) use ($search) {
                $q->where('ticket_number', 'like', "%{$search}%")
                    ->orWhereHas('subscriber', function ($s) use ($search) {
                        $s->where('subscriber_id', 'like', "%{$search}%")
                            ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$search}%"));
                    });
            });
        }

        return $query;
    }
}
