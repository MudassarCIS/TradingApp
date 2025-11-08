@extends('layouts.customer-layout')

@section('title', 'AI Agents - AI Trade App')
@section('page-title', 'AI Agents')

@section('content')
<style>
    .bot-plan-card {
        border: none;
        border-radius: 12px;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        overflow: hidden;
        background: white;
        position: relative;
    }
    
    .bot-plan-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 3px;
        background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));
    }
    
    .bot-plan-card.rent-bot::before {
        background: linear-gradient(90deg, #fd7e14, #ff9500);
    }
    
    .bot-plan-card.sharing-nexa::before {
        background: linear-gradient(90deg, #0d6efd, #198754);
    }
    
    .bot-plan-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.12);
    }
    
    .bot-plan-card .card-header {
        background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
        border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        padding: 0.75rem 1rem;
    }
    
    .bot-plan-card .card-header h5 {
        font-size: 0.95rem;
        margin-bottom: 0.25rem;
    }
    
    .bot-plan-card .card-header small {
        font-size: 0.75rem;
    }
    
    .bot-plan-card .card-body {
        padding: 1rem;
    }
    
    .bot-plan-card .card-footer {
        background: #f8f9fa;
        border-top: 1px solid rgba(0, 0, 0, 0.05);
        padding: 0.75rem 1rem;
    }
    
    .plan-detail-item {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 0.5rem;
        transition: all 0.2s ease;
    }
    
    .plan-detail-item:hover {
        background: #e9ecef;
    }
    
    .plan-detail-item i {
        font-size: 1rem !important;
    }
    
    .plan-detail-item small {
        font-size: 0.7rem;
        line-height: 1.2;
    }
    
    .plan-detail-item .fw-bold {
        font-size: 0.85rem;
    }
    
    .status-badge {
        border-radius: 12px;
        padding: 0.25rem 0.6rem;
        font-weight: 500;
        font-size: 0.7rem;
    }
    
    .plan-name-badge {
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        color: white;
        border-radius: 12px;
        padding: 0.3rem 0.75rem;
        font-weight: 500;
        font-size: 0.75rem;
        display: inline-block;
    }
    
    .btn-pay-now {
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        border: none;
        border-radius: 6px;
        color: white;
        font-weight: 500;
        font-size: 0.8rem;
        padding: 0.4rem 0.8rem;
        transition: all 0.3s ease;
    }
    
    .btn-pay-now:hover {
        transform: translateY(-1px);
        box-shadow: 0 3px 8px rgba(13, 110, 253, 0.3);
        color: white;
    }
    
    .btn-active {
        background: linear-gradient(135deg, #198754, #20c997);
        border: none;
        border-radius: 6px;
        color: white;
        font-weight: 500;
        font-size: 0.8rem;
        padding: 0.4rem 0.8rem;
    }
    
    .btn-outline-primary.btn-sm {
        border-radius: 6px;
        font-size: 0.8rem;
        padding: 0.4rem 0.8rem;
    }
    
    .alert-warning {
        padding: 0.5rem 0.75rem;
        font-size: 0.75rem;
        margin-bottom: 0;
    }
    
    .agent-card {
        border: none;
        border-radius: 12px;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        overflow: hidden;
    }
    
    .agent-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.12);
    }
    
    .agent-card .card-header {
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        color: white;
        border: none;
        padding: 0.75rem 1rem;
    }
    
    .agent-card .card-header h5 {
        font-size: 0.95rem;
    }
</style>

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
        <div class="col-md-6 col-lg-4 mb-3">
            <div class="card h-100 bot-plan-card {{ $bot->buy_type === 'Rent A Bot' ? 'rent-bot' : 'sharing-nexa' }}">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="flex-grow-1">
                            <h5 class="mb-0 text-dark fw-bold">
                                <i class="bi {{ $bot->buy_type === 'Rent A Bot' ? 'bi-robot' : 'bi-share' }} me-1"></i>
                                {{ $bot->buy_type }}
                            </h5>
                            <small class="text-muted">{{ $bot->created_at->format('M d, Y') }}</small>
                        </div>
                        <div class="text-end ms-2">
                            @if($bot->invoice_status === 'Paid')
                                <span class="status-badge bg-success text-white">
                                    <i class="bi bi-check-circle me-1"></i>Active
                                </span>
                            @elseif($bot->invoice_status === 'Unpaid')
                                <span class="status-badge bg-warning text-dark">
                                    <i class="bi bi-clock me-1"></i>Unpaid
                                </span>
                            @else
                                <span class="status-badge bg-secondary text-white">
                                    <i class="bi bi-question-circle me-1"></i>Unknown
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
                
                <div class="card-body">
                    @if($bot->buy_type === 'Rent A Bot')
                        @php $details = $bot->buy_plan_details; @endphp
                        <div class="row g-2">
                            <div class="col-6">
                                <div class="plan-detail-item">
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-robot text-primary me-2"></i>
                                        <div>
                                            <small class="text-muted d-block mb-0">Bots</small>
                                            <span class="fw-bold">{{ $details['allowed_bots'] ?? 'N/A' }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="plan-detail-item">
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-graph-up text-success me-2"></i>
                                        <div>
                                            <small class="text-muted d-block mb-0">Trades</small>
                                            <span class="fw-bold">{{ $details['allowed_trades'] ?? 'N/A' }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="plan-detail-item">
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-calendar text-info me-2"></i>
                                        <div>
                                            <small class="text-muted d-block mb-0">Validity</small>
                                            <span class="fw-bold">{{ $details['validity'] ?? 'N/A' }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="plan-detail-item">
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-currency-dollar text-primary me-2"></i>
                                        <div>
                                            <small class="text-muted d-block mb-0">Amount</small>
                                            <span class="fw-bold">${{ number_format($details['amount'] ?? 0, 2) }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @else
                        @php $details = $bot->buy_plan_details; @endphp
                        @if(isset($details['name']) && !empty($details['name']))
                        <div class="mb-2">
                            <span class="plan-name-badge">
                                <i class="bi bi-tag me-1"></i>{{ $details['name'] }}
                            </span>
                        </div>
                        @endif
                        <div class="row g-2">
                            <div class="col-6">
                                <div class="plan-detail-item">
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-robot text-primary me-2"></i>
                                        <div>
                                            <small class="text-muted d-block mb-0">Bots Allowed</small>
                                            <span class="fw-bold">{{ $details['bots_allowed'] ?? 'N/A' }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="plan-detail-item">
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-graph-up text-success me-2"></i>
                                        <div>
                                            <small class="text-muted d-block mb-0">Trades/Day</small>
                                            <span class="fw-bold">{{ $details['trades_per_day'] ?? 'N/A' }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="plan-detail-item">
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-currency-dollar text-primary me-2"></i>
                                        <div>
                                            <small class="text-muted d-block mb-0">Joining Fee</small>
                                            <span class="fw-bold">${{ number_format($details['joining_fee'] ?? 0, 2) }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="plan-detail-item">
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-bank text-info me-2"></i>
                                        <div>
                                            <small class="text-muted d-block mb-0">Investment</small>
                                            <span class="fw-bold">${{ number_format($details['investment_amount'] ?? 0, 2) }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                    
                    @if($bot->invoice_status === 'Unpaid')
                        <div class="alert alert-warning mt-2 mb-0 py-2 rounded-2" style="background: #fff3cd; border: 1px solid #ffc107;">
                            <small class="d-flex align-items-center">
                                <i class="bi bi-exclamation-triangle me-1 text-warning"></i>
                                <span>
                                    <strong>Due:</strong> ${{ number_format($bot->invoice_amount, 2) }}
                                    @if($bot->invoice_due_date)
                                        ({{ $bot->invoice_due_date->format('M d') }})
                                    @endif
                                </span>
                            </small>
                        </div>
                    @endif
                </div>
                
                <div class="card-footer">
                    @if($bot->invoice_status === 'Paid')
                        <a href="{{ route('customer.packages.show', $bot->id) }}" class="btn btn-pay-now btn-sm w-100">
                            <i class="bi bi-info-circle me-1"></i> View Active Package Details
                        </a>
                    @else
                        <div class="d-flex gap-2">
                            @if($bot->invoice_status === 'Unpaid' && $bot->invoice_id)
                                <a href="{{ route('customer.wallet.deposit', ['invoice_id' => $bot->invoice_id]) }}" class="btn btn-pay-now btn-sm flex-fill">
                                    <i class="bi bi-credit-card me-1"></i> Pay Now
                                </a>
                            @elseif($bot->invoice_status === 'Unpaid')
                                <a href="{{ route('customer.wallet.deposit') }}" class="btn btn-pay-now btn-sm flex-fill">
                                    <i class="bi bi-credit-card me-1"></i> Pay Now
                                </a>
                            @endif
                            <a href="{{ route('customer.invoices.index') }}" class="btn btn-outline-primary btn-sm" style="border-radius: 8px;">
                                <i class="bi bi-receipt"></i>
                            </a>
                        </div>
                    @endif
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
        <div class="card agent-card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0 text-white fw-bold">{{ $agent->name }}</h5>
                <span class="badge bg-{{ $agent->status === 'active' ? 'light text-success' : 'light text-secondary' }}">
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
