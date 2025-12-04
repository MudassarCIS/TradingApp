@extends('layouts.admin-layout')

@section('title', 'Edit Plan')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Edit Investment Plan: {{ $plan->name }}</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.plans.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Plans
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.plans.update', $plan->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="name">Plan Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                           id="name" name="name" value="{{ old('name', $plan->name) }}" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="investment_amount">Investment Amount ($) <span class="text-danger">*</span></label>
                                    <input type="number" step="0.01" class="form-control @error('investment_amount') is-invalid @enderror" 
                                           id="investment_amount" name="investment_amount" value="{{ old('investment_amount', $plan->investment_amount) }}" required>
                                    @error('investment_amount')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="joining_fee">Joining Fee ($) <span class="text-danger">*</span></label>
                                    <input type="number" step="0.01" class="form-control @error('joining_fee') is-invalid @enderror" 
                                           id="joining_fee" name="joining_fee" value="{{ old('joining_fee', $plan->joining_fee) }}" required>
                                    @error('joining_fee')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="fee_percentage">Fee Percentage (%)</label>
                                    <input type="number" step="0.01" class="form-control @error('fee_percentage') is-invalid @enderror" 
                                           id="fee_percentage" name="fee_percentage" value="{{ old('fee_percentage', $plan->fee_percentage) }}" 
                                           placeholder="Auto-calculated if left empty">
                                    <small class="text-muted">Leave empty to auto-calculate from joining fee and investment amount</small>
                                    @error('fee_percentage')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="bots_allowed">Bots Allowed <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control @error('bots_allowed') is-invalid @enderror" 
                                           id="bots_allowed" name="bots_allowed" value="{{ old('bots_allowed', $plan->bots_allowed) }}" required>
                                    @error('bots_allowed')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="trades_per_day">Trades per Day <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control @error('trades_per_day') is-invalid @enderror" 
                                           id="trades_per_day" name="trades_per_day" value="{{ old('trades_per_day', $plan->trades_per_day) }}" required>
                                    @error('trades_per_day')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="direct_bonus">Direct Bonus ($) <span class="text-danger">*</span></label>
                                    <input type="number" step="0.01" class="form-control @error('direct_bonus') is-invalid @enderror" 
                                           id="direct_bonus" name="direct_bonus" value="{{ old('direct_bonus', $plan->direct_bonus) }}" required>
                                    @error('direct_bonus')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group mb-3">
                                    <label for="referral_level_1">Referral Level 1 (%) <span class="text-danger">*</span></label>
                                    <input type="number" step="0.01" class="form-control @error('referral_level_1') is-invalid @enderror" 
                                           id="referral_level_1" name="referral_level_1" value="{{ old('referral_level_1', $plan->referral_level_1) }}" required>
                                    @error('referral_level_1')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group mb-3">
                                    <label for="referral_level_2">Referral Level 2 (%) <span class="text-danger">*</span></label>
                                    <input type="number" step="0.01" class="form-control @error('referral_level_2') is-invalid @enderror" 
                                           id="referral_level_2" name="referral_level_2" value="{{ old('referral_level_2', $plan->referral_level_2) }}" required>
                                    @error('referral_level_2')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group mb-3">
                                    <label for="referral_level_3">Referral Level 3 (%) <span class="text-danger">*</span></label>
                                    <input type="number" step="0.01" class="form-control @error('referral_level_3') is-invalid @enderror" 
                                           id="referral_level_3" name="referral_level_3" value="{{ old('referral_level_3', $plan->referral_level_3) }}" required>
                                    @error('referral_level_3')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="sort_order">Sort Order</label>
                                    <input type="number" class="form-control @error('sort_order') is-invalid @enderror" 
                                           id="sort_order" name="sort_order" value="{{ old('sort_order', $plan->sort_order) }}">
                                    @error('sort_order')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="is_active" name="is_active" value="1" 
                                               {{ old('is_active', $plan->is_active) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_active">
                                            Active Plan
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group mb-3">
                            <label for="description">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" name="description" rows="3">{{ old('description', $plan->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Update Plan
                            </button>
                            <a href="{{ route('admin.plans.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
