<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Services\ExternalApiService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
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
        }
        
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
