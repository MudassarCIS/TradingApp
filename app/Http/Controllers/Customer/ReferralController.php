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
        
        // Get referral statistics
        $referralCount = $user->referredUsers()->count();
        $totalCommission = $user->referrals()->sum('total_commission');
        $pendingCommission = $user->referrals()->sum('pending_commission');
        
        // Get referral tree
        $referrals = $user->referrals()->with('referred')->get();
        
        // Generate referral link and QR code
        $referralLink = route('register', ['ref' => $user->referral_code]);
        $qrCode = QrCode::size(200)->generate($referralLink);
        
        return view('customer.referrals.index', compact(
            'referralCount',
            'totalCommission',
            'pendingCommission',
            'referrals',
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
}
