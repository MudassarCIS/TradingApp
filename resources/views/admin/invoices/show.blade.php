@extends('layouts.admin-layout')

@section('title', 'Invoice Details')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Invoice Details</h3>
                    <a href="{{ route('admin.invoices.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Invoices
                    </a>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-bordered">
                                <tr>
                                    <th width="40%">Invoice Number:</th>
                                    <td><strong>{{ $invoice->formatted_invoice_id }}</strong></td>
                                </tr>
                                <tr>
                                    <th>Invoice Type:</th>
                                    <td>{{ $invoice->invoice_type }}</td>
                                </tr>
                                <tr>
                                    <th>Amount:</th>
                                    <td><strong>${{ number_format($invoice->amount, 2) }} USDT</strong></td>
                                </tr>
                                <tr>
                                    <th>Due Date:</th>
                                    <td>{{ $invoice->due_date->format('d/m/Y') }}</td>
                                </tr>
                                <tr>
                                    <th>Status:</th>
                                    <td>
                                        @if($invoice->status === 'Paid')
                                            <span class="badge bg-success">Paid</span>
                                        @else
                                            <span class="badge bg-warning">Unpaid</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Created At:</th>
                                    <td>{{ $invoice->created_at->format('d/m/Y H:i') }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h5>Customer Information</h5>
                            <table class="table table-bordered">
                                <tr>
                                    <th width="40%">Customer Name:</th>
                                    <td>{{ $invoice->user->name ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Email:</th>
                                    <td>{{ $invoice->user->email ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Customer ID:</th>
                                    <td>{{ $invoice->user_id }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

