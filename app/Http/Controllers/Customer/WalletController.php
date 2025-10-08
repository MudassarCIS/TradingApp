<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Wallet;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class WalletController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        // Ensure user has a main wallet
        $mainWallet = $user->getMainWallet('USDT');
        if (!$mainWallet) {
            $mainWallet = Wallet::create([
                'user_id' => $user->id,
                'currency' => 'USDT',
                'balance' => 0,
                'total_deposited' => 0,
                'total_withdrawn' => 0,
                'total_profit' => 0,
                'total_loss' => 0,
            ]);
        }
        
        $wallets = $user->wallets()->get();
        
        return view('customer.wallet.index', compact('wallets'));
    }

    public function deposit()
    {
        $user = Auth::user();
        $wallet = $user->getMainWallet('USDT');
        
        // Generate deposit address (in real app, this would come from admin settings)
        $depositAddress = 'TQn9Y2khEsLJW1ChVWFMSMeRDow5KcbLSE'; // Example USDT address
        $qrCode = 'data:image/png;base64,' . base64_encode('QR_CODE_DATA'); // Generate QR code
        
        return view('customer.wallet.deposit', compact('wallet', 'depositAddress', 'qrCode'));
    }

    public function withdraw()
    {
        $user = Auth::user();
        $wallet = $user->getMainWallet('USDT');
        
        return view('customer.wallet.withdraw', compact('wallet'));
    }

    public function processWithdrawal(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:10',
            'address' => 'required|string',
            'transaction_password' => 'required|string',
        ]);

        $user = Auth::user();
        $wallet = $user->getMainWallet('USDT');
        
        // Verify transaction password
        if (!Hash::check($request->transaction_password, $user->profile->transaction_password)) {
            return back()->withErrors(['transaction_password' => 'Invalid transaction password']);
        }

        // Check if user has sufficient balance
        if (!$wallet->canWithdraw($request->amount)) {
            return back()->withErrors(['amount' => 'Insufficient balance']);
        }

        // Create withdrawal transaction
        $transaction = Transaction::create([
            'user_id' => $user->id,
            'transaction_id' => (new Transaction())->generateTransactionId(),
            'type' => 'withdrawal',
            'status' => 'pending',
            'currency' => 'USDT',
            'amount' => $request->amount,
            'fee' => 5, // 5 USDT withdrawal fee
            'net_amount' => $request->amount - 5,
            'to_address' => $request->address,
            'notes' => 'Withdrawal request',
        ]);

        // Lock the balance
        $wallet->lockBalance($request->amount);

        return redirect()->route('customer.wallet.history')
            ->with('success', 'Withdrawal request submitted successfully');
    }

    public function history()
    {
        $user = Auth::user();
        $transactions = $user->transactions()
            ->latest()
            ->paginate(20);
        
        return view('customer.wallet.history', compact('transactions'));
    }

    public function purchases()
    {
        $user = Auth::user();
        $purchases = $user->transactions()
            ->where('type', 'deposit')
            ->latest()
            ->paginate(20);
        
        return view('customer.wallet.purchases', compact('purchases'));
    }
}
