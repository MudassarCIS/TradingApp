@extends('layouts.customer-layout')

@section('title', 'AI Agents - AI Trade App')
@section('page-title', 'AI Agents')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>AI Agents</h2>
    <a href="{{ route('customer.agents.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-circle"></i> Create New Agent
    </a>
</div>

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
<div class="text-center py-5">
    <i class="bi bi-robot display-1 text-muted"></i>
    <h4 class="text-muted mt-3">No AI Agents</h4>
    <p class="text-muted">Create your first AI trading agent to start automated trading.</p>
    <a href="{{ route('customer.agents.create') }}" class="btn btn-primary">
        <i class="bi bi-robot"></i> Create Your First Agent
    </a>
</div>
@endif
@endsection
