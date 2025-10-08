<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Checking Customer Authentication...\n\n";

try {
    // Find customer user
    $customer = App\Models\User::find(5);
    
    if (!$customer) {
        echo "❌ Customer user not found\n";
        exit;
    }
    
    echo "✅ Customer: {$customer->name}\n";
    echo "📧 Email: {$customer->email}\n";
    echo "🔐 Roles: " . $customer->roles->pluck('name')->implode(', ') . "\n";
    echo "🔍 isCustomer(): " . ($customer->isCustomer() ? 'Yes' : 'No') . "\n";
    echo "🔍 hasRole('customer'): " . ($customer->hasRole('customer') ? 'Yes' : 'No') . "\n";
    
    // Test login
    Auth::login($customer);
    echo "🔑 Logged in as customer\n";
    
    // Test customer dashboard route
    $request = Illuminate\Http\Request::create('/customer/dashboard', 'GET');
    $request->setUserResolver(function () use ($customer) {
        return $customer;
    });
    
    $response = app('router')->dispatch($request);
    echo "🌐 Customer dashboard status: " . $response->getStatusCode() . "\n";
    
    if ($response->getStatusCode() !== 200) {
        echo "❌ Customer dashboard not accessible\n";
        echo "Response: " . substr($response->getContent(), 0, 200) . "...\n";
    } else {
        echo "✅ Customer dashboard accessible\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
