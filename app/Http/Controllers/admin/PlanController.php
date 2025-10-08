<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class PlanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $plans = Plan::select('*');
            return DataTables::of($plans)
                ->addIndexColumn()
                ->addColumn('action', function ($plan) {
                    $editBtn = '<a href="' . route('admin.plans.edit', $plan->id) . '" class="btn btn-sm btn-primary">Edit</a>';
                    $deleteBtn = '<form method="POST" action="' . route('admin.plans.destroy', $plan->id) . '" style="display:inline">
                        ' . csrf_field() . '
                        ' . method_field('DELETE') . '
                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm(\'Are you sure?\')">Delete</button>
                    </form>';
                    return $editBtn . ' ' . $deleteBtn;
                })
                ->addColumn('status', function ($plan) {
                    return $plan->is_active ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-danger">Inactive</span>';
                })
                ->addColumn('investment_amount', function ($plan) {
                    return '$' . number_format($plan->investment_amount, 2);
                })
                ->addColumn('joining_fee', function ($plan) {
                    return '$' . number_format($plan->joining_fee, 2);
                })
                ->addColumn('direct_bonus', function ($plan) {
                    return '$' . number_format($plan->direct_bonus, 2);
                })
                ->rawColumns(['action', 'status'])
                ->make(true);
        }

        return view('admin.plans.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.plans.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'investment_amount' => 'required|numeric|min:0',
            'joining_fee' => 'required|numeric|min:0',
            'bots_allowed' => 'required|integer|min:1',
            'trades_per_day' => 'required|integer|min:1',
            'direct_bonus' => 'required|numeric|min:0',
            'referral_level_1' => 'required|numeric|min:0|max:100',
            'referral_level_2' => 'required|numeric|min:0|max:100',
            'referral_level_3' => 'required|numeric|min:0|max:100',
            'sort_order' => 'integer|min:0',
            'description' => 'nullable|string',
            'is_active' => 'boolean'
        ]);

        Plan::create($request->all());

        return redirect()->route('admin.plans.index')
            ->with('success', 'Plan created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Plan $plan)
    {
        return view('admin.plans.show', compact('plan'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Plan $plan)
    {
        return view('admin.plans.edit', compact('plan'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Plan $plan)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'investment_amount' => 'required|numeric|min:0',
            'joining_fee' => 'required|numeric|min:0',
            'bots_allowed' => 'required|integer|min:1',
            'trades_per_day' => 'required|integer|min:1',
            'direct_bonus' => 'required|numeric|min:0',
            'referral_level_1' => 'required|numeric|min:0|max:100',
            'referral_level_2' => 'required|numeric|min:0|max:100',
            'referral_level_3' => 'required|numeric|min:0|max:100',
            'sort_order' => 'integer|min:0',
            'description' => 'nullable|string',
            'is_active' => 'boolean'
        ]);

        $plan->update($request->all());

        return redirect()->route('admin.plans.index')
            ->with('success', 'Plan updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Plan $plan)
    {
        $plan->delete();

        return redirect()->route('admin.plans.index')
            ->with('success', 'Plan deleted successfully.');
    }
}
