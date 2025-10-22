<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Run our trading app seeders
        $this->call([
            TradingAppSeeder::class,
            CustomerSeeder::class,
            ReferralTestSeeder::class,
            ComprehensiveReferralSeeder::class,
        ]);
    }

}
