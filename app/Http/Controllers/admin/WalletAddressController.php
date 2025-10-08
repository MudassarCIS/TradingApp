<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WalletAddress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables;
// use SimpleSoftwareIO\QrCode\Facades\QrCode;

class WalletAddressController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            try {
                $walletAddresses = WalletAddress::select('*');
                return DataTables::of($walletAddresses)
                    ->addIndexColumn()
                    ->addColumn('action', function ($walletAddress) {
                        $editBtn = '<a href="' . route('admin.wallet-addresses.edit', $walletAddress->id) . '" class="btn btn-sm btn-primary">Edit</a>';
                        $deleteBtn = '<form method="POST" action="' . route('admin.wallet-addresses.destroy', $walletAddress->id) . '" style="display:inline">
                            ' . csrf_field() . '
                            ' . method_field('DELETE') . '
                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm(\'Are you sure?\')">Delete</button>
                        </form>';
                        return $editBtn . ' ' . $deleteBtn;
                    })
                    ->addColumn('status', function ($walletAddress) {
                        return $walletAddress->is_active ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-danger">Inactive</span>';
                    })
                    ->addColumn('qr_code', function ($walletAddress) {
                        if ($walletAddress->qr_code_image) {
                            return '<img src="' . $walletAddress->qr_code_url . '" alt="QR Code" style="width: 50px; height: 50px;">';
                        }
                        return '<span class="text-muted">No QR Code</span>';
                    })
                    ->rawColumns(['action', 'status', 'qr_code'])
                    ->make(true);
            } catch (\Exception $e) {
                return response()->json(['error' => $e->getMessage()], 500);
            }
        }

        return view('admin.wallet-addresses.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.wallet-addresses.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'symbol' => 'required|string|max:10',
            'wallet_address' => 'required|string|max:255',
            'network' => 'nullable|string|max:50',
            'instructions' => 'nullable|string',
            'sort_order' => 'integer|min:0',
            'is_active' => 'boolean',
            'qr_code_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        $data = $request->all();

        // Handle QR code image upload
        if ($request->hasFile('qr_code_image')) {
            $data['qr_code_image'] = $request->file('qr_code_image')->store('qr-codes', 'public');
        }
        // Note: QR code auto-generation can be added later with QR code package

        WalletAddress::create($data);

        return redirect()->route('admin.wallet-addresses.index')
            ->with('success', 'Wallet address created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(WalletAddress $walletAddress)
    {
        return view('admin.wallet-addresses.show', compact('walletAddress'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(WalletAddress $walletAddress)
    {
        return view('admin.wallet-addresses.edit', compact('walletAddress'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, WalletAddress $walletAddress)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'symbol' => 'required|string|max:10',
            'wallet_address' => 'required|string|max:255',
            'network' => 'nullable|string|max:50',
            'instructions' => 'nullable|string',
            'sort_order' => 'integer|min:0',
            'is_active' => 'boolean',
            'qr_code_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        $data = $request->all();

        // Handle QR code image upload
        if ($request->hasFile('qr_code_image')) {
            // Delete old QR code image
            if ($walletAddress->qr_code_image) {
                Storage::disk('public')->delete($walletAddress->qr_code_image);
            }
            $data['qr_code_image'] = $request->file('qr_code_image')->store('qr-codes', 'public');
        } elseif ($request->has('regenerate_qr')) {
            // Regenerate QR code - can be implemented later with QR code package
            // For now, just remove the old QR code
            if ($walletAddress->qr_code_image) {
                Storage::disk('public')->delete($walletAddress->qr_code_image);
                $data['qr_code_image'] = null;
            }
        }

        $walletAddress->update($data);

        return redirect()->route('admin.wallet-addresses.index')
            ->with('success', 'Wallet address updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(WalletAddress $walletAddress)
    {
        // Delete QR code image if exists
        if ($walletAddress->qr_code_image) {
            Storage::disk('public')->delete($walletAddress->qr_code_image);
        }

        $walletAddress->delete();

        return redirect()->route('admin.wallet-addresses.index')
            ->with('success', 'Wallet address deleted successfully.');
    }
}
