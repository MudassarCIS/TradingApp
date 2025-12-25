<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Referral;
use App\Services\ExternalApiService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'referral_code' => ['nullable', 'string'],
            'referred_by' => ['nullable', 'exists:users,id'],
        ]);

        // Resolve referral code if provided
        if (!empty($data['referral_code']) && empty($data['referred_by'])) {
            $refUser = User::where('referral_code', $data['referral_code'])->first();
            if ($refUser) {
                $data['referred_by'] = $refUser->id;
            }
        }

        // Encrypt password for API use
        $encryptedPassword = Crypt::encryptString($data['password']);
        
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'api_password' => $encryptedPassword, // Encrypted password for API login
            'user_type' => 'customer',
            'is_active' => true,
            'referred_by' => $data['referred_by'] ?? null,
        ]);

        // Create referral relationship if referred_by is present
        if (!empty($data['referred_by'])) {
            Referral::create([
                'referrer_id' => $data['referred_by'],
                'referred_id' => $user->id,
                'commission_rate' => 0, // will be set per plan at time of investment
                'status' => 'active',
                'joined_at' => now()
            ]);
        }

        // Assign customer role
        $user->assignRole('customer');

        event(new Registered($user));

        // Call external API to register user and save tokens
        try {
            Log::info('User Registration - Calling 3rd Party API', [
                'user_id' => $user->id,
                'email' => $user->email,
                'name' => $user->name,
            ]);
            
            $apiService = new ExternalApiService($user);
            $apiResponse = $apiService->registerUser([
                'email' => $user->email,
                'password' => $data['password'], // Send plain password for registration
                'name' => $user->name,
                // hb_master_password will be set to same as password in ExternalApiService
            ]);
            
            // Tokens are automatically saved by the service if registration is successful
            if ($apiResponse && isset($apiResponse['success']) && $apiResponse['success']) {
                Log::info('User Registration - 3rd Party API Registration Successful', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'has_token' => isset($apiResponse['data']['token']) || isset($apiResponse['data']['access_token']),
                ]);
            } else {
                // Handle both null response (exception occurred) and failed response
                $errorMessage = 'Unknown error';
                if ($apiResponse === null) {
                    $errorMessage = 'API call failed - check logs for details (likely connection error or invalid API URL)';
                } elseif (isset($apiResponse['message'])) {
                    $errorMessage = $apiResponse['message'];
                } elseif (isset($apiResponse['error'])) {
                    $errorMessage = $apiResponse['error'];
                }
                
                Log::warning('User Registration - 3rd Party API Registration Failed', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'response' => $apiResponse,
                    'error_message' => $errorMessage,
                    'note' => $apiResponse === null ? 'API returned null - check ExternalApiService logs for exception details' : 'API returned failure response',
                ]);
            }
        } catch (\Exception $e) {
            // Log error but don't fail registration
            Log::error('User Registration - 3rd Party API Exception', [
                'user_id' => $user->id,
                'email' => $user->email,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }

        Auth::login($user);

        // Redirect based on user role (newly registered users are always customers)
        return redirect()->intended(route('customer.dashboard'));
    }
}
