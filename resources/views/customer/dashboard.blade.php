@extends('layouts.customer-layout')

@section('title', 'Dashboard - AI Trade App')
@section('page-title', 'Dashboard')

@push('styles')
<style>
    .stats-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 15px;
        padding: 1.5rem;
        margin-bottom: 1rem;
    }
    
    .stats-card h3 {
        font-size: 2.5rem;
        font-weight: bold;
        margin: 0;
    }
    
    .stats-card p {
        margin: 0;
        opacity: 0.9;
        font-size: 0.9rem;
    }
    
    .profit-card {
        background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
    }
    
    .loss-card {
        background: linear-gradient(135deg, #ff416c 0%, #ff4b2b 100%);
    }
    
    .balance-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }
    
    .trades-card {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    }
    
    .package-card {
        border: none;
        border-radius: 15px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        transition: transform 0.3s ease;
    }
    
    .package-card:hover {
        transform: translateY(-5px);
    }
    
    .package-card .card-header {
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        color: white;
        border-radius: 15px 15px 0 0 !important;
    }
    
    .recent-activity {
        max-height: 400px;
        overflow-y: auto;
    }
    
    .activity-item {
        padding: 10px 0;
        border-bottom: 1px solid #eee;
    }
    
    .activity-item:last-child {
        border-bottom: none;
    }
</style>
@endpush

@section('content')
<div class="row">
    <!-- Statistics Cards -->
    <div class="col-md-3 mb-4">
        <div class="stats-card balance-card">
            <h3>${{ number_format($wallet->balance ?? 0, 2) }}</h3>
            <p>Available Balance</p>
        </div>
    </div>
    
    <div class="col-md-3 mb-4">
        <div class="stats-card profit-card">
            <h3>${{ number_format($totalProfit, 2) }}</h3>
            <p>Total Profit</p>
        </div>
    </div>
    
    <div class="col-md-3 mb-4">
        <div class="stats-card trades-card">
            <h3>{{ $totalTrades }}</h3>
            <p>Total Trades</p>
        </div>
    </div>
    
    <div class="col-md-3 mb-4">
        <div class="stats-card">
            <h3>{{ $activeAgents }}</h3>
            <p>Active Agents</p>
        </div>
    </div>
</div>

<div class="row">
    <!-- Trading Overview -->
    <div class="col-md-8 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-graph-up"></i> Trading Overview</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6>Active Trades</h6>
                        <h3 class="text-primary">{{ $activeTrades }}</h3>
                    </div>
                    <div class="col-md-6">
                        <h6>Profitable Trades</h6>
                        <h3 class="text-success">{{ $profitableTrades }}</h3>
                    </div>
                </div>
                
                @if($recentTrades->count() > 0)
                <hr>
                <h6>Recent Trades</h6>
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Symbol</th>
                                <th>Side</th>
                                <th>Amount</th>
                                <th>P&L</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentTrades as $trade)
                            <tr>
                                <td>{{ $trade->symbol }}</td>
                                <td>
                                    <span class="badge {{ $trade->side === 'buy' ? 'bg-success' : 'bg-danger' }}">
                                        {{ strtoupper($trade->side) }}
                                    </span>
                                </td>
                                <td>${{ number_format($trade->quantity, 4) }}</td>
                                <td class="{{ $trade->profit_loss >= 0 ? 'text-success' : 'text-danger' }}">
                                    ${{ number_format($trade->profit_loss, 2) }}
                                </td>
                                <td>
                                    <span class="badge bg-{{ $trade->status === 'filled' ? 'success' : ($trade->status === 'pending' ? 'warning' : 'secondary') }}">
                                        {{ ucfirst($trade->status) }}
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <p class="text-muted">No recent trades found.</p>
                @endif
            </div>
        </div>
    </div>
    
    <!-- Quick Actions -->
    <div class="col-md-4 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-lightning"></i> Quick Actions</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('customer.wallet.deposit') }}" class="btn btn-primary">
                        <i class="bi bi-plus-circle"></i> Deposit Funds
                    </a>
                    <a href="{{ route('customer.agents.create') }}" class="btn btn-success">
                        <i class="bi bi-robot"></i> Create AI Agent
                    </a>
                    <a href="{{ route('customer.trading.index') }}" class="btn btn-info">
                        <i class="bi bi-graph-up"></i> View Trading
                    </a>
                    <a href="{{ route('customer.referrals.index') }}" class="btn btn-warning">
                        <i class="bi bi-people"></i> Referrals
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Referral Stats -->
        <div class="card mt-3">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-people"></i> Referral Stats</h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-6">
                        <h4 class="text-primary">{{ $referralCount }}</h4>
                        <small>Referrals</small>
                    </div>
                    <div class="col-6">
                        <h4 class="text-success">${{ number_format($referralEarnings, 2) }}</h4>
                        <small>Earnings</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Available Packages -->
@if($packages->count() > 0)
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-gift"></i> Available Packages</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    @foreach($packages as $package)
                    <div class="col-md-4 mb-3">
                        <div class="package-card card">
                            <div class="card-header">
                                <h6 class="mb-0">{{ $package->name }}</h6>
                            </div>
                            <div class="card-body">
                                <h4 class="text-primary">${{ number_format($package->price, 2) }}</h4>
                                <p class="text-muted">{{ $package->description }}</p>
                                <ul class="list-unstyled">
                                    <li><i class="bi bi-check text-success"></i> Min: ${{ number_format($package->min_investment, 2) }}</li>
                                    <li><i class="bi bi-check text-success"></i> {{ $package->daily_return_rate }}% daily return</li>
                                    <li><i class="bi bi-check text-success"></i> {{ $package->duration_days }} days duration</li>
                                </ul>
                                <button class="btn btn-primary btn-sm w-100">Invest Now</button>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endif

<!-- Recent Transactions -->
@if($recentTransactions->count() > 0)
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-clock-history"></i> Recent Transactions</h5>
            </div>
            <div class="card-body">
                <div class="recent-activity">
                    @foreach($recentTransactions as $transaction)
                    <div class="activity-item">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <strong>{{ ucfirst($transaction->type) }}</strong>
                                <br>
                                <small class="text-muted">{{ $transaction->created_at->format('M d, Y H:i') }}</small>
                            </div>
                            <div class="text-end">
                                <span class="badge bg-{{ $transaction->status === 'completed' ? 'success' : ($transaction->status === 'pending' ? 'warning' : 'secondary') }}">
                                    {{ ucfirst($transaction->status) }}
                                </span>
                                <br>
                                <strong class="{{ $transaction->type === 'deposit' ? 'text-success' : 'text-danger' }}">
                                    {{ $transaction->type === 'deposit' ? '+' : '-' }}${{ number_format($transaction->amount, 2) }}
                                </strong>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endif
@endsection

@push('scripts')
<script>
    // Auto-refresh dashboard data every 30 seconds
    setInterval(function() {
        // You can add AJAX calls here to refresh data
        console.log('Refreshing dashboard data...');
    }, 30000);
</script>
@endpush
