<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class SocialLoginController extends Controller
{
    /**
     * Redirect to Google OAuth
     */
    public function redirectToGoogle()
    {
        // For now, we'll simulate Google OAuth
        // In production, you would use Laravel Socialite
        return redirect()->route('google.callback');
    }

    /**
     * Handle Google OAuth callback
     */
    public function handleGoogleCallback(Request $request)
    {
        try {
            // Simulate Google user data
            // In production, you would get this from Google OAuth
            $googleUser = [
                'id' => 'google_' . Str::random(10),
                'name' => 'Google User',
                'email' => 'user@gmail.com',
                'avatar' => 'https://via.placeholder.com/150'
            ];

            // Check if user exists
            $user = User::where('email', $googleUser['email'])->first();

            if (!$user) {
                // Create new user
                $user = User::create([
                    'name' => $googleUser['name'],
                    'email' => $googleUser['email'],
                    'password' => Hash::make(Str::random(16)), // Random password for social users
                    'email_verified_at' => now(),
                    'user_type' => 'customer'
                ]);
            }

            // Login the user
            Auth::login($user);

            return redirect()->intended(route('dashboard'))
                ->with('success', 'Successfully logged in with Google!');

        } catch (\Exception $e) {
            return redirect()->route('login')
                ->with('error', 'Failed to login with Google. Please try again.');
        }
    }
}
