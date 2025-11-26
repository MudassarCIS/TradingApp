<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Set timezone from settings if available
        try {
            $setting = \App\Models\Setting::first();
            if ($setting && $setting->timezone) {
                config(['app.timezone' => $setting->timezone]);
                date_default_timezone_set($setting->timezone);
            } else {
                // Default to UAE if no setting found
                config(['app.timezone' => 'Asia/Dubai']);
                date_default_timezone_set('Asia/Dubai');
            }
        } catch (\Exception $e) {
            // Fallback to default if settings table doesn't exist yet
            config(['app.timezone' => 'Asia/Dubai']);
            date_default_timezone_set('Asia/Dubai');
        }
    }
}
