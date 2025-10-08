<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginRedirectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_redirects_to_customer_dashboard()
    {
        // Create a customer user
        $customer = User::factory()->create([
            'user_type' => 'customer',
            'email' => 'customer@test.com',
            'password' => bcrypt('password')
        ]);

        // Test login redirection
        $response = $this->post('/login', [
            'email' => 'customer@test.com',
            'password' => 'password'
        ]);

        $response->assertRedirect('/customer/dashboard');
    }

    public function test_admin_redirects_to_admin_dashboard()
    {
        // Create an admin user
        $admin = User::factory()->create([
            'user_type' => 'admin',
            'email' => 'admin@test.com',
            'password' => bcrypt('password')
        ]);

        // Test login redirection
        $response = $this->post('/login', [
            'email' => 'admin@test.com',
            'password' => 'password'
        ]);

        $response->assertRedirect('/admin/dashboard');
    }

    public function test_manager_redirects_to_admin_dashboard()
    {
        // Create a manager user
        $manager = User::factory()->create([
            'user_type' => 'manager',
            'email' => 'manager@test.com',
            'password' => bcrypt('password')
        ]);

        // Test login redirection
        $response = $this->post('/login', [
            'email' => 'manager@test.com',
            'password' => 'password'
        ]);

        $response->assertRedirect('/admin/dashboard');
    }

    public function test_customer_cannot_access_admin_panel()
    {
        // Create a customer user
        $customer = User::factory()->create([
            'user_type' => 'customer',
            'email' => 'customer@test.com',
            'password' => bcrypt('password')
        ]);

        // Login as customer
        $this->actingAs($customer);

        // Try to access admin panel
        $response = $this->get('/admin/dashboard');

        $response->assertRedirect('/customer/dashboard');
        $response->assertSessionHas('error', 'Access denied. You do not have permission to access the admin panel.');
    }

    public function test_admin_can_access_admin_panel()
    {
        // Create an admin user
        $admin = User::factory()->create([
            'user_type' => 'admin',
            'email' => 'admin@test.com',
            'password' => bcrypt('password')
        ]);

        // Login as admin
        $this->actingAs($admin);

        // Try to access admin panel
        $response = $this->get('/admin/dashboard');

        $response->assertStatus(200);
    }
}
