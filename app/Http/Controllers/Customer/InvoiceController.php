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
        $invoices = $user->invoices()->with(['plan', 'rentBotPackage'])->orderBy('created_at', 'desc');

        return DataTables::of($invoices)
            ->orderColumn('formatted_created_at', 'created_at $1') // Map formatted_created_at to created_at for ordering
            ->addColumn('formatted_invoice_id', function ($invoice) {
                return $invoice->formatted_invoice_id;
            })
            ->addColumn('formatted_invoice_type', function ($invoice) use ($user) {
                $type = $invoice->invoice_type;
                $planName = null;
                
                if ($type === 'PEX') {
                    // For PEX, get package_name from database relationship
                    if ($invoice->rent_bot_package_id) {
                        // Check if relationship is loaded
                        if ($invoice->relationLoaded('rentBotPackage') && $invoice->rentBotPackage) {
                            $planName = $invoice->rentBotPackage->package_name;
                        } else {
                            // Load package if relationship not loaded
                            $package = RentBotPackage::find($invoice->rent_bot_package_id);
                            if ($package && $package->package_name) {
                                $planName = $package->package_name;
                            }
                        }
                    } elseif ($invoice->plan_id) {
                        // Fallback: if rent_bot_package_id is empty, check plan_id
                        // Find PEX package where id matches plan_id
                        $package = RentBotPackage::find($invoice->plan_id);
                        if ($package && $package->package_name) {
                            $planName = $package->package_name;
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