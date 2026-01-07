@extends('layouts.customer-layout')

@section('title', 'Trading - AI Trade App')
@section('page-title', 'Trading')

@section('content')
@if($activePackages->count() > 0)
<div class="row mb-4">
    <div class="col-12">
        <div class="card border-primary">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="bi bi-box-seam"></i> Active Packages</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    @foreach($activePackages as $package)
                    <div class="col-md-6 col-lg-4">
                        <div class="card h-100">
                            <div class="card-body">
                                <h6 class="card-title text-primary">
                                    <i class="bi bi-robot"></i> {{ $package['title'] }}@if(isset($package['plan_name']) && $package['plan_name'])<span class="text-muted"> ({{ $package['plan_name'] }})</span>@endif
                                </h6>
                                <div class="mb-2">
                                    <span class="badge bg-success">Active</span>
                                </div>
                                <div class="mt-3">
                                    <strong>Available Bots:</strong>
                                    <span class="text-primary fs-4 ms-2">{{ $package['available_bots'] }}</span>
                                </div>
                                @if($package['type'] === 'PEX' && isset($package['plan_details']['allowed_trades']))
                                <div class="mt-2">
                                    <small class="text-muted">Allowed Trades: {{ $package['plan_details']['allowed_trades'] }}</small>
                                </div>
                                @elseif($package['type'] === 'NEXA' && isset($package['plan_details']['trades_per_day']))
                                <div class="mt-2">
                                    <small class="text-muted">Trades/Day: {{ $package['plan_details']['trades_per_day'] }}</small>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endif

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-graph-up"></i> Trading History</h5>
                <div class="d-flex gap-2">
                    <a href="{{ route('customer.trading.save-credentials') }}" class="btn btn-success btn-sm">
                        <i class="bi bi-key"></i> Save Credentials
                    </a>
                    @if($hasNexaPackage)
                    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#startTradeModal">
                        <i class="bi bi-play-circle"></i> Start Trade
                    </button>
                    @endif
                </div>
            </div>
            <div class="card-body">
                @if($trades->count() > 0)
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Symbol</th>
                                <th>Side</th>
                                <th>Amount</th>
                                <th>Price</th>
                                <th>P&L</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($trades as $trade)
                            <tr>
                                <td><strong>{{ $trade->symbol }}</strong></td>
                                <td>
                                    <span class="badge {{ $trade->side === 'buy' ? 'bg-success' : 'bg-danger' }}">
                                        {{ strtoupper($trade->side) }}
                                    </span>
                                </td>
                                <td>${{ number_format($trade->quantity, 4) }}</td>
                                <td>${{ number_format($trade->price, 2) }}</td>
                                <td class="{{ $trade->profit_loss >= 0 ? 'text-success' : 'text-danger' }}">
                                    ${{ number_format($trade->profit_loss, 2) }}
                                    @if($trade->profit_loss_percentage)
                                    <small>({{ number_format($trade->profit_loss_percentage, 2) }}%)</small>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $statusClass = match($trade->status) {
                                            'filled' => 'success',
                                            'pending', 'partially_filled' => 'warning',
                                            'cancelled', 'rejected' => 'danger',
                                            default => 'secondary'
                                        };
                                    @endphp
                                    <span class="badge bg-{{ $statusClass }}">
                                        {{ ucfirst(str_replace('_', ' ', $trade->status)) }}
                                    </span>
                                </td>
                                <td>{{ $trade->created_at->format('M d, Y H:i') }}</td>
                                <td>
                                    @if(in_array($trade->status, ['pending', 'partially_filled']))
                                    <button type="button" class="btn btn-danger btn-sm close-trade-btn" data-trade-id="{{ $trade->trade_id ?: $trade->exchange_order_id ?: $trade->id }}">
                                        <i class="bi bi-x-circle"></i> Close Trade
                                    </button>
                                    @else
                                    <span class="text-muted">-</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <div class="d-flex justify-content-center mt-3">
                    {{ $trades->links() }}
                </div>
                @else
                <div class="text-center py-5">
                    <i class="bi bi-graph-up display-1 text-muted"></i>
                    <h4 class="text-muted mt-3">No trades yet</h4>
                    <p class="text-muted">Start trading by creating an AI agent or manually starting a trade.</p>
                    @if($hasNexaPackage)
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#startTradeModal">
                        <i class="bi bi-play-circle"></i> Start Trade
                    </button>
                    @endif
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Start Trade Modal -->
<div class="modal fade" id="startTradeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Start New Trade</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="startTradeForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="symbol" class="form-label">Symbol <span class="text-danger">*</span></label>
                        <select class="form-select" id="symbol" name="symbol" required>
                            <option value="BTCUSDT">BTC/USDT</option>
                            <option value="ETHUSDT">ETH/USDT</option>
                            <option value="BNBUSDT">BNB/USDT</option>
                            <option value="ADAUSDT">ADA/USDT</option>
                            <option value="SOLUSDT">SOL/USDT</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="side" class="form-label">Side <span class="text-danger">*</span></label>
                        <select class="form-select" id="side" name="side" required>
                            <option value="buy">Buy</option>
                            <option value="sell">Sell</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="quantity" class="form-label">Quantity <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="quantity" name="quantity" step="0.0001" min="0.0001" required>
                        <small class="text-muted">Minimum: 0.0001</small>
                    </div>
                    <div class="mb-3">
                        <label for="type" class="form-label">Order Type</label>
                        <select class="form-select" id="type" name="type">
                            <option value="MARKET">Market</option>
                            <option value="LIMIT">Limit</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-play-circle"></i> Start Trade
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Start Trade Form Submission
    $('#startTradeForm').on('submit', function(e) {
        e.preventDefault();
        
        const formData = {
            symbol: $('#symbol').val(),
            side: $('#side').val(),
            quantity: parseFloat($('#quantity').val()),
            type: $('#type').val()
        };
        
        $.ajax({
            url: '{{ route("customer.trading.start") }}',
            method: 'POST',
            data: formData,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    $('#startTradeModal').modal('hide');
                    location.reload();
                } else {
                    alert('Error: ' + (response.message || 'Failed to start trade'));
                }
            },
            error: function(xhr) {
                const error = xhr.responseJSON?.message || 'An error occurred';
                alert('Error: ' + error);
            }
        });
    });
    
    // Close Trade Button
    $('.close-trade-btn').on('click', function() {
        const tradeId = $(this).data('trade-id');
        const btn = $(this);
        
        if (!confirm('Are you sure you want to close this trade?')) {
            return;
        }
        
        btn.prop('disabled', true).html('<i class="bi bi-hourglass-split"></i> Closing...');
        
        $.ajax({
            url: '/customer/trading/close/' + tradeId,
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert('Error: ' + (response.message || 'Failed to close trade'));
                    btn.prop('disabled', false).html('<i class="bi bi-x-circle"></i> Close Trade');
                }
            },
            error: function(xhr) {
                const error = xhr.responseJSON?.message || 'An error occurred';
                alert('Error: ' + error);
                btn.prop('disabled', false).html('<i class="bi bi-x-circle"></i> Close Trade');
            }
        });
    });
});
</script>
@endpush
