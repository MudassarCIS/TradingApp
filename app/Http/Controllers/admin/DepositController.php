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
use App\Models\RentBotPackage;
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
            // Get all active PEX packages ordered by amount ASC for plan name matching
            $pexPackages = RentBotPackage::active()->orderBy('amount', 'asc')->get();
            
            $query = Deposit::with(['user.activeBots', 'approver', 'invoice.plan']);

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
            $orderColumn = 9; // Default to created_at (column index 9)
            $orderDirection = 'desc'; // Default to descending (latest first)
            
            // Check if DataTables sent order parameters
            if ($request->has('order') && is_array($request->get('order')) && count($request->get('order')) > 0) {
                $orderData = $request->get('order')[0];
                if (isset($orderData['column']) && $orderData['column'] !== null && $orderData['column'] !== '') {
                    $orderColumn = (int) $orderData['column'];
                }
                if (isset($orderData['dir']) && !empty($orderData['dir'])) {
                    $orderDirection = strtolower($orderData['dir']) === 'asc' ? 'asc' : 'desc';
                }
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

            // Always apply ordering - default to created_at desc if column not found
            if (isset($columns[$orderColumn])) {
                if ($orderColumn == 1) {
                    // Order by user name using join
                    if (!$query->getQuery()->joins) {
                        $query->join('users', 'deposits.user_id', '=', 'users.id')
                              ->select('deposits.*')
                              ->groupBy('deposits.id');
                    }
                    $query->orderBy('users.name', $orderDirection);
                    // Add secondary sort by created_at desc
                    $query->orderBy('deposits.created_at', 'desc');
                } elseif ($orderColumn == 5) {
                    // Order by invoice_type (from deposits table or invoice relationship)
                    $query->orderBy('deposits.invoice_type', $orderDirection);
                    // Add secondary sort by created_at desc
                    $query->orderBy('deposits.created_at', 'desc');
                } elseif ($orderColumn == 9) {
                    // Order by created_at (primary sort)
                    $query->orderBy('deposits.created_at', $orderDirection);
                } else {
                    // Order by other columns
                    $query->orderBy('deposits.' . $columns[$orderColumn], $orderDirection);
                    // Add secondary sort by created_at desc
                    $query->orderBy('deposits.created_at', 'desc');
                }
            } else {
                // Always default to latest first
                $query->orderBy('deposits.created_at', 'desc');
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
                ->addColumn('invoice_type', function ($deposit) use ($pexPackages) {
                    // Get invoice_type from deposit or invoice relationship
                    $invoiceType = $deposit->invoice_type;
                    if (!$invoiceType && $deposit->invoice) {
                        $invoiceType = $deposit->invoice->invoice_type;
                    }
                    
                    // Get package name from invoice plan or activeBots
                    $packageName = null;
                    
                    if ($invoiceType === 'PEX') {
                        // For PEX, match invoice/deposit amount with rent_bot_packages and assign name based on order
                        $amount = null;
                        if ($deposit->invoice) {
                            $amount = (float) $deposit->invoice->amount;
                        } else {
                            $amount = (float) $deposit->amount;
                        }
                        
                        $matchedPackage = $pexPackages->first(function ($package) use ($amount) {
                            return abs((float) $package->amount - $amount) < 0.01; // Allow small floating point differences
                        });
                        
                        if ($matchedPackage) {
                            // Find the index of the matched package (0-based)
                            $index = $pexPackages->search(function ($package) use ($matchedPackage) {
                                return $package->id === $matchedPackage->id;
                            });
                            // Assign name: PEX-1, PEX-2, etc. (index + 1)
                            // search() returns false if not found, so check for that
                            if ($index !== false) {
                                $packageName = 'PEX-' . ($index + 1);
                            }
                        }
                    } else {
                        // For other types (NEXA, package buy, etc.), try to get plan name from invoice plan or activeBots
                    if ($deposit->invoice) {
                        // First try to get plan name from invoice's plan relationship
                        if ($deposit->invoice->plan) {
                            $packageName = $deposit->invoice->plan->name;
                        } else {
                            // If no plan_id, try to get plan name from activeBots buy_plan_details
                            $user = $deposit->user;
                            if ($user && $invoiceType) {
                                // Find activeBot created around the same time as invoice (within 10 minutes)
                                $invoiceCreatedAt = $deposit->invoice->created_at ?? $deposit->created_at;
                                $startTime = $invoiceCreatedAt->copy()->subMinutes(10);
                                $endTime = $invoiceCreatedAt->copy()->addMinutes(10);
                                
                                $activeBot = $user->activeBots()
                                    ->where('buy_type', $invoiceType)
                                    ->where('created_at', '>=', $startTime)
                                    ->where('created_at', '<=', $endTime)
                                    ->first();
                                
                                if ($activeBot && $activeBot->buy_plan_details) {
                                    $planDetails = $activeBot->buy_plan_details;
                                    $packageName = $planDetails['name'] ?? null;
                                    }
                                }
                            }
                        }
                    }
                    
                    if ($invoiceType) {
                        $badgeClass = match($invoiceType) {
                            'PEX' => 'bg-primary',
                            'NEXA' => 'bg-info',
                            'package buy' => 'bg-success',
                            'profit invoice' => 'bg-warning',
                            default => 'bg-secondary'
                        };
                        $displayText = htmlspecialchars($invoiceType);
                        // Only show plan name in brackets for PEX and NEXA types
                        if ($packageName && in_array($invoiceType, ['PEX', 'NEXA'])) {
                            $displayText .= ' (' . htmlspecialchars($packageName) . ')';
                        }
                        return '<span class="badge ' . $badgeClass . '">' . $displayText . '</span>';
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
     * Distribute bonus for NEXA deposits only
     * Gets parent's active package (active_plan_id) and uses parent's plan direct_bonus
     * Gives bonus to first level parent only
     */
    private function distributeRentBotBonus($deposit): void
    {
        try {
            \Log::info('Starting NEXA bonus distribution', [
                'deposit_id' => $deposit->id,
                'user_id' => $deposit->user_id,
                'deposit_amount' => $deposit->amount,
                'currency' => $deposit->currency
            ]);
            
            $user = $deposit->user;
            
            // Check for referral record (with detailed logging)
            $allReferrals = Referral::where('referred_id', $user->id)->get();
            \Log::info('Checking referrals for user', [
                'user_id' => $user->id,
                'user_name' => $user->name,
                'total_referrals_found' => $allReferrals->count(),
                'referrals' => $allReferrals->map(function($r) {
                    return [
                        'id' => $r->id,
                        'referrer_id' => $r->referrer_id,
                        'status' => $r->status,
                        'referred_id' => $r->referred_id
                    ];
                })->toArray()
            ]);
            
            // Get first level parent from referrals table
            $referral = Referral::where('referred_id', $user->id)
                ->where('status', 'active')
                ->first();
            
            // If no active referral found, check if user has referred_by field as fallback
            if (!$referral && $user->referred_by) {
                \Log::info('No active referral found, checking referred_by field', [
                    'user_id' => $user->id,
                    'referred_by' => $user->referred_by
                ]);
                
                // Try to find parent using referred_by
                $parentFromReferredBy = \App\Models\User::find($user->referred_by);
                if ($parentFromReferredBy) {
                    \Log::info('Found parent via referred_by field, creating referral record', [
                        'user_id' => $user->id,
                        'parent_id' => $parentFromReferredBy->id,
                        'parent_name' => $parentFromReferredBy->name
                    ]);
                    
                    // Create referral record if it doesn't exist
                    $referral = Referral::firstOrCreate(
                        [
                            'referrer_id' => $parentFromReferredBy->id,
                            'referred_id' => $user->id
                        ],
                        [
                            'status' => 'active',
                            'commission_rate' => 10.00,
                            'joined_at' => now()
                        ]
                    );
                    
                    \Log::info('Referral record created/retrieved', [
                        'referral_id' => $referral->id,
                        'referrer_id' => $referral->referrer_id,
                        'referred_id' => $referral->referred_id,
                        'status' => $referral->status
                    ]);
                }
            }
            
            if (!$referral) {
                \Log::warning('No parent found for NEXA bonus - no referral record and no referred_by', [
                    'user_id' => $user->id,
                    'user_name' => $user->name,
                    'deposit_id' => $deposit->id,
                    'user_referred_by' => $user->referred_by ?? null
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
            
            \Log::info('Parent found for bonus distribution', [
                'parent_id' => $parent->id,
                'parent_name' => $parent->name,
                'investor_id' => $user->id,
                'deposit_id' => $deposit->id
            ]);
            
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
            
            \Log::info('Parent plan details retrieved', [
                'parent_id' => $parent->id,
                'plan_id' => $parentPlan->id,
                'plan_name' => $parentPlan->name,
                'direct_bonus' => $directBonus,
                'deposit_id' => $deposit->id
            ]);
            
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
                ->where('payment_type', 'Deposit')
                ->where('user_id', $parent->id)
                ->first();
            
            if ($existingBonus) {
                \Log::info('Bonus already distributed for this deposit', [
                    'deposit_id' => $deposit->id,
                    'parent_id' => $parent->id,
                    'existing_wallet_entry_id' => $existingBonus->id
                ]);
                return;
            }
            
            // Add bonus to customers_wallet
            DB::transaction(function () use ($deposit, $user, $parent, $parentPlan, $directBonus) {
                \Log::info('Creating customers_wallets entry for parent bonus', [
                    'parent_id' => $parent->id,
                    'bonus_amount' => $directBonus,
                    'deposit_id' => $deposit->id,
                    'investor_id' => $user->id
                ]);
                
                // Insert into customers_wallets with deposit id as related_id
                $walletEntry = CustomersWallet::create([
                    'user_id' => $parent->id,
                    'amount' => $directBonus,
                    'currency' => 'USDT',
                    'payment_type' => 'Deposit',
                    'transaction_type' => 'debit',
                    'related_id' => $deposit->id,
                    'caused_by_user_id' => $user->id, // User who made the deposit/investment
                ]);

                \Log::info('Customers_wallets entry created successfully', [
                    'wallet_entry_id' => $walletEntry->id,
                    'parent_id' => $parent->id,
                    'bonus_amount' => $directBonus
                ]);

                // Update parent's wallet summary
                $wallet = Wallet::where('user_id', $parent->id)->where('currency', 'USDT')->first();
                $oldBalance = $wallet ? $wallet->balance : 0;
                
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
                    \Log::info('Created new wallet for parent', [
                        'parent_id' => $parent->id,
                        'new_balance' => $directBonus
                    ]);
                } else {
                    $wallet->increment('balance', $directBonus);
                    $wallet->increment('total_profit', $directBonus);
                    \Log::info('Updated parent wallet balance', [
                        'parent_id' => $parent->id,
                        'old_balance' => $oldBalance,
                        'new_balance' => $wallet->balance,
                        'bonus_added' => $directBonus
                    ]);
                }

                // Update referral totals
                $ref = Referral::where('referrer_id', $parent->id)
                    ->where('referred_id', $user->id)
                    ->first();
                if ($ref) {
                    $oldTotalCommission = $ref->total_commission;
                    $oldPendingCommission = $ref->pending_commission;
                    $ref->increment('total_commission', $directBonus);
                    $ref->increment('pending_commission', $directBonus);
                    \Log::info('Updated referral totals', [
                        'parent_id' => $parent->id,
                        'investor_id' => $user->id,
                        'old_total_commission' => $oldTotalCommission,
                        'new_total_commission' => $ref->total_commission,
                        'old_pending_commission' => $oldPendingCommission,
                        'new_pending_commission' => $ref->pending_commission,
                        'bonus_added' => $directBonus
                    ]);
                } else {
                    \Log::warning('Referral record not found for updating totals', [
                        'parent_id' => $parent->id,
                        'investor_id' => $user->id
                    ]);
                }
                
                // Log bonus distribution for verification
                \Log::info('NEXA bonus distributed to parent successfully', [
                    'parent_id' => $parent->id,
                    'parent_plan_id' => $parentPlan->id,
                    'parent_plan_name' => $parentPlan->name,
                    'investor_id' => $user->id,
                    'deposit_id' => $deposit->id,
                    'bonus_amount' => $directBonus,
                    'wallet_entry_id' => $walletEntry->id,
                    'wallet_id' => $wallet->id
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
            $invoiceType = null;
            if ($deposit->invoice_id && $deposit->invoice) {
                $deposit->invoice->update(['status' => 'Paid']);
                $invoiceType = $deposit->invoice->invoice_type ?? null;
                
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
            
            // Update user's active plans based on their investments (NEXA or PEX)
            $user = $deposit->user;
            if ($invoiceType === 'NEXA') {
                // Update user's active NEXA plan (only if investment > previous)
                $this->updateUserActiveNexaPlan($user);
            } elseif ($invoiceType === 'PEX') {
                // Update user's active PEX plan
                $this->updateUserActivePexPlan($user);
            }
            
            // Update all parent plans based on their investments
            $this->updateParentPlansFromInvestments($user);
            
            // Distribute referral bonuses based on invoice type
            // For "NEXA" only: Get parent's active package and direct_bonus, give to first level parent only
            // For "NEXA Profit": 3-level bonuses using parent's active plan referral_level percentages
            // For "profit invoice": 3-level bonuses using referral_level percentages
            try {
                if ($invoiceType === null) {
                    $invoiceType = $deposit->invoice_type ?? ($deposit->invoice->invoice_type ?? null);
                }
                
                if ($invoiceType === 'NEXA') {
                    // Special handling for NEXA only - get parent's active package and direct_bonus
                    $this->distributeRentBotBonus($deposit);
                } else {
                    // Use existing referral service for other types (including "NEXA Profit" and "profit invoice")
                    app(ReferralService::class)->distributeReferralBonuses($deposit->user, $deposit);
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
                    $invoiceType = null;
                    if ($deposit->invoice_id) {
                        $deposit->load('invoice');
                        if ($deposit->invoice) {
                            $deposit->invoice->update(['status' => 'Paid']);
                            $invoiceType = $deposit->invoice->invoice_type ?? null;
                            
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
                    
                    // Update user's active plans based on their investments (NEXA or PEX)
                    $user = $deposit->user;
                    if ($invoiceType === 'NEXA') {
                        // Update user's active NEXA plan (only if investment > previous)
                        $this->updateUserActiveNexaPlan($user);
                    } elseif ($invoiceType === 'PEX') {
                        // Update user's active PEX plan
                        $this->updateUserActivePexPlan($user);
                    }
                    
                    // Update all parent plans based on their investments
                    $this->updateParentPlansFromInvestments($user);
                    
                    // Distribute referral bonuses based on invoice type
                    // For "NEXA" only: Get parent's active package and direct_bonus, give to first level parent only
                    // For "NEXA Profit": 3-level bonuses using parent's active plan referral_level percentages
                    // For "profit invoice": 3-level bonuses using referral_level percentages
                    try {
                        // Ensure user relationship is loaded
                        if (!$deposit->relationLoaded('user')) {
                            $deposit->load('user');
                        }
                        
                        if ($invoiceType === null) {
                            $invoiceType = $deposit->invoice_type ?? ($deposit->invoice->invoice_type ?? null);
                        }
                        
                        if ($invoiceType === 'NEXA') {
                            // Special handling for NEXA only - get parent's active package and direct_bonus
                            $this->distributeRentBotBonus($deposit);
                        } else {
                            // Use existing referral service for other types (including "NEXA Profit" and "profit invoice")
                            $referralService = app(ReferralService::class);
                            $referralService->distributeReferralBonuses($deposit->user, $deposit);
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

    /**
     * Update user's active NEXA plan based on their paid NEXA invoices
     * Only updates if new plan's investment_amount > current plan's investment_amount
     */
    protected function updateUserActiveNexaPlan($user): void
    {
        try {
            \Log::info('Starting NEXA plan update check', [
                'user_id' => $user->id,
                'current_active_plan_id' => $user->active_plan_id,
                'current_active_plan_name' => $user->active_plan_name ?? null
            ]);
            
            // Get all paid NEXA invoices with plans
            $nexaInvoices = UserInvoice::where('user_id', $user->id)
                ->where('invoice_type', 'NEXA')
                ->where('status', 'Paid')
                ->with('plan')
                ->get();
            
            \Log::info('Retrieved NEXA invoices for plan update', [
                'user_id' => $user->id,
                'invoices_count' => $nexaInvoices->count()
            ]);
            
            if ($nexaInvoices->isEmpty()) {
                \Log::info('No NEXA investments found, skipping plan update', [
                    'user_id' => $user->id
                ]);
                return; // No NEXA investments
            }
            
            // Find invoice with maximum investment amount
            $maxInvoice = null;
            $maxInvestmentAmount = 0;
            
            foreach ($nexaInvoices as $invoice) {
                if ($invoice->plan) {
                    $investmentAmount = (float) $invoice->plan->investment_amount;
                    \Log::debug('Checking invoice for max investment', [
                        'user_id' => $user->id,
                        'invoice_id' => $invoice->id,
                        'plan_id' => $invoice->plan->id,
                        'plan_name' => $invoice->plan->name,
                        'investment_amount' => $investmentAmount
                    ]);
                    if ($investmentAmount > $maxInvestmentAmount) {
                        $maxInvestmentAmount = $investmentAmount;
                        $maxInvoice = $invoice;
                    }
                }
            }
            
            \Log::info('Found maximum NEXA investment', [
                'user_id' => $user->id,
                'max_investment_amount' => $maxInvestmentAmount,
                'max_invoice_id' => $maxInvoice ? $maxInvoice->id : null
            ]);
            
            if ($maxInvoice && $maxInvoice->plan) {
                $newPlan = $maxInvoice->plan;
                $oldPlanId = $user->active_plan_id;
                $oldPlan = $oldPlanId ? Plan::find($oldPlanId) : null;
                $oldPlanInvestmentAmount = $oldPlan ? (float) $oldPlan->investment_amount : 0;
                
                \Log::info('Comparing plans for update', [
                    'user_id' => $user->id,
                    'old_plan_id' => $oldPlanId,
                    'old_plan_name' => $oldPlan ? $oldPlan->name : null,
                    'old_plan_investment_amount' => $oldPlanInvestmentAmount,
                    'new_plan_id' => $newPlan->id,
                    'new_plan_name' => $newPlan->name,
                    'new_plan_investment_amount' => $maxInvestmentAmount
                ]);
                
                // Only update if new plan's investment_amount > current plan's investment_amount
                if ($maxInvestmentAmount > $oldPlanInvestmentAmount) {
                    $oldPlanName = $user->active_plan_name ?? ($oldPlan ? $oldPlan->name : null);
                    
                    $user->active_plan_id = $newPlan->id;
                    if (\Illuminate\Support\Facades\Schema::hasColumn('users', 'active_plan_name')) {
                        $user->active_plan_name = $newPlan->name;
                    }
                    $user->active_investment_amount = $maxInvestmentAmount;
                    $user->save();
                    
                    \Log::info('User active plan updated in users table', [
                        'user_id' => $user->id,
                        'old_plan_id' => $oldPlanId,
                        'old_plan_name' => $oldPlanName,
                        'new_plan_id' => $newPlan->id,
                        'new_plan_name' => $newPlan->name,
                        'new_investment_amount' => $maxInvestmentAmount
                    ]);
                    
                    // Save to plan history
                    $planHistory = UserPlanHistory::create([
                        'user_id' => $user->id,
                        'plan_id' => $newPlan->id,
                        'plan_name' => $newPlan->name,
                        'joining_fee' => $newPlan->joining_fee ?? 0,
                        'investment_amount' => $maxInvestmentAmount,
                        'notes' => $oldPlanName 
                            ? "Auto-updated from {$oldPlanName} to {$newPlan->name} (max NEXA investment: \${$maxInvestmentAmount})"
                            : "Auto-assigned plan {$newPlan->name} (max NEXA investment: \${$maxInvestmentAmount})"
                    ]);
                    
                    \Log::info('User active NEXA plan updated successfully', [
                        'user_id' => $user->id,
                        'old_plan_id' => $oldPlanId,
                        'old_plan_name' => $oldPlanName,
                        'new_plan_id' => $newPlan->id,
                        'new_plan_name' => $newPlan->name,
                        'investment_amount' => $maxInvestmentAmount,
                        'plan_history_id' => $planHistory->id
                    ]);
                } else {
                    \Log::info('Plan update skipped - new investment not higher than current', [
                        'user_id' => $user->id,
                        'current_plan_investment' => $oldPlanInvestmentAmount,
                        'new_plan_investment' => $maxInvestmentAmount
                    ]);
                }
            } else {
                \Log::warning('Max invoice or plan not found', [
                    'user_id' => $user->id,
                    'max_invoice_id' => $maxInvoice ? $maxInvoice->id : null,
                    'has_plan' => $maxInvoice && $maxInvoice->plan ? true : false
                ]);
            }
        } catch (\Exception $e) {
            \Log::error('Error updating user active NEXA plan: ' . $e->getMessage(), [
                'user_id' => $user->id ?? null
            ]);
            // Don't throw - allow deposit approval to continue
        }
    }

    /**
     * Update user's active PEX plan based on their paid PEX invoices
     * Updates to package with maximum amount
     */
    protected function updateUserActivePexPlan($user): void
    {
        try {
            \Log::info('Starting PEX plan update check', [
                'user_id' => $user->id,
                'current_active_pex_plan_id' => $user->active_pex_plan_id
            ]);
            
            // Get all paid PEX invoices
            $pexInvoices = UserInvoice::where('user_id', $user->id)
                ->where('invoice_type', 'PEX')
                ->where('status', 'Paid')
                ->get();
            
            \Log::info('Retrieved PEX invoices for plan update', [
                'user_id' => $user->id,
                'invoices_count' => $pexInvoices->count()
            ]);
            
            if ($pexInvoices->isEmpty()) {
                \Log::info('No PEX investments found, skipping plan update', [
                    'user_id' => $user->id
                ]);
                return; // No PEX investments
            }
            
            // Get all active PEX packages ordered by amount
            $pexPackages = RentBotPackage::active()->orderBy('amount', 'asc')->get();
            
            \Log::info('Retrieved PEX packages', [
                'user_id' => $user->id,
                'packages_count' => $pexPackages->count()
            ]);
            
            if ($pexPackages->isEmpty()) {
                \Log::warning('No PEX packages available', [
                    'user_id' => $user->id
                ]);
                return; // No PEX packages available
            }
            
            // Find invoice with maximum amount and match with package
            $maxInvoice = null;
            $maxAmount = 0;
            $matchedPackage = null;
            
            foreach ($pexInvoices as $invoice) {
                $invoiceAmount = (float) $invoice->amount;
                \Log::debug('Checking PEX invoice for max amount', [
                    'user_id' => $user->id,
                    'invoice_id' => $invoice->id,
                    'invoice_amount' => $invoiceAmount
                ]);
                if ($invoiceAmount > $maxAmount) {
                    // Match invoice amount with package
                    $package = $pexPackages->first(function ($pkg) use ($invoiceAmount) {
                        return abs((float) $pkg->amount - $invoiceAmount) < 0.01;
                    });
                    
                    if ($package) {
                        $maxAmount = $invoiceAmount;
                        $maxInvoice = $invoice;
                        $matchedPackage = $package;
                    }
                }
            }
            
            \Log::info('Found maximum PEX investment', [
                'user_id' => $user->id,
                'max_amount' => $maxAmount,
                'max_invoice_id' => $maxInvoice ? $maxInvoice->id : null,
                'matched_package_id' => $matchedPackage ? $matchedPackage->id : null
            ]);
            
            if ($matchedPackage) {
                $oldPexPlanId = $user->active_pex_plan_id;
                
                \Log::info('Comparing PEX plans for update', [
                    'user_id' => $user->id,
                    'old_pex_plan_id' => $oldPexPlanId,
                    'new_pex_plan_id' => $matchedPackage->id,
                    'new_package_amount' => $matchedPackage->amount
                ]);
                
                // Update if different
                if ($oldPexPlanId != $matchedPackage->id) {
                    $oldPexPlan = $oldPexPlanId ? RentBotPackage::find($oldPexPlanId) : null;
                    $oldPexPlanName = $oldPexPlan ? "PEX-" . ($pexPackages->search(function($p) use ($oldPexPlan) { return $p->id === $oldPexPlan->id; }) + 1) : null;
                    
                    $user->active_pex_plan_id = $matchedPackage->id;
                    $user->save();
                    
                    \Log::info('User active PEX plan updated in users table', [
                        'user_id' => $user->id,
                        'old_pex_plan_id' => $oldPexPlanId,
                        'old_pex_plan_name' => $oldPexPlanName,
                        'new_pex_plan_id' => $matchedPackage->id,
                        'new_package_amount' => $matchedPackage->amount
                    ]);
                    
                    // Find package index for name
                    $packageIndex = $pexPackages->search(function($p) use ($matchedPackage) {
                        return $p->id === $matchedPackage->id;
                    });
                    $packageName = $packageIndex !== false ? 'PEX-' . ($packageIndex + 1) : 'PEX Package';
                    
                    // Save to plan history (using NEXA plan structure but noting it's PEX)
                    $planHistory = UserPlanHistory::create([
                        'user_id' => $user->id,
                        'plan_id' => null, // PEX doesn't use plans table
                        'plan_name' => $packageName,
                        'joining_fee' => 0,
                        'investment_amount' => $maxAmount,
                        'notes' => $oldPexPlanName 
                            ? "Auto-updated PEX plan from {$oldPexPlanName} to {$packageName} (max PEX investment: \${$maxAmount})"
                            : "Auto-assigned PEX plan {$packageName} (max PEX investment: \${$maxAmount})"
                    ]);
                    
                    \Log::info('User active PEX plan updated successfully', [
                        'user_id' => $user->id,
                        'old_pex_plan_id' => $oldPexPlanId,
                        'old_pex_plan_name' => $oldPexPlanName,
                        'new_pex_plan_id' => $matchedPackage->id,
                        'package_name' => $packageName,
                        'investment_amount' => $maxAmount,
                        'plan_history_id' => $planHistory->id
                    ]);
                } else {
                    \Log::info('PEX plan update skipped - same plan already active', [
                        'user_id' => $user->id,
                        'pex_plan_id' => $oldPexPlanId
                    ]);
                }
            } else {
                \Log::warning('Matched package not found for PEX plan update', [
                    'user_id' => $user->id,
                    'max_amount' => $maxAmount
                ]);
            }
        } catch (\Exception $e) {
            \Log::error('Error updating user active PEX plan: ' . $e->getMessage(), [
                'user_id' => $user->id ?? null
            ]);
            // Don't throw - allow deposit approval to continue
        }
    }

    /**
     * Update all parent plans (NEXA and PEX) based on their investments
     * Loops through up to 3 levels of parents
     */
    protected function updateParentPlansFromInvestments($user): void
    {
        try {
            \Log::info('Starting parent plans update from investments', [
                'user_id' => $user->id,
                'user_name' => $user->name
            ]);
            
            $level = 1;
            $parent = $this->getParent($user->id);
            
            while ($level <= 3 && $parent) {
                try {
                    \Log::info('Processing parent plan update', [
                        'level' => $level,
                        'parent_id' => $parent->id,
                        'parent_name' => $parent->name,
                        'investor_id' => $user->id
                    ]);
                    
                    // Update parent's NEXA plan
                    \Log::info('Updating parent NEXA plan', [
                        'level' => $level,
                        'parent_id' => $parent->id
                    ]);
                    $this->updateUserActiveNexaPlan($parent);
                    
                    // Update parent's PEX plan
                    \Log::info('Updating parent PEX plan', [
                        'level' => $level,
                        'parent_id' => $parent->id
                    ]);
                    $this->updateUserActivePexPlan($parent);
                    
                    \Log::info('Completed parent plan updates for level', [
                        'level' => $level,
                        'parent_id' => $parent->id
                    ]);
                } catch (\Exception $e) {
                    \Log::error("Error updating parent plans for level {$level}: " . $e->getMessage(), [
                        'parent_id' => $parent->id ?? null,
                        'level' => $level,
                        'investor_id' => $user->id,
                        'trace' => $e->getTraceAsString()
                    ]);
                }
                
                $level++;
                $parent = $this->getParent($parent->id);
            }
            
            \Log::info('Completed all parent plans update from investments', [
                'user_id' => $user->id,
                'levels_processed' => $level - 1
            ]);
        } catch (\Exception $e) {
            \Log::error('Error in updateParentPlansFromInvestments: ' . $e->getMessage(), [
                'user_id' => $user->id ?? null
            ]);
            // Don't throw - allow deposit approval to continue
        }
    }

    /**
     * Get parent user from referral chain
     */
    protected function getParent($userId)
    {
        // First try referrals table
        $referral = Referral::where('referred_id', $userId)
            ->where('status', 'active')
            ->first();
        
        if ($referral) {
            return \App\Models\User::find($referral->referrer_id);
        }
        
        // Fallback: Check if user has referred_by field
        $user = \App\Models\User::find($userId);
        if ($user && $user->referred_by) {
            $parent = \App\Models\User::find($user->referred_by);
            if ($parent) {
                // Create referral record if it doesn't exist
                Referral::firstOrCreate(
                    [
                        'referrer_id' => $parent->id,
                        'referred_id' => $user->id
                    ],
                    [
                        'status' => 'active',
                        'commission_rate' => 10.00,
                        'joined_at' => now()
                    ]
                );
                return $parent;
            }
        }
        
        return null;
    }

    public function edit($id)
    {
        $deposit = Deposit::with(['user', 'approver', 'invoice'])->findOrFail($id);
        return view('admin.deposits.edit', compact('deposit'));
    }
}
