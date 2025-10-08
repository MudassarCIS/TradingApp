@extends('layouts.customer-layout')

@section('title', 'Create AI Agent - AI Trade App')
@section('page-title', 'Create AI Agent')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-robot"></i> Create New AI Agent</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('customer.agents.store') }}">
                    @csrf
                    
                    <div class="mb-3">
                        <label for="name" class="form-label">Agent Name</label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" 
                               id="name" name="name" value="{{ old('name') }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label for="strategy" class="form-label">Trading Strategy</label>
                        <select class="form-select @error('strategy') is-invalid @enderror" 
                                id="strategy" name="strategy" required>
                            <option value="">Select Strategy</option>
                            <option value="scalping" {{ old('strategy') === 'scalping' ? 'selected' : '' }}>Scalping</option>
                            <option value="swing" {{ old('strategy') === 'swing' ? 'selected' : '' }}>Swing Trading</option>
                            <option value="trend_following" {{ old('strategy') === 'trend_following' ? 'selected' : '' }}>Trend Following</option>
                            <option value="mean_reversion" {{ old('strategy') === 'mean_reversion' ? 'selected' : '' }}>Mean Reversion</option>
                            <option value="momentum" {{ old('strategy') === 'momentum' ? 'selected' : '' }}>Momentum</option>
                        </select>
                        @error('strategy')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label for="risk_level" class="form-label">Risk Level</label>
                        <select class="form-select @error('risk_level') is-invalid @enderror" 
                                id="risk_level" name="risk_level" required>
                            <option value="">Select Risk Level</option>
                            <option value="low" {{ old('risk_level') === 'low' ? 'selected' : '' }}>Low Risk</option>
                            <option value="medium" {{ old('risk_level') === 'medium' ? 'selected' : '' }}>Medium Risk</option>
                            <option value="high" {{ old('risk_level') === 'high' ? 'selected' : '' }}>High Risk</option>
                        </select>
                        @error('risk_level')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label for="initial_balance" class="form-label">Initial Balance (USDT)</label>
                        <input type="number" class="form-control @error('initial_balance') is-invalid @enderror" 
                               id="initial_balance" name="initial_balance" value="{{ old('initial_balance') }}" 
                               min="0" step="0.01" required>
                        @error('initial_balance')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Minimum: $10 USDT</div>
                    </div>
                    
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-robot"></i> Create Agent
                        </button>
                        <a href="{{ route('customer.agents.index') }}" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
