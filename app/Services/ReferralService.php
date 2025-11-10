<?php

namespace App\Services;

use App\Models\User;
use App\Models\Referral;
use App\Models\Plan;
use App\Models\BonusWallet;
use App\Models\CustomersWallet;
use App\Models\Wallet;
use App\Models\UserPlanHistory;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ReferralService
{
    /**
     * Convert any currency to USDT using Binance API
     */
    public function convertToUSDT(string $currency, float $amount): float
    {
        $currency = strtoupper($currency);
        if ($currency === 'USDT') {
            return round($amount, 8);
        }

        $symbol = $currency . 'USDT';
        $response = Http::get('https://api.binance.com/api/v3/ticker/price', ['symbol' => $symbol]);

        if ($response->successful()) {
            $rate = (float) $response->json()['price'];
            $usdtAmount = $amount * $rate;
            
            // Round to 8 decimal places for precision, 2 for USDT/USDC
            if (in_array($currency, ['USDT', 'USDC'])) {
                return round($usdtAmount, 2);
            }
            return round($usdtAmount, 8);
        }
        
        throw new \Exception("Unable to fetch Binance rate for {$currency}");
    }

    /**
     * Assign active plan to user based on USDT investment amount
     * Tracks plan changes and saves to plan history
     */
    public function assignPlanToUser(User $user, float $usdtAmount): ?Plan
    {
        // Get current plan before update
        $oldPlanId = $user->active_plan_id;
        $oldPlanName = null;
        try {
            if (Schema::hasColumn('users', 'active_plan_name')) {
                $oldPlanName = $user->active_plan_name;
            }
        } catch (\Exception $e) {
            // Column doesn't exist, use null
        }
        
        // Try matching plan by exact amount first
        $plan = Plan::where('investment_amount', $usdtAmount)
                    ->where('is_active', true)
                    ->first();
        
        // If no exact match, find the plan with the closest investment_amount that's <= the amount
        // This handles cases where user deposits more than exact plan amounts
        if (!$plan) {
            $plan = Plan::where('investment_amount', '<=', $usdtAmount)
                        ->where('is_active', true)
                        ->orderBy('investment_amount', 'desc')
                        ->first();
        }

        // If still no plan found, default to Starter plan
        if (!$plan) {
            $plan = Plan::where('name', 'Starter')
                        ->where('is_active', true)
                        ->first();
        }

        // Check if plan has changed
        $planChanged = false;
        if ($plan) {
            $oldPlanName = $oldPlanName ?? null;
            $planChanged = ($oldPlanId != $plan->id || $oldPlanName != $plan->name);
            
            // Update user's active plan
            $user->active_investment_amount = $usdtAmount;
            $user->active_plan_id = $plan->id;
            
            // Only update active_plan_name if column exists (check without throwing error)
            try {
                if (Schema::hasColumn('users', 'active_plan_name')) {
                    $user->active_plan_name = $plan->name;
                }
            } catch (\Exception $e) {
                // Column might not exist, continue without it
                \Log::debug('active_plan_name column check failed: ' . $e->getMessage());
            }
            
            $user->save();

            // If plan changed, save to plan history
            if ($planChanged) {
                try {
                    $this->savePlanHistory($user, $plan, $usdtAmount, $oldPlanName);
                } catch (\Exception $e) {
                    // If table doesn't exist yet, just log the error
                    \Log::warning('Could not save plan history: ' . $e->getMessage());
                }
            }
        }

        return $plan;
    }

    /**
     * Save plan change to history
     */
    protected function savePlanHistory(User $user, Plan $plan, float $investmentAmount, ?string $oldPlanName = null): void
    {
        if (!$user || !$plan) {
            return;
        }
        
        try {
            UserPlanHistory::create([
                'user_id' => $user->id,
                'plan_id' => $plan->id,
                'plan_name' => $plan->name ?? 'Unknown',
                'joining_fee' => $plan->joining_fee ?? 0,
                'investment_amount' => $investmentAmount,
                'notes' => $oldPlanName ? "Changed from {$oldPlanName} to {$plan->name}" : "Initial plan assignment: {$plan->name}",
            ]);
        } catch (\Exception $e) {
            // If table doesn't exist, just log and continue
            \Log::warning('Could not save plan history: ' . $e->getMessage());
            // Don't re-throw - allow the process to continue
        }
    }

    /**
     * Distribute referral bonuses based on deposit/invoice type
     */
    public function distributeReferralBonuses(User $investor, $deposit): void
    {
        try {
            // Check if bonuses have already been distributed for this deposit to prevent duplicates
            if ($deposit->id) {
                $existingBonus = CustomersWallet::where('related_id', $deposit->id)
                    ->where('payment_type', 'bonus')
                    ->first();
                if ($existingBonus) {
                    // Bonuses already distributed for this deposit
                    return;
                }
            }
            
            // Get invoice type if deposit is associated with an invoice
            $invoiceType = null;
            if ($deposit->invoice_id && $deposit->relationLoaded('invoice')) {
                $invoice = $deposit->invoice;
                $invoiceType = $invoice->invoice_type ?? null;
            } elseif ($deposit->invoice_id) {
                $invoice = \App\Models\UserInvoice::find($deposit->invoice_id);
                $invoiceType = $invoice->invoice_type ?? null;
            }
            
            // Route to appropriate bonus distribution method based on invoice type
            // "Rent A Bot" and "Sharing Nexa" are treated as package buy (direct_bonus to level 1 only)
            // "profit invoice" uses 3-level bonuses
            if (in_array($invoiceType, ['Rent A Bot', 'Sharing Nexa', 'package buy'])) {
                // Type 1: Package buy (Rent A Bot, Sharing Nexa, or package buy) - give direct_bonus to first level parent only
                $this->distributePackageBuyBonus($investor, $deposit);
            } elseif ($invoiceType === 'profit invoice') {
                // Type 2: Profit invoice - give 3-level bonuses using referral_level percentages
                $this->distributeProfitInvoiceBonus($investor, $deposit);
            } else {
                // Default: For deposits without invoice or unknown type, use package buy logic
                // This treats deposits without invoices as package purchases
                $this->distributePackageBuyBonus($investor, $deposit);
            }
        } catch (\Exception $e) {
            \Log::error('Error in distributeReferralBonuses: ' . $e->getMessage(), [
                'deposit_id' => $deposit->id ?? null,
                'user_id' => $investor->id,
                'invoice_type' => $invoiceType ?? 'none'
            ]);
            throw $e; // Re-throw to be caught by controller
        }
    }

    /**
     * Distribute package buy bonus - gives direct_bonus to first level parent only
     * Used when invoice_type is "Rent A Bot", "Sharing Nexa", or "package buy"
     */
    public function distributePackageBuyBonus(User $investor, $deposit): void
    {
        try {
            $currency = $deposit->currency ?? 'USDT';
            $amount = (float) $deposit->amount;
            $amountInUSDT = $this->convertToUSDT($currency, $amount);
            
            // Assign plan to investor
            $plan = $this->assignPlanToUser($investor, $amountInUSDT);
            
            if (!$plan) {
                \Log::warning('No plan found for package buy deposit', [
                    'deposit_id' => $deposit->id,
                    'user_id' => $investor->id,
                    'amount' => $amountInUSDT
                ]);
                return;
            }
            
            // Get direct_bonus from plan
            $directBonus = (float) ($plan->direct_bonus ?? 0);
            
            if ($directBonus <= 0) {
                \Log::info('No direct_bonus configured for plan', [
                    'plan_id' => $plan->id,
                    'plan_name' => $plan->name
                ]);
                return;
            }
            
            // Get first level parent only
            $parent = $this->getParent($investor->id);
            
            if (!$parent) {
                \Log::info('No parent found for package buy bonus', [
                    'investor_id' => $investor->id,
                    'deposit_id' => $deposit->id
                ]);
                return;
            }
            
            // Get parent's active plan to check if they qualify
            $parentActivePlan = $this->getParentActivePlan($parent);
            
            if ($parentActivePlan) {
                // Convert direct_bonus to USDT if needed (assuming direct_bonus is in USDT)
                $bonusUSDT = $directBonus;
                
                if ($bonusUSDT > 0) {
                    DB::transaction(function () use ($deposit, $investor, $parent, $bonusUSDT, $directBonus) {
                        // Insert into customers_wallets with deposit id as related_id
                        $walletEntry = CustomersWallet::create([
                            'user_id' => $parent->id,
                            'amount' => $bonusUSDT,
                            'currency' => 'USDT',
                            'payment_type' => 'bonus',
                            'transaction_type' => 'debit',
                            'related_id' => $deposit->id,
                        ]);

                        // Update parent's wallet summary
                        $this->creditParentWallet($parent->id, $bonusUSDT);

                        // Update referral totals
                        $this->updateReferralTotals($parent->id, $investor->id, $bonusUSDT);
                        
                        // Log bonus distribution for verification
                        \Log::info('Package buy bonus distributed', [
                            'level' => 1,
                            'parent_id' => $parent->id,
                            'investor_id' => $investor->id,
                            'deposit_id' => $deposit->id,
                            'bonus_amount' => $bonusUSDT,
                            'direct_bonus' => $directBonus,
                            'wallet_entry_id' => $walletEntry->id
                        ]);
                    });
                }
            }
        } catch (\Exception $e) {
            \Log::error('Error in distributePackageBuyBonus: ' . $e->getMessage(), [
                'deposit_id' => $deposit->id ?? null,
                'user_id' => $investor->id
            ]);
            throw $e;
        }
    }

    /**
     * Distribute profit invoice bonus - gives 3-level bonuses using referral_level percentages
     * Used when invoice_type is "profit invoice"
     */
    public function distributeProfitInvoiceBonus(User $investor, $deposit): void
    {
        try {
            $currency = $deposit->currency ?? 'USDT';
            $amount = (float) $deposit->amount;
            $amountInUSDT = $this->convertToUSDT($currency, $amount);
            
            // Assign plan to investor
            $this->assignPlanToUser($investor, $amountInUSDT);
        } catch (\Exception $e) {
            \Log::error('Error in distributeProfitInvoiceBonus (initial setup): ' . $e->getMessage());
            throw $e;
        }

        try {
            $level = 1;
            $parent = $this->getParent($investor->id);

            while ($level <= 3 && $parent) {
                try {
                    // Check if parent has an active plan with paid invoice
                    $parentActivePlan = $this->getParentActivePlan($parent);
                    
                    if ($parentActivePlan) {
                        $percentField = 'referral_level_' . $level;
                        $percent = (float) ($parentActivePlan->$percentField ?? 0);

                        if ($percent > 0) {
                            $bonusUSDT = round($amountInUSDT * ($percent / 100), 8);

                            if ($bonusUSDT > 0) {
                                DB::transaction(function () use ($deposit, $investor, $parent, $level, $bonusUSDT, $percent) {
                                    // Insert into customers_wallets with deposit id as related_id
                                    $walletEntry = CustomersWallet::create([
                                        'user_id' => $parent->id,
                                        'amount' => $bonusUSDT,
                                        'currency' => 'USDT',
                                        'payment_type' => 'bonus',
                                        'transaction_type' => 'debit',
                                        'related_id' => $deposit->id,
                                    ]);

                                    // Update parent's wallet summary
                                    $this->creditParentWallet($parent->id, $bonusUSDT);

                                    // Update referral totals
                                    $this->updateReferralTotals($parent->id, $investor->id, $bonusUSDT);
                                    
                                    // Log bonus distribution for verification
                                    \Log::info('Profit invoice bonus distributed', [
                                        'level' => $level,
                                        'parent_id' => $parent->id,
                                        'investor_id' => $investor->id,
                                        'deposit_id' => $deposit->id,
                                        'bonus_amount' => $bonusUSDT,
                                        'percent' => $percent,
                                        'wallet_entry_id' => $walletEntry->id
                                    ]);
                                });
                            }
                        }
                    }
                } catch (\Exception $e) {
                    // Log error for this level but continue to next level
                    \Log::error("Error processing profit invoice bonus for level {$level}: " . $e->getMessage(), [
                        'parent_id' => $parent->id ?? null,
                        'investor_id' => $investor->id,
                        'level' => $level
                    ]);
                }
                
                $level++;
                $parent = $this->getParent($parent->id);
            }
        } catch (\Exception $e) {
            \Log::error('Error in distributeProfitInvoiceBonus (parent loop): ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get parent's active plan (Sharing Nexa with paid invoice)
     * Determines plan based on joining_fee amount from plans table
     * If parent has no payment, returns Starter plan as default
     */
    protected function getParentActivePlan(User $parent): ?Plan
    {
        // Get active Sharing Nexa bots with paid invoices
        $activeBots = $parent->activeBots()
            ->where('buy_type', 'Sharing Nexa')
            ->latest()
            ->get();

        foreach ($activeBots as $bot) {
            // Check if there's a paid invoice matching this bot
            $botCreatedAt = $bot->created_at;
            $startTime = $botCreatedAt->copy()->subMinutes(10);
            $endTime = $botCreatedAt->copy()->addMinutes(10);
            
            $invoice = $parent->invoices()
                ->where('invoice_type', 'Sharing Nexa')
                ->where('status', 'Paid')
                ->where('created_at', '>=', $startTime)
                ->where('created_at', '<=', $endTime)
                ->first();
            
            if ($invoice) {
                // Get plan details from bot to extract joining_fee
                $planDetails = $bot->buy_plan_details ?? [];
                $userJoiningFee = (float) ($planDetails['joining_fee'] ?? 0);
                $userInvestmentAmount = (float) ($planDetails['investment_amount'] ?? 0);
                
                if ($userJoiningFee > 0) {
                    // Determine plan based on joining_fee amount from plans table
                    // Logic: Find the plan where plan's joining_fee <= user's joining_fee
                    // This matches the highest plan tier the user qualifies for
                    // Example: 
                    // - If user's joining_fee is 10-24 → Starter (joining_fee = 10)
                    // - If user's joining_fee is 25-49 → Bronze (joining_fee = 25)
                    // - If user's joining_fee is 50-124 → Silver (joining_fee = 50)
                    // - If user's joining_fee is 125+ → Gold (joining_fee = 125)
                    
                    $plan = Plan::where('is_active', true)
                        ->where('joining_fee', '<=', $userJoiningFee)
                        ->orderBy('joining_fee', 'desc')
                        ->first();
                    
                    if ($plan && $userInvestmentAmount > 0) {
                        // Check if parent's plan has changed and update if needed
                        try {
                            $this->updateParentPlanIfChanged($parent, $plan, $userInvestmentAmount);
                        } catch (\Exception $e) {
                            // Log error but don't fail the bonus distribution
                            \Log::error('Error updating parent plan: ' . $e->getMessage());
                        }
                    }
                    
                    return $plan;
                }
            }
        }
        
        // If parent has no active Sharing Nexa plan with paid invoice, 
        // return Starter plan as default so they can still receive referral bonuses
        $starterPlan = Plan::where('name', 'Starter')
            ->where('is_active', true)
            ->first();
        
        if ($starterPlan) {
            // Update parent's plan to Starter if they don't have one set
            try {
                if (!$parent->active_plan_id || $parent->active_plan_id != $starterPlan->id) {
                    $parent->active_plan_id = $starterPlan->id;
                    
                    // Only update active_plan_name if column exists
                    try {
                        if (Schema::hasColumn('users', 'active_plan_name')) {
                            $parent->active_plan_name = $starterPlan->name;
                        }
                    } catch (\Exception $e) {
                        // Column doesn't exist, skip it
                    }
                    
                    $parent->save();
                    
                    // Log that we're using default Starter plan
                    \Log::info('Using default Starter plan for parent without payment', [
                        'parent_id' => $parent->id,
                        'plan_id' => $starterPlan->id
                    ]);
                }
            } catch (\Exception $e) {
                // Log error but don't fail - still return the Starter plan
                \Log::error('Error setting default Starter plan for parent: ' . $e->getMessage(), [
                    'parent_id' => $parent->id
                ]);
            }
            
            return $starterPlan;
        }
        
        // If Starter plan doesn't exist, return null (shouldn't happen in normal operation)
        \Log::warning('Starter plan not found in database', [
            'parent_id' => $parent->id
        ]);
        
        return null;
    }

    /**
     * Update parent's plan if it has changed
     */
    protected function updateParentPlanIfChanged(User $parent, Plan $newPlan, float $investmentAmount): void
    {
        if (!$newPlan || !$parent) {
            return;
        }
        
        $oldPlanId = $parent->active_plan_id;
        $oldPlanName = $parent->active_plan_name ?? null;
        
        // Check if plan has changed
        if ($oldPlanId != $newPlan->id || $oldPlanName != $newPlan->name) {
            // Update parent's active plan
            $parent->active_plan_id = $newPlan->id;
            
            // Only update active_plan_name if column exists (check without throwing error)
            try {
                if (Schema::hasColumn('users', 'active_plan_name')) {
                    $parent->active_plan_name = $newPlan->name;
                }
            } catch (\Exception $e) {
                // Column might not exist, continue without it
                \Log::debug('active_plan_name column check failed: ' . $e->getMessage());
            }
            
            $parent->active_investment_amount = $investmentAmount;
            $parent->save();
            
            // Save to plan history (only if table exists)
            try {
                $this->savePlanHistory($parent, $newPlan, $investmentAmount, $oldPlanName);
            } catch (\Exception $e) {
                // If table doesn't exist yet, just log the error
                \Log::warning('Could not save plan history: ' . $e->getMessage());
            }
        }
    }

    /**
     * Get parent user from referral chain
     */
    protected function getParent($userId): ?User
    {
        // Use referrals table: find the active ref where referred_id = $userId
        $ref = Referral::where('referred_id', $userId)->where('status', 'active')->first();
        if (!$ref) {
            return null;
        }
        return User::find($ref->referrer_id);
    }

    /**
     * Credit parent wallet with bonus
     */
    protected function creditParentWallet($parentId, $amount): void
    {
        $wallet = Wallet::where('user_id', $parentId)->where('currency', 'USDT')->first();
        
        if (!$wallet) {
            // Create wallet row if missing
            Wallet::create([
                'user_id' => $parentId,
                'currency' => 'USDT',
                'balance' => $amount,
                'total_profit' => $amount,
                'total_deposited' => 0,
                'total_withdrawn' => 0,
                'total_loss' => 0,
            ]);
        } else {
            // Increment balance and profit totals
            $wallet->balance = bcadd($wallet->balance, $amount, 8);
            $wallet->total_profit = bcadd($wallet->total_profit, $amount, 8);
            $wallet->save();
        }
    }

    /**
     * Update referral totals in referrals table
     */
    protected function updateReferralTotals($parentId, $investorId, $bonusAmount): void
    {
        $ref = Referral::where('referrer_id', $parentId)
                      ->where('referred_id', $investorId)
                      ->first();
                      
        if ($ref) {
            $ref->total_commission = bcadd($ref->total_commission, $bonusAmount, 8);
            $ref->pending_commission = bcadd($ref->pending_commission, $bonusAmount, 8);
            $ref->save();
        }
    }
}
