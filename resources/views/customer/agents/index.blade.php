@extends('layouts.customer-layout')

@section('title', 'AI Agents - AI Trade App')
@section('page-title', 'AI Agents')

@section('content')
@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="bi bi-check-circle me-2"></i>
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <i class="bi bi-exclamation-triangle me-2"></i>
    {{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>All Bot Plans</h2>
    <a href="{{ route('customer.agents.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-circle"></i> Create New Agent
    </a>
</div>

@if($activeBots->count() > 0)
<!-- Active Bot Plans Section -->
<div class="mb-5">
    <h4 class="mb-4">
        <i class="bi bi-robot text-primary"></i> Your Bot Plans
        <span class="badge bg-primary ms-2">{{ $activeBots->count() }}</span>
    </h4>
    
    <div class="row">
        @foreach($activeBots as $bot)
        <div class="col-md-6 col-lg-4 mb-4">
            <div class="card h-100 shadow-sm border-0" style="border-left: 4px solid {{ $bot->buy_type === 'Rent A Bot' ? '#fd7e14' : '#0d6efd' }} !important;">
                <div class="card-header bg-white border-0 pb-0">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h5 class="mb-1 text-dark">
                                <i class="bi {{ $bot->buy_type === 'Rent A Bot' ? 'bi-robot' : 'bi-share' }} me-2"></i>
                                {{ $bot->buy_type }}
                            </h5>
                            <small class="text-muted">{{ $bot->created_at->format('M d, Y') }}</small>
                        </div>
                        <div class="text-end">
                            @if($bot->invoice_status === 'Paid')
                                <span class="badge bg-success fs-6 px-3 py-2">
                                    <i class="bi bi-check-circle me-1"></i>Active
                                </span>
                            @elseif($bot->invoice_status === 'Unpaid')
                                <span class="badge bg-warning fs-6 px-3 py-2">
                                    <i class="bi bi-clock me-1"></i>Unpaid
                                </span>
                            @else
                                <span class="badge bg-secondary fs-6 px-3 py-2">
                                    <i class="bi bi-question-circle me-1"></i>Unknown
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
                
                <div class="card-body pt-2">
                    @if($bot->buy_type === 'Rent A Bot')
                        @php $details = $bot->buy_plan_details; @endphp
                        <div class="row g-3">
                            <div class="col-6">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-robot text-primary me-2"></i>
                                    <div>
                                        <small class="text-muted d-block">Bots</small>
                                        <span class="fw-bold">{{ $details['allowed_bots'] ?? 'N/A' }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-graph-up text-success me-2"></i>
                                    <div>
                                        <small class="text-muted d-block">Trades</small>
                                        <span class="fw-bold">{{ $details['allowed_trades'] ?? 'N/A' }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-calendar text-info me-2"></i>
                                    <div>
                                        <small class="text-muted d-block">Validity</small>
                                        <span class="fw-bold">{{ $details['validity'] ?? 'N/A' }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-currency-dollar text-warning me-2"></i>
                                    <div>
                                        <small class="text-muted d-block">Amount</small>
                                        <span class="fw-bold">${{ number_format($details['amount'] ?? 0, 2) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @else
                        @php $details = $bot->buy_plan_details; @endphp
                        <div class="row g-3">
                            <div class="col-6">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-robot text-primary me-2"></i>
                                    <div>
                                        <small class="text-muted d-block">Bots Allowed</small>
                                        <span class="fw-bold">{{ $details['bots_allowed'] ?? 'N/A' }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-graph-up text-success me-2"></i>
                                    <div>
                                        <small class="text-muted d-block">Trades/Day</small>
                                        <span class="fw-bold">{{ $details['trades_per_day'] ?? 'N/A' }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-currency-dollar text-warning me-2"></i>
                                    <div>
                                        <small class="text-muted d-block">Joining Fee</small>
                                        <span class="fw-bold">${{ number_format($details['joining_fee'] ?? 0, 2) }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-bank text-info me-2"></i>
                                    <div>
                                        <small class="text-muted d-block">Investment</small>
                                        <span class="fw-bold">${{ number_format($details['investment_amount'] ?? 0, 2) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                    
                    @if($bot->invoice_status === 'Unpaid')
                        <div class="alert alert-warning mt-3 mb-0 py-2">
                            <small>
                                <i class="bi bi-exclamation-triangle me-1"></i>
                                Payment due: ${{ number_format($bot->invoice_amount, 2) }}
                                @if($bot->invoice_due_date)
                                    (Due: {{ $bot->invoice_due_date->format('M d, Y') }})
                                @endif
                            </small>
                        </div>
                    @endif
                </div>
                
                <div class="card-footer bg-white border-0 pt-0">
                    <div class="d-flex gap-2">
                        @if($bot->invoice_status === 'Unpaid' && $bot->invoice_id)
                            <a href="{{ route('customer.wallet.deposit', ['invoice_id' => $bot->invoice_id]) }}" class="btn btn-warning btn-sm flex-fill">
                                <i class="bi bi-credit-card me-1"></i> Pay Now
                            </a>
                        @elseif($bot->invoice_status === 'Unpaid')
                            <a href="{{ route('customer.wallet.deposit') }}" class="btn btn-warning btn-sm flex-fill">
                                <i class="bi bi-credit-card me-1"></i> Pay Now
                            </a>
                        @else
                            <button class="btn btn-success btn-sm flex-fill" disabled>
                                <i class="bi bi-check-circle me-1"></i> Active
                            </button>
                        @endif
                        <a href="{{ route('customer.invoices.index') }}" class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-receipt"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endif

@if($agents->count() > 0)
<div class="row">
    @foreach($agents as $agent)
    <div class="col-md-6 col-lg-4 mb-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">{{ $agent->name }}</h5>
                <span class="badge bg-{{ $agent->status === 'active' ? 'success' : 'secondary' }}">
                    {{ ucfirst($agent->status) }}
                </span>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-6">
                        <small class="text-muted">Strategy</small>
                        <div class="fw-bold">{{ ucfirst($agent->strategy) }}</div>
                    </div>
                    <div class="col-6">
                        <small class="text-muted">Risk Level</small>
                        <div class="fw-bold">
                            <span class="badge bg-{{ $agent->risk_level === 'low' ? 'success' : ($agent->risk_level === 'medium' ? 'warning' : 'danger') }}">
                                {{ ucfirst($agent->risk_level) }}
                            </span>
                        </div>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-6">
                        <small class="text-muted">Initial Balance</small>
                        <div class="fw-bold">${{ number_format($agent->initial_balance, 2) }}</div>
                    </div>
                    <div class="col-6">
                        <small class="text-muted">Current Balance</small>
                        <div class="fw-bold {{ $agent->current_balance >= $agent->initial_balance ? 'text-success' : 'text-danger' }}">
                            ${{ number_format($agent->current_balance, 2) }}
                        </div>
                    </div>
                </div>
                
                <div class="d-flex gap-2">
                    <a href="{{ route('customer.agents.show', $agent) }}" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-eye"></i> View
                    </a>
                    <a href="{{ route('customer.agents.edit', $agent) }}" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-pencil"></i> Edit
                    </a>
                    <form method="POST" action="{{ route('customer.agents.destroy', $agent) }}" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this agent?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-outline-danger">
                            <i class="bi bi-trash"></i> Delete
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>

<div class="d-flex justify-content-center">
    {{ $agents->links() }}
</div>
@else
@if($activeBots->count() == 0)
<div class="text-center py-5">
    <i class="bi bi-robot display-1 text-muted"></i>
    <h4 class="text-muted mt-3">No AI Agents</h4>
    <p class="text-muted">Create your first AI trading agent to start automated trading.</p>
    <a href="{{ route('customer.agents.create') }}" class="btn btn-primary">
        <i class="bi bi-robot"></i> Create Your First Agent
    </a>
</div>
@endif
@endif
@endsection
