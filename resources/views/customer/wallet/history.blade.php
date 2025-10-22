@extends('layouts.customer-layout')

@section('title', 'Transaction History - AI Trade App')
@section('page-title', 'Transaction History')

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/select/1.7.0/css/select.bootstrap5.min.css">

<style>
    .transaction-history-card {
        background: white;
        border-radius: 15px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        overflow: hidden;
    }
    
    .filter-section {
        background: #f8f9fa;
        padding: 1.5rem;
        border-bottom: 1px solid #dee2e6;
    }
    
    .filter-row {
        display: flex;
        gap: 1rem;
        align-items: end;
        flex-wrap: wrap;
    }
    
    .filter-group {
        flex: 1;
        min-width: 200px;
    }
    
    .filter-group label {
        font-weight: 600;
        color: #495057;
        margin-bottom: 0.5rem;
        display: block;
    }
    
    .filter-group select,
    .filter-group input {
        width: 100%;
        padding: 0.5rem;
        border: 1px solid #ced4da;
        border-radius: 0.375rem;
        font-size: 0.875rem;
    }
    
    .filter-actions {
        display: flex;
        gap: 0.5rem;
        align-items: end;
    }
    
    .btn-filter {
        background: var(--primary-color);
        color: white;
        border: none;
        padding: 0.5rem 1rem;
        border-radius: 0.375rem;
        font-size: 0.875rem;
        font-weight: 500;
        transition: all 0.3s ease;
    }
    
    .btn-filter:hover {
        background: var(--primary-color-dark);
        transform: translateY(-1px);
    }
    
    .btn-reset {
        background: #6c757d;
        color: white;
        border: none;
        padding: 0.5rem 1rem;
        border-radius: 0.375rem;
        font-size: 0.875rem;
        font-weight: 500;
        transition: all 0.3s ease;
    }
    
    .btn-reset:hover {
        background: #5a6268;
        transform: translateY(-1px);
    }
    
    .dataTables_wrapper {
        padding: 1.5rem;
    }
    
    .dataTables_wrapper .dataTables_length,
    .dataTables_wrapper .dataTables_filter,
    .dataTables_wrapper .dataTables_info,
    .dataTables_wrapper .dataTables_processing,
    .dataTables_wrapper .dataTables_paginate {
        margin-bottom: 1rem;
    }
    
    .dataTables_wrapper .dataTables_length select {
        padding: 0.25rem 0.5rem;
        border: 1px solid #ced4da;
        border-radius: 0.25rem;
        margin: 0 0.5rem;
    }
    
    .dataTables_wrapper .dataTables_filter input {
        padding: 0.5rem;
        border: 1px solid #ced4da;
        border-radius: 0.375rem;
        margin-left: 0.5rem;
    }
    
    .table {
        margin-bottom: 0;
    }
    
    .table thead th {
        background: var(--primary-color);
        color: white;
        border: none;
        padding: 1rem 0.75rem;
        font-weight: 600;
        font-size: 0.875rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .table tbody td {
        padding: 1rem 0.75rem;
        vertical-align: middle;
        border-top: 1px solid #dee2e6;
        font-size: 0.875rem;
    }
    
    .table tbody tr:hover {
        background-color: #f8f9fa;
    }
    
    .transaction-id {
        font-family: 'Courier New', monospace;
        font-size: 0.8rem;
        color: #6c757d;
        background: #f8f9fa;
        padding: 0.25rem 0.5rem;
        border-radius: 0.25rem;
        display: inline-block;
    }
    
    .amount-positive {
        color: #28a745;
        font-weight: 600;
    }
    
    .amount-negative {
        color: #dc3545;
        font-weight: 600;
    }
    
    .amount-neutral {
        color: #6c757d;
        font-weight: 600;
    }
    
    .tx-hash {
        font-family: 'Courier New', monospace;
        font-size: 0.75rem;
        color: #007bff;
        text-decoration: none;
        max-width: 120px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        display: inline-block;
    }
    
    .tx-hash:hover {
        color: #0056b3;
        text-decoration: underline;
    }
    
    .address {
        font-family: 'Courier New', monospace;
        font-size: 0.75rem;
        color: #6c757d;
        max-width: 100px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        display: inline-block;
    }
    
    .dataTables_paginate .paginate_button {
        padding: 0.5rem 0.75rem;
        margin: 0 0.125rem;
        border: 1px solid #dee2e6;
        border-radius: 0.25rem;
        color: #007bff;
        text-decoration: none;
        transition: all 0.3s ease;
    }
    
    .dataTables_paginate .paginate_button:hover {
        background: #e9ecef;
        border-color: #adb5bd;
    }
    
    .dataTables_paginate .paginate_button.current {
        background: var(--primary-color);
        color: white;
        border-color: var(--primary-color);
    }
    
    .dataTables_paginate .paginate_button.disabled {
        color: #6c757d;
        cursor: not-allowed;
    }
    
    .dataTables_paginate .paginate_button.disabled:hover {
        background: transparent;
        border-color: #dee2e6;
    }
    
    .loading-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(255, 255, 255, 0.8);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 1000;
    }
    
    .loading-spinner {
        width: 40px;
        height: 40px;
        border: 4px solid #f3f3f3;
        border-top: 4px solid var(--primary-color);
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }
    
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
    
    .no-data {
        text-align: center;
        padding: 3rem;
        color: #6c757d;
    }
    
    .no-data i {
        font-size: 3rem;
        opacity: 0.3;
        margin-bottom: 1rem;
    }
    
    /* Responsive adjustments */
    @media (max-width: 768px) {
        .filter-row {
            flex-direction: column;
            align-items: stretch;
        }
        
        .filter-group {
            min-width: auto;
        }
        
        .filter-actions {
            justify-content: center;
        }
        
        .dataTables_wrapper {
            padding: 1rem;
        }
        
        .table-responsive {
            font-size: 0.8rem;
        }
        
        .table thead th,
        .table tbody td {
            padding: 0.5rem 0.25rem;
        }
    }
</style>
@endpush

@section('content')
<div class="row">
    <div class="col-12">
        <div class="transaction-history-card">
            <!-- Filter Section -->
            <div class="filter-section">
                <h5 class="mb-3"><i class="bi bi-funnel"></i> Filter Transactions</h5>
                <div class="filter-row">
                    <div class="filter-group">
                        <label for="filter-type">Transaction Type</label>
                        <select id="filter-type" class="form-select">
                            <option value="">All Types</option>
                            <option value="deposit">Deposit</option>
                            <option value="withdrawal">Withdrawal</option>
                            <option value="transfer">Transfer</option>
                            <option value="bonus">Bonus</option>
                            <option value="commission">Commission</option>
                            <option value="refund">Refund</option>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label for="filter-status">Status</label>
                        <select id="filter-status" class="form-select">
                            <option value="">All Statuses</option>
                            <option value="pending">Pending</option>
                            <option value="completed">Completed</option>
                            <option value="failed">Failed</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label for="filter-date-from">Date From</label>
                        <input type="date" id="filter-date-from" class="form-control">
                    </div>
                    
                    <div class="filter-group">
                        <label for="filter-date-to">Date To</label>
                        <input type="date" id="filter-date-to" class="form-control">
                    </div>
                    
                    <div class="filter-actions">
                        <button type="button" class="btn-filter" id="apply-filters">
                            <i class="bi bi-search"></i> Apply
                        </button>
                        <button type="button" class="btn-reset" id="reset-filters">
                            <i class="bi bi-arrow-clockwise"></i> Reset
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- DataTable Section -->
            <div class="dataTables_wrapper">
                <div class="loading-overlay" id="loading-overlay" style="display: none;">
                    <div class="loading-spinner"></div>
                </div>
                
                <table id="transactions-table" class="table table-striped table-hover" style="width:100%">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Transaction ID</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Amount</th>
                            <th>Fee</th>
                            <th>Net Amount</th>
                            <th>Currency</th>
                            <th>Notes</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Data will be loaded via AJAX -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Transaction Details Modal -->
<div class="modal fade" id="transactionModal" tabindex="-1" aria-labelledby="transactionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="transactionModalLabel">Transaction Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="transaction-details">
                <!-- Transaction details will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>
<script src="https://cdn.datatables.net/select/1.7.0/js/dataTables.select.min.js"></script>

<script>
$(document).ready(function() {
    // Initialize DataTable
    var table = $('#transactions-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('customer.wallet.history') }}",
            type: 'GET',
            data: function(d) {
                d.type = $('#filter-type').val();
                d.status = $('#filter-status').val();
                d.date_from = $('#filter-date-from').val();
                d.date_to = $('#filter-date-to').val();
            },
            beforeSend: function() {
                $('#loading-overlay').show();
            },
            complete: function() {
                $('#loading-overlay').hide();
            },
            error: function(xhr, error, thrown) {
                console.error('DataTables error:', error);
                $('#loading-overlay').hide();
                alert('Error loading transaction data. Please try again.');
            }
        },
        columns: [
            { data: 'created_at', name: 'created_at', orderable: true },
            { 
                data: 'transaction_id', 
                name: 'transaction_id',
                render: function(data, type, row) {
                    return '<span class="transaction-id">' + data + '</span>';
                }
            },
            { data: 'type', name: 'type', orderable: false },
            { data: 'status', name: 'status', orderable: false },
            { 
                data: 'amount', 
                name: 'amount',
                render: function(data, type, row) {
                    var amount = parseFloat(data);
                    var type = row.type.toLowerCase();
                    var cssClass = 'amount-neutral';
                    
                    if (type.includes('deposit') || type.includes('bonus') || type.includes('commission')) {
                        cssClass = 'amount-positive';
                    } else if (type.includes('withdrawal') || type.includes('fee')) {
                        cssClass = 'amount-negative';
                    }
                    
                    return '<span class="' + cssClass + '">$' + data + '</span>';
                }
            },
            { 
                data: 'fee', 
                name: 'fee',
                render: function(data, type, row) {
                    return data === '-' ? '<span class="text-muted">-</span>' : '<span class="amount-negative">$' + data + '</span>';
                }
            },
            { 
                data: 'net_amount', 
                name: 'net_amount',
                render: function(data, type, row) {
                    return data === '-' ? '<span class="text-muted">-</span>' : '<span class="amount-positive">$' + data + '</span>';
                }
            },
            { data: 'currency', name: 'currency', orderable: false },
            { 
                data: 'notes', 
                name: 'notes',
                render: function(data, type, row) {
                    return data === '-' ? '<span class="text-muted">-</span>' : data;
                }
            },
            {
                data: null,
                orderable: false,
                searchable: false,
                render: function(data, type, row) {
                    return '<button class="btn btn-sm btn-outline-primary" onclick="viewTransactionDetails(' + row.id + ')">' +
                           '<i class="bi bi-eye"></i> View</button>';
                }
            }
        ],
        order: [[0, 'desc']],
        pageLength: 25,
        lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
        responsive: true,
        language: {
            processing: "Loading transactions...",
            lengthMenu: "Show _MENU_ transactions per page",
            zeroRecords: "No transactions found",
            info: "Showing _START_ to _END_ of _TOTAL_ transactions",
            infoEmpty: "Showing 0 to 0 of 0 transactions",
            infoFiltered: "(filtered from _MAX_ total transactions)",
            search: "Search:",
            paginate: {
                first: "First",
                last: "Last",
                next: "Next",
                previous: "Previous"
            }
        },
        dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>' +
             '<"row"<"col-sm-12"tr>>' +
             '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
        initComplete: function() {
            // Add custom styling after initialization
            $('.dataTables_length select').addClass('form-select form-select-sm');
            $('.dataTables_filter input').addClass('form-control form-control-sm');
        }
    });
    
    // Apply filters
    $('#apply-filters').on('click', function() {
        table.ajax.reload();
    });
    
    // Reset filters
    $('#reset-filters').on('click', function() {
        $('#filter-type').val('');
        $('#filter-status').val('');
        $('#filter-date-from').val('');
        $('#filter-date-to').val('');
        table.ajax.reload();
    });
    
    // Auto-apply filters on change
    $('#filter-type, #filter-status, #filter-date-from, #filter-date-to').on('change', function() {
        table.ajax.reload();
    });
    
    // Set default date range (last 30 days)
    var today = new Date();
    var thirtyDaysAgo = new Date(today.getTime() - (30 * 24 * 60 * 60 * 1000));
    
    $('#filter-date-from').val(thirtyDaysAgo.toISOString().split('T')[0]);
    $('#filter-date-to').val(today.toISOString().split('T')[0]);
});

// View transaction details
function viewTransactionDetails(transactionId) {
    // This would typically make an AJAX call to get full transaction details
    // For now, we'll show a placeholder
    $('#transaction-details').html(`
        <div class="text-center">
            <i class="bi bi-info-circle text-primary" style="font-size: 3rem;"></i>
            <h5 class="mt-3">Transaction Details</h5>
            <p class="text-muted">Transaction ID: ${transactionId}</p>
            <p class="text-muted">Full transaction details would be loaded here via AJAX.</p>
        </div>
    `);
    
    $('#transactionModal').modal('show');
}

// Export functions
function exportToCSV() {
    // This would trigger a CSV export
    console.log('Export to CSV');
}

function exportToPDF() {
    // This would trigger a PDF export
    console.log('Export to PDF');
}

// Refresh table
function refreshTable() {
    $('#transactions-table').DataTable().ajax.reload();
}

// Auto-refresh every 30 seconds
setInterval(function() {
    refreshTable();
}, 30000);
</script>
@endpush
