<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;

class TradeServerService
{
    protected $client;
    protected $baseUrl;

    public function __construct()
    {
        $this->client = new Client([
            'timeout' => 30,
            'verify' => false,
        ]);
        $this->baseUrl = env('TRADE_SERVER_URL', 'http://165.22.59.174:8000');
    }

    /**
     * Get connectors list from trade server
     */
    public function getConnectors(): ?array
    {
        try {
            $url = rtrim($this->baseUrl, '/') . '/connectors';
            
            Log::info('Trade Server API - Fetching connectors', [
                'url' => $url,
            ]);

            $response = $this->client->request('GET', $url, [
                'headers' => [
                    'Accept' => 'application/json',
                ],
            ]);

            $statusCode = $response->getStatusCode();
            $body = json_decode($response->getBody()->getContents(), true);

            if ($statusCode === 200 && is_array($body)) {
                Log::info('Trade Server API - Connectors fetched successfully', [
                    'count' => count($body),
                ]);
                return $body;
            }

            Log::warning('Trade Server API - Unexpected response format', [
                'status_code' => $statusCode,
                'response' => $body,
            ]);

            return null;
        } catch (GuzzleException $e) {
            Log::error('Trade Server API - Error fetching connectors', [
                'error' => $e->getMessage(),
                'url' => $url ?? null,
            ]);
            return null;
        } catch (\Exception $e) {
            Log::error('Trade Server API - Unexpected error fetching connectors', [
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Save account to trade server
     */
    public function addAccount(string $accountName): array
    {
        try {
            $url = rtrim($this->baseUrl, '/') . '/accounts/add-account';
            $url .= '?account_name=' . urlencode($accountName);

            Log::info('Trade Server API - Adding account', [
                'url' => $url,
                'account_name' => $accountName,
            ]);

            $response = $this->client->request('POST', $url, [
                'headers' => [
                    'Accept' => 'application/json',
                ],
            ]);

            $statusCode = $response->getStatusCode();
            $body = json_decode($response->getBody()->getContents(), true);

            if ($statusCode === 200 || $statusCode === 201) {
                Log::info('Trade Server API - Account added successfully', [
                    'account_name' => $accountName,
                    'response' => $body,
                ]);
                return [
                    'success' => true,
                    'data' => $body,
                ];
            }

            Log::warning('Trade Server API - Account add failed', [
                'status_code' => $statusCode,
                'response' => $body,
            ]);

            return [
                'success' => false,
                'message' => $body['message'] ?? 'Failed to add account',
                'data' => $body,
            ];
        } catch (GuzzleException $e) {
            $statusCode = 0;
            if ($e->hasResponse()) {
                $statusCode = $e->getResponse()->getStatusCode();
                $body = json_decode($e->getResponse()->getBody()->getContents(), true);
            }

            Log::error('Trade Server API - Error adding account', [
                'error' => $e->getMessage(),
                'status_code' => $statusCode,
                'account_name' => $accountName,
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage(),
                'status_code' => $statusCode,
            ];
        } catch (\Exception $e) {
            Log::error('Trade Server API - Unexpected error adding account', [
                'error' => $e->getMessage(),
                'account_name' => $accountName,
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Save connector with keys to trade server
     * Note: This endpoint structure may need adjustment based on actual API
     */
    public function saveConnectorWithKeys(string $accountName, int $connectorId, string $apiKey, string $secretKey): array
    {
        try {
            // Note: The actual endpoint structure may differ - adjust as needed
            $url = rtrim($this->baseUrl, '/') . '/accounts/add-connector';

            $data = [
                'account_name' => $accountName,
                'connector_id' => $connectorId,
                'api_key' => $apiKey,
                'secret_key' => $secretKey,
            ];

            Log::info('Trade Server API - Saving connector with keys', [
                'url' => $url,
                'account_name' => $accountName,
                'connector_id' => $connectorId,
            ]);

            $response = $this->client->request('POST', $url, [
                'headers' => [
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ],
                'json' => $data,
            ]);

            $statusCode = $response->getStatusCode();
            $body = json_decode($response->getBody()->getContents(), true);

            if ($statusCode === 200 || $statusCode === 201) {
                Log::info('Trade Server API - Connector saved successfully', [
                    'account_name' => $accountName,
                    'connector_id' => $connectorId,
                ]);
                return [
                    'success' => true,
                    'data' => $body,
                ];
            }

            Log::warning('Trade Server API - Connector save failed', [
                'status_code' => $statusCode,
                'response' => $body,
            ]);

            return [
                'success' => false,
                'message' => $body['message'] ?? 'Failed to save connector',
                'data' => $body,
            ];
        } catch (GuzzleException $e) {
            $statusCode = 0;
            if ($e->hasResponse()) {
                $statusCode = $e->getResponse()->getStatusCode();
                $body = json_decode($e->getResponse()->getBody()->getContents(), true);
            }

            Log::error('Trade Server API - Error saving connector', [
                'error' => $e->getMessage(),
                'status_code' => $statusCode,
                'account_name' => $accountName,
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage(),
                'status_code' => $statusCode,
            ];
        } catch (\Exception $e) {
            Log::error('Trade Server API - Unexpected error saving connector', [
                'error' => $e->getMessage(),
                'account_name' => $accountName,
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }
}

