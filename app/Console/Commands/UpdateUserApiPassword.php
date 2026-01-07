<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Crypt;

class UpdateUserApiPassword extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:update-api-password {email} {password}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update user API password for trade API authentication';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');
        $password = $this->argument('password');

        $user = User::where('email', $email)->first();

        if (!$user) {
            $this->error("User with email '{$email}' not found.");
            return 1;
        }

        // Encrypt the password
        $encryptedPassword = Crypt::encryptString($password);

        $user->update([
            'api_password' => $encryptedPassword,
        ]);

        $this->info("Successfully updated API password for user: {$email}");
        $this->info("Password has been encrypted and stored.");

        return 0;
    }
}
