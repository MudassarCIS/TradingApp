<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\TradeApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;

class SocialLoginController extends Controller
{
    protected $tradeApiService;

    public function __construct(TradeApiService $tradeApiService)
    {
        $this->tradeApiService = $tradeApiService;
    }

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
            $isNewUser = false;

            if (!$user) {
                // Create new user
                $randomPassword = Str::random(16);
                $encryptedPassword = Crypt::encryptString($randomPassword);
                
                $user = User::create([
                    'name' => $googleUser['name'],
                    'email' => $googleUser['email'],
                    'password' => Hash::make($randomPassword),
                    'api_password' => $encryptedPassword,
                    'email_verified_at' => now(),
                    'user_type' => 'customer'
                ]);
                
                $user->assignRole('customer');
                $isNewUser = true;
            }

            // Call Trade API for customers
            if ($user->isCustomer()) {
                try {
                    Log::info('Social Login - Calling Trade API', [
                        'user_id' => $user->id,
                        'email' => $user->email,
                        'is_new_user' => $isNewUser,
                    ]);
                    
                    // Get password from stored api_password or use default
                    $password = null;
                    if ($user->api_password) {
                        try {
                            $password = Crypt::decryptString($user->api_password);
                        } catch (\Exception $e) {
                            $password = $user->api_password;
                        }
                    }
                    if (!$password) {
                        $password = 'Test@1234';
                    }
                    
                    if ($isNewUser) {
                        // Try to register new user
                        if ($this->tradeApiService->registerUser($user, $password)) {
                            $user->refresh();
                            Log::info('Social Login - Trade API Registration Successful', [
                                'user_id' => $user->id,
                                'email' => $user->email,
                                'has_api_token' => !empty($user->api_token),
                            ]);
                        } else {
                            // If registration fails, try login
                            if ($this->tradeApiService->loginUser($user, $password)) {
                                $user->refresh();
                                Log::info('Social Login - Trade API Login Successful', [
                                    'user_id' => $user->id,
                                    'email' => $user->email,
                                ]);
                            }
                        }
                    } else {
                        // Existing user - try to ensure token
                        if ($this->tradeApiService->ensureCustomerToken($user)) {
                            $user->refresh();
                            Log::info('Social Login - Trade API authentication successful', [
                                'user_id' => $user->id,
                                'email' => $user->email,
                                'has_api_token' => !empty($user->api_token),
                            ]);
                        }
                    }
                } catch (\Exception $e) {
                    Log::error('Social Login - Trade API Exception', [
                        'user_id' => $user->id,
                        'email' => $user->email,
                        'error' => $e->getMessage(),
                    ]);
                }
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
