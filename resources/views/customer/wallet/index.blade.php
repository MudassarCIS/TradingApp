@extends('layouts.customer-layout')

@section('title', 'Wallet - AI Trade App')
@section('page-title', 'My Wallet')

@push('styles')
<style>
    .wallet-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 15px;
        padding: 2rem;
        margin-bottom: 2rem;
    }
    
    .wallet-card h2 {
        font-size: 2.5rem;
        font-weight: bold;
        margin: 0;
    }
    
    .wallet-card p {
        margin: 0;
        opacity: 0.9;
    }
    
    .action-card {
        border: none;
        border-radius: 15px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        transition: transform 0.3s ease;
    }
    
    .action-card:hover {
        transform: translateY(-5px);
    }
    
    .action-card .card-body {
        padding: 2rem;
        text-align: center;
    }
    
    .action-card i {
        font-size: 3rem;
        margin-bottom: 1rem;
    }
    
    .deposit-card {
        background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
        color: white;
    }
    
    .withdraw-card {
        background: linear-gradient(135deg, #ff416c 0%, #ff4b2b 100%);
        color: white;
    }
    
    .history-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }
</style>
@endpush

@section('content')
<div class="row">
    <!-- Wallet Balance -->
    <div class="col-12 mb-4">
        <div class="wallet-card">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h2>${{ number_format($wallets->where('currency', 'USDT')->first()->balance ?? 0, 2) }}</h2>
                    <p>Available Balance (USDT)</p>
                </div>
                <div class="col-md-4 text-end">
                    <i class="bi bi-wallet2" style="font-size: 4rem; opacity: 0.3;"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Quick Actions -->
    <div class="col-md-4 mb-4">
        <div class="card action-card deposit-card">
            <div class="card-body">
                <i class="bi bi-plus-circle"></i>
                <h5>Deposit Funds</h5>
                <p>Add money to your wallet</p>
                <a href="{{ route('customer.wallet.deposit') }}" class="btn btn-light btn-lg">
                    <i class="bi bi-plus"></i> Deposit
                </a>
            </div>
        </div>
    </div>
    
    <div class="col-md-4 mb-4">
        <div class="card action-card withdraw-card">
            <div class="card-body">
                <i class="bi bi-dash-circle"></i>
                <h5>Withdraw Funds</h5>
                <p>Withdraw money from your wallet</p>
                <a href="{{ route('customer.wallet.withdraw') }}" class="btn btn-light btn-lg">
                    <i class="bi bi-dash"></i> Withdraw
                </a>
            </div>
        </div>
    </div>
    
    <div class="col-md-4 mb-4">
        <div class="card action-card history-card">
            <div class="card-body">
                <i class="bi bi-clock-history"></i>
                <h5>Transaction History</h5>
                <p>View all your transactions</p>
                <a href="{{ route('customer.wallet.history') }}" class="btn btn-light btn-lg">
                    <i class="bi bi-list"></i> View History
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Wallet Details -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-wallet2"></i> Wallet Details</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Currency</th>
                                <th>Available Balance</th>
                                <th>Locked Balance</th>
                                <th>Total Deposited</th>
                                <th>Total Withdrawn</th>
                                <th>Total Profit</th>
                                <th>Total Loss</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($wallets as $wallet)
                            <tr>
                                <td>
                                    <strong>{{ $wallet->currency }}</strong>
                                </td>
                                <td>
                                    <span class="text-success fw-bold">
                                        ${{ number_format($wallet->available_balance, 2) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="text-warning">
                                        ${{ number_format($wallet->locked_balance, 2) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="text-info">
                                        ${{ number_format($wallet->total_deposited, 2) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="text-danger">
                                        ${{ number_format($wallet->total_withdrawn, 2) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="text-success">
                                        ${{ number_format($wallet->total_profit, 2) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="text-danger">
                                        ${{ number_format($wallet->total_loss, 2) }}
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted">
                                    No wallet data available
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

<!-- Recent Transactions -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-clock-history"></i> Recent Transactions</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Type</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($wallets->first()->user->transactions()->latest()->limit(5)->get() as $transaction)
                            <tr>
                                <td>
                                    <span class="badge bg-{{ $transaction->type === 'deposit' ? 'success' : ($transaction->type === 'withdrawal' ? 'danger' : 'info') }}">
                                        {{ ucfirst($transaction->type) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="{{ $transaction->type === 'deposit' ? 'text-success' : 'text-danger' }}">
                                        {{ $transaction->type === 'deposit' ? '+' : '-' }}${{ number_format($transaction->amount, 2) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $transaction->status === 'completed' ? 'success' : ($transaction->status === 'pending' ? 'warning' : 'secondary') }}">
                                        {{ ucfirst($transaction->status) }}
                                    </span>
                                </td>
                                <td>{{ $transaction->created_at->format('M d, Y H:i') }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted">
                                    No transactions found
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="text-center mt-3">
                    <a href="{{ route('customer.wallet.history') }}" class="btn btn-primary">
                        View All Transactions
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Auto-refresh wallet data every 30 seconds
    setInterval(function() {
        // You can add AJAX calls here to refresh wallet data
        console.log('Refreshing wallet data...');
    }, 30000);
</script>
@endpush
