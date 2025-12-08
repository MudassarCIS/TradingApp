@extends('layouts.customer-layout')

@section('title', 'Create AI BOT - AI Trade App')
@section('page-title', 'Create AI BOT')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-10">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-robot"></i> Create New AI BOT</h5>
            </div>
            <div class="card-body">
                <!-- Bot Type Selection -->
                <div class="text-center mb-5">
                    <h3 class="mb-4">SELECT BOT TYPE</h3>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="bot-type-card" data-type="rent-bot">
                                <div class="d-flex align-items-center justify-content-between p-4 border rounded-3 bot-card-inner" style="cursor: pointer; transition: all 0.3s ease;" onmouseover="this.style.backgroundColor='#f8f9fa'" onmouseout="this.style.backgroundColor='white'">
                                    <div class="d-flex align-items-center">
                                        <div class="me-3">
                                            <i class="bi bi-robot" style="font-size: 3rem; color: #fd7e14;"></i>
                                        </div>
                                        <div>
                                            <h4 class="mb-1">PEX</h4>
                                            <small class="text-muted">Details</small>
                                        </div>
                                    </div>
                                    <div class="bot-image-container">
                                        <img src="{{ asset('images/pex_images/pex.png') }}" alt="PEX Bot" class="bot-image" style="max-width: 120px; height: auto; object-fit: contain;">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="bot-type-card" data-type="sharing-nexa">
                                <div class="d-flex align-items-center justify-content-between p-4 border rounded-3 bot-card-inner" style="cursor: pointer; transition: all 0.3s ease;" onmouseover="this.style.backgroundColor='#f8f9fa'" onmouseout="this.style.backgroundColor='white'">
                                    <div class="d-flex align-items-center">
                                        <div class="me-3">
                                            <i class="bi bi-share" style="font-size: 3rem; color: #fd7e14;"></i>
                                        </div>
                                        <div>
                                            <h4 class="mb-1">NEXA</h4>
                                            <small class="text-muted">Details</small>
                                        </div>
                                    </div>
                                    <div class="bot-image-container">
                                        <img src="{{ asset('images/pex_images/NEXA.png') }}" alt="NEXA Bot" class="bot-image" style="max-width: 120px; height: auto; object-fit: contain;">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- NEXA Custom Investment Calculator -->
                <div id="nexa-calculator-container" style="display: none;">
                    <div class="card mb-4 border-primary">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0"><i class="bi bi-calculator me-2"></i>NEXA JOINING FEE Calculator</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="custom-investment-amount" class="form-label">
                                            <strong>Enter Trade Amount (USDT)</strong>
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-text">$</span>
                                            <input type="number" 
                                                   class="form-control" 
                                                   id="custom-investment-amount" 
                                                   placeholder="Enter amount (minimum: 100 USDT)"
                                                   min="100"
                                                   step="0.01">
                                        </div>
                                        <small class="text-muted">Minimum Trade: 100 USDT</small>
                                    </div>
                                    <button type="button" class="btn btn-primary w-100" id="calculate-fee-btn">
                                        <i class="bi bi-calculator me-2"></i>Calculate Fee
                                    </button>
                                </div>
                                <div class="col-md-6">
                                    <div class="calculator-results" id="calculator-results" style="display: none;">
                                        <h6 class="text-primary mb-3"><i class="bi bi-info-circle me-2"></i>Calculation Results</h6>
                                        <div class="alert alert-info">
                                            <div class="d-flex justify-content-between mb-2">
                                                <span><strong>Trade Amount:</strong></span>
                                                <span id="result-investment">$0.00</span>
                                            </div>
                                            <div class="d-flex justify-content-between mb-2">
                                                <span><strong>Joining Fee:</strong></span>
                                                <span id="result-fee" class="text-primary">$0.00</span>
                                            </div>
                                            <div class="d-flex justify-content-between mb-2">
                                                <span><strong>Fee Percentage:</strong></span>
                                                <span id="result-percentage">0%</span>
                                            </div>
                                            <hr>
                                            <div class="d-flex justify-content-between">
                                                <span><strong>Total Amount:</strong></span>
                                                <span id="result-total" class="text-success fw-bold">$0.00</span>
                                            </div>
                                            <div id="matched-plan-info" class="mt-2 text-muted small"></div>
                                        </div>
                                        <button type="button" class="btn btn-success w-100" id="select-custom-plan-btn">
                                            <i class="bi bi-check-circle me-2"></i>Select Custom Plan
                                        </button>
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
                    <a href="{{ route('customer.bots.index') }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Back to BOTs
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
    let loadedPlans = []; // Store plans in memory

    // Bot type selection
    $('.bot-type-card').click(function() {
        $('.bot-type-card').removeClass('selected');
        $(this).addClass('selected');
        
        selectedBotType = $(this).data('type');
        
        // Show/hide calculator based on bot type
        if (selectedBotType === 'sharing-nexa') {
            $('#nexa-calculator-container').show();
        } else {
            $('#nexa-calculator-container').hide();
            $('#calculator-results').hide();
        }
        
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
                loadedPlans = response.data; // Store plans in memory
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
            const planName = plan.name || (botType === 'rent-bot' ? `Package ${index + 1}` : 'Plan');
            
            html += `
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card ${cardClass}">
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
                                     <p><strong>Trade Amount:</strong> $${plan.investment_amount} USDT</p>
                                     <p><strong>Trades/Day:</strong> ${plan.trades_per_day}</p>`
                                }
                            </div>
                            <button class="btn btn-primary w-100 select-plan-btn" data-plan-index="${index}">
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
            const planIndex = parseInt($(this).data('plan-index'));
            if (planIndex >= 0 && planIndex < loadedPlans.length) {
                selectedPlan = loadedPlans[planIndex];
                showPlanConfirmation();
            } else {
                throw new Error('Invalid plan index');
            }
        } catch (e) {
            console.error('Error selecting plan:', e);
            alert('Error selecting plan. Please try again.');
        }
    });

    // Show plan confirmation modal
    function showPlanConfirmation() {
        let detailsHtml = '';
        
        if (selectedBotType === 'rent-bot') {
            detailsHtml = `
                <div class="alert alert-info">
                    <h6>PEX Package</h6>
                    <p><strong>Bots:</strong> ${selectedPlan.allowed_bots}</p>
                    <p><strong>Trades:</strong> ${selectedPlan.allowed_trades}</p>
                    <p><strong>Validity:</strong> ${selectedPlan.validity}</p>
                    <p><strong>Amount:</strong> $${selectedPlan.amount} USDT</p>
                </div>
            `;
        } else {
            const isCustom = selectedPlan.is_custom || false;
            // Use actual plan name if available, otherwise show NEXA Plan
            const planName = selectedPlan.name || 'NEXA Plan';
            
            detailsHtml = `
                <div class="alert alert-info">
                    <h6>${planName}</h6>
                    ${isCustom ? '<p class="text-info"><i class="bi bi-info-circle me-1"></i><strong>Custom Investment Amount</strong> (Plan: ' + planName + ')</p>' : ''}
                    <p><strong>Investment Amount:</strong> $${parseFloat(selectedPlan.investment_amount).toFixed(2)} USDT</p>
                    <p><strong>Joining Fee:</strong> $${parseFloat(selectedPlan.joining_fee).toFixed(2)} USDT</p>
                    <p><strong>Total Amount:</strong> $${(parseFloat(selectedPlan.investment_amount) + parseFloat(selectedPlan.joining_fee)).toFixed(2)} USDT</p>
                    ${selectedPlan.bots_allowed ? `<p><strong>Bots Allowed:</strong> ${selectedPlan.bots_allowed}</p>` : ''}
                    ${selectedPlan.trades_per_day ? `<p><strong>Trades/Day:</strong> ${selectedPlan.trades_per_day}</p>` : ''}
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
            url: '{{ route("customer.bots.store") }}',
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
                        window.location.href = '{{ route("customer.bots.index") }}';
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
        $('#confirm-plan-selection').prop('disabled', false).html('<i class="bi bi-check-circle me-1"></i>Confirm & Create Plan');
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

    // Store matched plan data globally
    let matchedPlanData = null;

    // Calculate NEXA custom investment fee
    $('#calculate-fee-btn').click(function() {
        const investmentAmount = parseFloat($('#custom-investment-amount').val());
        
        if (!investmentAmount || investmentAmount < 100) {
            showErrorMessage('Please enter a valid investment amount (minimum: 100 USDT)');
            return;
        }
        
        // Show loading state
        $(this).prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Calculating...');
        
        $.ajax({
            url: '/api/calculate-nexa-fee',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                investment_amount: investmentAmount
            },
            success: function(response) {
                if (response.success) {
                    // Store matched plan data for later use
                    matchedPlanData = response.matched_plan_data;
                    
                    // Display results
                    $('#result-investment').text('$' + parseFloat(response.investment_amount).toFixed(2));
                    $('#result-fee').text('$' + parseFloat(response.joining_fee).toFixed(2));
                    $('#result-percentage').text(parseFloat(response.fee_percentage).toFixed(2) + '%');
                    $('#result-total').text('$' + parseFloat(response.total_amount).toFixed(2));
                    
                    // Show matched plan info if available
                    if (response.matched_plan) {
                        $('#matched-plan-info').html('<i class="bi bi-info-circle me-1"></i>Fee calculated based on: <strong>' + response.matched_plan + '</strong> plan tier');
                    } else {
                        $('#matched-plan-info').html('<i class="bi bi-info-circle me-1"></i>Fee calculated based on standard percentage');
                    }
                    
                    $('#calculator-results').show();
                } else {
                    showErrorMessage('Error calculating fee. Please try again.');
                }
                
                $('#calculate-fee-btn').prop('disabled', false).html('<i class="bi bi-calculator me-2"></i>Calculate Fee');
            },
            error: function(xhr) {
                console.error('Error calculating fee:', xhr);
                let errorMessage = 'Error calculating fee. Please try again.';
                
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                    const errors = xhr.responseJSON.errors;
                    errorMessage = Object.values(errors).flat().join(', ');
                }
                
                showErrorMessage(errorMessage);
                $('#calculate-fee-btn').prop('disabled', false).html('<i class="bi bi-calculator me-2"></i>Calculate Fee');
            }
        });
    });

    // Select custom plan
    $('#select-custom-plan-btn').click(function() {
        const investmentAmount = parseFloat($('#custom-investment-amount').val());
        const joiningFee = parseFloat($('#result-fee').text().replace('$', ''));
        
        if (!investmentAmount || !joiningFee) {
            showErrorMessage('Please calculate the fee first.');
            return;
        }
        
        // Create a custom plan object using matched plan data if available
        // Use the matched plan's name and details instead of "Custom Investment"
        if (matchedPlanData) {
            selectedPlan = {
                id: matchedPlanData.id,
                name: matchedPlanData.name, // Use actual plan name from plans table
                investment_amount: investmentAmount, // User's custom investment amount
                joining_fee: joiningFee, // Calculated fee
                bots_allowed: matchedPlanData.bots_allowed,
                trades_per_day: matchedPlanData.trades_per_day,
                is_custom: true // Still mark as custom since amount may differ
            };
        } else {
            // Fallback if matched plan data is not available
        selectedPlan = {
            investment_amount: investmentAmount,
            joining_fee: joiningFee,
                bots_allowed: 1,
                trades_per_day: 5,
            is_custom: true
        };
        }
        
        showPlanConfirmation();
    });

    // Allow Enter key to trigger calculation
    $('#custom-investment-amount').on('keypress', function(e) {
        if (e.which === 13) {
            e.preventDefault();
            $('#calculate-fee-btn').click();
        }
    });
});
</script>

<style>
.bot-type-card {
    height: 100%;
    display: flex;
}

.bot-card-inner {
    width: 100%;
    min-height: 180px;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

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

.bot-image-container {
    display: flex;
    align-items: center;
    justify-content: center;
    padding-left: 1rem;
    flex-shrink: 0;
}

.bot-image {
    transition: transform 0.3s ease;
    max-height: 140px;
    width: auto;
}

.bot-type-card:hover .bot-image {
    transform: scale(1.05);
}

@media (max-width: 768px) {
    .bot-card-inner {
        min-height: 150px;
        flex-direction: column;
        text-align: center;
    }
    
    .bot-image-container {
        padding-left: 0;
        padding-top: 1rem;
    }
    
    .bot-image {
        max-width: 80px !important;
        max-height: 100px;
    }
}

/* Calculator Styles */
#nexa-calculator-container {
    animation: fadeIn 0.3s ease-in;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.calculator-results {
    animation: slideIn 0.3s ease-in;
}

@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateX(-10px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

#calculator-results .alert {
    border-left: 4px solid #0d6efd;
}

#result-total {
    font-size: 1.2rem;
}
</style>
@endpush