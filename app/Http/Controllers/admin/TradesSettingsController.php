<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Connector;
use App\Models\User;
use App\Services\TradeApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TradesSettingsController extends Controller
{
    protected $tradeApiService;

    public function __construct(TradeApiService $tradeApiService)
    {
        $this->tradeApiService = $tradeApiService;
    }

    /**
     * Display trades settings page with connectors list
     */
    public function index()
    {
        $connectors = Connector::orderBy('connector_name')->get();
        $lastSyncTime = Connector::whereNotNull('synced_at')
            ->orderBy('synced_at', 'desc')
            ->value('synced_at');

        return view('admin.trades-settings.index', compact('connectors', 'lastSyncTime'));
    }

    /**
     * Sync connectors from API
     */
    public function syncConnectors(Request $request)
    {
        try {
            // Get or create admin user for API authentication
            $adminUser = $this->getOrCreateAdminUser();

            // Ensure admin has valid token
            if (!$this->tradeApiService->ensureValidToken($adminUser)) {
                // Try to refresh token first
                if (!$this->tradeApiService->refreshToken($adminUser)) {
                    // If refresh fails, login admin with default password "Admin@1234"
                    $adminPassword = 'Admin@1234';
                    if (!$this->tradeApiService->loginUser($adminUser, $adminPassword)) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Failed to authenticate admin user for API access. Please ensure admin email and password "Admin@1234" are correct.',
                        ], 401);
                    }
                }
                $adminUser->refresh();
            }

            // Fetch connectors from API
            $connectors = $this->tradeApiService->getConnectors($adminUser);

            if (!$connectors || !is_array($connectors)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to fetch connectors from API or invalid response format',
                ], 500);
            }

            // Clear existing connectors (can't use truncate due to foreign key constraints)
            // Temporarily disable foreign key checks, truncate, then re-enable
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            Connector::truncate();
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');

            // Save all connectors to database
            $syncedCount = 0;
            foreach ($connectors as $connectorData) {
                try {
                    // Handle different response formats:
                    // 1. Simple array of strings: ["ndax", "bybit", ...]
                    // 2. Array of objects: [{"name": "ndax", "code": "ndax"}, ...]
                    if (is_string($connectorData)) {
                        // Simple string format - use as both name and code
                        $connectorName = $connectorData;
                        $connectorCode = $connectorData;
                    } elseif (is_array($connectorData)) {
                        // Object format
                        $connectorName = $connectorData['name'] ?? $connectorData['connector_name'] ?? $connectorData['code'] ?? 'Unknown';
                        $connectorCode = $connectorData['code'] ?? $connectorData['connector_code'] ?? $connectorData['id'] ?? $connectorName ?? uniqid();
                    } else {
                        // Skip invalid formats
                        continue;
                    }

                    Connector::create([
                        'connector_name' => $connectorName,
                        'connector_code' => $connectorCode,
                        'is_active' => true,
                        'synced_at' => now(),
                    ]);
                    $syncedCount++;
                } catch (\Exception $e) {
                    Log::error('Error saving connector', [
                        'connector_data' => $connectorData,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            Log::info('Connectors synced successfully', [
                'count' => $syncedCount,
                'admin_user_id' => $adminUser->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => "Successfully synced {$syncedCount} connectors",
                'count' => $syncedCount,
            ]);
        } catch (\Exception $e) {
            Log::error('Error syncing connectors', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to sync connectors: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update connector status (enable/disable)
     */
    public function updateConnectorStatus(Request $request, $id)
    {
        $request->validate([
            'is_active' => 'required|boolean',
        ]);

        try {
            $connector = Connector::findOrFail($id);
            $connector->update([
                'is_active' => $request->is_active,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Connector status updated successfully',
                'connector' => [
                    'id' => $connector->id,
                    'name' => $connector->connector_name,
                    'is_active' => $connector->is_active,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Error updating connector status', [
                'connector_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update connector status: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get or create admin user for API authentication
     */
    protected function getOrCreateAdminUser()
    {
        // Try to find admin user (prefer currently logged in admin, or any admin)
        $adminUser = Auth::user();
        
        // If current user is not admin, find any admin user
        if (!$adminUser || !$adminUser->hasRole('admin')) {
            $adminUser = User::whereHas('roles', function ($query) {
                $query->where('name', 'admin');
            })->first();
        }

        if (!$adminUser) {
            // Create admin user if doesn't exist (shouldn't happen, but handle it)
            $adminUser = User::create([
                'name' => 'Admin',
                'email' => config('mail.from.address', 'admin@example.com'),
                'password' => bcrypt('Admin@1234'),
                'user_type' => 'admin',
                'is_active' => true,
            ]);
            $adminUser->assignRole('admin');
        }

        return $adminUser;
    }
}
