<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Referral;
use App\Models\User;
use App\Models\CustomersWallet;
use App\Models\Deposit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class ReferralController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // Ensure user has a referral code
        if (!$user->referral_code) {
            $user->generateReferralCode();
        }
        
        // Get referral statistics
        $referralCount = $user->referredUsers()->count();
        
        // Get commission statistics from customers_wallet table
        // Total Commission Earned: Sum of all debit amounts for logged-in user
        $totalDebit = CustomersWallet::where('user_id', $user->id)
            ->where('transaction_type', 'debit')
            ->sum('amount') ?? 0;
        
        // Available Commission Balance: Total debit - Total credit (actual balance available for withdrawal)
        $totalCredit = CustomersWallet::where('user_id', $user->id)
            ->where('transaction_type', 'credit')
            ->sum('amount') ?? 0;
        
        $totalCommission = $totalDebit;
        $pendingCommission = max(0, $totalDebit - $totalCredit); // Ensure non-negative
        
        // Get referral tree (up to 3 levels)
        $referralTree = $this->buildReferralTree($user, 3);
        
        // Generate referral link and QR code
        $referralLink = route('register', ['ref' => $user->referral_code]);
        $qrCode = QrCode::size(150)->generate($referralLink);
        
        // Get referrals by level with pagination
        $level1Referrals = $this->getReferralsByLevel($user, 1, $request);
        $level2Referrals = $this->getReferralsByLevel($user, 2, $request);
        $level3Referrals = $this->getReferralsByLevel($user, 3, $request);
        
        // Get counts for each level
        $level1Count = $this->getReferralCountByLevel($user, 1);
        $level2Count = $this->getReferralCountByLevel($user, 2);
        $level3Count = $this->getReferralCountByLevel($user, 3);
        
        // Get total investment for each level from deposits table
        $level1TotalInvestment = $this->getTotalInvestmentByLevel($user, 1);
        $level2TotalInvestment = $this->getTotalInvestmentByLevel($user, 2);
        $level3TotalInvestment = $this->getTotalInvestmentByLevel($user, 3);
        
        // Get 3 levels of parents with bonus amounts
        $parents = $this->getParentsWithBonuses($user);
        
        return view('customer.referrals.index', compact(
            'referralCount',
            'totalCommission',
            'pendingCommission',
            'referralTree',
            'level1Referrals',
            'level2Referrals',
            'level3Referrals',
            'level1Count',
            'level2Count',
            'level3Count',
            'level1TotalInvestment',
            'level2TotalInvestment',
            'level3TotalInvestment',
            'referralLink',
            'qrCode',
            'parents'
        ));
    }
    
    public function tree()
    {
        $user = Auth::user();
        
        // Get referral tree (up to 3 levels deep)
        $referralTree = $this->buildReferralTree($user, 3);
        
        return view('customer.referrals.tree', compact('referralTree'));
    }
    
    protected function buildReferralTree(User $user, int $maxDepth, int $currentDepth = 0)
    {
        if ($currentDepth >= $maxDepth) {
            return null;
        }
        
        $referrals = $user->referredUsers()->with('profile')->orderBy('created_at', 'desc')->get();
        $tree = [
            'user' => $user,
            'referrals' => [],
            'level' => $currentDepth
        ];
        
        foreach ($referrals as $referral) {
            $tree['referrals'][] = $this->buildReferralTree($referral, $maxDepth, $currentDepth + 1);
        }
        
        return $tree;
    }
    
    protected function getAllReferrals(User $user, int $maxLevel = 3)
    {
        $allReferrals = [];
        $this->collectReferrals($user, $allReferrals, 1, $maxLevel);
        return $allReferrals;
    }
    
    protected function collectReferrals(User $user, array &$allReferrals, int $currentLevel, int $maxLevel)
    {
        if ($currentLevel > $maxLevel) {
            return;
        }
        
        $referrals = $user->referredUsers()->with(['wallets', 'activePlan'])->orderBy('created_at', 'desc')->get();
        
        foreach ($referrals as $referral) {
            // Get total investment amount from wallets
            $totalInvestment = $referral->wallets()->sum('total_deposited');
            
            $allReferrals[] = [
                'user' => $referral,
                'level' => $currentLevel,
                'total_investment' => $totalInvestment,
                'active_plan' => $referral->activePlan,
                'referral_code' => $referral->referral_code,
                'joined_at' => $referral->created_at
            ];
            
            // Recursively get referrals from this user
            $this->collectReferrals($referral, $allReferrals, $currentLevel + 1, $maxLevel);
        }
    }
    
    protected function getReferralsByLevel(User $user, int $level, Request $request)
    {
        $perPage = $request->get('per_page', 10);
        
        if ($level === 1) {
            // Direct referrals
            $query = $user->referredUsers()->with(['wallets', 'activePlan', 'profile'])->orderBy('created_at', 'desc');
        } else {
            // Get referrals at specific level using recursive approach
            $userIds = $this->getUserIdsAtLevel($user, $level);
            $query = User::whereIn('id', $userIds)->with(['wallets', 'activePlan', 'profile'])->orderBy('created_at', 'desc');
        }
        
        return $query->paginate($perPage)->appends($request->query());
    }
    
    protected function getReferralCountByLevel(User $user, int $level)
    {
        if ($level === 1) {
            return $user->referredUsers()->count();
        } else {
            $userIds = $this->getUserIdsAtLevel($user, $level);
            return User::whereIn('id', $userIds)->count();
        }
    }
    
    protected function getUserIdsAtLevel(User $user, int $targetLevel, int $currentLevel = 1)
    {
        // If we've reached the target level, return empty (we want users at this level, not the current user)
        if ($currentLevel > $targetLevel) {
            return [];
        }
        
        // If we're at the target level, we want the direct referrals of the current user
        if ($currentLevel === $targetLevel) {
            return $user->referredUsers()->pluck('id')->toArray();
        }
        
        // Otherwise, recursively get referrals from the next level
        $userIds = [];
        $referrals = $user->referredUsers;
        
        foreach ($referrals as $referral) {
            $userIds = array_merge($userIds, $this->getUserIdsAtLevel($referral, $targetLevel, $currentLevel + 1));
        }
        
        return $userIds;
    }
    
    /**
     * Get 3 levels of parents with their bonus wallet amounts
     * Bonuses are from current user's approved deposits and profits
     * Calculated from customers_wallet table using caused_by_user_id column
     */
    protected function getParentsWithBonuses(User $user): array
    {
        $parents = [
            'first' => null,
            'second' => null,
            'third' => null
        ];
        
        // Get first parent
        $firstParent = $this->getParent($user->id);
        if ($firstParent) {
            // Get bonuses caused by this user's approved deposits and profits
            // Only sum DEBIT transactions (credits to parent), exclude CREDIT transactions
            // Include both 'Deposit' (NEXA deposit bonuses) and 'Profit' (profit invoice bonuses)
            $firstParentBonus = CustomersWallet::where('user_id', $firstParent->id)
                ->whereIn('payment_type', ['Deposit', 'Profit'])
                ->where('transaction_type', 'debit') // Only debit transactions (money going to parent)
                ->where('caused_by_user_id', $user->id)
                ->sum('amount');
            
            $parents['first'] = [
                'user' => $firstParent,
                'bonus_amount' => (float) $firstParentBonus
            ];
            
            // Get second parent
            $secondParent = $this->getParent($firstParent->id);
            if ($secondParent) {
                // Get bonuses caused by this user's approved deposits and profits
                // Only sum DEBIT transactions (credits to parent), exclude CREDIT transactions
                $secondParentBonus = CustomersWallet::where('user_id', $secondParent->id)
                    ->whereIn('payment_type', ['Deposit', 'Profit'])
                    ->where('transaction_type', 'debit') // Only debit transactions (money going to parent)
                    ->where('caused_by_user_id', $user->id)
                    ->sum('amount');
                
                $parents['second'] = [
                    'user' => $secondParent,
                    'bonus_amount' => (float) $secondParentBonus
                ];
                
                // Get third parent
                $thirdParent = $this->getParent($secondParent->id);
                if ($thirdParent) {
                    // Get bonuses caused by this user's approved deposits and profits
                    // Only sum DEBIT transactions (credits to parent), exclude CREDIT transactions
                    $thirdParentBonus = CustomersWallet::where('user_id', $thirdParent->id)
                        ->whereIn('payment_type', ['Deposit', 'Profit'])
                        ->where('transaction_type', 'debit') // Only debit transactions (money going to parent)
                        ->where('caused_by_user_id', $user->id)
                        ->sum('amount');
                    
                    $parents['third'] = [
                        'user' => $thirdParent,
                        'bonus_amount' => (float) $thirdParentBonus
                    ];
                }
            }
        }
        
        return $parents;
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
     * Get total investment for all users at a specific level from deposits table
     * Only includes approved NEXA deposits
     */
    protected function getTotalInvestmentByLevel(User $user, int $level): float
    {
        $userIds = $this->getUserIdsAtLevel($user, $level);
        
        if (empty($userIds)) {
            return 0;
        }
        
        return (float) Deposit::whereIn('user_id', $userIds)
            ->where('status', 'approved')
            ->where('invoice_type', 'NEXA')
            ->sum('amount') ?? 0;
    }
    
    /**
     * Get total investment for a specific user from deposits table
     * Only includes approved NEXA deposits
     */
    protected function getUserTotalInvestment(User $user): float
    {
        return (float) Deposit::where('user_id', $user->id)
            ->where('status', 'approved')
            ->where('invoice_type', 'NEXA')
            ->sum('amount') ?? 0;
    }
}
