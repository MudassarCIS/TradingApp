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

<!-- Trade Credentials Section -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-key"></i> Trade Credentials</h5>
            </div>
            <div class="card-body">
                <!-- Existing Credentials -->
                <div id="existingCredentials" class="mb-4">
                    <h6 class="mb-3">Existing Credentials</h6>
                    <div id="credentialsList">
                        @if(isset($tradeCredentials) && $tradeCredentials->count() > 0)
                            @foreach($tradeCredentials as $credential)
                                <div class="card mb-2">
                                    <div class="card-body">
                                        <div class="row align-items-center">
                                            <div class="col-md-3">
                                                <strong>Account:</strong> {{ $credential->account_name }}
                                            </div>
                                            <div class="col-md-3">
                                                <strong>Connector:</strong> {{ $credential->connector->connector_name ?? 'N/A' }}
                                            </div>
                                            <div class="col-md-3">
                                                <strong>API Key:</strong> {{ substr($credential->api_key, 0, 8) }}...
                                            </div>
                                            <div class="col-md-3">
                                                <span class="badge bg-{{ $credential->active_credentials ? 'success' : 'warning' }}">
                                                    {{ $credential->active_credentials ? 'Active' : 'Inactive' }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <p class="text-muted">No trade credentials added yet.</p>
                        @endif
                    </div>
                </div>

                <!-- Add New Credentials Form -->
                <div id="credentialsFormContainer">
                    <h6 class="mb-3">Add New Credentials</h6>
                    <form id="tradeCredentialsForm">
                        <div id="credentialsForms">
                            <!-- First credential form -->
                            <div class="credential-form-item card mb-3">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="account_name" class="form-label">Account Name <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control account-name" name="account_name[]" required>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="connector_id" class="form-label">Connector <span class="text-danger">*</span></label>
                                            <select class="form-select connector-select" name="connector_id[]" required>
                                                <option value="">Select Connector</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="api_key" class="form-label">API Key (Public Key) <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control api-key" name="api_key[]" required>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="secret_key" class="form-label">Secret Key <span class="text-danger">*</span></label>
                                            <input type="password" class="form-control secret-key" name="secret_key[]" required>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-secondary" id="addMoreBtn">
                                <i class="bi bi-plus-circle"></i> Add More
                            </button>
                            <button type="submit" class="btn btn-primary" id="saveCredentialsBtn">
                                <i class="bi bi-save"></i> Save Credentials
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    let connectors = [];
    let formCounter = 0;

    // Load connectors on page load
    loadConnectors();

    function loadConnectors() {
        $.ajax({
            url: '{{ route("customer.profile.connectors") }}',
            method: 'GET',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success && response.connectors) {
                    connectors = response.connectors;
                    // Populate all connector selects
                    $('.connector-select').each(function() {
                        populateConnectorSelect($(this));
                    });
                } else {
                    showAlert('error', 'Failed to load connectors');
                }
            },
            error: function(xhr) {
                console.error('Error loading connectors:', xhr);
                showAlert('error', 'Failed to load connectors. Please refresh the page.');
            }
        });
    }

    function populateConnectorSelect($select) {
        $select.empty().append('<option value="">Select Connector</option>');
        connectors.forEach(function(connector) {
            $select.append('<option value="' + connector.id + '">' + connector.name + '</option>');
        });
    }

    // Add More functionality
    $('#addMoreBtn').on('click', function() {
        formCounter++;
        const newForm = $('.credential-form-item').first().clone();
        newForm.find('input').val('');
        newForm.find('select').val('');
        newForm.find('.account-name').attr('name', 'account_name[]');
        newForm.find('.connector-select').attr('name', 'connector_id[]');
        newForm.find('.api-key').attr('name', 'api_key[]');
        newForm.find('.secret-key').attr('name', 'secret_key[]');
        
        // Populate connector select for new form
        populateConnectorSelect(newForm.find('.connector-select'));
        
        // Add remove button
        newForm.find('.card-body').append(
            '<div class="text-end mt-2">' +
            '<button type="button" class="btn btn-sm btn-danger remove-form-btn">' +
            '<i class="bi bi-trash"></i> Remove</button>' +
            '</div>'
        );
        
        $('#credentialsForms').append(newForm);
    });

    // Remove form item
    $(document).on('click', '.remove-form-btn', function() {
        if ($('.credential-form-item').length > 1) {
            $(this).closest('.credential-form-item').remove();
        } else {
            showAlert('warning', 'At least one credential form is required');
        }
    });

    // Form submission - Two-step save process
    $('#tradeCredentialsForm').on('submit', function(e) {
        e.preventDefault();
        
        const $btn = $('#saveCredentialsBtn');
        const originalText = $btn.html();
        $btn.prop('disabled', true).html('<i class="bi bi-hourglass-split"></i> Saving...');

        const forms = $('.credential-form-item');
        let savedCount = 0;
        let totalCount = forms.length;
        let errors = [];

        // Process each credential form
        forms.each(function(index) {
            const $form = $(this);
            const accountName = $form.find('.account-name').val();
            const connectorId = $form.find('.connector-select').val();
            const apiKey = $form.find('.api-key').val();
            const secretKey = $form.find('.secret-key').val();

            if (!accountName || !connectorId || !apiKey || !secretKey) {
                errors.push('Form ' + (index + 1) + ' has missing fields');
                return;
            }

            // Step 1: Save account
            $.ajax({
                url: '{{ route("customer.profile.trade-credentials.account") }}',
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: {
                    account_name: accountName
                },
                success: function(response) {
                    if (response.success) {
                        const credentialId = response.credential_id;
                        
                        // Step 2: Save connector with keys
                        $.ajax({
                            url: '{{ route("customer.profile.trade-credentials.connector") }}',
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            data: {
                                credential_id: credentialId,
                                connector_id: connectorId,
                                api_key: apiKey,
                                secret_key: secretKey
                            },
                            success: function(connectorResponse) {
                                savedCount++;
                                if (savedCount === totalCount) {
                                    if (errors.length > 0) {
                                        showAlert('warning', 'Some credentials saved with errors: ' + errors.join(', '));
                                    } else {
                                        showAlert('success', 'All credentials saved successfully!');
                                        // Reset form
                                        $('#tradeCredentialsForm')[0].reset();
                                        // Keep only first form
                                        $('.credential-form-item').not(':first').remove();
                                        // Reload page after 2 seconds to show updated credentials
                                        setTimeout(function() {
                                            location.reload();
                                        }, 2000);
                                    }
                                    $btn.prop('disabled', false).html(originalText);
                                }
                            },
                            error: function(xhr) {
                                savedCount++;
                                const errorMsg = xhr.responseJSON?.message || 'Failed to save connector';
                                errors.push('Form ' + (index + 1) + ': ' + errorMsg);
                                if (savedCount === totalCount) {
                                    showAlert('error', 'Some credentials failed to save: ' + errors.join(', '));
                                    $btn.prop('disabled', false).html(originalText);
                                }
                            }
                        });
                    } else {
                        savedCount++;
                        errors.push('Form ' + (index + 1) + ': ' + (response.message || 'Failed to save account'));
                        if (savedCount === totalCount) {
                            showAlert('error', 'Some credentials failed to save: ' + errors.join(', '));
                            $btn.prop('disabled', false).html(originalText);
                        }
                    }
                },
                error: function(xhr) {
                    savedCount++;
                    const errorMsg = xhr.responseJSON?.message || 'Failed to save account';
                    errors.push('Form ' + (index + 1) + ': ' + errorMsg);
                    if (savedCount === totalCount) {
                        showAlert('error', 'Some credentials failed to save: ' + errors.join(', '));
                        $btn.prop('disabled', false).html(originalText);
                    }
                }
            });
        });

        if (errors.length === totalCount) {
            $btn.prop('disabled', false).html(originalText);
        }
    });

    function showAlert(type, message) {
        const alertClass = type === 'success' ? 'alert-success' : 
                          type === 'error' ? 'alert-danger' : 
                          type === 'warning' ? 'alert-warning' : 'alert-info';
        
        const alertHtml = '<div class="alert ' + alertClass + ' alert-dismissible fade show" role="alert">' +
            message +
            '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>' +
            '</div>';
        
        $('#credentialsFormContainer').prepend(alertHtml);
        
        // Auto-dismiss after 5 seconds
        setTimeout(function() {
            $('.alert').fadeOut(function() {
                $(this).remove();
            });
        }, 5000);
    }
});
</script>
@endpush
