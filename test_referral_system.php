<?php

/**
 * Test script for the referral system
 * Run this after seeding the database to test the referral functionality
 */

require_once 'vendor/autoload.php';

use App\Models\User;
use App\Models\Referral;
use App\Models\Plan;
use App\Models\Wallet;
use App\Services\ReferralService;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== REFERRAL SYSTEM TEST ===\n\n";

// Test 1: Display referral structure
echo "1. DISPLAYING REFERRAL STRUCTURE\n";
echo "================================\n";

$mainUser = User::where('email', 'main@test.com')->first();
if ($mainUser) {
    echo "Main User: {$mainUser->name} ({$mainUser->email})\n";
    echo "Referral Code: {$mainUser->referral_code}\n";
    echo "Active Plan: " . ($mainUser->activePlan ? $mainUser->activePlan->name : 'None') . "\n";
    echo "Investment: " . ($mainUser->active_investment_amount ?? 0) . " USDT\n\n";
    
    // Show direct referrals
    $directReferrals = $mainUser->referredUsers;
    echo "Direct Referrals ({$directReferrals->count()}):\n";
    foreach ($directReferrals as $referral) {
        echo "  - {$referral->name} ({$referral->email}) - Investment: " . ($referral->active_investment_amount ?? 0) . " USDT\n";
    }
    echo "\n";
} else {
    echo "Main user not found. Please run the seeders first.\n";
}

// Test 2: Show all referral relationships
echo "2. ALL REFERRAL RELATIONSHIPS\n";
echo "=============================\n";

$referrals = Referral::with(['referrer', 'referred'])->get();
foreach ($referrals as $referral) {
    echo "{$referral->referrer->name} -> {$referral->referred->name} ({$referral->commission_rate}%)\n";
}
echo "\n";

// Test 3: Show commission totals
echo "3. COMMISSION TOTALS\n";
echo "===================\n";

$usersWithCommissions = User::whereHas('referrals')->with('referrals')->get();
foreach ($usersWithCommissions as $user) {
    $totalCommission = $user->referrals->sum('total_commission');
    $pendingCommission = $user->referrals->sum('pending_commission');
    echo "{$user->name}: Total: {$totalCommission} USDT, Pending: {$pendingCommission} USDT\n";
}
echo "\n";

// Test 4: Show wallet balances
echo "4. WALLET BALANCES\n";
echo "==================\n";

$wallets = Wallet::with('user')->where('currency', 'USDT')->get();
foreach ($wallets as $wallet) {
    echo "{$wallet->user->name}: Balance: {$wallet->balance} USDT, Profit: {$wallet->total_profit} USDT\n";
}
echo "\n";

// Test 5: Test referral service
echo "5. TESTING REFERRAL SERVICE\n";
echo "===========================\n";

$testUser = User::where('email', 'level1_1@test.com')->first();
if ($testUser) {
    echo "Testing with user: {$testUser->name} ({$testUser->email})\n";
    
    // Create a mock deposit
    $deposit = new \App\Models\Deposit([
        'user_id' => $testUser->id,
        'deposit_id' => 'TEST_' . time(),
        'amount' => 2000,
        'currency' => 'USDT',
        'network' => 'TRC20',
        'status' => 'approved',
        'notes' => 'Test deposit for referral system testing',
        'approved_at' => now(),
    ]);
    $deposit->save();
    
    echo "Created test deposit: 2000 USDT\n";
    
    // Test referral bonus distribution
    $referralService = new ReferralService();
    $referralService->distributeReferralBonuses($testUser, $deposit);
    
    echo "Referral bonuses distributed!\n";
    
    // Show updated commissions
    $referral = Referral::where('referred_id', $testUser->id)->first();
    if ($referral) {
        echo "Referrer: {$referral->referrer->name}\n";
        echo "Commission Rate: {$referral->commission_rate}%\n";
        echo "Total Commission: {$referral->total_commission} USDT\n";
        echo "Pending Commission: {$referral->pending_commission} USDT\n";
    }
} else {
    echo "Test user not found. Please run the seeders first.\n";
}

echo "\n=== TEST COMPLETED ===\n";
echo "To run individual tests, use:\n";
echo "php artisan test:referral-system --user-email=main@test.com --amount=2000\n";
echo "php artisan referral:setup --type=all\n";
