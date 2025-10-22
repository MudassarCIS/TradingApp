<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Plan;
use App\Models\Profile;
use App\Models\Wallet;
use App\Models\Referral;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class CustomerSeeder extends Seeder
{
    public function run()
    {
        // Ensure we have plans
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
            'description' => 'Basic investment plan',
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
            'description' => 'Premium investment plan',
        ]);

        // Create various customer scenarios
        $this->createActiveCustomers($basicPlan, $premiumPlan);
        $this->createInactiveCustomers($basicPlan);
        $this->createCustomersWithDifferentKycStatus($basicPlan, $premiumPlan);
        $this->createCustomersWithDifferentInvestmentLevels($basicPlan, $premiumPlan);
        
        $this->command->info('Customer seeder completed successfully!');
        $this->command->info('All customers have password: password');
    }

    private function createActiveCustomers($basicPlan, $premiumPlan)
    {
        $this->command->info('Creating active customers...');

        $activeCustomers = [
            [
                'name' => 'Alice Johnson',
                'email' => 'alice.johnson@example.com',
                'investment' => 2500,
                'plan' => $basicPlan,
                'kyc_status' => 'approved',
                'balance' => 2500,
                'profit' => 250,
            ],
            [
                'name' => 'Bob Smith',
                'email' => 'bob.smith@example.com',
                'investment' => 7500,
                'plan' => $premiumPlan,
                'kyc_status' => 'approved',
                'balance' => 7500,
                'profit' => 750,
            ],
            [
                'name' => 'Carol Davis',
                'email' => 'carol.davis@example.com',
                'investment' => 1200,
                'plan' => $basicPlan,
                'kyc_status' => 'approved',
                'balance' => 1200,
                'profit' => 120,
            ],
        ];

        foreach ($activeCustomers as $customerData) {
            $user = User::firstOrCreate([
                'email' => $customerData['email']
            ], [
                'name' => $customerData['name'],
                'password' => Hash::make('password'),
                'user_type' => 'customer',
                'is_active' => true,
                'active_plan_id' => $customerData['plan']->id,
                'active_investment_amount' => $customerData['investment'],
            ]);

            $user->assignRole('customer');
            $user->generateReferralCode();

            // Create profile
            Profile::firstOrCreate([
                'user_id' => $user->id,
            ], [
                'first_name' => explode(' ', $customerData['name'])[0],
                'last_name' => explode(' ', $customerData['name'])[1],
                'phone' => '+1234567' . rand(100, 999),
                'kyc_status' => $customerData['kyc_status'],
                'referral_code' => $user->referral_code,
            ]);

            // Create wallet
            Wallet::firstOrCreate([
                'user_id' => $user->id,
                'currency' => 'USDT',
            ], [
                'balance' => $customerData['balance'],
                'total_deposited' => $customerData['investment'],
                'total_withdrawn' => 0,
                'total_profit' => $customerData['profit'],
                'total_loss' => 0,
            ]);

            $this->command->info("Created active customer: {$user->email}");
        }
    }

    private function createInactiveCustomers($basicPlan)
    {
        $this->command->info('Creating inactive customers...');

        $inactiveCustomers = [
            [
                'name' => 'David Wilson',
                'email' => 'david.wilson@example.com',
                'investment' => 500,
            ],
            [
                'name' => 'Eva Brown',
                'email' => 'eva.brown@example.com',
                'investment' => 800,
            ],
        ];

        foreach ($inactiveCustomers as $customerData) {
            $user = User::firstOrCreate([
                'email' => $customerData['email']
            ], [
                'name' => $customerData['name'],
                'password' => Hash::make('password'),
                'user_type' => 'customer',
                'is_active' => false, // Inactive
                'active_plan_id' => $basicPlan->id,
                'active_investment_amount' => $customerData['investment'],
            ]);

            $user->assignRole('customer');
            $user->generateReferralCode();

            // Create profile
            Profile::firstOrCreate([
                'user_id' => $user->id,
            ], [
                'first_name' => explode(' ', $customerData['name'])[0],
                'last_name' => explode(' ', $customerData['name'])[1],
                'phone' => '+1234567' . rand(100, 999),
                'kyc_status' => 'pending',
                'referral_code' => $user->referral_code,
            ]);

            // Create wallet with minimal balance
            Wallet::firstOrCreate([
                'user_id' => $user->id,
                'currency' => 'USDT',
            ], [
                'balance' => 50,
                'total_deposited' => $customerData['investment'],
                'total_withdrawn' => $customerData['investment'] - 50,
                'total_profit' => 0,
                'total_loss' => 0,
            ]);

            $this->command->info("Created inactive customer: {$user->email}");
        }
    }

    private function createCustomersWithDifferentKycStatus($basicPlan, $premiumPlan)
    {
        $this->command->info('Creating customers with different KYC statuses...');

        $kycCustomers = [
            [
                'name' => 'Frank Miller',
                'email' => 'frank.miller@example.com',
                'kyc_status' => 'pending',
                'plan' => $basicPlan,
                'investment' => 1000,
            ],
            [
                'name' => 'Grace Lee',
                'email' => 'grace.lee@example.com',
                'kyc_status' => 'rejected',
                'plan' => $basicPlan,
                'investment' => 500,
            ],
            [
                'name' => 'Henry Taylor',
                'email' => 'henry.taylor@example.com',
                'kyc_status' => 'approved',
                'plan' => $premiumPlan,
                'investment' => 5000,
            ],
        ];

        foreach ($kycCustomers as $customerData) {
            $user = User::firstOrCreate([
                'email' => $customerData['email']
            ], [
                'name' => $customerData['name'],
                'password' => Hash::make('password'),
                'user_type' => 'customer',
                'is_active' => true,
                'active_plan_id' => $customerData['plan']->id,
                'active_investment_amount' => $customerData['investment'],
            ]);

            $user->assignRole('customer');
            $user->generateReferralCode();

            // Create profile
            Profile::firstOrCreate([
                'user_id' => $user->id,
            ], [
                'first_name' => explode(' ', $customerData['name'])[0],
                'last_name' => explode(' ', $customerData['name'])[1],
                'phone' => '+1234567' . rand(100, 999),
                'kyc_status' => $customerData['kyc_status'],
                'referral_code' => $user->referral_code,
            ]);

            // Create wallet
            Wallet::firstOrCreate([
                'user_id' => $user->id,
                'currency' => 'USDT',
            ], [
                'balance' => $customerData['investment'] * 0.6,
                'total_deposited' => $customerData['investment'],
                'total_withdrawn' => 0,
                'total_profit' => $customerData['investment'] * 0.1,
                'total_loss' => 0,
            ]);

            $this->command->info("Created customer with {$customerData['kyc_status']} KYC: {$user->email}");
        }
    }

    private function createCustomersWithDifferentInvestmentLevels($basicPlan, $premiumPlan)
    {
        $this->command->info('Creating customers with different investment levels...');

        $investmentLevels = [
            ['min' => 100, 'max' => 500, 'count' => 5, 'plan' => $basicPlan],
            ['min' => 1000, 'max' => 2000, 'count' => 3, 'plan' => $basicPlan],
            ['min' => 3000, 'max' => 5000, 'count' => 2, 'plan' => $premiumPlan],
            ['min' => 6000, 'max' => 10000, 'count' => 2, 'plan' => $premiumPlan],
        ];

        foreach ($investmentLevels as $level) {
            for ($i = 1; $i <= $level['count']; $i++) {
                $investment = rand($level['min'], $level['max']);
                $name = "Customer_{$level['min']}_{$i}";
                $email = "customer_{$level['min']}_{$i}@example.com";

                $user = User::firstOrCreate([
                    'email' => $email
                ], [
                    'name' => $name,
                    'password' => Hash::make('password'),
                    'user_type' => 'customer',
                    'is_active' => true,
                    'active_plan_id' => $level['plan']->id,
                    'active_investment_amount' => $investment,
                ]);

                $user->assignRole('customer');
                $user->generateReferralCode();

                // Create profile
                Profile::firstOrCreate([
                    'user_id' => $user->id,
                ], [
                    'first_name' => $name,
                    'last_name' => 'User',
                    'phone' => '+1234567' . rand(100, 999),
                    'kyc_status' => 'approved',
                    'referral_code' => $user->referral_code,
                ]);

                // Create wallet
                Wallet::firstOrCreate([
                    'user_id' => $user->id,
                    'currency' => 'USDT',
                ], [
                    'balance' => $investment * 0.7,
                    'total_deposited' => $investment,
                    'total_withdrawn' => 0,
                    'total_profit' => $investment * 0.15,
                    'total_loss' => 0,
                ]);

                $this->command->info("Created customer with ${investment} investment: {$user->email}");
            }
        }
    }
}
