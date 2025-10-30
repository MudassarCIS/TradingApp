<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use App\Models\UserInvoice;

class ViewComposerServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Share unpaid invoice count with customer layout
        View::composer('layouts.customer-layout', function ($view) {
            if (Auth::check() && Auth::user()->isCustomer()) {
                $unpaidCount = Auth::user()->invoices()->where('status', 'Unpaid')->count();
                $view->with('unpaidCount', $unpaidCount);
            }
        });
    }
}