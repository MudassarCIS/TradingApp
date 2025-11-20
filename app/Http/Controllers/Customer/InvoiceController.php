<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\UserInvoice;
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

        return DataTables::of($invoices)
            ->addColumn('formatted_invoice_id', function ($invoice) {
                return $invoice->formatted_invoice_id;
            })
            ->addColumn('formatted_invoice_type', function ($invoice) {
                $type = $invoice->invoice_type;
                $planName = $invoice->plan ? $invoice->plan->name : null;
                
                if ($planName) {
                    return $type . ' <span class="text-muted">(' . $planName . ')</span>';
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