<?php

namespace App\Providers;

use App\Services\GroqService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /*
     * Register any application services.
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
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
