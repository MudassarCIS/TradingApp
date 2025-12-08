<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\CustomersWallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class WithdrawalController extends Controller
{
    /**
     * Display withdrawals management page
     */
    public function index()
    {
        // Get statistics
        $totalWithdrawals = Transaction::where('type', 'withdrawal')->count();
        $pendingWithdrawals = Transaction::where('type', 'withdrawal')->where('status', 'pending')->count();
        $processingWithdrawals = Transaction::where('type', 'withdrawal')->where('status', 'processing')->count();
        $completedWithdrawals = Transaction::where('type', 'withdrawal')->where('status', 'completed')->count();
        $totalAmount = Transaction::where('type', 'withdrawal')->where('status', 'completed')->sum('amount');
        
        return view('admin.withdrawals.index', compact(
            'totalWithdrawals',
            'pendingWithdrawals',
            'processingWithdrawals',
            'completedWithdrawals',
            'totalAmount'
        ));
    }
    
    /**
     * Get withdrawals data for DataTable
     */
    public function getWithdrawalsData(Request $request)
    {
        // Show ALL withdrawals for admin (no user filter)
        $query = Transaction::with('user')
            ->where('type', 'withdrawal')
            ->select([
                'id',
                'user_id',
                'transaction_id',
                'amount',
                'fee',
                'net_amount',
                'to_address',
                'status',
                'tx_hash',
                'notes',
                'created_at',
                'processed_at'
            ])
            ->orderBy('created_at', 'desc'); // Show newest first
        
        // Apply status filter
        if ($request->has('status') && !empty($request->status)) {
            $query->where('status', $request->status);
        }
        
        return DataTables::of($query)
            ->addColumn('user_name', function ($transaction) {
                return $transaction->user ? $transaction->user->name : 'N/A';
            })
            ->addColumn('user_email', function ($transaction) {
                return $transaction->user ? $transaction->user->email : 'N/A';
            })
            ->addColumn('amount', function ($transaction) {
                return '$' . number_format($transaction->amount, 2) . ' USDT';
            })
            ->addColumn('fee', function ($transaction) {
                return '$' . number_format($transaction->fee, 2) . ' USDT';
            })
            ->addColumn('net_amount', function ($transaction) {
                return '$' . number_format($transaction->net_amount, 2) . ' USDT';
            })
            ->addColumn('to_address', function ($transaction) {
                return '<code>' . substr($transaction->to_address, 0, 10) . '...' . substr($transaction->to_address, -10) . '</code>';
            })
            ->addColumn('status', function ($transaction) {
                $badges = [
                    'pending' => '<span class="badge bg-warning">Pending</span>',
                    'processing' => '<span class="badge bg-info">Processing</span>',
                    'completed' => '<span class="badge bg-success">Completed</span>',
                    'failed' => '<span class="badge bg-danger">Failed</span>',
                    'cancelled' => '<span class="badge bg-secondary">Cancelled</span>',
                ];
                return $badges[$transaction->status] ?? '<span class="badge bg-secondary">' . ucfirst($transaction->status) . '</span>';
            })
            ->addColumn('created_at', function ($transaction) {
                return $transaction->created_at->format('M d, Y H:i');
            })
            ->addColumn('actions', function ($transaction) {
                $actions = '';
                
                if ($transaction->status === 'pending') {
                    $actions .= '<button class="btn btn-sm btn-success me-1" onclick="approveWithdrawal(' . $transaction->id . ')" title="Approve & Payout">
                        <i class="bi bi-check-circle"></i> Approve
                    </button>';
                    $actions .= '<button class="btn btn-sm btn-danger" onclick="rejectWithdrawal(' . $transaction->id . ')" title="Reject">
                        <i class="bi bi-x-circle"></i> Reject
                    </button>';
                } elseif ($transaction->status === 'processing') {
                    $actions .= '<button class="btn btn-sm btn-success me-1" onclick="completeWithdrawal(' . $transaction->id . ')" title="Mark as Completed">
                        <i class="bi bi-check-circle"></i> Complete
                    </button>';
                }
                
                $actions .= '<button class="btn btn-sm btn-info ms-1" onclick="viewWithdrawal(' . $transaction->id . ')" title="View Details">
                    <i class="bi bi-eye"></i> View
                </button>';
                
                return $actions;
            })
            ->rawColumns(['to_address', 'status', 'actions'])
            ->make(true);
    }
    
    /**
     * Approve and payout withdrawal
     */
    public function approve(Request $request, $id)
    {
        $transaction = Transaction::findOrFail($id);
        
        if ($transaction->type !== 'withdrawal') {
            return response()->json(['success' => false, 'message' => 'Invalid transaction type']);
        }
        
        if ($transaction->status !== 'pending') {
            return response()->json(['success' => false, 'message' => 'Withdrawal is not pending']);
        }
        
        DB::transaction(function () use ($transaction) {
            // Update transaction status to processing
            $transaction->status = 'processing';
            $transaction->notes = $request->notes ?? 'Withdrawal approved and processing';
            $transaction->save();
        });
        
        return response()->json([
            'success' => true,
            'message' => 'Withdrawal approved and marked as processing'
        ]);
    }
    
    /**
     * Complete withdrawal (mark as completed after payout)
     */
    public function complete(Request $request, $id)
    {
        $transaction = Transaction::findOrFail($id);
        
        if ($transaction->type !== 'withdrawal') {
            return response()->json(['success' => false, 'message' => 'Invalid transaction type']);
        }
        
        if ($transaction->status !== 'processing') {
            return response()->json(['success' => false, 'message' => 'Withdrawal is not processing']);
        }
        
        $request->validate([
            'tx_hash' => 'nullable|string|max:255',
        ]);
        
        DB::transaction(function () use ($transaction, $request) {
            // Update transaction status to completed
            $transaction->status = 'completed';
            $transaction->tx_hash = $request->tx_hash;
            $transaction->processed_at = now();
            $transaction->notes = $request->notes ?? 'Withdrawal completed and paid out';
            $transaction->save();
        });
        
        return response()->json([
            'success' => true,
            'message' => 'Withdrawal marked as completed'
        ]);
    }
    
    /**
     * Reject withdrawal
     */
    public function reject(Request $request, $id)
    {
        $transaction = Transaction::findOrFail($id);
        
        if ($transaction->type !== 'withdrawal') {
            return response()->json(['success' => false, 'message' => 'Invalid transaction type']);
        }
        
        if ($transaction->status !== 'pending') {
            return response()->json(['success' => false, 'message' => 'Withdrawal is not pending']);
        }
        
        $request->validate([
            'reason' => 'required|string|max:500',
        ]);
        
        DB::transaction(function () use ($transaction, $request) {
            // Update transaction status to cancelled
            $transaction->status = 'cancelled';
            $transaction->notes = 'Rejected: ' . $request->reason;
            $transaction->save();
            
            // Refund the amount back to customer wallet
            $walletEntry = CustomersWallet::where('related_id', $transaction->id)
                ->where('payment_type', 'withdraw')
                ->where('transaction_type', 'credit')
                ->first();
            
            if ($walletEntry) {
                // Reverse the withdrawal - add back as debit
                CustomersWallet::create([
                    'user_id' => $transaction->user_id,
                    'amount' => $transaction->amount + $transaction->fee,
                    'currency' => 'USDT',
                    'payment_type' => 'refund',
                    'transaction_type' => 'debit',
                    'related_id' => $transaction->id,
                ]);
            }
        });
        
        return response()->json([
            'success' => true,
            'message' => 'Withdrawal rejected and amount refunded'
        ]);
    }
    
    /**
     * Get withdrawal details
     */
    public function show($id)
    {
        $transaction = Transaction::with('user')->findOrFail($id);
        
        return response()->json([
            'success' => true,
            'data' => [
                'id' => $transaction->id,
                'transaction_id' => $transaction->transaction_id,
                'user_name' => $transaction->user->name ?? 'N/A',
                'user_email' => $transaction->user->email ?? 'N/A',
                'amount' => number_format($transaction->amount, 2),
                'fee' => number_format($transaction->fee, 2),
                'net_amount' => number_format($transaction->net_amount, 2),
                'to_address' => $transaction->to_address,
                'status' => $transaction->status,
                'tx_hash' => $transaction->tx_hash,
                'notes' => $transaction->notes,
                'created_at' => $transaction->created_at->format('M d, Y H:i'),
                'processed_at' => $transaction->processed_at ? $transaction->processed_at->format('M d, Y H:i') : null,
            ]
        ]);
    }
}

