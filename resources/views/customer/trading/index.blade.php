@extends('layouts.customer-layout')

@section('title', 'Trading - AI Trade App')
@section('page-title', 'Trading')

@section('content')
@if($activePackages->count() > 0)
<div class="row mb-4">
    <div class="col-12">
        <div class="card border-primary">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="bi bi-box-seam"></i> Active Packages</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    @foreach($activePackages as $package)
                    <div class="col-md-6 col-lg-4">
                        <div class="card h-100">
                            <div class="card-body">
                                <h6 class="card-title text-primary">
                                    <i class="bi bi-robot"></i> {{ $package['title'] }}
                                </h6>
                                <div class="mb-2">
                                    <span class="badge bg-success">Active</span>
                                </div>
                                <div class="mt-3">
                                    <strong>Available Bots:</strong>
                                    <span class="text-primary fs-4 ms-2">{{ $package['available_bots'] }}</span>
                                </div>
                                @if($package['type'] === 'Rent A Bot' && isset($package['plan_details']['allowed_trades']))
                                <div class="mt-2">
                                    <small class="text-muted">Allowed Trades: {{ $package['plan_details']['allowed_trades'] }}</small>
                                </div>
                                @elseif($package['type'] === 'Sharing Nexa' && isset($package['plan_details']['trades_per_day']))
                                <div class="mt-2">
                                    <small class="text-muted">Trades/Day: {{ $package['plan_details']['trades_per_day'] }}</small>
                                </div>
                                @endif
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

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-graph-up"></i> Trading History</h5>
            </div>
            <div class="card-body">
                @if($trades->count() > 0)
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Symbol</th>
                                <th>Side</th>
                                <th>Amount</th>
                                <th>Price</th>
                                <th>P&L</th>
                                <th>Status</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($trades as $trade)
                            <tr>
                                <td>{{ $trade->symbol }}</td>
                                <td>
                                    <span class="badge {{ $trade->side === 'buy' ? 'bg-success' : 'bg-danger' }}">
                                        {{ strtoupper($trade->side) }}
                                    </span>
                                </td>
                                <td>${{ number_format($trade->quantity, 4) }}</td>
                                <td>${{ number_format($trade->price, 2) }}</td>
                                <td class="{{ $trade->profit_loss >= 0 ? 'text-success' : 'text-danger' }}">
                                    ${{ number_format($trade->profit_loss, 2) }}
                                </td>
                                <td>
                                    <span class="badge bg-{{ $trade->status === 'filled' ? 'success' : ($trade->status === 'pending' ? 'warning' : 'secondary') }}">
                                        {{ ucfirst($trade->status) }}
                                    </span>
                                </td>
                                <td>{{ $trade->created_at->format('M d, Y H:i') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <div class="d-flex justify-content-center">
                    {{ $trades->links() }}
                </div>
                @else
                <div class="text-center py-5">
                    <i class="bi bi-graph-up display-1 text-muted"></i>
                    <h4 class="text-muted mt-3">No trades yet</h4>
                    <p class="text-muted">Start by creating an AI agent to begin trading.</p>
                    <a href="{{ route('customer.agents.create') }}" class="btn btn-primary">
                        <i class="bi bi-robot"></i> Create AI Agent
                    </a>
                </div>
                @endif
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-robot"></i> Active Agents</h5>
            </div>
            <div class="card-body">
                @if($agents->count() > 0)
                    @foreach($agents as $agent)
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h6 class="mb-0">{{ $agent->name }}</h6>
                            <small class="text-muted">{{ ucfirst($agent->strategy) }}</small>
                        </div>
                        <div class="text-end">
                            <div class="text-success">${{ number_format($agent->current_balance, 2) }}</div>
                            <small class="text-muted">Balance</small>
                        </div>
                    </div>
                    @endforeach
                @else
                <div class="text-center py-3">
                    <i class="bi bi-robot display-4 text-muted"></i>
                    <p class="text-muted mt-2">No active agents</p>
                    <a href="{{ route('customer.agents.create') }}" class="btn btn-primary btn-sm">
                        Create Agent
                    </a>
                </div>
                @endif
            </div>
        </div>
        
        <div class="card mt-3">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-lightning"></i> Quick Actions</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('customer.agents.create') }}" class="btn btn-primary">
                        <i class="bi bi-robot"></i> Create AI Agent
                    </a>
                    <a href="{{ route('customer.market.index') }}" class="btn btn-info">
                        <i class="bi bi-currency-exchange"></i> View Market
                    </a>
                    <a href="{{ route('customer.wallet.index') }}" class="btn btn-success">
                        <i class="bi bi-wallet2"></i> Manage Wallet
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
