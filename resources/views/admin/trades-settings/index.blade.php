@extends('layouts.admin-layout')

@section('title', 'Trades Settings - Connectors')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="bi bi-gear"></i> Trades Settings - Connectors Management
                    </h3>
                </div>
                <div class="card-body">
                    <div id="alertContainer"></div>

                    <!-- Sync Connectors Section -->
                    <div class="card mb-4">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0"><i class="bi bi-arrow-repeat"></i> Sync Connectors from API</h5>
                        </div>
                        <div class="card-body">
                            <p class="text-muted">
                                Click the button below to fetch the latest connectors list from the Trade Server API and update the database.
                                All existing connectors will be removed and replaced with the latest data.
                            </p>
                            @if($lastSyncTime)
                                <p class="mb-3">
                                    <strong>Last Sync:</strong> 
                                    <span class="badge bg-info">{{ $lastSyncTime->format('M d, Y H:i:s') }}</span>
                                </p>
                            @else
                                <p class="mb-3">
                                    <strong>Last Sync:</strong> 
                                    <span class="badge bg-warning">Never</span>
                                </p>
                            @endif
                            <button type="button" class="btn btn-primary" id="syncConnectorsBtn">
                                <i class="bi bi-arrow-repeat"></i> Sync Connectors
                            </button>
                        </div>
                    </div>

                    <!-- Connectors List -->
                    <div class="card">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0"><i class="bi bi-list-ul"></i> Connectors List</h5>
                        </div>
                        <div class="card-body">
                            @if($connectors->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Connector Name</th>
                                                <th>Connector Code</th>
                                                <th>Status</th>
                                                <th>Last Synced</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($connectors as $connector)
                                                <tr>
                                                    <td>{{ $connector->id }}</td>
                                                    <td><strong>{{ $connector->connector_name }}</strong></td>
                                                    <td><code>{{ $connector->connector_code }}</code></td>
                                                    <td>
                                                        <span class="badge bg-{{ $connector->is_active ? 'success' : 'danger' }}">
                                                            {{ $connector->is_active ? 'Active' : 'Inactive' }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        @if($connector->synced_at)
                                                            {{ $connector->synced_at->format('M d, Y H:i:s') }}
                                                        @else
                                                            <span class="text-muted">Never</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <div class="form-check form-switch">
                                                            <input class="form-check-input connector-toggle" 
                                                                   type="checkbox" 
                                                                   data-connector-id="{{ $connector->id }}"
                                                                   {{ $connector->is_active ? 'checked' : '' }}>
                                                            <label class="form-check-label">
                                                                {{ $connector->is_active ? 'Enabled' : 'Disabled' }}
                                                            </label>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="alert alert-info">
                                    <i class="bi bi-info-circle"></i> No connectors found. Please sync connectors from the API first.
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Sync connectors
    $('#syncConnectorsBtn').on('click', function() {
        const $btn = $(this);
        const originalHtml = $btn.html();
        $btn.prop('disabled', true).html('<i class="bi bi-hourglass-split"></i> Syncing...');

        $.ajax({
            url: '{{ route("admin.trades-settings.sync-connectors") }}',
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    showAlert('success', response.message || 'Connectors synced successfully! Page will reload...');
                    setTimeout(function() {
                        location.reload();
                    }, 2000);
                } else {
                    showAlert('danger', response.message || 'Failed to sync connectors');
                    $btn.prop('disabled', false).html(originalHtml);
                }
            },
            error: function(xhr) {
                const errorMsg = xhr.responseJSON?.message || 'Failed to sync connectors';
                showAlert('danger', errorMsg);
                $btn.prop('disabled', false).html(originalHtml);
            }
        });
    });

    // Toggle connector status
    $('.connector-toggle').on('change', function() {
        const $toggle = $(this);
        const connectorId = $toggle.data('connector-id');
        const isActive = $toggle.is(':checked');
        const $label = $toggle.next('label');

        // Disable toggle while updating
        $toggle.prop('disabled', true);

        $.ajax({
            url: '{{ route("admin.trades-settings.connector.toggle", ":id") }}'.replace(':id', connectorId),
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: {
                is_active: isActive ? 1 : 0
            },
            success: function(response) {
                if (response.success) {
                    $label.text(isActive ? 'Enabled' : 'Disabled');
                    showAlert('success', 'Connector status updated successfully');
                } else {
                    // Revert toggle
                    $toggle.prop('checked', !isActive);
                    showAlert('danger', response.message || 'Failed to update connector status');
                }
                $toggle.prop('disabled', false);
            },
            error: function(xhr) {
                // Revert toggle
                $toggle.prop('checked', !isActive);
                const errorMsg = xhr.responseJSON?.message || 'Failed to update connector status';
                showAlert('danger', errorMsg);
                $toggle.prop('disabled', false);
            }
        });
    });

    function showAlert(type, message) {
        const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
        const alertHtml = '<div class="alert ' + alertClass + ' alert-dismissible fade show" role="alert">' +
            message +
            '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>' +
            '</div>';
        
        $('#alertContainer').html(alertHtml);
        
        setTimeout(function() {
            $('#alertContainer .alert').fadeOut(function() {
                $(this).remove();
            });
        }, 5000);
    }
});
</script>
@endpush

