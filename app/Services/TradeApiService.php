<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Crypt;

class TradeApiService
{
    protected $baseUrl;
    protected $timeout = 30;

    public function __construct()
    {
        $this->baseUrl = env('TRADE_SERVER_URL', 'http://165.22.59.174:8000');
    }

    /**
     * Ensure user has a valid access token
     * Returns true if token is available/refreshed, false if login needed
     */
    public function ensureValidToken(User $user): bool
    {
        // If user has an access token, assume it's valid
        if ($user->api_token) {
            return true;
        }

        // No access token, try to refresh if we have refresh token
        if ($user->refresh_token) {
            if ($this->refreshToken($user)) {
                return true;
            }
        }

        // No token and refresh failed - need to login
        return false;
    }

    /**
     * Ensure customer has valid token using full authentication flow
     * Implements: register → login → refresh pattern
     * Returns true if any step succeeds, false if all fail
     */
    public function ensureCustomerToken(User $user): bool
    {
        Log::info('ensureCustomerToken: Starting authentication flow', [
            'user_id' => $user->id,
            'email' => $user->email,
            'has_api_token' => !empty($user->api_token),
            'has_refresh_token' => !empty($user->refresh_token),
        ]);

        // Refresh user model to get latest token from database
        $user->refresh();

        // If token exists, return true (let API calls handle 401 with auto-retry)
        if ($user->api_token) {
            Log::info('ensureCustomerToken: Token exists, using existing token', [
                'user_id' => $user->id,
                'token_preview' => substr($user->api_token, 0, 20) . '...',
            ]);
            return true;
        }

        // No token but has refresh_token: try refreshToken()
        if ($user->refresh_token) {
            Log::info('ensureCustomerToken: No token, trying refresh', [
                'user_id' => $user->id,
            ]);
            if ($this->refreshToken($user)) {
                $user->refresh();
                Log::info('ensureCustomerToken: Token refreshed successfully', [
                    'user_id' => $user->id,
                    'token_preview' => substr($user->api_token ?? '', 0, 20) . '...',
                ]);
                return true;
            }
            Log::warning('ensureCustomerToken: Token refresh failed', [
                'user_id' => $user->id,
            ]);
        }

        // Refresh failed or no refresh_token: try loginUser() with stored password or default
        $password = null;
        
        // Try to get password from stored api_password field (encrypted or plain text)
        if ($user->api_password) {
            try {
                // Try to decrypt first (in case it's encrypted)
                $password = Crypt::decryptString($user->api_password);
                Log::info('ensureCustomerToken: Using stored encrypted api_password', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                ]);
            } catch (\Exception $e) {
                // If decryption fails, assume it's stored in plain text (backward compatibility)
                $password = $user->api_password;
                Log::info('ensureCustomerToken: Using stored plain text api_password', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                ]);
            }
        }
        
        // Fallback to default password if no stored password
        if (!$password) {
            $password = 'Test@1234';
            Log::info('ensureCustomerToken: Using default password', [
                'user_id' => $user->id,
                'email' => $user->email,
            ]);
        }
        
        Log::info('ensureCustomerToken: Trying login', [
            'user_id' => $user->id,
            'email' => $user->email,
            'using_stored_password' => $user->api_password ? true : false,
            'password_length' => strlen($password),
            'password_preview' => substr($password, 0, 1) . str_repeat('*', max(0, strlen($password) - 2)) . substr($password, -1),
        ]);
        
        if ($this->loginUser($user, $password)) {
            $user->refresh();
            Log::info('ensureCustomerToken: Login successful', [
                'user_id' => $user->id,
                'token_preview' => substr($user->api_token ?? '', 0, 20) . '...',
            ]);
            return true;
        }
        Log::warning('ensureCustomerToken: Login failed, trying registration', [
            'user_id' => $user->id,
        ]);

        // Login failed (user not registered): try registerUser() with same password
        Log::info('ensureCustomerToken: Trying registration', [
            'user_id' => $user->id,
            'email' => $user->email,
        ]);
        if ($this->registerUser($user, $password)) {
            $user->refresh();
            Log::info('ensureCustomerToken: Registration successful', [
                'user_id' => $user->id,
                'token_preview' => substr($user->api_token ?? '', 0, 20) . '...',
            ]);
            return true;
        }

        // All failed
        Log::error('ensureCustomerToken: All authentication methods failed', [
            'user_id' => $user->id,
            'email' => $user->email,
        ]);
        return false;
    }

    /**
     * Login user and save tokens
     */
    public function loginUser(User $user, string $password): bool
    {
        $loginUrl = $this->baseUrl . '/auth/login';
        Log::info('loginUser: Attempting login', [
            'user_id' => $user->id,
            'email' => $user->email,
            'url' => $loginUrl,
        ]);

        try {
            $response = Http::timeout($this->timeout)
                ->withoutVerifying()
                ->post($loginUrl, [
                    'email' => $user->email,
                    'password' => $password,
                ]);

            $statusCode = $response->status();
            $data = $response->json();
            $rawBody = $response->body();

            Log::info('loginUser: API response received', [
                'user_id' => $user->id,
                'status_code' => $statusCode,
                'response_keys' => is_array($data) ? array_keys($data) : 'not_array',
                'raw_body_preview' => substr($rawBody, 0, 500),
            ]);

            if ($statusCode >= 200 && $statusCode < 300) {
                // Handle different response structures
                $accessToken = null;
                $refreshToken = null;

                if (isset($data['access_token'])) {
                    $accessToken = $data['access_token'];
                    $refreshToken = $data['refresh_token'] ?? null;
                } elseif (isset($data['data']['access_token'])) {
                    $accessToken = $data['data']['access_token'];
                    $refreshToken = $data['data']['refresh_token'] ?? null;
                }

                if ($accessToken) {
                    $updateResult = $user->update([
                        'api_token' => $accessToken,
                        'refresh_token' => $refreshToken,
                    ]);

                    // Verify token was saved
                    $user->refresh();
                    $savedToken = $user->api_token;

                    Log::info('loginUser: Token saved successfully', [
                        'user_id' => $user->id,
                        'email' => $user->email,
                        'update_result' => $updateResult,
                        'token_saved' => !empty($savedToken),
                        'token_preview' => substr($accessToken, 0, 20) . '...',
                        'saved_token_preview' => substr($savedToken ?? '', 0, 20) . '...',
                    ]);

                    return true;
                } else {
                    Log::warning('loginUser: No access_token in response', [
                        'user_id' => $user->id,
                        'status_code' => $statusCode,
                        'response_structure' => $data,
                    ]);
                }
            } else {
                Log::warning('loginUser: Login failed - non-2xx status', [
                    'user_id' => $user->id,
                    'status_code' => $statusCode,
                    'response' => $data,
                    'raw_body' => $rawBody,
                ]);
            }

            return false;
        } catch (\Exception $e) {
            Log::error('loginUser: Exception occurred', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return false;
        }
    }

    /**
     * Register user with trade API
     */
    public function registerUser(User $user, string $password): bool
    {
        $registerUrl = $this->baseUrl . '/auth/register';
        Log::info('registerUser: Attempting registration', [
            'user_id' => $user->id,
            'email' => $user->email,
            'name' => $user->name,
            'url' => $registerUrl,
        ]);

        try {
            $response = Http::timeout($this->timeout)
                ->withoutVerifying()
                ->post($registerUrl, [
                    'name' => $user->name,
                    'email' => $user->email,
                    'password' => $password,
                    'hb_master_password' => $password,
                ]);

            $statusCode = $response->status();
            $data = $response->json();
            $rawBody = $response->body();

            Log::info('registerUser: API response received', [
                'user_id' => $user->id,
                'status_code' => $statusCode,
                'response_keys' => is_array($data) ? array_keys($data) : 'not_array',
                'raw_body_preview' => substr($rawBody, 0, 500),
            ]);

            if ($statusCode >= 200 && $statusCode < 300) {
                // Handle different response structures
                $accessToken = null;
                $refreshToken = null;

                if (isset($data['access_token'])) {
                    $accessToken = $data['access_token'];
                    $refreshToken = $data['refresh_token'] ?? null;
                } elseif (isset($data['data']['access_token'])) {
                    $accessToken = $data['data']['access_token'];
                    $refreshToken = $data['data']['refresh_token'] ?? null;
                }

                if ($accessToken) {
                    $updateResult = $user->update([
                        'api_token' => $accessToken,
                        'refresh_token' => $refreshToken,
                    ]);

                    // Verify token was saved
                    $user->refresh();
                    $savedToken = $user->api_token;

                    Log::info('registerUser: Token saved successfully', [
                        'user_id' => $user->id,
                        'email' => $user->email,
                        'update_result' => $updateResult,
                        'token_saved' => !empty($savedToken),
                        'token_preview' => substr($accessToken, 0, 20) . '...',
                        'saved_token_preview' => substr($savedToken ?? '', 0, 20) . '...',
                    ]);

                    return true;
                } else {
                    Log::warning('registerUser: No access_token in response', [
                        'user_id' => $user->id,
                        'status_code' => $statusCode,
                        'response_structure' => $data,
                    ]);
                }
            } else {
                Log::warning('registerUser: Registration failed - non-2xx status', [
                    'user_id' => $user->id,
                    'status_code' => $statusCode,
                    'response' => $data,
                    'raw_body' => $rawBody,
                ]);
            }

            return false;
        } catch (\Exception $e) {
            Log::error('registerUser: Exception occurred', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return false;
        }
    }

    /**
     * Refresh expired token
     */
    public function refreshToken(User $user): bool
    {
        if (!$user->refresh_token) {
            Log::warning('refreshToken: No refresh token available', [
                'user_id' => $user->id,
            ]);
            return false;
        }

        $refreshUrl = $this->baseUrl . '/auth/refresh';
        Log::info('refreshToken: Attempting token refresh', [
            'user_id' => $user->id,
            'url' => $refreshUrl,
            'refresh_token_preview' => substr($user->refresh_token, 0, 20) . '...',
        ]);

        try {
            $response = Http::timeout($this->timeout)
                ->withoutVerifying()
                ->post($refreshUrl, [
                    'refresh_token' => $user->refresh_token,
                ]);

            $statusCode = $response->status();
            $data = $response->json();
            $rawBody = $response->body();

            Log::info('refreshToken: API response received', [
                'user_id' => $user->id,
                'status_code' => $statusCode,
                'response_keys' => is_array($data) ? array_keys($data) : 'not_array',
                'raw_body_preview' => substr($rawBody, 0, 500),
            ]);

            if ($statusCode >= 200 && $statusCode < 300) {
                $accessToken = null;
                $refreshToken = null;

                if (isset($data['access_token'])) {
                    $accessToken = $data['access_token'];
                    $refreshToken = $data['refresh_token'] ?? $user->refresh_token;
                } elseif (isset($data['data']['access_token'])) {
                    $accessToken = $data['data']['access_token'];
                    $refreshToken = $data['data']['refresh_token'] ?? $user->refresh_token;
                }

                if ($accessToken) {
                    $updateResult = $user->update([
                        'api_token' => $accessToken,
                        'refresh_token' => $refreshToken,
                    ]);

                    // Verify token was saved
                    $user->refresh();
                    $savedToken = $user->api_token;

                    Log::info('refreshToken: Token refreshed and saved successfully', [
                        'user_id' => $user->id,
                        'update_result' => $updateResult,
                        'token_saved' => !empty($savedToken),
                        'token_preview' => substr($accessToken, 0, 20) . '...',
                        'saved_token_preview' => substr($savedToken ?? '', 0, 20) . '...',
                    ]);

                    return true;
                } else {
                    Log::warning('refreshToken: No access_token in response', [
                        'user_id' => $user->id,
                        'status_code' => $statusCode,
                        'response_structure' => $data,
                    ]);
                }
            } else {
                Log::warning('refreshToken: Refresh failed - non-2xx status', [
                    'user_id' => $user->id,
                    'status_code' => $statusCode,
                    'response' => $data,
                    'raw_body' => $rawBody,
                ]);
            }

            return false;
        } catch (\Exception $e) {
            Log::error('refreshToken: Exception occurred', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return false;
        }
    }

    /**
     * Make authenticated API request with Bearer token
     */
    public function makeAuthenticatedRequest(User $user, string $url, string $method = 'GET', array $data = null, bool $retryOn401 = true, bool $useCustomerAuth = true): array
    {
        Log::info('makeAuthenticatedRequest: Starting request', [
            'user_id' => $user->id,
            'url' => $url,
            'method' => $method,
            'use_customer_auth' => $useCustomerAuth,
        ]);

        // Ensure we have a valid token - use customer auth flow for customer users
        if ($useCustomerAuth) {
            if (!$this->ensureCustomerToken($user)) {
                Log::error('makeAuthenticatedRequest: ensureCustomerToken failed', [
                    'user_id' => $user->id,
                    'url' => $url,
                ]);
                return [
                    'success' => false,
                    'http_code' => 401,
                    'data' => ['detail' => 'Authentication required. Please login first.'],
                    'error' => 'No valid access token available',
                ];
            }
        } else {
            if (!$this->ensureValidToken($user)) {
                Log::error('makeAuthenticatedRequest: ensureValidToken failed', [
                    'user_id' => $user->id,
                    'url' => $url,
                ]);
                return [
                    'success' => false,
                    'http_code' => 401,
                    'data' => ['detail' => 'Authentication required. Please login first.'],
                    'error' => 'No valid access token available',
                ];
            }
        }

        // Refresh user model to get latest token from database
        $user->refresh();
        
        if (empty($user->api_token)) {
            Log::error('makeAuthenticatedRequest: Token is empty after refresh', [
                'user_id' => $user->id,
                'url' => $url,
            ]);
            return [
                'success' => false,
                'http_code' => 401,
                'data' => ['detail' => 'Token not found in database'],
                'error' => 'Token is empty',
            ];
        }

        $tokenPreview = substr($user->api_token, 0, 20) . '...';
        Log::info('makeAuthenticatedRequest: Making API call', [
            'user_id' => $user->id,
            'url' => $url,
            'method' => $method,
            'token_preview' => $tokenPreview,
            'has_data' => !empty($data),
        ]);

        try {
            $request = Http::timeout($this->timeout)
                ->withoutVerifying()
                ->withHeaders([
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . trim($user->api_token),
                ]);

            $response = null;
            if ($method === 'POST') {
                $response = $request->post($url, $data ?? []);
            } elseif ($method === 'GET') {
                $response = $request->get($url);
            } else {
                $response = $request->send($method, $url, $data ? ['json' => $data] : []);
            }

            $statusCode = $response->status();
            $responseData = $response->json();
            $rawBody = $response->body();

            Log::info('makeAuthenticatedRequest: API response received', [
                'user_id' => $user->id,
                'url' => $url,
                'status_code' => $statusCode,
                'success' => $statusCode >= 200 && $statusCode < 300,
                'response_preview' => is_array($responseData) ? json_encode(array_slice($responseData, 0, 3)) : substr($rawBody, 0, 500),
            ]);

            $result = [
                'success' => $statusCode >= 200 && $statusCode < 300,
                'http_code' => $statusCode,
                'data' => $responseData !== null ? $responseData : $response->body(),
                'raw' => $rawBody,
            ];

            // If we got 401 and retry is enabled, try full customer auth flow
            if ($statusCode === 401 && $retryOn401) {
                Log::warning('makeAuthenticatedRequest: Got 401, attempting re-authentication', [
                    'user_id' => $user->id,
                    'url' => $url,
                    'current_token_preview' => substr($user->api_token ?? '', 0, 20) . '...',
                ]);
                
                // Clear the invalid token and force re-authentication
                $user->update(['api_token' => null]);
                $user->refresh();
                
                if ($useCustomerAuth) {
                    // Force full re-authentication (login/register)
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
                    
                    // Try login first
                    $loginSuccess = $this->loginUser($user, $password);
                    if (!$loginSuccess) {
                        // If login fails, try register
                        $loginSuccess = $this->registerUser($user, $password);
                    }
                    
                    if ($loginSuccess) {
                        $user->refresh();
                        Log::info('makeAuthenticatedRequest: Re-authenticated successfully, retrying request', [
                            'user_id' => $user->id,
                            'url' => $url,
                            'new_token_preview' => substr($user->api_token ?? '', 0, 20) . '...',
                        ]);
                        // Retry the original request with new token
                        return $this->makeAuthenticatedRequest($user, $url, $method, $data, false, $useCustomerAuth);
                    } else {
                        Log::error('makeAuthenticatedRequest: Re-authentication failed', [
                            'user_id' => $user->id,
                            'url' => $url,
                        ]);
                    }
                } elseif (!$useCustomerAuth && $user->refresh_token && $this->refreshToken($user)) {
                    $user->refresh();
                    Log::info('makeAuthenticatedRequest: Token refreshed, retrying request', [
                        'user_id' => $user->id,
                        'url' => $url,
                    ]);
                    return $this->makeAuthenticatedRequest($user, $url, $method, $data, false, $useCustomerAuth);
                } else {
                    Log::error('makeAuthenticatedRequest: Re-authentication failed', [
                        'user_id' => $user->id,
                        'url' => $url,
                    ]);
                }
            }

            if (!$result['success']) {
                Log::warning('makeAuthenticatedRequest: Request failed', [
                    'user_id' => $user->id,
                    'url' => $url,
                    'status_code' => $statusCode,
                    'response' => $responseData,
                ]);
            }

            return $result;
        } catch (\Exception $e) {
            Log::error('makeAuthenticatedRequest: Exception occurred', [
                'user_id' => $user->id,
                'url' => $url,
                'method' => $method,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'http_code' => 0,
                'data' => ['detail' => $e->getMessage()],
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get connectors list from API
     */
    public function getConnectors(User $user): ?array
    {
        Log::info('getConnectors: Fetching connectors', [
            'user_id' => $user->id,
        ]);
        
        $result = $this->makeAuthenticatedRequest(
            $user,
            $this->baseUrl . '/connectors',
            'GET',
            null,
            true,
            true // Use customer auth flow
        );

        if ($result['success'] && is_array($result['data'])) {
            Log::info('getConnectors: Successfully fetched connectors', [
                'user_id' => $user->id,
                'count' => count($result['data']),
            ]);
            return $result['data'];
        }

        Log::warning('getConnectors: Failed to fetch connectors', [
            'user_id' => $user->id,
            'result' => $result,
        ]);

        return null;
    }

    /**
     * Add account to trade server
     */
    public function addAccount(User $user, string $accountName): array
    {
        $url = $this->baseUrl . '/accounts/add-account?account_name=' . urlencode($accountName);
        
        Log::info('addAccount: Adding account', [
            'user_id' => $user->id,
            'account_name' => $accountName,
            'url' => $url,
        ]);
        
        return $this->makeAuthenticatedRequest(
            $user,
            $url,
            'POST',
            null,
            true,
            true // Use customer auth flow
        );
    }

    /**
     * Get accounts list
     */
    public function getAccounts(User $user): array
    {
        Log::info('getAccounts: Fetching accounts', [
            'user_id' => $user->id,
        ]);
        
        return $this->makeAuthenticatedRequest(
            $user,
            $this->baseUrl . '/accounts',
            'GET',
            null,
            true,
            true // Use customer auth flow
        );
    }

    /**
     * Get account credentials
     */
    public function getAccountCredentials(User $user, string $accountName): array
    {
        $url = $this->baseUrl . '/accounts/' . urlencode($accountName) . '/credentials';
        
        Log::info('getAccountCredentials: Fetching account credentials', [
            'user_id' => $user->id,
            'account_name' => $accountName,
            'url' => $url,
        ]);
        
        return $this->makeAuthenticatedRequest(
            $user,
            $url,
            'GET',
            null,
            true,
            true // Use customer auth flow
        );
    }

    /**
     * Add credentials to account
     */
    public function addCredential(User $user, string $accountName, string $connectorName, string $apiKey, string $secretKey): array
    {
        $url = $this->baseUrl . '/accounts/add-credential/' . urlencode($accountName) . '/' . urlencode($connectorName);
        
        Log::info('addCredential: Adding credentials to account', [
            'user_id' => $user->id,
            'account_name' => $accountName,
            'connector_name' => $connectorName,
            'url' => $url,
        ]);

        // Ensure we have a valid token
        if (!$this->ensureCustomerToken($user)) {
            Log::error('addCredential: ensureCustomerToken failed', [
                'user_id' => $user->id,
                'url' => $url,
            ]);
            return [
                'success' => false,
                'http_code' => 401,
                'data' => ['detail' => 'Authentication required. Please login first.'],
                'error' => 'No valid access token available',
            ];
        }

        // Refresh user model to get latest token from database
        $user->refresh();
        
        if (empty($user->api_token)) {
            Log::error('addCredential: Token is empty after refresh', [
                'user_id' => $user->id,
                'url' => $url,
            ]);
            return [
                'success' => false,
                'http_code' => 401,
                'data' => ['detail' => 'Token not found in database'],
                'error' => 'Token is empty',
            ];
        }

        try {
            // Try sending as form data (application/x-www-form-urlencoded)
            $response = Http::timeout($this->timeout)
                ->withoutVerifying()
                ->withHeaders([
                    'Accept' => 'application/json',
                    'Authorization' => 'Bearer ' . trim($user->api_token),
                ])
                ->asForm()
                ->post($url, [
                    'api_key' => $apiKey,
                    'secret_key' => $secretKey,
                ]);

            $statusCode = $response->status();
            $responseData = $response->json();
            $rawBody = $response->body();

            Log::info('addCredential: API response received', [
                'user_id' => $user->id,
                'url' => $url,
                'status_code' => $statusCode,
                'success' => $statusCode >= 200 && $statusCode < 300,
                'response_preview' => is_array($responseData) ? json_encode(array_slice($responseData, 0, 3)) : substr($rawBody, 0, 500),
            ]);

            $result = [
                'success' => $statusCode >= 200 && $statusCode < 300,
                'http_code' => $statusCode,
                'data' => $responseData !== null ? $responseData : $rawBody,
                'raw' => $rawBody,
            ];

            if (!$result['success']) {
                Log::warning('addCredential: Request failed', [
                    'user_id' => $user->id,
                    'url' => $url,
                    'status_code' => $statusCode,
                    'response' => $responseData,
                ]);
            }

            return $result;
        } catch (\Exception $e) {
            Log::error('addCredential: Exception occurred', [
                'user_id' => $user->id,
                'url' => $url,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'http_code' => 0,
                'data' => ['detail' => $e->getMessage()],
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Delete credential from account
     */
    public function deleteCredential(User $user, string $accountName, string $connectorName): array
    {
        $url = $this->baseUrl . '/accounts/delete-credential/' . urlencode($accountName) . '/' . urlencode($connectorName);
        
        Log::info('deleteCredential: Deleting credential from account', [
            'user_id' => $user->id,
            'account_name' => $accountName,
            'connector_name' => $connectorName,
            'url' => $url,
        ]);

        // Ensure we have a valid token
        if (!$this->ensureCustomerToken($user)) {
            Log::error('deleteCredential: ensureCustomerToken failed', [
                'user_id' => $user->id,
                'url' => $url,
            ]);
            return [
                'success' => false,
                'http_code' => 401,
                'data' => ['detail' => 'Authentication required. Please login first.'],
                'error' => 'No valid access token available',
            ];
        }

        // Refresh user model to get latest token from database
        $user->refresh();
        
        if (empty($user->api_token)) {
            Log::error('deleteCredential: Token is empty after refresh', [
                'user_id' => $user->id,
                'url' => $url,
            ]);
            return [
                'success' => false,
                'http_code' => 401,
                'data' => ['detail' => 'Token not found in database'],
                'error' => 'Token is empty',
            ];
        }

        try {
            $response = Http::timeout($this->timeout)
                ->withoutVerifying()
                ->withHeaders([
                    'Accept' => 'application/json',
                    'Authorization' => 'Bearer ' . trim($user->api_token),
                ])
                ->post($url);

            $statusCode = $response->status();
            $responseData = $response->json();
            $rawBody = $response->body();

            Log::info('deleteCredential: API response received', [
                'user_id' => $user->id,
                'url' => $url,
                'status_code' => $statusCode,
                'success' => $statusCode >= 200 && $statusCode < 300,
                'response_preview' => is_array($responseData) ? json_encode(array_slice($responseData, 0, 3)) : substr($rawBody, 0, 500),
            ]);

            $result = [
                'success' => $statusCode >= 200 && $statusCode < 300,
                'http_code' => $statusCode,
                'data' => $responseData !== null ? $responseData : $rawBody,
                'raw' => $rawBody,
            ];

            if (!$result['success']) {
                Log::warning('deleteCredential: Request failed', [
                    'user_id' => $user->id,
                    'url' => $url,
                    'status_code' => $statusCode,
                    'response' => $responseData,
                ]);
            }

            return $result;
        } catch (\Exception $e) {
            Log::error('deleteCredential: Exception occurred', [
                'user_id' => $user->id,
                'url' => $url,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'http_code' => 0,
                'data' => ['detail' => $e->getMessage()],
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Delete account from trade server
     */
    public function deleteAccount(User $user, string $accountName): array
    {
        $url = $this->baseUrl . '/accounts/delete-account?account_name=' . urlencode($accountName);
        
        Log::info('deleteAccount: Deleting account from trade server', [
            'user_id' => $user->id,
            'account_name' => $accountName,
            'url' => $url,
        ]);
        
        return $this->makeAuthenticatedRequest(
            $user,
            $url,
            'POST',
            null,
            true,
            true // Use customer auth flow
        );
    }
}

