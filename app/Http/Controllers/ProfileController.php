<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        $user = $request->user();
        $user->load(['subscriber.plan', 'technicalStaff']);

        return view('profile.edit', [
            'user' => $user,
            'subscriber' => $user->subscriber,
            'technicalStaff' => $user->technicalStaff,
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());
        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Persist a single editable account field.
     *
     * Handles name, email, phone, and avatar updates for all roles.
     * Kept in sync with the subscriber "My Account" panel so the
     * profile page and account page always share the same stored credentials.
     */
    public function updateField(Request $request): JsonResponse
    {
        $user = $request->user();
        $subscriber = $user->subscriber;
        $technicalStaff = $user->technicalStaff;

        $field = $request->input('field', 'name');

        // System-managed fields are strictly read-only. They are owned by the
        // admin team (via the TechnicalStaffController) and must never be
        // mutated through the self-service profile panel.
        if (in_array($field, ['employee_id', 'role', 'department', 'supervisor'], true)) {
            return response()->json([
                'success' => false,
                'message' => 'This field is read-only and managed by administrators.',
            ], 422);
        }

        if ($field === 'name') {
            $validated = $request->validate([
                'name' => ['required', 'string', 'max:255'],
            ]);

            $user->name = $validated['name'];
            $user->save();
        } elseif ($field === 'email') {
            $validated = $request->validate([
                'email' => [
                    'required',
                    'string',
                    'lowercase',
                    'email',
                    'max:255',
                    Rule::unique('users')->ignore($user->id),
                ],
            ]);

            $user->email = $validated['email'];
            $user->save();
        } elseif ($field === 'phone') {
            $validated = $request->validate([
                'phone' => ['nullable', 'string', 'max:30'],
            ]);

            if ($subscriber) {
                $subscriber->contact = $validated['phone'];
                $subscriber->save();
            } elseif ($technicalStaff) {
                $technicalStaff->phone = $validated['phone'];
                $technicalStaff->save();
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'No subscriber or staff record found for this user.',
                ], 422);
            }
        } elseif ($field === 'service_address' && $subscriber) {
            $validated = $request->validate([
                'service_address' => ['nullable', 'string', 'max:1000'],
            ]);

            $subscriber->service_address = $validated['service_address'];
            $subscriber->save();
        } elseif ($field === 'location' && $technicalStaff) {
            $validated = $request->validate([
                'location' => ['nullable', 'string', 'max:255'],
            ]);

            $technicalStaff->location = $validated['location'];
            $technicalStaff->save();
        } elseif ($field === 'avatar' && $request->hasFile('avatar')) {
            // Only administrators may update profile pictures.
            if (! $user->hasRole('admin')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only administrators can update profile pictures.',
                ], 403);
            }

            $request->validate([
                'avatar' => ['required', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            ]);

            if ($user->avatar_path) {
                Storage::disk('public')->delete($user->avatar_path);
            }

            $path = $request->file('avatar')->store('avatars', 'public');
            $user->avatar_path = $path;
            $user->save();

            return response()->json([
                'success' => true,
                'avatar_url' => asset('storage/'.$path),
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Unsupported profile field: '.$field,
            ], 422);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Upload and persist the user's profile avatar for any role.
     *
     * Stores the image to the public `avatars` disk, cleans up the previous
     * avatar file to avoid orphaned storage, and returns a JSON payload with
     * the new public URL so the frontend can swap the image without a reload.
     */
    public function updateAvatar(Request $request): JsonResponse
    {
        // Only administrators may update profile pictures.
        if (! $request->user()->hasRole('admin')) {
            return response()->json([
                'success' => false,
                'message' => 'Only administrators can update profile pictures.',
            ], 403);
        }

        $request->validate([
            'avatar' => ['required', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
        ]);

        $user = $request->user();

        // Delete the old avatar if one exists.
        if ($user->avatar_path) {
            Storage::disk('public')->delete($user->avatar_path);
        }

        // Store the new avatar and keep its path.
        $path = $request->file('avatar')->store('avatars', 'public');
        $user->avatar_path = $path;
        $user->save();

        return response()->json([
            'success' => true,
            'avatar_url' => asset('storage/'.$path),
        ]);
    }

    /**
     * Mark notifications as read for the current user.
     */
    public function markNotificationsRead(Request $request): JsonResponse
    {
        $user = $request->user();
        $notificationId = $request->input('notification_id');

        if ($notificationId) {
            $notification = $user->notifications()->where('id', $notificationId)->first();
            if ($notification) {
                $notification->markAsRead();
            }
        } else {
            $user->unreadNotifications->each->markAsRead();
        }

        return response()->json(['success' => true]);
    }

    /**
     * Check for new notifications (used for real-time polling).
     */
    public function checkNewNotifications(Request $request): JsonResponse
    {
        $user = $request->user();
        $unreadCount = $user ? (int) $user->unreadNotifications()->count() : 0;
        $notifications = $user ? $user->notifications()->latest()->limit(5)->get() : collect();

        return response()->json([
            'count' => $unreadCount,
            'notifications' => $notifications->map(function ($notification) {
                return [
                    'id' => $notification->id,
                    'title' => $notification->data['title'] ?? 'Notification',
                    'message' => $notification->data['message'] ?? '',
                    'url' => $notification->data['url'] ?? '#',
                    'ticket_id' => $notification->data['ticket_id'] ?? null,
                    'created_at' => $notification->created_at->diffForHumans(),
                ];
            }),
        ]);
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        // There is no self-registration page, so if this were the only
        // admin account, deleting it would lock everyone out of /admin
        // with no way back in. Block that specific case.
        if ($user->hasRole('admin') && User::role('admin')->count() <= 1) {
            return Redirect::route('profile.edit')->withErrors([
                'password' => 'You are the only administrator, so this account cannot be deleted.',
            ], 'userDeletion');
        }

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
