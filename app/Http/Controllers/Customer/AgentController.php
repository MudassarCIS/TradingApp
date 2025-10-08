<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Agent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AgentController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $agents = $user->agents()->latest()->paginate(10);
        
        return view('customer.agents.index', compact('agents'));
    }
    
    public function create()
    {
        return view('customer.agents.create');
    }
    
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'strategy' => 'required|string',
            'risk_level' => 'required|in:low,medium,high',
            'initial_balance' => 'required|numeric|min:0',
        ]);
        
        $user = Auth::user();
        $wallet = $user->getMainWallet('USDT');
        
        if ($wallet->balance < $request->initial_balance) {
            return back()->withErrors(['initial_balance' => 'Insufficient balance']);
        }
        
        $agent = $user->agents()->create([
            'name' => $request->name,
            'strategy' => $request->strategy,
            'risk_level' => $request->risk_level,
            'initial_balance' => $request->initial_balance,
            'current_balance' => $request->initial_balance,
            'status' => 'active',
        ]);
        
        // Deduct from wallet
        $wallet->decrement('balance', $request->initial_balance);
        
        return redirect()->route('customer.agents.index')
            ->with('success', 'AI Agent created successfully!');
    }
    
    public function show(Agent $agent)
    {
        $this->authorize('view', $agent);
        
        $trades = $agent->trades()->latest()->paginate(10);
        
        return view('customer.agents.show', compact('agent', 'trades'));
    }
    
    public function edit(Agent $agent)
    {
        $this->authorize('update', $agent);
        
        return view('customer.agents.edit', compact('agent'));
    }
    
    public function update(Request $request, Agent $agent)
    {
        $this->authorize('update', $agent);
        
        $request->validate([
            'name' => 'required|string|max:255',
            'strategy' => 'required|string',
            'risk_level' => 'required|in:low,medium,high',
        ]);
        
        $agent->update($request->only(['name', 'strategy', 'risk_level']));
        
        return redirect()->route('customer.agents.index')
            ->with('success', 'Agent updated successfully!');
    }
    
    public function destroy(Agent $agent)
    {
        $this->authorize('delete', $agent);
        
        // Return balance to wallet
        $user = Auth::user();
        $wallet = $user->getMainWallet('USDT');
        $wallet->increment('balance', $agent->current_balance);
        
        $agent->delete();
        
        return redirect()->route('customer.agents.index')
            ->with('success', 'Agent deleted successfully!');
    }
}
