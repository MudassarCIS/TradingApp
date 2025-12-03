<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\UserInvoice;
use App\Models\RentBotPackage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;

class InvoiceController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $unpaidCount = $user->invoices()->where('status', 'Unpaid')->count();
        
        return view('customer.invoices.index', compact('unpaidCount'));
    }

    public function getInvoicesData()
    {
        $user = Auth::user();
        $invoices = $user->invoices()->with('plan')->latest()->get();

        // Get all active PEX packages ordered by amount ASC for plan name matching
        $pexPackages = RentBotPackage::active()->orderBy('amount', 'asc')->get();

        return DataTables::of($invoices)
            ->addColumn('formatted_invoice_id', function ($invoice) {
                return $invoice->formatted_invoice_id;
            })
            ->addColumn('formatted_invoice_type', function ($invoice) use ($user, $pexPackages) {
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
                        // Find activeBot created around the same time as invoice (within 10 minutes)
                        $invoiceCreatedAt = $invoice->created_at;
                        $startTime = $invoiceCreatedAt->copy()->subMinutes(10);
                        $endTime = $invoiceCreatedAt->copy()->addMinutes(10);
                        
                        $activeBot = $user->activeBots()
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
                $badgeClass = match($invoice->status) {
                    'Paid' => 'success',
                    'Processing' => 'info',
                    'payment_pending' => 'warning',
                    'Unpaid' => 'danger',
                    default => 'secondary'
                };
                if($invoice->status === 'payment_pending') {
                    return '<span class="badge bg-' . $badgeClass . '">Pending For Approval</span>';
                }else{
                    return '<span class="badge bg-' . $badgeClass . '">' . $invoice->status . '</span>';
                }
            })
            ->addColumn('payment_action', function ($invoice) {
                // Hide payment button if status is not Unpaid
                if ($invoice->status === 'Unpaid') {
                    return '<a href="' . route('customer.wallet.deposit', ['invoice_id' => $invoice->id]) . '" class="btn btn-sm btn-primary">
                                <i class="bi bi-credit-card"></i> Pay
                            </a>';
                } else {

                    if($invoice->status === 'payment_pending') {
                        
                        return '<span class="text-muted"><i class="bi bi-info-circle"></i> Pending </span>';
                    }else{
                        return '<span class="text-muted"><i class="bi bi-info-circle"></i> ' . $invoice->status . '</span>';
                    }
                    
                }
            })
            ->rawColumns(['status_badge', 'payment_action', 'formatted_invoice_type'])
            ->make(true);
    }
}