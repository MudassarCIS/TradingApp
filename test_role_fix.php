<?php

require_once 'vendor/autoload.php';

use Illuminate\Foundation\Application;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Testing Role-Based Authentication Fix...\n\n";

try {
    // Test user with ID 5 (customer)
    $customer = App\Models\User::find(5);
    if ($customer) {
        echo "✅ Customer User: {$customer->name}\n";
        echo "📧 Email: {$customer->email}\n";
        echo "🔐 Roles: " . $customer->roles->pluck('name')->implode(', ') . "\n";
        echo "🔍 isCustomer(): " . ($customer->isCustomer() ? 'Yes' : 'No') . "\n";
        echo "🔍 isAdmin(): " . ($customer->isAdmin() ? 'Yes' : 'No') . "\n";
        echo "🔍 isStaff(): " . ($customer->isStaff() ? 'Yes' : 'No') . "\n";
        echo "🔍 hasRole('customer'): " . ($customer->hasRole('customer') ? 'Yes' : 'No') . "\n";
        echo "🔍 hasRole('admin'): " . ($customer->hasRole('admin') ? 'Yes' : 'No') . "\n";
        echo "\n";
    }
    
    // Test admin user (if exists)
    $admin = App\Models\User::whereHas('roles', function($q) {
        $q->where('name', 'admin');
    })->first();
    
    if ($admin) {
        echo "✅ Admin User: {$admin->name}\n";
        echo "📧 Email: {$admin->email}\n";
        echo "🔐 Roles: " . $admin->roles->pluck('name')->implode(', ') . "\n";
        echo "🔍 isAdmin(): " . ($admin->isAdmin() ? 'Yes' : 'No') . "\n";
        echo "🔍 isCustomer(): " . ($admin->isCustomer() ? 'Yes' : 'No') . "\n";
        echo "🔍 isStaff(): " . ($admin->isStaff() ? 'Yes' : 'No') . "\n";
        echo "🔍 hasRole('admin'): " . ($admin->hasRole('admin') ? 'Yes' : 'No') . "\n";
        echo "🔍 hasRole('customer'): " . ($admin->hasRole('customer') ? 'Yes' : 'No') . "\n";
        echo "\n";
    } else {
        echo "❌ No admin user found\n\n";
    }
    
    // Test authentication flow
    echo "🔍 Testing Authentication Flow:\n";
    
    // Test customer login
    if ($customer) {
        Auth::login($customer);
        echo "🔑 Logged in as customer\n";
        
        // Test customer dashboard access
        $response = app('router')->dispatch(
            Illuminate\Http\Request::create('/customer/dashboard', 'GET')
        );
        echo "🌐 Customer dashboard status: " . $response->getStatusCode() . "\n";
        
        // Test admin dashboard access (should be blocked)
        $response = app('router')->dispatch(
            Illuminate\Http\Request::create('/admin/dashboard', 'GET')
        );
        echo "🌐 Admin dashboard status: " . $response->getStatusCode() . "\n";
        
        Auth::logout();
    }
    
    // Test admin login
    if ($admin) {
        Auth::login($admin);
        echo "🔑 Logged in as admin\n";
        
        // Test admin dashboard access
        $response = app('router')->dispatch(
            Illuminate\Http\Request::create('/admin/dashboard', 'GET')
        );
        echo "🌐 Admin dashboard status: " . $response->getStatusCode() . "\n";
        
        // Test customer dashboard access (should be blocked)
        $response = app('router')->dispatch(
            Illuminate\Http\Request::create('/customer/dashboard', 'GET')
        );
        echo "🌐 Customer dashboard status: " . $response->getStatusCode() . "\n";
        
        Auth::logout();
    }
    
    echo "\n✅ Role-based authentication test completed!\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
