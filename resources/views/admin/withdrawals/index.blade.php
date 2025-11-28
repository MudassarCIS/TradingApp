@extends('layouts.admin-layout')

@section('title', 'Withdrawals Management - AI Trade App')
@section('page-title', 'Withdrawals Management')

@push('styles')
<style>
    .withdrawal-card {
        background: linear-gradient(135deg, #ff416c 0%, #ff4b2b 100%);
        color: white;
        border-radius: 15px;
        padding: 2rem;
        margin-bottom: 2rem;
    }
    
    .stats-card {
        background: white;
        border-radius: 10px;
        padding: 1.5rem;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        margin-bottom: 1rem;
    }
    
    .stats-card h3 {
        font-size: 2rem;
        font-weight: bold;
        margin: 0;
        color: var(--primary-color);
    }
    
    .stats-card p {
        margin: 0;
        color: #6c757d;
    }
</style>
@endpush

@section('content')
<!-- Header -->
<div class="row">
    <div class="col-12 mb-4">
        <div class="withdrawal-card">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h2>Withdrawals Management</h2>
                    <p>Review and manage user withdrawal requests</p>
                </div>
                <div class="col-md-4 text-end">
                    <i class="bi bi-dash-circle" style="font-size: 4rem; opacity: 0.3;"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Statistics -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="stats-card">
            <h3>{{ $totalWithdrawals }}</h3>
            <p>Total Withdrawals</p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stats-card">
            <h3>{{ $pendingWithdrawals }}</h3>
            <p>Pending</p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stats-card">
            <h3>{{ $processingWithdrawals }}</h3>
            <p>Processing</p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stats-card">
            <h3>${{ number_format($totalAmount, 2) }}</h3>
            <p>Total Paid Out</p>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-funnel"></i> Filters</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label for="filter_status" class="form-label">Status</label>
                        <select class="form-select" id="filter_status" name="filter_status">
                            <option value="">All Statuses</option>
                            <option value="pending">Pending</option>
                            <option value="processing">Processing</option>
                            <option value="completed">Completed</option>
                            <option value="failed">Failed</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="button" class="btn btn-primary" id="applyFilters">
                            <i class="bi bi-search"></i> Apply Filters
                        </button>
                        <button type="button" class="btn btn-secondary ms-2" id="resetFilters">
                            <i class="bi bi-arrow-clockwise"></i> Reset
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Withdrawals Table -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-list-ul"></i> Withdrawals List</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="withdrawalsTable" class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Transaction ID</th>
                                <th>User</th>
                                <th>Email</th>
                                <th>Amount</th>
                                <th>Fee</th>
                                <th>Net Amount</th>
                                <th>Address</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- View Withdrawal Modal -->
<div class="modal fade" id="viewWithdrawalModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Withdrawal Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="withdrawalDetails">
                <!-- Details will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Complete Withdrawal Modal -->
<div class="modal fade" id="completeWithdrawalModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Complete Withdrawal</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="completeWithdrawalForm">
                <div class="modal-body">
                    <input type="hidden" id="complete_withdrawal_id" name="withdrawal_id">
                    <div class="mb-3">
                        <label for="tx_hash" class="form-label">Transaction Hash (Optional)</label>
                        <input type="text" class="form-control" id="tx_hash" name="tx_hash" placeholder="Enter blockchain transaction hash">
                    </div>
                    <div class="mb-3">
                        <label for="complete_notes" class="form-label">Notes</label>
                        <textarea class="form-control" id="complete_notes" name="notes" rows="3" placeholder="Add any notes about this withdrawal"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Mark as Completed</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Reject Withdrawal Modal -->
<div class="modal fade" id="rejectWithdrawalModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Reject Withdrawal</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="rejectWithdrawalForm">
                <div class="modal-body">
                    <input type="hidden" id="reject_withdrawal_id" name="withdrawal_id">
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle"></i> This will reject the withdrawal and refund the amount to the user's wallet.
                    </div>
                    <div class="mb-3">
                        <label for="reject_reason" class="form-label">Rejection Reason <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="reject_reason" name="reason" rows="3" required placeholder="Enter reason for rejection"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Reject Withdrawal</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Initialize DataTable
    var table = $('#withdrawalsTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('admin.withdrawals.data') }}",
            data: function(d) {
                d.filter_status = $('#filter_status').val();
            }
        },
        columns: [
            { data: 'transaction_id', name: 'transaction_id' },
            { data: 'user_name', name: 'user_name' },
            { data: 'user_email', name: 'user_email' },
            { data: 'amount', name: 'amount' },
            { data: 'fee', name: 'fee' },
            { data: 'net_amount', name: 'net_amount' },
            { data: 'to_address', name: 'to_address' },
            { data: 'status', name: 'status' },
            { data: 'created_at', name: 'created_at' },
            { data: 'actions', name: 'actions', orderable: false, searchable: false }
        ],
        order: [[8, 'desc']],
        pageLength: 25
    });

    // Apply filters
    $('#applyFilters').on('click', function() {
        table.ajax.reload();
    });
    
    // Reset filters
    $('#resetFilters').on('click', function() {
        $('#filter_status').val('');
        table.ajax.reload();
    });
});

// Approve withdrawal
function approveWithdrawal(id) {
    if (!confirm('Are you sure you want to approve this withdrawal? It will be marked as processing.')) {
        return;
    }
    
    $.ajax({
        url: "{{ route('admin.withdrawals.approve', ':id') }}".replace(':id', id),
        method: 'POST',
        data: {
            _token: '{{ csrf_token() }}'
        },
        success: function(response) {
            if (response.success) {
                alert(response.message);
                $('#withdrawalsTable').DataTable().ajax.reload();
            } else {
                alert('Error: ' + response.message);
            }
        },
        error: function(xhr) {
            alert('Error approving withdrawal');
        }
    });
}

// Complete withdrawal
function completeWithdrawal(id) {
    $('#complete_withdrawal_id').val(id);
    $('#completeWithdrawalModal').modal('show');
}

$('#completeWithdrawalForm').on('submit', function(e) {
    e.preventDefault();
    var id = $('#complete_withdrawal_id').val();
    
    $.ajax({
        url: "{{ route('admin.withdrawals.complete', ':id') }}".replace(':id', id),
        method: 'POST',
        data: {
            _token: '{{ csrf_token() }}',
            tx_hash: $('#tx_hash').val(),
            notes: $('#complete_notes').val()
        },
        success: function(response) {
            if (response.success) {
                alert(response.message);
                $('#completeWithdrawalModal').modal('hide');
                $('#completeWithdrawalForm')[0].reset();
                $('#withdrawalsTable').DataTable().ajax.reload();
            } else {
                alert('Error: ' + response.message);
            }
        },
        error: function(xhr) {
            alert('Error completing withdrawal');
        }
    });
});

// Reject withdrawal
function rejectWithdrawal(id) {
    $('#reject_withdrawal_id').val(id);
    $('#rejectWithdrawalModal').modal('show');
}

$('#rejectWithdrawalForm').on('submit', function(e) {
    e.preventDefault();
    var id = $('#reject_withdrawal_id').val();
    
    $.ajax({
        url: "{{ route('admin.withdrawals.reject', ':id') }}".replace(':id', id),
        method: 'POST',
        data: {
            _token: '{{ csrf_token() }}',
            reason: $('#reject_reason').val()
        },
        success: function(response) {
            if (response.success) {
                alert(response.message);
                $('#rejectWithdrawalModal').modal('hide');
                $('#rejectWithdrawalForm')[0].reset();
                $('#withdrawalsTable').DataTable().ajax.reload();
            } else {
                alert('Error: ' + response.message);
            }
        },
        error: function(xhr) {
            alert('Error rejecting withdrawal');
        }
    });
});

// View withdrawal details
function viewWithdrawal(id) {
    $.ajax({
        url: "{{ route('admin.withdrawals.show', ':id') }}".replace(':id', id),
        method: 'GET',
        success: function(response) {
            if (response.success) {
                var data = response.data;
                var html = `
                    <table class="table table-bordered">
                        <tr><th>Transaction ID</th><td>${data.transaction_id}</td></tr>
                        <tr><th>User Name</th><td>${data.user_name}</td></tr>
                        <tr><th>User Email</th><td>${data.user_email}</td></tr>
                        <tr><th>Amount</th><td>$${data.amount} USDT</td></tr>
                        <tr><th>Fee</th><td>$${data.fee} USDT</td></tr>
                        <tr><th>Net Amount</th><td>$${data.net_amount} USDT</td></tr>
                        <tr><th>Withdrawal Address</th><td><code>${data.to_address}</code></td></tr>
                        <tr><th>Status</th><td>${data.status}</td></tr>
                        <tr><th>Transaction Hash</th><td>${data.tx_hash || 'N/A'}</td></tr>
                        <tr><th>Notes</th><td>${data.notes || 'N/A'}</td></tr>
                        <tr><th>Created At</th><td>${data.created_at}</td></tr>
                        <tr><th>Processed At</th><td>${data.processed_at || 'N/A'}</td></tr>
                    </table>
                `;
                $('#withdrawalDetails').html(html);
                $('#viewWithdrawalModal').modal('show');
            }
        },
        error: function(xhr) {
            alert('Error loading withdrawal details');
        }
    });
}
</script>
@endpush

