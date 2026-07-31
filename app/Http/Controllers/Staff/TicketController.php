<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Http\Requests\ResolveTicketRequest;
use App\Http\Requests\StoreTicketRequest;
use App\Models\ActivityLog;
use App\Models\Subscriber;
use App\Models\Ticket;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class TicketController extends Controller
{
    /**
     * Two tabs: the shared "Open Queue" (unclaimed tickets anyone can grab)
     * and "My Tickets" (already claimed by the logged-in staff member).
     */
    public function index(Request $request): View
    {
        $tab = $request->string('tab', 'queue')->value();
        $staff = Auth::user()->technicalStaff;

        $tickets = $tab === 'mine'
            ? Ticket::with(['subscriber.user'])->where('assigned_to', $staff->id)->latest()->paginate(15)
            : Ticket::with(['subscriber.user'])->where('status', 'open')->latest()->paginate(15);

        return view('staff.tickets.index', ['tickets' => $tickets, 'tab' => $tab]);
    }

    /**
     * Staff can also log a ticket directly (e.g. spotting a problem in the
     * field), same form as the admin side.
     */
    public function create(): View
    {
        $subscribers = Subscriber::with('user')->get()->sortBy(fn ($s) => $s->user->name);

        return view('staff.tickets.create', ['subscribers' => $subscribers]);
    }

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

        return redirect()->route('staff.tickets.show', $ticket)->with('status', 'ticket-created');
    }

    public function show(Ticket $ticket): View
    {
        $ticket->load(['subscriber.user', 'assignee.user', 'creator']);

        $activity = ActivityLog::where('subject_type', Ticket::class)
            ->where('subject_id', $ticket->id)
            ->latest()
            ->get();

        return view('staff.tickets.show', ['ticket' => $ticket, 'activity' => $activity]);
    }

    /**
     * Claim an open ticket from the shared queue (self-assign).
     *
     * The row is locked for the duration of this check-then-update so that
     * if two staff members tap "Claim" on the same ticket at the same
     * moment, the second one is safely told it's already claimed instead
     * of both being assigned to it (same pattern as
     * BillingController::markPaid()).
     */
    public function claim(Ticket $ticket): RedirectResponse
    {
        $staff = Auth::user()->technicalStaff;

        DB::transaction(function () use ($ticket, $staff) {
            $locked = Ticket::where('id', $ticket->id)->lockForUpdate()->firstOrFail();

            abort_if($locked->status !== 'open', 422, 'This ticket has already been claimed.');

            $locked->update([
                'status' => 'assigned',
                'assigned_to' => $staff->id,
                'claimed_at' => now(),
            ]);

            ActivityLog::record('ticket.claimed', $locked, Auth::user()->name.' claimed this ticket.');
        });

        return redirect()->route('staff.tickets.show', $ticket)->with('status', 'ticket-claimed');
    }

    /**
     * Start work on a ticket you've claimed. Only the assigned staff
     * member can do this — someone else's claim can't be started by you.
     */
    public function start(Ticket $ticket): RedirectResponse
    {
        $staff = Auth::user()->technicalStaff;

        abort_if($ticket->assigned_to !== $staff->id, 403, 'Only the staff member who claimed this ticket can start it.');
        abort_if($ticket->status !== 'assigned', 422, 'Only an assigned ticket can be started.');

        DB::transaction(function () use ($ticket) {
            $ticket->update(['status' => 'in_progress', 'started_at' => now()]);

            ActivityLog::record('ticket.started', $ticket, Auth::user()->name.' started working on this ticket.');
        });

        return redirect()->route('staff.tickets.show', $ticket)->with('status', 'ticket-started');
    }

    /**
     * Mark a ticket resolved with notes on what was done. Admin gives the
     * final "Closed" sign-off separately.
     */
    public function resolve(ResolveTicketRequest $request, Ticket $ticket): RedirectResponse
    {
        $staff = Auth::user()->technicalStaff;

        abort_if($ticket->assigned_to !== $staff->id, 403, 'Only the staff member who claimed this ticket can resolve it.');
        abort_if($ticket->status !== 'in_progress', 422, 'Only a ticket that is in progress can be resolved.');

        $validated = $request->validated();

        DB::transaction(function () use ($ticket, $validated) {
            $ticket->update([
                'status' => 'resolved',
                'resolved_at' => now(),
                'resolution_notes' => $validated['resolution_notes'],
            ]);

            ActivityLog::record('ticket.resolved', $ticket, Auth::user()->name.' marked this ticket resolved.');
        });

        return redirect()->route('staff.tickets.show', $ticket)->with('status', 'ticket-resolved');
    }

}
