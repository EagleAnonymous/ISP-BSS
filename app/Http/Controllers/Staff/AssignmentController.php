<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class AssignmentController extends Controller
{
    public function index(Request $request): View
    {
        $staff = $request->user()->technicalStaff;

        if (! $staff) {
            throw new NotFoundHttpException('No staff profile found for this user.');
        }

        $assignments = Assignment::with(['ticket.subscriber.user'])
            ->where('staff_id', $staff->id)
            ->where('status', 'active')
            ->latest('assigned_at')
            ->paginate(15);

        return view('staff.assignments', [
            'assignments' => $assignments,
        ]);
    }
}
