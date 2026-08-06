<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StaffSchedule;
use App\Models\TechnicalStaff;
use Illuminate\Http\Request;

class StaffScheduleController extends Controller
{
    public function index(Request $request)
    {
        $schedules = StaffSchedule::orderBy('day_of_week')->get()->groupBy('day_of_week');
        $staff = TechnicalStaff::with('user')->get();

        return view('admin.staff.schedules', [
            'schedules' => $schedules,
            'staff' => $staff,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'day_of_week' => ['required', 'integer', 'min:0', 'max:6'],
            'shift_type' => ['required', 'in:day,night'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i'],
            'technical_staff_id' => ['nullable', 'exists:technical_staff,id'],
        ]);

        $schedule = StaffSchedule::updateOrCreate(
            [
                'technical_staff_id' => $validated['technical_staff_id'],
                'day_of_week' => $validated['day_of_week'],
            ],
            [
                'shift_type' => $validated['shift_type'],
                'start_time' => $validated['start_time'],
                'end_time' => $validated['end_time'],
            ]
        );

        return back()->with('status', 'Schedule updated successfully.');
    }
}
