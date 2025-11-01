<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Trade;
use App\Models\Transaction;
use App\Models\Agent;
use App\Models\Wallet;
use App\Models\Message;
use App\Models\Deposit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // Get statistics
        $totalUsers = User::where('user_type', 'customer')->count();
        $activeUsers = User::where('user_type', 'customer')->where('is_active', true)->count();
        $totalTrades = Trade::count();
        $activeTrades = Trade::whereIn('status', ['pending', 'partially_filled'])->count();
        $totalTransactions = Transaction::count();
        $pendingTransactions = Transaction::where('status', 'pending')->count();
        $totalAgents = Agent::count();
        $activeAgents = Agent::where('status', 'active')->count();
        
        // Get revenue statistics
        $totalDeposits = Transaction::where('type', 'deposit')
            ->where('status', 'completed')
            ->sum('amount');
        $totalWithdrawals = Transaction::where('type', 'withdrawal')
            ->where('status', 'completed')
            ->sum('amount');
        $totalProfit = Trade::sum('profit_loss');
        $adminProfit = $totalProfit * 0.5; // Admin takes 50%
        
        // Get recent activities
        $recentUsers = User::where('user_type', 'customer')
            ->with('profile')
            ->latest()
            ->limit(5)
            ->get();
        
        $recentTrades = Trade::with(['user', 'agent'])
            ->latest()
            ->limit(5)
            ->get();
        
        $recentTransactions = Transaction::with('user')
            ->latest()
            ->limit(5)
            ->get();
        
        $pendingMessages = Message::where('status', 'open')
            ->with('user')
            ->latest()
            ->limit(5)
            ->get();
        
        // Get counts for quick links
        $pendingDeposits = Deposit::where('status', 'pending')->count();
        $totalDepositsCount = Deposit::count();
        $totalUsersCount = User::where('user_type', 'customer')->count();
        $totalTransactionsCount = Transaction::count();
        $totalTradesCount = Trade::count();
        $totalAgentsCount = Agent::count();
        
        // Get trading statistics by day (last 7 days)
        $tradingStats = Trade::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as trades_count'),
                DB::raw('SUM(profit_loss) as total_profit')
            )
            ->where('created_at', '>=', now()->subDays(7))
            ->groupBy('date')
            ->orderBy('date')
            ->get();
        
        // Get user registration stats (last 30 days)
        $userStats = User::where('user_type', 'customer')
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as users_count')
            )
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get();
        
        return view('admin.dashboard', compact(
            'totalUsers',
            'activeUsers',
            'totalTrades',
            'activeTrades',
            'totalTransactions',
            'pendingTransactions',
            'totalAgents',
            'activeAgents',
            'totalDeposits',
            'totalWithdrawals',
            'totalProfit',
            'adminProfit',
            'recentUsers',
            'recentTrades',
            'recentTransactions',
            'pendingMessages',
            'tradingStats',
            'userStats',
            'pendingDeposits',
            'totalDepositsCount',
            'totalUsersCount',
            'totalTransactionsCount',
            'totalTradesCount',
            'totalAgentsCount'
        ));
    }
}
