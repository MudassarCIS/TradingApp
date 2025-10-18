<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomersWallet extends Model
{
    protected $table = 'customers_wallets';

    protected $fillable = [
        'user_id',
        'amount',
        'currency',
        'payment_type',
        'transaction_type',
        'related_id',
    ];

    protected $casts = [
        'amount' => 'decimal:8',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isDebit(): bool
    {
        return $this->transaction_type === 'debit';
    }

    public function isCredit(): bool
    {
        return $this->transaction_type === 'credit';
    }

    public function isBonus(): bool
    {
        return $this->payment_type === 'bonus';
    }

    public function isInvestment(): bool
    {
        return $this->payment_type === 'investment';
    }

    public function isTradeProfit(): bool
    {
        return $this->payment_type === 'trade_profit';
    }

    public function isTradeLoss(): bool
    {
        return $this->payment_type === 'trade_loss';
    }

    public function isWithdraw(): bool
    {
        return $this->payment_type === 'withdraw';
    }
}
