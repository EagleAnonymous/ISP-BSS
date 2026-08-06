<?php

namespace App\Providers;

use App\Models\Subscriber;
use App\Models\TechnicalStaff;
use App\Services\GroqService;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /*
     * Register app services.
     */
    public function register(): void
    {
        $this->app->bind(GroqService::class, function () {
            return new GroqService(
                config('services.groq.key'),
                config('services.groq.model'),
                config('services.groq.base_url'),
            );
        });
    }

    /*
         * Bootstrap app services.
         */
    public function boot(): void
    {
        // Provide the staff & admin layouts with a notification badge count.
        // We surface the number of unread in-app notifications for the logged
        // in user, falling back to open tickets in the shared queue when the
        // user has no notifications. Cached briefly to avoid hitting the DB
        // on every page load.
        View::composer(['layouts.staff', 'layouts.admin'], function ($view) {
            $user = $view->getData()['user'] ?? auth()->user();

            if ($user instanceof TechnicalStaff || $user instanceof Subscriber) {
                $user = $user->user;
            }

            $notificationCount = 0;

            if ($user) {
                $notificationCount = (int) $user->unreadNotifications()->count();
            }

            $notifications = $user ? $user->notifications()->latest()->limit(10)->get() : collect();

            $view->with('notificationCount', $notificationCount);
            $view->with('notifications', $notifications);
        });
    }
}
