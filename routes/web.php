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
    return view('welcome');
});

// API Routes for market data
Route::prefix('api')->group(function () {
    Route::get('/price/{symbol}', [App\Http\Controllers\MarketDataController::class, 'getPrice']);
    Route::get('/ticker/{symbol}', [App\Http\Controllers\MarketDataController::class, 'getTicker']);
    Route::get('/klines/{symbol}/{interval?}', [App\Http\Controllers\MarketDataController::class, 'getKlines']);
    Route::get('/market-data/{symbol}', [App\Http\Controllers\MarketDataController::class, 'getMarketData']);
    Route::post('/prices', [App\Http\Controllers\MarketDataController::class, 'getMultiplePrices']);
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
});

// General auth routes
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
