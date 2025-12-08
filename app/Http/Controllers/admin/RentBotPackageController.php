<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RentBotPackage;
use Illuminate\Http\Request;

class RentBotPackageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $packages = RentBotPackage::orderByDesc('id')->paginate(25);
        return view('admin.rent-bot-packages.index', compact('packages'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.rent-bot-packages.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'package_name' => 'nullable|string|max:255|unique:rent_bot_packages,package_name',
            'allowed_bots' => 'required|integer|min:0',
            'allowed_trades' => 'required|integer|min:0',
            'amount' => 'required|numeric|min:0',
            'validity' => 'required|in:month,year',
            'status' => 'required|in:0,1',
        ]);

        RentBotPackage::create($validated);

        return redirect()->route('admin.rent-bot-packages.index')
            ->with('success', 'PEX package created successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(RentBotPackage $rent_bot_package)
    {
        return view('admin.rent-bot-packages.edit', [
            'package' => $rent_bot_package,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, RentBotPackage $rent_bot_package)
    {
        $validated = $request->validate([
            'package_name' => 'nullable|string|max:255|unique:rent_bot_packages,package_name,' . $rent_bot_package->id,
            'allowed_bots' => 'required|integer|min:0',
            'allowed_trades' => 'required|integer|min:0',
            'amount' => 'required|numeric|min:0',
            'validity' => 'required|in:month,year',
            'status' => 'required|in:0,1',
        ]);

        $rent_bot_package->update($validated);

        return redirect()->route('admin.rent-bot-packages.index')
            ->with('success', 'PEX package updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(RentBotPackage $rent_bot_package)
    {
        $rent_bot_package->delete();

        return redirect()->route('admin.rent-bot-packages.index')
            ->with('success', 'PEX package deleted successfully.');
    }
}


