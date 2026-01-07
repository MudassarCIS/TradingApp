<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Services\ExternalApiService;
use App\Services\TradeApiService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    protected $tradeApiService;

    public function __construct(TradeApiService $tradeApiService)
    {
        $this->tradeApiService = $tradeApiService;
    }

    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        // Redirect based on user role
        $user = Auth::user();
        
        // Update last login time
        $user->update(['last_login_at' => now()]);
        
        // Call trade API to login/register and update api_token for customers
        if ($user->isCustomer()) {
            try {
                Log::info('User Login - Calling Trade API', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                ]);
                
                // Get password from stored api_password or use default
                $password = null;
                if ($user->api_password) {
                    try {
                        $password = \Illuminate\Support\Facades\Crypt::decryptString($user->api_password);
                    } catch (\Exception $e) {
                        $password = $user->api_password;
                    }
                }
                if (!$password) {
                    $password = 'Test@1234';
                }
                
                // Try to ensure customer token (will login or register)
                if ($this->tradeApiService->ensureCustomerToken($user)) {
                    $user->refresh();
                    Log::info('User Login - Trade API authentication successful', [
                        'user_id' => $user->id,
                        'email' => $user->email,
                        'has_api_token' => !empty($user->api_token),
                    ]);
                } else {
                    Log::warning('User Login - Trade API authentication failed', [
                        'user_id' => $user->id,
                        'email' => $user->email,
                    ]);
                }
            } catch (\Exception $e) {
                // Log error but don't fail login
                Log::error('User Login - Trade API Exception', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
            }
        }
        
        if ($user->isCustomer()) {
            // Only customers go to customer dashboard
            return redirect()->intended(route('customer.dashboard'));
        } else {
            // All non-customer roles (admin, manager, moderator) go to admin panel
            return redirect()->intended(route('admin.dashboard'));
        }
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $user = Auth::user();
        
        // Call external API to logout user
        if ($user) {
            try {
                Log::info('User Logout - Calling 3rd Party API', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                ]);
                
                $apiService = new ExternalApiService($user);
                $apiResponse = $apiService->logoutUser();
                
                if ($apiResponse && isset($apiResponse['success']) && $apiResponse['success']) {
                    Log::info('User Logout - 3rd Party API Logout Successful', [
                        'user_id' => $user->id,
                        'email' => $user->email,
                    ]);
                } else {
                    Log::warning('User Logout - 3rd Party API Logout Failed', [
                        'user_id' => $user->id,
                        'email' => $user->email,
                        'response' => $apiResponse,
                        'error_message' => $apiResponse['message'] ?? 'Logout failed',
                    ]);
                }
            } catch (\Exception $e) {
                // Log error but don't fail logout
                Log::error('User Logout - 3rd Party API Exception', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
            }
            
            // For customers, clear trade API token on logout
            if ($user->isCustomer()) {
                try {
                    Log::info('User Logout - Clearing Trade API token', [
                        'user_id' => $user->id,
                        'email' => $user->email,
                    ]);
                    
                    // Clear tokens on logout
                    $user->update([
                        'api_token' => null,
                        'refresh_token' => null,
                    ]);
                    
                    Log::info('User Logout - Trade API tokens cleared', [
                        'user_id' => $user->id,
                        'email' => $user->email,
                    ]);
                } catch (\Exception $e) {
                    Log::error('User Logout - Failed to clear Trade API tokens', [
                        'user_id' => $user->id,
                        'email' => $user->email,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }
        
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
