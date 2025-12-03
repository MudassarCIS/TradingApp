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
            // Get invoice type if deposit is associated with an invoice
            $invoiceType = null;
            if ($deposit->invoice_id && $deposit->relationLoaded('invoice')) {
                $invoice = $deposit->invoice;
                $invoiceType = $invoice->invoice_type ?? null;
            } elseif ($deposit->invoice_id) {
                $invoice = \App\Models\UserInvoice::find($deposit->invoice_id);
                $invoiceType = $invoice->invoice_type ?? null;
            }
            
            \Log::info('distributeReferralBonuses called', [
                'deposit_id' => $deposit->id ?? null,
                'user_id' => $investor->id,
                'invoice_id' => $deposit->invoice_id ?? null,
                'invoice_type' => $invoiceType ?? 'none'
            ]);
            
            // Route to appropriate bonus distribution method based on invoice type
            // "NEXA" and "package buy" are treated as package buy (direct_bonus to level 1 only)
            // "NEXA Profit" uses 3-level bonuses based on parent's active plan referral levels
            // "PEX" deposits do NOT trigger any bonus distribution
            // "profit invoice" uses 3-level bonuses
            if ($invoiceType === 'NEXA Profit') {
                // Type 2: NEXA Profit - give 3-level bonuses using parent's active plan referral_level percentages
                \Log::info('Routing to distributeNexaProfitBonus for NEXA Profit', [
                    'deposit_id' => $deposit->id ?? null,
                    'user_id' => $investor->id
                ]);
                $this->distributeNexaProfitBonus($investor, $deposit);
            } elseif (in_array($invoiceType, ['NEXA', 'package buy'])) {
                // Type 1: Package buy (NEXA or package buy) - give direct_bonus to first level parent only
                // Note: PEX is excluded - no bonus for PEX deposits
                \Log::info('Routing to distributePackageBuyBonus for NEXA/package buy', [
                    'deposit_id' => $deposit->id ?? null,
                    'user_id' => $investor->id,
                    'invoice_type' => $invoiceType
                ]);
                $this->distributePackageBuyBonus($investor, $deposit);
            } elseif ($invoiceType === 'profit invoice') {
                // Type 3: Profit invoice - give 3-level bonuses using referral_level percentages
                \Log::info('Routing to distributeProfitInvoiceBonus for profit invoice', [
                    'deposit_id' => $deposit->id ?? null,
                    'user_id' => $investor->id
                ]);
                $this->distributeProfitInvoiceBonus($investor, $deposit);
            } elseif ($invoiceType === 'PEX') {
                // PEX deposits - explicitly skip bonus distribution
                \Log::info('PEX deposit - skipping bonus distribution in ReferralService', [
                    'deposit_id' => $deposit->id ?? null,
                    'user_id' => $investor->id,
                    'invoice_type' => $invoiceType
                ]);
                // Do nothing - return without distributing any bonus
                return;
            } else {
                // Default: For deposits without invoice or unknown type, use package buy logic
                // This treats deposits without invoices as package purchases
                // Note: PEX is explicitly excluded above
                \Log::warning('Unknown invoice type, defaulting to distributePackageBuyBonus', [
                    'deposit_id' => $deposit->id ?? null,
                    'user_id' => $investor->id,
                    'invoice_type' => $invoiceType ?? 'none'
                ]);
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
     * Used when invoice_type is "PEX", "NEXA", or "package buy"
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
            
            \Log::info('Parent found for package buy bonus', [
                'parent_id' => $parent->id,
                'parent_name' => $parent->name,
                'investor_id' => $investor->id,
                'deposit_id' => $deposit->id
            ]);
            
            // Get parent's active plan to check if they qualify
            $parentActivePlan = $this->getParentActivePlan($parent);
            
            if ($parentActivePlan) {
                \Log::info('Parent active plan retrieved for package buy bonus', [
                    'parent_id' => $parent->id,
                    'plan_id' => $parentActivePlan->id,
                    'plan_name' => $parentActivePlan->name,
                    'direct_bonus' => $directBonus
                ]);
                
                // Convert direct_bonus to USDT if needed (assuming direct_bonus is in USDT)
                $bonusUSDT = $directBonus;
                
                if ($bonusUSDT > 0) {
                    DB::transaction(function () use ($deposit, $investor, $parent, $bonusUSDT, $directBonus, $parentActivePlan) {
                        \Log::info('Creating customers_wallets entry for package buy bonus', [
                            'parent_id' => $parent->id,
                            'bonus_amount' => $bonusUSDT,
                            'deposit_id' => $deposit->id,
                            'investor_id' => $investor->id
                        ]);
                        
                        // Insert into customers_wallets with deposit id as related_id
                        $walletEntry = CustomersWallet::create([
                            'user_id' => $parent->id,
                            'amount' => $bonusUSDT,
                            'currency' => 'USDT',
                            'payment_type' => 'Deposit',
                            'transaction_type' => 'debit',
                            'related_id' => $deposit->id,
                            'caused_by_user_id' => $investor->id, // User who invested/bought plan
                        ]);

                        \Log::info('Customers_wallets entry created for package buy bonus', [
                            'wallet_entry_id' => $walletEntry->id,
                            'parent_id' => $parent->id,
                            'bonus_amount' => $bonusUSDT
                        ]);

                        // Update parent's wallet summary
                        $this->creditParentWallet($parent->id, $bonusUSDT);

                        // Update referral totals
                        $this->updateReferralTotals($parent->id, $investor->id, $bonusUSDT);
                        
                        // Log bonus distribution for verification
                        \Log::info('Package buy bonus distributed successfully', [
                            'level' => 1,
                            'parent_id' => $parent->id,
                            'parent_plan_id' => $parentActivePlan->id,
                            'parent_plan_name' => $parentActivePlan->name,
                            'investor_id' => $investor->id,
                            'deposit_id' => $deposit->id,
                            'bonus_amount' => $bonusUSDT,
                            'direct_bonus' => $directBonus,
                            'wallet_entry_id' => $walletEntry->id
                        ]);
                    });
                } else {
                    \Log::warning('Package buy bonus amount is 0 or negative', [
                        'parent_id' => $parent->id,
                        'bonus_amount' => $bonusUSDT
                    ]);
                }
            } else {
                \Log::warning('Parent has no active plan for package buy bonus', [
                    'parent_id' => $parent->id,
                    'investor_id' => $investor->id
                ]);
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
            \Log::info('Starting profit invoice bonus distribution', [
                'investor_id' => $investor->id,
                'deposit_id' => $deposit->id,
                'amount' => $amount,
                'amount_in_usdt' => $amountInUSDT
            ]);
            
            $level = 1;
            $parent = $this->getParent($investor->id);

            while ($level <= 3 && $parent) {
                try {
                    \Log::info('Processing profit invoice bonus for level', [
                        'level' => $level,
                        'parent_id' => $parent->id,
                        'parent_name' => $parent->name,
                        'investor_id' => $investor->id
                    ]);
                    
                    // Check if parent has an active plan with paid invoice
                    $parentActivePlan = $this->getParentActivePlan($parent);
                    
                    if ($parentActivePlan) {
                        $percentField = 'referral_level_' . $level;
                        $percent = (float) ($parentActivePlan->$percentField ?? 0);
                        
                        \Log::info('Parent plan details for profit invoice bonus', [
                            'level' => $level,
                            'parent_id' => $parent->id,
                            'plan_id' => $parentActivePlan->id,
                            'plan_name' => $parentActivePlan->name,
                            'percent_field' => $percentField,
                            'percent' => $percent
                        ]);

                        if ($percent > 0) {
                            $bonusUSDT = round($amountInUSDT * ($percent / 100), 8);
                            
                            \Log::info('Calculated profit invoice bonus', [
                                'level' => $level,
                                'parent_id' => $parent->id,
                                'amount_in_usdt' => $amountInUSDT,
                                'percent' => $percent,
                                'bonus_amount' => $bonusUSDT
                            ]);

                            if ($bonusUSDT > 0) {
                                DB::transaction(function () use ($deposit, $investor, $parent, $level, $bonusUSDT, $percent, $parentActivePlan) {
                                    \Log::info('Creating customers_wallets entry for profit invoice bonus', [
                                        'level' => $level,
                                        'parent_id' => $parent->id,
                                        'bonus_amount' => $bonusUSDT,
                                        'deposit_id' => $deposit->id
                                    ]);
                                    
                                    // Insert into customers_wallets with deposit id as related_id
                                    $walletEntry = CustomersWallet::create([
                                        'user_id' => $parent->id,
                                        'amount' => $bonusUSDT,
                                        'currency' => 'USDT',
                                        'payment_type' => 'Profit',
                                        'transaction_type' => 'debit',
                                        'related_id' => $deposit->id,
                                        'caused_by_user_id' => $investor->id, // User who got profit
                                    ]);

                                    \Log::info('Customers_wallets entry created for profit invoice bonus', [
                                        'wallet_entry_id' => $walletEntry->id,
                                        'level' => $level,
                                        'parent_id' => $parent->id
                                    ]);

                                    // Update parent's wallet summary
                                    $this->creditParentWallet($parent->id, $bonusUSDT);

                                    // Update referral totals
                                    $this->updateReferralTotals($parent->id, $investor->id, $bonusUSDT);
                                    
                                    // Log bonus distribution for verification
                                    \Log::info('Profit invoice bonus distributed successfully', [
                                        'level' => $level,
                                        'parent_id' => $parent->id,
                                        'parent_plan_id' => $parentActivePlan->id,
                                        'parent_plan_name' => $parentActivePlan->name,
                                        'investor_id' => $investor->id,
                                        'deposit_id' => $deposit->id,
                                        'bonus_amount' => $bonusUSDT,
                                        'percent' => $percent,
                                        'wallet_entry_id' => $walletEntry->id
                                    ]);
                                });
                            } else {
                                \Log::info('Profit invoice bonus calculated as 0, skipping', [
                                    'level' => $level,
                                    'parent_id' => $parent->id,
                                    'percent' => $percent
                                ]);
                            }
                        } else {
                            \Log::info('Parent has no referral percentage for this level', [
                                'level' => $level,
                                'parent_id' => $parent->id,
                                'plan_id' => $parentActivePlan->id
                            ]);
                        }
                    } else {
                        \Log::info('Parent has no active plan for profit invoice bonus', [
                            'level' => $level,
                            'parent_id' => $parent->id
                        ]);
                    }
                } catch (\Exception $e) {
                    // Log error for this level but continue to next level
                    \Log::error("Error processing profit invoice bonus for level {$level}: " . $e->getMessage(), [
                        'parent_id' => $parent->id ?? null,
                        'investor_id' => $investor->id,
                        'level' => $level,
                        'trace' => $e->getTraceAsString()
                    ]);
                }
                
                $level++;
                $parent = $this->getParent($parent->id);
            }
            
            \Log::info('Completed profit invoice bonus distribution', [
                'investor_id' => $investor->id,
                'deposit_id' => $deposit->id,
                'levels_processed' => $level - 1
            ]);
        } catch (\Exception $e) {
            \Log::error('Error in distributeProfitInvoiceBonus (parent loop): ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Distribute NEXA Profit bonus - gives 3-level bonuses using parent's active plan referral_level percentages
     * Used when invoice_type is "NEXA Profit"
     */
    public function distributeNexaProfitBonus(User $investor, $deposit): void
    {
        try {
            // Check if bonuses have already been distributed for this deposit to prevent duplicates
            // Check for any wallet entries with this deposit ID and Profit payment_type
            if ($deposit->id) {
                $existingBonus = CustomersWallet::where('related_id', $deposit->id)
                    ->where('payment_type', 'Profit')
                    ->where('caused_by_user_id', $investor->id)
                    ->first();
                if ($existingBonus) {
                    // Bonuses already distributed for this deposit
                    \Log::info('NEXA Profit bonuses already distributed for this deposit', [
                        'deposit_id' => $deposit->id,
                        'user_id' => $investor->id,
                        'existing_wallet_entry_id' => $existingBonus->id
                    ]);
                    return;
                }
            }
            
            $currency = $deposit->currency ?? 'USDT';
            $amount = (float) $deposit->amount;
            $amountInUSDT = $this->convertToUSDT($currency, $amount);
            
            \Log::info('Starting NEXA Profit bonus distribution', [
                'deposit_id' => $deposit->id,
                'investor_id' => $investor->id,
                'amount' => $amount,
                'amount_in_usdt' => $amountInUSDT
            ]);
        } catch (\Exception $e) {
            \Log::error('Error in distributeNexaProfitBonus (initial setup): ' . $e->getMessage(), [
                'deposit_id' => $deposit->id ?? null,
                'user_id' => $investor->id
            ]);
            throw $e;
        }

        try {
            $level = 1;
            $parent = $this->getParent($investor->id);

            while ($level <= 3 && $parent) {
                try {
                    \Log::info('Processing NEXA Profit bonus for level', [
                        'level' => $level,
                        'parent_id' => $parent->id,
                        'parent_name' => $parent->name,
                        'investor_id' => $investor->id,
                        'parent_active_plan_id' => $parent->active_plan_id
                    ]);
                    
                    // Get parent's active plan directly from active_plan_id
                    $parentActivePlan = null;
                    if ($parent->active_plan_id) {
                        $parentActivePlan = Plan::find($parent->active_plan_id);
                    }
                    
                    if ($parentActivePlan) {
                        $percentField = 'referral_level_' . $level;
                        $percent = (float) ($parentActivePlan->$percentField ?? 0);
                        
                        \Log::info('Parent plan details for NEXA Profit bonus', [
                            'level' => $level,
                            'parent_id' => $parent->id,
                            'plan_id' => $parentActivePlan->id,
                            'plan_name' => $parentActivePlan->name,
                            'percent_field' => $percentField,
                            'percent' => $percent
                        ]);

                        if ($percent > 0) {
                            $bonusUSDT = round($amountInUSDT * ($percent / 100), 8);
                            
                            \Log::info('Calculated NEXA Profit bonus', [
                                'level' => $level,
                                'parent_id' => $parent->id,
                                'amount_in_usdt' => $amountInUSDT,
                                'percent' => $percent,
                                'bonus_amount' => $bonusUSDT
                            ]);

                            if ($bonusUSDT > 0) {
                                DB::transaction(function () use ($deposit, $investor, $parent, $level, $bonusUSDT, $percent, $parentActivePlan) {
                                    \Log::info('Creating customers_wallets entry for NEXA Profit bonus', [
                                        'level' => $level,
                                        'parent_id' => $parent->id,
                                        'bonus_amount' => $bonusUSDT,
                                        'deposit_id' => $deposit->id
                                    ]);
                                    
                                    // Insert into customers_wallets with deposit id as related_id
                                    $walletEntry = CustomersWallet::create([
                                        'user_id' => $parent->id,
                                        'amount' => $bonusUSDT,
                                        'currency' => 'USDT',
                                        'payment_type' => 'Profit',
                                        'transaction_type' => 'debit',
                                        'related_id' => $deposit->id,
                                        'caused_by_user_id' => $investor->id, // User who created the deposit
                                    ]);

                                    \Log::info('Customers_wallets entry created for NEXA Profit bonus', [
                                        'wallet_entry_id' => $walletEntry->id,
                                        'level' => $level,
                                        'parent_id' => $parent->id
                                    ]);

                                    // Update parent's wallet summary
                                    $this->creditParentWallet($parent->id, $bonusUSDT);

                                    // Update referral totals
                                    $this->updateReferralTotals($parent->id, $investor->id, $bonusUSDT);
                                    
                                    // Log bonus distribution for verification
                                    \Log::info('NEXA Profit bonus distributed successfully', [
                                        'level' => $level,
                                        'parent_id' => $parent->id,
                                        'parent_plan_id' => $parentActivePlan->id,
                                        'parent_plan_name' => $parentActivePlan->name,
                                        'investor_id' => $investor->id,
                                        'deposit_id' => $deposit->id,
                                        'bonus_amount' => $bonusUSDT,
                                        'percent' => $percent,
                                        'wallet_entry_id' => $walletEntry->id
                                    ]);
                                });
                            } else {
                                \Log::info('NEXA Profit bonus calculated as 0, skipping', [
                                    'level' => $level,
                                    'parent_id' => $parent->id,
                                    'percent' => $percent
                                ]);
                            }
                        } else {
                            \Log::info('Parent has no referral percentage for this level', [
                                'level' => $level,
                                'parent_id' => $parent->id,
                                'plan_id' => $parentActivePlan->id
                            ]);
                        }
                    } else {
                        \Log::info('Parent has no active plan, skipping NEXA Profit bonus', [
                            'level' => $level,
                            'parent_id' => $parent->id,
                            'parent_active_plan_id' => $parent->active_plan_id
                        ]);
                    }
                } catch (\Exception $e) {
                    // Log error for this level but continue to next level
                    \Log::error("Error processing NEXA Profit bonus for level {$level}: " . $e->getMessage(), [
                        'parent_id' => $parent->id ?? null,
                        'investor_id' => $investor->id,
                        'level' => $level,
                        'trace' => $e->getTraceAsString()
                    ]);
                }
                
                $level++;
                $parent = $this->getParent($parent->id);
            }
            
            \Log::info('Completed NEXA Profit bonus distribution', [
                'investor_id' => $investor->id,
                'deposit_id' => $deposit->id,
                'levels_processed' => $level - 1
            ]);
        } catch (\Exception $e) {
            \Log::error('Error in distributeNexaProfitBonus (parent loop): ' . $e->getMessage(), [
                'deposit_id' => $deposit->id ?? null,
                'investor_id' => $investor->id,
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Get parent's active plan (NEXA with paid invoice)
     * Determines plan based on joining_fee amount from plans table
     * If parent has no payment, returns Starter plan as default
     */
    protected function getParentActivePlan(User $parent): ?Plan
    {
        // Get active NEXA bots with paid invoices
        $activeBots = $parent->activeBots()
            ->where('buy_type', 'NEXA')
            ->latest()
            ->get();

        foreach ($activeBots as $bot) {
            // Check if there's a paid invoice matching this bot
            $botCreatedAt = $bot->created_at;
            $startTime = $botCreatedAt->copy()->subMinutes(10);
            $endTime = $botCreatedAt->copy()->addMinutes(10);
            
            $invoice = $parent->invoices()
                ->where('invoice_type', 'NEXA')
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
        
        // If parent has no active NEXA plan with paid invoice, 
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
        $oldBalance = $wallet ? $wallet->balance : 0;
        $oldTotalProfit = $wallet ? $wallet->total_profit : 0;
        
        if (!$wallet) {
            // Create wallet row if missing
            $wallet = Wallet::create([
                'user_id' => $parentId,
                'currency' => 'USDT',
                'balance' => $amount,
                'total_profit' => $amount,
                'total_deposited' => 0,
                'total_withdrawn' => 0,
                'total_loss' => 0,
            ]);
            \Log::info('Created new wallet for parent bonus', [
                'parent_id' => $parentId,
                'bonus_amount' => $amount,
                'new_balance' => $amount,
                'new_total_profit' => $amount
            ]);
        } else {
            // Increment balance and profit totals
            $wallet->balance = bcadd($wallet->balance, $amount, 8);
            $wallet->total_profit = bcadd($wallet->total_profit, $amount, 8);
            $wallet->save();
            \Log::info('Updated parent wallet with bonus', [
                'parent_id' => $parentId,
                'bonus_amount' => $amount,
                'old_balance' => $oldBalance,
                'new_balance' => $wallet->balance,
                'old_total_profit' => $oldTotalProfit,
                'new_total_profit' => $wallet->total_profit
            ]);
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
            $oldTotalCommission = $ref->total_commission;
            $oldPendingCommission = $ref->pending_commission;
            $ref->total_commission = bcadd($ref->total_commission, $bonusAmount, 8);
            $ref->pending_commission = bcadd($ref->pending_commission, $bonusAmount, 8);
            $ref->save();
            \Log::info('Updated referral totals', [
                'parent_id' => $parentId,
                'investor_id' => $investorId,
                'bonus_amount' => $bonusAmount,
                'old_total_commission' => $oldTotalCommission,
                'new_total_commission' => $ref->total_commission,
                'old_pending_commission' => $oldPendingCommission,
                'new_pending_commission' => $ref->pending_commission
            ]);
        } else {
            \Log::warning('Referral record not found for updating totals', [
                'parent_id' => $parentId,
                'investor_id' => $investorId,
                'bonus_amount' => $bonusAmount
            ]);
        }
    }
}
