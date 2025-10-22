<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Referral;
use App\Models\Plan;
use App\Models\Wallet;
use App\Models\Deposit;
use App\Services\ReferralService;
use Illuminate\Support\Facades\DB;

class TestReferralSystem extends Command
{
    protected $signature = 'test:referral-system {--user-email=} {--amount=1000}';
    protected $description = 'Test the referral system by simulating a deposit and checking commission distribution';

    public function handle()
    {
        $userEmail = $this->option('user-email');
        $amount = (float) $this->option('amount');

        if (!$userEmail) {
            $this->error('Please provide a user email with --user-email option');
            return 1;
        }

        $user = User::where('email', $userEmail)->first();
        if (!$user) {
            $this->error("User with email {$userEmail} not found");
            return 1;
        }

        $this->info("Testing referral system for user: {$user->email}");
        $this->info("Simulating deposit amount: {$amount} USDT");

        // Display current referral chain
        $this->displayReferralChain($user);

        // Simulate a deposit
        $deposit = $this->simulateDeposit($user, $amount);

        // Test referral bonus distribution
        $referralService = new ReferralService();
        $referralService->distributeReferralBonuses($user, $deposit);

        $this->info("\nReferral bonuses distributed successfully!");
        
        // Display updated referral chain with commissions
        $this->displayReferralChainWithCommissions($user);

        return 0;
    }

    private function displayReferralChain($user)
    {
        $this->info("\n=== REFERRAL CHAIN ===");
        
        $currentUser = $user;
        $level = 0;
        
        while ($currentUser) {
            $indent = str_repeat('  ', $level);
            $this->info("{$indent}Level {$level}: {$currentUser->name} ({$currentUser->email})");
            $this->info("{$indent}  Referral Code: {$currentUser->referral_code}");
            $this->info("{$indent}  Active Plan: " . ($currentUser->activePlan ? $currentUser->activePlan->name : 'None'));
            $this->info("{$indent}  Investment: " . ($currentUser->active_investment_amount ?? 0) . " USDT");
            
            $currentUser = $currentUser->referredBy;
            $level++;
            
            if ($level > 10) break; // Prevent infinite loops
        }
    }

    private function displayReferralChainWithCommissions($user)
    {
        $this->info("\n=== REFERRAL COMMISSIONS ===");
        
        // Get all referrals for this user's referrers
        $referralChain = $this->getReferralChain($user);
        
        foreach ($referralChain as $level => $referrer) {
            $referral = Referral::where('referrer_id', $referrer->id)
                              ->where('referred_id', $user->id)
                              ->first();
            
            if ($referral) {
                $this->info("Level {$level}: {$referrer->name} ({$referrer->email})");
                $this->info("  Commission Rate: {$referral->commission_rate}%");
                $this->info("  Total Commission: {$referral->total_commission} USDT");
                $this->info("  Pending Commission: {$referral->pending_commission} USDT");
                
                // Show wallet balance
                $wallet = Wallet::where('user_id', $referrer->id)->where('currency', 'USDT')->first();
                if ($wallet) {
                    $this->info("  Wallet Balance: {$wallet->balance} USDT");
                    $this->info("  Total Profit: {$wallet->total_profit} USDT");
                }
            }
        }
    }

    private function getReferralChain($user)
    {
        $chain = [];
        $currentUser = $user;
        $level = 0;
        
        while ($currentUser && $level < 5) {
            $currentUser = $currentUser->referredBy;
            if ($currentUser) {
                $chain[$level + 1] = $currentUser;
                $level++;
            }
        }
        
        return $chain;
    }

    private function simulateDeposit($user, $amount)
    {
        // Create a mock deposit object
        $deposit = new Deposit([
            'user_id' => $user->id,
            'deposit_id' => 'TEST_' . time(),
            'amount' => $amount,
            'currency' => 'USDT',
            'network' => 'TRC20',
            'status' => 'approved',
            'notes' => 'Test deposit for referral system testing',
            'approved_at' => now(),
        ]);
        
        $deposit->save();
        
        $this->info("Created test deposit: {$amount} USDT for user {$user->email}");
        
        return $deposit;
    }
}
