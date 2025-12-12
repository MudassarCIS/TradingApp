<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Wallet;
use App\Models\Transaction;
use App\Models\WalletAddress;
use App\Models\Deposit;
use App\Models\UserInvoice;
use App\Models\CustomersWallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;

class WalletController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        // Calculate total deposits from customers_wallets table (sum of all debit amounts)
        $totalDeposits = CustomersWallet::where('user_id', $user->id)
            ->where('transaction_type', 'debit')
            ->sum('amount');
        
        // Calculate total withdrawals from customers_wallets table (sum of all credit amounts)
        $totalWithdrawals = CustomersWallet::where('user_id', $user->id)
            ->where('transaction_type', 'credit')
            ->sum('amount');
        
        // Calculate available balance: (total debits - total credits), rounded to 2 decimals, minimum 0
        $availableBalance = max(0, round($totalDeposits - $totalWithdrawals, 2));
        
        // Get wallet history from customers_wallets table (all transactions for the user)
        $walletHistory = CustomersWallet::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        
        // Ensure user has a main wallet (for backward compatibility)
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
        
        return view('customer.wallet.index', compact('wallets', 'totalDeposits', 'totalWithdrawals', 'availableBalance', 'walletHistory'));
    }

    public function deposit(Request $request)
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
        
        // Get invoice_id from URL if present
        $invoiceId = $request->query('invoice_id');
        $selectedInvoice = null;
        
        // Fetch unpaid invoices for dropdown
        if ($invoiceId) {
            // If invoice_id is in URL, only show that specific invoice (if it's unpaid)
            $selectedInvoice = UserInvoice::with(['plan', 'rentBotPackage'])
                ->where('user_id', $user->id)
                ->where('id', $invoiceId)
                ->where('status', 'Unpaid')
                ->first();
            
            // Only include the selected invoice in the dropdown if it exists and is unpaid
            $unpaidInvoices = $selectedInvoice ? collect([$selectedInvoice]) : collect([]);
        } else {
            // If no invoice_id in URL, show all unpaid invoices
            $unpaidInvoices = UserInvoice::with(['plan', 'rentBotPackage'])
                ->where('user_id', $user->id)
                ->where('status', 'Unpaid')
                ->orderBy('created_at', 'desc')
                ->get();
        }
        
        return view('customer.wallet.deposit', compact('wallet', 'walletAddresses', 'recentDeposits', 'unpaidInvoices', 'invoiceId', 'selectedInvoice'));
    }

    public function submitDeposit(Request $request)
    {
        try {
            $validated = $request->validate([
                'amount' => 'required|numeric|min:0.01',
                'currency' => 'required|string|max:10',
                'network' => 'required|string|max:20',
                'trans_id' => 'required|string|max:255',
                'proof_image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
                'notes' => 'nullable|string|max:1000',
                'invoice_id' => 'nullable|exists:user_invoices,id'
            ]);

            $user = Auth::user();
            
            // Validate invoice_id belongs to user if provided
            if ($request->filled('invoice_id')) {
                $invoice = UserInvoice::where('id', $request->invoice_id)
                    ->where('user_id', $user->id)
                    ->first();
                    
                if (!$invoice) {
                    return redirect()->route('customer.wallet.deposit')
                        ->with('error', 'Invalid invoice selected.');
                }
            }

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

            // Get invoice_type from invoice if invoice_id is provided
            $invoiceType = null;
            if ($request->filled('invoice_id')) {
                $invoice = UserInvoice::where('id', $request->invoice_id)
                    ->where('user_id', $user->id)
                    ->first();
                if ($invoice) {
                    $invoiceType = $invoice->invoice_type;
                }
            }

            // Use database transaction to ensure atomicity
            $deposit = DB::transaction(function () use ($user, $request, $validated, $proofImagePath, $invoiceType) {
                // Create deposit record first (without deposit_id, it's nullable now)
                $deposit = Deposit::create([
                    'user_id' => $user->id,
                    'invoice_id' => $request->invoice_id ?? null,
                    'invoice_type' => $invoiceType,
                    'trans_id' => $validated['trans_id'],
                    'deposit_id' => null, // Will be set after creation
                    'amount' => $validated['amount'],
                    'currency' => $validated['currency'],
                    'network' => $validated['network'],
                    'status' => 'pending',
                    'proof_image' => $proofImagePath,
                    'notes' => $validated['notes'] ?? null
                ]);

                // Generate deposit_id with format TRX-000{id} using the inserted deposit ID
                // Format: TRX-0001, TRX-0010, TRX-0100, TRX-1000, etc.
                $depositId = 'TRX-' . str_pad($deposit->id, 4, '0', STR_PAD_LEFT);
                $deposit->update(['deposit_id' => $depositId]);
                
                return $deposit->fresh(); // Return fresh instance with updated deposit_id
            });

            // Update invoice status to payment_pending if invoice_id is provided
            if ($deposit && $request->filled('invoice_id')) {
                UserInvoice::where('id', $request->invoice_id)
                    ->where('user_id', $user->id)
                    ->update(['status' => 'payment_pending']);
            }
            
            return redirect()->route('customer.bots.index')
                ->with('success', 'Deposit submitted successfully! Your deposit ID is: ' . $deposit->deposit_id . '. We will review it within 24 hours.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->route('customer.wallet.deposit')
                ->withErrors($e->errors())
                ->withInput();
        } catch (\Exception $e) {
            Log::error('Deposit submission error: ' . $e->getMessage());
            Log::error('Deposit submission stack trace: ' . $e->getTraceAsString());
            Log::error('Deposit submission request data: ', $request->except(['proof_image', '_token']));
            
            // Show user-friendly error message
            $errorMessage = 'An error occurred while submitting your deposit. Please try again.';
            if (config('app.debug')) {
                $errorMessage .= ' Error: ' . $e->getMessage();
            }
            
            return redirect()->route('customer.wallet.deposit')
                ->with('error', $errorMessage)
                ->withInput();
        }
    }

    public function withdraw()
    {
        $user = Auth::user();
        
        // Calculate balance from customers_wallets table
        $totalDebits = CustomersWallet::where('user_id', $user->id)
            ->where('transaction_type', 'debit')
            ->sum('amount');
        
        $totalCredits = CustomersWallet::where('user_id', $user->id)
            ->where('transaction_type', 'credit')
            ->sum('amount');
        
        // Calculate available balance: (total debits - total credits), rounded to 2 decimals, minimum 0
        $availableBalance = max(0, round($totalDebits - $totalCredits, 2));
        
        // Calculate total withdrawals for display
        $totalWithdrawals = $totalCredits;
        
        // Get wallet for backward compatibility (but we won't use its balance)
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
        
        return view('customer.wallet.withdraw', compact('wallet', 'availableBalance', 'totalWithdrawals'));
    }

    public function processWithdrawal(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:20',
            'address' => 'required|string',
            'transaction_password' => 'nullable|string',
        ]);

        $user = Auth::user();
        
        // Verify transaction password only if provided
        if ($request->filled('transaction_password')) {
            // Check if user has a transaction password set
            if ($user->profile && $user->profile->transaction_password) {
                if (!Hash::check($request->transaction_password, $user->profile->transaction_password)) {
                    return back()->withErrors(['transaction_password' => 'Invalid transaction password']);
                }
            }
        }

        // Check minimum withdrawal amount
        if ($request->amount < 20) {
            return back()->withErrors(['amount' => 'Minimum withdrawal is 20 USDT']);
        }

        // Check withdrawal limit per month
        $setting = \App\Models\Setting::get();
        $withdrawalLimit = $setting->withdrawal_limit_per_month ?? 5;
        
        // Count withdrawals this month
        $startOfMonth = now()->startOfMonth();
        $withdrawalsThisMonth = \App\Models\Withdrawal::where('user_id', $user->id)
            ->where('created_at', '>=', $startOfMonth)
            ->whereIn('status', ['pending', 'processing', 'completed'])
            ->count();
        
        if ($withdrawalsThisMonth >= $withdrawalLimit) {
            return back()->withErrors(['amount' => 'You have reached the monthly withdrawal limit of ' . $withdrawalLimit . ' withdrawals. Please wait until next month.']);
        }

        $withdrawalFee = 2; // 2 USDT withdrawal fee
        $totalRequired = $request->amount + $withdrawalFee;

        // Use database transaction to prevent multiple simultaneous withdrawals and ensure data integrity
        try {
            $result = DB::transaction(function () use ($user, $request, $totalRequired, $withdrawalFee) {
                // Check for existing pending withdrawals - prevent multiple simultaneous withdrawals
                $existingPendingWithdrawal = \App\Models\Withdrawal::where('user_id', $user->id)
                    ->where('status', 'pending')
                    ->lockForUpdate() // Lock row to prevent concurrent withdrawals
                    ->first();
                
                if ($existingPendingWithdrawal) {
                    throw new \Exception('You already have a pending withdrawal request. Please wait for it to be processed before creating a new one.');
                }

                // Calculate balance from customers_wallets table (inside transaction for consistency)
                $totalDebits = CustomersWallet::where('user_id', $user->id)
                    ->where('transaction_type', 'debit')
                    ->sum('amount');
                
                $totalCredits = CustomersWallet::where('user_id', $user->id)
                    ->where('transaction_type', 'credit')
                    ->sum('amount');
                
                // Calculate available balance: (total debits - total credits), rounded to 2 decimals, minimum 0
                $availableBalance = max(0, round($totalDebits - $totalCredits, 2));
                
                // Check if user has sufficient balance
                if ($totalRequired > $availableBalance) {
                    throw new \Exception('Insufficient balance. You need at least ' . number_format($totalRequired, 2) . ' USDT (including ' . $withdrawalFee . ' USDT fee)');
                }

                // Create withdrawal record in withdrawals table
                $withdrawal = \App\Models\Withdrawal::create([
                    'user_id' => $user->id,
                    'transaction_id' => null, // Optional: link to transaction if needed
                    'withdrawal_id' => '', // Will be set by model boot event to WTD-000.id
                    'amount' => $request->amount,
                    'fee' => $withdrawalFee,
                    'net_amount' => $request->amount - $withdrawalFee,
                    'to_address' => $request->address,
                    'status' => 'pending',
                    'notes' => 'Withdrawal request',
                ]);

                // Refresh to get the generated withdrawal_id from model boot event
                $withdrawal->refresh();

                // Create entry in customers_wallets (credit - money going out)
                // Deduct both amount and fee to match the balance check
                $walletEntry = CustomersWallet::create([
                    'user_id' => $user->id,
                    'amount' => $totalRequired, // Deduct amount + fee to match balance check
                    'currency' => 'USDT',
                    'payment_type' => 'withdraw', // Use lowercase to match other entries
                    'transaction_type' => 'credit',
                    'related_id' => $withdrawal->id, // Link to withdrawal table id
                ]);

                // Verify both entries were created successfully
                if (!$withdrawal || !$walletEntry) {
                    throw new \Exception('Failed to create withdrawal records. Please try again.');
                }

                // Verify withdrawal has withdrawal_id
                $withdrawal->refresh();
                if (empty($withdrawal->withdrawal_id) || !str_starts_with($withdrawal->withdrawal_id, 'WTD-')) {
                    throw new \Exception('Failed to generate withdrawal ID. Please try again.');
                }

                return [
                    'withdrawal' => $withdrawal,
                    'wallet_entry' => $walletEntry,
                    'withdrawal_id' => $withdrawal->withdrawal_id
                ];
            });

            return redirect()->route('customer.wallet.withdraw')
                ->with('success', 'Withdrawal request submitted successfully! Your withdrawal ID is: ' . $result['withdrawal_id'] . '. Processing time: 2-3 Days.');

        } catch (\Exception $e) {
            // Rollback is automatic in DB::transaction if exception is thrown
            Log::error('Withdrawal creation failed: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'amount' => $request->amount,
                'address' => $request->address,
            ]);

            return back()->withErrors(['amount' => $e->getMessage()]);
        }
    }
    
    /**
     * Get withdrawals data for DataTable
     */
    public function getWithdrawalsData(Request $request)
    {
        $user = Auth::user();
        
        // Show ONLY current user's withdrawals from withdrawals table
        $query = \App\Models\Withdrawal::where('user_id', $user->id)
            ->select([
                'id',
                'withdrawal_id',
                'amount',
                'fee',
                'net_amount',
                'to_address',
                'status',
                'created_at',
                'processed_at'
            ])
            ->orderBy('created_at', 'desc'); // Show newest first
        
        return DataTables::of($query)
            ->addColumn('transaction_id', function ($withdrawal) {
                return $withdrawal->withdrawal_id ?? 'N/A';
            })
            ->addColumn('amount', function ($withdrawal) {
                return '$' . number_format($withdrawal->amount, 2) . ' USDT';
            })
            ->addColumn('fee', function ($withdrawal) {
                return '$' . number_format($withdrawal->fee, 2) . ' USDT';
            })
            ->addColumn('net_amount', function ($withdrawal) {
                return '$' . number_format($withdrawal->net_amount, 2) . ' USDT';
            })
            ->addColumn('to_address', function ($withdrawal) {
                return '<code>' . substr($withdrawal->to_address, 0, 10) . '...' . substr($withdrawal->to_address, -10) . '</code>';
            })
            ->addColumn('status', function ($withdrawal) {
                $badges = [
                    'pending' => '<span class="badge bg-warning">Pending</span>',
                    'processing' => '<span class="badge bg-info">Processing</span>',
                    'completed' => '<span class="badge bg-success">Completed</span>',
                    'failed' => '<span class="badge bg-danger">Failed</span>',
                    'cancelled' => '<span class="badge bg-secondary">Cancelled</span>',
                ];
                return $badges[$withdrawal->status] ?? '<span class="badge bg-secondary">' . ucfirst($withdrawal->status) . '</span>';
            })
            ->addColumn('created_at', function ($withdrawal) {
                return $withdrawal->created_at->format('M d, Y H:i');
            })
            ->rawColumns(['to_address', 'status'])
            ->make(true);
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

    public function getInvoiceDetails($invoiceId)
    {
        $user = Auth::user();
        
        $invoice = UserInvoice::where('id', $invoiceId)
            ->where('user_id', $user->id)
            ->first();
        
        if (!$invoice) {
            return response()->json(['error' => 'Invoice not found'], 404);
        }
        
        return response()->json([
            'id' => $invoice->id,
            'amount' => $invoice->amount,
            'invoice_type' => $invoice->invoice_type,
            'invoice_title' => $invoice->invoice_type, // invoice_title is same as invoice_type
            'status' => $invoice->status,
            'due_date' => $invoice->due_date ? $invoice->due_date->format('Y-m-d') : null,
        ]);
    }
}
