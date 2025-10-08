<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Plan;

class PlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $plans = [
            [
                'name' => 'Starter',
                'investment_amount' => 100.00,
                'joining_fee' => 5.00,
                'bots_allowed' => 1,
                'trades_per_day' => 3,
                'direct_bonus' => 1.50,
                'referral_level_1' => 3.00,
                'referral_level_2' => 2.00,
                'referral_level_3' => 1.00,
                'is_active' => true,
                'sort_order' => 1,
                'description' => 'Perfect for beginners to start their AI trading journey'
            ],
            [
                'name' => 'Bronze',
                'investment_amount' => 500.00,
                'joining_fee' => 25.00,
                'bots_allowed' => 2,
                'trades_per_day' => 5,
                'direct_bonus' => 7.50,
                'referral_level_1' => 4.00,
                'referral_level_2' => 2.00,
                'referral_level_3' => 1.00,
                'is_active' => true,
                'sort_order' => 2,
                'description' => 'Enhanced features with more trading opportunities'
            ],
            [
                'name' => 'Silver',
                'investment_amount' => 1000.00,
                'joining_fee' => 50.00,
                'bots_allowed' => 3,
                'trades_per_day' => 8,
                'direct_bonus' => 15.00,
                'referral_level_1' => 5.00,
                'referral_level_2' => 3.00,
                'referral_level_3' => 1.00,
                'is_active' => true,
                'sort_order' => 3,
                'description' => 'Professional level with increased trading capacity'
            ],
            [
                'name' => 'Gold',
                'investment_amount' => 5000.00,
                'joining_fee' => 125.00,
                'bots_allowed' => 5,
                'trades_per_day' => 12,
                'direct_bonus' => 37.50,
                'referral_level_1' => 6.00,
                'referral_level_2' => 3.00,
                'referral_level_3' => 2.00,
                'is_active' => true,
                'sort_order' => 4,
                'description' => 'Premium package with advanced AI trading capabilities'
            ],
            [
                'name' => 'Diamond',
                'investment_amount' => 10000.00,
                'joining_fee' => 250.00,
                'bots_allowed' => 8,
                'trades_per_day' => 15,
                'direct_bonus' => 75.00,
                'referral_level_1' => 7.00,
                'referral_level_2' => 4.00,
                'referral_level_3' => 2.00,
                'is_active' => true,
                'sort_order' => 5,
                'description' => 'Elite level with maximum trading potential'
            ],
            [
                'name' => 'Platinum',
                'investment_amount' => 25000.00,
                'joining_fee' => 625.00,
                'bots_allowed' => 12,
                'trades_per_day' => 20,
                'direct_bonus' => 187.50,
                'referral_level_1' => 8.00,
                'referral_level_2' => 4.00,
                'referral_level_3' => 2.00,
                'is_active' => true,
                'sort_order' => 6,
                'description' => 'VIP package for serious investors'
            ],
            [
                'name' => 'Elite',
                'investment_amount' => 50000.00,
                'joining_fee' => 1250.00,
                'bots_allowed' => 20,
                'trades_per_day' => 30,
                'direct_bonus' => 375.00,
                'referral_level_1' => 10.00,
                'referral_level_2' => 5.00,
                'referral_level_3' => 3.00,
                'is_active' => true,
                'sort_order' => 7,
                'description' => 'Ultimate package with maximum benefits and rewards'
            ]
        ];

        foreach ($plans as $plan) {
            Plan::create($plan);
        }
    }
}
