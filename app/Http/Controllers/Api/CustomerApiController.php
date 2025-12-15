<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Carbon\Carbon;

class CustomerApiController extends Controller
{
    /**
     * Get customer details including name, email, active packages, allowed bots, and status
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCustomerDetails(Request $request)
    {
        try {
            // Validate that either customer_id or email is provided
            $validated = $request->validate([
                'customer_id' => 'nullable|integer|exists:users,id',
                'email' => 'nullable|email|exists:users,email'
            ], [
                'customer_id.exists' => 'Customer with the provided ID does not exist.',
                'email.exists' => 'Customer with the provided email does not exist.',
                'email.email' => 'Please provide a valid email address.'
            ]);

            // Check if at least one parameter is provided
            if (empty($validated['customer_id']) && empty($validated['email'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please provide either customer_id or email parameter.'
                ], 400);
            }

            // Find the customer by ID or email
            if (!empty($validated['customer_id'])) {
                $user = User::find($validated['customer_id']);
            } else {
                $user = User::where('email', $validated['email'])->first();
            }

            // Check if user exists
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Customer not found.'
                ], 404);
            }

            // Check if user is a customer
            if (!$user->isCustomer()) {
                return response()->json([
                    'success' => false,
                    'message' => 'The provided ID does not belong to a customer.'
                ], 403);
            }

            // Check if customer is disabled by admin
            if (!$user->is_active) {
                return response()->json([
                    'success' => false,
                    'message' => 'Customer is disabled. Please contact with admin.',
                    'error' => 'customer_disabled'
                ], 403);
            }

            // Get active packages with allowed bots details
            $activePackages = $user->getActivePackages();

            // Format packages with bot details and check expiration
            $formattedPackages = $activePackages->map(function ($package) use ($user) {
                $packageCreatedAt = $package['created_at'];
                $now = Carbon::now();
                
                // Check if package has completed 1 month (exactly 1 month from creation)
                $expirationDate = $packageCreatedAt->copy()->addMonth();
                $isExpired = $now->greaterThanOrEqualTo($expirationDate);
                $daysSinceCreation = $packageCreatedAt->diffInDays($now);
                
                // Determine package status
                $packageStatus = $isExpired ? 'expired' : 'active';
                
                // Get actual bots (agents) for this package
                $bots = $user->agents()
                    ->where('status', 'active')
                    ->get()
                    ->map(function ($bot) {
                        return [
                            'id' => $bot->id,
                            'name' => $bot->name,
                            'description' => $bot->description,
                            'status' => $bot->status,
                            'strategy' => $bot->strategy,
                            'initial_balance' => $bot->initial_balance,
                            'current_balance' => $bot->current_balance,
                            'total_profit' => $bot->total_profit,
                            'total_loss' => $bot->total_loss,
                            'win_rate' => $bot->win_rate,
                            'total_trades' => $bot->total_trades,
                            'winning_trades' => $bot->winning_trades,
                            'losing_trades' => $bot->losing_trades,
                            'auto_trading' => $bot->auto_trading,
                            'last_trade_at' => $bot->last_trade_at ? $bot->last_trade_at->toDateTimeString() : null,
                        ];
                    });

                return [
                    'id' => $package['id'],
                    'type' => $package['type'],
                    'title' => $package['title'],
                    'plan_name' => $package['plan_name'],
                    'allowed_bots' => $package['available_bots'],
                    'status' => $packageStatus,
                    'is_expired' => $isExpired,
                    'days_since_creation' => $daysSinceCreation,
                    'expires_at' => $expirationDate->toDateTimeString(),
                    'created_at' => $packageCreatedAt->toDateTimeString(),
                    'bots' => $bots,
                    'plan_details' => $package['plan_details'],
                ];
            });

            // Filter out expired packages from active count (optional - you can keep them if needed)
            $activePackagesCount = $formattedPackages->where('status', 'active')->count();
            $expiredPackagesCount = $formattedPackages->where('status', 'expired')->count();

            // Return customer details
            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'status' => 'active',
                    'is_active' => true,
                    'active_packages' => $formattedPackages,
                    'total_active_packages' => $activePackagesCount,
                    'total_expired_packages' => $expiredPackagesCount,
                    'total_packages' => $formattedPackages->count(),
                    'created_at' => $user->created_at->toDateTimeString(),
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while fetching customer details.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
}
