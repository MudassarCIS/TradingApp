<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UserInvoice;
use App\Models\RentBotPackage;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class InvoiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            try {
                // Get all active PEX packages ordered by amount ASC for plan name matching
                $pexPackages = RentBotPackage::active()->orderBy('amount', 'asc')->get();
                
                $invoices = UserInvoice::with(['user', 'plan', 'user.activeBots'])->select('*');
                return DataTables::of($invoices)
                    ->addIndexColumn()
                    ->addColumn('formatted_invoice_id', function ($invoice) {
                        return $invoice->formatted_invoice_id;
                    })
                    ->addColumn('user_name', function ($invoice) {
                        return $invoice->user ? $invoice->user->name : 'N/A';
                    })
                    ->addColumn('user_email', function ($invoice) {
                        return $invoice->user ? $invoice->user->email : 'N/A';
                    })
                    ->addColumn('invoice_type', function ($invoice) use ($pexPackages) {
                        $type = $invoice->invoice_type;
                        $planName = null;
                        
                        if ($type === 'PEX') {
                            // For PEX, match invoice amount with rent_bot_packages and assign name based on order
                            $invoiceAmount = (float) $invoice->amount;
                            $matchedPackage = $pexPackages->first(function ($package) use ($invoiceAmount) {
                                return abs((float) $package->amount - $invoiceAmount) < 0.01; // Allow small floating point differences
                            });
                            
                            if ($matchedPackage) {
                                // Find the index of the matched package (0-based)
                                $index = $pexPackages->search(function ($package) use ($matchedPackage) {
                                    return $package->id === $matchedPackage->id;
                                });
                                // Assign name: PEX-1, PEX-2, etc. (index + 1)
                                // search() returns false if not found, so check for that
                                if ($index !== false) {
                                    $planName = 'PEX-' . ($index + 1);
                                }
                            }
                        } else {
                            // For NEXA, try to get plan name from invoice's plan relationship
                            if ($invoice->plan) {
                                $planName = $invoice->plan->name;
                            } else {
                                // If no plan_id, try to get plan name from activeBots buy_plan_details
                                $user = $invoice->user;
                                if ($user && $type) {
                                    // Find activeBot created around the same time as invoice (within 10 minutes)
                                    $invoiceCreatedAt = $invoice->created_at;
                                    $startTime = $invoiceCreatedAt->copy()->subMinutes(10);
                                    $endTime = $invoiceCreatedAt->copy()->addMinutes(10);
                                    
                                    $activeBot = $user->activeBots
                                        ->where('buy_type', $type)
                                        ->where('created_at', '>=', $startTime)
                                        ->where('created_at', '<=', $endTime)
                                        ->first();
                                    
                                    if ($activeBot && $activeBot->buy_plan_details) {
                                        $planDetails = $activeBot->buy_plan_details;
                                        $planName = $planDetails['name'] ?? null;
                                    }
                                }
                            }
                        }
                        
                        // Only show plan name in brackets for PEX and NEXA types
                        if ($planName && in_array($type, ['PEX', 'NEXA'])) {
                            return $type . ' <span class="text-muted">(' . htmlspecialchars($planName) . ')</span>';
                        }
                        return $type;
                    })
                    ->addColumn('formatted_amount', function ($invoice) {
                        return '$' . number_format($invoice->amount, 2) . ' USDT';
                    })
                    ->addColumn('formatted_due_date', function ($invoice) {
                        return $invoice->due_date->format('d/m/Y');
                    })
                    ->addColumn('formatted_created_at', function ($invoice) {
                        return $invoice->created_at->format('d/m/Y H:i');
                    })
                    ->addColumn('status_badge', function ($invoice) {
                        $badgeClass = $invoice->status === 'Paid' ? 'success' : ($invoice->status === 'Unpaid' ? 'warning' : 'danger');
                        return '<span class="badge bg-' . $badgeClass . '">' . $invoice->status . '</span>';
                    })
                    ->addColumn('action', function ($invoice) {
                        $viewBtn = '<a href="' . route('admin.invoices.show', $invoice->id) . '" class="btn btn-sm btn-info" title="View Details">
                            <i class="fas fa-eye"></i> View
                        </a>';
                        return $viewBtn;
                    })
                    ->rawColumns(['status_badge', 'action', 'invoice_type'])
                    ->make(true);
            } catch (\Exception $e) {
                return response()->json(['error' => $e->getMessage()], 500);
            }
        }

        return view('admin.invoices.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(UserInvoice $invoice)
    {
        $invoice->load('user');
        return view('admin.invoices.show', compact('invoice'));
    }
}

