<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SupportController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $messages = $user->messages()->latest()->paginate(10);
        
        return view('customer.support.index', compact('messages'));
    }
    
    public function store(Request $request)
    {
        $request->validate([
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
        ]);
        
        $user = Auth::user();
        
        $user->messages()->create([
            'subject' => $request->subject,
            'message' => $request->message,
            'status' => 'open',
        ]);
        
        return redirect()->route('customer.support.index')
            ->with('success', 'Support ticket created successfully!');
    }
}
