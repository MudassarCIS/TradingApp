<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Trade;
use App\Models\Agent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TradingController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        $trades = $user->trades()
            ->with('agent')
            ->latest()
            ->paginate(20);
            
        $agents = $user->agents()->where('status', 'active')->get();
        
        // Get active packages (with paid invoices)
        $activePackages = $user->getActivePackages();
        
        return view('customer.trading.index', compact('trades', 'agents', 'activePackages'));
    }
}
