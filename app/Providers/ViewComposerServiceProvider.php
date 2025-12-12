<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use App\Models\UserInvoice;
use App\Models\Deposit;
use App\Models\Withdrawal;

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
        
        // Share pending deposits count and pending withdrawals count with admin layout
        View::composer('layouts.admin-includes.leftmenu', function ($view) {
            $pendingDepositsCount = Deposit::where('status', 'pending')->count();
            $pendingWithdrawalsCount = Withdrawal::where('status', 'pending')->count();
            $view->with('pendingDepositsCount', $pendingDepositsCount);
            $view->with('pendingWithdrawalsCount', $pendingWithdrawalsCount);
        });
    }
}