<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class MarketController extends Controller
{
    public function index()
    {
        // Popular trading pairs
        $popularPairs = [
            'BTCUSDT' => ['symbol' => 'BTCUSDT', 'price' => 45000, 'change' => 2.5],
            'ETHUSDT' => ['symbol' => 'ETHUSDT', 'price' => 3000, 'change' => -1.2],
            'BNBUSDT' => ['symbol' => 'BNBUSDT', 'price' => 350, 'change' => 0.8],
            'ADAUSDT' => ['symbol' => 'ADAUSDT', 'price' => 0.45, 'change' => 3.1],
        ];
        
        return view('customer.market.index', compact('popularPairs'));
    }
}
