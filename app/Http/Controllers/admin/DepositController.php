<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Deposit;
use App\Models\Transaction;
use App\Models\Wallet;
use App\Models\UserInvoice;
use App\Services\ReferralService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class DepositController extends Controller
{
    public function index(Request $request)
    {
        // Return statistics if requested
        if ($request->has('statistics_only')) {
            return response()->json([
                'statistics' => [
                    'total' => Deposit::count(),
                    'pending' => Deposit::where('status', 'pending')->count(),
                    'processing' => Deposit::where('status', 'processing')->count(),
                    'approved' => Deposit::where('status', 'approved')->count(),
                    'rejected' => Deposit::where('status', 'rejected')->count(),
                    'cancelled' => Deposit::where('status', 'cancelled')->count(),
                ]
            ]);
        }
        
        if ($request->ajax()) {
            $query = Deposit::with(['user', 'approver', 'invoice']);

            // Get total records count (before any filters)
            $totalRecords = Deposit::count();

            // Apply custom filters
            if ($request->has('filter_status') && !empty($request->filter_status)) {
                $query->where('status', $request->filter_status);
            }
            
            if ($request->has('filter_user_id') && !empty($request->filter_user_id)) {
                $query->where('user_id', $request->filter_user_id);
            }
            
            if ($request->has('filter_date_from') && !empty($request->filter_date_from)) {
                $query->whereDate('created_at', '>=', $request->filter_date_from);
            }
            
            if ($request->has('filter_date_to') && !empty($request->filter_date_to)) {
                $query->whereDate('created_at', '<=', $request->filter_date_to);
            }

            // Apply search filter
            if ($request->has('search') && !empty($request->search['value'])) {
                $searchValue = $request->search['value'];
                $query->where(function($q) use ($searchValue) {
                    $q->where('deposit_id', 'like', "%{$searchValue}%")
                      ->orWhere('amount', 'like', "%{$searchValue}%")
                      ->orWhere('currency', 'like', "%{$searchValue}%")
                      ->orWhere('network', 'like', "%{$searchValue}%")
                      ->orWhere('status', 'like', "%{$searchValue}%")
                      ->orWhere('notes', 'like', "%{$searchValue}%")
                      ->orWhere('user_id', 'like', "%{$searchValue}%")
                      ->orWhereHas('user', function($userQuery) use ($searchValue) {
                          $userQuery->where('name', 'like', "%{$searchValue}%")
                                   ->orWhere('email', 'like', "%{$searchValue}%");
                      });
                });
            }

            // Get filtered count after search
            $filteredRecords = $query->count();

            // Apply ordering - Default to created_at desc (latest first)
            $orderColumn = 8; // Default to created_at
            $orderDirection = 'desc'; // Default to descending (latest first)
            
            if ($request->has('order') && is_array($request->get('order')) && count($request->get('order')) > 0) {
                $orderColumn = $request->get('order')[0]['column'] ?? 8;
                $orderDirection = $request->get('order')[0]['dir'] ?? 'desc';
            }

            $columns = [
                0 => 'deposit_id',
                1 => 'user_id',
                2 => 'amount',
                3 => 'currency',
                4 => 'network',
                5 => 'trans_id',
                6 => 'status',
                7 => 'proof_image',
                8 => 'created_at'
            ];

            if (isset($columns[$orderColumn])) {
                if ($orderColumn == 1) {
                    // Order by user name using join
                    if (!$query->getQuery()->joins) {
                        $query->join('users', 'deposits.user_id', '=', 'users.id')
                              ->select('deposits.*')
                              ->groupBy('deposits.id');
                    }
                    $query->orderBy('users.name', $orderDirection);
                } else {
                    $query->orderBy($columns[$orderColumn], $orderDirection);
                }
            } else {
                // Always default to latest first
                $query->orderBy('created_at', 'desc');
            }

            // Apply pagination
            $start = $request->get('start', 0);
            $length = $request->get('length', 25);
            $deposits = $query->offset($start)->limit($length)->get();

            return DataTables::of($deposits)
                ->addIndexColumn()
                ->addColumn('user_name', function ($deposit) {
                    return $deposit->user->name ?? '-';
                })
                ->addColumn('user_email', function ($deposit) {
                    return $deposit->user->email ?? '-';
                })
                ->addColumn('amount_formatted', function ($deposit) {
                    return '$' . number_format($deposit->amount, 2) . ' ' . $deposit->currency;
                })
                ->addColumn('trans_id', function ($deposit) {
                    if ($deposit->trans_id) {
                        $transId = strlen($deposit->trans_id) > 20 
                            ? substr($deposit->trans_id, 0, 20) . '...' 
                            : $deposit->trans_id;
                        return '<code title="' . htmlspecialchars($deposit->trans_id) . '">' . htmlspecialchars($transId) . '</code>';
                    }
                    return '-';
                })
                ->addColumn('status_badge', function ($deposit) {
                    $badgeClass = match($deposit->status) {
                        'pending' => 'bg-warning',
                        'processing' => 'bg-info',
                        'approved' => 'bg-success',
                        'rejected' => 'bg-danger',
                        'cancelled' => 'bg-secondary',
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
                ->addColumn('created_at', function ($deposit) {
                    return $deposit->created_at->format('M d, Y H:i');
                })
                ->addColumn('actions', function ($deposit) {
                    $disabled = ($deposit->status === 'approved') ? 'disabled' : '';
                    $actions = '<select class="form-select form-select-sm status-dropdown" data-deposit-id="' . $deposit->id . '" data-current-status="' . $deposit->status . '" style="width: auto; display: inline-block; min-width: 120px;" ' . $disabled . '>
                                    <option value="pending" ' . ($deposit->status === 'pending' ? 'selected' : '') . '>Pending</option>
                                    <option value="processing" ' . ($deposit->status === 'processing' ? 'selected' : '') . '>Processing</option>
                                    <option value="approved" ' . ($deposit->status === 'approved' ? 'selected' : '') . '>Approved</option>
                                    <option value="rejected" ' . ($deposit->status === 'rejected' ? 'selected' : '') . '>Rejected</option>
                                    <option value="cancelled" ' . ($deposit->status === 'cancelled' ? 'selected' : '') . '>Cancelled</option>
                                </select>';
                    $actions .= ' <button class="btn btn-sm btn-info ms-1" onclick="viewDeposit(' . $deposit->id . ')">
                                    <i class="bi bi-eye"></i> View
                                 </button>';
                    $actions .= ' <a href="' . route('admin.deposits.edit', $deposit->id) . '" class="btn btn-sm btn-secondary ms-1">
                                    <i class="bi bi-pencil"></i> Edit
                                 </a>';
                    return $actions;
                })
                ->rawColumns(['status_badge', 'proof_image', 'actions', 'trans_id'])
                ->with([
                    'recordsTotal' => $totalRecords,
                    'recordsFiltered' => $filteredRecords
                ])
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

            // Update invoice status to Paid if deposit is associated with an invoice
            if ($deposit->invoice_id) {
                $invoice = $deposit->invoice;
                $invoice->update(['status' => 'Paid']);
            }
            
            // Distribute referral bonuses to 3 levels for all approved deposits
            // This fetches bonus percentages from plans table and saves to bonus_wallets and customers_wallets
            app(ReferralService::class)->distributeReferralBonuses($deposit->user, $deposit);

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

    public function cancel(Request $request, $id)
    {
        $deposit = Deposit::findOrFail($id);
        
        if ($deposit->status !== 'pending') {
            return response()->json(['error' => 'Only pending deposits can be cancelled'], 400);
        }

        $deposit->update([
            'status' => 'cancelled',
            'notes' => $request->notes ?? $deposit->notes
        ]);

        // If deposit was associated with an invoice, reset invoice status back to Unpaid
        if ($deposit->invoice_id) {
            $deposit->invoice->update(['status' => 'Unpaid']);
        }

        return response()->json(['success' => 'Deposit cancelled successfully']);
    }

    public function show($id)
    {
        $deposit = Deposit::with(['user', 'approver', 'invoice'])->findOrFail($id);
        return response()->json($deposit);
    }

    public function update(Request $request, $id)
    {
        try {
            $deposit = Deposit::findOrFail($id);
            
            $validated = $request->validate([
                'status' => 'required|in:pending,processing,approved,rejected,cancelled',
                'notes' => 'nullable|string|max:1000',
                'rejection_reason' => 'nullable|string|max:1000'
            ]);

            if ($deposit->status === 'approved' && $validated['status'] !== 'approved') {
                return response()->json(['error' => 'Cannot change status of an approved deposit'], 400);
            }

            $updateData = [
                'status' => $validated['status'],
                'notes' => $validated['notes'] ?? $deposit->notes
            ];

            if ($validated['status'] === 'approved') {
                DB::transaction(function () use ($deposit, $updateData) {
                    $deposit->update(array_merge($updateData, [
                        'approved_by' => Auth::id(),
                        'approved_at' => now()
                    ]));

                    // Create transaction record if not exists
                    $existingTransaction = Transaction::where('notes', 'like', '%' . $deposit->deposit_id . '%')
                        ->where('user_id', $deposit->user_id)
                        ->first();

                    if (!$existingTransaction) {
                        Transaction::create([
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
                    }

                    // Update invoice status to Paid if deposit is associated with an invoice
                    if ($deposit->invoice_id) {
                        $invoice = $deposit->invoice;
                        $invoice->update(['status' => 'Paid']);
                    }
                    
                    // Distribute referral bonuses to 3 levels for all approved deposits
                    // This fetches bonus percentages from plans table and saves to bonus_wallets and customers_wallets
                    app(ReferralService::class)->distributeReferralBonuses($deposit->user, $deposit);
                });
            } else if ($validated['status'] === 'rejected') {
                $updateData['rejection_reason'] = $validated['rejection_reason'] ?? null;
                $deposit->update($updateData);
            } else {
                $deposit->update($updateData);
            }

            return response()->json(['success' => 'Deposit status updated successfully'], 200);
        } catch (\Exception $e) {
            \Log::error('Deposit status update error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to update deposit status: ' . $e->getMessage()], 500);
        }
    }

    public function edit($id)
    {
        $deposit = Deposit::with(['user', 'approver', 'invoice'])->findOrFail($id);
        return view('admin.deposits.edit', compact('deposit'));
    }
}
