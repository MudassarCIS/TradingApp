@extends('layouts.admin-layout')

@section('title', 'Manage NEXA Plans')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">NEXA Plans Management</h3>
                    <a href="{{ route('admin.plans.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add New NEXA Plan
                    </a>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table id="plans-table" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Investment Amount</th>
                                    <th>Joining Fee</th>
                                    <th>Fee Percentage</th>
                                    <th>Bots Allowed</th>
                                    <th>Trades/Day</th>
                                    <th>Direct Bonus</th>
                                    <th>Status</th>
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
    $('#plans-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('admin.plans.index') }}",
        columns: [
            {data: 'id', name: 'id'},
            {data: 'name', name: 'name'},
            {data: 'investment_amount', name: 'investment_amount'},
            {data: 'joining_fee', name: 'joining_fee'},
            {data: 'fee_percentage', name: 'fee_percentage'},
            {data: 'bots_allowed', name: 'bots_allowed'},
            {data: 'trades_per_day', name: 'trades_per_day'},
            {data: 'direct_bonus', name: 'direct_bonus'},
            {data: 'status', name: 'is_active'},
            {data: 'action', name: 'action', orderable: false, searchable: false}
        ],
        responsive: true,
        pageLength: 25,
        lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]]
    });
});
</script>
@endpush
@endsection
