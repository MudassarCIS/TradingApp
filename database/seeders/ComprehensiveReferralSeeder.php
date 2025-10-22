<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Plan;
use App\Models\Referral;
use App\Models\Wallet;
use App\Models\Profile;
use App\Models\Deposit;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ComprehensiveReferralSeeder extends Seeder
{
    public function run()
    {
        $this->command->info('Creating comprehensive multi-level referral system...');

        // Create plans
        $plans = $this->createPlans();

        // Create the main referrer (top of the pyramid)
        $mainReferrer = $this->createMainReferrer($plans['premium']);

        // Create a realistic multi-level structure
        $this->createRealisticReferralStructure($mainReferrer, $plans);

        $this->command->info('Comprehensive referral system created successfully!');
        $this->command->info("Main referrer: {$mainReferrer->email} (Code: {$mainReferrer->referral_code})");
        $this->command->info('Password for all users: password');
    }

    private function createPlans()
    {
        $basicPlan = Plan::firstOrCreate([
            'name' => 'Starter Plan',
        ], [
            'investment_amount' => 1000,
            'joining_fee' => 0,
            'bots_allowed' => 1,
            'trades_per_day' => 10,
            'direct_bonus' => 0,
            'referral_level_1' => 10,
            'referral_level_2' => 5,
            'referral_level_3' => 2,
            'is_active' => true,
            'sort_order' => 1,
            'description' => 'Perfect for beginners',
        ]);

        $premiumPlan = Plan::firstOrCreate([
            'name' => 'Professional Plan',
        ], [
            'investment_amount' => 5000,
            'joining_fee' => 0,
            'bots_allowed' => 3,
            'trades_per_day' => 50,
            'direct_bonus' => 0,
            'referral_level_1' => 15,
            'referral_level_2' => 8,
            'referral_level_3' => 3,
            'is_active' => true,
            'sort_order' => 2,
            'description' => 'For serious traders',
        ]);

        $vipPlan = Plan::firstOrCreate([
            'name' => 'VIP Plan',
        ], [
            'investment_amount' => 10000,
            'joining_fee' => 0,
            'bots_allowed' => 5,
            'trades_per_day' => 100,
            'direct_bonus' => 0,
            'referral_level_1' => 20,
            'referral_level_2' => 10,
            'referral_level_3' => 5,
            'is_active' => true,
            'sort_order' => 3,
            'description' => 'Maximum returns for VIP members',
        ]);

        return [
            'basic' => $basicPlan,
            'premium' => $premiumPlan,
            'vip' => $vipPlan,
        ];
    }

    private function createMainReferrer($plan)
    {
        $user = User::firstOrCreate([
            'email' => 'vip.referrer@test.com'
        ], [
            'name' => 'VIP Referrer',
            'password' => Hash::make('password'),
            'user_type' => 'customer',
            'is_active' => true,
            'active_plan_id' => $plan->id,
            'active_investment_amount' => 10000,
        ]);

        $user->assignRole('customer');
        $user->generateReferralCode();

        // Create profile
        Profile::firstOrCreate([
            'user_id' => $user->id,
        ], [
            'first_name' => 'VIP',
            'last_name' => 'Referrer',
            'phone' => '+1234567890',
            'kyc_status' => 'approved',
            'referral_code' => $user->referral_code,
        ]);

        // Create wallet
        Wallet::firstOrCreate([
            'user_id' => $user->id,
            'currency' => 'USDT',
        ], [
            'balance' => 15000,
            'total_deposited' => 10000,
            'total_withdrawn' => 0,
            'total_profit' => 5000,
            'total_loss' => 0,
        ]);

        return $user;
    }

    private function createRealisticReferralStructure($mainReferrer, $plans)
    {
        // Level 1: 5 direct referrals (high performers)
        $level1Users = $this->createReferralLevel($mainReferrer, 1, 5, $plans, [
            'plan' => 'premium',
            'investment_range' => [3000, 8000],
            'kyc_status' => 'approved',
        ]);

        // Level 2: Each level 1 user has 3-4 referrals
        $level2Users = [];
        foreach ($level1Users as $index => $level1User) {
            $count = rand(3, 4);
            $level2Users = array_merge($level2Users, $this->createReferralLevel($level1User, 2, $count, $plans, [
                'plan' => 'basic',
                'investment_range' => [1000, 3000],
                'kyc_status' => rand(0, 1) ? 'approved' : 'pending',
            ]));
        }

        // Level 3: Each level 2 user has 2-3 referrals
        $level3Users = [];
        foreach ($level2Users as $index => $level2User) {
            $count = rand(2, 3);
            $level3Users = array_merge($level3Users, $this->createReferralLevel($level2User, 3, $count, $plans, [
                'plan' => 'basic',
                'investment_range' => [500, 1500],
                'kyc_status' => 'pending',
            ]));
        }

        // Level 4: Some level 3 users have 1-2 referrals
        foreach (array_slice($level3Users, 0, 10) as $index => $level3User) {
            $count = rand(1, 2);
            $this->createReferralLevel($level3User, 4, $count, $plans, [
                'plan' => 'basic',
                'investment_range' => [100, 800],
                'kyc_status' => 'pending',
            ]);
        }

        // Create some deposits to test commission distribution
        $this->createTestDeposits($level1Users, $level2Users, $level3Users);
        
        // Create test transactions for wallet history
        $this->createTestTransactions($mainUser, $level1Users, $level2Users, $level3Users);
    }

    private function createReferralLevel($parentUser, $level, $count, $plans, $config)
    {
        $users = [];
        
        for ($i = 1; $i <= $count; $i++) {
            $investment = rand($config['investment_range'][0], $config['investment_range'][1]);
            $plan = $plans[$config['plan']];
            
            $user = User::firstOrCreate([
                'email' => "level{$level}_{$parentUser->id}_{$i}@test.com"
            ], [
                'name' => "Level {$level} User {$i}",
                'password' => Hash::make('password'),
                'user_type' => 'customer',
                'is_active' => true,
                'referred_by' => $parentUser->id,
                'active_plan_id' => $plan->id,
                'active_investment_amount' => $investment,
            ]);

            $user->assignRole('customer');
            $user->generateReferralCode();
            $users[] = $user;

            // Create profile
            Profile::firstOrCreate([
                'user_id' => $user->id,
            ], [
                'first_name' => "Level{$level}",
                'last_name' => "User{$i}",
                'phone' => '+1234567' . rand(100, 999),
                'kyc_status' => $config['kyc_status'],
                'referral_code' => $user->referral_code,
            ]);

            // Create referral relationship
            $commissionRate = $this->getCommissionRate($level, $plan);
            Referral::firstOrCreate([
                'referrer_id' => $parentUser->id,
                'referred_id' => $user->id,
            ], [
                'commission_rate' => $commissionRate,
                'status' => 'active',
                'joined_at' => now()->subDays(rand(1, 90)),
            ]);

            // Create wallet
            Wallet::firstOrCreate([
                'user_id' => $user->id,
                'currency' => 'USDT',
            ], [
                'balance' => $investment * 0.6,
                'total_deposited' => $investment,
                'total_withdrawn' => 0,
                'total_profit' => $investment * 0.1,
                'total_loss' => 0,
            ]);

            $this->command->info("Created Level {$level} User {$i}: {$user->email} (Investment: {$investment} USDT)");
        }

        return $users;
    }

    private function getCommissionRate($level, $plan)
    {
        $field = "referral_level_{$level}";
        return $plan->$field ?? 0;
    }

    private function createTestDeposits($level1Users, $level2Users, $level3Users)
    {
        $this->command->info('Creating test deposits...');

        // Create deposits for some users to test commission distribution
        $allUsers = array_merge($level1Users, $level2Users, $level3Users);
        
        foreach (array_slice($allUsers, 0, 15) as $user) {
            $depositAmount = rand(500, 2000);
            
            Deposit::create([
                'user_id' => $user->id,
                'deposit_id' => 'TEST_' . time() . '_' . $user->id,
                'amount' => $depositAmount,
                'currency' => 'USDT',
                'network' => 'TRC20',
                'status' => 'approved',
                'notes' => 'Test deposit for referral system',
                'approved_at' => now()->subDays(rand(1, 30)),
                'created_at' => now()->subDays(rand(1, 30)),
            ]);

            $this->command->info("Created test deposit: {$depositAmount} USDT for {$user->email}");
        }
    }
    
    private function createTestTransactions($mainUser, $level1Users, $level2Users, $level3Users)
    {
        $this->command->info('Creating test transactions...');
        
        $allUsers = array_merge([$mainUser], $level1Users, $level2Users, $level3Users);
        
        foreach ($allUsers as $user) {
            // Create various types of transactions
            $transactionTypes = [
                'deposit' => 3,
                'withdrawal' => 2,
                'bonus' => 1,
                'commission' => 2,
                'transfer' => 1,
            ];
            
            foreach ($transactionTypes as $type => $count) {
                for ($i = 0; $i < $count; $i++) {
                    $this->createTransaction($user, $type);
                }
            }
        }
    }
    
    private function createTransaction($user, $type)
    {
        $amounts = [
            'deposit' => [100, 500, 1000, 2000, 5000],
            'withdrawal' => [50, 200, 500, 1000],
            'bonus' => [10, 25, 50, 100],
            'commission' => [5, 15, 30, 75],
            'transfer' => [25, 100, 250, 500],
        ];
        
        $statuses = ['pending', 'completed', 'failed'];
        $statusWeights = [10, 85, 5]; // 10% pending, 85% completed, 5% failed
        
        $amount = $amounts[$type][array_rand($amounts[$type])];
        $status = $this->getWeightedRandomStatus($statuses, $statusWeights);
        
        $transaction = \App\Models\Transaction::create([
            'user_id' => $user->id,
            'transaction_id' => 'TXN' . time() . rand(1000, 9999),
            'type' => $type,
            'status' => $status,
            'currency' => 'USDT',
            'amount' => $amount,
            'fee' => $type === 'withdrawal' ? 5 : ($type === 'transfer' ? 2 : 0),
            'net_amount' => $type === 'withdrawal' ? $amount - 5 : ($type === 'transfer' ? $amount - 2 : $amount),
            'from_address' => $type === 'withdrawal' ? 'User Wallet' : ($type === 'transfer' ? 'User Wallet' : null),
            'to_address' => $type === 'deposit' ? 'Platform Wallet' : ($type === 'withdrawal' ? 'External Wallet' : 'User Wallet'),
            'tx_hash' => $status === 'completed' ? '0x' . bin2hex(random_bytes(16)) : null,
            'notes' => $this->getTransactionNote($type),
            'processed_at' => $status === 'completed' ? now()->subDays(rand(1, 30)) : null,
            'created_at' => now()->subDays(rand(1, 90)),
        ]);
        
        $this->command->info("Created {$type} transaction: {$amount} USDT for {$user->email} (Status: {$status})");
    }
    
    private function getWeightedRandomStatus($statuses, $weights)
    {
        $totalWeight = array_sum($weights);
        $random = rand(1, $totalWeight);
        
        $currentWeight = 0;
        foreach ($statuses as $index => $status) {
            $currentWeight += $weights[$index];
            if ($random <= $currentWeight) {
                return $status;
            }
        }
        
        return $statuses[0]; // fallback
    }
    
    private function getTransactionNote($type)
    {
        $notes = [
            'deposit' => [
                'Initial deposit',
                'Additional funding',
                'Bank transfer deposit',
                'Crypto deposit',
                'Wire transfer'
            ],
            'withdrawal' => [
                'Withdrawal request',
                'Bank transfer withdrawal',
                'Crypto withdrawal',
                'Emergency withdrawal'
            ],
            'bonus' => [
                'Welcome bonus',
                'Referral bonus',
                'Trading bonus',
                'Loyalty bonus',
                'Promotional bonus'
            ],
            'commission' => [
                'Level 1 referral commission',
                'Level 2 referral commission',
                'Level 3 referral commission',
                'Direct referral commission'
            ],
            'transfer' => [
                'Internal transfer',
                'Wallet to wallet transfer',
                'Account transfer'
            ]
        ];
        
        $typeNotes = $notes[$type] ?? ['Transaction'];
        return $typeNotes[array_rand($typeNotes)];
    }
}
