<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Database\Seeders\ReferralTestSeeder;
use Database\Seeders\ComprehensiveReferralSeeder;
use Database\Seeders\CustomerSeeder;

class SetupReferralTestData extends Command
{
    protected $signature = 'referral:setup {--type=all} {--fresh}';
    protected $description = 'Setup referral test data. Options: basic, comprehensive, customers, all';

    public function handle()
    {
        $type = $this->option('type');
        $fresh = $this->option('fresh');

        if ($fresh) {
            $this->info('Running fresh migration...');
            $this->call('migrate:fresh');
        }

        $this->info("Setting up referral test data: {$type}");

        switch ($type) {
            case 'basic':
                $this->runBasicReferralSeeder();
                break;
            case 'comprehensive':
                $this->runComprehensiveReferralSeeder();
                break;
            case 'customers':
                $this->runCustomerSeeder();
                break;
            case 'all':
            default:
                $this->runAllSeeders();
                break;
        }

        $this->info('Referral test data setup completed!');
        $this->displayTestInstructions();
    }

    private function runBasicReferralSeeder()
    {
        $this->info('Running basic referral seeder...');
        $seeder = new ReferralTestSeeder();
        $seeder->setCommand($this);
        $seeder->run();
    }

    private function runComprehensiveReferralSeeder()
    {
        $this->info('Running comprehensive referral seeder...');
        $seeder = new ComprehensiveReferralSeeder();
        $seeder->setCommand($this);
        $seeder->run();
    }

    private function runCustomerSeeder()
    {
        $this->info('Running customer seeder...');
        $seeder = new CustomerSeeder();
        $seeder->setCommand($this);
        $seeder->run();
    }

    private function runAllSeeders()
    {
        $this->info('Running all referral seeders...');
        $this->runCustomerSeeder();
        $this->runBasicReferralSeeder();
        $this->runComprehensiveReferralSeeder();
    }

    private function displayTestInstructions()
    {
        $this->info("\n=== TESTING INSTRUCTIONS ===");
        $this->info("1. Test referral system with a specific user:");
        $this->info("   php artisan test:referral-system --user-email=main@test.com --amount=2000");
        $this->info("\n2. Test with VIP referrer:");
        $this->info("   php artisan test:referral-system --user-email=vip.referrer@test.com --amount=5000");
        $this->info("\n3. Check referral relationships:");
        $this->info("   php artisan tinker");
        $this->info("   >>> \$user = App\Models\User::where('email', 'main@test.com')->first();");
        $this->info("   >>> \$user->referredUsers;");
        $this->info("   >>> \$user->referrals;");
        $this->info("\n4. Check commission distribution:");
        $this->info("   >>> \$referrals = App\Models\Referral::with(['referrer', 'referred'])->get();");
        $this->info("   >>> \$referrals->each(function(\$r) { echo \$r->referrer->name . ' -> ' . \$r->referred->name . ' (' . \$r->commission_rate . '%)\n'; });");
        $this->info("\n5. All test users have password: password");
    }
}
