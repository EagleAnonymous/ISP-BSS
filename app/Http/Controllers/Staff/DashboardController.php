<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $staff = $request->user()->technicalStaff;

        return view('staff.dashboard', [
            'user' => $request->user(),
            'openQueueCount' => Ticket::where('status', 'open')->count(),
            'myTicketsCount' => Ticket::where('assigned_to', $staff->id)
                ->whereIn('status', ['assigned', 'in_progress'])
                ->count(),
        ]);
    }
}
