@extends('layouts.admin-layout')

@section('title', 'Admin Dashboard - AI Trade App')
@section('page-title', 'Admin Dashboard')

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
    }
    
    .revenue-card {
        background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
    }
    
    .users-card {
        background: linear-gradient(135deg, #ff416c 0%, #ff4b2b 100%);
    }
    
    .trades-card {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    }
    
    .agents-card {
        background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
    }
    
    .chart-container {
        position: relative;
        height: 300px;
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
        min-height: 90px;
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
        display: block;
    }
</style>
@endpush

@section('content')
<!-- Statistics Cards -->
<div class="row">
    <div class="col-md-3 mb-4">
        <div class="stats-card users-card">
            <h3>{{ $totalUsers }}</h3>
            <p>Total Users</p>
            <small>{{ $activeUsers }} active</small>
        </div>
    </div>
    
    <div class="col-md-3 mb-4">
        <div class="stats-card trades-card">
            <h3>{{ $totalTrades }}</h3>
            <p>Total Trades</p>
            <small>{{ $activeTrades }} active</small>
        </div>
    </div>
    
    <div class="col-md-3 mb-4">
        <div class="stats-card revenue-card">
            <h3>${{ number_format($adminProfit, 2) }}</h3>
            <p>Admin Profit</p>
            <small>50% of total profit</small>
        </div>
    </div>
    
    <div class="col-md-3 mb-4">
        <div class="stats-card agents-card">
            <h3>{{ $totalAgents }}</h3>
            <p>AI Agents</p>
            <small>{{ $activeAgents }} active</small>
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
                    <div class="col-md-2 col-sm-4">
                        <a href="{{ route('admin.users.index') }}" class="btn btn-primary w-100 quick-action-btn">
                            <i class="bi bi-people"></i>
                            <span>Manage Users</span>
                            <small class="badge bg-light text-dark mt-1">{{ $totalUsersCount }} Total</small>
                        </a>
                    </div>
                    <div class="col-md-2 col-sm-4">
                        <a href="{{ route('admin.deposits.index') }}" class="btn btn-success w-100 quick-action-btn">
                            <i class="bi bi-wallet2"></i>
                            <span>All Deposits</span>
                            <small class="badge bg-light text-dark mt-1">
                                {{ $totalDepositsCount }} Total
                                @if($pendingDeposits > 0)
                                    <span class="badge bg-warning ms-1">{{ $pendingDeposits }} Pending</span>
                                @endif
                            </small>
                        </a>
                    </div>
                    <div class="col-md-2 col-sm-4">
                        <a href="{{ route('admin.transactions.index') }}" class="btn btn-info w-100 quick-action-btn">
                            <i class="bi bi-arrow-left-right"></i>
                            <span>View Transactions</span>
                            <small class="badge bg-light text-dark mt-1">{{ $totalTransactionsCount }} Total</small>
                        </a>
                    </div>
                    <div class="col-md-2 col-sm-4">
                        <a href="{{ route('admin.trades.index') }}" class="btn btn-warning w-100 quick-action-btn">
                            <i class="bi bi-graph-up"></i>
                            <span>View Trades</span>
                            <small class="badge bg-light text-dark mt-1">{{ $totalTradesCount }} Total</small>
                        </a>
                    </div>
                    <div class="col-md-2 col-sm-4">
                        <a href="{{ route('admin.agents.index') }}" class="btn btn-secondary w-100 quick-action-btn">
                            <i class="bi bi-robot"></i>
                            <span>Manage Agents</span>
                            <small class="badge bg-light text-dark mt-1">{{ $totalAgentsCount }} Total</small>
                        </a>
                    </div>
                    <div class="col-md-2 col-sm-4">
                        <a href="{{ route('admin.withdrawals.index') }}" class="btn btn-danger w-100 quick-action-btn">
                            <i class="bi bi-dash-circle"></i>
                            <span>All Withdrawals</span>
                            <small class="badge bg-light text-dark mt-1">
                                {{ $totalWithdrawalsCount }} Total
                                @if(isset($pendingWithdrawals) && $pendingWithdrawals > 0)
                                    <span class="badge bg-warning ms-1">{{ $pendingWithdrawals }} Pending</span>
                                @endif
                            </small>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Revenue Overview -->
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-graph-up"></i> Revenue Overview</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6>Total Deposits</h6>
                        <h3 class="text-success">${{ number_format($totalDeposits, 2) }}</h3>
                    </div>
                    <div class="col-md-6">
                        <h6>Total Withdrawals</h6>
                        <h3 class="text-danger">${{ number_format($totalWithdrawals, 2) }}</h3>
                    </div>
                </div>
                
                <hr>
                
                <div class="row">
                    <div class="col-md-6">
                        <h6>Total Profit</h6>
                        <h3 class="text-primary">${{ number_format($totalProfit, 2) }}</h3>
                    </div>
                    <div class="col-md-6">
                        <h6>Admin Share (50%)</h6>
                        <h3 class="text-warning">${{ number_format($adminProfit, 2) }}</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Pending Items -->
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-clock"></i> Pending Items</h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-4">
                        <h4 class="text-warning">{{ $pendingDeposits }}</h4>
                        <small>Pending Deposits</small>
                    </div>
                    <div class="col-4">
                        <h4 class="text-danger">{{ $pendingWithdrawals ?? 0 }}</h4>
                        <small>Pending Withdrawals</small>
                    </div>
                    <div class="col-4">
                        <h4 class="text-info">{{ $pendingMessages->count() }}</h4>
                        <small>Open Messages</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Activities -->
<div class="row">
    <!-- Recent Users -->
    <div class="col-md-4 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-people"></i> Recent Users</h5>
            </div>
            <div class="card-body">
                <div class="recent-activity">
                    @forelse($recentUsers as $user)
                    <div class="activity-item">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <strong>{{ $user->name }}</strong>
                                <br>
                                <small class="text-muted">{{ $user->email }}</small>
                            </div>
                            <div class="text-end">
                                <span class="badge bg-{{ $user->is_active ? 'success' : 'secondary' }}">
                                    {{ $user->is_active ? 'Active' : 'Inactive' }}
                                </span>
                                <br>
                                <small class="text-muted">{{ $user->created_at->format('M d, Y') }}</small>
                            </div>
                        </div>
                    </div>
                    @empty
                    <p class="text-muted">No recent users found.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
    
    <!-- Recent Trades -->
    <div class="col-md-4 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-graph-up"></i> Recent Trades</h5>
            </div>
            <div class="card-body">
                <div class="recent-activity">
                    @forelse($recentTrades as $trade)
                    <div class="activity-item">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <strong>{{ $trade->symbol }}</strong>
                                <br>
                                <small class="text-muted">{{ $trade->user->name }}</small>
                            </div>
                            <div class="text-end">
                                <span class="badge bg-{{ $trade->side === 'buy' ? 'success' : 'danger' }}">
                                    {{ strtoupper($trade->side) }}
                                </span>
                                <br>
                                <small class="{{ $trade->profit_loss >= 0 ? 'text-success' : 'text-danger' }}">
                                    ${{ number_format($trade->profit_loss, 2) }}
                                </small>
                            </div>
                        </div>
                    </div>
                    @empty
                    <p class="text-muted">No recent trades found.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
    
    <!-- Recent Withdrawals -->
    <div class="col-md-4 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-dash-circle"></i> Recent Withdrawals</h5>
            </div>
            <div class="card-body">
                <div class="recent-activity">
                    @forelse($recentWithdrawals ?? [] as $withdrawal)
                    <div class="activity-item">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <strong>{{ $withdrawal->withdrawal_id ?? 'N/A' }}</strong>
                                <br>
                                <small class="text-muted">{{ $withdrawal->user->name ?? 'N/A' }}</small>
                            </div>
                            <div class="text-end">
                                <span class="badge bg-{{ $withdrawal->status === 'completed' ? 'success' : ($withdrawal->status === 'pending' ? 'warning' : ($withdrawal->status === 'processing' ? 'info' : 'secondary')) }}">
                                    {{ ucfirst($withdrawal->status) }}
                                </span>
                                <br>
                                <small class="text-danger">
                                    ${{ number_format($withdrawal->amount, 2) }}
                                </small>
                            </div>
                        </div>
                    </div>
                    @empty
                    <p class="text-muted">No recent withdrawals found.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Pending Messages -->
@if($pendingMessages->count() > 0)
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-chat-dots"></i> Pending Support Messages</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Subject</th>
                                <th>Type</th>
                                <th>Priority</th>
                                <th>Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pendingMessages as $message)
                            <tr>
                                <td>{{ $message->user->name }}</td>
                                <td>{{ $message->subject }}</td>
                                <td>
                                    <span class="badge bg-info">{{ ucfirst($message->type) }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $message->priority === 'urgent' ? 'danger' : ($message->priority === 'high' ? 'warning' : 'secondary') }}">
                                        {{ ucfirst($message->priority) }}
                                    </span>
                                </td>
                                <td>{{ $message->created_at->format('M d, Y H:i') }}</td>
                                <td>
                                    <a href="#" class="btn btn-sm btn-primary">View</a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
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
        console.log('Refreshing admin dashboard data...');
    }, 30000);
</script>
@endpush
