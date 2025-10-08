<?php

namespace App\Services;

use App\Models\Agent;
use App\Models\Trade;
use App\Models\Wallet;
use App\Models\Transaction;
use App\Models\Notification;
use Illuminate\Support\Facades\Log;

class TradingEngineService
{
    protected $binanceApi;
    protected $bingxApi;
    
    public function __construct()
    {
        // Initialize API connections
        $this->binanceApi = new BinanceApiService();
        $this->bingxApi = new BingXApiService();
    }
    
    /**
     * Execute trading for all active agents
     */
    public function executeTrading()
    {
        $activeAgents = Agent::where('status', 'active')
            ->where('auto_trading', true)
            ->with('user')
            ->get();
            
        foreach ($activeAgents as $agent) {
            try {
                $this->processAgent($agent);
            } catch (\Exception $e) {
                Log::error("Trading error for agent {$agent->id}: " . $e->getMessage());
            }
        }
    }
    
    /**
     * Process individual agent trading
     */
    protected function processAgent(Agent $agent)
    {
        $user = $agent->user;
        $wallet = $user->getMainWallet('USDT');
        
        if (!$wallet || $wallet->available_balance < 10) {
            return; // Insufficient balance
        }
        
        $tradingRules = $agent->trading_rules;
        $symbols = $tradingRules['symbols'] ?? ['BTCUSDT', 'ETHUSDT'];
        
        foreach ($symbols as $symbol) {
            $this->analyzeAndTrade($agent, $symbol, $wallet);
        }
    }
    
    /**
     * Analyze market and execute trades
     */
    protected function analyzeAndTrade(Agent $agent, string $symbol, Wallet $wallet)
    {
        try {
            // Get current market data
            $marketData = $this->getMarketData($symbol);
            
            if (!$marketData) {
                return;
            }
            
            $currentPrice = $marketData['price'];
            $tradingRules = $agent->trading_rules;
            
            // Check for buy signals
            if ($this->shouldBuy($marketData, $tradingRules)) {
                $this->executeBuyOrder($agent, $symbol, $currentPrice, $wallet);
            }
            
            // Check for sell signals
            $this->checkSellSignals($agent, $symbol, $currentPrice);
            
        } catch (\Exception $e) {
            Log::error("Trading analysis error for {$symbol}: " . $e->getMessage());
        }
    }
    
    /**
     * Determine if we should buy
     */
    protected function shouldBuy(array $marketData, array $rules): bool
    {
        $currentPrice = $marketData['price'];
        $rsi = $marketData['rsi'] ?? 50;
        $macd = $marketData['macd'] ?? 0;
        $volume = $marketData['volume'] ?? 0;
        
        // RSI oversold condition
        if ($rules['rsi_oversold'] && $rsi < $rules['rsi_oversold_threshold']) {
            return true;
        }
        
        // MACD bullish crossover
        if ($rules['macd_bullish'] && $macd > 0) {
            return true;
        }
        
        // Volume spike
        if ($rules['volume_spike'] && $volume > $rules['volume_threshold']) {
            return true;
        }
        
        // Price momentum
        if ($rules['momentum_bullish'] && $marketData['price_change_24h'] > 0) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Execute buy order
     */
    protected function executeBuyOrder(Agent $agent, string $symbol, float $price, Wallet $wallet)
    {
        $tradingRules = $agent->trading_rules;
        $riskAmount = $wallet->available_balance * ($tradingRules['risk_per_trade'] / 100);
        $quantity = $riskAmount / $price;
        
        if ($quantity < 0.001) {
            return; // Quantity too small
        }
        
        // Create trade record
        $trade = Trade::create([
            'user_id' => $agent->user_id,
            'agent_id' => $agent->id,
            'trade_id' => $this->generateTradeId(),
            'symbol' => $symbol,
            'side' => 'buy',
            'type' => 'market',
            'quantity' => $quantity,
            'price' => $price,
            'status' => 'pending',
            'exchange' => $tradingRules['exchange'] ?? 'binance',
        ]);
        
        // Lock the balance
        $wallet->lockBalance($riskAmount);
        
        // Send notification
        $this->sendNotification($agent->user_id, 'trade_opened', 'Trade Opened', 
            "Buy order for {$quantity} {$symbol} at ${$price}");
        
        // Update agent stats
        $agent->last_trade_at = now();
        $agent->save();
    }
    
    /**
     * Check for sell signals
     */
    protected function checkSellSignals(Agent $agent, string $symbol, float $currentPrice)
    {
        $openTrades = Trade::where('user_id', $agent->user_id)
            ->where('agent_id', $agent->id)
            ->where('symbol', $symbol)
            ->whereIn('status', ['pending', 'partially_filled'])
            ->where('side', 'buy')
            ->get();
            
        foreach ($openTrades as $trade) {
            $profitLoss = $this->calculateProfitLoss($trade, $currentPrice);
            $profitPercentage = ($profitLoss / ($trade->quantity * $trade->price)) * 100;
            
            $tradingRules = $agent->trading_rules;
            
            // Check stop loss
            if ($tradingRules['stop_loss'] && $profitPercentage <= -$tradingRules['stop_loss_percentage']) {
                $this->executeSellOrder($trade, $currentPrice, 'stop_loss');
            }
            
            // Check take profit
            if ($tradingRules['take_profit'] && $profitPercentage >= $tradingRules['take_profit_percentage']) {
                $this->executeSellOrder($trade, $currentPrice, 'take_profit');
            }
        }
    }
    
    /**
     * Execute sell order
     */
    protected function executeSellOrder(Trade $trade, float $price, string $reason)
    {
        $trade->update([
            'side' => 'sell',
            'price' => $price,
            'status' => 'filled',
            'closed_at' => now(),
            'profit_loss' => $this->calculateProfitLoss($trade, $price),
        ]);
        
        // Unlock and update wallet
        $wallet = $trade->user->getMainWallet('USDT');
        $wallet->unlockBalance($trade->quantity * $trade->price);
        
        if ($trade->profit_loss > 0) {
            $wallet->total_profit += $trade->profit_loss;
        } else {
            $wallet->total_loss += abs($trade->profit_loss);
        }
        $wallet->save();
        
        // Update agent stats
        $agent = $trade->agent;
        if ($agent) {
            $agent->updateStats();
        }
        
        // Send notification
        $this->sendNotification($trade->user_id, 'trade_closed', 'Trade Closed', 
            "Sell order for {$trade->quantity} {$trade->symbol} at ${$price} ({$reason})");
    }
    
    /**
     * Calculate profit/loss
     */
    protected function calculateProfitLoss(Trade $trade, float $currentPrice): float
    {
        return ($currentPrice - $trade->price) * $trade->quantity;
    }
    
    /**
     * Get market data from exchange
     */
    protected function getMarketData(string $symbol): ?array
    {
        try {
            // Try Binance first
            $binancePrice = $this->binanceApi->getCurrentPrice($symbol);
            $binanceTicker = $this->binanceApi->get24hrTicker($symbol);
            
            if ($binancePrice && $binanceTicker) {
                // Get historical data for technical indicators
                $klines = $this->binanceApi->getKlines($symbol, '1h', 100);
                $prices = array_column($klines, 4); // Close prices
                
                $rsi = $this->binanceApi->calculateRSI($prices);
                $macd = $this->binanceApi->calculateMACD($prices);
                
                return [
                    'price' => $binancePrice,
                    'rsi' => $rsi ?? 50,
                    'macd' => $macd['macd'] ?? 0,
                    'volume' => (float) $binanceTicker['volume'],
                    'price_change_24h' => (float) $binanceTicker['priceChangePercent'],
                    'high_24h' => (float) $binanceTicker['highPrice'],
                    'low_24h' => (float) $binanceTicker['lowPrice'],
                ];
            }
            
            // Fallback to BingX
            $bingxPrice = $this->bingxApi->getCurrentPrice($symbol);
            $bingxTicker = $this->bingxApi->get24hrTicker($symbol);
            
            if ($bingxPrice && $bingxTicker) {
                $klines = $this->bingxApi->getKlines($symbol, '1h', 100);
                $prices = array_column($klines, 4);
                
                $rsi = $this->bingxApi->calculateRSI($prices);
                $macd = $this->bingxApi->calculateMACD($prices);
                
                return [
                    'price' => $bingxPrice,
                    'rsi' => $rsi ?? 50,
                    'macd' => $macd['macd'] ?? 0,
                    'volume' => (float) $bingxTicker['volume'],
                    'price_change_24h' => (float) $bingxTicker['priceChangePercent'],
                    'high_24h' => (float) $bingxTicker['highPrice'],
                    'low_24h' => (float) $bingxTicker['lowPrice'],
                ];
            }
            
            // Fallback to mock data if APIs fail
            Log::warning("Both APIs failed for {$symbol}, using mock data");
            return [
                'price' => rand(30000, 70000),
                'rsi' => rand(20, 80),
                'macd' => rand(-100, 100),
                'volume' => rand(1000000, 10000000),
                'price_change_24h' => rand(-5, 5),
            ];
            
        } catch (\Exception $e) {
            Log::error("Market data error for {$symbol}: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Generate unique trade ID
     */
    protected function generateTradeId(): string
    {
        return 'TRD' . time() . rand(1000, 9999);
    }
    
    /**
     * Send notification to user
     */
    protected function sendNotification(int $userId, string $type, string $title, string $message)
    {
        Notification::createForUser($userId, $type, $title, $message);
    }
}

class BinanceApiService
{
    // Placeholder for Binance API integration
    public function getMarketData(string $symbol): array
    {
        return [];
    }
}

class BingXApiService
{
    // Placeholder for BingX API integration
    public function getMarketData(string $symbol): array
    {
        return [];
    }
}
