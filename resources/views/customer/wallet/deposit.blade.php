@extends('layouts.customer-layout')

@section('title', 'Deposit - AI Trade App')
@section('page-title', 'Deposit Funds')

@push('styles')
<style>
    .deposit-card {
        background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
        color: white;
        border-radius: 15px;
        padding: 2rem;
        margin-bottom: 2rem;
    }
    
    .deposit-card h2 {
        font-size: 2.5rem;
        font-weight: bold;
        margin: 0;
    }
    
    .deposit-card p {
        margin: 0;
        opacity: 0.9;
    }
    
    .qr-code-container {
        text-align: center;
        background: white;
        padding: 2rem;
        border-radius: 15px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        margin-bottom: 2rem;
    }
    
    .address-container {
        background: #f8f9fa;
        border: 2px dashed #dee2e6;
        border-radius: 10px;
        padding: 1rem;
        margin: 1rem 0;
        word-break: break-all;
    }
    
    .instruction-card {
        background: white;
        border-radius: 15px;
        padding: 1.5rem;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        margin-bottom: 1rem;
    }
    
    .instruction-card h6 {
        color: var(--primary-color);
        font-weight: bold;
    }
    
    .instruction-card ol {
        margin: 0;
        padding-left: 1.2rem;
    }
    
    .instruction-card li {
        margin-bottom: 0.5rem;
    }
</style>
@endpush

@section('content')
<!-- Deposit Header -->
<div class="row">
    <div class="col-12 mb-4">
        <div class="deposit-card">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h2>Deposit Funds</h2>
                    <p>Add funds to your wallet to start trading</p>
                </div>
                <div class="col-md-4 text-end">
                    <i class="bi bi-plus-circle" style="font-size: 4rem; opacity: 0.3;"></i>
                </div>
            </div>
        </div>
    </div>
</div>

@if($walletAddresses->count() > 0)
    @foreach($walletAddresses as $walletAddress)
    <div class="row mb-4">
        <!-- Deposit Address & QR Code -->
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-wallet2"></i> Deposit {{ $walletAddress->name }} ({{ $walletAddress->symbol }})
                        @if($walletAddress->network)
                            <span class="badge bg-info ms-2">{{ $walletAddress->network }}</span>
                        @endif
                    </h5>
                </div>
                <div class="card-body">
                    <div class="address-container">
                        <code id="depositAddress{{ $walletAddress->id }}">{{ $walletAddress->wallet_address }}</code>
                    </div>
                    <button class="btn btn-primary" onclick="copyToClipboard('{{ $walletAddress->wallet_address }}', 'depositAddress{{ $walletAddress->id }}')">
                        <i class="bi bi-copy"></i> Copy Address
                    </button>
                    
                    <div class="mt-3">
                        <small class="text-muted">
                            <i class="bi bi-info-circle"></i>
                            @if($walletAddress->instructions)
                                {{ $walletAddress->instructions }}
                            @else
                                Only send {{ $walletAddress->symbol }} to this address. Other cryptocurrencies will be lost.
                            @endif
                        </small>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-qr-code"></i> QR Code</h5>
                </div>
                <div class="card-body">
                    <div class="qr-code-container">
                        @if($walletAddress->qr_code_image)
                            <img src="{{ $walletAddress->qr_code_url }}" alt="Deposit QR Code for {{ $walletAddress->name }}" class="img-fluid">
                        @else
                            <div class="text-center text-muted">
                                <i class="bi bi-qr-code" style="font-size: 3rem;"></i>
                                <p class="mt-2">QR Code not available</p>
                            </div>
                        @endif
                        <p class="mt-3 text-muted">Scan with your wallet app</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endforeach
@else
    <div class="row">
        <div class="col-12">
            <div class="alert alert-warning text-center">
                <h5><i class="bi bi-exclamation-triangle"></i> No Deposit Addresses Available</h5>
                <p class="mb-0">Please contact support to set up deposit addresses.</p>
            </div>
        </div>
    </div>
@endif

<!-- Instructions -->
<div class="row">
    <div class="col-12">
        <div class="instruction-card">
            <h6><i class="bi bi-list-ol"></i> How to Deposit</h6>
            <ol>
                <li>Copy the deposit address above or scan the QR code</li>
                <li>Open your USDT wallet (TRC20 network)</li>
                <li>Send USDT to the provided address</li>
                <li>Wait for network confirmation (usually 5-10 minutes)</li>
                <li>Your funds will appear in your wallet automatically</li>
            </ol>
        </div>
    </div>
</div>

<!-- Important Notes -->
<div class="row">
    <div class="col-12">
        <div class="alert alert-warning">
            <h6><i class="bi bi-exclamation-triangle"></i> Important Notes</h6>
            <ul class="mb-0">
                <li>Only send USDT on the TRC20 network</li>
                <li>Minimum deposit: 10 USDT</li>
                <li>Deposits are processed automatically</li>
                <li>Contact support if you don't see your deposit within 30 minutes</li>
            </ul>
        </div>
    </div>
</div>

<!-- Recent Deposits -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-clock-history"></i> Recent Deposits</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Confirmations</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($wallet->user->transactions()->where('type', 'deposit')->latest()->limit(5)->get() as $transaction)
                            <tr>
                                <td>
                                    <span class="text-success fw-bold">
                                        +${{ number_format($transaction->amount, 2) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $transaction->status === 'completed' ? 'success' : ($transaction->status === 'pending' ? 'warning' : 'secondary') }}">
                                        {{ ucfirst($transaction->status) }}
                                    </span>
                                </td>
                                <td>{{ $transaction->created_at->format('M d, Y H:i') }}</td>
                                <td>{{ $transaction->confirmations }}/3</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted">
                                    No deposits found
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function copyToClipboard(text, elementId) {
        navigator.clipboard.writeText(text).then(function() {
            // Show success message
            const btn = event.target;
            const originalText = btn.innerHTML;
            btn.innerHTML = '<i class="bi bi-check"></i> Copied!';
            btn.classList.remove('btn-primary');
            btn.classList.add('btn-success');
            
            setTimeout(function() {
                btn.innerHTML = originalText;
                btn.classList.remove('btn-success');
                btn.classList.add('btn-primary');
            }, 2000);
        }, function(err) {
            console.error('Could not copy text: ', err);
        });
    }
    
    // Auto-refresh to check for new deposits
    setInterval(function() {
        location.reload();
    }, 30000); // Refresh every 30 seconds
</script>
@endpush
