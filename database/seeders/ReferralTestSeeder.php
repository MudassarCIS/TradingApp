<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Plan;
use App\Models\Referral;
use App\Models\Wallet;
use App\Models\Profile;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ReferralTestSeeder extends Seeder
{
    public function run()
    {
        // Create test plans
        $basicPlan = Plan::firstOrCreate([
            'name' => 'Basic Plan',
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
            'description' => 'Basic investment plan for testing',
        ]);

        $premiumPlan = Plan::firstOrCreate([
            'name' => 'Premium Plan',
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
            'description' => 'Premium investment plan for testing',
        ]);

        // Create main user (top referrer)
        $mainUser = User::firstOrCreate([
            'email' => 'main@test.com'
        ], [
            'name' => 'Main Referrer',
            'password' => Hash::make('password'),
            'user_type' => 'customer',
            'is_active' => true,
        ]);
        $mainUser->assignRole('customer');
        $mainUser->generateReferralCode();

        // Create profile for main user
        Profile::firstOrCreate([
            'user_id' => $mainUser->id,
        ], [
            'first_name' => 'Main',
            'last_name' => 'Referrer',
            'phone' => '+1234567890',
            'kyc_status' => 'approved',
            'referral_code' => $mainUser->referral_code,
        ]);

        // Create wallet for main user
        Wallet::firstOrCreate([
            'user_id' => $mainUser->id,
            'currency' => 'USDT',
        ], [
            'balance' => 10000,
            'total_deposited' => 10000,
            'total_withdrawn' => 0,
            'total_profit' => 0,
            'total_loss' => 0,
        ]);

        // Assign premium plan to main user
        $mainUser->active_plan_id = $premiumPlan->id;
        $mainUser->active_investment_amount = 5000;
        $mainUser->save();

        $this->command->info("Created main user: {$mainUser->email} (Referral Code: {$mainUser->referral_code})");

        // Create multi-level referral structure
        $this->createReferralLevel($mainUser, 1, 4, $basicPlan, $premiumPlan);
        
        $this->command->info('Multi-level referral test data created successfully!');
        $this->command->info("Main user: {$mainUser->email} (Referral Code: {$mainUser->referral_code})");
        $this->command->info('Password for all test users: password');
    }

    private function createReferralLevel($parentUser, $level, $maxLevel, $basicPlan, $premiumPlan, $usersPerLevel = 3)
    {
        if ($level > $maxLevel) {
            return;
        }

        $levelUsers = [];
        
        for ($i = 1; $i <= $usersPerLevel; $i++) {
            $user = User::firstOrCreate([
                'email' => "level{$level}_{$i}@test.com"
            ], [
                'name' => "Level {$level} User {$i}",
                'password' => Hash::make('password'),
                'user_type' => 'customer',
                'is_active' => true,
                'referred_by' => $parentUser->id,
            ]);
            
            $user->assignRole('customer');
            $user->generateReferralCode();
            $levelUsers[] = $user;

            // Create profile
            Profile::firstOrCreate([
                'user_id' => $user->id,
            ], [
                'first_name' => "Level{$level}",
                'last_name' => "User{$i}",
                'phone' => '+123456789' . ($level * 10 + $i),
                'kyc_status' => $level <= 2 ? 'approved' : 'pending',
                'referral_code' => $user->referral_code,
            ]);

            // Create referral relationship
            $commissionRate = $this->getCommissionRate($level);
            Referral::firstOrCreate([
                'referrer_id' => $parentUser->id,
                'referred_id' => $user->id,
            ], [
                'commission_rate' => $commissionRate,
                'status' => 'active',
                'joined_at' => now()->subDays(rand(1, 30)),
            ]);

            // Create wallet with investment
            $investmentAmount = $this->getInvestmentAmount($level);
            Wallet::firstOrCreate([
                'user_id' => $user->id,
                'currency' => 'USDT',
            ], [
                'balance' => $investmentAmount * 0.5,
                'total_deposited' => $investmentAmount,
                'total_withdrawn' => 0,
                'total_profit' => $investmentAmount * 0.1,
                'total_loss' => 0,
            ]);

            // Assign plan based on level
            $plan = $level <= 2 ? $premiumPlan : $basicPlan;
            $user->active_plan_id = $plan->id;
            $user->active_investment_amount = $investmentAmount;
            $user->save();

            $this->command->info("Created Level {$level} User {$i}: {$user->email} (Referred by: {$parentUser->email})");

            // Recursively create next level
            $this->createReferralLevel($user, $level + 1, $maxLevel, $basicPlan, $premiumPlan, $usersPerLevel - 1);
        }
    }

    private function getCommissionRate($level)
    {
        return match($level) {
            1 => 15,  // Level 1: 15%
            2 => 8,   // Level 2: 8%
            3 => 3,   // Level 3: 3%
            4 => 1,   // Level 4: 1%
            default => 0
        };
    }

    private function getInvestmentAmount($level)
    {
        return match($level) {
            1 => 5000,  // Level 1: $5000
            2 => 3000,  // Level 2: $3000
            3 => 1500,  // Level 3: $1500
            4 => 1000,  // Level 4: $1000
            default => 500
        };
    }
}
