<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Referral;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class ReferralController extends Controller
{
    public function index()
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
        $qrCode = QrCode::size(200)->generate($referralLink);
        
        // Get all referrals with their investment data
        $allReferrals = $this->getAllReferrals($user, 3);
        
        return view('customer.referrals.index', compact(
            'referralCount',
            'totalCommission',
            'pendingCommission',
            'referralTree',
            'allReferrals',
            'referralLink',
            'qrCode'
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
}
