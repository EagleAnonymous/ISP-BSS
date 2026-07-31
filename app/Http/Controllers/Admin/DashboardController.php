<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\BillingSummary;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request, BillingSummary $summary): View
    {
        return view('admin.dashboard', [
            'user' => $request->user(),
            'summary' => $summary->currentPeriod(),
        ]);
    }
}
