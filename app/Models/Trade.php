<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Trade extends Model
{
    protected $fillable = [
        'user_id',
        'agent_id',
        'trade_id',
        'symbol',
        'side',
        'type',
        'quantity',
        'price',
        'stop_price',
        'executed_quantity',
        'average_price',
        'commission',
        'status',
        'time_in_force',
        'profit_loss',
        'profit_loss_percentage',
        'exchange',
        'exchange_order_id',
        'opened_at',
        'closed_at',
        'notes',
        'metadata',
    ];

    protected $casts = [
        'quantity' => 'decimal:8',
        'price' => 'decimal:8',
        'stop_price' => 'decimal:8',
        'executed_quantity' => 'decimal:8',
        'average_price' => 'decimal:8',
        'commission' => 'decimal:8',
        'profit_loss' => 'decimal:8',
        'profit_loss_percentage' => 'decimal:4',
        'metadata' => 'array',
        'opened_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class);
    }

    public function isOpen(): bool
    {
        return in_array($this->status, ['pending', 'partially_filled']);
    }

    public function isClosed(): bool
    {
        return in_array($this->status, ['filled', 'cancelled', 'rejected']);
    }

    public function isProfitable(): bool
    {
        return $this->profit_loss > 0;
    }

    public function isLosing(): bool
    {
        return $this->profit_loss < 0;
    }

    public function calculateProfitLoss(float $currentPrice): float
    {
        if ($this->side === 'buy') {
            return ($currentPrice - $this->average_price) * $this->executed_quantity;
        } else {
            return ($this->average_price - $currentPrice) * $this->executed_quantity;
        }
    }

    public function generateTradeId(): string
    {
        return 'TRD' . time() . rand(1000, 9999);
    }
}
