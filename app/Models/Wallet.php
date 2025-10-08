<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Wallet extends Model
{
    protected $fillable = [
        'user_id',
        'currency',
        'balance',
        'locked_balance',
        'total_deposited',
        'total_withdrawn',
        'total_profit',
        'total_loss',
    ];

    protected $casts = [
        'balance' => 'decimal:8',
        'locked_balance' => 'decimal:8',
        'total_deposited' => 'decimal:8',
        'total_withdrawn' => 'decimal:8',
        'total_profit' => 'decimal:8',
        'total_loss' => 'decimal:8',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getAvailableBalanceAttribute(): float
    {
        return $this->balance - $this->locked_balance;
    }

    public function getTotalEarningsAttribute(): float
    {
        return $this->total_profit - $this->total_loss;
    }

    public function canWithdraw(float $amount): bool
    {
        return $this->available_balance >= $amount;
    }

    public function lockBalance(float $amount): bool
    {
        if ($this->canWithdraw($amount)) {
            $this->locked_balance += $amount;
            return $this->save();
        }
        return false;
    }

    public function unlockBalance(float $amount): bool
    {
        if ($this->locked_balance >= $amount) {
            $this->locked_balance -= $amount;
            return $this->save();
        }
        return false;
    }

    public function addBalance(float $amount): bool
    {
        $this->balance += $amount;
        $this->total_deposited += $amount;
        return $this->save();
    }

    public function subtractBalance(float $amount): bool
    {
        if ($this->canWithdraw($amount)) {
            $this->balance -= $amount;
            $this->total_withdrawn += $amount;
            return $this->save();
        }
        return false;
    }
}
