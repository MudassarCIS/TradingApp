@extends('layouts.customer-layout')

@section('title', 'Deposit - AI Trade App')
@section('page-title', 'Deposit Funds')

@push('styles')
<style>
    .deposit-card {
        background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
        color: white;
        border-radius: 15px;
        padding: 2rem;
        margin-bottom: 2rem;
    }
    
    .deposit-card h2 {
        font-size: 2.5rem;
        font-weight: bold;
        margin: 0;
    }
    
    .deposit-card p {
        margin: 0;
        opacity: 0.9;
    }
    
    .qr-code-container {
        text-align: center;
        background: white;
        padding: 2rem;
        border-radius: 15px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        margin-bottom: 2rem;
    }
    
    .address-container {
        background: #f8f9fa;
        border: 2px dashed #dee2e6;
        border-radius: 10px;
        padding: 1rem;
        margin: 1rem 0;
        word-break: break-all;
    }
    
    .instruction-card {
        background: white;
        border-radius: 15px;
        padding: 1.5rem;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        margin-bottom: 1rem;
    }
    
    .instruction-card h6 {
        color: var(--primary-color);
        font-weight: bold;
    }
    
    .instruction-card ol {
        margin: 0;
        padding-left: 1.2rem;
    }
    
    .instruction-card li {
        margin-bottom: 0.5rem;
    }
</style>
@endpush

@section('content')
<!-- Success/Error Messages -->
@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="bi bi-check-circle"></i> {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <i class="bi bi-exclamation-triangle"></i> {{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

@if($errors->any())
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <i class="bi bi-exclamation-triangle"></i> 
    <ul class="mb-0">
        @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

<!-- Deposit Header -->
<div class="row">
    <div class="col-12 mb-4">
        <div class="deposit-card">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h2>Deposit Funds</h2>
                    <p>Add funds to your wallet to start trading</p>
                </div>
                <div class="col-md-4 text-end">
                    <button type="button" class="btn btn-light btn-lg" data-bs-toggle="modal" data-bs-target="#depositModal">
                        <i class="bi bi-plus-circle"></i> New Deposit
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@if($walletAddresses->count() > 0)
    @foreach($walletAddresses as $walletAddress)
    <div class="row mb-4">
        <!-- Deposit Address & QR Code -->
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-wallet2"></i> Deposit {{ $walletAddress->name }} ({{ $walletAddress->symbol }})
                        @if($walletAddress->network)
                            <span class="badge bg-info ms-2">{{ $walletAddress->network }}</span>
                        @endif
                    </h5>
                </div>
                <div class="card-body">
                    <div class="address-container">
                        <code id="depositAddress{{ $walletAddress->id }}">{{ $walletAddress->wallet_address }}</code>
                    </div>
                    <button class="btn btn-primary" onclick="copyToClipboard('{{ $walletAddress->wallet_address }}', 'depositAddress{{ $walletAddress->id }}')">
                        <i class="bi bi-copy"></i> Copy Address
                    </button>
                    
                    <div class="mt-3">
                        <small class="text-muted">
                            <i class="bi bi-info-circle"></i>
                            @if($walletAddress->instructions)
                                {{ $walletAddress->instructions }}
                            @else
                                Only send {{ $walletAddress->symbol }} to this address. Other cryptocurrencies will be lost.
                            @endif
                        </small>
                    </div>
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
                        @if($walletAddress->qr_code_image)
                            <img src="{{ $walletAddress->qr_code_url }}" alt="Deposit QR Code for {{ $walletAddress->name }}" class="img-fluid">
                        @else
                            <div class="text-center text-muted">
                                <i class="bi bi-qr-code" style="font-size: 3rem;"></i>
                                <p class="mt-2">QR Code not available</p>
                            </div>
                        @endif
                        <p class="mt-3 text-muted">Scan with your wallet app</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endforeach
@else
    <div class="row">
        <div class="col-12">
            <div class="alert alert-warning text-center">
                <h5><i class="bi bi-exclamation-triangle"></i> No Deposit Addresses Available</h5>
                <p class="mb-0">Please contact support to set up deposit addresses.</p>
            </div>
        </div>
    </div>
@endif

<!-- Instructions -->
<div class="row">
    <div class="col-12">
        <div class="instruction-card">
            <h6><i class="bi bi-list-ol"></i> How to Deposit</h6>
            <ol>
                <li>Copy the deposit address above or scan the QR code</li>
                <li>Open your USDT wallet (TRC20 network)</li>
                <li>Send USDT to the provided address</li>
                <li>Wait for network confirmation (usually 5-10 minutes)</li>
                <li>Your funds will appear in your wallet automatically</li>
            </ol>
        </div>
    </div>
</div>

<!-- Important Notes -->
<div class="row">
    <div class="col-12">
        <div class="alert alert-warning">
            <h6><i class="bi bi-exclamation-triangle"></i> Important Notes</h6>
            <ul class="mb-0">
                <li>Only send USDT on the TRC20 network</li>
                <li>Minimum deposit: 10 USDT</li>
                <li>Deposits are processed automatically</li>
                <li>Contact support if you don't see your deposit within 30 minutes</li>
            </ul>
        </div>
    </div>
</div>

<!-- Recent Deposits -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-clock-history"></i> Recent Deposits</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Deposit ID</th>
                                <th>Amount</th>
                                <th>Currency</th>
                                <th>Network</th>
                                <th>Status</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentDeposits as $deposit)
                            <tr>
                                <td>
                                    <small class="text-muted">{{ $deposit->deposit_id }}</small>
                                </td>
                                <td>
                                    <span class="text-success fw-bold">
                                        +{{ number_format($deposit->amount, 2) }}
                                    </span>
                                </td>
                                <td>{{ $deposit->currency }}</td>
                                <td>{{ $deposit->network }}</td>
                                <td>
                                    <span class="badge bg-{{ $deposit->status === 'approved' ? 'success' : ($deposit->status === 'pending' ? 'warning' : 'danger') }}">
                                        {{ ucfirst($deposit->status) }}
                                    </span>
                                </td>
                                <td>{{ $deposit->created_at->format('M d, Y H:i') }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted">
                                    No deposits found
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Deposit Modal -->
<div class="modal fade" id="depositModal" tabindex="-1" aria-labelledby="depositModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="depositModalLabel">
                    <i class="bi bi-plus-circle"></i> Submit New Deposit
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="depositForm" action="{{ route('customer.wallet.deposit.submit') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="amount" class="form-label">Amount <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="amount" name="amount" step="0.01" min="0.01" value="{{ old('amount') }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="currency" class="form-label">Currency <span class="text-danger">*</span></label>
                            <select class="form-select" id="currency" name="currency" required>
                                <option value="">Select Currency</option>
                                @foreach($walletAddresses->pluck('symbol')->unique() as $symbol)
                                    <option value="{{ $symbol }}" {{ old('currency') == $symbol ? 'selected' : '' }}>{{ $symbol }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="network" class="form-label">Network <span class="text-danger">*</span></label>
                            <select class="form-select" id="network" name="network" required>
                                <option value="">Select Network</option>
                                @foreach($walletAddresses->pluck('network')->filter()->unique() as $network)
                                    <option value="{{ $network }}" {{ old('network') == $network ? 'selected' : '' }}>{{ $network }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="proof_image" class="form-label">Proof Image <span class="text-danger">*</span></label>
                            <input type="file" class="form-control" id="proof_image" name="proof_image" accept="image/*" required>
                            <div class="form-text">Upload screenshot or receipt of your deposit transaction</div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="notes" class="form-label">Additional Notes (Optional)</label>
                        <textarea class="form-control" id="notes" name="notes" rows="3" placeholder="Any additional information about your deposit...">{{ old('notes') }}</textarea>
                    </div>
                    
                    <div class="alert alert-info">
                        <h6><i class="bi bi-info-circle"></i> Important Information</h6>
                        <ul class="mb-0">
                            <li>Your deposit will be reviewed by our team</li>
                            <li>Processing time: 1-24 hours</li>
                            <li>Make sure the proof image clearly shows the transaction details</li>
                            <li>Only send the exact amount specified</li>
                        </ul>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle"></i> Submit Deposit
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function copyToClipboard(text, elementId) {
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
    
    // Handle deposit form submission
    $(document).ready(function() {
        $('#depositForm').on('submit', function(e) {
            const form = $(this);
            const submitBtn = form.find('button[type="submit"]');
            const originalBtnText = submitBtn.html();
            
            // Disable submit button to prevent double submission
            submitBtn.prop('disabled', true).html('<i class="bi bi-hourglass-split"></i> Submitting...');
            
            // Form will submit normally, but we can add validation feedback
            const amount = $('#amount').val();
            const currency = $('#currency').val();
            const network = $('#network').val();
            const proofImage = $('#proof_image').val();
            
            if (!amount || amount <= 0) {
                e.preventDefault();
                alert('Please enter a valid amount');
                submitBtn.prop('disabled', false).html(originalBtnText);
                return false;
            }
            
            if (!currency) {
                e.preventDefault();
                alert('Please select a currency');
                submitBtn.prop('disabled', false).html(originalBtnText);
                return false;
            }
            
            if (!network) {
                e.preventDefault();
                alert('Please select a network');
                submitBtn.prop('disabled', false).html(originalBtnText);
                return false;
            }
            
            if (!proofImage) {
                e.preventDefault();
                alert('Please upload a proof image');
                submitBtn.prop('disabled', false).html(originalBtnText);
                return false;
            }
            
            // If all validations pass, form will submit
            // The server will handle the response
        });
        
        // Reset form when modal is closed
        $('#depositModal').on('hidden.bs.modal', function() {
            $('#depositForm')[0].reset();
            $('#depositForm').find('button[type="submit"]').prop('disabled', false).html('<i class="bi bi-check-circle"></i> Submit Deposit');
        });
        
        // Auto-refresh to check for new deposits (optional - can be disabled)
        // setInterval(function() {
        //     location.reload();
        // }, 30000); // Refresh every 30 seconds
    });
</script>
@endpush
