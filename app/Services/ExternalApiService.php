<?php

namespace App\Services;

use App\Models\User;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Exception\ConnectException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Crypt;

class ExternalApiService
{
    protected $client;
    protected $baseUrl;
    protected $apiKey;
    protected $user;
    
    // Configurable API endpoints
    protected $endpoints = [
        'register' => '/auth/register',
        'login' => '/auth/login',
        'refresh_token' => '/auth/refresh',
        'logout' => '/auth/logout',
        'trading_history' => '/api/trading/history',
        'start_trade' => '/api/trading/start',
        'close_trade' => '/api/trading/close',
    ];
    
    public function __construct(?User $user = null)
    {
        $this->client = new Client([
            'timeout' => 30,
            'verify' => false, // Set to true in production with valid SSL
        ]);
        $this->baseUrl = env('EXTERNAL_API_URL', 'https://api.example.com');
        $this->apiKey = env('EXTERNAL_API_KEY', '');
        $this->user = $user;
        
        // Load custom endpoints from config if available
        $this->loadCustomEndpoints();
        
        // Warn if API URL is still using default placeholder
        if ($this->baseUrl === 'https://api.example.com') {
            Log::warning('External API URL not configured - using default placeholder', [
                'current_url' => $this->baseUrl,
                'action' => 'configuration_warning',
                'note' => 'Please set EXTERNAL_API_URL in your .env file to the actual 3rd party API URL',
            ]);
        }
    }
    
    /**
     * Load custom endpoints from environment variables
     */
    protected function loadCustomEndpoints(): void
    {
        $customEndpoints = [
            'register' => env('EXTERNAL_API_ENDPOINT_REGISTER'),
            'login' => env('EXTERNAL_API_ENDPOINT_LOGIN'),
            'refresh_token' => env('EXTERNAL_API_ENDPOINT_REFRESH_TOKEN'),
            'logout' => env('EXTERNAL_API_ENDPOINT_LOGOUT'),
            'trading_history' => env('EXTERNAL_API_ENDPOINT_TRADING_HISTORY'),
            'start_trade' => env('EXTERNAL_API_ENDPOINT_START_TRADE'),
            'close_trade' => env('EXTERNAL_API_ENDPOINT_CLOSE_TRADE'),
        ];
        
        foreach ($customEndpoints as $key => $endpoint) {
            if ($endpoint !== null) {
                $this->endpoints[$key] = $endpoint;
            }
        }
    }
    
    /**
     * Get endpoint path
     */
    protected function getEndpoint(string $key): string
    {
        return $this->endpoints[$key] ?? '/api/' . $key;
    }
    
    /**
     * Set user for API calls
     */
    public function setUser(User $user): void
    {
        $this->user = $user;
    }
    
    /**
     * Register user to external API
     */
    public function registerUser(array $userData): ?array
    {
        $endpoint = $this->getEndpoint('register');
        $logContext = [
            'action' => 'register_user',
            'user_id' => $this->user?->id,
            'email' => $userData['email'] ?? null,
            'endpoint' => $endpoint,
        ];
        
        // Format payload to match external API requirements
        // Expected format: email, password, name, hb_master_password (same as password)
        $payload = [
            'email' => $userData['email'] ?? null,
            'password' => $userData['password'] ?? null,
            'name' => $userData['name'] ?? null,
            'hb_master_password' => $userData['password'] ?? null, // Same as password
        ];
        
        // Log request (without password)
        $logData = $payload;
        if (isset($logData['password'])) {
            $logData['password'] = '***REDACTED***';
        }
        if (isset($logData['hb_master_password'])) {
            $logData['hb_master_password'] = '***REDACTED***';
        }
        Log::info('3rd Party API Call - Register User [REQUEST]', array_merge($logContext, ['request_data' => $logData]));
        
        try {
            $response = $this->makeRequest('POST', $endpoint, $payload, false);
            
            // Log response
            if ($response && isset($response['success']) && $response['success']) {
                Log::info('3rd Party API Call - Register User [SUCCESS]', array_merge($logContext, [
                    'status' => 'success',
                    'response' => [
                        'success' => $response['success'] ?? false,
                        'message' => $response['message'] ?? null,
                        'has_token' => isset($response['data']['token']) || isset($response['data']['access_token']),
                        'has_refresh_token' => isset($response['data']['refresh_token']),
                    ]
                ]));
                
                // If registration successful and tokens provided, save them
                if (isset($response['data']['token']) || isset($response['data']['access_token'])) {
                    $token = $response['data']['token'] ?? $response['data']['access_token'] ?? null;
                    $refreshToken = $response['data']['refresh_token'] ?? null;
                    
                    if ($this->user && $token) {
                        $this->user->update([
                            'api_token' => $token,
                            'refresh_token' => $refreshToken,
                        ]);
                        Log::info('3rd Party API Call - Register User [TOKEN SAVED]', array_merge($logContext, [
                            'token_saved' => true,
                        ]));
                    }
                }
            } else {
                Log::warning('3rd Party API Call - Register User [FAILURE]', array_merge($logContext, [
                    'status' => 'failure',
                    'response' => $response,
                    'error_message' => $response['message'] ?? 'Unknown error',
                ]));
            }
            
            return $response;
        } catch (\Exception $e) {
            Log::error('3rd Party API Call - Register User [EXCEPTION]', array_merge($logContext, [
                'status' => 'exception',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]));
            return null;
        }
    }
    
    /**
     * Login user to external API
     */
    public function loginUser(string $email, string $password): ?array
    {
        $endpoint = $this->getEndpoint('login');
        $logContext = [
            'action' => 'login_user',
            'user_id' => $this->user?->id,
            'email' => $email,
            'endpoint' => $endpoint,
        ];
        
        Log::info('3rd Party API Call - Login User [REQUEST]', $logContext);
        
        try {
            $response = $this->makeRequest('POST', $endpoint, [
                'email' => $email,
                'password' => $password
            ], false);
            
            // Log response
            if ($response && isset($response['success']) && $response['success']) {
                Log::info('3rd Party API Call - Login User [SUCCESS]', array_merge($logContext, [
                    'status' => 'success',
                    'response' => [
                        'success' => $response['success'] ?? false,
                        'message' => $response['message'] ?? null,
                        'has_token' => isset($response['data']['token']) || isset($response['data']['access_token']),
                    ]
                ]));
                
                // If login successful, save tokens
                $token = $response['data']['token'] ?? $response['data']['access_token'] ?? null;
                $refreshToken = $response['data']['refresh_token'] ?? null;
                
                if ($this->user && $token) {
                    $this->user->update([
                        'api_token' => $token,
                        'refresh_token' => $refreshToken,
                    ]);
                    Log::info('3rd Party API Call - Login User [TOKEN SAVED]', array_merge($logContext, [
                        'token_saved' => true,
                    ]));
                }
            } else {
                Log::warning('3rd Party API Call - Login User [FAILURE]', array_merge($logContext, [
                    'status' => 'failure',
                    'response' => $response,
                    'error_message' => $response['message'] ?? 'Login failed',
                ]));
            }
            
            return $response;
        } catch (\Exception $e) {
            Log::error('3rd Party API Call - Login User [EXCEPTION]', array_merge($logContext, [
                'status' => 'exception',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]));
            return null;
        }
    }
    
    /**
     * Refresh access token
     */
    public function refreshToken(): ?array
    {
        if (!$this->user || !$this->user->refresh_token) {
            Log::warning('3rd Party API Call - Refresh Token [SKIPPED]', [
                'action' => 'refresh_token',
                'user_id' => $this->user?->id,
                'reason' => 'No user or refresh token available',
            ]);
            return null;
        }
        
        $endpoint = $this->getEndpoint('refresh_token');
        $logContext = [
            'action' => 'refresh_token',
            'user_id' => $this->user->id,
            'endpoint' => $endpoint,
        ];
        
        Log::info('3rd Party API Call - Refresh Token [REQUEST]', $logContext);
        
        try {
            $response = $this->makeRequest('POST', $endpoint, [
                'refresh_token' => $this->user->refresh_token
            ], false);
            
            // Log response
            if ($response && isset($response['success']) && $response['success']) {
                Log::info('3rd Party API Call - Refresh Token [SUCCESS]', array_merge($logContext, [
                    'status' => 'success',
                    'response' => [
                        'success' => $response['success'] ?? false,
                        'has_token' => isset($response['data']['token']) || isset($response['data']['access_token']),
                    ]
                ]));
                
                // If refresh successful, update tokens
                $token = $response['data']['token'] ?? $response['data']['access_token'] ?? null;
                $refreshToken = $response['data']['refresh_token'] ?? $this->user->refresh_token;
                
                if ($token) {
                    $this->user->update([
                        'api_token' => $token,
                        'refresh_token' => $refreshToken,
                    ]);
                    Log::info('3rd Party API Call - Refresh Token [TOKEN UPDATED]', array_merge($logContext, [
                        'token_updated' => true,
                    ]));
                }
            } else {
                Log::warning('3rd Party API Call - Refresh Token [FAILURE]', array_merge($logContext, [
                    'status' => 'failure',
                    'response' => $response,
                    'error_message' => $response['message'] ?? 'Token refresh failed',
                ]));
            }
            
            return $response;
        } catch (\Exception $e) {
            Log::error('3rd Party API Call - Refresh Token [EXCEPTION]', array_merge($logContext, [
                'status' => 'exception',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]));
            return null;
        }
    }
    
    /**
     * Logout user from external API
     */
    public function logoutUser(): ?array
    {
        if (!$this->user) {
            Log::warning('3rd Party API Call - Logout User [SKIPPED]', [
                'action' => 'logout_user',
                'reason' => 'No user available',
            ]);
            return null;
        }
        
        $endpoint = $this->getEndpoint('logout');
        $logContext = [
            'action' => 'logout_user',
            'user_id' => $this->user->id,
            'email' => $this->user->email,
            'endpoint' => $endpoint,
        ];
        
        Log::info('3rd Party API Call - Logout User [REQUEST]', $logContext);
        
        try {
            $response = $this->makeRequest('POST', $endpoint, [], true);
            
            // Log response
            if ($response && isset($response['success']) && $response['success']) {
                Log::info('3rd Party API Call - Logout User [SUCCESS]', array_merge($logContext, [
                    'status' => 'success',
                    'response' => [
                        'success' => $response['success'] ?? false,
                        'message' => $response['message'] ?? null,
                    ]
                ]));
                
                // Clear tokens after successful logout
                if ($this->user) {
                    $this->user->update([
                        'api_token' => null,
                        'refresh_token' => null,
                    ]);
                    Log::info('3rd Party API Call - Logout User [TOKENS CLEARED]', array_merge($logContext, [
                        'tokens_cleared' => true,
                    ]));
                }
            } else {
                Log::warning('3rd Party API Call - Logout User [FAILURE]', array_merge($logContext, [
                    'status' => 'failure',
                    'response' => $response,
                    'error_message' => $response['message'] ?? 'Logout failed',
                ]));
            }
            
            return $response;
        } catch (\Exception $e) {
            Log::error('3rd Party API Call - Logout User [EXCEPTION]', array_merge($logContext, [
                'status' => 'exception',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]));
            
            // Clear tokens even if API call fails (local logout)
            if ($this->user) {
                $this->user->update([
                    'api_token' => null,
                    'refresh_token' => null,
                ]);
                Log::info('3rd Party API Call - Logout User [TOKENS CLEARED ON EXCEPTION]', array_merge($logContext, [
                    'tokens_cleared' => true,
                    'note' => 'Tokens cleared locally despite API call failure',
                ]));
            }
            
            return null;
        }
    }
    
    /**
     * Get trading history from external API
     */
    public function getTradingHistory(int $userId, array $params = []): ?array
    {
        $endpoint = $this->getEndpoint('trading_history');
        $logContext = [
            'action' => 'get_trading_history',
            'user_id' => $userId,
            'endpoint' => $endpoint,
            'params' => $params,
        ];
        
        Log::info('3rd Party API Call - Get Trading History [REQUEST]', $logContext);
        
        try {
            $queryParams = array_merge(['user_id' => $userId], $params);
            $response = $this->makeRequest('GET', $endpoint, $queryParams);
            
            // Log response
            if ($response && isset($response['success']) && $response['success']) {
                $tradeCount = isset($response['data']) && is_array($response['data']) ? count($response['data']) : 0;
                Log::info('3rd Party API Call - Get Trading History [SUCCESS]', array_merge($logContext, [
                    'status' => 'success',
                    'response' => [
                        'success' => $response['success'] ?? false,
                        'trade_count' => $tradeCount,
                        'message' => $response['message'] ?? null,
                    ]
                ]));
            } else {
                Log::warning('3rd Party API Call - Get Trading History [FAILURE]', array_merge($logContext, [
                    'status' => 'failure',
                    'response' => $response,
                    'error_message' => $response['message'] ?? 'Failed to get trading history',
                ]));
            }
            
            return $response;
        } catch (\Exception $e) {
            Log::error('3rd Party API Call - Get Trading History [EXCEPTION]', array_merge($logContext, [
                'status' => 'exception',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]));
            return null;
        }
    }
    
    /**
     * Start a new trade
     */
    public function startTrade(int $userId, array $tradeData): ?array
    {
        $endpoint = $this->getEndpoint('start_trade');
        $logContext = [
            'action' => 'start_trade',
            'user_id' => $userId,
            'endpoint' => $endpoint,
            'trade_data' => $tradeData,
        ];
        
        Log::info('3rd Party API Call - Start Trade [REQUEST]', $logContext);
        
        try {
            $data = array_merge(['user_id' => $userId], $tradeData);
            $response = $this->makeRequest('POST', $endpoint, $data);
            
            // Log response
            if ($response && isset($response['success']) && $response['success']) {
                Log::info('3rd Party API Call - Start Trade [SUCCESS]', array_merge($logContext, [
                    'status' => 'success',
                    'response' => [
                        'success' => $response['success'] ?? false,
                        'message' => $response['message'] ?? null,
                        'trade_id' => $response['data']['trade_id'] ?? $response['data']['id'] ?? null,
                        'symbol' => $tradeData['symbol'] ?? null,
                        'side' => $tradeData['side'] ?? null,
                        'quantity' => $tradeData['quantity'] ?? null,
                    ]
                ]));
            } else {
                Log::warning('3rd Party API Call - Start Trade [FAILURE]', array_merge($logContext, [
                    'status' => 'failure',
                    'response' => $response,
                    'error_message' => $response['message'] ?? 'Failed to start trade',
                ]));
            }
            
            return $response;
        } catch (\Exception $e) {
            Log::error('3rd Party API Call - Start Trade [EXCEPTION]', array_merge($logContext, [
                'status' => 'exception',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]));
            return null;
        }
    }
    
    /**
     * Close an existing trade
     */
    public function closeTrade(int $userId, string $tradeId): ?array
    {
        $endpoint = $this->getEndpoint('close_trade');
        $logContext = [
            'action' => 'close_trade',
            'user_id' => $userId,
            'trade_id' => $tradeId,
            'endpoint' => $endpoint,
        ];
        
        Log::info('3rd Party API Call - Close Trade [REQUEST]', $logContext);
        
        try {
            $response = $this->makeRequest('POST', $endpoint, [
                'user_id' => $userId,
                'trade_id' => $tradeId
            ]);
            
            // Log response
            if ($response && isset($response['success']) && $response['success']) {
                Log::info('3rd Party API Call - Close Trade [SUCCESS]', array_merge($logContext, [
                    'status' => 'success',
                    'response' => [
                        'success' => $response['success'] ?? false,
                        'message' => $response['message'] ?? null,
                        'profit_loss' => $response['data']['profit_loss'] ?? null,
                        'closed_at' => $response['data']['closed_at'] ?? null,
                    ]
                ]));
            } else {
                Log::warning('3rd Party API Call - Close Trade [FAILURE]', array_merge($logContext, [
                    'status' => 'failure',
                    'response' => $response,
                    'error_message' => $response['message'] ?? 'Failed to close trade',
                ]));
            }
            
            return $response;
        } catch (\Exception $e) {
            Log::error('3rd Party API Call - Close Trade [EXCEPTION]', array_merge($logContext, [
                'status' => 'exception',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]));
            return null;
        }
    }
    
    /**
     * Make HTTP request to external API with automatic token management
     */
    protected function makeRequest(string $method, string $endpoint, array $params = [], bool $useAuth = true): array
    {
        $url = rtrim($this->baseUrl, '/') . $endpoint;
        
        $headers = [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ];
        
        // Add authentication if needed
        if ($useAuth && $this->user) {
            $token = $this->getValidToken();
            if ($token) {
                $headers['Authorization'] = 'Bearer ' . $token;
            }
        } elseif (!$useAuth && $this->apiKey) {
            // Use API key for public endpoints
            $headers['Authorization'] = 'Bearer ' . $this->apiKey;
        }
        
        $options = [
            'headers' => $headers,
        ];
        
        if ($method === 'GET') {
            $url .= '?' . http_build_query($params);
        } else {
            $options['json'] = $params;
        }
        
        // Log request details
        $requestLogContext = [
            'action' => 'api_request',
            'method' => $method,
            'url' => $url,
            'endpoint' => $endpoint,
            'user_id' => $this->user?->id,
            'use_auth' => $useAuth,
            'has_params' => !empty($params),
        ];
        
        // Log request (sanitize sensitive data)
        $sanitizedParams = $params;
        if (isset($sanitizedParams['password'])) {
            $sanitizedParams['password'] = '***REDACTED***';
        }
        if (isset($sanitizedParams['refresh_token'])) {
            $sanitizedParams['refresh_token'] = '***REDACTED***';
        }
        
        Log::info('3rd Party API Request [OUTGOING]', array_merge($requestLogContext, [
            'params' => $sanitizedParams,
        ]));
        
        try {
            $startTime = microtime(true);
            $response = $this->client->request($method, $url, $options);
            $duration = round((microtime(true) - $startTime) * 1000, 2); // milliseconds
            
            $statusCode = $response->getStatusCode();
            $body = json_decode($response->getBody()->getContents(), true);
            
            // Log successful response
            Log::info('3rd Party API Response [SUCCESS]', array_merge($requestLogContext, [
                'status_code' => $statusCode,
                'duration_ms' => $duration,
                'response_success' => $body['success'] ?? null,
                'response_message' => $body['message'] ?? null,
            ]));
            
            return $body ?? [];
        } catch (GuzzleException $e) {
            // Get status code safely (ConnectException doesn't have hasResponse)
            $statusCode = 0;
            if ($e instanceof ClientException || $e instanceof ServerException) {
                $statusCode = $e->getResponse()->getStatusCode();
            }
            
            // If unauthorized and using auth, try to refresh token and retry
            if ($useAuth && $statusCode === 401 && $this->user) {
                Log::info('Token expired, attempting to refresh...');
                
                // Try to refresh token
                $refreshResponse = $this->refreshToken();
                
                if ($refreshResponse && isset($refreshResponse['success']) && $refreshResponse['success']) {
                    // Retry the original request with new token
                    $token = $this->user->fresh()->api_token;
                    if ($token) {
                        $headers['Authorization'] = 'Bearer ' . $token;
                        $options['headers'] = $headers;
                        
                        try {
                            Log::info('3rd Party API Request [RETRY AFTER REFRESH]', array_merge($requestLogContext, [
                                'retry_reason' => 'token_refreshed',
                            ]));
                            $response = $this->client->request($method, $url, $options);
                            $body = json_decode($response->getBody()->getContents(), true);
                            Log::info('3rd Party API Response [RETRY SUCCESS]', array_merge($requestLogContext, [
                                'status_code' => $response->getStatusCode(),
                                'retry_reason' => 'token_refreshed',
                            ]));
                            return $body ?? [];
                        } catch (GuzzleException $retryException) {
                            Log::error('3rd Party API Response [RETRY FAILED]', array_merge($requestLogContext, [
                                'retry_reason' => 'token_refreshed',
                                'error' => $retryException->getMessage(),
                            ]));
                        }
                    }
                }
                
                // If refresh failed, try to login
                Log::info('Refresh failed, attempting to login...');
                $loginResponse = $this->loginWithStoredPassword();
                
                if ($loginResponse && isset($loginResponse['success']) && $loginResponse['success']) {
                    // Retry the original request with new token
                    $token = $this->user->fresh()->api_token;
                    if ($token) {
                        $headers['Authorization'] = 'Bearer ' . $token;
                        $options['headers'] = $headers;
                        
                        try {
                            Log::info('3rd Party API Request [RETRY AFTER LOGIN]', array_merge($requestLogContext, [
                                'retry_reason' => 'reauthenticated',
                            ]));
                            $response = $this->client->request($method, $url, $options);
                            $body = json_decode($response->getBody()->getContents(), true);
                            Log::info('3rd Party API Response [RETRY SUCCESS]', array_merge($requestLogContext, [
                                'status_code' => $response->getStatusCode(),
                                'retry_reason' => 'reauthenticated',
                            ]));
                            return $body ?? [];
                        } catch (GuzzleException $retryException) {
                            Log::error('3rd Party API Response [RETRY FAILED]', array_merge($requestLogContext, [
                                'retry_reason' => 'reauthenticated',
                                'error' => $retryException->getMessage(),
                            ]));
                        }
                    }
                }
            }
            
            // Get error response body if available
            $errorBody = null;
            if ($e instanceof ClientException || $e instanceof ServerException) {
                try {
                    $errorBody = json_decode($e->getResponse()->getBody()->getContents(), true);
                } catch (\Exception $bodyException) {
                    // Ignore if can't read body
                }
            }
            
            // Enhanced error logging for 404 errors
            $errorDetails = [
                'status_code' => $statusCode,
                'error' => $e->getMessage(),
                'error_type' => get_class($e),
                'error_response' => $errorBody,
            ];
            
            // Add helpful message for 404 errors
            if ($statusCode === 404) {
                $errorDetails['helpful_message'] = 'Endpoint not found (404). Please verify:';
                $errorDetails['suggestions'] = [
                    '1. Check if the endpoint path is correct',
                    '2. Verify EXTERNAL_API_URL is correct: ' . $this->baseUrl,
                    '3. Check if endpoint requires different path (e.g., /register instead of /api/register)',
                    '4. Set custom endpoint via EXTERNAL_API_ENDPOINT_REGISTER env variable',
                ];
                $errorDetails['current_endpoint'] = $endpoint;
                $errorDetails['full_url'] = $url;
            }
            
            Log::error('3rd Party API Response [FAILURE]', array_merge($requestLogContext, $errorDetails));
            throw $e;
        }
    }
    
    /**
     * Get valid token (current token or refresh if needed)
     */
    protected function getValidToken(): ?string
    {
        if (!$this->user) {
            return null;
        }
        
        // Return current token if exists
        if ($this->user->api_token) {
            return $this->user->api_token;
        }
        
        // If no token, try to login
        $this->loginWithStoredPassword();
        
        return $this->user->fresh()->api_token;
    }
    
    /**
     * Login using stored encrypted password
     */
    protected function loginWithStoredPassword(): ?array
    {
        if (!$this->user || !$this->user->api_password) {
            return null;
        }
        
        try {
            // Decrypt the stored password
            $password = Crypt::decryptString($this->user->api_password);
            
            // Call login API
            return $this->loginUser($this->user->email, $password);
        } catch (\Exception $e) {
            Log::error('Failed to decrypt password or login: ' . $e->getMessage());
            return null;
        }
    }
}
