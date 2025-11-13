@extends('layouts.customer-layout')

@section('title', 'Package Details - AI Trade App')
@section('page-title', 'Package Details')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card bot-plan-card {{ $bot->buy_type === 'PEX' ? 'rent-bot' : 'sharing-nexa' }}">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="mb-1 text-dark fw-bold">
                            <i class="bi {{ $bot->buy_type === 'PEX' ? 'bi-robot' : 'bi-share' }} me-2"></i>
                            {{ $bot->buy_type }} - Package Details
                        </h4>
                        <small class="text-muted">Purchased on {{ $bot->created_at->format('M d, Y') }}</small>
                    </div>
                    <div>
                        <span class="status-badge bg-success text-white">
                            <i class="bi bi-check-circle me-1"></i>Active
                        </span>
                    </div>
                </div>
            </div>
            
            <div class="card-body">
                @if($bot->buy_type === 'PEX')
                    @php $details = $bot->buy_plan_details; @endphp
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <div class="plan-detail-item">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-robot text-primary me-3 fs-4"></i>
                                    <div>
                                        <small class="text-muted d-block mb-1">Bots Allowed</small>
                                        <span class="fw-bold fs-5">{{ $details['allowed_bots'] ?? 'N/A' }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="plan-detail-item">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-graph-up text-success me-3 fs-4"></i>
                                    <div>
                                        <small class="text-muted d-block mb-1">Allowed Trades</small>
                                        <span class="fw-bold fs-5">{{ $details['allowed_trades'] ?? 'N/A' }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="plan-detail-item">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-calendar text-info me-3 fs-4"></i>
                                    <div>
                                        <small class="text-muted d-block mb-1">Validity Period</small>
                                        <span class="fw-bold fs-5">{{ $details['validity'] ?? 'N/A' }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="plan-detail-item">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-currency-dollar text-primary me-3 fs-4"></i>
                                    <div>
                                        <small class="text-muted d-block mb-1">Package Amount</small>
                                        <span class="fw-bold fs-5">${{ number_format($details['amount'] ?? 0, 2) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @else
                    @php $details = $bot->buy_plan_details; @endphp
                    @if(isset($details['name']) && !empty($details['name']))
                    <div class="mb-3">
                        <span class="plan-name-badge" style="font-size: 0.9rem; padding: 0.5rem 1rem;">
                            <i class="bi bi-tag me-1"></i>{{ $details['name'] }} Plan
                        </span>
                    </div>
                    @endif
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <div class="plan-detail-item">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-robot text-primary me-3 fs-4"></i>
                                    <div>
                                        <small class="text-muted d-block mb-1">Bots Allowed</small>
                                        <span class="fw-bold fs-5">{{ $details['bots_allowed'] ?? 'N/A' }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="plan-detail-item">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-graph-up text-success me-3 fs-4"></i>
                                    <div>
                                        <small class="text-muted d-block mb-1">Trades Per Day</small>
                                        <span class="fw-bold fs-5">{{ $details['trades_per_day'] ?? 'N/A' }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="plan-detail-item">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-currency-dollar text-primary me-3 fs-4"></i>
                                    <div>
                                        <small class="text-muted d-block mb-1">Joining Fee</small>
                                        <span class="fw-bold fs-5">${{ number_format($details['joining_fee'] ?? 0, 2) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="plan-detail-item">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-bank text-info me-3 fs-4"></i>
                                    <div>
                                        <small class="text-muted d-block mb-1">Investment Amount</small>
                                        <span class="fw-bold fs-5">${{ number_format($details['investment_amount'] ?? 0, 2) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
                
                @if($bot->invoice_id)
                <div class="alert alert-info mt-3 mb-0">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-receipt me-2"></i>
                        <div>
                            <strong>Invoice ID:</strong> #{{ $bot->invoice_id }}
                            <br>
                            <small class="text-muted">Status: <span class="badge bg-success">Paid</span></small>
                        </div>
                    </div>
                </div>
                @endif
            </div>
            
            <div class="card-footer">
                <div class="d-flex gap-2">
                    <a href="{{ route('customer.agents.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-arrow-left me-1"></i> Back to Agents
                    </a>
                    <a href="{{ route('customer.trading.index') }}" class="btn btn-primary btn-sm">
                        <i class="bi bi-graph-up me-1"></i> View Trading
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .plan-detail-item {
        background: #f8f9fa;
        border-radius: 12px;
        padding: 1rem;
        transition: all 0.2s ease;
    }
    
    .plan-detail-item:hover {
        background: #e9ecef;
        transform: translateY(-2px);
    }
</style>
@endsection

