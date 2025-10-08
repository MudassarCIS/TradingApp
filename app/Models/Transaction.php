<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    protected $fillable = [
        'user_id',
        'transaction_id',
        'type',
        'status',
        'currency',
        'amount',
        'fee',
        'net_amount',
        'from_address',
        'to_address',
        'tx_hash',
        'confirmations',
        'notes',
        'metadata',
        'processed_at',
    ];

    protected $casts = [
        'amount' => 'decimal:8',
        'fee' => 'decimal:8',
        'net_amount' => 'decimal:8',
        'metadata' => 'array',
        'processed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    public function markAsCompleted(): bool
    {
        $this->status = 'completed';
        $this->processed_at = now();
        return $this->save();
    }

    public function markAsFailed(): bool
    {
        $this->status = 'failed';
        $this->processed_at = now();
        return $this->save();
    }

    public function generateTransactionId(): string
    {
        return 'TXN' . time() . rand(1000, 9999);
    }
}
