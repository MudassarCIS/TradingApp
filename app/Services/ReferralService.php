<?php

namespace App\Services;

use App\Models\User;
use App\Models\Referral;
use App\Models\Plan;
use App\Models\BonusWallet;
use App\Models\CustomersWallet;
use App\Models\Wallet;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;

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
     */
    public function assignPlanToUser(User $user, float $usdtAmount): ?Plan
    {
        // Try matching plan by exact amount field
        $plan = Plan::where('investment_amount', $usdtAmount)->first();
        
        if (!$plan) {
            // Fallback: plans use min_amount & max_amount
            $plan = Plan::where('min_amount', '<=', $usdtAmount)
                        ->where('max_amount', '>=', $usdtAmount)
                        ->first();
        }

        $user->active_investment_amount = $usdtAmount;
        $user->active_plan_id = $plan?->id;
        $user->save();

        return $plan;
    }

    /**
     * Distribute referral bonuses up to 3 levels
     */
    public function distributeReferralBonuses(User $investor, $deposit): void
    {
        // Check if bonuses have already been distributed for this deposit to prevent duplicates
        if ($deposit->id) {
            $existingBonus = BonusWallet::where('deposit_id', $deposit->id)->first();
            if ($existingBonus) {
                // Bonuses already distributed for this deposit
                return;
            }
        }
        
        $currency = $deposit->currency ?? 'USDT';
        $amount = (float) $deposit->amount;
        $amountInUSDT = $this->convertToUSDT($currency, $amount);
        
        // Assign plan to investor
        $this->assignPlanToUser($investor, $amountInUSDT);

        $level = 1;
        $parent = $this->getParent($investor->id);

        while ($level <= 3 && $parent) {
            $parentPlan = $parent->activePlan;
            $percentField = 'referral_level_' . $level;

            if ($parentPlan && isset($parentPlan->$percentField) && $parentPlan->$percentField > 0) {
                $percent = (float) $parentPlan->$percentField;
                $bonusUSDT = round($amountInUSDT * ($percent / 100), 8);

                DB::transaction(function () use ($deposit, $amountInUSDT, $investor, $parent, $level, $bonusUSDT, $parentPlan) {
                    // Insert into bonus_wallets
                    $bonusWallet = BonusWallet::create([
                        'deposit_id' => $deposit->id ?? null,
                        'investment_amount' => $amountInUSDT,
                        'user_id' => $investor->id,
                        'parent_id' => $parent->id,
                        'parent_level' => $level,
                        'bonus_amount' => $bonusUSDT,
                        'currency' => 'USDT',
                        'package_id' => $parentPlan->id,
                    ]);

                    // Insert into customers_wallets
                    CustomersWallet::create([
                        'user_id' => $parent->id,
                        'amount' => $bonusUSDT,
                        'currency' => 'USDT',
                        'payment_type' => 'bonus',
                        'transaction_type' => 'debit', // per your rule: bonus -> debit
                        'related_id' => $bonusWallet->id,
                    ]);

                    // Update parent's wallet summary
                    $this->creditParentWallet($parent->id, $bonusUSDT);

                    // Update referrals table totals
                    $this->updateReferralTotals($parent->id, $investor->id, $bonusUSDT);
                });
            }
            
            $level++;
            $parent = $this->getParent($parent->id);
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
