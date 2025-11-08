<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Referral;
use App\Models\User;
use App\Models\CustomersWallet;
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
        $totalCommission = $user->referrals()->sum('total_commission');
        $pendingCommission = $user->referrals()->sum('pending_commission');
        
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
        
        $referrals = $user->referredUsers()->with('profile')->get();
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
        
        $referrals = $user->referredUsers()->with(['wallets', 'activePlan'])->get();
        
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
            $query = $user->referredUsers()->with(['wallets', 'activePlan', 'profile']);
        } else {
            // Get referrals at specific level using recursive approach
            $userIds = $this->getUserIdsAtLevel($user, $level);
            $query = User::whereIn('id', $userIds)->with(['wallets', 'activePlan', 'profile']);
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
        if ($currentLevel >= $targetLevel) {
            return [$user->id];
        }
        
        $userIds = [];
        $referrals = $user->referredUsers;
        
        foreach ($referrals as $referral) {
            if ($currentLevel + 1 === $targetLevel) {
                $userIds[] = $referral->id;
            } else {
                $userIds = array_merge($userIds, $this->getUserIdsAtLevel($referral, $targetLevel, $currentLevel + 1));
            }
        }
        
        return $userIds;
    }
    
    /**
     * Get 3 levels of parents with their bonus wallet amounts
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
            $firstParentBonus = CustomersWallet::where('user_id', $firstParent->id)
                ->where('payment_type', 'bonus')
                ->where('transaction_type', 'debit')
                ->sum('amount');
            
            $parents['first'] = [
                'user' => $firstParent,
                'bonus_amount' => (float) $firstParentBonus
            ];
            
            // Get second parent
            $secondParent = $this->getParent($firstParent->id);
            if ($secondParent) {
                $secondParentBonus = CustomersWallet::where('user_id', $secondParent->id)
                    ->where('payment_type', 'bonus')
                    ->where('transaction_type', 'debit')
                    ->sum('amount');
                
                $parents['second'] = [
                    'user' => $secondParent,
                    'bonus_amount' => (float) $secondParentBonus
                ];
                
                // Get third parent
                $thirdParent = $this->getParent($secondParent->id);
                if ($thirdParent) {
                    $thirdParentBonus = CustomersWallet::where('user_id', $thirdParent->id)
                        ->where('payment_type', 'bonus')
                        ->where('transaction_type', 'debit')
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
}
