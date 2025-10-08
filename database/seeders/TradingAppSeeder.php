<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Profile;
use App\Models\Wallet;
use App\Models\Package;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Hash;

class TradingAppSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create roles
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $customerRole = Role::firstOrCreate(['name' => 'customer']);
        
        // Create permissions
        $permissions = [
            'manage_users',
            'manage_trades',
            'manage_agents',
            'manage_wallets',
            'manage_transactions',
            'manage_packages',
            'view_reports',
            'manage_support',
        ];
        
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }
        
        // Assign permissions to admin role
        $adminRole->givePermissionTo($permissions);
        
        // Create admin user
        $admin = User::firstOrCreate(
            ['email' => 'admin@aitradeapp.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('password'),
                'user_type' => 'admin',
                'is_active' => true,
                'referral_code' => 'ADMIN001',
            ]
        );
        
        $admin->assignRole('admin');
        
        // Create admin profile
        Profile::firstOrCreate(
            ['user_id' => $admin->id],
            [
                'first_name' => 'Admin',
                'last_name' => 'User',
                'phone' => '+1234567890',
                'kyc_status' => 'approved',
                'referral_code' => 'ADMIN001',
            ]
        );
        
        // Create sample customer
        $customer = User::firstOrCreate(
            ['email' => 'customer@example.com'],
            [
                'name' => 'John Doe',
                'password' => Hash::make('password'),
                'user_type' => 'customer',
                'is_active' => true,
                'referral_code' => 'JOHN001',
            ]
        );
        
        $customer->assignRole('customer');

        // Create manager user
        $manager = User::firstOrCreate(
            ['email' => 'manager@aitradeapp.com'],
            [
                'name' => 'Manager User',
                'password' => Hash::make('password'),
                'user_type' => 'manager',
                'is_active' => true,
                'referral_code' => 'MGR001',
            ]
        );
        
        $manager->assignRole('admin'); // Assign admin role to manager for now
        
        // Create customer profile
        Profile::firstOrCreate(
            ['user_id' => $customer->id],
            [
                'first_name' => 'John',
                'last_name' => 'Doe',
                'phone' => '+1234567891',
                'kyc_status' => 'pending',
                'referral_code' => 'JOHN001',
                'transaction_password' => Hash::make('123456'),
            ]
        );
        
        // Create wallet for customer
        Wallet::firstOrCreate(
            ['user_id' => $customer->id, 'currency' => 'USDT'],
            [
                'balance' => 1000.00,
                'total_deposited' => 1000.00,
            ]
        );
        
        // Create sample packages
        $packages = [
            [
                'name' => 'Starter Package',
                'description' => 'Perfect for beginners',
                'price' => 100.00,
                'min_investment' => 100.00,
                'max_investment' => 500.00,
                'daily_return_rate' => 2.5,
                'duration_days' => 30,
                'profit_share' => 50.0,
                'features' => ['Basic AI trading', 'Email support', 'Mobile app access'],
            ],
            [
                'name' => 'Professional Package',
                'description' => 'For serious traders',
                'price' => 500.00,
                'min_investment' => 500.00,
                'max_investment' => 2000.00,
                'daily_return_rate' => 3.5,
                'duration_days' => 30,
                'profit_share' => 50.0,
                'features' => ['Advanced AI trading', 'Priority support', 'Custom strategies', 'Real-time alerts'],
            ],
            [
                'name' => 'Premium Package',
                'description' => 'Maximum returns',
                'price' => 1000.00,
                'min_investment' => 1000.00,
                'max_investment' => 5000.00,
                'daily_return_rate' => 5.0,
                'duration_days' => 30,
                'profit_share' => 50.0,
                'features' => ['Premium AI trading', '24/7 support', 'Custom strategies', 'Real-time alerts', 'Personal manager'],
            ],
        ];
        
        foreach ($packages as $packageData) {
            Package::create($packageData);
        }
        
        $this->command->info('Trading app data seeded successfully!');
        $this->command->info('Admin: admin@aitradeapp.com / password');
        $this->command->info('Customer: customer@example.com / password');
    }
}
