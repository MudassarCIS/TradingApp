@extends('layouts.customer-layout')

@section('title', 'Customer Invoices - AI Trade App')
@section('page-title', 'Customer Invoices')

@section('content')
<div class="mb-4">
    <h2>Pay Rental or Joining Fee</h2>
    <div class="alert alert-info mt-3 mb-4">
        <i class="bi bi-info-circle me-2"></i>
        Pay Rental, Joining Fee or Profit share Through Secured Invoices System, TSG not access directly your Exchange Wallet for any payment withdrawal.
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0"><i class="bi bi-receipt"></i> Invoices</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table id="invoices-table" class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Invoice No.</th>
                        <th>Type</th>
                        <th>Amount (USDT)</th>
                        <th>Due Date</th>
                        <th>Status</th>
                        <th>Created At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Data will be loaded via DataTables -->
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Payment Modal -->
<div class="modal fade" id="paymentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Payment Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <h6>Payment Instructions</h6>
                    <p>Please make payment to the following wallet address:</p>
                    <div class="input-group mb-3">
                        <input type="text" class="form-control" id="wallet-address" readonly>
                        <button class="btn btn-outline-secondary" type="button" onclick="copyToClipboard('wallet-address')">
                            <i class="bi bi-copy"></i>
                        </button>
                    </div>
                    <p><strong>Amount:</strong> <span id="payment-amount"></span> USDT</p>
                    <p><strong>Network:</strong> TRC20 (Tron)</p>
                </div>
                <div class="alert alert-warning">
                    <small>
                        <i class="bi bi-exclamation-triangle"></i>
                        Please ensure you send the exact amount and use the TRC20 network. 
                        Payments may take up to 30 minutes to be confirmed.
                    </small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="markAsPaid()">Mark as Paid</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">

<script>
$(document).ready(function() {
    // Initialize DataTable
    $('#invoices-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("customer.invoices.data") }}',
            type: 'GET'
        },
        columns: [
            { 
                data: 'formatted_invoice_id',
                render: function(data, type, row) {
                    return '<i class="bi bi-receipt"></i> ' + data;
                }
            },
            { data: 'formatted_invoice_type' },
            { data: 'formatted_amount' },
            { data: 'formatted_due_date' },
            { data: 'status_badge' },
            { data: 'formatted_created_at' },
            { data: 'payment_action', orderable: false, searchable: false }
        ],
        order: [[0, 'desc']],
        pageLength: 10,
        responsive: true
    });
});

function showPaymentModal(invoiceId, amount) {
    $('#payment-amount').text(amount);
    // You can fetch the actual wallet address from your system
    $('#wallet-address').val('TYourWalletAddressHere123456789');
    $('#paymentModal').modal('show');
}

function copyToClipboard(elementId) {
    const element = document.getElementById(elementId);
    element.select();
    element.setSelectionRange(0, 99999);
    document.execCommand('copy');
    
    // Show feedback
    const button = event.target.closest('button');
    const originalText = button.innerHTML;
    button.innerHTML = '<i class="bi bi-check"></i> Copied!';
    setTimeout(() => {
        button.innerHTML = originalText;
    }, 2000);
}

function markAsPaid() {
    // This would typically involve an API call to mark the invoice as paid
    alert('Payment confirmation feature coming soon! Please contact support to confirm your payment.');
    $('#paymentModal').modal('hide');
}
</script>

<style>
.table th {
    background-color: var(--primary-color);
    color: white;
    border: none;
}

.table td {
    vertical-align: middle;
}

.badge {
    font-size: 0.8em;
}

.btn-sm {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
}
</style>
@endpush
