<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Deposit;
use App\Models\Transaction;
use App\Models\Wallet;
use App\Services\ReferralService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class DepositController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $deposits = Deposit::with(['user', 'approver'])
                ->select('deposits.*')
                ->orderBy('created_at', 'desc');

            return DataTables::of($deposits)
                ->addIndexColumn()
                ->addColumn('user_name', function ($deposit) {
                    return $deposit->user->name;
                })
                ->addColumn('user_email', function ($deposit) {
                    return $deposit->user->email;
                })
                ->addColumn('amount_formatted', function ($deposit) {
                    return '$' . number_format($deposit->amount, 2) . ' ' . $deposit->currency;
                })
                ->addColumn('status_badge', function ($deposit) {
                    $badgeClass = match($deposit->status) {
                        'pending' => 'bg-warning',
                        'approved' => 'bg-success',
                        'rejected' => 'bg-danger',
                        default => 'bg-secondary'
                    };
                    return '<span class="badge ' . $badgeClass . '">' . ucfirst($deposit->status) . '</span>';
                })
                ->addColumn('proof_image', function ($deposit) {
                    if ($deposit->proof_image) {
                        return '<a href="' . $deposit->proof_image_url . '" target="_blank" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-image"></i> View
                                </a>';
                    }
                    return '<span class="text-muted">No image</span>';
                })
                ->addColumn('actions', function ($deposit) {
                    $actions = '';
                    if ($deposit->status === 'pending') {
                        $actions .= '<button class="btn btn-sm btn-success me-1" onclick="approveDeposit(' . $deposit->id . ')">
                                        <i class="bi bi-check"></i> Approve
                                     </button>';
                        $actions .= '<button class="btn btn-sm btn-danger" onclick="rejectDeposit(' . $deposit->id . ')">
                                        <i class="bi bi-x"></i> Reject
                                     </button>';
                    }
                    $actions .= '<button class="btn btn-sm btn-info" onclick="viewDeposit(' . $deposit->id . ')">
                                    <i class="bi bi-eye"></i> View
                                 </button>';
                    return $actions;
                })
                ->rawColumns(['status_badge', 'proof_image', 'actions'])
                ->make(true);
        }

        return view('admin.deposits.index');
    }

    public function approve(Request $request, $id)
    {
        $deposit = Deposit::findOrFail($id);
        
        if ($deposit->status !== 'pending') {
            return response()->json(['error' => 'Deposit is not pending'], 400);
        }

        DB::transaction(function () use ($deposit, $request) {
            // Update deposit status
            $deposit->update([
                'status' => 'approved',
                'approved_by' => Auth::id(),
                'approved_at' => now(),
                'notes' => $request->notes ?? $deposit->notes
            ]);

            // Create transaction record
            $transaction = Transaction::create([
                'user_id' => $deposit->user_id,
                'transaction_id' => (new Transaction())->generateTransactionId(),
                'type' => 'deposit',
                'status' => 'completed',
                'currency' => $deposit->currency,
                'amount' => $deposit->amount,
                'fee' => 0,
                'net_amount' => $deposit->amount,
                'notes' => 'Deposit approved: ' . $deposit->deposit_id,
                'processed_at' => now()
            ]);

            // Update user's wallet
            $wallet = $deposit->user->getMainWallet($deposit->currency);
            if (!$wallet) {
                $wallet = Wallet::create([
                    'user_id' => $deposit->user_id,
                    'currency' => $deposit->currency,
                    'balance' => 0,
                    'total_deposited' => 0,
                    'total_withdrawn' => 0,
                    'total_profit' => 0,
                    'total_loss' => 0,
                ]);
            }

            $wallet->increment('balance', $deposit->amount);
            $wallet->increment('total_deposited', $deposit->amount);

            // Distribute referral bonuses using ReferralService
            app(ReferralService::class)->distributeReferralBonuses($deposit->user, $deposit);
        });

        return response()->json(['success' => 'Deposit approved successfully']);
    }

    public function reject(Request $request, $id)
    {
        $deposit = Deposit::findOrFail($id);
        
        if ($deposit->status !== 'pending') {
            return response()->json(['error' => 'Deposit is not pending'], 400);
        }

        $deposit->update([
            'status' => 'rejected',
            'rejection_reason' => $request->rejection_reason,
            'notes' => $request->notes ?? $deposit->notes
        ]);

        return response()->json(['success' => 'Deposit rejected successfully']);
    }

    public function show($id)
    {
        $deposit = Deposit::with(['user', 'approver'])->findOrFail($id);
        return response()->json($deposit);
    }
}
