@extends('layouts.customer-layout')

@section('title', 'Save Trade Credentials - AI Trade App')
@section('page-title', 'Save Trade Credentials')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-key"></i> Save Trade Credentials</h5>
            </div>
            <div class="card-body">
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
                                            <small class="text-muted">If account doesn't exist, it will be created automatically</small>
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
                            <a href="{{ route('customer.trading.index') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left"></i> Back to Trading
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Existing Trade Credentials Table -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-list-ul"></i> My Trade Credentials</h5>
            </div>
            <div class="card-body">
                @if(isset($tradeCredentials) && $tradeCredentials->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Account Name</th>
                                    <th>Connector</th>
                                    <th>Credential Type</th>
                                    <th>Priority</th>
                                    <th>API Key</th>
                                    <th>Secret Key</th>
                                    <th>Status</th>
                                    <th>Created At</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($tradeCredentials as $index => $credential)
                                    <tr data-credential-id="{{ $credential->id }}" 
                                        data-account-name="{{ $credential->account_name }}"
                                        data-connector-name="{{ $credential->connector_name }}"
                                        data-api-key="{{ $credential->api_key }}"
                                        data-secret-key="{{ $credential->secret_key }}">
                                        <td>{{ $index + 1 }}</td>
                                        <td><strong>{{ $credential->account_name }}</strong></td>
                                        <td>
                                            @if($credential->connector_name)
                                                <span class="badge bg-info">{{ $credential->connector_name }}</span>
                                            @else
                                                <span class="text-muted">N/A</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary">{{ $credential->credential_type ?? 'NEXA' }}</span>
                                        </td>
                                        <td>
                                            @php
                                                $priority = $credential->credential_priority ?? 'none';
                                                $priorityBadgeClass = $priority === 'primary' ? 'bg-primary' : ($priority === 'secondary' ? 'bg-warning' : 'bg-secondary');
                                                $priorityText = ucfirst($priority);
                                            @endphp
                                            <span class="badge {{ $priorityBadgeClass }}" id="priority-badge-{{ $credential->id }}">
                                                {{ $priorityText }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($credential->api_key)
                                                <code>{{ substr($credential->api_key, 0, 12) }}...</code>
                                                <button class="btn btn-sm btn-link p-0 ms-1" type="button" onclick="copyToClipboard('{{ $credential->api_key }}', this)">
                                                    <i class="bi bi-copy" title="Copy"></i>
                                                </button>
                                            @else
                                                <span class="text-muted">Not set</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($credential->secret_key)
                                                <code>{{ substr($credential->secret_key, 0, 12) }}...</code>
                                                <button class="btn btn-sm btn-link p-0 ms-1" type="button" onclick="copyToClipboard('{{ $credential->secret_key }}', this)">
                                                    <i class="bi bi-copy" title="Copy"></i>
                                                </button>
                                            @else
                                                <span class="text-muted">Not set</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="form-check form-switch">
                                                <input class="form-check-input status-toggle" 
                                                       type="checkbox" 
                                                       data-credential-id="{{ $credential->id }}"
                                                       {{ $credential->active_credentials ? 'checked' : '' }}
                                                       id="status-toggle-{{ $credential->id }}">
                                                <label class="form-check-label" for="status-toggle-{{ $credential->id }}">
                                                    <span class="badge bg-{{ $credential->active_credentials ? 'success' : 'warning' }}" id="status-badge-{{ $credential->id }}">
                                                        {{ $credential->active_credentials ? 'Active' : 'Inactive' }}
                                                    </span>
                                                </label>
                                            </div>
                                        </td>
                                        <td>
                                            <small class="text-muted">{{ $credential->created_at->format('M d, Y H:i') }}</small>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="editCredential({{ $credential->id }})" title="Edit">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <div class="btn-group" role="group">
                                                    <button type="button" class="btn btn-sm btn-outline-info dropdown-toggle" data-bs-toggle="dropdown" title="Set Priority">
                                                        <i class="bi bi-star"></i>
                                                    </button>
                                                    <ul class="dropdown-menu">
                                                        <li><a class="dropdown-item" href="#" onclick="setPriority({{ $credential->id }}, 'primary'); return false;">
                                                            <i class="bi bi-star-fill text-primary"></i> Set as Primary
                                                        </a></li>
                                                        <li><a class="dropdown-item" href="#" onclick="setPriority({{ $credential->id }}, 'secondary'); return false;">
                                                            <i class="bi bi-star text-warning"></i> Set as Secondary
                                                        </a></li>
                                                        <li><a class="dropdown-item" href="#" onclick="setPriority({{ $credential->id }}, 'none'); return false;">
                                                            <i class="bi bi-dash-circle"></i> Remove Priority
                                                        </a></li>
                                                    </ul>
                                                </div>
                                                <button type="button" class="btn btn-sm btn-outline-success" onclick="syncCredential({{ $credential->id }}, '{{ $credential->account_name }}', '{{ $credential->connector_name }}')" title="Sync with API">
                                                    <i class="bi bi-arrow-repeat"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteCredential({{ $credential->id }}, '{{ $credential->account_name }}')" title="Delete">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4 mb-4"></div> <!-- Add spacing under table -->
                @else
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i> No trade credentials found. Please add credentials using the form above.
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Add extra spacing at the bottom for better visibility -->
<div class="row">
    <div class="col-12">
        <div class="mb-5"></div>
    </div>
</div>

<!-- Edit Credential Modal -->
<div class="modal fade" id="editCredentialModal" tabindex="-1" aria-labelledby="editCredentialModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editCredentialModalLabel">Edit Trade Credentials</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editCredentialForm">
                <div class="modal-body">
                    <input type="hidden" id="edit_credential_id" name="credential_id">
                    <div class="mb-3">
                        <label for="edit_account_name" class="form-label">Account Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="edit_account_name" name="account_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_connector_id" class="form-label">Connector <span class="text-danger">*</span></label>
                        <select class="form-select" id="edit_connector_id" name="connector_id" required>
                            <option value="">Select Connector</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="edit_api_key" class="form-label">API Key (Public Key) <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="edit_api_key" name="api_key" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_secret_key" class="form-label">Secret Key <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" id="edit_secret_key" name="secret_key" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Credentials</button>
                </div>
            </form>
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

    // Form submission - Single-step save process
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
                savedCount++;
                if (savedCount === totalCount) {
                    showAlert('error', 'Some forms have missing fields: ' + errors.join(', '));
                    $btn.prop('disabled', false).html(originalText);
                }
                return;
            }

            // Save all credentials at once (account will be created via API if not exists)
            $.ajax({
                url: '{{ route("customer.trading.store-credentials") }}',
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: {
                    account_name: accountName,
                    connector_id: connectorId,
                    api_key: apiKey,
                    secret_key: secretKey
                },
                success: function(response) {
                    savedCount++;
                    if (response.success) {
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
                    } else {
                        // Check if account was saved but credentials failed
                        if (response.account_saved && response.credentials_failed) {
                            showAlert('warning', 'Form ' + (index + 1) + ': ' + (response.message || 'Account saved but credentials failed. Please try saving credentials again.'));
                            // Don't add to errors array, as account was saved successfully
                        } else if (response.already_exists) {
                            // Check if account already exists
                            showAlert('warning', 'Form ' + (index + 1) + ': ' + (response.message || 'Account already exists'));
                        } else {
                            errors.push('Form ' + (index + 1) + ': ' + (response.message || 'Failed to save credentials'));
                        }
                        if (savedCount === totalCount) {
                            if (errors.length > 0) {
                                showAlert('error', 'Some credentials failed to save: ' + errors.join(', '));
                            }
                            $btn.prop('disabled', false).html(originalText);
                        }
                    }
                },
                error: function(xhr) {
                    savedCount++;
                    const errorMsg = xhr.responseJSON?.message || xhr.responseJSON?.data?.detail || 'Failed to save credentials';
                    errors.push('Form ' + (index + 1) + ': ' + errorMsg);
                    if (savedCount === totalCount) {
                        showAlert('error', 'Some credentials failed to save: ' + errors.join(', '));
                        $btn.prop('disabled', false).html(originalText);
                    }
                }
            });
        });

        if (totalCount === 0) {
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

    // Copy to clipboard function
    function copyToClipboard(text, button) {
        navigator.clipboard.writeText(text).then(function() {
            const $icon = $(button).find('i');
            const originalClass = $icon.attr('class');
            $icon.removeClass('bi-copy').addClass('bi-check text-success');
            setTimeout(function() {
                $icon.removeClass('bi-check text-success').addClass(originalClass);
            }, 2000);
        }).catch(function(err) {
            console.error('Failed to copy:', err);
            alert('Failed to copy to clipboard');
        });
    }

    // Edit credential function
    window.editCredential = function(credentialId) {
        // Get credential data from table row
        const $row = $('tr[data-credential-id="' + credentialId + '"]');
        
        if ($row.length === 0) {
            showAlert('error', 'Credential not found');
            return;
        }
        
        const accountName = $row.data('account-name');
        const connectorName = $row.data('connector-name');
        const apiKey = $row.data('api-key');
        const secretKey = $row.data('secret-key');
        
        // Populate form
        $('#edit_credential_id').val(credentialId);
        $('#edit_account_name').val(accountName);
        $('#edit_api_key').val(apiKey);
        $('#edit_secret_key').val(secretKey);
        
        // Populate connector dropdown - find connector by name
        const $connectorSelect = $('#edit_connector_id');
        $connectorSelect.empty().append('<option value="">Select Connector</option>');
        connectors.forEach(function(connector) {
            const selected = connector.name === connectorName ? 'selected' : '';
            $connectorSelect.append('<option value="' + connector.id + '" ' + selected + '>' + connector.name + '</option>');
        });
        
        // Show modal
        const editModal = new bootstrap.Modal(document.getElementById('editCredentialModal'));
        editModal.show();
    };

    // Handle edit form submission
    $('#editCredentialForm').on('submit', function(e) {
        e.preventDefault();
        
        const credentialId = $('#edit_credential_id').val();
        const $btn = $(this).find('button[type="submit"]');
        const originalText = $btn.html();
        $btn.prop('disabled', true).html('<i class="bi bi-hourglass-split"></i> Updating...');

        $.ajax({
            url: '{{ route("customer.trading.update-credentials", ":id") }}'.replace(':id', credentialId),
            method: 'PUT',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'X-HTTP-Method-Override': 'PUT'
            },
            data: {
                account_name: $('#edit_account_name').val(),
                connector_id: $('#edit_connector_id').val(),
                api_key: $('#edit_api_key').val(),
                secret_key: $('#edit_secret_key').val(),
            },
            success: function(response) {
                if (response.success) {
                    showAlert('success', response.message || 'Credentials updated successfully');
                    const editModal = bootstrap.Modal.getInstance(document.getElementById('editCredentialModal'));
                    editModal.hide();
                    // Reload page after 1 second
                    setTimeout(function() {
                        location.reload();
                    }, 1000);
                } else {
                    showAlert('error', response.message || 'Failed to update credentials');
                    $btn.prop('disabled', false).html(originalText);
                }
            },
            error: function(xhr) {
                const errorMsg = xhr.responseJSON?.message || 'Failed to update credentials';
                showAlert('error', errorMsg);
                $btn.prop('disabled', false).html(originalText);
            }
        });
    });

    // Delete credential function
    window.deleteCredential = function(credentialId, accountName) {
        if (!confirm('Are you sure you want to delete credentials for account "' + accountName + '"? This action cannot be undone.')) {
            return;
        }

        $.ajax({
            url: '{{ route("customer.trading.delete-credentials", ":id") }}'.replace(':id', credentialId),
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'X-HTTP-Method-Override': 'DELETE'
            },
            success: function(response) {
                if (response.success) {
                    showAlert('success', response.message || 'Credentials deleted successfully');
                    // Reload page after 1 second
                    setTimeout(function() {
                        location.reload();
                    }, 1000);
                } else {
                    showAlert('error', response.message || 'Failed to delete credentials');
                }
            },
            error: function(xhr) {
                const errorMsg = xhr.responseJSON?.message || 'Failed to delete credentials';
                showAlert('error', errorMsg);
            }
        });
    };

    // Toggle status function
    $(document).on('change', '.status-toggle', function() {
        const $toggle = $(this);
        const credentialId = $toggle.data('credential-id');
        const status = $toggle.is(':checked');
        const $badge = $('#status-badge-' + credentialId);

        // Disable toggle while updating
        $toggle.prop('disabled', true);

        $.ajax({
            url: '{{ route("customer.trading.toggle-status", ":id") }}'.replace(':id', credentialId),
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: {
                status: status ? 1 : 0
            },
            success: function(response) {
                if (response.success) {
                    // Update badge
                    if (status) {
                        $badge.removeClass('bg-warning').addClass('bg-success').text('Active');
                    } else {
                        $badge.removeClass('bg-success').addClass('bg-warning').text('Inactive');
                    }
                    showAlert('success', response.message || 'Status updated successfully');
                } else {
                    // Revert toggle
                    $toggle.prop('checked', !status);
                    showAlert('error', response.message || 'Failed to update status');
                }
                $toggle.prop('disabled', false);
            },
            error: function(xhr) {
                // Revert toggle
                $toggle.prop('checked', !status);
                const errorMsg = xhr.responseJSON?.message || 'Failed to update status';
                showAlert('error', errorMsg);
                $toggle.prop('disabled', false);
            }
        });
    });

    // Sync credential function
    window.syncCredential = function(credentialId, accountName, connectorName) {
        if (!confirm('Sync credentials for account "' + accountName + '" with connector "' + connectorName + '"?\n\nThis will verify and create missing account/credentials on the trade server.')) {
            return;
        }

        // Find the sync button and disable it
        const $row = $('tr[data-credential-id="' + credentialId + '"]');
        const $syncBtn = $row.find('button[onclick*="syncCredential"]');
        const originalHtml = $syncBtn.html();
        $syncBtn.prop('disabled', true).html('<i class="bi bi-hourglass-split"></i>');

        $.ajax({
            url: '{{ route("customer.trading.sync-credentials", ":id") }}'.replace(':id', credentialId),
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    // Update status badge to active if credentials were created
                    if (response.results && (response.results.credentials_created || response.results.account_created)) {
                        const $statusToggle = $('#status-toggle-' + credentialId);
                        const $statusBadge = $('#status-badge-' + credentialId);
                        if (!$statusToggle.is(':checked')) {
                            $statusToggle.prop('checked', true);
                            $statusBadge.removeClass('bg-warning').addClass('bg-success').text('Active');
                        }
                    }
                    showAlert('success', response.message || 'Credentials synced successfully');
                } else {
                    showAlert('error', response.message || 'Failed to sync credentials');
                }
                $syncBtn.prop('disabled', false).html(originalHtml);
            },
            error: function(xhr) {
                const errorMsg = xhr.responseJSON?.message || 'Failed to sync credentials';
                showAlert('error', errorMsg);
                $syncBtn.prop('disabled', false).html(originalHtml);
            }
        });
    };

    // Set priority function
    window.setPriority = function(credentialId, priority) {
        $.ajax({
            url: '{{ route("customer.trading.set-priority", ":id") }}'.replace(':id', credentialId),
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: {
                priority: priority
            },
            success: function(response) {
                if (response.success) {
                    // Update badge
                    const $badge = $('#priority-badge-' + credentialId);
                    const priorityText = priority.charAt(0).toUpperCase() + priority.slice(1);
                    let badgeClass = 'bg-secondary';
                    if (priority === 'primary') {
                        badgeClass = 'bg-primary';
                    } else if (priority === 'secondary') {
                        badgeClass = 'bg-warning';
                    }
                    $badge.removeClass('bg-primary bg-warning bg-secondary').addClass(badgeClass).text(priorityText);
                    
                    showAlert('success', response.message || 'Priority updated successfully');
                    
                    // If setting as primary, update other badges
                    if (priority === 'primary') {
                        $('[id^="priority-badge-"]').not('#priority-badge-' + credentialId).each(function() {
                            if ($(this).hasClass('bg-primary')) {
                                $(this).removeClass('bg-primary').addClass('bg-secondary').text('None');
                            }
                        });
                    }
                } else {
                    showAlert('error', response.message || 'Failed to update priority');
                }
            },
            error: function(xhr) {
                const errorMsg = xhr.responseJSON?.message || 'Failed to update priority';
                showAlert('error', errorMsg);
            }
        });
    };
});
</script>
@endpush

