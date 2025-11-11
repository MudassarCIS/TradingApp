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
    
    .quick-action-btn {
        height: 100%;
        min-height: 100px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        padding: 1rem;
        transition: transform 0.2s ease;
    }
    
    .quick-action-btn:hover {
        transform: translateY(-2px);
    }
    
    .quick-action-btn i {
        font-size: 1.5rem;
        margin-bottom: 0.5rem;
    }
    
    .quick-action-btn small {
        font-size: 0.75rem;
        margin-top: 0.5rem;
    }
</style>
@endpush

@section('content')
<div class="row">
    <!-- Statistics Cards -->
    <div class="col-md-3 mb-4">
        <div class="stats-card balance-card">
            <h3>${{ number_format($availableBalance ?? 0, 2) }}</h3>
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
    <div class="col-md-6 mb-4">
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
    
    <!-- Referral Stats -->
    <div class="col-md-6 mb-4">
        <div class="card">
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

<!-- Quick Actions -->
<div class="row">
    <div class="col-12 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-lightning"></i> Quick Actions</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3 col-sm-6">
                        <a href="{{ route('customer.wallet.deposit') }}" class="btn btn-primary w-100 quick-action-btn">
                            <i class="bi bi-plus-circle"></i>
                            <span>Deposit Funds</span>
                            <small class="badge bg-light text-dark mt-1">{{ $totalDeposits }} Total</small>
                        </a>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <a href="{{ route('customer.invoices.index') }}" class="btn btn-success w-100 quick-action-btn">
                            <i class="bi bi-file-earmark-text"></i>
                            <span>Invoices</span>
                            <small class="badge bg-light text-dark mt-1">
                                {{ $totalInvoices }} Total
                                @if($unpaidInvoices > 0)
                                <span class="badge bg-danger ms-1">{{ $unpaidInvoices }} Unpaid</span>
                                @endif
                            </small>
                        </a>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <a href="{{ route('customer.wallet.index') }}" class="btn btn-info w-100 quick-action-btn">
                            <i class="bi bi-wallet2"></i>
                            <span>Wallet</span>
                            <small class="badge bg-light text-dark mt-1">{{ $totalTransactions }} Transactions</small>
                        </a>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <a href="{{ route('customer.agents.create') }}" class="btn btn-warning w-100 quick-action-btn">
                            <i class="bi bi-robot"></i>
                            <span>Create AI Agent</span>
                            <small class="badge bg-light text-dark mt-1">{{ $activeAgents }} Active</small>
                        </a>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <a href="{{ route('customer.trading.index') }}" class="btn btn-secondary w-100 quick-action-btn">
                            <i class="bi bi-graph-up"></i>
                            <span>View Trading</span>
                            <small class="badge bg-light text-dark mt-1">{{ $totalTrades }} Trades</small>
                        </a>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <a href="{{ route('customer.referrals.index') }}" class="btn btn-dark w-100 quick-action-btn">
                            <i class="bi bi-people"></i>
                            <span>Referrals</span>
                            <small class="badge bg-light text-dark mt-1">{{ $referralCount }} Total</small>
                        </a>
                    </div>
                    @if($pendingDeposits > 0)
                    <div class="col-md-3 col-sm-6">
                        <a href="{{ route('customer.wallet.index') }}" class="btn btn-danger w-100 quick-action-btn">
                            <i class="bi bi-clock-history"></i>
                            <span>Pending Deposits</span>
                            <small class="badge bg-light text-dark mt-1">{{ $pendingDeposits }} Pending</small>
                        </a>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>


<!-- Active Packages -->
@if($activePackages->count() > 0)
<div class="row">
    <div class="col-12 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-box-seam"></i> Active Packages</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    @foreach($activePackages as $package)
                    <div class="col-md-6">
                        <div class="card border-primary">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h5 class="card-title text-primary mb-2">
                                            <i class="bi bi-robot"></i> {{ $package['title'] }}
                                        </h5>
                                        <div class="mb-2">
                                            <span class="badge bg-success">Active</span>
                                        </div>
                                        <div>
                                            <strong>Available Bots:</strong> 
                                            <span class="text-primary fs-5">{{ $package['available_bots'] }}</span>
                                        </div>
                                        @if($package['type'] === 'Rent A Bot')
                                            @if(isset($package['plan_details']['allowed_trades']))
                                            <div class="mt-2">
                                                <small class="text-muted">
                                                    Allowed Trades: {{ $package['plan_details']['allowed_trades'] }}
                                                </small>
                                            </div>
                                            @endif
                                            @if(isset($package['plan_details']['validity']))
                                            <div>
                                                <small class="text-muted">
                                                    Validity: {{ ucfirst($package['plan_details']['validity']) }}
                                                </small>
                                            </div>
                                            @endif
                                        @elseif($package['type'] === 'Sharing Nexa')
                                            @if(isset($package['plan_details']['trades_per_day']))
                                            <div class="mt-2">
                                                <small class="text-muted">
                                                    Trades/Day: {{ $package['plan_details']['trades_per_day'] }}
                                                </small>
                                            </div>
                                            @endif
                                        @endif
                                    </div>
                                    <div class="text-end">
                                        <small class="text-muted">
                                            Activated: {{ $package['created_at']->format('M d, Y') }}
                                        </small>
                                    </div>
                                </div>
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
