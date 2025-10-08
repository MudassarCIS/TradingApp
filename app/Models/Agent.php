<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Agent extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'description',
        'status',
        'strategy',
        'trading_rules',
        'initial_balance',
        'current_balance',
        'total_profit',
        'total_loss',
        'win_rate',
        'total_trades',
        'winning_trades',
        'losing_trades',
        'max_drawdown',
        'risk_per_trade',
        'auto_trading',
        'last_trade_at',
    ];

    protected $casts = [
        'trading_rules' => 'array',
        'initial_balance' => 'decimal:8',
        'current_balance' => 'decimal:8',
        'total_profit' => 'decimal:8',
        'total_loss' => 'decimal:8',
        'win_rate' => 'decimal:2',
        'max_drawdown' => 'decimal:2',
        'risk_per_trade' => 'decimal:2',
        'auto_trading' => 'boolean',
        'last_trade_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function trades(): HasMany
    {
        return $this->hasMany(Trade::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isPaused(): bool
    {
        return $this->status === 'paused';
    }

    public function isInactive(): bool
    {
        return $this->status === 'inactive';
    }

    public function getNetProfitAttribute(): float
    {
        return $this->total_profit - $this->total_loss;
    }

    public function getProfitPercentageAttribute(): float
    {
        if ($this->initial_balance > 0) {
            return ($this->net_profit / $this->initial_balance) * 100;
        }
        return 0;
    }

    public function updateStats(): void
    {
        $this->total_trades = $this->trades()->count();
        $this->winning_trades = $this->trades()->where('profit_loss', '>', 0)->count();
        $this->losing_trades = $this->trades()->where('profit_loss', '<', 0)->count();
        
        if ($this->total_trades > 0) {
            $this->win_rate = ($this->winning_trades / $this->total_trades) * 100;
        }
        
        $this->save();
    }
}
