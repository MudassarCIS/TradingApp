<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Customer\DashboardController;
use App\Http\Controllers\Customer\TradingController;
use App\Http\Controllers\Customer\MarketController;
use App\Http\Controllers\Customer\WalletController;
use App\Http\Controllers\Customer\AgentController;
use App\Http\Controllers\Customer\ProfileController as CustomerProfileController;
use App\Http\Controllers\Customer\ReferralController;
use App\Http\Controllers\Customer\SupportController;
use Illuminate\Support\Facades\Artisan;
// Route to clear all application cache
Route::get('/cache-clear', function() {
    Artisan::call('optimize:clear');
    return 'Application cache cleared!';
});

// Route to clear route cache
Route::get('/clear-route-cache', function() {
    Artisan::call('route:clear');
    return 'Route cache cleared!';
});

// Route to run database migrations
Route::get('/migrate', function() {
    Artisan::call('migrate');
    return 'Database migrations executed!';
});

// Route to run database migrations with seeding (optional)
Route::get('/migrate-seed', function() {
    Artisan::call('migrate:fresh --seed'); // This will drop all tables and re-run migrations, then seed the database
    return 'Database migrated and seeded!';
});

// Route to create storage symlink (REMOVE IN PRODUCTION)
Route::get('/create-storage-link', function() {
    try {
        Artisan::call('storage:link');
        $symlinkPath = public_path('storage');
        $targetPath = storage_path('app/public');
        
        if (is_link($symlinkPath) || file_exists($symlinkPath)) {
            return 'Storage symlink created successfully!<br>Symlink: ' . $symlinkPath . '<br>Target: ' . $targetPath;
        } else {
            return 'Storage symlink may not have been created. Please check manually.';
        }
    } catch (\Exception $e) {
        return 'Error creating storage symlink: ' . $e->getMessage();
    }
});


Route::get('/', function () {
    return view('home');
});

Route::get('/home', function () {
    return view('home');
})->name('home');

// API Routes for market data
Route::prefix('api')->group(function () {
    Route::get('/price/{symbol}', [App\Http\Controllers\MarketDataController::class, 'getPrice']);
    Route::get('/ticker/{symbol}', [App\Http\Controllers\MarketDataController::class, 'getTicker']);
    Route::get('/klines/{symbol}/{interval?}', [App\Http\Controllers\MarketDataController::class, 'getKlines']);
    Route::get('/market-data/{symbol}', [App\Http\Controllers\MarketDataController::class, 'getMarketData']);
    Route::post('/prices', [App\Http\Controllers\MarketDataController::class, 'getMultiplePrices']);
    
    // Referral resolution
    Route::get('/referral/resolve', function(\Illuminate\Http\Request $request) {
        $code = $request->query('code');
        $user = \App\Models\User::where('referral_code', $code)->first();
        
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Referral code not found'], 404);
        }
        
        return response()->json([
            'success' => true, 
            'user_id' => $user->id, 
            'referral_code' => $user->referral_code
        ]);
    });

    // Bot packages and plans API
    Route::get('/rent-bot-packages', function() {
        $packages = \App\Models\RentBotPackage::where('status', 1)->orWhere('status', 'active')->orderBy('created_at', 'desc')->get();
        return response()->json(['success' => true, 'data' => $packages]);
    });

    Route::get('/plans', function() {
        $plans = \App\Models\Plan::active()->orderBy('investment_amount', 'asc')->get();
        return response()->json(['success' => true, 'data' => $plans]);
    });

    Route::post('/calculate-nexa-fee', function(\Illuminate\Http\Request $request) {
        $request->validate([
            'investment_amount' => 'required|numeric|min:100',
        ]);

        $investmentAmount = (float) $request->investment_amount;
        
        // Get all active NEXA plans ordered by investment amount
        $plans = \App\Models\Plan::active()->orderBy('investment_amount', 'asc')->get();
        
        // Find the closest plan tier or calculate based on percentage
        $calculatedFee = 0;
        $matchedPlan = null;
        $feePercentage = 0;
        
        // If investment matches exactly a plan, use that plan's fee
        $exactMatch = $plans->where('investment_amount', $investmentAmount)->first();
        if ($exactMatch) {
            $matchedPlan = $exactMatch;
            // Use fee_percentage if available, otherwise calculate it
            if ($matchedPlan->fee_percentage) {
                $feePercentage = $matchedPlan->fee_percentage;
            } else {
                $feePercentage = ($matchedPlan->joining_fee / $matchedPlan->investment_amount) * 100;
            }
            $calculatedFee = round(($investmentAmount * $feePercentage) / 100, 2);
        } else {
            // Find the plan with investment_amount <= user's investment
            $lowerPlan = $plans->where('investment_amount', '<=', $investmentAmount)->last();
            
            if ($lowerPlan) {
                $matchedPlan = $lowerPlan;
                // Use fee_percentage if available, otherwise calculate it
                if ($matchedPlan->fee_percentage) {
                    $feePercentage = $matchedPlan->fee_percentage;
                } else {
                    $feePercentage = ($matchedPlan->joining_fee / $matchedPlan->investment_amount) * 100;
                }
                $calculatedFee = round(($investmentAmount * $feePercentage) / 100, 2);
            } else {
                // If investment is less than the smallest plan, use the smallest plan's percentage
                $smallestPlan = $plans->first();
                if ($smallestPlan) {
                    $matchedPlan = $smallestPlan;
                    // Use fee_percentage if available, otherwise calculate it
                    if ($matchedPlan->fee_percentage) {
                        $feePercentage = $matchedPlan->fee_percentage;
                    } else {
                        $feePercentage = ($matchedPlan->joining_fee / $matchedPlan->investment_amount) * 100;
                    }
                    $calculatedFee = round(($investmentAmount * $feePercentage) / 100, 2);
                }
            }
        }
        
        return response()->json([
            'success' => true,
            'investment_amount' => $investmentAmount,
            'joining_fee' => $calculatedFee,
            'total_amount' => $investmentAmount + $calculatedFee,
            'fee_percentage' => round($feePercentage, 2),
            'matched_plan' => $matchedPlan ? $matchedPlan->name : null,
            'matched_plan_id' => $matchedPlan ? $matchedPlan->id : null,
            'matched_plan_data' => $matchedPlan ? [
                'id' => $matchedPlan->id,
                'name' => $matchedPlan->name,
                'investment_amount' => $matchedPlan->investment_amount,
                'joining_fee' => $matchedPlan->joining_fee,
                'fee_percentage' => $matchedPlan->fee_percentage,
                'bots_allowed' => $matchedPlan->bots_allowed,
                'trades_per_day' => $matchedPlan->trades_per_day
            ] : null
        ]);
    });
});

// Redirect authenticated users to appropriate dashboard
Route::get('/dashboard', function () {
    $user = auth()->user();
    
    if ($user->isCustomer()) {
        // Only customers go to customer dashboard
        return redirect()->route('customer.dashboard');
    } else {
        // All non-customer roles (admin, manager, moderator) go to admin panel
        return redirect()->route('admin.dashboard');
    }
})->middleware(['auth', 'verified'])->name('dashboard');

// Admin Routes (for admin, manager, and other non-customer roles)
Route::middleware(['auth', 'admin.access'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');
    
    Route::resource('users', UserController::class);
    Route::resource('roles', RoleController::class)->only(['index', 'edit', 'update']);
    Route::resource('trades', App\Http\Controllers\Admin\TradeController::class);
    Route::resource('transactions', App\Http\Controllers\Admin\TransactionController::class);
    Route::resource('agents', App\Http\Controllers\Admin\AgentController::class);
    Route::resource('plans', App\Http\Controllers\Admin\PlanController::class);
    Route::resource('rent-bot-packages', App\Http\Controllers\Admin\RentBotPackageController::class);
    Route::resource('wallet-addresses', App\Http\Controllers\Admin\WalletAddressController::class);
    Route::resource('deposits', App\Http\Controllers\Admin\DepositController::class)->only(['index']);
    Route::get('/deposits/{id}/edit', [App\Http\Controllers\Admin\DepositController::class, 'edit'])->name('deposits.edit');
    Route::post('/deposits/{id}/approve', [App\Http\Controllers\Admin\DepositController::class, 'approve'])->name('deposits.approve');
    Route::post('/deposits/{id}/reject', [App\Http\Controllers\Admin\DepositController::class, 'reject'])->name('deposits.reject');
    Route::post('/deposits/{id}/cancel', [App\Http\Controllers\Admin\DepositController::class, 'cancel'])->name('deposits.cancel');
    Route::put('/deposits/{id}', [App\Http\Controllers\Admin\DepositController::class, 'update'])->name('deposits.update');
    Route::post('/deposits/{id}/update', [App\Http\Controllers\Admin\DepositController::class, 'update'])->name('deposits.update.post');
    Route::get('/deposits/{id}/show', [App\Http\Controllers\Admin\DepositController::class, 'show'])->name('deposits.show');
    Route::resource('invoices', App\Http\Controllers\Admin\InvoiceController::class)->only(['index', 'show']);
    Route::get('/withdrawals', [App\Http\Controllers\Admin\WithdrawalController::class, 'index'])->name('withdrawals.index');
    Route::get('/withdrawals/data', [App\Http\Controllers\Admin\WithdrawalController::class, 'getWithdrawalsData'])->name('withdrawals.data');
    Route::post('/withdrawals/{id}/approve', [App\Http\Controllers\Admin\WithdrawalController::class, 'approve'])->name('withdrawals.approve');
    Route::post('/withdrawals/{id}/complete', [App\Http\Controllers\Admin\WithdrawalController::class, 'complete'])->name('withdrawals.complete');
    Route::post('/withdrawals/{id}/reject', [App\Http\Controllers\Admin\WithdrawalController::class, 'reject'])->name('withdrawals.reject');
    Route::get('/withdrawals/{id}/show', [App\Http\Controllers\Admin\WithdrawalController::class, 'show'])->name('withdrawals.show');
    Route::get('/settings', [App\Http\Controllers\Admin\SettingsController::class, 'index'])->name('settings.index');
    Route::put('/settings', [App\Http\Controllers\Admin\SettingsController::class, 'update'])->name('settings.update');
    
    // Support Messages
    Route::get('/support', [App\Http\Controllers\Admin\SupportController::class, 'index'])->name('support.index');
    Route::get('/support/{threadId}', [App\Http\Controllers\Admin\SupportController::class, 'show'])->name('support.show');
    Route::post('/support/reply', [App\Http\Controllers\Admin\SupportController::class, 'reply'])->name('support.reply');
    Route::post('/support/mark-read', [App\Http\Controllers\Admin\SupportController::class, 'markAsRead'])->name('support.mark-read');
    Route::get('/support/unread-count', [App\Http\Controllers\Admin\SupportController::class, 'getUnreadCount'])->name('support.unread-count');
    Route::get('/support/{threadId}/messages', [App\Http\Controllers\Admin\SupportController::class, 'getMessages'])->name('support.messages');
});

// Customer Routes (only for customers)
Route::middleware(['auth', 'role:customer'])->prefix('customer')->name('customer.')->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Trading
    Route::get('/trading', [TradingController::class, 'index'])->name('trading.index');
    Route::post('/trading/start', [TradingController::class, 'startTrade'])->name('trading.start');
    Route::post('/trading/close/{tradeId}', [TradingController::class, 'closeTrade'])->name('trading.close');
    
    // Market
    Route::get('/market', [MarketController::class, 'index'])->name('market.index');
    
    // Wallet
    Route::get('/wallet', [WalletController::class, 'index'])->name('wallet.index');
    Route::get('/wallet/deposit', [WalletController::class, 'deposit'])->name('wallet.deposit');
    Route::post('/wallet/deposit', [WalletController::class, 'submitDeposit'])->name('wallet.deposit.submit');
    Route::get('/wallet/withdraw', [WalletController::class, 'withdraw'])->name('wallet.withdraw');
    Route::post('/wallet/withdraw', [WalletController::class, 'processWithdrawal'])->name('wallet.withdraw.process');
    Route::get('/wallet/withdrawals/data', [WalletController::class, 'getWithdrawalsData'])->name('wallet.withdrawals.data');
    Route::get('/wallet/history', [WalletController::class, 'history'])->name('wallet.history');
    Route::get('/wallet/purchases', [WalletController::class, 'purchases'])->name('wallet.purchases');
    Route::get('/invoices/{invoiceId}/details', [WalletController::class, 'getInvoiceDetails'])->name('invoices.details');
    
    // AI Bots
    Route::get('/bots', [AgentController::class, 'index'])->name('bots.index');
    Route::get('/bots/create', [AgentController::class, 'create'])->name('bots.create');
    Route::post('/bots', [AgentController::class, 'store'])->name('bots.store');
    Route::get('/bots/{agent}', [AgentController::class, 'show'])->name('bots.show');
    Route::get('/bots/{agent}/edit', [AgentController::class, 'edit'])->name('bots.edit');
    Route::put('/bots/{agent}', [AgentController::class, 'update'])->name('bots.update');
    Route::delete('/bots/{agent}', [AgentController::class, 'destroy'])->name('bots.destroy');
    
    // Package Details
    Route::get('/packages/{bot}', [AgentController::class, 'showPackage'])->name('packages.show');
    
    // Profile
    Route::get('/profile', [CustomerProfileController::class, 'index'])->name('profile.index');
    Route::get('/profile/edit', [CustomerProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [CustomerProfileController::class, 'update'])->name('profile.update');
    
    // Referrals
    Route::get('/referrals', [ReferralController::class, 'index'])->name('referrals.index');
    
    // Support
    Route::get('/support', [SupportController::class, 'index'])->name('support.index');
    Route::post('/support', [SupportController::class, 'store'])->name('support.store');
    Route::get('/support/messages', [SupportController::class, 'getMessages'])->name('support.messages');
    Route::post('/support/mark-read', [SupportController::class, 'markAsRead'])->name('support.mark-read');
    Route::get('/support/unread-count', [SupportController::class, 'getUnreadCount'])->name('support.unread-count');
    
    // Invoices
    Route::get('/invoices', [App\Http\Controllers\Customer\InvoiceController::class, 'index'])->name('invoices.index');
    Route::get('/invoices/data', [App\Http\Controllers\Customer\InvoiceController::class, 'getInvoicesData'])->name('invoices.data');
});

// General auth routes
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
