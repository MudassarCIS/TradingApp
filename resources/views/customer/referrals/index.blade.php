@extends('layouts.customer-layout')

@section('title', 'Referrals - AI Trade App')
@section('page-title', 'My Referrals')

@push('styles')
<style>
    .referral-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 15px;
        padding: 2rem;
        margin-bottom: 2rem;
    }
    
    .referral-card h2 {
        font-size: 2.5rem;
        font-weight: bold;
        margin: 0;
    }
    
    .referral-card p {
        margin: 0;
        opacity: 0.9;
    }
    
    .stats-card {
        background: white;
        border-radius: 15px;
        padding: 1.5rem;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        text-align: center;
        margin-bottom: 1rem;
    }
    
    .stats-card h4 {
        font-size: 2rem;
        font-weight: bold;
        color: var(--primary-color);
        margin: 0;
    }
    
    .stats-card p {
        color: #666;
        margin: 0;
    }
    
    .qr-code-container {
        text-align: center;
        background: white;
        padding: 2rem;
        border-radius: 15px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }
    
    .referral-link {
        background: #f8f9fa;
        border: 2px dashed #dee2e6;
        border-radius: 10px;
        padding: 1rem;
        margin: 1rem 0;
        word-break: break-all;
    }
    
    .referral-table {
        background: white;
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }
    
    .referral-table .table {
        margin: 0;
    }
    
    .referral-table .table thead th {
        background: var(--primary-color);
        color: white;
        border: none;
        padding: 1rem;
    }
    
    .referral-table .table tbody td {
        padding: 1rem;
        vertical-align: middle;
    }
</style>
@endpush

@section('content')
<!-- Referral Statistics -->
<div class="row">
    <div class="col-12 mb-4">
        <div class="referral-card">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h2>{{ $referralCount }}</h2>
                    <p>Total Referrals</p>
                </div>
                <div class="col-md-4 text-end">
                    <i class="bi bi-people" style="font-size: 4rem; opacity: 0.3;"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Commission Stats -->
    <div class="col-md-4 mb-4">
        <div class="stats-card">
            <h4>${{ number_format($totalCommission, 2) }}</h4>
            <p>Total Commission Earned</p>
        </div>
    </div>
    
    <div class="col-md-4 mb-4">
        <div class="stats-card">
            <h4>${{ number_format($pendingCommission, 2) }}</h4>
            <p>Pending Commission</p>
        </div>
    </div>
    
    <div class="col-md-4 mb-4">
        <div class="stats-card">
            <h4>{{ $referralCount }}</h4>
            <p>Active Referrals</p>
        </div>
    </div>
</div>

<div class="row">
    <!-- Referral Link & QR Code -->
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-link-45deg"></i> Your Referral Link</h5>
            </div>
            <div class="card-body">
                <div class="referral-link">
                    <code>{{ $referralLink }}</code>
                </div>
                <button class="btn btn-primary" onclick="copyToClipboard('{{ $referralLink }}')">
                    <i class="bi bi-copy"></i> Copy Link
                </button>
            </div>
        </div>
    </div>
    
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-qr-code"></i> QR Code</h5>
            </div>
            <div class="card-body">
                <div class="qr-code-container">
                    {!! $qrCode !!}
                    <p class="mt-3 text-muted">Share this QR code to invite friends</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Referral List -->
<div class="row">
    <div class="col-12">
        <div class="referral-table">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-list"></i> Referral List</h5>
            </div>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Join Date</th>
                            <th>Commission Rate</th>
                            <th>Total Commission</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($referrals as $referral)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar-sm bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2">
                                        {{ substr($referral->referred->name, 0, 1) }}
                                    </div>
                                    {{ $referral->referred->name }}
                                </div>
                            </td>
                            <td>{{ $referral->referred->email }}</td>
                            <td>{{ $referral->joined_at->format('M d, Y') }}</td>
                            <td>{{ $referral->commission_rate }}%</td>
                            <td>
                                <span class="text-success fw-bold">
                                    ${{ number_format($referral->total_commission, 2) }}
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-{{ $referral->isActive() ? 'success' : 'secondary' }}">
                                    {{ $referral->isActive() ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                <i class="bi bi-people" style="font-size: 3rem; opacity: 0.3;"></i>
                                <br>
                                <p class="mt-2">No referrals yet</p>
                                <p class="text-muted">Share your referral link to start earning commissions!</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- How It Works -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-info-circle"></i> How Referrals Work</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 text-center mb-3">
                        <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                            <i class="bi bi-1-circle-fill"></i>
                        </div>
                        <h6 class="mt-2">Share Your Link</h6>
                        <p class="text-muted">Share your unique referral link or QR code with friends</p>
                    </div>
                    <div class="col-md-4 text-center mb-3">
                        <div class="bg-success text-white rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                            <i class="bi bi-2-circle-fill"></i>
                        </div>
                        <h6 class="mt-2">They Sign Up</h6>
                        <p class="text-muted">Your friends register using your referral link</p>
                    </div>
                    <div class="col-md-4 text-center mb-3">
                        <div class="bg-warning text-white rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                            <i class="bi bi-3-circle-fill"></i>
                        </div>
                        <h6 class="mt-2">Earn Commission</h6>
                        <p class="text-muted">Earn 10% commission on their trading profits</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function copyToClipboard(text) {
        navigator.clipboard.writeText(text).then(function() {
            // Show success message
            const btn = event.target;
            const originalText = btn.innerHTML;
            btn.innerHTML = '<i class="bi bi-check"></i> Copied!';
            btn.classList.remove('btn-primary');
            btn.classList.add('btn-success');
            
            setTimeout(function() {
                btn.innerHTML = originalText;
                btn.classList.remove('btn-success');
                btn.classList.add('btn-primary');
            }, 2000);
        }, function(err) {
            console.error('Could not copy text: ', err);
        });
    }
</script>
@endpush
