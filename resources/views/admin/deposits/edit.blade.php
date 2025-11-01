@extends('layouts.admin-layout')

@section('title', 'Edit Deposit - AI Trade App')
@section('page-title', 'Edit Deposit')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="bi bi-pencil"></i> Edit Deposit: {{ $deposit->deposit_id }}
                </h5>
            </div>
            <div class="card-body">
                <form id="editDepositForm" action="{{ route('admin.deposits.update', $deposit->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="deposit_id" class="form-label">Deposit ID</label>
                            <input type="text" class="form-control" id="deposit_id" value="{{ $deposit->deposit_id }}" readonly>
                        </div>
                        <div class="col-md-6">
                            <label for="user_info" class="form-label">User</label>
                            <input type="text" class="form-control" value="{{ $deposit->user->name }} ({{ $deposit->user->email }})" readonly>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="amount" class="form-label">Amount</label>
                            <input type="text" class="form-control" value="${{ number_format($deposit->amount, 2) }} {{ $deposit->currency }}" readonly>
                        </div>
                        <div class="col-md-4">
                            <label for="network" class="form-label">Network</label>
                            <input type="text" class="form-control" value="{{ $deposit->network }}" readonly>
                        </div>
                        <div class="col-md-4">
                            <label for="current_status" class="form-label">Current Status</label>
                            <input type="text" class="form-control" value="{{ ucfirst($deposit->status) }}" readonly>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                            <select class="form-select" id="status" name="status" required>
                                <option value="pending" {{ $deposit->status === 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="processing" {{ $deposit->status === 'processing' ? 'selected' : '' }}>Processing</option>
                                <option value="approved" {{ $deposit->status === 'approved' ? 'selected' : '' }}>Approved</option>
                                <option value="rejected" {{ $deposit->status === 'rejected' ? 'selected' : '' }}>Rejected</option>
                                <option value="cancelled" {{ $deposit->status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                            </select>
                        </div>
                        @if($deposit->invoice)
                        <div class="col-md-6">
                            <label for="invoice_info" class="form-label">Invoice</label>
                            <input type="text" class="form-control" value="{{ $deposit->invoice->invoice_type }} - {{ $deposit->invoice->status }}" readonly>
                        </div>
                        @endif
                    </div>
                    
                    @if($deposit->status === 'rejected' || request('status') === 'rejected')
                    <div class="row mb-3">
                        <div class="col-12">
                            <label for="rejection_reason" class="form-label">Rejection Reason</label>
                            <textarea class="form-control" id="rejection_reason" name="rejection_reason" rows="3">{{ $deposit->rejection_reason }}</textarea>
                        </div>
                    </div>
                    @endif
                    
                    <div class="row mb-3">
                        <div class="col-12">
                            <label for="notes" class="form-label">Notes</label>
                            <textarea class="form-control" id="notes" name="notes" rows="3">{{ $deposit->notes }}</textarea>
                        </div>
                    </div>
                    
                    @if($deposit->proof_image)
                    <div class="row mb-3">
                        <div class="col-12">
                            <label class="form-label">Proof Image</label>
                            <div>
                                <a href="{{ $deposit->proof_image_url }}" target="_blank" class="btn btn-outline-primary">
                                    <i class="bi bi-image"></i> View Proof Image
                                </a>
                            </div>
                        </div>
                    </div>
                    @endif
                    
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i> 
                        <strong>Note:</strong> Changing status to "Approved" will:
                        <ul class="mb-0 mt-2">
                            <li>Credit the user's wallet</li>
                            <li>Update associated invoice status to "Paid" (if any)</li>
                            <li>Distribute referral bonuses to 3 levels (if invoice is Sharing Nexa or Rent A Bot)</li>
                        </ul>
                    </div>
                    
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('admin.deposits.index') }}" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Back to Deposits
                        </a>
                        <button type="submit" class="btn btn-primary" id="submitBtn">
                            <i class="bi bi-check-circle"></i> Update Deposit
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    $('#editDepositForm').on('submit', function(e) {
        e.preventDefault();
        
        var form = $(this);
        var currentStatus = '{{ $deposit->status }}';
        var newStatus = $('#status').val();
        
        if (newStatus !== currentStatus) {
            if (!confirm('Are you sure you want to change the status from ' + currentStatus.toUpperCase() + ' to ' + newStatus.toUpperCase() + '?')) {
                return false;
            }
        }
        
        var submitBtn = $('#submitBtn');
        var originalText = submitBtn.html();
        submitBtn.prop('disabled', true).html('<i class="bi bi-hourglass-split"></i> Updating...');
        
        $.ajax({
            url: form.attr('action'),
            type: 'PUT',
            data: form.serialize(),
            success: function(response) {
                if (response.success) {
                    alert(response.success);
                    window.location.href = "{{ route('admin.deposits.index') }}";
                }
            },
            error: function(xhr) {
                var errorMsg = xhr.responseJSON?.error || 'An error occurred while updating the deposit';
                alert('Error: ' + errorMsg);
                submitBtn.prop('disabled', false).html(originalText);
            }
        });
        
        return false;
    });
    
    // Show/hide rejection reason based on status
    $('#status').on('change', function() {
        if ($(this).val() === 'rejected') {
            $('#rejection_reason').closest('.row').show();
        } else {
            $('#rejection_reason').closest('.row').hide();
        }
    }).trigger('change');
});
</script>
@endpush

