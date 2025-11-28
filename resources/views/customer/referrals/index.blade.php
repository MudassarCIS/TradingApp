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
        font-family: monospace;
        font-size: 0.9rem;
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
    
    .level-badge {
        display: inline-block;
        padding: 0.25rem 0.5rem;
        border-radius: 12px;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
    }
    
    .level-1 { background: #e3f2fd; color: #1976d2; }
    .level-2 { background: #f3e5f5; color: #7b1fa2; }
    .level-3 { background: #e8f5e8; color: #388e3c; }
    
    .copy-btn {
        position: relative;
        overflow: hidden;
    }
    
    .copy-btn::after {
        content: '';
        position: absolute;
        top: 50%;
        left: 50%;
        width: 0;
        height: 0;
        background: rgba(255, 255, 255, 0.3);
        border-radius: 50%;
        transform: translate(-50%, -50%);
        transition: width 0.3s, height 0.3s;
    }
    
    .copy-btn.copied::after {
        width: 300px;
        height: 300px;
    }
    
    .investment-amount {
        font-weight: 600;
        color: #28a745;
    }
    
    .no-investment {
        color: #6c757d;
        font-style: italic;
    }
    
    /* Tab Styles */
    .nav-tabs .nav-link {
        border: none;
        border-bottom: 3px solid transparent;
        color: #6c757d;
        font-weight: 500;
        padding: 1rem 1.5rem;
        transition: all 0.3s ease;
    }
    
    .nav-tabs .nav-link:hover {
        border-color: transparent;
        border-bottom-color: #dee2e6;
        color: #495057;
    }
    
    .nav-tabs .nav-link.active {
        color: var(--primary-color);
        border-bottom-color: var(--primary-color);
        background-color: transparent;
    }
    
    .nav-tabs .nav-link .badge {
        font-size: 0.75rem;
        padding: 0.25rem 0.5rem;
    }
    
    .tab-content {
        border: none;
        background: white;
    }
    
    .tab-pane {
        padding: 0;
    }
    
    .avatar-sm {
        width: 32px;
        height: 32px;
        font-size: 0.875rem;
        font-weight: 600;
    }
    
    .card-footer {
        background-color: #f8f9fa;
        border-top: 1px solid #dee2e6;
        padding: 1rem 1.5rem;
    }
    
    /* Responsive adjustments */
    @media (max-width: 768px) {
        .nav-tabs .nav-link {
            padding: 0.75rem 1rem;
            font-size: 0.875rem;
        }
        
        .nav-tabs .nav-link .badge {
            font-size: 0.65rem;
            padding: 0.2rem 0.4rem;
        }
        
        .table-responsive {
            font-size: 0.875rem;
        }
        
        .avatar-sm {
            width: 28px;
            height: 28px;
            font-size: 0.75rem;
        }
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

<!-- Parent Levels Section -->

<div class="row">
    <!-- First Parent -->
    <div class="col-md-4 mb-4">
        <div class="stats-card">
            <h6 class="text-muted mb-2">First Parent</h6>
            @if($parents['first'])
                <p class="mb-1"><strong>Referral ID:</strong> <code>{{ $parents['first']['user']->referral_code ?? 'N/A' }}</code></p>
                <h4>${{ number_format($parents['first']['bonus_amount'], 2) }}</h4>
                <p class="text-muted mb-0">Bonus Amount</p>
            @else
                <h4 class="text-muted">-</h4>
                <p class="text-muted mb-0">No parent</p>
            @endif
        </div>
    </div>
    
    <!-- Second Parent -->
    <div class="col-md-4 mb-4">
        <div class="stats-card">
            <h6 class="text-muted mb-2">Second Parent</h6>
            @if($parents['second'])
                <p class="mb-1"><strong>Referral ID:</strong> <code>{{ $parents['second']['user']->referral_code ?? 'N/A' }}</code></p>
                <h4>${{ number_format($parents['second']['bonus_amount'], 2) }}</h4>
                <p class="text-muted mb-0">Bonus Amount</p>
            @else
                <h4 class="text-muted">-</h4>
                <p class="text-muted mb-0">No parent</p>
            @endif
        </div>
    </div>
    
    <!-- Third Parent -->
    <div class="col-md-4 mb-4">
        <div class="stats-card">
            <h6 class="text-muted mb-2">Third Parent</h6>
            @if($parents['third'])
                <p class="mb-1"><strong>Referral ID:</strong> <code>{{ $parents['third']['user']->referral_code ?? 'N/A' }}</code></p>
                <h4>${{ number_format($parents['third']['bonus_amount'], 2) }}</h4>
                <p class="text-muted mb-0">Bonus Amount</p>
            @else
                <h4 class="text-muted">-</h4>
                <p class="text-muted mb-0">No parent</p>
            @endif
        </div>
    </div>
</div>

<!-- Referral Link & QR Code -->
<div class="row">
    <div class="col-12 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-link-45deg"></i> Your Referral Link & QR Code</h5>
            </div>
            <div class="card-body">
                <div class="row align-items-center">
                    <!-- Referral Link - Left Side -->
                    <div class="col-md-6 mb-3 mb-md-0">
                        <h6 class="mb-3"><i class="bi bi-link-45deg"></i> Referral Link</h6>
                        <div class="address-container" style="background: #f8f9fa; border: 2px dashed #dee2e6; border-radius: 8px; padding: 0.75rem; word-break: break-all; display: flex; align-items: center; justify-content: space-between; gap: 0.5rem;">
                            <code id="modal-deposit-address" style="font-size: 0.85rem; flex: 1; margin: 0; color: #495057; padding-right: 0.5rem; min-width: 0;">{{ $referralLink }}</code>
                            <button class="btn btn-sm btn-primary copy-btn" onclick="copyToClipboard('{{ $referralLink }}')">
                                <i class="bi bi-copy"></i> Copy
                            </button>
                        </div>
                    </div>

                    <!-- QR Code - Right Side -->
                    <div class="col-md-6 text-center">
                        <h6 class="mb-3"><i class="bi bi-qr-code"></i> QR Code</h6>
                        <div style="background: white; padding: 1rem; border-radius: 8px; display: inline-block;">
                            <div id="qrCodeContainer" style="max-width: 150px; margin: 0 auto;">
                                {!! $qrCode !!}
                            </div>
                            <p class="mt-2 mb-0 text-muted" style="font-size: 0.85rem;">Share this QR code to invite friends</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>  
    

<!--
<div class="row">
   
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-link-45deg"></i> Your Referral Link</h5>
            </div>
            <div class="card-body">
                <div class="referral-link">
                    <code id="referralLink">{{ $referralLink }}</code>
                </div>
                <button class="btn btn-primary copy-btn" onclick="copyToClipboard('{{ $referralLink }}')">
                    <i class="bi bi-copy"></i> Copy Link
                </button>
                <button class="btn btn-outline-secondary ms-2" onclick="downloadQR()">
                    <i class="bi bi-download"></i> Download QR
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
                    <div id="qrCodeContainer">
                        {!! $qrCode !!}
                    </div>
                    <p class="mt-3 text-muted">Share this QR code to invite friends</p>
                </div>
            </div>
        </div>
    </div>
</div>-->

<!-- Referral Tree (Up to 3 Levels) -->
<div class="row">
    <div class="col-12">
        <div class="referral-table">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-diagram-3"></i> Referral Tree (Up to 3 Levels)</h5>
            </div>
            
            <!-- Tabs Navigation -->
            <div class="card-body p-0">
                <ul class="nav nav-tabs nav-fill" id="referralTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="level1-tab" data-bs-toggle="tab" data-bs-target="#level1" type="button" role="tab">
                            <i class="bi bi-1-circle"></i> Level 1 Referrals
                            <span class="badge bg-primary ms-2">{{ $level1Count }}</span>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="level2-tab" data-bs-toggle="tab" data-bs-target="#level2" type="button" role="tab">
                            <i class="bi bi-2-circle"></i> Level 2 Referrals
                            <span class="badge bg-secondary ms-2">{{ $level2Count }}</span>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="level3-tab" data-bs-toggle="tab" data-bs-target="#level3" type="button" role="tab">
                            <i class="bi bi-3-circle"></i> Level 3 Referrals
                            <span class="badge bg-success ms-2">{{ $level3Count }}</span>
                        </button>
                    </li>
                </ul>
                
                <!-- Tab Content -->
                <div class="tab-content" id="referralTabsContent">
                    <!-- Level 1 Tab -->
                    <div class="tab-pane fade show active" id="level1" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table mb-0">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Referral ID</th>
                                        <th>Join Date</th>
                                        <th>Total Investment</th>
                                        <th>Active Plan</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($level1Referrals as $referral)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-sm bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2">
                                                    {{ substr($referral->name, 0, 1) }}
                                                </div>
                                                {{ $referral->name }}
                                            </div>
                                        </td>
                                        <td>{{ $referral->email }}</td>
                                        <td>
                                            <code>{{ $referral->referral_code ?? 'N/A' }}</code>
                                        </td>
                                        <td>{{ $referral->created_at->format('M d, Y') }}</td>
                                        <td>
                                            @php
                                                $totalInvestment = $referral->wallets()->sum('total_deposited');
                                            @endphp
                                            @if($totalInvestment > 0)
                                                <span class="investment-amount">
                                                    ${{ number_format($totalInvestment, 2) }}
                                                </span>
                                            @else
                                                <span class="no-investment">No investment</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($referral->activePlan)
                                                <span class="badge bg-info">{{ $referral->activePlan->name }}</span>
                                            @else
                                                <span class="text-muted">No plan</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-success">Active</span>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-muted py-4">
                                            <i class="bi bi-people" style="font-size: 3rem; opacity: 0.3;"></i>
                                            <br>
                                            <p class="mt-2">No Level 1 referrals yet</p>
                                            <p class="text-muted">Share your referral link to get direct referrals!</p>
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Level 1 Pagination -->
                        @if($level1Referrals->hasPages())
                        <div class="card-footer">
                            {{ $level1Referrals->links() }}
                        </div>
                        @endif
                    </div>
                    
                    <!-- Level 2 Tab -->
                    <div class="tab-pane fade" id="level2" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table mb-0">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Referral ID</th>
                                        <th>Join Date</th>
                                        <th>Total Investment</th>
                                        <th>Active Plan</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($level2Referrals as $referral)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-sm bg-secondary text-white rounded-circle d-flex align-items-center justify-content-center me-2">
                                                    {{ substr($referral->name, 0, 1) }}
                                                </div>
                                                {{ $referral->name }}
                                            </div>
                                        </td>
                                        <td>{{ $referral->email }}</td>
                                        <td>
                                            <code>{{ $referral->referral_code ?? 'N/A' }}</code>
                                        </td>
                                        <td>{{ $referral->created_at->format('M d, Y') }}</td>
                                        <td>
                                            @php
                                                $totalInvestment = $referral->wallets()->sum('total_deposited');
                                            @endphp
                                            @if($totalInvestment > 0)
                                                <span class="investment-amount">
                                                    ${{ number_format($totalInvestment, 2) }}
                                                </span>
                                            @else
                                                <span class="no-investment">No investment</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($referral->activePlan)
                                                <span class="badge bg-info">{{ $referral->activePlan->name }}</span>
                                            @else
                                                <span class="text-muted">No plan</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-success">Active</span>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-muted py-4">
                                            <i class="bi bi-people" style="font-size: 3rem; opacity: 0.3;"></i>
                                            <br>
                                            <p class="mt-2">No Level 2 referrals yet</p>
                                            <p class="text-muted">Your Level 1 referrals need to refer others!</p>
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Level 2 Pagination -->
                        @if($level2Referrals->hasPages())
                        <div class="card-footer">
                            {{ $level2Referrals->links() }}
                        </div>
                        @endif
                    </div>
                    
                    <!-- Level 3 Tab -->
                    <div class="tab-pane fade" id="level3" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table mb-0">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Referral ID</th>
                                        <th>Join Date</th>
                                        <th>Total Investment</th>
                                        <th>Active Plan</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($level3Referrals as $referral)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-sm bg-success text-white rounded-circle d-flex align-items-center justify-content-center me-2">
                                                    {{ substr($referral->name, 0, 1) }}
                                                </div>
                                                {{ $referral->name }}
                                            </div>
                                        </td>
                                        <td>{{ $referral->email }}</td>
                                        <td>
                                            <code>{{ $referral->referral_code ?? 'N/A' }}</code>
                                        </td>
                                        <td>{{ $referral->created_at->format('M d, Y') }}</td>
                                        <td>
                                            @php
                                                $totalInvestment = $referral->wallets()->sum('total_deposited');
                                            @endphp
                                            @if($totalInvestment > 0)
                                                <span class="investment-amount">
                                                    ${{ number_format($totalInvestment, 2) }}
                                                </span>
                                            @else
                                                <span class="no-investment">No investment</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($referral->activePlan)
                                                <span class="badge bg-info">{{ $referral->activePlan->name }}</span>
                                            @else
                                                <span class="text-muted">No plan</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-success">Active</span>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-muted py-4">
                                            <i class="bi bi-people" style="font-size: 3rem; opacity: 0.3;"></i>
                                            <br>
                                            <p class="mt-2">No Level 3 referrals yet</p>
                                            <p class="text-muted">Your Level 2 referrals need to refer others!</p>
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Level 3 Pagination -->
                        @if($level3Referrals->hasPages())
                        <div class="card-footer">
                            {{ $level3Referrals->links() }}
                        </div>
                        @endif
                    </div>
                </div>
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
                        <p class="text-muted">Earn commission on their investments up to 3 levels</p>
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
            // Show success animation
            const btn = event.target.closest('.copy-btn');
            btn.classList.add('copied');
            
            // Change button text
            const originalHTML = btn.innerHTML;
            btn.innerHTML = '<i class="bi bi-check"></i> Copied!';
            btn.classList.remove('btn-primary');
            btn.classList.add('btn-success');
            
            setTimeout(function() {
                btn.innerHTML = originalHTML;
                btn.classList.remove('btn-success', 'copied');
                btn.classList.add('btn-primary');
            }, 2000);
        }, function(err) {
            console.error('Could not copy text: ', err);
            alert('Failed to copy to clipboard');
        });
    }
    
    function downloadQR() {
        // Get the QR code SVG
        const qrSvg = document.querySelector('#qrCodeContainer svg');
        if (!qrSvg) {
            alert('QR code not found');
            return;
        }
        
        // Convert SVG to canvas and download
        const canvas = document.createElement('canvas');
        const ctx = canvas.getContext('2d');
        const img = new Image();
        
        const svgData = new XMLSerializer().serializeToString(qrSvg);
        const svgBlob = new Blob([svgData], {type: 'image/svg+xml;charset=utf-8'});
        const svgUrl = URL.createObjectURL(svgBlob);
        
        img.onload = function() {
            canvas.width = img.width;
            canvas.height = img.height;
            ctx.drawImage(img, 0, 0);
            
            // Download the canvas as PNG
            const link = document.createElement('a');
            link.download = 'referral-qr-code.png';
            link.href = canvas.toDataURL('image/png');
            link.click();
            
            URL.revokeObjectURL(svgUrl);
        };
        
        img.src = svgUrl;
    }
    
    // Tab functionality with URL hash support
    document.addEventListener('DOMContentLoaded', function() {
        // Handle URL hash for direct tab access
        const hash = window.location.hash;
        if (hash) {
            const tabButton = document.querySelector(`[data-bs-target="${hash}"]`);
            if (tabButton) {
                const tab = new bootstrap.Tab(tabButton);
                tab.show();
            }
        }
        
        // Update URL hash when tab changes
        const tabButtons = document.querySelectorAll('#referralTabs button[data-bs-toggle="tab"]');
        tabButtons.forEach(function(button) {
            button.addEventListener('shown.bs.tab', function(event) {
                const target = event.target.getAttribute('data-bs-target');
                window.location.hash = target;
            });
        });
        
        // Add smooth scrolling for tab content
        const tabContent = document.querySelector('.tab-content');
        if (tabContent) {
            tabContent.style.minHeight = '400px';
        }
    });
    
    // Auto-refresh referral data every 30 seconds
    setInterval(function() {
        // You can add AJAX call here to refresh data without page reload
        console.log('Refreshing referral data...');
    }, 30000);
</script>
@endpush