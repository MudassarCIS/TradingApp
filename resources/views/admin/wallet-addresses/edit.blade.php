@extends('layouts.admin-layout')

@section('title', 'Edit Wallet Address')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Edit Wallet Address: {{ $walletAddress->name }}</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.wallet-addresses.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Wallet Addresses
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.wallet-addresses.update', $walletAddress->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="name">Cryptocurrency Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                           id="name" name="name" value="{{ old('name', $walletAddress->name) }}" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="symbol">Symbol <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('symbol') is-invalid @enderror" 
                                           id="symbol" name="symbol" value="{{ old('symbol', $walletAddress->symbol) }}" required>
                                    @error('symbol')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group mb-3">
                            <label for="wallet_address">Wallet Address <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('wallet_address') is-invalid @enderror" 
                                   id="wallet_address" name="wallet_address" value="{{ old('wallet_address', $walletAddress->wallet_address) }}" required>
                            @error('wallet_address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="network">Network (Optional)</label>
                                    <select class="form-control @error('network') is-invalid @enderror" 
                                            id="network" name="network">
                                        <option value="">Select Network</option>
                                        <option value="TRC20" {{ old('network', $walletAddress->network) == 'TRC20' ? 'selected' : '' }}>TRC20</option>
                                        <option value="ERC20" {{ old('network', $walletAddress->network) == 'ERC20' ? 'selected' : '' }}>ERC20</option>
                                        <option value="BEP20" {{ old('network', $walletAddress->network) == 'BEP20' ? 'selected' : '' }}>BEP20</option>
                                        <option value="BTC" {{ old('network', $walletAddress->network) == 'BTC' ? 'selected' : '' }}>BTC</option>
                                        <option value="ETH" {{ old('network', $walletAddress->network) == 'ETH' ? 'selected' : '' }}>ETH</option>
                                    </select>
                                    @error('network')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="sort_order">Sort Order</label>
                                    <input type="number" class="form-control @error('sort_order') is-invalid @enderror" 
                                           id="sort_order" name="sort_order" value="{{ old('sort_order', $walletAddress->sort_order) }}">
                                    @error('sort_order')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        @if($walletAddress->qr_code_image)
                        <div class="form-group mb-3">
                            <label>Current QR Code</label>
                            <div class="d-flex align-items-center">
                                <img src="{{ $walletAddress->qr_code_url }}" alt="Current QR Code" style="width: 100px; height: 100px;" class="me-3">
                                <div>
                                    <p class="mb-1"><strong>Current QR Code</strong></p>
                                    <small class="text-muted">Upload a new image to replace or check "Regenerate QR Code" to auto-generate</small>
                                </div>
                            </div>
                        </div>
                        @endif

                        <div class="form-group mb-3">
                            <label for="qr_code_image">New QR Code Image (Optional)</label>
                            <input type="file" class="form-control @error('qr_code_image') is-invalid @enderror" 
                                   id="qr_code_image" name="qr_code_image" accept="image/*">
                            <small class="form-text text-muted">Upload a new QR code image or leave empty to keep current one.</small>
                            @error('qr_code_image')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="regenerate_qr" name="regenerate_qr" value="1">
                                <label class="form-check-label" for="regenerate_qr">
                                    Regenerate QR Code from wallet address
                                </label>
                            </div>
                        </div>

                        <div class="form-group mb-3">
                            <label for="instructions">Instructions (Optional)</label>
                            <textarea class="form-control @error('instructions') is-invalid @enderror" 
                                      id="instructions" name="instructions" rows="3">{{ old('instructions', $walletAddress->instructions) }}</textarea>
                            <small class="form-text text-muted">Additional instructions for users (e.g., "Only send BTC to this address").</small>
                            @error('instructions')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="is_active" name="is_active" value="1" 
                                       {{ old('is_active', $walletAddress->is_active) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">
                                    Active (Show to customers)
                                </label>
                            </div>
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Update Wallet Address
                            </button>
                            <a href="{{ route('admin.wallet-addresses.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
