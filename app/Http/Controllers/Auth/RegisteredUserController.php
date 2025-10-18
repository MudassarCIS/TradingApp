<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Referral;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
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

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
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

        Auth::login($user);

        // Redirect based on user role (newly registered users are always customers)
        return redirect()->intended(route('customer.dashboard'));
    }
}
