@extends('layouts.admin-layout')

@section('title', 'Deposits Management - AI Trade App')
@section('page-title', 'Deposits Management')

@push('styles')
<style>
    .deposit-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
        <div class="deposit-card">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h2>Deposits Management</h2>
                    <p>Review and manage user deposit requests</p>
                </div>
                <div class="col-md-4 text-end">
                    <i class="bi bi-credit-card" style="font-size: 4rem; opacity: 0.3;"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Statistics -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="stats-card">
            <h3 id="totalDeposits">-</h3>
            <p>Total Deposits</p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stats-card">
            <h3 id="pendingDeposits">-</h3>
            <p>Pending</p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stats-card">
            <h3 id="approvedDeposits">-</h3>
            <p>Approved</p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stats-card">
            <h3 id="rejectedDeposits">-</h3>
            <p>Rejected</p>
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
                <form id="filterForm" class="row g-3">
                    <div class="col-md-3">
                        <label for="filter_status" class="form-label">Status</label>
                        <select class="form-select" id="filter_status" name="filter_status">
                            <option value="">All Statuses</option>
                            <option value="pending">Pending</option>
                            <option value="processing">Processing</option>
                            <option value="approved">Approved</option>
                            <option value="rejected">Rejected</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="filter_user_id" class="form-label">User ID</label>
                        <input type="number" class="form-control" id="filter_user_id" name="filter_user_id" placeholder="User ID">
                    </div>
                    <div class="col-md-3">
                        <label for="filter_date_from" class="form-label">Date From</label>
                        <input type="date" class="form-control" id="filter_date_from" name="filter_date_from">
                    </div>
                    <div class="col-md-2">
                        <label for="filter_date_to" class="form-label">Date To</label>
                        <input type="date" class="form-control" id="filter_date_to" name="filter_date_to">
                    </div>
                    <div class="col-md-2 d-flex align-items-end gap-2">
                        <button type="button" class="btn btn-primary" id="applyFilters">
                            <i class="bi bi-search"></i> Filter
                        </button>
                        <button type="button" class="btn btn-secondary" id="resetFilters">
                            <i class="bi bi-arrow-clockwise"></i> Reset
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Deposits Table -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="bi bi-list-ul"></i> All Deposits
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="depositsTable" class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>User</th>
                                <th>Amount</th>
                                <th>Currency</th>
                                <th>Network</th>
                                <th>Transaction ID</th>
                                <th>Status</th>
                                <th>Proof</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- View Deposit Modal -->
<div class="modal fade" id="viewDepositModal" tabindex="-1" aria-labelledby="viewDepositModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewDepositModalLabel">
                    <i class="bi bi-eye"></i> Deposit Details
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="depositDetails">
                <!-- Deposit details will be loaded here -->
            </div>
        </div>
    </div>
</div>

<!-- Approve Deposit Modal -->
<div class="modal fade" id="approveDepositModal" tabindex="-1" aria-labelledby="approveDepositModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="approveDepositModalLabel">
                    <i class="bi bi-check-circle"></i> Approve Deposit
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="approveDepositForm">
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i> Are you sure you want to approve this deposit? This action will credit the user's wallet.
                    </div>
                    <div class="mb-3">
                        <label for="approveNotes" class="form-label">Notes (Optional)</label>
                        <textarea class="form-control" id="approveNotes" name="notes" rows="3" placeholder="Add any notes about this approval..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-check"></i> Approve Deposit
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Reject Deposit Modal -->
<div class="modal fade" id="rejectDepositModal" tabindex="-1" aria-labelledby="rejectDepositModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="rejectDepositModalLabel">
                    <i class="bi bi-x-circle"></i> Reject Deposit
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="rejectDepositForm">
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle"></i> Are you sure you want to reject this deposit? Please provide a reason.
                    </div>
                    <div class="mb-3">
                        <label for="rejectionReason" class="form-label">Rejection Reason <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="rejectionReason" name="rejection_reason" rows="3" placeholder="Please provide a reason for rejection..." required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="rejectNotes" class="form-label">Notes (Optional)</label>
                        <textarea class="form-control" id="rejectNotes" name="notes" rows="3" placeholder="Add any additional notes..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-x"></i> Reject Deposit
                    </button>
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
    var table = $('#depositsTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('admin.deposits.index') }}",
            type: 'GET',
            data: function(d) {
                // Add custom filters
                d.filter_status = $('#filter_status').val();
                d.filter_user_id = $('#filter_user_id').val();
                d.filter_date_from = $('#filter_date_from').val();
                d.filter_date_to = $('#filter_date_to').val();
            }
        },
        columns: [
            { data: 'deposit_id', name: 'deposit_id' },
            { data: 'user_name', name: 'user_name' },
            { data: 'amount_formatted', name: 'amount' },
            { data: 'currency', name: 'currency' },
            { data: 'network', name: 'network' },
            { data: 'trans_id', name: 'trans_id' },
            { data: 'status_badge', name: 'status' },
            { data: 'proof_image', name: 'proof_image' },
            { data: 'created_at', name: 'created_at' },
            { data: 'actions', name: 'actions', orderable: false, searchable: false }
        ],
        order: [[8, 'desc']], // Order by created_at (column 8) descending - latest first
        pageLength: 25,
        responsive: true,
        dom: 'Bfrtip',
        buttons: [
            'copy', 'csv', 'excel', 'pdf', 'print'
        ]
    });

    // Load statistics
    loadStatistics();

    // Apply filters
    $('#applyFilters').on('click', function() {
        table.ajax.reload();
        loadStatistics();
    });
    
    // Reset filters
    $('#resetFilters').on('click', function() {
        $('#filter_status').val('');
        $('#filter_user_id').val('');
        $('#filter_date_from').val('');
        $('#filter_date_to').val('');
        table.ajax.reload();
        loadStatistics();
    });

    // Status dropdown change with confirmation
    $(document).on('change', '.status-dropdown', function() {
        var $select = $(this);
        var depositId = $select.data('deposit-id');
        var currentStatus = $select.data('current-status');
        var newStatus = $select.val();
        
        if (newStatus === currentStatus) {
            return; // No change needed
        }
        
        if (!confirm('Are you sure you want to change the status from ' + currentStatus.toUpperCase() + ' to ' + newStatus.toUpperCase() + '?')) {
            // Reset to current status
            $select.val(currentStatus);
            return;
        }
        
        // Disable select during update
        $select.prop('disabled', true);
        
        // Get CSRF token from meta tag
        var csrfToken = $('meta[name="csrf-token"]').attr('content');
        
        // Use form-urlencoded data for Laravel method spoofing
        var formData = {
            status: newStatus,
            _token: csrfToken,
            _method: 'PUT'
        };
        
        var updateUrl = '/admin/deposits/' + depositId;
        
        $.ajax({
            url: updateUrl,
            type: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            data: formData,
            dataType: 'json',
            success: function(response) {
                console.log('Status update response:', response);
                
                if (response && response.success) {
                    // Show success message
                    showAlert('success', response.success || 'Deposit status updated successfully');
                    
                    // Reload the table to refresh all rows with updated data
                    table.ajax.reload(function() {
                        // After reload, update statistics
                        loadStatistics();
                        $select.prop('disabled', false);
                    }, false); // false = don't reset pagination
                } else {
                    var errorMsg = (response && response.error) || 'Status update failed';
                    showAlert('error', errorMsg);
                    $select.val(currentStatus);
                    $select.prop('disabled', false);
                }
            },
            error: function(xhr, status, error) {
                console.error('Status update error:', {
                    status: xhr.status,
                    statusText: xhr.statusText,
                    response: xhr.responseJSON || xhr.responseText,
                    error: error
                });
                
                var errorMsg = 'An error occurred while updating status';
                
                // Try to parse error response
                if (xhr.responseJSON) {
                    if (xhr.responseJSON.error) {
                        errorMsg = xhr.responseJSON.error;
                    } else if (xhr.responseJSON.message) {
                        errorMsg = xhr.responseJSON.message;
                    }
                } else if (xhr.responseText) {
                    try {
                        var jsonResponse = JSON.parse(xhr.responseText);
                        errorMsg = jsonResponse.error || jsonResponse.message || errorMsg;
                    } catch (e) {
                        // If it's not JSON, show status text
                        if (xhr.status === 404) {
                            errorMsg = 'Deposit not found';
                        } else if (xhr.status === 403) {
                            errorMsg = 'Permission denied';
                        } else if (xhr.status === 422) {
                            errorMsg = 'Validation error: ' + (xhr.responseJSON?.message || 'Invalid data');
                        } else if (xhr.status === 500) {
                            errorMsg = 'Server error occurred. Please check the logs.';
                        }
                    }
                }
                
                showAlert('error', errorMsg);
                $select.val(currentStatus);
                $select.prop('disabled', false);
            }
        });
    });

    // View deposit
    window.viewDeposit = function(id) {
        $.get("/admin/deposits/" + id + "/show", function(data) {
            var html = `
                <div class="row">
                    <div class="col-md-6">
                        <h6>Deposit Information</h6>
                        <p><strong>Deposit ID:</strong> ${data.deposit_id}</p>
                        ${data.trans_id ? `<p><strong>Transaction ID:</strong> ${data.trans_id}</p>` : ''}
                        <p><strong>Amount:</strong> $${parseFloat(data.amount).toFixed(2)} ${data.currency}</p>
                        <p><strong>Network:</strong> ${data.network}</p>
                        <p><strong>Status:</strong> <span class="badge bg-${data.status === 'pending' ? 'warning' : (data.status === 'processing' ? 'info' : (data.status === 'approved' ? 'success' : (data.status === 'rejected' ? 'danger' : (data.status === 'cancelled' ? 'secondary' : 'secondary'))))}">${data.status.charAt(0).toUpperCase() + data.status.slice(1)}</span></p>
                        <p><strong>Date:</strong> ${new Date(data.created_at).toLocaleString()}</p>
                    </div>
                    <div class="col-md-6">
                        <h6>User Information</h6>
                        <p><strong>Name:</strong> ${data.user.name}</p>
                        <p><strong>Email:</strong> ${data.user.email}</p>
                        ${data.approver ? `<p><strong>Approved by:</strong> ${data.approver.name}</p>` : ''}
                        ${data.approved_at ? `<p><strong>Approved at:</strong> ${new Date(data.approved_at).toLocaleString()}</p>` : ''}
                    </div>
                </div>
                ${data.notes ? `<div class="mt-3"><h6>Notes</h6><p>${data.notes}</p></div>` : ''}
                ${data.rejection_reason ? `<div class="mt-3"><h6>Rejection Reason</h6><p>${data.rejection_reason}</p></div>` : ''}
                ${data.proof_image ? `<div class="mt-3"><h6>Proof Image</h6><img src="${data.proof_image_url}" class="img-fluid" alt="Proof Image"></div>` : ''}
            `;
            $('#depositDetails').html(html);
            $('#viewDepositModal').modal('show');
        });
    };

    // Approve deposit
    window.approveDeposit = function(id) {
        $('#approveDepositForm').data('deposit-id', id);
        $('#approveDepositModal').modal('show');
    };

    $('#approveDepositForm').on('submit', function(e) {
        e.preventDefault();
        var id = $(this).data('deposit-id');
        var formData = $(this).serialize();
        
        $.post("/admin/deposits/" + id + "/approve", formData, function(response) {
            if (response.success) {
                $('#approveDepositModal').modal('hide');
                table.ajax.reload();
                loadStatistics();
                showAlert('success', response.success);
            }
        }).fail(function(xhr) {
            showAlert('error', xhr.responseJSON.error || 'An error occurred');
        });
    });

    // Reject deposit
    window.rejectDeposit = function(id) {
        $('#rejectDepositForm').data('deposit-id', id);
        $('#rejectDepositModal').modal('show');
    };

    $('#rejectDepositForm').on('submit', function(e) {
        e.preventDefault();
        var id = $(this).data('deposit-id');
        var formData = $(this).serialize();
        
        $.post("/admin/deposits/" + id + "/reject", formData, function(response) {
            if (response.success) {
                $('#rejectDepositModal').modal('hide');
                table.ajax.reload();
                loadStatistics();
                showAlert('success', response.success);
            }
        }).fail(function(xhr) {
            showAlert('error', xhr.responseJSON.error || 'An error occurred');
        });
    });

    // Cancel deposit
    window.cancelDeposit = function(id) {
        if (confirm('Are you sure you want to cancel this deposit? The associated invoice (if any) will be reset to Unpaid status.')) {
            $.post("/admin/deposits/" + id + "/cancel", {_token: '{{ csrf_token() }}'}, function(response) {
                if (response.success) {
                    table.ajax.reload();
                    loadStatistics();
                    showAlert('success', response.success);
                }
            }).fail(function(xhr) {
                showAlert('error', xhr.responseJSON.error || 'An error occurred');
            });
        }
    };

    function loadStatistics() {
        $.ajax({
            url: "{{ route('admin.deposits.index') }}",
            type: 'GET',
            data: {
                statistics_only: true
            },
            success: function(response) {
                if (response.statistics) {
                    $('#totalDeposits').text(response.statistics.total || 0);
                    $('#pendingDeposits').text(response.statistics.pending || 0);
                    $('#approvedDeposits').text(response.statistics.approved || 0);
                    $('#rejectedDeposits').text(response.statistics.rejected || 0);
                }
            },
            error: function() {
                // Fallback - load from table data
                var stats = {
                    total: table.page.info().recordsTotal,
                    pending: 0,
                    approved: 0,
                    rejected: 0
                };
                $('#totalDeposits').text(stats.total);
            }
        });
    }

    function showAlert(type, message) {
        var alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
        var alertHtml = `
            <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        $('body').prepend(alertHtml);
        setTimeout(function() {
            $('.alert').fadeOut();
        }, 5000);
    }
});
</script>
@endpush
