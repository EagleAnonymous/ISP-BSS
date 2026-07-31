<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreSubscriberRequest;
use App\Models\Plan;
use App\Models\Subscriber;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class SubscriberController extends Controller
{
    /**
     * Show every subscriber in a table, newest first.
     */
    public function index(): View
    {
        $subscribers = Subscriber::with(['user', 'plan'])->latest()->get();

        return view('admin.subscribers.index', ['subscribers' => $subscribers]);
    }

    /**
     * Show the "add a subscriber" form. Only active plans are offered.
     */
    public function create(): View
    {
        $plans = Plan::where('is_active', true)->orderBy('name')->get();

        return view('admin.subscribers.create', ['plans' => $plans]);
    }

    /**
     * Create a subscriber's login, profile, and starting plan together.
     *
     * This creates three linked records in one go: a `User` (for logging in),
     * a `Subscriber` (their profile), and a `Subscription` (which plan they're
     * on, with today's price locked in so future price changes don't affect
     * them). All three are wrapped in one transaction so a failure partway
     * through leaves nothing behind — either all three are created, or none are.
     */
    public function store(StoreSubscriberRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        DB::transaction(function () use ($validated) {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
            ]);

            $user->assignRole('subscriber');

            $subscriber = Subscriber::create([
                'user_id' => $user->id,
                'subscriber_id' => $this->nextSubscriberId(),
                'contact' => $validated['contact'] ?? null,
                'plan_id' => $validated['plan_id'],
                'status' => $validated['status'],
                'joined_date' => $validated['joined_date'],
            ]);

            $plan = Plan::findOrFail($validated['plan_id']);

            Subscription::create([
                'subscriber_id' => $subscriber->id,
                'plan_id' => $plan->id,
                'locked_price' => $plan->price,
                'status' => 'active',
                'starts_at' => $validated['joined_date'],
            ]);
        });

        return redirect()
            ->route('admin.subscribers.index')
            ->with('status', 'subscriber-created');
    }

    /**
     * Work out the next subscriber ID, e.g. "SUB-00042".
     *
     * `lockForUpdate()` briefly locks the subscribers table while we count,
     * so that if two admins submit "Add Subscriber" at the exact same
     * moment, the second one waits its turn instead of getting the same
     * number as the first (which would fail with a database error).
     */
    private function nextSubscriberId(): string
    {
        $next = Subscriber::lockForUpdate()->count() + 1;

        return 'SUB-'.str_pad((string) $next, 5, '0', STR_PAD_LEFT);
    }
}
