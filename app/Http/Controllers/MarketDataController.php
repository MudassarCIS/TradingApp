<?php

namespace App\Http\Controllers;

use App\Services\BinanceApiService;
use App\Services\BingXApiService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class MarketDataController extends Controller
{
    protected $binanceApi;
    protected $bingxApi;
    
    public function __construct()
    {
        $this->binanceApi = new BinanceApiService();
        $this->bingxApi = new BingXApiService();
    }
    
    /**
     * Get current price for a symbol
     */
    public function getPrice(string $symbol): JsonResponse
    {
        try {
            // Try Binance first
            $price = $this->binanceApi->getCurrentPrice($symbol);
            $exchange = 'binance';
            
            // Fallback to BingX
            if (!$price) {
                $price = $this->bingxApi->getCurrentPrice($symbol);
                $exchange = 'bingx';
            }
            
            if (!$price) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unable to fetch price data'
                ], 500);
            }
            
            return response()->json([
                'success' => true,
                'symbol' => $symbol,
                'price' => $price,
                'exchange' => $exchange,
                'timestamp' => now()->timestamp
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching price data: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get 24hr ticker data
     */
    public function getTicker(string $symbol): JsonResponse
    {
        try {
            // Try Binance first
            $ticker = $this->binanceApi->get24hrTicker($symbol);
            $exchange = 'binance';
            
            // Fallback to BingX
            if (!$ticker) {
                $ticker = $this->bingxApi->get24hrTicker($symbol);
                $exchange = 'bingx';
            }
            
            if (!$ticker) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unable to fetch ticker data'
                ], 500);
            }
            
            return response()->json([
                'success' => true,
                'symbol' => $symbol,
                'data' => $ticker,
                'exchange' => $exchange,
                'timestamp' => now()->timestamp
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching ticker data: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get kline/candlestick data
     */
    public function getKlines(string $symbol, string $interval = '1h', int $limit = 100): JsonResponse
    {
        try {
            // Try Binance first
            $klines = $this->binanceApi->getKlines($symbol, $interval, $limit);
            $exchange = 'binance';
            
            // Fallback to BingX
            if (!$klines) {
                $klines = $this->bingxApi->getKlines($symbol, $interval, $limit);
                $exchange = 'bingx';
            }
            
            if (!$klines) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unable to fetch kline data'
                ], 500);
            }
            
            return response()->json([
                'success' => true,
                'symbol' => $symbol,
                'interval' => $interval,
                'data' => $klines,
                'exchange' => $exchange,
                'timestamp' => now()->timestamp
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching kline data: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get market data with technical indicators
     */
    public function getMarketData(string $symbol): JsonResponse
    {
        try {
            // Get price and ticker data
            $price = $this->binanceApi->getCurrentPrice($symbol) ?? $this->bingxApi->getCurrentPrice($symbol);
            $ticker = $this->binanceApi->get24hrTicker($symbol) ?? $this->bingxApi->get24hrTicker($symbol);
            
            if (!$price || !$ticker) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unable to fetch market data'
                ], 500);
            }
            
            // Get historical data for technical indicators
            $klines = $this->binanceApi->getKlines($symbol, '1h', 100) ?? $this->bingxApi->getKlines($symbol, '1h', 100);
            $prices = $klines ? array_column($klines, 4) : []; // Close prices
            
            // Calculate technical indicators
            $rsi = $this->binanceApi->calculateRSI($prices) ?? $this->bingxApi->calculateRSI($prices);
            $macd = $this->binanceApi->calculateMACD($prices) ?? $this->bingxApi->calculateMACD($prices);
            
            return response()->json([
                'success' => true,
                'symbol' => $symbol,
                'data' => [
                    'price' => $price,
                    'rsi' => $rsi ?? 50,
                    'macd' => $macd['macd'] ?? 0,
                    'macd_signal' => $macd['signal'] ?? 0,
                    'macd_histogram' => $macd['histogram'] ?? 0,
                    'volume' => (float) $ticker['volume'],
                    'price_change_24h' => (float) $ticker['priceChangePercent'],
                    'high_24h' => (float) $ticker['highPrice'],
                    'low_24h' => (float) $ticker['lowPrice'],
                    'open_price' => (float) $ticker['openPrice'],
                    'close_price' => (float) $ticker['lastPrice'],
                ],
                'timestamp' => now()->timestamp
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching market data: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get multiple symbols data
     */
    public function getMultiplePrices(Request $request): JsonResponse
    {
        $symbols = $request->input('symbols', ['BTCUSDT', 'ETHUSDT', 'ADAUSDT']);
        $data = [];
        
        foreach ($symbols as $symbol) {
            $price = $this->binanceApi->getCurrentPrice($symbol) ?? $this->bingxApi->getCurrentPrice($symbol);
            if ($price) {
                $data[] = [
                    'symbol' => $symbol,
                    'price' => $price,
                    'timestamp' => now()->timestamp
                ];
            }
        }
        
        return response()->json([
            'success' => true,
            'data' => $data,
            'timestamp' => now()->timestamp
        ]);
    }
}
