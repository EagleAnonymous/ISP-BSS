<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePlanRequest;
use App\Models\Plan;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PlanController extends Controller
{
    public function index(): View
    {
        $plans = Plan::withCount(['subscribers as active_subscribers_count' => function ($query) {
            $query->where('status', 'active');
        }])->latest()->get();

        return view('admin.plans.index', ['plans' => $plans]);
    }

    public function create(): View
    {
        return view('admin.plans.create');
    }

    public function store(StorePlanRequest $request): RedirectResponse
    {
        Plan::create($request->validated());

        return redirect()
            ->route('admin.plans.index')
            ->with('status', 'plan-created');
    }

    /**
     * A plan can only be deleted once nothing depends on it — a subscription
     * pointing to a deleted plan would have no price/speed to look up, so
     * the database itself refuses this if any subscription still
     * references the plan. Check that up front for a clear message
     * instead of letting a database error surface.
     */
    public function destroy(Plan $plan): RedirectResponse
    {
        if ($plan->subscriptions()->exists()) {
            return redirect()
                ->route('admin.plans.index')
                ->with('status', 'plan-has-subscribers');
        }

        $plan->delete();

        return redirect()
            ->route('admin.plans.index')
            ->with('status', 'plan-deleted');
    }
}
