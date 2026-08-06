<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\StaffSchedule;
use App\Models\Ticket;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $openCount = Cache::remember('staff.dashboard.open_count', 120, function () {
            return Ticket::where('status', 'open')->count();
        });

        $inProgressCount = Cache::remember('staff.dashboard.in_progress_count', 120, function () {
            return Ticket::where('status', 'in_progress')->count();
        });

        $pendingCount = Cache::remember('staff.dashboard.assigned_count', 120, function () {
            return Ticket::where('status', 'assigned')->count();
        });

        $resolvedTodayCount = Cache::remember('staff.dashboard.resolved_today_count', 120, function () {
            return Ticket::where('status', 'resolved')
                ->whereDate('resolved_at', now()->toDateString())
                ->count();
        });

        $recentTickets = Ticket::with(['subscriber.user', 'assignee.user'])
            ->latest()
            ->limit(10)
            ->get();

        $announcements = Announcement::orderByDesc('announcement_date')
            ->limit(5)
            ->get();

        $user = $request->user()->load('technicalStaff');
        $technicalStaff = $user->technicalStaff;

        $scheduleDays = collect();
        $weekStart = now()->startOfWeek(Carbon::MONDAY);

        for ($i = 0; $i < 7; $i++) {
            $day = $weekStart->copy()->addDays($i);
            $dayOfWeek = (int) $day->dayOfWeek;

            $schedule = StaffSchedule::where('day_of_week', $dayOfWeek)
                ->where(function ($query) use ($technicalStaff) {
                    $query->where('technical_staff_id', $technicalStaff?->id)
                        ->orWhere('technical_staff_id', null);
                })
                ->first();

            $scheduleDays[] = (object) [
                'date' => $day,
                'shift_type' => $schedule?->shift_type ?? 'day',
                'start_time' => $schedule?->start_time ? Carbon::parse($schedule->start_time)->format('g:i A') : '8:00 AM',
                'end_time' => $schedule?->end_time ? Carbon::parse($schedule->end_time)->format('g:i A') : '5:00 PM',
            ];
        }

        return view('staff.dashboard', [
            'user' => $user,
            'openCount' => $openCount,
            'inProgressCount' => $inProgressCount,
            'pendingCount' => $pendingCount,
            'resolvedTodayCount' => $resolvedTodayCount,
            'recentTickets' => $recentTickets,
            'announcements' => $announcements,
            'scheduleDays' => $scheduleDays,
        ]);
    }

    /**
     * Show the staff member's account details.
     */
    public function myAccount(Request $request): View
    {
        $user = $request->user()->load('technicalStaff');
        $technicalStaff = $user->technicalStaff;

        $scheduleDays = collect();
        $weekStart = now()->startOfWeek(Carbon::MONDAY);

        for ($i = 0; $i < 7; $i++) {
            $day = $weekStart->copy()->addDays($i);
            $dayOfWeek = (int) $day->dayOfWeek;

            $schedule = StaffSchedule::where('day_of_week', $dayOfWeek)
                ->where(function ($query) use ($technicalStaff) {
                    $query->where('technical_staff_id', $technicalStaff?->id)
                        ->orWhere('technical_staff_id', null);
                })
                ->first();

            $scheduleDays[] = (object) [
                'date' => $day,
                'shift_type' => $schedule?->shift_type ?? 'day',
                'start_time' => $schedule?->start_time ? Carbon::parse($schedule->start_time)->format('g:i A') : '8:00 AM',
                'end_time' => $schedule?->end_time ? Carbon::parse($schedule->end_time)->format('g:i A') : '5:00 PM',
            ];
        }

        return view('staff.my-account', [
            'user' => $user,
            'technicalStaff' => $technicalStaff,
            'scheduleDays' => $scheduleDays,
        ]);
    }
}
