<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Wallet;
use App\Models\Trade;
use App\Models\Agent;
use App\Models\Transaction;
use App\Models\Package;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        // Ensure user has a profile
        if (!$user->profile) {
            $user->profile()->create([
                'first_name' => $user->name,
                'referral_code' => strtoupper(substr($user->name, 0, 3) . rand(1000, 9999)),
            ]);
        }
        
        // Ensure user has a main wallet
        $wallet = $user->getMainWallet('USDT');
        if (!$wallet) {
            $wallet = Wallet::create([
                'user_id' => $user->id,
                'currency' => 'USDT',
                'balance' => 0,
                'total_deposited' => 0,
                'total_withdrawn' => 0,
                'total_profit' => 0,
                'total_loss' => 0,
            ]);
        }
        
        // Get trading statistics
        $totalTrades = $user->trades()->count();
        $activeTrades = $user->trades()->whereIn('status', ['pending', 'partially_filled'])->count();
        $profitableTrades = $user->trades()->where('profit_loss', '>', 0)->count();
        $totalProfit = $user->trades()->sum('profit_loss') ?? 0;
        
        // Get agent statistics
        $totalAgents = $user->agents()->count();
        $activeAgents = $user->agents()->where('status', 'active')->count();
        
        // Get recent transactions
        $recentTransactions = $user->transactions()
            ->with('user')
            ->latest()
            ->limit(5)
            ->get();
        
        // Get recent trades
        $recentTrades = $user->trades()
            ->with('agent')
            ->latest()
            ->limit(5)
            ->get();
        
        // Get available packages
        $packages = Package::where('is_active', true)->get();
        
        // Get referral statistics
        $referralCount = $user->referredUsers()->count();
        $referralEarnings = $user->referrals()->sum('total_commission') ?? 0;
        
        return view('customer.dashboard', compact(
            'wallet',
            'totalTrades',
            'activeTrades',
            'profitableTrades',
            'totalProfit',
            'totalAgents',
            'activeAgents',
            'recentTransactions',
            'recentTrades',
            'packages',
            'referralCount',
            'referralEarnings'
        ));
    }
}
