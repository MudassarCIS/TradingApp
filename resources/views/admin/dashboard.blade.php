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

<div class="row">
    <!-- Revenue Overview -->
    <div class="col-md-8 mb-4">
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
    
    <!-- Quick Actions -->
    <div class="col-md-4 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-lightning"></i> Quick Actions</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('admin.users.index') }}" class="btn btn-primary">
                        <i class="bi bi-people"></i> Manage Users
                    </a>
                    <a href="{{ route('admin.transactions.index') }}" class="btn btn-success">
                        <i class="bi bi-arrow-left-right"></i> View Transactions
                    </a>
                    <a href="{{ route('admin.trades.index') }}" class="btn btn-info">
                        <i class="bi bi-graph-up"></i> View Trades
                    </a>
                    <a href="{{ route('admin.agents.index') }}" class="btn btn-warning">
                        <i class="bi bi-robot"></i> Manage Agents
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Pending Items -->
        <div class="card mt-3">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-clock"></i> Pending Items</h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-6">
                        <h4 class="text-warning">{{ $pendingTransactions }}</h4>
                        <small>Pending Transactions</small>
                    </div>
                    <div class="col-6">
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
    
    <!-- Recent Transactions -->
    <div class="col-md-4 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-arrow-left-right"></i> Recent Transactions</h5>
            </div>
            <div class="card-body">
                <div class="recent-activity">
                    @forelse($recentTransactions as $transaction)
                    <div class="activity-item">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <strong>{{ ucfirst($transaction->type) }}</strong>
                                <br>
                                <small class="text-muted">{{ $transaction->user->name }}</small>
                            </div>
                            <div class="text-end">
                                <span class="badge bg-{{ $transaction->status === 'completed' ? 'success' : ($transaction->status === 'pending' ? 'warning' : 'secondary') }}">
                                    {{ ucfirst($transaction->status) }}
                                </span>
                                <br>
                                <small class="{{ $transaction->type === 'deposit' ? 'text-success' : 'text-danger' }}">
                                    ${{ number_format($transaction->amount, 2) }}
                                </small>
                            </div>
                        </div>
                    </div>
                    @empty
                    <p class="text-muted">No recent transactions found.</p>
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
