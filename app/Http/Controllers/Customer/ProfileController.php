<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Connector;
use App\Models\TradeCredential;
use App\Services\TradeServerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class ProfileController extends Controller
{
    protected $tradeServerService;

    public function __construct(TradeServerService $tradeServerService)
    {
        $this->tradeServerService = $tradeServerService;
    }

    public function index()
    {
        $user = Auth::user();
        $tradeCredentials = $user->tradeCredentials()->with('connector')->get();
        return view('customer.profile.index', compact('user', 'tradeCredentials'));
    }
    
    public function edit()
    {
        $user = Auth::user();
        return view('customer.profile.edit', compact('user'));
    }
    
    public function update(Request $request)
    {
        $user = Auth::user();
        
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'current_password' => 'required_with:password|current_password',
            'password' => 'nullable|string|min:8|confirmed',
        ]);
        
        $user->update([
            'name' => $request->name,
            'email' => $request->email,
        ]);
        
        if ($request->password) {
            $user->update([
                'password' => Hash::make($request->password),
            ]);
        }
        
        return redirect()->route('customer.profile.index')
            ->with('success', 'Profile updated successfully!');
    }

    /**
     * Get connectors list (from cache or API)
     */
    public function getConnectors()
    {
        try {
            // First, try to get from cache (database)
            $connectors = Connector::where('is_active', true)->get();

            // If cache is empty or stale (older than 1 hour), fetch from API
            if ($connectors->isEmpty() || 
                ($connectors->first() && $connectors->first()->synced_at && 
                 $connectors->first()->synced_at->diffInHours(now()) > 1)) {
                
                $apiConnectors = $this->tradeServerService->getConnectors();
                
                if ($apiConnectors && is_array($apiConnectors)) {
                    // Clear old connectors
                    Connector::truncate();
                    
                    // Save new connectors
                    foreach ($apiConnectors as $connectorData) {
                        Connector::create([
                            'connector_name' => $connectorData['name'] ?? $connectorData['connector_name'] ?? 'Unknown',
                            'connector_code' => $connectorData['code'] ?? $connectorData['connector_code'] ?? $connectorData['id'] ?? uniqid(),
                            'is_active' => $connectorData['is_active'] ?? true,
                            'synced_at' => now(),
                        ]);
                    }
                    
                    $connectors = Connector::where('is_active', true)->get();
                }
            }

            return response()->json([
                'success' => true,
                'connectors' => $connectors->map(function ($connector) {
                    return [
                        'id' => $connector->id,
                        'name' => $connector->connector_name,
                        'code' => $connector->connector_code,
                    ];
                }),
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching connectors', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch connectors',
            ], 500);
        }
    }

    /**
     * Save account to trade server and local database
     */
    public function saveAccount(Request $request)
    {
        $request->validate([
            'account_name' => 'required|string|max:255',
        ]);

        $user = Auth::user();
        $accountName = $request->account_name;

        try {
            // Step 1: Save account to trade server
            $serverResponse = $this->tradeServerService->addAccount($accountName);
            
            $activeCredentials = $serverResponse['success'] ? 1 : 0;

            // Step 2: Save to local database (we'll update with connector info later)
            $tradeCredential = TradeCredential::create([
                'user_id' => $user->id,
                'account_name' => $accountName,
                'connector_id' => null, // Will be updated when connector is saved
                'api_key' => '',
                'secret_key' => '',
                'active_credentials' => $activeCredentials,
            ]);

            return response()->json([
                'success' => true,
                'message' => $serverResponse['success'] 
                    ? 'Account saved successfully' 
                    : 'Account saved locally but failed on server',
                'credential_id' => $tradeCredential->id,
                'active_credentials' => $activeCredentials,
                'server_response' => $serverResponse,
            ]);
        } catch (\Exception $e) {
            Log::error('Error saving account', [
                'error' => $e->getMessage(),
                'account_name' => $accountName,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to save account: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Save connector with keys to trade server and update local database
     */
    public function saveConnector(Request $request)
    {
        $request->validate([
            'credential_id' => 'required|exists:trade_credentials,id',
            'connector_id' => 'required|exists:connectors,id',
            'api_key' => 'required|string',
            'secret_key' => 'required|string',
        ]);

        $user = Auth::user();
        $credentialId = $request->credential_id;
        $connectorId = $request->connector_id;
        $apiKey = $request->api_key;
        $secretKey = $request->secret_key;

        try {
            // Get the trade credential
            $tradeCredential = TradeCredential::where('id', $credentialId)
                ->where('user_id', $user->id)
                ->firstOrFail();

            $connector = Connector::findOrFail($connectorId);

            // Step 1: Save connector with keys to trade server
            $serverResponse = $this->tradeServerService->saveConnectorWithKeys(
                $tradeCredential->account_name,
                $connectorId,
                $apiKey,
                $secretKey
            );

            $activeCredentials = $serverResponse['success'] ? 1 : 0;

            // Step 2: Update local database
            $tradeCredential->update([
                'connector_id' => $connectorId,
                'api_key' => $apiKey,
                'secret_key' => $secretKey,
                'active_credentials' => $activeCredentials,
            ]);

            return response()->json([
                'success' => true,
                'message' => $serverResponse['success'] 
                    ? 'Connector saved successfully' 
                    : 'Connector saved locally but failed on server',
                'active_credentials' => $activeCredentials,
                'server_response' => $serverResponse,
            ]);
        } catch (\Exception $e) {
            Log::error('Error saving connector', [
                'error' => $e->getMessage(),
                'credential_id' => $credentialId,
                'connector_id' => $connectorId,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to save connector: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get user's trade credentials
     */
    public function getTradeCredentials()
    {
        $user = Auth::user();
        $credentials = $user->tradeCredentials()->with('connector')->get();

        return response()->json([
            'success' => true,
            'credentials' => $credentials->map(function ($credential) {
                return [
                    'id' => $credential->id,
                    'account_name' => $credential->account_name,
                    'connector_name' => $credential->connector->connector_name ?? 'N/A',
                    'api_key' => substr($credential->api_key, 0, 8) . '...', // Masked
                    'active_credentials' => $credential->active_credentials,
                    'created_at' => $credential->created_at->format('Y-m-d H:i:s'),
                ];
            }),
        ]);
    }
}
