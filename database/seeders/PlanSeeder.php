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
                'investment_amount' => 100,
                'joining_fee' => 10,
                'bots_allowed' => 1,
                'trades_per_day' => 5,
                'direct_bonus' => 5,
                'referral_level_1' => 5,
                'referral_level_2' => 3,
                'referral_level_3' => 2,
                'is_active' => true,
                'sort_order' => 1,
                'description' => 'Perfect for beginners'
            ],
            [
                'name' => 'Bronze',
                'investment_amount' => 500,
                'joining_fee' => 25,
                'bots_allowed' => 2,
                'trades_per_day' => 10,
                'direct_bonus' => 25,
                'referral_level_1' => 7,
                'referral_level_2' => 5,
                'referral_level_3' => 3,
                'is_active' => true,
                'sort_order' => 2,
                'description' => 'Great for regular investors'
            ],
            [
                'name' => 'Silver',
                'investment_amount' => 1000,
                'joining_fee' => 50,
                'bots_allowed' => 3,
                'trades_per_day' => 15,
                'direct_bonus' => 50,
                'referral_level_1' => 10,
                'referral_level_2' => 7,
                'referral_level_3' => 5,
                'is_active' => true,
                'sort_order' => 3,
                'description' => 'Ideal for serious investors'
            ],
            [
                'name' => 'Gold',
                'investment_amount' => 2500,
                'joining_fee' => 125,
                'bots_allowed' => 5,
                'trades_per_day' => 25,
                'direct_bonus' => 125,
                'referral_level_1' => 12,
                'referral_level_2' => 10,
                'referral_level_3' => 7,
                'is_active' => true,
                'sort_order' => 4,
                'description' => 'Premium investment package'
            ],
            [
                'name' => 'Diamond',
                'investment_amount' => 5000,
                'joining_fee' => 250,
                'bots_allowed' => 8,
                'trades_per_day' => 40,
                'direct_bonus' => 250,
                'referral_level_1' => 15,
                'referral_level_2' => 12,
                'referral_level_3' => 10,
                'is_active' => true,
                'sort_order' => 5,
                'description' => 'Elite investment opportunity'
            ],
            [
                'name' => 'Platinum',
                'investment_amount' => 10000,
                'joining_fee' => 500,
                'bots_allowed' => 12,
                'trades_per_day' => 60,
                'direct_bonus' => 500,
                'referral_level_1' => 18,
                'referral_level_2' => 15,
                'referral_level_3' => 12,
                'is_active' => true,
                'sort_order' => 6,
                'description' => 'VIP investment experience'
            ],
            [
                'name' => 'Elite',
                'investment_amount' => 25000,
                'joining_fee' => 1250,
                'bots_allowed' => 20,
                'trades_per_day' => 100,
                'direct_bonus' => 1250,
                'referral_level_1' => 20,
                'referral_level_2' => 18,
                'referral_level_3' => 15,
                'is_active' => true,
                'sort_order' => 7,
                'description' => 'Ultimate investment package'
            ]
        ];

        foreach ($plans as $plan) {
            Plan::create($plan);
        }
    }
}