<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Deposit;
use App\Models\Transaction;
use App\Models\Wallet;
use App\Models\UserInvoice;
use App\Models\Plan;
use App\Models\UserPlanHistory;
use App\Models\CustomersWallet;
use App\Models\Referral;
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
                      ->orWhere('invoice_type', 'like', "%{$searchValue}%")
                      ->orWhere('notes', 'like', "%{$searchValue}%")
                      ->orWhere('user_id', 'like', "%{$searchValue}%")
                      ->orWhereHas('user', function($userQuery) use ($searchValue) {
                          $userQuery->where('name', 'like', "%{$searchValue}%")
                                   ->orWhere('email', 'like', "%{$searchValue}%");
                      })
                      ->orWhereHas('invoice', function($invoiceQuery) use ($searchValue) {
                          $invoiceQuery->where('invoice_type', 'like', "%{$searchValue}%");
                      });
                });
            }

            // Get filtered count after search
            $filteredRecords = $query->count();

            // Apply ordering - Default to created_at desc (latest first)
            $orderColumn = 9; // Default to created_at (updated index after adding invoice_type column)
            $orderDirection = 'desc'; // Default to descending (latest first)
            
            if ($request->has('order') && is_array($request->get('order')) && count($request->get('order')) > 0) {
                $orderColumn = $request->get('order')[0]['column'] ?? 9;
                $orderDirection = $request->get('order')[0]['dir'] ?? 'desc';
            }

            $columns = [
                0 => 'deposit_id',
                1 => 'user_id',
                2 => 'amount',
                3 => 'currency',
                4 => 'network',
                5 => 'invoice_type',
                6 => 'trans_id',
                7 => 'status',
                8 => 'proof_image',
                9 => 'created_at'
            ];

            // Always order by created_at desc first (primary sort)
            $query->orderBy('created_at', 'desc');
            
            // If user explicitly orders by a different column, add it as secondary sort
            if (isset($columns[$orderColumn]) && $orderColumn != 9) {
                if ($orderColumn == 1) {
                    // Order by user name using join
                    if (!$query->getQuery()->joins) {
                        $query->join('users', 'deposits.user_id', '=', 'users.id')
                              ->select('deposits.*')
                              ->groupBy('deposits.id');
                    }
                    $query->orderBy('users.name', $orderDirection);
                } elseif ($orderColumn == 5) {
                    // Order by invoice_type (from deposits table or invoice relationship)
                    $query->orderBy('deposits.invoice_type', $orderDirection);
                } else {
                    $query->orderBy($columns[$orderColumn], $orderDirection);
                }
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
                ->addColumn('invoice_type', function ($deposit) {
                    // Get invoice_type from deposit or invoice relationship
                    $invoiceType = $deposit->invoice_type;
                    if (!$invoiceType && $deposit->invoice) {
                        $invoiceType = $deposit->invoice->invoice_type;
                    }
                    if ($invoiceType) {
                        $badgeClass = match($invoiceType) {
                            'Rent A Bot' => 'bg-primary',
                            'Sharing Nexa' => 'bg-info',
                            'package buy' => 'bg-success',
                            'profit invoice' => 'bg-warning',
                            default => 'bg-secondary'
                        };
                        return '<span class="badge ' . $badgeClass . '">' . htmlspecialchars($invoiceType) . '</span>';
                    }
                    return '<span class="text-muted">-</span>';
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
                ->rawColumns(['status_badge', 'invoice_type', 'proof_image', 'actions', 'trans_id'])
                ->with([
                    'recordsTotal' => $totalRecords,
                    'recordsFiltered' => $filteredRecords
                ])
                ->make(true);
        }

        return view('admin.deposits.index');
    }

    /**
     * Distribute profit bonus to 3 levels of parents
     * Uses each parent's active plan referral_level percentages
     * Only gives bonus if parent has active_plan_id
     */
    private function distributeProfitBonus($deposit): void
    {
        try {
            $user = $deposit->user;
            $depositAmount = (float) $deposit->amount;
            $currency = $deposit->currency ?? 'USDT';
            
            // Convert to USDT if needed
            $amountInUSDT = $depositAmount;
            if ($currency !== 'USDT') {
                try {
                    $referralService = app(ReferralService::class);
                    $amountInUSDT = $referralService->convertToUSDT($currency, $depositAmount);
                } catch (\Exception $e) {
                    \Log::warning('Could not convert currency to USDT, using original amount', [
                        'currency' => $currency,
                        'amount' => $depositAmount,
                        'error' => $e->getMessage()
                    ]);
                }
            }
            
            // Check if bonuses have already been distributed for this deposit
            $existingBonus = CustomersWallet::where('related_id', $deposit->id)
                ->where('payment_type', 'bonus')
                ->first();
            
            if ($existingBonus) {
                \Log::info('Profit bonus already distributed for this deposit', [
                    'deposit_id' => $deposit->id
                ]);
                return;
            }
            
            // Process 3 levels of parents
            $level = 1;
            $currentUserId = $user->id; // Start with the investor
            
            while ($level <= 3) {
                // Store the child user ID before finding parent (this is the user we're finding the parent for)
                $childUserId = $currentUserId;
                
                // Get parent for current user
                $referral = Referral::where('referred_id', $currentUserId)
                    ->where('status', 'active')
                    ->first();
                
                if (!$referral) {
                    \Log::info('No parent found for profit bonus distribution', [
                        'level' => $level,
                        'user_id' => $currentUserId,
                        'deposit_id' => $deposit->id
                    ]);
                    break; // No more parents, stop processing
                }
                
                $parent = \App\Models\User::find($referral->referrer_id);
                
                if (!$parent) {
                    \Log::warning('Parent user not found', [
                        'level' => $level,
                        'referrer_id' => $referral->referrer_id,
                        'deposit_id' => $deposit->id
                    ]);
                    break;
                }
                
                // Check if parent has active plan
                if (!$parent->active_plan_id) {
                    \Log::info('Parent has no active plan, skipping bonus', [
                        'level' => $level,
                        'parent_id' => $parent->id,
                        'deposit_id' => $deposit->id
                    ]);
                    // Move to next level parent
                    $currentUserId = $parent->id;
                    $level++;
                    continue;
                }
                
                // Get parent's active plan
                $parentPlan = Plan::find($parent->active_plan_id);
                
                if (!$parentPlan) {
                    \Log::warning('Parent plan not found', [
                        'level' => $level,
                        'parent_id' => $parent->id,
                        'plan_id' => $parent->active_plan_id,
                        'deposit_id' => $deposit->id
                    ]);
                    // Move to next level parent
                    $currentUserId = $parent->id;
                    $level++;
                    continue;
                }
                
                // Get referral percentage for this level
                $percentField = 'referral_level_' . $level;
                $percent = (float) ($parentPlan->$percentField ?? 0);
                
                if ($percent <= 0) {
                    \Log::info('Parent plan has no referral percentage for this level', [
                        'level' => $level,
                        'parent_id' => $parent->id,
                        'plan_id' => $parentPlan->id,
                        'plan_name' => $parentPlan->name,
                        'deposit_id' => $deposit->id
                    ]);
                    // Move to next level parent
                    $currentUserId = $parent->id;
                    $level++;
                    continue;
                }
                
                // Calculate bonus amount: deposit_amount × (percentage / 100)
                $bonusAmount = round($amountInUSDT * ($percent / 100), 8);
                
                if ($bonusAmount > 0) {
                    // Check if bonus already distributed for this deposit and level
                    $existingLevelBonus = CustomersWallet::where('related_id', $deposit->id)
                        ->where('payment_type', 'bonus')
                        ->where('user_id', $parent->id)
                        ->first();
                    
                    if ($existingLevelBonus) {
                        \Log::info('Bonus already distributed for this deposit and parent', [
                            'level' => $level,
                            'parent_id' => $parent->id,
                            'deposit_id' => $deposit->id
                        ]);
                        // Move to next level parent
                        $currentUserId = $parent->id;
                        $level++;
                        continue;
                    }
                    
                    // Distribute bonus to parent
                    DB::transaction(function () use ($deposit, $user, $parent, $parentPlan, $bonusAmount, $percent, $level, $childUserId) {
                        // Insert into customers_wallets
                        $walletEntry = CustomersWallet::create([
                            'user_id' => $parent->id,
                            'amount' => $bonusAmount,
                            'currency' => 'USDT',
                            'payment_type' => 'bonus',
                            'transaction_type' => 'debit',
                            'related_id' => $deposit->id,
                        ]);

                        // Update parent's wallet summary
                        $wallet = Wallet::where('user_id', $parent->id)->where('currency', 'USDT')->first();
                        if (!$wallet) {
                            $wallet = Wallet::create([
                                'user_id' => $parent->id,
                                'currency' => 'USDT',
                                'balance' => $bonusAmount,
                                'total_profit' => $bonusAmount,
                                'total_deposited' => 0,
                                'total_withdrawn' => 0,
                                'total_loss' => 0,
                            ]);
                        } else {
                            $wallet->increment('balance', $bonusAmount);
                            $wallet->increment('total_profit', $bonusAmount);
                        }

                        // Update referral totals - track commission from direct child
                        $ref = Referral::where('referrer_id', $parent->id)
                            ->where('referred_id', $childUserId)
                            ->first();
                        if ($ref) {
                            $ref->increment('total_commission', $bonusAmount);
                            $ref->increment('pending_commission', $bonusAmount);
                        }
                        
                        // Log bonus distribution
                        \Log::info('Profit bonus distributed to parent', [
                            'level' => $level,
                            'parent_id' => $parent->id,
                            'parent_plan_id' => $parentPlan->id,
                            'parent_plan_name' => $parentPlan->name,
                            'child_user_id' => $childUserId,
                            'investor_id' => $user->id,
                            'deposit_id' => $deposit->id,
                            'deposit_amount' => $deposit->amount,
                            'percentage' => $percent,
                            'bonus_amount' => $bonusAmount,
                            'wallet_entry_id' => $walletEntry->id
                        ]);
                    });
                }
                
                // Move to next level parent
                $currentUserId = $parent->id;
                $level++;
            }
        } catch (\Exception $e) {
            \Log::error('Error in distributeProfitBonus: ' . $e->getMessage(), [
                'deposit_id' => $deposit->id ?? null,
                'user_id' => $deposit->user_id ?? null
            ]);
            throw $e;
        }
    }

    /**
     * Distribute bonus for Sharing Nexa deposits only
     * Gets parent's active_plan_id and uses parent's plan direct_bonus
     * Only gives bonus to level-1 parent if parent has active plan
     */
    private function distributeRentBotBonus($deposit): void
    {
        try {
            // Get invoice_type from deposit or invoice relationship
            $invoiceType = $deposit->invoice_type ?? ($deposit->invoice->invoice_type ?? null);
            
            // Only process "Sharing Nexa" deposits, skip "Rent A Bot"
            if ($invoiceType !== 'Sharing Nexa') {
                \Log::info('Skipping bonus distribution - deposit type is not Sharing Nexa', [
                    'deposit_id' => $deposit->id,
                    'invoice_type' => $invoiceType
                ]);
                return;
            }
            
            $user = $deposit->user;
            
            // Get first level parent
            $referral = Referral::where('referred_id', $user->id)
                ->where('status', 'active')
                ->first();
            
            if (!$referral) {
                \Log::info('No level-1 parent found for Sharing Nexa bonus', [
                    'user_id' => $user->id,
                    'deposit_id' => $deposit->id
                ]);
                return;
            }
            
            $parent = \App\Models\User::find($referral->referrer_id);
            
            if (!$parent) {
                \Log::warning('Parent user not found', [
                    'referrer_id' => $referral->referrer_id,
                    'deposit_id' => $deposit->id
                ]);
                return;
            }
            
            // Get parent's active_plan_id from users table
            if (!$parent->active_plan_id) {
                \Log::info('Parent has no active plan', [
                    'parent_id' => $parent->id,
                    'deposit_id' => $deposit->id
                ]);
                return;
            }
            
            // Get parent's plan and direct_bonus
            $parentPlan = Plan::find($parent->active_plan_id);
            
            if (!$parentPlan) {
                \Log::warning('Parent plan not found', [
                    'parent_id' => $parent->id,
                    'plan_id' => $parent->active_plan_id,
                    'deposit_id' => $deposit->id
                ]);
                return;
            }
            
            $directBonus = (float) ($parentPlan->direct_bonus ?? 0);
            
            if ($directBonus <= 0) {
                \Log::info('Parent plan has no direct_bonus configured', [
                    'parent_id' => $parent->id,
                    'plan_id' => $parentPlan->id,
                    'plan_name' => $parentPlan->name,
                    'deposit_id' => $deposit->id
                ]);
                return;
            }
            
            // Check if bonus already distributed for this deposit
            $existingBonus = CustomersWallet::where('related_id', $deposit->id)
                ->where('payment_type', 'bonus')
                ->where('user_id', $parent->id)
                ->first();
            
            if ($existingBonus) {
                \Log::info('Bonus already distributed for this deposit', [
                    'deposit_id' => $deposit->id,
                    'parent_id' => $parent->id
                ]);
                return;
            }
            
            // Add bonus to customers_wallet
            DB::transaction(function () use ($deposit, $user, $parent, $parentPlan, $directBonus) {
                // Insert into customers_wallets with deposit id as related_id
                $walletEntry = CustomersWallet::create([
                    'user_id' => $parent->id,
                    'amount' => $directBonus,
                    'currency' => 'USDT',
                    'payment_type' => 'bonus',
                    'transaction_type' => 'debit',
                    'related_id' => $deposit->id,
                ]);

                // Update parent's wallet summary
                $wallet = Wallet::where('user_id', $parent->id)->where('currency', 'USDT')->first();
                if (!$wallet) {
                    $wallet = Wallet::create([
                        'user_id' => $parent->id,
                        'currency' => 'USDT',
                        'balance' => $directBonus,
                        'total_profit' => $directBonus,
                        'total_deposited' => 0,
                        'total_withdrawn' => 0,
                        'total_loss' => 0,
                    ]);
                } else {
                    $wallet->increment('balance', $directBonus);
                    $wallet->increment('total_profit', $directBonus);
                }

                // Update referral totals
                $ref = Referral::where('referrer_id', $parent->id)
                    ->where('referred_id', $user->id)
                    ->first();
                if ($ref) {
                    $ref->increment('total_commission', $directBonus);
                    $ref->increment('pending_commission', $directBonus);
                }
                
                // Log bonus distribution for verification
                \Log::info('Sharing Nexa bonus distributed to level-1 parent', [
                    'parent_id' => $parent->id,
                    'parent_plan_id' => $parentPlan->id,
                    'parent_plan_name' => $parentPlan->name,
                    'investor_id' => $user->id,
                    'deposit_id' => $deposit->id,
                    'bonus_amount' => $directBonus,
                    'wallet_entry_id' => $walletEntry->id
                ]);
            });
        } catch (\Exception $e) {
            \Log::error('Error in distributeRentBotBonus: ' . $e->getMessage(), [
                'deposit_id' => $deposit->id ?? null,
                'user_id' => $deposit->user_id ?? null
            ]);
            throw $e;
        }
    }

    public function approve(Request $request, $id)
    {
        $deposit = Deposit::findOrFail($id);
        
        if ($deposit->status !== 'pending') {
            return response()->json(['error' => 'Deposit is not pending'], 400);
        }

        DB::transaction(function () use ($deposit, $request) {
            // Load invoice relationship if exists
            if ($deposit->invoice_id) {
                $deposit->load('invoice');
            }
            
            // Update deposit status
            $deposit->update([
                'status' => 'approved',
                'approved_by' => Auth::id(),
                'approved_at' => now(),
                'notes' => $request->notes ?? $deposit->notes
            ]);

            // Update invoice status to Paid if deposit is associated with an invoice
            if ($deposit->invoice_id && $deposit->invoice) {
                $deposit->invoice->update(['status' => 'Paid']);
                
                // If invoice has plan_id, update user's plan and plan history
                if ($deposit->invoice->plan_id) {
                    $plan = Plan::find($deposit->invoice->plan_id);
                    if ($plan) {
                        $user = $deposit->user;
                        $oldPlanId = $user->active_plan_id;
                        $oldPlanName = $user->active_plan_name ?? null;
                        
                        // Update user's active plan
                        $user->active_plan_id = $plan->id;
                        if (\Illuminate\Support\Facades\Schema::hasColumn('users', 'active_plan_name')) {
                            $user->active_plan_name = $plan->name;
                        }
                        $user->active_investment_amount = $deposit->amount;
                        $user->save();
                        
                        // Save to plan history
                        UserPlanHistory::create([
                            'user_id' => $user->id,
                            'plan_id' => $plan->id,
                            'plan_name' => $plan->name,
                            'joining_fee' => $plan->joining_fee ?? 0,
                            'investment_amount' => $deposit->amount,
                            'notes' => $oldPlanName ? "Changed from {$oldPlanName} to {$plan->name}" : "Plan purchased: {$plan->name}",
                        ]);
                    }
                }
            }
            
            // Distribute referral bonuses based on invoice type
            // For "Sharing Nexa": Get parent's plan direct_bonus and give to first level parent only (if parent has active plan)
            // For "Rent A Bot": No bonus distribution
            // For "profit invoice" or "trade profit": Give bonus to 3 levels of parents using their active plan referral percentages
            // For other types: No bonus distribution
            try {
                $invoiceType = $deposit->invoice_type ?? ($deposit->invoice->invoice_type ?? null);
                
                if ($invoiceType === 'Sharing Nexa') {
                    // Special handling for Sharing Nexa - get parent's plan direct_bonus (only if parent has active plan)
                    // Only gives bonus to level-1 parent if parent has active plan
                    $this->distributeRentBotBonus($deposit);
                } elseif ($invoiceType === 'Rent A Bot') {
                    // Rent A Bot - no bonus distribution
                    \Log::info('Rent A Bot deposit approved - no bonus distribution', [
                        'deposit_id' => $deposit->id,
                        'user_id' => $deposit->user_id
                    ]);
                } elseif (in_array($invoiceType, ['profit invoice', 'trade profit'])) {
                    // Profit invoice or trade profit - give bonus to 3 levels of parents
                    // Uses each parent's active plan referral_level percentages
                    $this->distributeProfitBonus($deposit);
                } else {
                    // Other invoice types - no bonus distribution
                    \Log::info('Deposit approved - no bonus distribution for this invoice type', [
                        'deposit_id' => $deposit->id,
                        'user_id' => $deposit->user_id,
                        'invoice_type' => $invoiceType ?? 'none'
                    ]);
                }
            } catch (\Exception $e) {
                // Log error but don't fail the deposit approval
                \Log::error('Error distributing referral bonuses: ' . $e->getMessage(), [
                    'deposit_id' => $deposit->id,
                    'user_id' => $deposit->user_id,
                    'invoice_id' => $deposit->invoice_id,
                    'invoice_type' => $deposit->invoice->invoice_type ?? 'none',
                    'error' => $e->getTraceAsString()
                ]);
            }

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
                    // Reload deposit with user relationship to ensure we have fresh data
                    $deposit->load('user');
                    
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
                        $deposit->load('invoice');
                        if ($deposit->invoice) {
                            $deposit->invoice->update(['status' => 'Paid']);
                            
                            // If invoice has plan_id, update user's plan and plan history
                            if ($deposit->invoice->plan_id) {
                                $plan = Plan::find($deposit->invoice->plan_id);
                                if ($plan) {
                                    $user = $deposit->user;
                                    $oldPlanId = $user->active_plan_id;
                                    $oldPlanName = $user->active_plan_name ?? null;
                                    
                                    // Update user's active plan
                                    $user->active_plan_id = $plan->id;
                                    if (\Illuminate\Support\Facades\Schema::hasColumn('users', 'active_plan_name')) {
                                        $user->active_plan_name = $plan->name;
                                    }
                                    $user->active_investment_amount = $deposit->amount;
                                    $user->save();
                                    
                                    // Save to plan history
                                    UserPlanHistory::create([
                                        'user_id' => $user->id,
                                        'plan_id' => $plan->id,
                                        'plan_name' => $plan->name,
                                        'joining_fee' => $plan->joining_fee ?? 0,
                                        'investment_amount' => $deposit->amount,
                                        'notes' => $oldPlanName ? "Changed from {$oldPlanName} to {$plan->name}" : "Plan purchased: {$plan->name}",
                                    ]);
                                }
                            }
                        }
                    }
                    
                    // Distribute referral bonuses based on invoice type
                    // For "Sharing Nexa": Get parent's plan direct_bonus and give to first level parent only (if parent has active plan)
                    // For "Rent A Bot": No bonus distribution
                    // For "profit invoice" or "trade profit": Give bonus to 3 levels of parents using their active plan referral percentages
                    // For other types: No bonus distribution
                    try {
                        // Ensure user relationship is loaded
                        if (!$deposit->relationLoaded('user')) {
                            $deposit->load('user');
                        }
                        
                        $invoiceType = $deposit->invoice_type ?? ($deposit->invoice->invoice_type ?? null);
                        
                        if ($invoiceType === 'Sharing Nexa') {
                            // Special handling for Sharing Nexa - get parent's plan direct_bonus (only if parent has active plan)
                            // Only gives bonus to level-1 parent if parent has active plan
                            $this->distributeRentBotBonus($deposit);
                        } elseif ($invoiceType === 'Rent A Bot') {
                            // Rent A Bot - no bonus distribution
                            \Log::info('Rent A Bot deposit approved - no bonus distribution', [
                                'deposit_id' => $deposit->id,
                                'user_id' => $deposit->user_id
                            ]);
                        } elseif (in_array($invoiceType, ['profit invoice', 'trade profit'])) {
                            // Profit invoice or trade profit - give bonus to 3 levels of parents
                            // Uses each parent's active plan referral_level percentages
                            $this->distributeProfitBonus($deposit);
                        } else {
                            // Other invoice types - no bonus distribution
                            \Log::info('Deposit approved - no bonus distribution for this invoice type', [
                                'deposit_id' => $deposit->id,
                                'user_id' => $deposit->user_id,
                                'invoice_type' => $invoiceType ?? 'none'
                            ]);
                        }
                        
                        // Log success for verification
                        \Log::info('Referral bonuses distributed successfully', [
                            'deposit_id' => $deposit->id,
                            'user_id' => $deposit->user_id,
                            'invoice_id' => $deposit->invoice_id,
                            'invoice_type' => $invoiceType ?? 'none',
                            'amount' => $deposit->amount,
                            'currency' => $deposit->currency
                        ]);
                    } catch (\Exception $e) {
                        // Log error but don't fail the deposit approval
                        \Log::error('Error distributing referral bonuses: ' . $e->getMessage(), [
                            'deposit_id' => $deposit->id,
                            'user_id' => $deposit->user_id,
                            'invoice_id' => $deposit->invoice_id,
                            'invoice_type' => $deposit->invoice->invoice_type ?? 'none',
                            'error' => $e->getTraceAsString()
                        ]);
                    }
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
