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
        $invoices = $user->invoices()->latest()->get();

        return DataTables::of($invoices)
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
                $badgeClass = $invoice->status === 'Paid' ? 'success' : 'danger';
                return '<span class="badge bg-' . $badgeClass . '">' . $invoice->status . '</span>';
            })
            ->rawColumns(['status_badge'])
            ->make(true);
    }
}