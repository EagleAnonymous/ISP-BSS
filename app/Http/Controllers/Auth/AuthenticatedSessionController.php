<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        /** @var User $user */
        $user = $request->user();

        return match (true) {
            $user->hasRole('admin') => redirect()->route('admin.dashboard'),
            $user->hasRole('technical_staff') => redirect()->route('staff.dashboard'),
            $user->hasRole('subscriber') => redirect()->route('subscriber.dashboard'),
            default => $this->rejectUnassignedRole($request),
        };
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }

    /**
     * Reject a successfully-authenticated user who has no role assigned.
     *
     * The session is torn down here (not just redirected away from) so the
     * next request isn't bounced off the `guest`-only /login route by
     * RedirectIfAuthenticated. Redirecting explicitly to the named route
     * rather than back() matters too: invalidate() wipes the session's
     * "previous URL", so back() would land on '/' instead of the login form.
     */
    private function rejectUnassignedRole(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->withErrors([
            'email' => __('Your account has no assigned role — contact an administrator.'),
        ]);
    }
}
