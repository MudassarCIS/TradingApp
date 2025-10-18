<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Plan;
use App\Models\Referral;
use App\Models\Wallet;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ReferralTestSeeder extends Seeder
{
    public function run()
    {
        // Create a test plan
        $plan = Plan::firstOrCreate([
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

        // Create main user (referrer)
        $mainUser = User::firstOrCreate([
            'email' => 'main@test.com'
        ], [
            'name' => 'Main User',
            'password' => Hash::make('password'),
            'user_type' => 'customer',
            'is_active' => true,
        ]);
        $mainUser->assignRole('customer');
        $mainUser->generateReferralCode();

        // Create level 1 referrals
        $level1Users = [];
        for ($i = 1; $i <= 3; $i++) {
            $user = User::firstOrCreate([
                'email' => "level1_{$i}@test.com"
            ], [
                'name' => "Level 1 User {$i}",
                'password' => Hash::make('password'),
                'user_type' => 'customer',
                'is_active' => true,
                'referred_by' => $mainUser->id,
            ]);
            $user->assignRole('customer');
            $user->generateReferralCode();
            $level1Users[] = $user;

            // Create referral relationship
            Referral::firstOrCreate([
                'referrer_id' => $mainUser->id,
                'referred_id' => $user->id,
            ], [
                'commission_rate' => 10,
                'status' => 'active',
                'joined_at' => now(),
            ]);

            // Create wallets with some investment
            Wallet::firstOrCreate([
                'user_id' => $user->id,
                'currency' => 'USDT',
            ], [
                'balance' => 500,
                'total_deposited' => 1000,
                'total_withdrawn' => 0,
                'total_profit' => 0,
                'total_loss' => 0,
            ]);

            // Assign plan to some users
            if ($i <= 2) {
                $user->active_plan_id = $plan->id;
                $user->active_investment_amount = 1000;
                $user->save();
            }
        }

        // Create level 2 referrals
        foreach ($level1Users as $index => $level1User) {
            for ($j = 1; $j <= 2; $j++) {
                $user = User::firstOrCreate([
                    'email' => "level2_{$index}_{$j}@test.com"
                ], [
                    'name' => "Level 2 User {$index}-{$j}",
                    'password' => Hash::make('password'),
                    'user_type' => 'customer',
                    'is_active' => true,
                    'referred_by' => $level1User->id,
                ]);
                $user->assignRole('customer');
                $user->generateReferralCode();

                // Create referral relationship
                Referral::firstOrCreate([
                    'referrer_id' => $level1User->id,
                    'referred_id' => $user->id,
                ], [
                    'commission_rate' => 5,
                    'status' => 'active',
                    'joined_at' => now(),
                ]);

                // Create wallets with some investment
                Wallet::firstOrCreate([
                    'user_id' => $user->id,
                    'currency' => 'USDT',
                ], [
                    'balance' => 250,
                    'total_deposited' => 500,
                    'total_withdrawn' => 0,
                    'total_profit' => 0,
                    'total_loss' => 0,
                ]);
            }
        }

        $this->command->info('Referral test data created successfully!');
        $this->command->info("Main user: {$mainUser->email} (Referral Code: {$mainUser->referral_code})");
    }
}
