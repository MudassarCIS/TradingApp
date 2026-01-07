<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Trade;
use App\Models\Agent;
use App\Models\TradeCredential;
use App\Models\Connector;
use App\Services\ExternalApiService;
use App\Services\TradeApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class TradingController extends Controller
{
    protected $tradeApiService;

    public function __construct(TradeApiService $tradeApiService)
    {
        $this->tradeApiService = $tradeApiService;
    }

    public function index()
    {
        $user = Auth::user();
        
        // Fetch trade history from external API
        $this->syncTradeHistory($user);
        
        // Get trades from database
        $trades = $user->trades()
            ->with('agent')
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        
        // Get active packages (with paid invoices)
        $activePackages = $user->getActivePackages();
        
        // Check if user has NEXA packages (not PEXA)
        $hasNexaPackage = $activePackages->contains(function ($package) {
            return $package['type'] === 'NEXA';
        });
        
        return view('customer.trading.index', compact('trades', 'activePackages', 'hasNexaPackage'));
    }

    /**
     * Show save credentials page
     */
    public function saveCredentials()
    {
        $user = Auth::user();
        
        // Ensure user has valid token using full authentication flow
        if (!$this->tradeApiService->ensureCustomerToken($user)) {
            // Log error but continue - token will be retried on API calls
            Log::warning('Failed to ensure customer token', ['user_id' => $user->id]);
        }
        $user->refresh();
        
        $tradeCredentials = $user->tradeCredentials()->with('connector')->get();
        
        return view('customer.trading.save-credentials', compact('user', 'tradeCredentials'));
    }

    /**
     * Store trade credentials (account + connector + keys)
     */
    public function storeCredentials(Request $request)
    {
        $request->validate([
            'account_name' => 'required|string|max:255',
            'connector_id' => 'required|exists:connectors,id',
            'api_key' => 'required|string',
            'secret_key' => 'required|string',
        ]);

        $user = Auth::user();
        $accountName = $request->account_name;
        $connectorId = $request->connector_id;
        $apiKey = $request->api_key;
        $secretKey = $request->secret_key;

        try {
            Log::info('storeCredentials: Starting credential storage', [
                'user_id' => $user->id,
                'account_name' => $accountName,
                'connector_id' => $connectorId,
            ]);

            // Ensure user has valid token using full authentication flow
            Log::info('storeCredentials: Ensuring customer token', [
                'user_id' => $user->id,
                'current_token_exists' => !empty($user->api_token),
            ]);
            
            if (!$this->tradeApiService->ensureCustomerToken($user)) {
                Log::error('storeCredentials: Authentication failed', [
                    'user_id' => $user->id,
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to authenticate. Please try again.',
                ], 401);
            }
            $user->refresh();
            
            Log::info('storeCredentials: Authentication successful', [
                'user_id' => $user->id,
                'token_preview' => substr($user->api_token ?? '', 0, 20) . '...',
            ]);

            // Step 1: Check if account exists in API by fetching all accounts
            Log::info('storeCredentials: Fetching accounts from API', [
                'user_id' => $user->id,
            ]);
            $accountsResponse = $this->tradeApiService->getAccounts($user);
            
            Log::info('storeCredentials: Accounts response received', [
                'user_id' => $user->id,
                'success' => $accountsResponse['success'] ?? false,
                'http_code' => $accountsResponse['http_code'] ?? null,
                'has_data' => isset($accountsResponse['data']),
            ]);
            
            $accountExistsInAPI = false;
            
            if ($accountsResponse['success'] && isset($accountsResponse['data'])) {
                $accounts = $accountsResponse['data'];
                // Handle different response formats
                if (is_array($accounts)) {
                    foreach ($accounts as $account) {
                        // Check if account is a string or object
                        $accountNameFromAPI = is_string($account) ? $account : ($account['name'] ?? $account['account_name'] ?? null);
                        if ($accountNameFromAPI === $accountName) {
                            $accountExistsInAPI = true;
                            break;
                        }
                    }
                }
            }

            // Step 2: Check if account already exists in local DB for this user
            $existingCredential = TradeCredential::where('user_id', $user->id)
                ->where('account_name', $accountName)
                ->first();

            // Step 3: Handle different scenarios
            if ($accountExistsInAPI && $existingCredential) {
                // Account exists in both API and local DB - already saved
                return response()->json([
                    'success' => false,
                    'message' => 'This account name is already saved. Please use a different account name.',
                    'already_exists' => true,
                ], 400);
            }

            $accountCreatedInAPI = false;
            if ($accountExistsInAPI && !$existingCredential) {
                // Account exists in API but not in local DB - just save to local DB
                // Don't call create account API
                $accountCreatedInAPI = true; // Account already exists
            } elseif (!$accountExistsInAPI && !$existingCredential) {
                // Account doesn't exist in API or local DB - create it via API first
                Log::info('storeCredentials: Creating new account via API', [
                    'user_id' => $user->id,
                    'account_name' => $accountName,
                ]);
                
                // Refresh token before creating account
                $user->refresh();
                $apiResponse = $this->tradeApiService->addAccount($user, $accountName);
                
                Log::info('storeCredentials: Add account API response', [
                    'user_id' => $user->id,
                    'account_name' => $accountName,
                    'success' => $apiResponse['success'] ?? false,
                    'http_code' => $apiResponse['http_code'] ?? null,
                    'response_data' => $apiResponse['data'] ?? null,
                    'error' => $apiResponse['error'] ?? null,
                ]);
                
                if ($apiResponse['success']) {
                    $accountCreatedInAPI = true;
                } else {
                    // Check if it's a token error - if so, try to re-authenticate and retry once
                    if (($apiResponse['http_code'] ?? 0) === 401) {
                        Log::warning('storeCredentials: Got 401 on account creation, re-authenticating', [
                            'user_id' => $user->id,
                            'account_name' => $accountName,
                        ]);
                        
                        // Clear token and force re-authentication
                        $user->update(['api_token' => null]);
                        $user->refresh();
                        
                        if ($this->tradeApiService->ensureCustomerToken($user)) {
                            $user->refresh();
                            // Retry account creation
                            $apiResponse = $this->tradeApiService->addAccount($user, $accountName);
                            if ($apiResponse['success']) {
                                $accountCreatedInAPI = true;
                            }
                        }
                    }
                    
                    if (!$accountCreatedInAPI) {
                        Log::error('storeCredentials: Failed to create account', [
                            'user_id' => $user->id,
                            'account_name' => $accountName,
                            'response' => $apiResponse,
                        ]);
                        return response()->json([
                            'success' => false,
                            'message' => 'Failed to create account on trade server: ' . ($apiResponse['data']['detail'] ?? $apiResponse['data']['message'] ?? 'Unknown error'),
                        ], 500);
                    }
                }
            } else {
                $accountCreatedInAPI = true; // Account already exists
            }

            // Step 4: Get connector
            $connector = Connector::findOrFail($connectorId);
            $connectorName = $connector->connector_name;

            // Step 5: Check if credentials already exist for this account and connector
            Log::info('storeCredentials: Checking existing credentials', [
                'user_id' => $user->id,
                'account_name' => $accountName,
                'connector_name' => $connectorName,
            ]);
            
            $existingCredentialsResponse = $this->tradeApiService->getAccountCredentials($user, $accountName);
            $credentialsExistInAPI = false;
            
            if ($existingCredentialsResponse['success'] && isset($existingCredentialsResponse['data'])) {
                $credentials = $existingCredentialsResponse['data'];
                Log::info('storeCredentials: Existing credentials response received', [
                    'user_id' => $user->id,
                    'account_name' => $accountName,
                    'credentials_data' => is_array($credentials) ? $credentials : 'not_array',
                ]);
                
                // Check if credentials exist for this connector
                if (is_array($credentials)) {
                    foreach ($credentials as $cred) {
                        // Handle different response formats
                        $credConnectorName = null;
                        if (is_array($cred)) {
                            $credConnectorName = $cred['connector'] ?? $cred['connector_name'] ?? $cred['name'] ?? null;
                        } elseif (is_string($cred)) {
                            // If it's just a string, it might be the connector name
                            $credConnectorName = $cred;
                        }
                        
                        if ($credConnectorName === $connectorName) {
                            $credentialsExistInAPI = true;
                            Log::info('storeCredentials: Credentials already exist for this connector', [
                                'user_id' => $user->id,
                                'account_name' => $accountName,
                                'connector_name' => $connectorName,
                            ]);
                            break;
                        }
                    }
                }
            } else {
                Log::warning('storeCredentials: Failed to fetch existing credentials, will attempt to add credentials', [
                    'user_id' => $user->id,
                    'account_name' => $accountName,
                    'response' => $existingCredentialsResponse,
                ]);
            }

            // Step 6: Add credentials to API if they don't exist
            $credentialsSavedToAPI = false;
            if (!$credentialsExistInAPI) {
                Log::info('storeCredentials: Adding credentials to API', [
                    'user_id' => $user->id,
                    'account_name' => $accountName,
                    'connector_name' => $connectorName,
                ]);
                
                // Refresh token before adding credentials
                $user->refresh();
                $addCredentialResponse = $this->tradeApiService->addCredential($user, $accountName, $connectorName, $apiKey, $secretKey);
                
                Log::info('storeCredentials: Add credential API response', [
                    'user_id' => $user->id,
                    'account_name' => $accountName,
                    'connector_name' => $connectorName,
                    'success' => $addCredentialResponse['success'] ?? false,
                    'http_code' => $addCredentialResponse['http_code'] ?? null,
                    'response_data' => $addCredentialResponse['data'] ?? null,
                    'error' => $addCredentialResponse['error'] ?? null,
                ]);
                
                if ($addCredentialResponse['success']) {
                    $credentialsSavedToAPI = true;
                } else {
                    // Check if it's a token error - if so, try to re-authenticate and retry once
                    if (($addCredentialResponse['http_code'] ?? 0) === 401) {
                        Log::warning('storeCredentials: Got 401 on credential save, re-authenticating', [
                            'user_id' => $user->id,
                            'account_name' => $accountName,
                        ]);
                        
                        // Clear token and force re-authentication
                        $user->update(['api_token' => null]);
                        $user->refresh();
                        
                        if ($this->tradeApiService->ensureCustomerToken($user)) {
                            $user->refresh();
                            // Retry credential save
                            $addCredentialResponse = $this->tradeApiService->addCredential($user, $accountName, $connectorName, $apiKey, $secretKey);
                            if ($addCredentialResponse['success']) {
                                $credentialsSavedToAPI = true;
                            }
                        }
                    }
                    
                    if (!$credentialsSavedToAPI) {
                        Log::error('storeCredentials: Failed to add credentials to API', [
                            'user_id' => $user->id,
                            'account_name' => $accountName,
                            'connector_name' => $connectorName,
                            'response' => $addCredentialResponse,
                        ]);
                        
                        // If account was created but credentials failed, save account to DB and show appropriate message
                        if ($accountCreatedInAPI) {
                            // Save account to local DB even though credentials failed
                            if (!$existingCredential) {
                                $credential = TradeCredential::create([
                                    'user_id' => $user->id,
                                    'account_name' => $accountName,
                                    'connector_name' => $connectorName,
                                    'api_key' => $apiKey,
                                    'secret_key' => $secretKey,
                                    'active_credentials' => false, // Mark as inactive since API save failed
                                    'credential_type' => 'NEXA',
                                    'credential_priority' => 'none',
                                ]);
                                
                                Log::info('storeCredentials: Account saved to DB but credentials failed on API', [
                                    'user_id' => $user->id,
                                    'account_name' => $accountName,
                                    'credential_id' => $credential->id,
                                ]);
                            }
                            
                            return response()->json([
                                'success' => false,
                                'message' => 'Account saved successfully, but failed to save credentials to trade server. Please try saving credentials again.',
                                'account_saved' => true,
                                'credentials_failed' => true,
                                'error_detail' => $addCredentialResponse['data']['detail'] ?? $addCredentialResponse['data']['message'] ?? 'Unknown error',
                            ], 200); // Return 200 so frontend can handle it gracefully
                        } else {
                            return response()->json([
                                'success' => false,
                                'message' => 'Failed to save credentials to trade server: ' . ($addCredentialResponse['data']['detail'] ?? $addCredentialResponse['data']['message'] ?? 'Unknown error'),
                            ], 500);
                        }
                    }
                }
            } else {
                Log::info('storeCredentials: Credentials already exist in API, skipping API call', [
                    'user_id' => $user->id,
                    'account_name' => $accountName,
                    'connector_name' => $connectorName,
                ]);
                $credentialsSavedToAPI = true; // Credentials already exist
            }

            // Step 7: Save or update trade credential in local database
            if ($existingCredential) {
                // Update existing credential
                $existingCredential->update([
                    'connector_name' => $connectorName,
                    'api_key' => $apiKey,
                    'secret_key' => $secretKey,
                    'active_credentials' => true,
                    'credential_type' => 'NEXA',
                ]);
                
                $credential = $existingCredential;
                $message = 'Credentials updated successfully';
            } else {
                // Create new credential
                $credential = TradeCredential::create([
                    'user_id' => $user->id,
                    'account_name' => $accountName,
                    'connector_name' => $connectorName,
                    'api_key' => $apiKey,
                    'secret_key' => $secretKey,
                    'active_credentials' => true,
                    'credential_type' => 'NEXA',
                    'credential_priority' => 'none',
                ]);
                $message = 'Credentials saved successfully';
            }

            Log::info('Trade credentials saved', [
                'user_id' => $user->id,
                'account_name' => $accountName,
                'connector_name' => $connectorName,
                'account_existed_in_api' => $accountExistsInAPI,
            ]);

            return response()->json([
                'success' => true,
                'message' => $message,
                'credential' => [
                    'id' => $credential->id,
                    'account_name' => $credential->account_name,
                    'connector_name' => $connector->connector_name,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Error saving trade credentials', [
                'error' => $e->getMessage(),
                'account_name' => $accountName,
                'user_id' => $user->id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to save credentials: ' . $e->getMessage(),
            ], 500);
        }
    }
    
    /**
     * Display the Trades Dashboard with embedded external page
     */
    public function dashboard()
    {
        // Get dashboard URL based on environment
        $env = app()->environment();
        $dashboardUrls = config('trading.dashboard_urls', []);
        
        // Use environment-specific URL if available, otherwise fall back to default
        $dashboardUrl = $dashboardUrls[$env] ?? config('trading.dashboard_url', 'http://165.22.59.174:5173/');
        
        // Ensure URL ends with /
        if (!str_ends_with($dashboardUrl, '/')) {
            $dashboardUrl .= '/';
        }
        
        return view('customer.trading.dashboard', [
            'dashboardUrl' => $dashboardUrl
        ]);
    }
    
    /**
     * Sync trade history from external API
     */
    protected function syncTradeHistory($user)
    {
        try {
            // Initialize API service with user for token management
            $apiService = new ExternalApiService($user);
            $apiResponse = $apiService->getTradingHistory($user->id);
            
            if ($apiResponse && isset($apiResponse['success']) && $apiResponse['success'] && isset($apiResponse['data'])) {
                $trades = $apiResponse['data'];
                
                foreach ($trades as $tradeData) {
                    // Check if trade exists
                    $trade = Trade::where('trade_id', $tradeData['trade_id'] ?? $tradeData['id'] ?? null)
                        ->orWhere('exchange_order_id', $tradeData['exchange_order_id'] ?? null)
                        ->first();
                    
                    if (!$trade) {
                        // Create new trade
                        Trade::create([
                            'user_id' => $user->id,
                            'agent_id' => $tradeData['agent_id'] ?? null,
                            'trade_id' => $tradeData['trade_id'] ?? $tradeData['id'] ?? null,
                            'symbol' => $tradeData['symbol'] ?? 'BTCUSDT',
                            'side' => $tradeData['side'] ?? 'buy',
                            'type' => $tradeData['type'] ?? 'MARKET',
                            'quantity' => $tradeData['quantity'] ?? 0,
                            'price' => $tradeData['price'] ?? 0,
                            'stop_price' => $tradeData['stop_price'] ?? null,
                            'executed_quantity' => $tradeData['executed_quantity'] ?? $tradeData['quantity'] ?? 0,
                            'average_price' => $tradeData['average_price'] ?? $tradeData['price'] ?? 0,
                            'commission' => $tradeData['commission'] ?? 0,
                            'status' => $tradeData['status'] ?? 'pending',
                            'time_in_force' => $tradeData['time_in_force'] ?? 'GTC',
                            'profit_loss' => $tradeData['profit_loss'] ?? 0,
                            'profit_loss_percentage' => $tradeData['profit_loss_percentage'] ?? 0,
                            'exchange' => $tradeData['exchange'] ?? 'binance',
                            'exchange_order_id' => $tradeData['exchange_order_id'] ?? null,
                            'opened_at' => isset($tradeData['opened_at']) ? Carbon::parse($tradeData['opened_at']) : now(),
                            'closed_at' => isset($tradeData['closed_at']) ? Carbon::parse($tradeData['closed_at']) : null,
                            'notes' => $tradeData['notes'] ?? null,
                            'metadata' => $tradeData['metadata'] ?? [],
                        ]);
                    } else {
                        // Update existing trade if status changed
                        $updateData = [];
                        if (isset($tradeData['status']) && $trade->status !== $tradeData['status']) {
                            $updateData['status'] = $tradeData['status'];
                        }
                        if (isset($tradeData['profit_loss']) && $trade->profit_loss != $tradeData['profit_loss']) {
                            $updateData['profit_loss'] = $tradeData['profit_loss'];
                        }
                        if (isset($tradeData['profit_loss_percentage']) && $trade->profit_loss_percentage != $tradeData['profit_loss_percentage']) {
                            $updateData['profit_loss_percentage'] = $tradeData['profit_loss_percentage'];
                        }
                        if (isset($tradeData['closed_at']) && !$trade->closed_at) {
                            $updateData['closed_at'] = Carbon::parse($tradeData['closed_at']);
                        }
                        
                        if (!empty($updateData)) {
                            $trade->update($updateData);
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error('Failed to sync trade history: ' . $e->getMessage());
        }
    }
    
    /**
     * Start a new trade
     */
    public function startTrade(Request $request)
    {
        $request->validate([
            'symbol' => 'required|string',
            'side' => 'required|in:buy,sell',
            'quantity' => 'required|numeric|min:0.0001',
            'type' => 'nullable|string',
        ]);
        
        $user = Auth::user();
        
        // Initialize API service with user for token management
        $this->apiService = new ExternalApiService($user);
        
        try {
            $tradeData = [
                'symbol' => $request->symbol,
                'side' => $request->side,
                'quantity' => $request->quantity,
                'type' => $request->type ?? 'MARKET',
            ];
            
            $apiResponse = $this->apiService->startTrade($user->id, $tradeData);
            
            if ($apiResponse && isset($apiResponse['success']) && $apiResponse['success']) {
                // Sync trade history to update local database
                $this->syncTradeHistory($user);
                
                return response()->json([
                    'success' => true,
                    'message' => 'Trade started successfully',
                    'data' => $apiResponse['data'] ?? null
                ]);
            }
            
            return response()->json([
                'success' => false,
                'message' => $apiResponse['message'] ?? 'Failed to start trade'
            ], 400);
            
        } catch (\Exception $e) {
            Log::error('Failed to start trade: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while starting the trade'
            ], 500);
        }
    }
    
    /**
     * Close an existing trade
     */
    public function closeTrade(Request $request, $tradeId)
    {
        $user = Auth::user();
        
        // Initialize API service with user for token management
        $this->apiService = new ExternalApiService($user);
        
        try {
            $apiResponse = $this->apiService->closeTrade($user->id, $tradeId);
            
            if ($apiResponse && isset($apiResponse['success']) && $apiResponse['success']) {
                // Sync trade history to update local database
                $this->syncTradeHistory($user);
                
                return response()->json([
                    'success' => true,
                    'message' => 'Trade closed successfully',
                    'data' => $apiResponse['data'] ?? null
                ]);
            }
            
            return response()->json([
                'success' => false,
                'message' => $apiResponse['message'] ?? 'Failed to close trade'
            ], 400);
            
        } catch (\Exception $e) {
            Log::error('Failed to close trade: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while closing the trade'
            ], 500);
        }
    }

    /**
     * Update trade credentials
     */
    public function updateCredentials(Request $request, $id)
    {
        $request->validate([
            'account_name' => 'required|string|max:255',
            'connector_id' => 'required|exists:connectors,id',
            'api_key' => 'required|string',
            'secret_key' => 'required|string',
        ]);

        $user = Auth::user();
        
        try {
            $credential = TradeCredential::where('id', $id)
                ->where('user_id', $user->id)
                ->firstOrFail();

            $connector = Connector::findOrFail($request->connector_id);

            $connector = Connector::findOrFail($request->connector_id);
            
            $credential->update([
                'account_name' => $request->account_name,
                'connector_name' => $connector->connector_name,
                'api_key' => $request->api_key,
                'secret_key' => $request->secret_key,
            ]);

            Log::info('Trade credentials updated', [
                'user_id' => $user->id,
                'credential_id' => $credential->id,
                'account_name' => $request->account_name,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Credentials updated successfully',
                'credential' => [
                    'id' => $credential->id,
                    'account_name' => $credential->account_name,
                    'connector_name' => $connector->connector_name,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Error updating trade credentials', [
                'error' => $e->getMessage(),
                'credential_id' => $id,
                'user_id' => $user->id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update credentials: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete trade credentials
     */
    public function deleteCredentials($id)
    {
        $user = Auth::user();
        
        try {
            $credential = TradeCredential::where('id', $id)
                ->where('user_id', $user->id)
                ->firstOrFail();

            $accountName = $credential->account_name;
            $connectorName = $credential->connector_name;
            
            Log::info('deleteCredentials: Starting deletion process', [
                'user_id' => $user->id,
                'credential_id' => $id,
                'account_name' => $accountName,
                'connector_name' => $connectorName,
            ]);

            // Step 1: Ensure user has valid token
            if (!$this->tradeApiService->ensureCustomerToken($user)) {
                Log::warning('deleteCredentials: Authentication failed, proceeding with DB deletion only', [
                    'user_id' => $user->id,
                    'account_name' => $accountName,
                ]);
            } else {
                $user->refresh();
                
                // Step 2: Delete credentials from trade server API first
                // POST /accounts/delete-credential/{account_name}/{connector_name}
                if ($connectorName) {
                    Log::info('deleteCredentials: Deleting credentials from trade server', [
                        'user_id' => $user->id,
                        'account_name' => $accountName,
                        'connector_name' => $connectorName,
                    ]);
                    
                    $deleteCredentialResponse = $this->tradeApiService->deleteCredential($user, $accountName, $connectorName);
                    
                    Log::info('deleteCredentials: Delete credential API response', [
                        'user_id' => $user->id,
                        'account_name' => $accountName,
                        'connector_name' => $connectorName,
                        'success' => $deleteCredentialResponse['success'] ?? false,
                        'http_code' => $deleteCredentialResponse['http_code'] ?? null,
                        'response_data' => $deleteCredentialResponse['data'] ?? null,
                        'error' => $deleteCredentialResponse['error'] ?? null,
                    ]);
                    
                    // Log warning if API deletion failed, but continue with DB deletion
                    if (!$deleteCredentialResponse['success']) {
                        Log::warning('deleteCredentials: Failed to delete credentials from trade server, but continuing with DB deletion', [
                            'user_id' => $user->id,
                            'account_name' => $accountName,
                            'connector_name' => $connectorName,
                            'api_response' => $deleteCredentialResponse,
                        ]);
                    }
                }
                
                // Step 3: Delete account from trade server API
                Log::info('deleteCredentials: Deleting account from trade server', [
                    'user_id' => $user->id,
                    'account_name' => $accountName,
                ]);
                
                $deleteAccountResponse = $this->tradeApiService->deleteAccount($user, $accountName);
                
                Log::info('deleteCredentials: Delete account API response', [
                    'user_id' => $user->id,
                    'account_name' => $accountName,
                    'success' => $deleteAccountResponse['success'] ?? false,
                    'http_code' => $deleteAccountResponse['http_code'] ?? null,
                    'response_data' => $deleteAccountResponse['data'] ?? null,
                    'error' => $deleteAccountResponse['error'] ?? null,
                ]);
                
                // Log warning if API deletion failed, but continue with DB deletion
                if (!$deleteAccountResponse['success']) {
                    Log::warning('deleteCredentials: Failed to delete account from trade server, but continuing with DB deletion', [
                        'user_id' => $user->id,
                        'account_name' => $accountName,
                        'api_response' => $deleteAccountResponse,
                    ]);
                }
            }

            // Step 4: Delete from local database
            $credential->delete();

            Log::info('Trade credentials deleted', [
                'user_id' => $user->id,
                'credential_id' => $id,
                'account_name' => $accountName,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Credentials deleted successfully',
            ]);
        } catch (\Exception $e) {
            Log::error('Error deleting trade credentials', [
                'error' => $e->getMessage(),
                'credential_id' => $id,
                'user_id' => $user->id,
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete credentials: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Toggle credential status (active/inactive)
     */
    public function toggleStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|boolean',
        ]);

        $user = Auth::user();
        $status = $request->status;
        
        try {
            $credential = TradeCredential::where('id', $id)
                ->where('user_id', $user->id)
                ->firstOrFail();

            $credential->update([
                'active_credentials' => $status,
            ]);

            Log::info('Trade credential status updated', [
                'user_id' => $user->id,
                'credential_id' => $credential->id,
                'status' => $status ? 'active' : 'inactive',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Status updated successfully',
                'status' => $status,
            ]);
        } catch (\Exception $e) {
            Log::error('Error updating credential status', [
                'error' => $e->getMessage(),
                'credential_id' => $id,
                'user_id' => $user->id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update status: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Sync credentials with trade server API
     * Verifies account and credentials exist on server, creates missing ones
     */
    public function syncCredentials($id)
    {
        $user = Auth::user();
        
        try {
            $credential = TradeCredential::where('id', $id)
                ->where('user_id', $user->id)
                ->firstOrFail();

            $accountName = $credential->account_name;
            $connectorName = $credential->connector_name;
            $apiKey = $credential->api_key;
            $secretKey = $credential->secret_key;

            Log::info('syncCredentials: Starting sync', [
                'user_id' => $user->id,
                'credential_id' => $id,
                'account_name' => $accountName,
                'connector_name' => $connectorName,
            ]);

            // Ensure user has valid token
            if (!$this->tradeApiService->ensureCustomerToken($user)) {
                Log::error('syncCredentials: Authentication failed', [
                    'user_id' => $user->id,
                    'credential_id' => $id,
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to authenticate. Please try again.',
                ], 401);
            }
            $user->refresh();

            $syncResults = [
                'account_exists' => false,
                'account_created' => false,
                'credentials_exist' => false,
                'credentials_created' => false,
            ];

            // Step 1: Check if account exists
            Log::info('syncCredentials: Checking if account exists', [
                'user_id' => $user->id,
                'account_name' => $accountName,
            ]);

            $accountsResponse = $this->tradeApiService->getAccounts($user);
            $accountExists = false;

            if ($accountsResponse['success'] && isset($accountsResponse['data'])) {
                $accounts = $accountsResponse['data'];
                if (is_array($accounts)) {
                    foreach ($accounts as $account) {
                        $accountNameFromAPI = is_string($account) ? $account : ($account['name'] ?? $account['account_name'] ?? null);
                        if ($accountNameFromAPI === $accountName) {
                            $accountExists = true;
                            break;
                        }
                    }
                }
            }

            $syncResults['account_exists'] = $accountExists;

            // Step 2: Create account if it doesn't exist
            if (!$accountExists) {
                Log::info('syncCredentials: Account does not exist, creating', [
                    'user_id' => $user->id,
                    'account_name' => $accountName,
                ]);

                $addAccountResponse = $this->tradeApiService->addAccount($user, $accountName);
                
                if ($addAccountResponse['success']) {
                    $syncResults['account_created'] = true;
                    Log::info('syncCredentials: Account created successfully', [
                        'user_id' => $user->id,
                        'account_name' => $accountName,
                    ]);
                } else {
                    Log::warning('syncCredentials: Failed to create account', [
                        'user_id' => $user->id,
                        'account_name' => $accountName,
                        'response' => $addAccountResponse,
                    ]);
                }
            }

            // Step 3: Check if credentials exist for this connector using API
            if ($accountExists || $syncResults['account_created']) {
                Log::info('syncCredentials: Checking if credentials exist via API', [
                    'user_id' => $user->id,
                    'account_name' => $accountName,
                    'connector_name' => $connectorName,
                ]);

                // Call API to get credentials: GET /accounts/{account_name}/credentials
                $existingCredentialsResponse = $this->tradeApiService->getAccountCredentials($user, $accountName);
                $credentialsExist = false;

                Log::info('syncCredentials: Credentials API response', [
                    'user_id' => $user->id,
                    'account_name' => $accountName,
                    'success' => $existingCredentialsResponse['success'] ?? false,
                    'http_code' => $existingCredentialsResponse['http_code'] ?? null,
                    'has_data' => isset($existingCredentialsResponse['data']),
                ]);

                if ($existingCredentialsResponse['success'] && isset($existingCredentialsResponse['data'])) {
                    $credentials = $existingCredentialsResponse['data'];
                    if (is_array($credentials)) {
                        foreach ($credentials as $cred) {
                            $credConnectorName = null;
                            if (is_array($cred)) {
                                $credConnectorName = $cred['connector'] ?? $cred['connector_name'] ?? $cred['name'] ?? null;
                            } elseif (is_string($cred)) {
                                $credConnectorName = $cred;
                            }
                            
                            if ($credConnectorName === $connectorName) {
                                $credentialsExist = true;
                                Log::info('syncCredentials: Credentials found for connector', [
                                    'user_id' => $user->id,
                                    'account_name' => $accountName,
                                    'connector_name' => $connectorName,
                                ]);
                                break;
                            }
                        }
                    }
                }

                $syncResults['credentials_exist'] = $credentialsExist;

                // Step 4: Create credentials if they don't exist using API
                // POST /accounts/add-credential/{account_name}/{connector_name}
                if (!$credentialsExist && $apiKey && $secretKey && $connectorName) {
                    Log::info('syncCredentials: Credentials do not exist, creating via API', [
                        'user_id' => $user->id,
                        'account_name' => $accountName,
                        'connector_name' => $connectorName,
                    ]);

                    $addCredentialResponse = $this->tradeApiService->addCredential($user, $accountName, $connectorName, $apiKey, $secretKey);
                    
                    Log::info('syncCredentials: Add credential API response', [
                        'user_id' => $user->id,
                        'account_name' => $accountName,
                        'connector_name' => $connectorName,
                        'success' => $addCredentialResponse['success'] ?? false,
                        'http_code' => $addCredentialResponse['http_code'] ?? null,
                        'response_data' => $addCredentialResponse['data'] ?? null,
                    ]);
                    
                    if ($addCredentialResponse['success']) {
                        $syncResults['credentials_created'] = true;
                        // Update local credential status
                        $credential->update(['active_credentials' => true]);
                        Log::info('syncCredentials: Credentials created successfully', [
                            'user_id' => $user->id,
                            'account_name' => $accountName,
                            'connector_name' => $connectorName,
                        ]);
                    } else {
                        Log::warning('syncCredentials: Failed to create credentials', [
                            'user_id' => $user->id,
                            'account_name' => $accountName,
                            'connector_name' => $connectorName,
                            'response' => $addCredentialResponse,
                        ]);
                    }
                } elseif ($credentialsExist) {
                    // Credentials exist, update local status
                    $credential->update(['active_credentials' => true]);
                    Log::info('syncCredentials: Credentials already exist, status updated', [
                        'user_id' => $user->id,
                        'account_name' => $accountName,
                        'connector_name' => $connectorName,
                    ]);
                }
            }

            // Build detailed success message based on sync results
            $accountStatus = '';
            $credentialsStatus = '';
            $message = '';
            
            // Determine account status
            if ($syncResults['account_created']) {
                $accountStatus = 'Account created on server';
            } elseif ($syncResults['account_exists']) {
                $accountStatus = 'Account already exists on server';
            } else {
                $accountStatus = 'Account not found on server (failed to create)';
            }
            
            // Determine credentials status
            if ($syncResults['credentials_created']) {
                $credentialsStatus = 'Credentials created on server';
            } elseif ($syncResults['credentials_exist']) {
                $credentialsStatus = 'Credentials already exist on server';
            } else {
                // Check if we have required data to create credentials
                if ($apiKey && $secretKey && $connectorName) {
                    if ($syncResults['account_exists'] || $syncResults['account_created']) {
                        $credentialsStatus = 'Credentials not found on server (failed to create)';
                    } else {
                        $credentialsStatus = 'Credentials not checked (account creation failed)';
                    }
                } else {
                    $credentialsStatus = 'Credentials not checked (missing API key, secret key, or connector name)';
                }
            }

            // Build comprehensive message based on all scenarios
            if ($syncResults['account_exists'] && $syncResults['credentials_exist']) {
                $message = 'Account and credentials already exist on server. Everything is synced.';
            } elseif ($syncResults['account_exists'] && $syncResults['credentials_created']) {
                $message = 'Account already exists on server. Credentials created successfully.';
            } elseif ($syncResults['account_created'] && $syncResults['credentials_created']) {
                $message = 'Account and credentials created successfully on server.';
            } elseif ($syncResults['account_created'] && $syncResults['credentials_exist']) {
                $message = 'Account created on server. Credentials already exist on server.';
            } elseif ($syncResults['account_exists'] && !$syncResults['credentials_exist'] && !$syncResults['credentials_created']) {
                if ($apiKey && $secretKey && $connectorName) {
                    $message = 'Account exists on server, but credentials could not be created. Please check your API keys and connector name.';
                } else {
                    $message = 'Account exists on server, but credentials cannot be created (missing API key, secret key, or connector name).';
                }
            } elseif (!$syncResults['account_exists'] && !$syncResults['account_created']) {
                $message = 'Failed to create account on server. Credentials were not checked.';
            } else {
                // Fallback: combine status messages
                $message = $accountStatus . '. ' . $credentialsStatus . '.';
            }

            Log::info('syncCredentials: Sync completed', [
                'user_id' => $user->id,
                'credential_id' => $id,
                'results' => $syncResults,
                'account_status' => $accountStatus,
                'credentials_status' => $credentialsStatus,
            ]);

            return response()->json([
                'success' => true,
                'message' => $message,
                'results' => $syncResults,
                'account_status' => $accountStatus,
                'credentials_status' => $credentialsStatus,
            ]);
        } catch (\Exception $e) {
            Log::error('Error syncing credentials', [
                'error' => $e->getMessage(),
                'credential_id' => $id,
                'user_id' => $user->id,
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to sync credentials: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Set credential priority (primary/secondary)
     */
    public function setCredentialPriority(Request $request, $id)
    {
        $request->validate([
            'priority' => 'required|in:primary,secondary,none',
        ]);

        $user = Auth::user();
        $priority = $request->priority;
        
        try {
            $credential = TradeCredential::where('id', $id)
                ->where('user_id', $user->id)
                ->firstOrFail();

            // If setting as primary, remove primary from other credentials
            if ($priority === 'primary') {
                TradeCredential::where('user_id', $user->id)
                    ->where('id', '!=', $id)
                    ->where('credential_priority', 'primary')
                    ->update(['credential_priority' => 'none']);
            }

            // If setting as secondary, check if there's already a secondary
            // (Optional: you can limit to one secondary, or allow multiple)
            // For now, we'll allow multiple secondary credentials

            $credential->update([
                'credential_priority' => $priority,
            ]);

            Log::info('Trade credential priority updated', [
                'user_id' => $user->id,
                'credential_id' => $credential->id,
                'priority' => $priority,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Credential priority updated successfully',
                'priority' => $priority,
            ]);
        } catch (\Exception $e) {
            Log::error('Error updating credential priority', [
                'error' => $e->getMessage(),
                'credential_id' => $id,
                'user_id' => $user->id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update priority: ' . $e->getMessage(),
            ], 500);
        }
    }
}
