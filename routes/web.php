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
        $packages = \App\Models\RentBotPackage::where('status', 1)->orWhere('status', 'active')->get();
        return response()->json(['success' => true, 'data' => $packages]);
    });

    Route::get('/plans', function() {
        $plans = \App\Models\Plan::active()->ordered()->get();
        return response()->json(['success' => true, 'data' => $plans]);
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
    Route::post('/deposits/{id}/approve', [App\Http\Controllers\Admin\DepositController::class, 'approve'])->name('deposits.approve');
    Route::post('/deposits/{id}/reject', [App\Http\Controllers\Admin\DepositController::class, 'reject'])->name('deposits.reject');
    Route::get('/deposits/{id}/show', [App\Http\Controllers\Admin\DepositController::class, 'show'])->name('deposits.show');
});

// Customer Routes (only for customers)
Route::middleware(['auth', 'role:customer'])->prefix('customer')->name('customer.')->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Trading
    Route::get('/trading', [TradingController::class, 'index'])->name('trading.index');
    
    // Market
    Route::get('/market', [MarketController::class, 'index'])->name('market.index');
    
    // Wallet
    Route::get('/wallet', [WalletController::class, 'index'])->name('wallet.index');
    Route::get('/wallet/deposit', [WalletController::class, 'deposit'])->name('wallet.deposit');
    Route::post('/wallet/deposit', [WalletController::class, 'submitDeposit'])->name('wallet.deposit.submit');
    Route::get('/wallet/withdraw', [WalletController::class, 'withdraw'])->name('wallet.withdraw');
    Route::post('/wallet/withdraw', [WalletController::class, 'processWithdrawal'])->name('wallet.withdraw.process');
    Route::get('/wallet/history', [WalletController::class, 'history'])->name('wallet.history');
    Route::get('/wallet/purchases', [WalletController::class, 'purchases'])->name('wallet.purchases');
    
    // AI Agents
    Route::get('/agents', [AgentController::class, 'index'])->name('agents.index');
    Route::get('/agents/create', [AgentController::class, 'create'])->name('agents.create');
    Route::post('/agents', [AgentController::class, 'store'])->name('agents.store');
    Route::get('/agents/{agent}', [AgentController::class, 'show'])->name('agents.show');
    Route::get('/agents/{agent}/edit', [AgentController::class, 'edit'])->name('agents.edit');
    Route::put('/agents/{agent}', [AgentController::class, 'update'])->name('agents.update');
    Route::delete('/agents/{agent}', [AgentController::class, 'destroy'])->name('agents.destroy');
    
    // Profile
    Route::get('/profile', [CustomerProfileController::class, 'index'])->name('profile.index');
    Route::get('/profile/edit', [CustomerProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [CustomerProfileController::class, 'update'])->name('profile.update');
    
    // Referrals
    Route::get('/referrals', [ReferralController::class, 'index'])->name('referrals.index');
    
    // Support
    Route::get('/support', [SupportController::class, 'index'])->name('support.index');
    Route::post('/support', [SupportController::class, 'store'])->name('support.store');
    
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
