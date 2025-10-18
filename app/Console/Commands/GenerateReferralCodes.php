<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class GenerateReferralCodes extends Command
{
    protected $signature = 'referrals:generate-codes';
    protected $description = 'Generate referral codes for users who don\'t have them';

    public function handle()
    {
        $usersWithoutCodes = User::whereNull('referral_code')->orWhere('referral_code', '')->get();
        
        $this->info("Found {$usersWithoutCodes->count()} users without referral codes");
        
        $bar = $this->output->createProgressBar($usersWithoutCodes->count());
        $bar->start();
        
        foreach ($usersWithoutCodes as $user) {
            $user->generateReferralCode();
            $bar->advance();
        }
        
        $bar->finish();
        $this->newLine();
        $this->info('Referral codes generated successfully!');
        
        return 0;
    }
}

