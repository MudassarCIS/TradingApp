<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Wallet;
use App\Models\Transaction;
use App\Models\WalletAddress;
use App\Models\Deposit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
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
        
        // Fetch active wallet addresses from admin settings
        $walletAddresses = WalletAddress::active()->ordered()->get();
        
        // Fetch user's recent deposits
        $recentDeposits = Deposit::where('user_id', $user->id)
            ->latest()
            ->limit(5)
            ->get();
        
        return view('customer.wallet.deposit', compact('wallet', 'walletAddresses', 'recentDeposits'));
    }

    public function submitDeposit(Request $request)
    {
        try {
            $validated = $request->validate([
                'amount' => 'required|numeric|min:0.01',
                'currency' => 'required|string|max:10',
                'network' => 'required|string|max:20',
                'proof_image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
                'notes' => 'nullable|string|max:1000'
            ]);

            $user = Auth::user();

            // Generate unique deposit ID
            $depositId = 'DEP' . strtoupper(Str::random(8)) . time();

            // Handle file upload
            $proofImagePath = null;
            if ($request->hasFile('proof_image')) {
                $file = $request->file('proof_image');
                $proofImagePath = $file->store('deposits/proofs', 'public');
                
                // Ensure storage directory exists
                if (!$proofImagePath) {
                    return redirect()->route('customer.wallet.deposit')
                        ->with('error', 'Failed to upload proof image. Please try again.');
                }
            }

            // Create deposit record
            $deposit = Deposit::create([
                'user_id' => $user->id,
                'deposit_id' => $depositId,
                'amount' => $validated['amount'],
                'currency' => $validated['currency'],
                'network' => $validated['network'],
                'status' => 'pending',
                'proof_image' => $proofImagePath,
                'notes' => $validated['notes'] ?? null
            ]);

            if ($deposit) {
                return redirect()->route('customer.wallet.deposit')
                    ->with('success', 'Deposit submitted successfully! Your deposit ID is: ' . $depositId . '. We will review it within 24 hours.');
            } else {
                return redirect()->route('customer.wallet.deposit')
                    ->with('error', 'Failed to create deposit. Please try again.');
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->route('customer.wallet.deposit')
                ->withErrors($e->errors())
                ->withInput();
        } catch (\Exception $e) {
            Log::error('Deposit submission error: ' . $e->getMessage());
            return redirect()->route('customer.wallet.deposit')
                ->with('error', 'An error occurred while submitting your deposit. Please try again.');
        }
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

    public function history(Request $request)
    {
        $user = Auth::user();
        
        // If it's an AJAX request for DataTables
        if ($request->ajax()) {
            return $this->getTransactionHistoryData($user, $request);
        }
        
        return view('customer.wallet.history');
    }
    
    private function getTransactionHistoryData($user, Request $request)
    {
        $query = $user->transactions()->select([
            'id',
            'transaction_id',
            'type',
            'status',
            'currency',
            'amount',
            'fee',
            'net_amount',
            'from_address',
            'to_address',
            'tx_hash',
            'notes',
            'created_at',
            'processed_at'
        ]);
        
        // Apply search filter
        if ($request->has('search') && !empty($request->search['value'])) {
            $searchValue = $request->search['value'];
            $query->where(function($q) use ($searchValue) {
                $q->where('transaction_id', 'like', "%{$searchValue}%")
                  ->orWhere('type', 'like', "%{$searchValue}%")
                  ->orWhere('status', 'like', "%{$searchValue}%")
                  ->orWhere('currency', 'like', "%{$searchValue}%")
                  ->orWhere('notes', 'like', "%{$searchValue}%");
            });
        }
        
        // Apply type filter
        if ($request->has('type') && !empty($request->type)) {
            $query->where('type', $request->type);
        }
        
        // Apply status filter
        if ($request->has('status') && !empty($request->status)) {
            $query->where('status', $request->status);
        }
        
        // Apply date range filter
        if ($request->has('date_from') && !empty($request->date_from)) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        
        if ($request->has('date_to') && !empty($request->date_to)) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        
        // Get total count before pagination
        $totalRecords = $query->count();
        
        // Apply ordering
        $orderColumn = $request->get('order')[0]['column'] ?? 0;
        $orderDirection = $request->get('order')[0]['dir'] ?? 'desc';
        
        $columns = [
            'created_at',
            'transaction_id',
            'type',
            'status',
            'amount',
            'fee',
            'net_amount',
            'currency',
            'notes'
        ];
        
        if (isset($columns[$orderColumn])) {
            $query->orderBy($columns[$orderColumn], $orderDirection);
        } else {
            $query->orderBy('created_at', 'desc');
        }
        
        // Apply pagination
        $start = $request->get('start', 0);
        $length = $request->get('length', 10);
        $transactions = $query->offset($start)->limit($length)->get();
        
        // Format data for DataTables
        $data = $transactions->map(function($transaction) {
            return [
                'id' => $transaction->id,
                'created_at' => $transaction->created_at->format('M d, Y H:i'),
                'transaction_id' => $transaction->transaction_id,
                'type' => $this->formatTransactionType($transaction->type),
                'status' => $this->formatTransactionStatus($transaction->status),
                'amount' => number_format($transaction->amount, 2),
                'fee' => $transaction->fee ? number_format($transaction->fee, 2) : '-',
                'net_amount' => $transaction->net_amount ? number_format($transaction->net_amount, 2) : '-',
                'currency' => $transaction->currency,
                'notes' => $transaction->notes ?? '-',
                'tx_hash' => $transaction->tx_hash,
                'from_address' => $transaction->from_address,
                'to_address' => $transaction->to_address,
                'processed_at' => $transaction->processed_at ? $transaction->processed_at->format('M d, Y H:i') : '-',
            ];
        });
        
        return response()->json([
            'draw' => intval($request->get('draw')),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $totalRecords,
            'data' => $data
        ]);
    }
    
    private function formatTransactionType($type)
    {
        $types = [
            'deposit' => '<span class="badge bg-success"><i class="bi bi-arrow-down"></i> Deposit</span>',
            'withdrawal' => '<span class="badge bg-warning"><i class="bi bi-arrow-up"></i> Withdrawal</span>',
            'transfer' => '<span class="badge bg-info"><i class="bi bi-arrow-left-right"></i> Transfer</span>',
            'bonus' => '<span class="badge bg-primary"><i class="bi bi-gift"></i> Bonus</span>',
            'commission' => '<span class="badge bg-secondary"><i class="bi bi-percent"></i> Commission</span>',
            'refund' => '<span class="badge bg-danger"><i class="bi bi-arrow-counterclockwise"></i> Refund</span>',
        ];
        
        return $types[$type] ?? '<span class="badge bg-light text-dark">' . ucfirst($type) . '</span>';
    }
    
    private function formatTransactionStatus($status)
    {
        $statuses = [
            'pending' => '<span class="badge bg-warning"><i class="bi bi-clock"></i> Pending</span>',
            'completed' => '<span class="badge bg-success"><i class="bi bi-check-circle"></i> Completed</span>',
            'failed' => '<span class="badge bg-danger"><i class="bi bi-x-circle"></i> Failed</span>',
            'cancelled' => '<span class="badge bg-secondary"><i class="bi bi-dash-circle"></i> Cancelled</span>',
        ];
        
        return $statuses[$status] ?? '<span class="badge bg-light text-dark">' . ucfirst($status) . '</span>';
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
