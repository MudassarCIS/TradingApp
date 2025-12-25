<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Trade;
use App\Models\Agent;
use App\Services\ExternalApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class TradingController extends Controller
{
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
     * Display the Trades Dashboard with embedded external page
     */
    public function dashboard()
    {
        return view('customer.trading.dashboard');
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
}
