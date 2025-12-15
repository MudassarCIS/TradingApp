<?php

namespace App\Services;

use App\Models\User;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Crypt;

class ExternalApiService
{
    protected $client;
    protected $baseUrl;
    protected $apiKey;
    protected $user;
    
    public function __construct(?User $user = null)
    {
        $this->client = new Client([
            'timeout' => 30,
            'verify' => false, // Set to true in production with valid SSL
        ]);
        $this->baseUrl = env('EXTERNAL_API_URL', 'https://api.example.com');
        $this->apiKey = env('EXTERNAL_API_KEY', '');
        $this->user = $user;
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
        try {
            $response = $this->makeRequest('POST', '/api/register', $userData, false);
            
            // If registration successful and tokens provided, save them
            if ($response && isset($response['success']) && $response['success']) {
                if (isset($response['data']['token']) || isset($response['data']['access_token'])) {
                    $token = $response['data']['token'] ?? $response['data']['access_token'] ?? null;
                    $refreshToken = $response['data']['refresh_token'] ?? null;
                    
                    if ($this->user && $token) {
                        $this->user->update([
                            'api_token' => $token,
                            'refresh_token' => $refreshToken,
                        ]);
                    }
                }
            }
            
            return $response;
        } catch (\Exception $e) {
            Log::error('External API Error - Register User: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Login user to external API
     */
    public function loginUser(string $email, string $password): ?array
    {
        try {
            $response = $this->makeRequest('POST', '/api/login', [
                'email' => $email,
                'password' => $password
            ], false);
            
            // If login successful, save tokens
            if ($response && isset($response['success']) && $response['success']) {
                $token = $response['data']['token'] ?? $response['data']['access_token'] ?? null;
                $refreshToken = $response['data']['refresh_token'] ?? null;
                
                if ($this->user && $token) {
                    $this->user->update([
                        'api_token' => $token,
                        'refresh_token' => $refreshToken,
                    ]);
                }
            }
            
            return $response;
        } catch (\Exception $e) {
            Log::error('External API Error - Login User: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Refresh access token
     */
    public function refreshToken(): ?array
    {
        if (!$this->user || !$this->user->refresh_token) {
            return null;
        }
        
        try {
            $response = $this->makeRequest('POST', '/api/refresh-token', [
                'refresh_token' => $this->user->refresh_token
            ], false);
            
            // If refresh successful, update tokens
            if ($response && isset($response['success']) && $response['success']) {
                $token = $response['data']['token'] ?? $response['data']['access_token'] ?? null;
                $refreshToken = $response['data']['refresh_token'] ?? $this->user->refresh_token;
                
                if ($token) {
                    $this->user->update([
                        'api_token' => $token,
                        'refresh_token' => $refreshToken,
                    ]);
                }
            }
            
            return $response;
        } catch (\Exception $e) {
            Log::error('External API Error - Refresh Token: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Get trading history from external API
     */
    public function getTradingHistory(int $userId, array $params = []): ?array
    {
        try {
            $queryParams = array_merge(['user_id' => $userId], $params);
            $response = $this->makeRequest('GET', '/api/trading/history', $queryParams);
            return $response;
        } catch (\Exception $e) {
            Log::error('External API Error - Get Trading History: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Start a new trade
     */
    public function startTrade(int $userId, array $tradeData): ?array
    {
        try {
            $data = array_merge(['user_id' => $userId], $tradeData);
            $response = $this->makeRequest('POST', '/api/trading/start', $data);
            return $response;
        } catch (\Exception $e) {
            Log::error('External API Error - Start Trade: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Close an existing trade
     */
    public function closeTrade(int $userId, string $tradeId): ?array
    {
        try {
            $response = $this->makeRequest('POST', '/api/trading/close', [
                'user_id' => $userId,
                'trade_id' => $tradeId
            ]);
            return $response;
        } catch (\Exception $e) {
            Log::error('External API Error - Close Trade: ' . $e->getMessage());
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
        
        try {
            $response = $this->client->request($method, $url, $options);
            $body = json_decode($response->getBody()->getContents(), true);
            
            return $body ?? [];
        } catch (GuzzleException $e) {
            $statusCode = $e->hasResponse() ? $e->getResponse()->getStatusCode() : 0;
            
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
                            $response = $this->client->request($method, $url, $options);
                            $body = json_decode($response->getBody()->getContents(), true);
                            return $body ?? [];
                        } catch (GuzzleException $retryException) {
                            Log::error('Retry after refresh failed: ' . $retryException->getMessage());
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
                            $response = $this->client->request($method, $url, $options);
                            $body = json_decode($response->getBody()->getContents(), true);
                            return $body ?? [];
                        } catch (GuzzleException $retryException) {
                            Log::error('Retry after login failed: ' . $retryException->getMessage());
                        }
                    }
                }
            }
            
            Log::error('External API Request Failed: ' . $e->getMessage(), [
                'url' => $url,
                'method' => $method,
                'status_code' => $statusCode,
                'params' => $params
            ]);
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
