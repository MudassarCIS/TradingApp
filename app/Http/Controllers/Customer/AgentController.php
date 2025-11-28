<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Models\UserActiveBot;
use App\Models\Plan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AgentController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $agents = $user->agents()->latest()->paginate(10);
        
        // Get user's active bots with their invoice status
        $activeBots = $user->activeBots()
            ->with(['user' => function($query) {
                $query->with(['invoices' => function($q) {
                    $q->where('invoice_type', '!=', null);
                }]);
            }])
            ->latest()
            ->get()
            ->map(function($bot) use ($user) {
                // Get the corresponding invoice for this bot
                $invoice = $user->invoices()
                    ->where('invoice_type', $bot->buy_type)
                    ->where('created_at', '>=', $bot->created_at->subMinutes(5))
                    ->where('created_at', '<=', $bot->created_at->addMinutes(5))
                    ->first();
                
                $bot->invoice_status = $invoice ? $invoice->status : 'Unknown';
                $bot->invoice_amount = $invoice ? $invoice->amount : 0;
                $bot->invoice_due_date = $invoice ? $invoice->due_date : null;
                $bot->invoice_id = $invoice ? $invoice->id : null;
                
                return $bot;
            })
            ->sortByDesc(function($bot) {
                // Sort by created_at DESC (newest first)
                return $bot->created_at;
            })
            ->values();
        
        return view('customer.agents.index', compact('agents', 'activeBots'));
    }
    
    public function create()
    {
        return view('customer.agents.create');
    }
    
    public function store(Request $request)
    {
        // Check if this is the new bot selection flow
        if ($request->has('bot_type') && $request->has('plan_data')) {
            return $this->storeBotSelection($request);
        }

        // Original agent creation flow (keep for backward compatibility)
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
        
        return redirect()->route('customer.bots.index')
            ->with('success', 'AI Agent created successfully!');
    }

    private function storeBotSelection(Request $request)
    {
        $request->validate([
            'bot_type' => 'required|in:rent-bot,sharing-nexa',
            'plan_data' => 'required|array',
        ]);

        $user = Auth::user();
        $planData = $request->plan_data;
        $botType = $request->bot_type;

        try {
            \DB::beginTransaction();

            // Create user active bot record
            $activeBot = $user->activeBots()->create([
                'buy_type' => $botType === 'rent-bot' ? 'PEX' : 'NEXA',
                'buy_plan_details' => $planData,
            ]);

            // Create invoice
            $amount = $botType === 'rent-bot' ? $planData['amount'] : $planData['joining_fee'];
            $invoiceType = $botType === 'rent-bot' ? 'PEX' : 'NEXA';
            
            // Find plan_id if available in plan_data or by matching plan details
            $planId = null;
            if (isset($planData['id'])) {
                // If plan_id is directly in plan_data
                $planId = $planData['id'];
            } elseif ($botType === 'sharing-nexa' && isset($planData['investment_amount']) && isset($planData['joining_fee'])) {
                // For NEXA, try to find plan by matching investment_amount and joining_fee
                $plan = Plan::where('investment_amount', $planData['investment_amount'])
                    ->where('joining_fee', $planData['joining_fee'])
                    ->where('is_active', true)
                    ->first();
                if ($plan) {
                    $planId = $plan->id;
                }
            }
            
            $invoice = $user->invoices()->create([
                'plan_id' => $planId,
                'invoice_type' => $invoiceType,
                'amount' => $amount,
                'due_date' => now()->addDays(7),
                'status' => 'Unpaid',
            ]);

            \DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Bot plan created successfully! Invoice generated for payment.',
                'invoice_id' => $invoice->id,
            ]);

        } catch (\Exception $e) {
            \DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error saving bot selection: ' . $e->getMessage(),
            ], 500);
        }
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
        
        return redirect()->route('customer.bots.index')
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
        
        return redirect()->route('customer.bots.index')
            ->with('success', 'Agent deleted successfully!');
    }
    
    public function showPackage(UserActiveBot $bot)
    {
        $user = Auth::user();
        
        // Ensure the bot belongs to the authenticated user
        if ($bot->user_id !== $user->id) {
            abort(403, 'Unauthorized access to this package.');
        }
        
        // Get the corresponding invoice for this bot
        $invoice = $user->invoices()
            ->where('invoice_type', $bot->buy_type)
            ->where('created_at', '>=', $bot->created_at->subMinutes(5))
            ->where('created_at', '<=', $bot->created_at->addMinutes(5))
            ->first();
        
        $bot->invoice_status = $invoice ? $invoice->status : 'Unknown';
        $bot->invoice_amount = $invoice ? $invoice->amount : 0;
        $bot->invoice_due_date = $invoice ? $invoice->due_date : null;
        $bot->invoice_id = $invoice ? $invoice->id : null;
        
        return view('customer.agents.package-details', compact('bot'));
    }
}
