<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UserInvoice;
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
                $invoices = UserInvoice::with('user')->select('*');
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
                    ->rawColumns(['status_badge', 'action'])
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

