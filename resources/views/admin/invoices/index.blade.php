@extends('layouts.admin-layout')

@section('title', 'Invoices Management')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Invoices Management</h3>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table id="invoices-table" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Invoice No.</th>
                                    <th>Customer</th>
                                    <th>Email</th>
                                    <th>Type</th>
                                    <th>Amount</th>
                                    <th>Due Date</th>
                                    <th>Status</th>
                                    <th>Created At</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    $('#invoices-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('admin.invoices.index') }}",
        columns: [
            {data: 'id', name: 'id'},
            {data: 'formatted_invoice_id', name: 'formatted_invoice_id'},
            {data: 'user_name', name: 'user.name'},
            {data: 'user_email', name: 'user.email'},
            {data: 'invoice_type', name: 'invoice_type'},
            {data: 'formatted_amount', name: 'amount'},
            {data: 'formatted_due_date', name: 'due_date'},
            {data: 'status_badge', name: 'status', orderable: false, searchable: false},
            {data: 'formatted_created_at', name: 'created_at'},
            {data: 'action', name: 'action', orderable: false, searchable: false}
        ],
        responsive: true,
        pageLength: 25,
        lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
        order: [[0, 'desc']]
    });
});
</script>
@endpush
@endsection

