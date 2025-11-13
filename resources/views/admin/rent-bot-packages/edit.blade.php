@extends('layouts.admin-layout')

@section('title', 'Edit PEX Package')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Edit PEX Package</h3>
                    <a href="{{ route('admin.rent-bot-packages.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back
                    </a>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.rent-bot-packages.update', $package->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="allowed_bots">Allowed No. of Bots <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control @error('allowed_bots') is-invalid @enderror" id="allowed_bots" name="allowed_bots" value="{{ old('allowed_bots', $package->allowed_bots) }}" required>
                                    @error('allowed_bots')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="allowed_trades">Allowed No. of Trades <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control @error('allowed_trades') is-invalid @enderror" id="allowed_trades" name="allowed_trades" value="{{ old('allowed_trades', $package->allowed_trades) }}" required>
                                    @error('allowed_trades')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="amount">Package Amount ($) <span class="text-danger">*</span></label>
                                    <input type="number" step="0.01" class="form-control @error('amount') is-invalid @enderror" id="amount" name="amount" value="{{ old('amount', $package->amount) }}" required>
                                    @error('amount')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="validity">Package Validity <span class="text-danger">*</span></label>
                                    <select class="form-select @error('validity') is-invalid @enderror" id="validity" name="validity" required>
                                        <option value="month" {{ old('validity', $package->validity) === 'month' ? 'selected' : '' }}>Per Month</option>
                                        <option value="year" {{ old('validity', $package->validity) === 'year' ? 'selected' : '' }}>Per Year</option>
                                    </select>
                                    @error('validity')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label>Status <span class="text-danger">*</span></label>
                                    <div class="d-flex gap-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="status" id="status_active" value="1" {{ old('status', (string)$package->status) === '1' ? 'checked' : '' }}>
                                            <label class="form-check-label" for="status_active">Active</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="status" id="status_disabled" value="0" {{ old('status', (string)$package->status) === '0' ? 'checked' : '' }}>
                                            <label class="form-check-label" for="status_disabled">Disabled</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Update Package
                            </button>
                            <a href="{{ route('admin.rent-bot-packages.index') }}" class="btn btn-secondary">
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


