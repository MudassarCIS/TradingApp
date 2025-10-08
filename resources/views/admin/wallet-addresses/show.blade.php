@extends('layouts.admin-layout')

@section('title', 'View Wallet Address')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Wallet Address Details: {{ $walletAddress->name }}</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.wallet-addresses.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Wallet Addresses
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-bordered">
                                <tr>
                                    <th>Name</th>
                                    <td>{{ $walletAddress->name }}</td>
                                </tr>
                                <tr>
                                    <th>Symbol</th>
                                    <td>{{ $walletAddress->symbol }}</td>
                                </tr>
                                <tr>
                                    <th>Wallet Address</th>
                                    <td><code>{{ $walletAddress->wallet_address }}</code></td>
                                </tr>
                                <tr>
                                    <th>Network</th>
                                    <td>{{ $walletAddress->network ?? 'Not specified' }}</td>
                                </tr>
                                <tr>
                                    <th>Status</th>
                                    <td>
                                        @if($walletAddress->is_active)
                                            <span class="badge bg-success">Active</span>
                                        @else
                                            <span class="badge bg-danger">Inactive</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Sort Order</th>
                                    <td>{{ $walletAddress->sort_order }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            @if($walletAddress->qr_code_image)
                                <h5>QR Code</h5>
                                <img src="{{ $walletAddress->qr_code_url }}" alt="QR Code" class="img-fluid" style="max-width: 200px;">
                            @else
                                <h5>QR Code</h5>
                                <p class="text-muted">No QR code available</p>
                            @endif
                            
                            @if($walletAddress->instructions)
                                <h5 class="mt-4">Instructions</h5>
                                <p>{{ $walletAddress->instructions }}</p>
                            @endif
                        </div>
                    </div>
                    
                    <div class="mt-4">
                        <a href="{{ route('admin.wallet-addresses.edit', $walletAddress->id) }}" class="btn btn-primary">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        <a href="{{ route('admin.wallet-addresses.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to List
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
