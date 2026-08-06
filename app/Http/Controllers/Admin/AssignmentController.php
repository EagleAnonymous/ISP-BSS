<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\TechnicalStaff;
use App\Models\Ticket;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AssignmentController extends Controller
{
    public function index(Request $request): View
    {
        $staff = TechnicalStaff::with('user')->get()->sortBy(fn ($s) => $s->user->name);

        $query = Ticket::with(['subscriber.user', 'assignments.staff.user'])
            ->whereHas('assignments', function ($q) {
                $q->where('status', 'active');
            })
            ->latest();

        if ($request->filled('staff_id')) {
            $query->whereHas('assignments', function ($q) use ($request) {
                $q->where('staff_id', $request->string('staff_id'))
                    ->where('status', 'active');
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        if ($request->filled('search')) {
            $search = $request->string('search');
            $query->where(function ($q) use ($search) {
                $q->where('ticket_number', 'like', "%{$search}%")
                    ->orWhere('subject', 'like', "%{$search}%")
                    ->orWhereHas('subscriber', function ($s) use ($search) {
                        $s->where('subscriber_id', 'like', "%{$search}%")
                            ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$search}%"));
                    });
            });
        }

        $assignments = $query->paginate(15)->withQueryString();

        return view('admin.assignments.index', [
            'assignments' => $assignments,
            'staff' => $staff,
            'filters' => $request->only(['staff_id', 'status', 'search']),
        ]);
    }

    public function create(Request $request): View
    {
        $ticketId = $request->query('ticket_id');
        $ticket = $ticketId ? Ticket::with('subscriber.user')->findOrFail($ticketId) : null;
        $staff = TechnicalStaff::with('user')->get()->sortBy(fn ($s) => $s->user->name);

        $unassignedTickets = Ticket::with('subscriber.user')
            ->where(function ($q) {
                $q->where('status', 'open')
                    ->orWhereHas('assignments', function ($a) {
                        $a->where('status', 'active');
                    });
            })
            ->latest()
            ->get();

        return view('admin.assignments.create', [
            'ticket' => $ticket,
            'staff' => $staff,
            'unassignedTickets' => $unassignedTickets,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'ticket_id' => 'required|exists:tickets,id',
            'staff_id' => 'required|exists:technical_staff,id',
            'notes' => 'nullable|string|max:2000',
        ]);

        $ticket = Ticket::findOrFail($validated['ticket_id']);

        Assignment::create([
            'ticket_id' => $validated['ticket_id'],
            'staff_id' => $validated['staff_id'],
            'assigned_by' => Auth::id(),
            'notes' => $validated['notes'],
            'status' => 'active',
            'assigned_at' => now(),
        ]);

        $ticket->update([
            'assigned_to' => $validated['staff_id'],
            'status' => 'assigned',
            'claimed_at' => now(),
        ]);

        return redirect()->route('admin.assignments.index')->with('status', 'assignment-created');
    }

    public function complete(Assignment $assignment): RedirectResponse
    {
        $assignment->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        return redirect()->route('admin.assignments.index')->with('status', 'assignment-completed');
    }

    public function destroy(Assignment $assignment): RedirectResponse
    {
        $ticket = $assignment->ticket;

        $assignment->update(['status' => 'cancelled']);

        $activeAssignments = Assignment::where('ticket_id', $ticket->id)
            ->where('status', 'active')
            ->count();

        if ($activeAssignments === 0) {
            $ticket->update([
                'assigned_to' => null,
                'status' => 'open',
                'claimed_at' => null,
            ]);
        }

        return redirect()->route('admin.assignments.index')->with('status', 'assignment-cancelled');
    }
}
