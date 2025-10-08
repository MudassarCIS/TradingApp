<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;

class BinanceApiService
{
    protected $client;
    protected $baseUrl;
    protected $apiKey;
    protected $secretKey;
    
    public function __construct()
    {
        $this->client = new Client();
        $this->baseUrl = config('services.binance.base_url', 'https://api.binance.com');
        $this->apiKey = config('services.binance.api_key');
        $this->secretKey = config('services.binance.secret_key');
    }
    
    /**
     * Get account information
     */
    public function getAccountInfo(string $apiKey, string $secretKey): ?array
    {
        try {
            $response = $this->makeRequest('GET', '/api/v3/account', [
                'apiKey' => $apiKey,
                'secretKey' => $secretKey
            ]);
            
            return $response;
        } catch (\Exception $e) {
            Log::error('Binance API Error - Account Info: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Get current price for a symbol
     */
    public function getCurrentPrice(string $symbol): ?float
    {
        try {
            $response = $this->makeRequest('GET', '/api/v3/ticker/price', [
                'symbol' => $symbol
            ]);
            
            return (float) $response['price'];
        } catch (\Exception $e) {
            Log::error("Binance API Error - Price for {$symbol}: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Get 24hr ticker price change statistics
     */
    public function get24hrTicker(string $symbol): ?array
    {
        try {
            $response = $this->makeRequest('GET', '/api/v3/ticker/24hr', [
                'symbol' => $symbol
            ]);
            
            return $response;
        } catch (\Exception $e) {
            Log::error("Binance API Error - 24hr Ticker for {$symbol}: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Get kline/candlestick data
     */
    public function getKlines(string $symbol, string $interval = '1h', int $limit = 100): ?array
    {
        try {
            $response = $this->makeRequest('GET', '/api/v3/klines', [
                'symbol' => $symbol,
                'interval' => $interval,
                'limit' => $limit
            ]);
            
            return $response;
        } catch (\Exception $e) {
            Log::error("Binance API Error - Klines for {$symbol}: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Place a new order
     */
    public function placeOrder(string $apiKey, string $secretKey, array $orderData): ?array
    {
        try {
            $response = $this->makeRequest('POST', '/api/v3/order', array_merge($orderData, [
                'apiKey' => $apiKey,
                'secretKey' => $secretKey
            ]));
            
            return $response;
        } catch (\Exception $e) {
            Log::error('Binance API Error - Place Order: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Get order status
     */
    public function getOrderStatus(string $apiKey, string $secretKey, string $symbol, int $orderId): ?array
    {
        try {
            $response = $this->makeRequest('GET', '/api/v3/order', [
                'apiKey' => $apiKey,
                'secretKey' => $secretKey,
                'symbol' => $symbol,
                'orderId' => $orderId
            ]);
            
            return $response;
        } catch (\Exception $e) {
            Log::error('Binance API Error - Order Status: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Cancel an order
     */
    public function cancelOrder(string $apiKey, string $secretKey, string $symbol, int $orderId): ?array
    {
        try {
            $response = $this->makeRequest('DELETE', '/api/v3/order', [
                'apiKey' => $apiKey,
                'secretKey' => $secretKey,
                'symbol' => $symbol,
                'orderId' => $orderId
            ]);
            
            return $response;
        } catch (\Exception $e) {
            Log::error('Binance API Error - Cancel Order: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Get all open orders
     */
    public function getOpenOrders(string $apiKey, string $secretKey, string $symbol = null): ?array
    {
        try {
            $params = [
                'apiKey' => $apiKey,
                'secretKey' => $secretKey
            ];
            
            if ($symbol) {
                $params['symbol'] = $symbol;
            }
            
            $response = $this->makeRequest('GET', '/api/v3/openOrders', $params);
            
            return $response;
        } catch (\Exception $e) {
            Log::error('Binance API Error - Open Orders: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Get trading symbols
     */
    public function getExchangeInfo(): ?array
    {
        try {
            $response = $this->makeRequest('GET', '/api/v3/exchangeInfo');
            
            return $response;
        } catch (\Exception $e) {
            Log::error('Binance API Error - Exchange Info: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Calculate RSI indicator
     */
    public function calculateRSI(array $prices, int $period = 14): ?float
    {
        if (count($prices) < $period + 1) {
            return null;
        }
        
        $gains = [];
        $losses = [];
        
        for ($i = 1; $i < count($prices); $i++) {
            $change = $prices[$i] - $prices[$i - 1];
            if ($change > 0) {
                $gains[] = $change;
                $losses[] = 0;
            } else {
                $gains[] = 0;
                $losses[] = abs($change);
            }
        }
        
        $avgGain = array_sum(array_slice($gains, 0, $period)) / $period;
        $avgLoss = array_sum(array_slice($losses, 0, $period)) / $period;
        
        for ($i = $period; $i < count($gains); $i++) {
            $avgGain = (($avgGain * ($period - 1)) + $gains[$i]) / $period;
            $avgLoss = (($avgLoss * ($period - 1)) + $losses[$i]) / $period;
        }
        
        if ($avgLoss == 0) {
            return 100;
        }
        
        $rs = $avgGain / $avgLoss;
        $rsi = 100 - (100 / (1 + $rs));
        
        return $rsi;
    }
    
    /**
     * Calculate MACD indicator
     */
    public function calculateMACD(array $prices, int $fastPeriod = 12, int $slowPeriod = 26, int $signalPeriod = 9): ?array
    {
        if (count($prices) < $slowPeriod) {
            return null;
        }
        
        $emaFast = $this->calculateEMA($prices, $fastPeriod);
        $emaSlow = $this->calculateEMA($prices, $slowPeriod);
        
        if (!$emaFast || !$emaSlow) {
            return null;
        }
        
        $macdLine = [];
        for ($i = 0; $i < count($emaFast); $i++) {
            $macdLine[] = $emaFast[$i] - $emaSlow[$i];
        }
        
        $signalLine = $this->calculateEMA($macdLine, $signalPeriod);
        $histogram = [];
        
        for ($i = 0; $i < count($macdLine); $i++) {
            $histogram[] = $macdLine[$i] - ($signalLine[$i] ?? 0);
        }
        
        return [
            'macd' => end($macdLine),
            'signal' => end($signalLine),
            'histogram' => end($histogram)
        ];
    }
    
    /**
     * Calculate EMA (Exponential Moving Average)
     */
    protected function calculateEMA(array $prices, int $period): ?array
    {
        if (count($prices) < $period) {
            return null;
        }
        
        $ema = [];
        $multiplier = 2 / ($period + 1);
        
        // First EMA is SMA
        $sma = array_sum(array_slice($prices, 0, $period)) / $period;
        $ema[] = $sma;
        
        for ($i = $period; $i < count($prices); $i++) {
            $ema[] = ($prices[$i] * $multiplier) + ($ema[count($ema) - 1] * (1 - $multiplier));
        }
        
        return $ema;
    }
    
    /**
     * Make HTTP request to Binance API
     */
    protected function makeRequest(string $method, string $endpoint, array $params = []): array
    {
        $url = $this->baseUrl . $endpoint;
        $headers = [
            'Content-Type' => 'application/json',
            'User-Agent' => 'AI-Trade-App/1.0'
        ];
        
        // Add API key to headers if provided
        if (isset($params['apiKey'])) {
            $headers['X-MBX-APIKEY'] = $params['apiKey'];
            unset($params['apiKey']);
        }
        
        // Add signature for authenticated requests
        if (isset($params['secretKey'])) {
            $params['timestamp'] = time() * 1000;
            $queryString = http_build_query($params);
            $signature = hash_hmac('sha256', $queryString, $params['secretKey']);
            $params['signature'] = $signature;
            unset($params['secretKey']);
        }
        
        $options = [
            'headers' => $headers,
            'timeout' => 30
        ];
        
        if ($method === 'GET') {
            $url .= '?' . http_build_query($params);
        } else {
            $options['json'] = $params;
        }
        
        $response = $this->client->request($method, $url, $options);
        
        return json_decode($response->getBody()->getContents(), true);
    }
}
