<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Withdrawal;
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
        // Get statistics from withdrawals table
        $totalWithdrawals = Withdrawal::count();
        $pendingWithdrawals = Withdrawal::where('status', 'pending')->count();
        $processingWithdrawals = Withdrawal::where('status', 'processing')->count();
        $completedWithdrawals = Withdrawal::where('status', 'completed')->count();
        $totalAmount = Withdrawal::where('status', 'completed')->sum('amount');
        
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
        // Show ALL withdrawals for admin (no user filter) from withdrawals table
        $query = Withdrawal::with('user')
            ->select([
                'id',
                'user_id',
                'withdrawal_id',
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
            ->addColumn('transaction_id', function ($withdrawal) {
                return $withdrawal->withdrawal_id ?? 'N/A';
            })
            ->addColumn('user_name', function ($withdrawal) {
                return $withdrawal->user ? $withdrawal->user->name : 'N/A';
            })
            ->addColumn('user_email', function ($withdrawal) {
                return $withdrawal->user ? $withdrawal->user->email : 'N/A';
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
            ->addColumn('actions', function ($withdrawal) {
                $actions = '';
                
                if ($withdrawal->status === 'pending') {
                    $actions .= '<button class="btn btn-sm btn-success me-1" onclick="approveWithdrawal(' . $withdrawal->id . ')" title="Approve & Payout">
                        <i class="bi bi-check-circle"></i> Approve
                    </button>';
                    $actions .= '<button class="btn btn-sm btn-danger" onclick="rejectWithdrawal(' . $withdrawal->id . ')" title="Reject">
                        <i class="bi bi-x-circle"></i> Reject
                    </button>';
                } elseif ($withdrawal->status === 'processing') {
                    $actions .= '<button class="btn btn-sm btn-success me-1" onclick="completeWithdrawal(' . $withdrawal->id . ')" title="Mark as Completed">
                        <i class="bi bi-check-circle"></i> Complete
                    </button>';
                }
                
                $actions .= '<button class="btn btn-sm btn-info ms-1" onclick="viewWithdrawal(' . $withdrawal->id . ')" title="View Details">
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
        $withdrawal = Withdrawal::findOrFail($id);
        
        if ($withdrawal->status !== 'pending') {
            return response()->json(['success' => false, 'message' => 'Withdrawal is not pending']);
        }
        
        DB::transaction(function () use ($withdrawal, $request) {
            // Update withdrawal status to processing
            $withdrawal->status = 'processing';
            $withdrawal->notes = $request->notes ?? 'Withdrawal approved and processing';
            $withdrawal->save();
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
        $withdrawal = Withdrawal::findOrFail($id);
        
        if ($withdrawal->status !== 'processing') {
            return response()->json(['success' => false, 'message' => 'Withdrawal is not processing']);
        }
        
        $request->validate([
            'tx_hash' => 'nullable|string|max:255',
        ]);
        
        DB::transaction(function () use ($withdrawal, $request) {
            // Update withdrawal status to completed
            $withdrawal->status = 'completed';
            $withdrawal->tx_hash = $request->tx_hash;
            $withdrawal->processed_at = now();
            $withdrawal->notes = $request->notes ?? 'Withdrawal completed and paid out';
            $withdrawal->save();
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
        $withdrawal = Withdrawal::findOrFail($id);
        
        if ($withdrawal->status !== 'pending') {
            return response()->json(['success' => false, 'message' => 'Withdrawal is not pending']);
        }
        
        $request->validate([
            'reason' => 'required|string|max:500',
        ]);
        
        DB::transaction(function () use ($withdrawal, $request) {
            // Update withdrawal status to cancelled
            $withdrawal->status = 'cancelled';
            $withdrawal->notes = 'Rejected: ' . $request->reason;
            $withdrawal->save();
            
            // Refund the amount back to customer wallet
            $walletEntry = CustomersWallet::where('related_id', $withdrawal->id)
                ->where('payment_type', 'withdraw')
                ->where('transaction_type', 'credit')
                ->first();
            
            if ($walletEntry) {
                // Reverse the withdrawal - add back as debit
                CustomersWallet::create([
                    'user_id' => $withdrawal->user_id,
                    'amount' => $withdrawal->amount + $withdrawal->fee,
                    'currency' => 'USDT',
                    'payment_type' => 'refund',
                    'transaction_type' => 'debit',
                    'related_id' => $withdrawal->id,
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
        $withdrawal = Withdrawal::with('user')->findOrFail($id);
        
        return response()->json([
            'success' => true,
            'data' => [
                'id' => $withdrawal->id,
                'transaction_id' => $withdrawal->withdrawal_id,
                'user_name' => $withdrawal->user->name ?? 'N/A',
                'user_email' => $withdrawal->user->email ?? 'N/A',
                'amount' => number_format($withdrawal->amount, 2),
                'fee' => number_format($withdrawal->fee, 2),
                'net_amount' => number_format($withdrawal->net_amount, 2),
                'to_address' => $withdrawal->to_address,
                'status' => $withdrawal->status,
                'tx_hash' => $withdrawal->tx_hash,
                'notes' => $withdrawal->notes,
                'created_at' => $withdrawal->created_at->format('M d, Y H:i'),
                'processed_at' => $withdrawal->processed_at ? $withdrawal->processed_at->format('M d, Y H:i') : null,
            ]
        ]);
    }
}

