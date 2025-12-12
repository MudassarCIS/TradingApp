<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Wallet;
use App\Models\Trade;
use App\Models\Agent;
use App\Models\Transaction;
use App\Models\UserInvoice;
use App\Models\Deposit;
use App\Models\Withdrawal;
use App\Models\CustomersWallet;
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
        
        // Calculate balance from customers_wallets table
        // Sum of all debit amounts minus sum of all credit amounts
        $totalDebits = CustomersWallet::where('user_id', $user->id)
            ->where('transaction_type', 'debit')
            ->sum('amount');
        
        $totalCredits = CustomersWallet::where('user_id', $user->id)
            ->where('transaction_type', 'credit')
            ->sum('amount');
        
        // Calculate available balance: (total debits - total credits), rounded to 2 decimals, minimum 0
        $availableBalance = max(0, round($totalDebits - $totalCredits, 2));
        
        // Ensure user has a main wallet (for backward compatibility, but we won't use its balance)
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
        
        // Get recent transactions (deposits only, withdrawals are separate)
        $recentTransactions = $user->transactions()
            ->where('type', 'deposit')
            ->with('user')
            ->latest()
            ->limit(5)
            ->get();
        
        // Get recent withdrawals from withdrawals table
        $recentWithdrawals = Withdrawal::where('user_id', $user->id)
            ->latest()
            ->limit(5)
            ->get();
        
        // Get recent trades
        $recentTrades = $user->trades()
            ->with('agent')
            ->latest()
            ->limit(5)
            ->get();
        
        
        // Get referral statistics
        $referralCount = $user->referredUsers()->count();
        $referralEarnings = $user->referrals()->sum('total_commission') ?? 0;
        
        // Get counts for quick actions
        $totalInvoices = $user->invoices()->count();
        $unpaidInvoices = $user->invoices()->where('status', 'Unpaid')->count();
        $totalDeposits = $user->deposits()->count();
        $pendingDeposits = $user->deposits()->where('status', 'pending')->count();
        $totalWithdrawals = Withdrawal::where('user_id', $user->id)->count();
        $pendingWithdrawals = Withdrawal::where('user_id', $user->id)->where('status', 'pending')->count();
        $totalTransactions = $user->transactions()->count();
        
        // Get active packages (with paid invoices)
        $activePackages = $user->getActivePackages();
        
        return view('customer.dashboard', compact(
            'wallet',
            'availableBalance',
            'totalTrades',
            'activeTrades',
            'profitableTrades',
            'totalProfit',
            'totalAgents',
            'activeAgents',
            'recentTransactions',
            'recentWithdrawals',
            'recentTrades',
            'referralCount',
            'referralEarnings',
            'totalInvoices',
            'unpaidInvoices',
            'totalDeposits',
            'pendingDeposits',
            'totalWithdrawals',
            'pendingWithdrawals',
            'totalTransactions',
            'activePackages'
        ));
    }
}
