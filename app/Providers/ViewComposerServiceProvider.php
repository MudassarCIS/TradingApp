<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use App\Models\UserInvoice;
use App\Models\Deposit;
use App\Models\Withdrawal;
use App\Models\SupportMessage;

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
        // Share unpaid invoice count and unread support messages with customer layout
        View::composer('layouts.customer-layout', function ($view) {
            if (Auth::check() && Auth::user()->isCustomer()) {
                $unpaidCount = Auth::user()->invoices()->where('status', 'Unpaid')->count();
                $view->with('unpaidCount', $unpaidCount);
                
                // Get unread support messages count
                $threadId = SupportMessage::generateThreadId(Auth::id());
                $unreadSupportCount = SupportMessage::where('thread_id', $threadId)
                    ->where('sender_type', 'admin')
                    ->where('is_read_by_customer', false)
                    ->count();
                $view->with('unreadSupportCount', $unreadSupportCount);
            }
        });
        
        // Share pending deposits count, pending withdrawals count, and unread support messages with admin layout
        View::composer('layouts.admin-includes.leftmenu', function ($view) {
            $pendingDepositsCount = Deposit::where('status', 'pending')->count();
            $pendingWithdrawalsCount = Withdrawal::where('status', 'pending')->count();
            $view->with('pendingDepositsCount', $pendingDepositsCount);
            $view->with('pendingWithdrawalsCount', $pendingWithdrawalsCount);
            
            // Get unread support messages count (messages from customers not read by admin)
            $unreadSupportCount = SupportMessage::where('sender_type', 'customer')
                ->where('is_read_by_admin', false)
                ->count();
            $view->with('unreadSupportCount', $unreadSupportCount);
        });
    }
}