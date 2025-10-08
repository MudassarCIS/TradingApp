@extends('layouts.admin-layout')

@section('title', 'Wallet Addresses Management')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Wallet Addresses Management</h3>
                    <a href="{{ route('admin.wallet-addresses.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add New Wallet Address
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
                        <table id="wallet-addresses-table" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Symbol</th>
                                    <th>Wallet Address</th>
                                    <th>Network</th>
                                    <th>QR Code</th>
                                    <th>Status</th>
                                    <th>Sort Order</th>
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
    $('#wallet-addresses-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('admin.wallet-addresses.index') }}",
        columns: [
            {data: 'id', name: 'id'},
            {data: 'name', name: 'name'},
            {data: 'symbol', name: 'symbol'},
            {data: 'wallet_address', name: 'wallet_address'},
            {data: 'network', name: 'network'},
            {data: 'qr_code', name: 'qr_code', orderable: false, searchable: false},
            {data: 'status', name: 'is_active'},
            {data: 'sort_order', name: 'sort_order'},
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
