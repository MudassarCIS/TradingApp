@extends('layouts.customer-layout')

@section('title', 'Withdraw - AI Trade App')
@section('page-title', 'Withdraw Funds')

@push('styles')
<style>
    .withdraw-card {
        background: linear-gradient(135deg, #ff416c 0%, #ff4b2b 100%);
        color: white;
        border-radius: 15px;
        padding: 2rem;
        margin-bottom: 2rem;
    }
    
    .withdraw-card h2 {
        font-size: 2.5rem;
        font-weight: bold;
        margin: 0;
    }
    
    .withdraw-card p {
        margin: 0;
        opacity: 0.9;
    }
    
    .balance-card {
        background: white;
        border-radius: 15px;
        padding: 1.5rem;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        text-align: center;
        margin-bottom: 2rem;
    }
    
    .balance-card h4 {
        font-size: 2rem;
        font-weight: bold;
        color: var(--primary-color);
        margin: 0;
    }
    
    .balance-card p {
        color: #666;
        margin: 0;
    }
    
    .form-card {
        background: white;
        border-radius: 15px;
        padding: 2rem;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }
    
    .form-label {
        font-weight: 600;
        color: #333;
    }
    
    .form-control {
        border-radius: 10px;
        border: 2px solid #e9ecef;
        padding: 12px 15px;
        transition: all 0.3s ease;
    }
    
    .form-control:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
    }
    
    .btn-withdraw {
        background: linear-gradient(135deg, #ff416c 0%, #ff4b2b 100%);
        border: none;
        border-radius: 10px;
        padding: 12px 30px;
        font-weight: 600;
        color: white;
        transition: all 0.3s ease;
    }
    
    .btn-withdraw:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.2);
    }
    
    .fee-info {
        background: #f8f9fa;
        border-radius: 10px;
        padding: 1rem;
        margin-top: 1rem;
    }
    
    .fee-info h6 {
        color: var(--primary-color);
        margin-bottom: 0.5rem;
    }
    
    .fee-info ul {
        margin: 0;
        padding-left: 1.2rem;
    }
    
    .fee-info li {
        margin-bottom: 0.25rem;
        font-size: 0.9rem;
    }
</style>
@endpush

@section('content')
<!-- Withdraw Header -->
<div class="row">
    <div class="col-12 mb-4">
        <div class="withdraw-card">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h2>Withdraw USDT</h2>
                    <p>Withdraw funds from your wallet</p>
                </div>
                <div class="col-md-4 text-end">
                    <i class="bi bi-dash-circle" style="font-size: 4rem; opacity: 0.3;"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Balance Info -->
    <div class="col-md-4 mb-4">
        <div class="balance-card">
            <h4>${{ number_format($availableBalance ?? 0, 2) }}</h4>
            <p>Available Balance</p>
        </div>
    </div>
    
    <div class="col-md-4 mb-4">
        <div class="balance-card">
            <h4>$0.00</h4>
            <p>Locked Balance</p>
        </div>
    </div>
    
    <div class="col-md-4 mb-4">
        <div class="balance-card">
            <h4>${{ number_format($totalWithdrawals ?? 0, 2) }}</h4>
            <p>Total Withdrawn</p>
        </div>
    </div>
</div>

<div class="row">
    <!-- Withdrawal Form -->
    <div class="col-md-8 mb-4">
        <div class="form-card">
            <h5 class="mb-4"><i class="bi bi-arrow-up-right"></i> Withdrawal Form</h5>
            
            @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            
            <form method="POST" action="{{ route('customer.wallet.withdraw.process') }}">
                @csrf
                
                <div class="mb-3">
                    <label for="amount" class="form-label">Amount (USDT)</label>
                    <input type="number" 
                           class="form-control @error('amount') is-invalid @enderror" 
                           id="amount" 
                           name="amount" 
                           step="0.01" 
                           min="10" 
                           max="{{ $availableBalance ?? 0 }}" 
                           value="{{ old('amount') }}" 
                           required>
                    <div class="form-text">
                        Minimum: 10 USDT | Maximum: {{ number_format($availableBalance ?? 0, 2) }} USDT
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="address" class="form-label">Withdrawal Address</label>
                    <input type="text" 
                           class="form-control @error('address') is-invalid @enderror" 
                           id="address" 
                           name="address" 
                           placeholder="Enter USDT (TRC20) address" 
                           value="{{ old('address') }}" 
                           required>
                    <div class="form-text">
                        Make sure this is a valid USDT (TRC20) address
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="transaction_password" class="form-label">Transaction Password</label>
                    <input type="password" 
                           class="form-control @error('transaction_password') is-invalid @enderror" 
                           id="transaction_password" 
                           name="transaction_password" 
                           placeholder="Enter your transaction password" 
                           required>
                </div>
                
                <div class="fee-info">
                    <h6><i class="bi bi-info-circle"></i> Withdrawal Information</h6>
                    <ul>
                        <li>Withdrawal fee: 5 USDT</li>
                        <li>Processing time: 5-30 minutes</li>
                        <li>Minimum withdrawal: 10 USDT</li>
                        <li>Network: TRC20 (Tron)</li>
                    </ul>
                </div>
                
                <div class="d-grid">
                    <button type="submit" class="btn btn-withdraw btn-lg">
                        <i class="bi bi-arrow-up-right"></i> Process Withdrawal
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Withdrawal History -->
    <div class="col-md-4 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-clock-history"></i> Recent Withdrawals</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($wallet->user->transactions()->where('type', 'withdrawal')->latest()->limit(5)->get() as $transaction)
                            <tr>
                                <td>
                                    <span class="text-danger fw-bold">
                                        -${{ number_format($transaction->amount, 2) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $transaction->status === 'completed' ? 'success' : ($transaction->status === 'pending' ? 'warning' : 'secondary') }}">
                                        {{ ucfirst($transaction->status) }}
                                    </span>
                                </td>
                                <td>{{ $transaction->created_at->format('M d, Y') }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="text-center text-muted">
                                    No withdrawals found
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

<!-- Security Notice -->
<div class="row">
    <div class="col-12">
        <div class="alert alert-warning">
            <h6><i class="bi bi-shield-exclamation"></i> Security Notice</h6>
            <ul class="mb-0">
                <li>Double-check the withdrawal address before confirming</li>
                <li>Only withdraw to addresses you control</li>
                <li>Withdrawals are processed manually for security</li>
                <li>Contact support if you don't receive your funds within 1 hour</li>
            </ul>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Real-time balance calculation
    document.getElementById('amount').addEventListener('input', function() {
        const amount = parseFloat(this.value) || 0;
        const fee = 5;
        const total = amount + fee;
        const available = {{ $availableBalance ?? 0 }};
        
        if (amount > available) {
            this.setCustomValidity('Amount exceeds available balance');
        } else if (amount < 10) {
            this.setCustomValidity('Minimum withdrawal is 10 USDT');
        } else {
            this.setCustomValidity('');
        }
    });
    
    // Address validation
    document.getElementById('address').addEventListener('input', function() {
        const address = this.value;
        if (address.length > 0 && !address.startsWith('T')) {
            this.setCustomValidity('Please enter a valid TRC20 address (starts with T)');
        } else {
            this.setCustomValidity('');
        }
    });
</script>
@endpush
