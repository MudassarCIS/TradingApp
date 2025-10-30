@extends('layouts.customer-layout')

@section('title', 'Create AI Agent - AI Trade App')
@section('page-title', 'Create AI Agent')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-10">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-robot"></i> Create New AI Agent</h5>
            </div>
            <div class="card-body">
                <!-- Bot Type Selection -->
                <div class="text-center mb-5">
                    <h3 class="mb-4">SELECT BOT TYPE</h3>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="bot-type-card" data-type="rent-bot">
                                <div class="d-flex align-items-center justify-content-between p-4 border rounded-3 h-100" style="cursor: pointer; transition: all 0.3s ease;" onmouseover="this.style.backgroundColor='#f8f9fa'" onmouseout="this.style.backgroundColor='white'">
                                    <div class="d-flex align-items-center">
                                        <div class="me-3">
                                            <i class="bi bi-robot" style="font-size: 3rem; color: #fd7e14;"></i>
                                        </div>
                                        <div>
                                            <h4 class="mb-1">RENT A BOT</h4>
                                            <small class="text-muted">Details</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="bot-type-card" data-type="sharing-nexa">
                                <div class="d-flex align-items-center justify-content-between p-4 border rounded-3 h-100" style="cursor: pointer; transition: all 0.3s ease;" onmouseover="this.style.backgroundColor='#f8f9fa'" onmouseout="this.style.backgroundColor='white'">
                                    <div class="d-flex align-items-center">
                                        <div class="me-3">
                                            <i class="bi bi-share" style="font-size: 3rem; color: #fd7e14;"></i>
                                        </div>
                                        <div>
                                            <h4 class="mb-1">Sharing NEXA</h4>
                                            <small class="text-muted">Details</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Plans/Packages Display -->
                <div id="plans-container" style="display: none;">
                    <h4 class="mb-4">Available Plans</h4>
                    <div id="plans-list" class="row">
                        <!-- Plans will be loaded here via AJAX -->
                    </div>
                </div>

                <!-- Loading Spinner -->
                <div id="loading-spinner" class="text-center" style="display: none;">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">Loading plans...</p>
                </div>

                <!-- Back Button -->
                <div class="text-center mt-4">
                    <a href="{{ route('customer.agents.index') }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Back to Agents
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Plan Selection Modal -->
<div class="modal fade" id="planModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="bi bi-check-circle me-2"></i>Confirm Plan Selection
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <h6><i class="bi bi-info-circle me-2"></i>Please Review Your Selection</h6>
                    <p class="mb-0">By confirming this selection, a new bot plan will be created and an invoice will be generated for payment.</p>
                </div>
                <div id="selected-plan-details"></div>
                <div class="alert alert-warning mt-3">
                    <small>
                        <i class="bi bi-exclamation-triangle me-1"></i>
                        <strong>Note:</strong> You can cancel this selection if you want to choose a different plan.
                    </small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" id="cancel-selection">
                    <i class="bi bi-x-circle me-1"></i>Cancel
                </button>
                <button type="button" class="btn btn-success" id="confirm-plan-selection">
                    <i class="bi bi-check-circle me-1"></i>Confirm & Create Plan
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    let selectedBotType = null;
    let selectedPlan = null;

    // Bot type selection
    $('.bot-type-card').click(function() {
        $('.bot-type-card').removeClass('selected');
        $(this).addClass('selected');
        
        selectedBotType = $(this).data('type');
        loadPlans(selectedBotType);
    });

    // Load plans based on bot type
    function loadPlans(botType) {
        $('#plans-container').show();
        $('#loading-spinner').show();
        $('#plans-list').empty();

        const endpoint = botType === 'rent-bot' ? '/api/rent-bot-packages' : '/api/plans';
        
        $.ajax({
            url: endpoint,
            method: 'GET',
            success: function(response) {
                $('#loading-spinner').hide();
                displayPlans(response.data, botType);
            },
            error: function(xhr) {
                $('#loading-spinner').hide();
                console.error('Error loading plans:', xhr);
                $('#plans-list').html('<div class="col-12"><div class="alert alert-danger">Error loading plans. Please try again.</div></div>');
            }
        });
    }

    // Display plans
    function displayPlans(plans, botType) {
        let html = '';
        
        plans.forEach(function(plan, index) {
            const cardClass = botType === 'rent-bot' ? 'rent-bot-card' : 'sharing-nexa-card';
            const planData = JSON.stringify(plan);
            const planName = plan.name || (botType === 'rent-bot' ? `Package ${index + 1}` : 'Plan');
            
            html += `
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card ${cardClass}" data-plan='${planData.replace(/'/g, "&apos;")}'>
                        <div class="card-body">
                            <h5 class="card-title">${planName}</h5>
                            <div class="plan-details">
                                ${botType === 'rent-bot' ? 
                                    `<p><strong>Bots:</strong> ${plan.allowed_bots}</p>
                                     <p><strong>Trades:</strong> ${plan.allowed_trades}</p>
                                     <p><strong>Validity:</strong> ${plan.validity}</p>
                                     <p><strong>Amount:</strong> $${plan.amount} USDT</p>` :
                                    `<p><strong>Bots Allowed:</strong> ${plan.bots_allowed}</p>
                                     <p><strong>Joining Fee:</strong> $${plan.joining_fee} USDT</p>
                                     <p><strong>Investment:</strong> $${plan.investment_amount} USDT</p>
                                     <p><strong>Trades/Day:</strong> ${plan.trades_per_day}</p>`
                                }
                            </div>
                            <button class="btn btn-primary w-100 select-plan-btn" data-plan='${planData.replace(/'/g, "&apos;")}'>
                                Select
                            </button>
                        </div>
                    </div>
                </div>
            `;
        });
        
        $('#plans-list').html(html);
    }

    // Plan selection
    $(document).on('click', '.select-plan-btn', function() {
        try {
            selectedPlan = JSON.parse($(this).data('plan'));
            showPlanConfirmation();
        } catch (e) {
            console.error('Error parsing plan data:', e);
            alert('Error parsing plan data. Please try again.');
        }
    });

    // Show plan confirmation modal
    function showPlanConfirmation() {
        let detailsHtml = '';
        
        if (selectedBotType === 'rent-bot') {
            detailsHtml = `
                <div class="alert alert-info">
                    <h6>Rent A Bot Package</h6>
                    <p><strong>Bots:</strong> ${selectedPlan.allowed_bots}</p>
                    <p><strong>Trades:</strong> ${selectedPlan.allowed_trades}</p>
                    <p><strong>Validity:</strong> ${selectedPlan.validity}</p>
                    <p><strong>Amount:</strong> $${selectedPlan.amount} USDT</p>
                </div>
            `;
        } else {
            detailsHtml = `
                <div class="alert alert-info">
                    <h6>Sharing NEXA Plan</h6>
                    <p><strong>Bots Allowed:</strong> ${selectedPlan.bots_allowed}</p>
                    <p><strong>Joining Fee:</strong> $${selectedPlan.joining_fee} USDT</p>
                    <p><strong>Investment:</strong> $${selectedPlan.investment_amount} USDT</p>
                    <p><strong>Trades/Day:</strong> ${selectedPlan.trades_per_day}</p>
                </div>
            `;
        }
        
        $('#selected-plan-details').html(detailsHtml);
        $('#planModal').modal('show');
    }

    // Confirm plan selection
    $('#confirm-plan-selection').click(function() {
        if (selectedPlan && selectedBotType) {
            saveBotSelection();
        } else {
            alert('Missing selection data. Please try selecting a plan again.');
        }
    });

    // Cancel selection
    $('#cancel-selection').click(function() {
        // Reset selection
        selectedPlan = null;
        // Don't reset selectedBotType - user should be able to select a different plan of the same type
        
        // Hide plans container
        $('#plans-container').hide();
        $('#plans-list').empty();
        
        // Show message
        showInfoMessage('Selection cancelled. You can choose a different plan.');
    });

    // Handle modal dismissal - don't reset selectedBotType
    $('#planModal').on('hidden.bs.modal', function() {
        // Only reset selectedPlan, keep selectedBotType so user can select another plan
        selectedPlan = null;
    });

    // Save bot selection
    function saveBotSelection() {
        // Show loading state
        $('#confirm-plan-selection').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Saving...');
        
        $.ajax({
            url: '{{ route("customer.agents.store") }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                bot_type: selectedBotType,
                plan_data: selectedPlan
            },
            success: function(response) {
                $('#planModal').modal('hide');
                if (response.success) {
                    // Show success message and redirect to agents page
                    showSuccessMessage('Bot plan created successfully! You will be redirected to your agents page.');
                    setTimeout(function() {
                        window.location.href = '{{ route("customer.agents.index") }}';
                    }, 2000);
                } else {
                    showErrorMessage('Error: ' + (response.message || 'Failed to save bot selection'));
                    resetConfirmButton();
                }
            },
            error: function(xhr) {
                console.error('Error saving bot selection:', xhr);
                let errorMessage = 'Error saving bot selection. Please try again.';
                
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                
                showErrorMessage(errorMessage);
                resetConfirmButton();
            }
        });
    }

    // Reset confirm button
    function resetConfirmButton() {
        $('#confirm-plan-selection').prop('disabled', false).html('Confirm Selection');
    }

    // Show success message
    function showSuccessMessage(message) {
        // Create success alert
        const alertHtml = `
            <div class="alert alert-success alert-dismissible fade show position-fixed" 
                 style="top: 20px; right: 20px; z-index: 9999; min-width: 300px;">
                <i class="bi bi-check-circle me-2"></i>
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        $('body').append(alertHtml);
        
        // Auto remove after 5 seconds
        setTimeout(function() {
            $('.alert-success').fadeOut();
        }, 5000);
    }

    // Show error message
    function showErrorMessage(message) {
        // Create error alert
        const alertHtml = `
            <div class="alert alert-danger alert-dismissible fade show position-fixed" 
                 style="top: 20px; right: 20px; z-index: 9999; min-width: 300px;">
                <i class="bi bi-exclamation-triangle me-2"></i>
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        $('body').append(alertHtml);
        
        // Auto remove after 7 seconds
        setTimeout(function() {
            $('.alert-danger').fadeOut();
        }, 7000);
    }

    // Show info message
    function showInfoMessage(message) {
        // Create info alert
        const alertHtml = `
            <div class="alert alert-info alert-dismissible fade show position-fixed" 
                 style="top: 20px; right: 20px; z-index: 9999; min-width: 300px;">
                <i class="bi bi-info-circle me-2"></i>
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        $('body').append(alertHtml);
        
        // Auto remove after 4 seconds
        setTimeout(function() {
            $('.alert-info').fadeOut();
        }, 4000);
    }
});
</script>

<style>
.bot-type-card.selected .border {
    border-color: #fd7e14 !important;
    border-width: 3px !important;
    background-color: #fff3e0 !important;
}

.rent-bot-card {
    border-left: 4px solid #fd7e14;
}

.sharing-nexa-card {
    border-left: 4px solid #0d6efd;
}

.plan-details p {
    margin-bottom: 0.5rem;
}

.select-plan-btn {
    margin-top: 1rem;
}
</style>
@endpush