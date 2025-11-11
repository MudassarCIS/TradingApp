@extends('layouts.customer-layout')

@section('title', 'Profile - AI Trade App')
@section('page-title', 'Profile')

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-person"></i> Profile Information</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Name</label>
                            <div class="form-control-plaintext">{{ $user->name }}</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <div class="form-control-plaintext">{{ $user->email }}</div>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">User Type</label>
                            <div class="form-control-plaintext">
                                <span class="badge bg-primary">{{ ucfirst($user->user_type) }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Member Since</label>
                            <div class="form-control-plaintext">{{ $user->created_at->format('M d, Y') }}</div>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Last Login</label>
                            <div class="form-control-plaintext">
                                {{ $user->last_login_at ? $user->last_login_at->format('M d, Y H:i') : 'Never' }}
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <div class="form-control-plaintext">
                                <span class="badge bg-{{ $user->is_active ? 'success' : 'danger' }}">
                                    {{ $user->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Active Plan</label>
                            <div class="form-control-plaintext">
                                @if($user->active_plan_name)
                                    <span class="badge bg-info">{{ $user->active_plan_name }}</span>
                                @else
                                    <span class="text-muted">No active plan</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="d-flex gap-2">
                    <a href="{{ route('customer.profile.edit') }}" class="btn btn-primary">
                        <i class="bi bi-pencil"></i> Edit Profile
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-shield-check"></i> Account Security</h5>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span>Email Verified</span>
                    <span class="badge bg-{{ $user->email_verified_at ? 'success' : 'warning' }}">
                        {{ $user->email_verified_at ? 'Verified' : 'Pending' }}
                    </span>
                </div>
                
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span>Two-Factor Auth</span>
                    <span class="badge bg-secondary">Not Enabled</span>
                </div>
                
                <div class="d-flex justify-content-between align-items-center">
                    <span>Login Activity</span>
                    <a href="#" class="btn btn-sm btn-outline-primary">View</a>
                </div>
            </div>
        </div>
        
        <div class="card mt-3">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-graph-up"></i> Account Stats</h5>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span>Total Trades</span>
                    <span class="fw-bold">{{ $user->trades()->count() }}</span>
                </div>
                
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span>AI Agents</span>
                    <span class="fw-bold">{{ $user->agents()->count() }}</span>
                </div>
                
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span>Referrals</span>
                    <span class="fw-bold">{{ $user->referredUsers()->count() }}</span>
                </div>
                
                <div class="d-flex justify-content-between align-items-center">
                    <span>Account Balance</span>
                    <span class="fw-bold text-success">
                        ${{ number_format($user->getMainWallet('USDT')->balance ?? 0, 2) }}
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
