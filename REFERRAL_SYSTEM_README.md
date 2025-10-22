# Referral System Testing Guide

This guide explains how to set up and test the multi-level referral system in the Trading App.

## Overview

The referral system includes:
- **Multi-level referral structure** (up to 4 levels)
- **Commission distribution** based on plan levels
- **Customer seeders** with various scenarios
- **Test commands** for validation

## Quick Start

### 1. Run the Seeders

```bash
# Run all seeders (recommended)
php artisan db:seed

# Or run specific seeders
php artisan referral:setup --type=all
php artisan referral:setup --type=basic
php artisan referral:setup --type=comprehensive
php artisan referral:setup --type=customers
```

### 2. Test the Referral System

```bash
# Test with main referrer
php artisan test:referral-system --user-email=main@test.com --amount=2000

# Test with VIP referrer
php artisan test:referral-system --user-email=vip.referrer@test.com --amount=5000

# Test with any user
php artisan test:referral-system --user-email=level1_1@test.com --amount=1500
```

### 3. Run the Test Script

```bash
php test_referral_system.php
```

## Created Seeders

### 1. CustomerSeeder
Creates various customer scenarios:
- **Active customers** with different investment levels
- **Inactive customers** for testing edge cases
- **Different KYC statuses** (approved, pending, rejected)
- **Various investment amounts** (100-10000 USDT)

### 2. ReferralTestSeeder (Enhanced)
Creates a 4-level referral structure:
- **Main referrer** with premium plan
- **Level 1**: 3 users with premium plans
- **Level 2**: 2 users per level 1 user
- **Level 3**: 1 user per level 2 user
- **Level 4**: 1 user per level 3 user

### 3. ComprehensiveReferralSeeder
Creates a realistic multi-level structure:
- **VIP referrer** at the top
- **Level 1**: 5 high-performing users
- **Level 2**: 3-4 users per level 1
- **Level 3**: 2-3 users per level 2
- **Level 4**: 1-2 users per level 3
- **Test deposits** for commission testing

## Test Users Created

### Main Test Users
- `main@test.com` - Main referrer (Basic structure)
- `vip.referrer@test.com` - VIP referrer (Comprehensive structure)
- `level1_1@test.com` to `level1_3@test.com` - Level 1 referrals
- `level2_1_1@test.com` to `level2_2_2@test.com` - Level 2 referrals
- And more...

### Customer Test Users
- `alice.johnson@example.com` - Active customer
- `bob.smith@example.com` - Premium customer
- `david.wilson@example.com` - Inactive customer
- `frank.miller@example.com` - Pending KYC
- `grace.lee@example.com` - Rejected KYC
- And more...

**Password for all test users: `password`**

## Commission Structure

### Plan-Based Commissions
- **Starter Plan**: Level 1: 10%, Level 2: 5%, Level 3: 2%
- **Professional Plan**: Level 1: 15%, Level 2: 8%, Level 3: 3%
- **VIP Plan**: Level 1: 20%, Level 2: 10%, Level 3: 5%

### Investment Levels
- **Level 1**: $5000 investment
- **Level 2**: $3000 investment
- **Level 3**: $1500 investment
- **Level 4**: $1000 investment

## Testing Commands

### 1. Setup Commands
```bash
# Setup all referral data
php artisan referral:setup --type=all

# Setup with fresh migration
php artisan referral:setup --type=all --fresh

# Setup specific type
php artisan referral:setup --type=basic
php artisan referral:setup --type=comprehensive
php artisan referral:setup --type=customers
```

### 2. Test Commands
```bash
# Test referral system
php artisan test:referral-system --user-email=main@test.com --amount=2000

# Test with different amounts
php artisan test:referral-system --user-email=level1_1@test.com --amount=5000
```

## Database Structure

### Key Tables
- `users` - User accounts with referral relationships
- `referrals` - Referral relationships and commissions
- `plans` - Investment plans with commission rates
- `wallets` - User wallet balances
- `deposits` - Deposit transactions for testing
- `profiles` - User profile information

### Key Relationships
- `users.referred_by` - Points to parent referrer
- `referrals.referrer_id` - Points to referrer
- `referrals.referred_id` - Points to referred user
- `users.active_plan_id` - User's current plan

## Manual Testing

### 1. Check Referral Chain
```php
// In tinker
$user = App\Models\User::where('email', 'main@test.com')->first();
$user->referredUsers; // Direct referrals
$user->referredBy; // Parent referrer
```

### 2. Check Commissions
```php
$referrals = App\Models\Referral::with(['referrer', 'referred'])->get();
$referrals->each(function($r) {
    echo $r->referrer->name . ' -> ' . $r->referred->name . ' (' . $r->commission_rate . '%)\n';
});
```

### 3. Check Wallet Balances
```php
$wallets = App\Models\Wallet::with('user')->where('currency', 'USDT')->get();
$wallets->each(function($w) {
    echo $w->user->name . ': ' . $w->balance . ' USDT\n';
});
```

## Troubleshooting

### Common Issues
1. **Users not found**: Run the seeders first
2. **No commissions**: Check if deposits exist and referral service is called
3. **Wrong commission rates**: Verify plan configuration
4. **Missing relationships**: Check referral table data

### Reset Data
```bash
# Fresh start
php artisan migrate:fresh --seed

# Or reset specific seeder
php artisan referral:setup --type=all --fresh
```

## Features Tested

✅ Multi-level referral structure (4 levels)
✅ Commission distribution based on plans
✅ User roles and permissions
✅ Wallet balance updates
✅ Profile creation
✅ KYC status variations
✅ Investment level variations
✅ Active/inactive user scenarios
✅ Referral code generation
✅ Deposit simulation
✅ Commission calculation

## Next Steps

1. **Test commission distribution** with real deposits
2. **Verify wallet updates** after commission distribution
3. **Test edge cases** (inactive users, rejected KYC)
4. **Performance testing** with large referral trees
5. **Integration testing** with the main application
