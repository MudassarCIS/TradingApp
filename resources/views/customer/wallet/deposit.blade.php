@extends('layouts.customer-layout')

@section('title', 'Deposit - AI Trade App')
@section('page-title', 'Deposit Funds')

@push('styles')
<style>
    .admin-address-section {
        background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
        color: white;
        border-radius: 15px;
        padding: 1.25rem;
        margin-bottom: 2rem;
        box-shadow: 0 10px 30px rgba(17, 153, 142, 0.3);
    }
    
    .admin-address-section h3 {
        font-size: 1.2rem;
        font-weight: 700;
        margin-bottom: 1rem;
        color: white;
    }
    
    .address-display-box {
        background: rgba(255, 255, 255, 0.15);
        backdrop-filter: blur(10px);
        border: 2px solid rgba(255, 255, 255, 0.3);
        border-radius: 10px;
        padding: 0.75rem;
        margin-bottom: 0.75rem;
        word-break: break-all;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.5rem;
    }
    
    .address-display-box code {
        color: white;
        font-size: 0.8rem;
        font-weight: 500;
        background: transparent;
        padding: 0;
        flex: 1;
        margin: 0;
    }
    
    .copy-btn-icon {
        background: rgba(255, 255, 255, 0.2);
        border: 2px solid rgba(255, 255, 255, 0.4);
        color: white;
        padding: 0.5rem;
        border-radius: 8px;
        width: 36px;
        height: 36px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s ease;
        flex-shrink: 0;
        cursor: pointer;
    }
    
    .copy-btn-icon:hover {
        background: rgba(255, 255, 255, 0.3);
        border-color: rgba(255, 255, 255, 0.6);
        color: white;
        transform: translateY(-2px);
    }
    
    .copy-btn-icon i {
        font-size: 1.1rem;
    }
    
    .qr-code-box {
        background: white;
        border-radius: 12px;
        padding: 1rem;
        text-align: center;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    }
    
    .qr-code-box img {
        max-width: 120px;
        max-height: 120px;
        width: auto;
        height: auto;
        object-fit: contain;
        border-radius: 8px;
    }
    
    .qr-code-box p {
        font-size: 0.75rem;
        margin-top: 0.5rem;
    }
    
    .deposit-form-card {
        background: white;
        border-radius: 20px;
        padding: 2.5rem;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        margin-bottom: 2rem;
    }
    
    .deposit-form-card h4 {
        color: #11998e;
        font-weight: 700;
        margin-bottom: 1.5rem;
        font-size: 1.5rem;
    }
    
    .form-label {
        font-weight: 600;
        color: #495057;
        margin-bottom: 0.5rem;
    }
    
    .form-control, .form-select {
        border-radius: 10px;
        border: 2px solid #e0e0e0;
        padding: 0.75rem 1rem;
        transition: all 0.3s ease;
    }
    
    .form-control:focus, .form-select:focus {
        border-color: #11998e;
        box-shadow: 0 0 0 0.2rem rgba(17, 153, 142, 0.25);
    }
    
    .btn-submit-deposit {
        background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
        border: none;
        color: white;
        padding: 1rem 2.5rem;
        border-radius: 50px;
        font-weight: 700;
        font-size: 1.1rem;
        transition: all 0.3s ease;
        box-shadow: 0 5px 15px rgba(17, 153, 142, 0.3);
    }
    
    .btn-submit-deposit:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(17, 153, 142, 0.4);
        color: white;
    }
    
    .info-alert {
        background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
        border-left: 4px solid #2196f3;
        border-radius: 10px;
        padding: 1.25rem;
        margin-bottom: 1.5rem;
    }
    
    .info-alert h6 {
        color: #1976d2;
        font-weight: 700;
        margin-bottom: 0.75rem;
    }
    
    .info-alert ul {
        margin: 0;
        padding-left: 1.5rem;
        color: #424242;
    }
    
    .info-alert li {
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

@if($walletAddresses->count() > 0)
    @php $firstAddress = $walletAddresses->first(); @endphp
    
    <!-- Admin Address & QR Code Section (Green Section) -->
    <div class="admin-address-section">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h3><i class="bi bi-wallet2"></i> Admin Deposit Address</h3>
                <div class="address-display-box">
                    <code id="depositAddress">{{ $firstAddress->wallet_address }}</code>
                    <button type="button" class="copy-btn-icon" onclick="copyToClipboard(event, '{{ $firstAddress->wallet_address }}', 'depositAddress')" title="Copy address to clipboard">
                        <i class="bi bi-copy"></i>
                    </button>
                </div>
            </div>
            <div class="col-md-4">
                <div class="qr-code-box">
                    @if($firstAddress->qr_code_image && $firstAddress->qr_code_url)
                        <img src="{{ $firstAddress->qr_code_url }}" alt="Deposit QR Code" onerror="this.onerror=null; this.style.display='none'; this.nextElementSibling.style.display='block';">
                        <div style="display: none; color: #999;">
                            <i class="bi bi-qr-code" style="font-size: 2rem;"></i>
                            <p class="mt-2 mb-0" style="font-size: 0.75rem;">QR Code not available</p>
                        </div>
                    @else
                        <div style="color: #999;">
                            <i class="bi bi-qr-code" style="font-size: 2rem;"></i>
                            <p class="mt-2 mb-0" style="font-size: 0.75rem;">QR Code not available</p>
                        </div>
                    @endif
                    <p class="mt-2 text-muted mb-0">
                        <i class="bi bi-phone"></i> Scan with your wallet app
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Deposit Form Section -->
    <div class="deposit-form-card">
        <h4><i class="bi bi-plus-circle"></i> Submit Deposit</h4>
        
        <form id="depositForm" action="{{ route('customer.wallet.deposit.submit') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="hidden" id="invoice_id" name="invoice_id" value="{{ old('invoice_id', $invoiceId) }}">
            <input type="hidden" id="invoice_title" name="invoice_title" value="{{ old('invoice_title', $selectedInvoice ? $selectedInvoice->invoice_type : '') }}">
            
            <!-- Invoice Selection -->
            <div class="row mb-3">
                <div class="col-12">
                    <label for="invoice-dropdown" class="form-label">Invoice (Optional)</label>
                    <select class="form-select" id="invoice-dropdown" name="invoice_dropdown">
                        <option value="">Select an invoice (optional)</option>
                        @foreach($unpaidInvoices as $invoice)
                            @php
                                $displayName = '';
                                if ($invoice->invoice_type === 'NEXA') {
                                    if ($invoice->plan) {
                                        $displayName = ' - [' . $invoice->plan->name . ']';
                                    } else {
                                        // Show invoice type if no plan
                                        $displayName = ' - ' . $invoice->invoice_type;
                                    }
                                } elseif ($invoice->invoice_type === 'PEX') {
                                    if ($invoice->rentBotPackage && $invoice->rentBotPackage->package_name) {
                                        $displayName = ' - ' . $invoice->rentBotPackage->package_name;
                                    } elseif ($invoice->rent_bot_package_id) {
                                        // Fallback: try to get package name by matching amount
                                        $matchedPackage = \App\Models\RentBotPackage::where('id', $invoice->rent_bot_package_id)->first();
                                        if ($matchedPackage && $matchedPackage->package_name) {
                                            $displayName = ' - ' . $matchedPackage->package_name;
                                        } else {
                                            // Show invoice type if no package name found
                                            $displayName = ' - ' . $invoice->invoice_type;
                                        }
                                    } else {
                                        // Fallback: match by amount and get package name
                                        $matchedPackage = \App\Models\RentBotPackage::where('amount', $invoice->amount)
                                            ->orderBy('id', 'asc')
                                            ->first();
                                        if ($matchedPackage && $matchedPackage->package_name) {
                                            $displayName = ' - ' . $matchedPackage->package_name;
                                        } else {
                                            // Show invoice type if no package found
                                            $displayName = ' - ' . $invoice->invoice_type;
                                        }
                                    }
                                } elseif ($invoice->invoice_type === 'Upcoming NEXA Profit') {
                                    $displayName = ' - Upcoming NEXA Profit';
                                } else {
                                    // For any other invoice type, show it
                                    $displayName = ' - ' . $invoice->invoice_type;
                                }
                            @endphp
                            <option value="{{ $invoice->id }}" 
                                data-amount="{{ $invoice->amount }}"
                                data-invoice-title="{{ $invoice->invoice_type }}"
                                {{ old('invoice_id', $invoiceId) == $invoice->id ? 'selected' : '' }}>
                                #{{ $invoice->invoice_id ?? $invoice->id }} (${{ number_format($invoice->amount, 2) }}){{ $displayName }}
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
            
            <!-- Amount and Currency -->
            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="amount" class="form-label">Amount <span class="text-danger">*</span></label>
                    <input type="number" class="form-control" id="amount" name="amount" step="0.01" min="0.01" value="{{ old('amount', $selectedInvoice ? $selectedInvoice->amount : '') }}" required>
                </div>
                <div class="col-md-6">
                    <label for="currency" class="form-label">Currency <span class="text-danger">*</span></label>
                    <select class="form-select" id="currency" name="currency" required>
                        <option value="USDT" {{ old('currency', 'USDT') == 'USDT' ? 'selected' : '' }}>USDT</option>
                    </select>
                </div>
            </div>
            
            <!-- Transaction ID and Network -->
            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="trans_id" class="form-label">Transaction ID <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="trans_id" name="trans_id" value="{{ old('trans_id') }}" placeholder="Enter blockchain transaction ID" required>
                    <div class="form-text">Enter the transaction hash/ID from your wallet</div>
                </div>
                <div class="col-md-6">
                    <label for="network" class="form-label">Network <span class="text-danger">*</span></label>
                    <select class="form-select" id="network" name="network" required>
                        <option value="TRC20" {{ old('network') == 'TRC20' ? 'selected' : '' }}>TRC20 (USDT)</option>
                        <option value="ERC20" {{ old('network') == 'ERC20' ? 'selected' : '' }}>ERC20 (USDT)</option>
                        <option value="BEP20" {{ old('network') == 'BEP20' ? 'selected' : '' }}>BEP20 (USDT)</option>
                    </select>
                </div>
            </div>
            
            <!-- Proof Image and Notes -->
            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="proof_image" class="form-label">Proof Image <span class="text-danger">*</span></label>
                    <input type="file" class="form-control" id="proof_image" name="proof_image" accept="image/*" required>
                    <div class="form-text">Upload screenshot or receipt of your deposit transaction</div>
                </div>
                <div class="col-md-6">
                    <label for="notes" class="form-label">Additional Notes (Optional)</label>
                    <textarea class="form-control" id="notes" name="notes" rows="3" placeholder="Any additional information about your deposit...">{{ old('notes') }}</textarea>
                </div>
            </div>
            
            <!-- Submit Button -->
            <div class="row">
                <div class="col-12 text-center">
                    <button type="submit" class="btn btn-submit-deposit">
                        <i class="bi bi-check-circle"></i> Submit Deposit
                    </button>
                </div>
            </div>
        </form>
        
        <!-- Important Information -->
        <div class="info-alert mt-4">
            <h6><i class="bi bi-info-circle"></i> Important Information</h6>
            <ul>
                <li>After paying the amount to the admin address above, you must create a deposit record by filling the form below.</li>
                <li>Your deposit will be reviewed by our team. Processing time: 1-24 hours</li>
                <li>Make sure the proof image clearly shows the transaction details. Only send the exact amount specified</li>
            </ul>
        </div>
    </div>
@else
    <div class="alert alert-warning text-center">
        <h5><i class="bi bi-exclamation-triangle"></i> No Deposit Addresses Available</h5>
        <p class="mb-0">Please contact support to set up deposit addresses.</p>
    </div>
@endif
@endsection

@push('scripts')
<script>
    // Store wallet addresses data
    @php
        $walletAddressesData = $walletAddresses->map(function($addr) {
            return [
                'id' => $addr->id,
                'name' => $addr->name,
                'symbol' => $addr->symbol,
                'wallet_address' => $addr->wallet_address,
                'network' => $addr->network,
                'qr_code_url' => $addr->qr_code_url ? $addr->qr_code_url : '',
                'instructions' => $addr->instructions,
            ];
        })->values()->all();
    @endphp
    const walletAddresses = @json($walletAddressesData);
    
    function copyToClipboard(event, text, elementId) {
        if (event) {
            event.preventDefault();
        }
        
        navigator.clipboard.writeText(text).then(function() {
            const buttonElement = event.target.closest('button') || event.target;
            
            // Create success tooltip
            const tooltip = document.createElement('div');
            tooltip.innerHTML = 'Copied!';
            tooltip.style.cssText = `
                position: absolute;
                background-color: #28a745;
                color: white;
                padding: 8px 16px;
                border-radius: 8px;
                font-size: 0.875rem;
                font-weight: 500;
                z-index: 1050;
                box-shadow: 0 4px 12px rgba(0,0,0,0.3);
                white-space: nowrap;
                pointer-events: none;
            `;
            
            const rect = buttonElement.getBoundingClientRect();
            const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
            const scrollLeft = window.pageXOffset || document.documentElement.scrollLeft;
            
            tooltip.style.top = (rect.top + scrollTop - tooltip.offsetHeight - 10) + 'px';
            tooltip.style.left = (rect.left + scrollLeft + (rect.width / 2) - (tooltip.offsetWidth / 2)) + 'px';
            
            document.body.appendChild(tooltip);
            
            setTimeout(function() {
                if (tooltip.parentNode) {
                    tooltip.remove();
                }
            }, 2000);
        }, function(err) {
            console.error('Could not copy text: ', err);
        });
    }
    
    // Handle deposit form
    $(document).ready(function() {
        const urlParams = new URLSearchParams(window.location.search);
        const invoiceIdFromUrl = urlParams.get('invoice_id');
        const invoiceIdInput = $('#invoice_id');
        const invoiceDropdown = $('#invoice-dropdown');
        const amountInput = $('#amount');
        const invoiceTitleInput = $('#invoice_title');
        const invoiceTitleDisplay = $('#invoice_title_display');
        const currencySelect = $('#currency');
        
        currencySelect.val('USDT');
        
        // If invoice_id is in URL, fetch invoice details
        if (invoiceIdFromUrl) {
            fetch(`/customer/invoices/${invoiceIdFromUrl}/details`)
                .then(res => res.json())
                .then(data => {
                    if (data.amount) {
                        amountInput.val(data.amount);
                        amountInput.prop('readonly', true);
                        invoiceIdInput.val(data.id);
                        invoiceTitleInput.val(data.invoice_title || data.invoice_type);
                        currencySelect.val('USDT');
                        
                        if (invoiceTitleDisplay.length) {
                            invoiceTitleDisplay.val(data.invoice_title || data.invoice_type);
                            invoiceTitleDisplay.closest('#invoice-title-row').show();
                        }
                        
                        invoiceDropdown.val(data.id).trigger('change');
                    }
                })
                .catch(err => {
                    console.error('Error fetching invoice details:', err);
                });
        }
        
        // Update amount and invoice details on invoice selection
        invoiceDropdown.on('change', function() {
            const selectedOption = $(this).find('option:selected');
            const selectedAmount = selectedOption.data('amount');
            const selectedInvoiceId = $(this).val();
            const selectedInvoiceTitle = selectedOption.data('invoice-title');
            
            if (selectedAmount && selectedInvoiceId) {
                amountInput.val(selectedAmount);
                amountInput.prop('readonly', true);
                invoiceIdInput.val(selectedInvoiceId);
                invoiceTitleInput.val(selectedInvoiceTitle || '');
                currencySelect.val('USDT');
                
                if (invoiceTitleDisplay.length) {
                    invoiceTitleDisplay.val(selectedInvoiceTitle || '');
                    invoiceTitleDisplay.closest('#invoice-title-row').show();
                }
            } else {
                invoiceIdInput.val('');
                invoiceTitleInput.val('');
                amountInput.prop('readonly', false);
                if (invoiceTitleDisplay.length) {
                    invoiceTitleDisplay.closest('#invoice-title-row').hide();
                }
            }
        });
        
        // Form submission
        $('#depositForm').on('submit', function(e) {
            const form = $(this);
            const submitBtn = form.find('button[type="submit"]');
            const originalBtnText = submitBtn.html();
            
            submitBtn.prop('disabled', true).html('<i class="bi bi-hourglass-split"></i> Submitting...');
            
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
        });
    });
</script>
@endpush
