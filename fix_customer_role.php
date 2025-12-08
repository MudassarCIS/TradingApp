<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Cache;

echo "🔧 Fixing Customer Role Issues...\n\n";

try {
    // Clear permission cache
    echo "1️⃣ Clearing permission cache...\n";
    Cache::forget('spatie.permission.cache');
    app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    echo "✅ Permission cache cleared\n\n";
    
    // Ensure customer role exists
    echo "2️⃣ Checking customer role exists...\n";
    $customerRole = Role::firstOrCreate(['name' => 'customer']);
    echo "✅ Customer role exists: {$customerRole->name}\n\n";
    
    // Get all users
    $users = User::all();
    echo "3️⃣ Checking and fixing user roles...\n";
    $fixed = 0;
    $alreadyHasRole = 0;
    
    foreach ($users as $user) {
        // Check if user has customer role
        if (!$user->hasRole('customer')) {
            // Check if user_type is customer
            if ($user->user_type === 'customer') {
                echo "   ⚠️  User #{$user->id} ({$user->email}) has user_type='customer' but missing role. Fixing...\n";
                $user->assignRole('customer');
                $fixed++;
            } else {
                // Check if user doesn't have admin/manager/moderator roles
                if (!$user->hasAnyRole(['admin', 'manager', 'moderator'])) {
                    echo "   ⚠️  User #{$user->id} ({$user->email}) has no roles. Assigning customer role...\n";
                    $user->assignRole('customer');
                    $fixed++;
                }
            }
        } else {
            $alreadyHasRole++;
        }
    }
    
    echo "\n✅ Fixed {$fixed} users\n";
    echo "✅ {$alreadyHasRole} users already have customer role\n\n";
    
    // Test a customer user
    echo "4️⃣ Testing customer role check...\n";
    $testCustomer = User::whereHas('roles', function($query) {
        $query->where('name', 'customer');
    })->first();
    
    if ($testCustomer) {
        echo "   ✅ Found customer: {$testCustomer->email}\n";
        echo "   🔍 hasRole('customer'): " . ($testCustomer->hasRole('customer') ? 'Yes ✅' : 'No ❌') . "\n";
        echo "   🔍 isCustomer(): " . ($testCustomer->isCustomer() ? 'Yes ✅' : 'No ❌') . "\n";
        echo "   🔐 Roles: " . $testCustomer->roles->pluck('name')->implode(', ') . "\n";
    } else {
        echo "   ⚠️  No customer users found\n";
    }
    
    echo "\n✅ Role fix completed!\n";
    echo "\n💡 If you're still getting 403 errors, try:\n";
    echo "   1. Logout and login again\n";
    echo "   2. Clear browser cache\n";
    echo "   3. Run: php artisan permission:cache-reset\n";
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

