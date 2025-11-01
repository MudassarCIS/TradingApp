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
    
    .qr-code-container img {
        max-width: 200px;
        max-height: 200px;
        width: auto;
        height: auto;
        object-fit: contain;
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
                        <i class="bi bi-wallet2"></i> Admin Deposits Address - {{ $walletAddress->name }} ({{ $walletAddress->symbol }})
                        @if($walletAddress->network)
                            <span class="badge bg-info ms-2">{{ $walletAddress->network }}</span>
                        @endif
                    </h5>
                </div>
                <div class="card-body">
                    <div class="address-container">
                        <code id="depositAddress{{ $walletAddress->id }}">{{ $walletAddress->wallet_address }}</code>
                    </div>
                    <button class="btn btn-primary" onclick="copyToClipboard(event, '{{ $walletAddress->wallet_address }}', 'depositAddress{{ $walletAddress->id }}')">
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
                    <h5 class="mb-0"><i class="bi bi-qr-code"></i> Admin Deposits Address QR code Scan</h5>
                </div>
                <div class="card-body">
                    <div class="qr-code-container">
                        @if($walletAddress->qr_code_image && $walletAddress->qr_code_url)
                            <img src="{{ $walletAddress->qr_code_url }}" alt="Deposit QR Code for {{ $walletAddress->name }}" onerror="this.onerror=null; this.src='data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'200\' height=\'200\'%3E%3Ctext x=\'50%25\' y=\'50%25\' text-anchor=\'middle\' dy=\'.3em\' fill=\'%23999\'%3EQR Code%3C/text%3E%3C/svg%3E';">
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

<!-- Instructions and Important Notes -->
<div class="row">
    <div class="col-md-6 mb-4">
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
    <div class="col-md-6 mb-4">
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
                <input type="hidden" id="invoice_id" name="invoice_id" value="{{ old('invoice_id', $invoiceId) }}">
                <input type="hidden" id="invoice_title" name="invoice_title" value="{{ old('invoice_title', $selectedInvoice ? $selectedInvoice->invoice_type : '') }}">
                <div class="modal-body">
                    <div class="row mb-3" id="invoice-dropdown-container">
                        <div class="col-12">
                            <label for="invoice-dropdown" class="form-label">Invoice (Optional)</label>
                            <select class="form-select" id="invoice-dropdown" name="invoice_dropdown">
                                <option value="">Select an invoice (optional)</option>
                                @foreach($unpaidInvoices as $invoice)
                                    <option value="{{ $invoice->id }}" 
                                        data-amount="{{ $invoice->amount }}"
                                        data-invoice-title="{{ $invoice->invoice_type }}"
                                        {{ old('invoice_id', $invoiceId) == $invoice->id ? 'selected' : '' }}>
                                        Invoice #{{ $invoice->id }} (${{ number_format($invoice->amount, 2) }})
                                    </option>
                                @endforeach
                            </select>
                            <div class="form-text">Select an unpaid invoice to auto-fill the amount</div>
                        </div>
                    </div>
                    
                    <div class="row mb-3" id="invoice-title-row" style="{{ $selectedInvoice ? '' : 'display: none;' }}">
                        <div class="col-12">
                            <label for="invoice_title_display" class="form-label">Invoice Title</label>
                            <input type="text" class="form-control" id="invoice_title_display" value="{{ $selectedInvoice ? $selectedInvoice->invoice_type : '' }}" readonly>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="amount" class="form-label">Amount <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="amount" name="amount" step="0.01" min="0.01" value="{{ old('amount', $selectedInvoice ? $selectedInvoice->amount : '') }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="currency" class="form-label">Currency <span class="text-danger">*</span></label>
                            <select class="form-select" id="currency" name="currency" required>
                                <option value="USDT" {{ old('currency', 'USDT') == 'USDT' ? 'selected' : '' }}>USDT</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="network" class="form-label">Network <span class="text-danger">*</span></label>
                            <select class="form-select" id="network" name="network" required>
                                <option value="">Select Network</option>
                                <option value="TRC20" {{ old('network') == 'TRC20' ? 'selected' : '' }}>TRC20 (USDT)</option>
                                <option value="ERC20" {{ old('network') == 'ERC20' ? 'selected' : '' }}>ERC20 (USDT)</option>
                                <option value="BEP20" {{ old('network') == 'BEP20' ? 'selected' : '' }}>BEP20 (USDT)</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="trans_id" class="form-label">Transaction ID <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="trans_id" name="trans_id" value="{{ old('trans_id') }}" placeholder="Enter blockchain transaction ID" required>
                            <div class="form-text">Enter the transaction hash/ID from your wallet</div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-12 mb-3">
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
    function copyToClipboard(event, text, elementId) {
        // Prevent default if event is provided
        if (event) {
            event.preventDefault();
        }
        
        navigator.clipboard.writeText(text).then(function() {
            // Show success message
            const btn = event ? event.target : document.querySelector(`button[onclick*="${elementId}"]`);
            if (!btn) {
                console.warn('Could not find button element');
                return;
            }
            
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
        // Check if invoice_id exists in URL
        const urlParams = new URLSearchParams(window.location.search);
        const invoiceIdFromUrl = urlParams.get('invoice_id');
        const invoiceIdInput = $('#invoice_id');
        const invoiceDropdown = $('#invoice-dropdown');
        const invoiceDropdownContainer = $('#invoice-dropdown-container');
        const amountInput = $('#amount');
        
        const invoiceTitleInput = $('#invoice_title');
        let invoiceTitleDisplay = $('#invoice_title_display');
        const currencySelect = $('#currency');
        
        // Auto-set currency to USDT (always USDT for invoices)
        currencySelect.val('USDT');
        
        // If invoice_id is in URL, fetch invoice details and auto-fill amount
        if (invoiceIdFromUrl) {
            // Fetch invoice details and auto-fill amount
            fetch(`/customer/invoices/${invoiceIdFromUrl}/details`)
                .then(res => res.json())
                .then(data => {
                    if (data.amount) {
                        amountInput.val(data.amount);
                        amountInput.prop('readonly', true); // Lock amount field
                        invoiceIdInput.val(data.id);
                        invoiceTitleInput.val(data.invoice_title || data.invoice_type);
                        currencySelect.val('USDT'); // Auto-set to USDT
                        
                        // Show invoice title if field exists
                        if (invoiceTitleDisplay.length) {
                            invoiceTitleDisplay.val(data.invoice_title || data.invoice_type);
                            invoiceTitleDisplay.closest('#invoice-title-row').show();
                        }
                        
                        // Ensure the dropdown is set to the correct invoice
                        invoiceDropdown.val(data.id).trigger('change');
                    }
                })
                .catch(err => {
                    console.error('Error fetching invoice details:', err);
                });
        }
        
        // Update amount and invoice details on invoice selection from dropdown
        invoiceDropdown.on('change', function() {
            const selectedOption = $(this).find('option:selected');
            const selectedAmount = selectedOption.data('amount');
            const selectedInvoiceId = $(this).val();
            const selectedInvoiceTitle = selectedOption.data('invoice-title');
            
            if (selectedAmount && selectedInvoiceId) {
                amountInput.val(selectedAmount);
                amountInput.prop('readonly', true); // Lock amount when invoice selected
                invoiceIdInput.val(selectedInvoiceId);
                invoiceTitleInput.val(selectedInvoiceTitle || '');
                currencySelect.val('USDT'); // Auto-set to USDT
                
                // Show invoice title field
                if (invoiceTitleDisplay.length) {
                    invoiceTitleDisplay.val(selectedInvoiceTitle || '');
                    invoiceTitleDisplay.closest('#invoice-title-row').show();
                } else {
                    // Create invoice title display if it doesn't exist
                    const titleRow = $('<div class="row mb-3" id="invoice-title-row"><div class="col-12"><label for="invoice_title_display" class="form-label">Invoice Title</label><input type="text" class="form-control" id="invoice_title_display" value="' + (selectedInvoiceTitle || '') + '" readonly></div></div>');
                    invoiceDropdownContainer.after(titleRow);
                    invoiceTitleDisplay = $('#invoice_title_display');
                }
            } else {
                invoiceIdInput.val('');
                invoiceTitleInput.val('');
                amountInput.prop('readonly', false); // Unlock amount if no invoice selected
                if (invoiceTitleDisplay.length) {
                    invoiceTitleDisplay.closest('#invoice-title-row').hide();
                }
            }
        });
        
        // Auto-open modal if invoice_id is in URL
        if (invoiceIdFromUrl && $('#depositModal').length) {
            $('#depositModal').modal('show');
        }
        
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
            
            // Reset invoice-related fields
            invoiceIdInput.val('');
            invoiceTitleInput.val('');
            amountInput.prop('readonly', false);
            currencySelect.val('USDT');
            
            // Hide invoice title display if it exists
            if (invoiceTitleDisplay.length) {
                invoiceTitleDisplay.closest('#invoice-title-row').hide();
            }
            
            // Clear invoice_id from URL if modal is closed
            if (invoiceIdFromUrl) {
                const url = new URL(window.location);
                url.searchParams.delete('invoice_id');
                window.history.replaceState({}, '', url);
            }
        });
        
        // Auto-refresh to check for new deposits (optional - can be disabled)
        // setInterval(function() {
        //     location.reload();
        // }, 30000); // Refresh every 30 seconds
    });
</script>
@endpush
