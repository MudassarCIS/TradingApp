<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Withdrawal extends Model
{
    protected $fillable = [
        'user_id',
        'transaction_id',
        'withdrawal_id',
        'amount',
        'fee',
        'net_amount',
        'to_address',
        'status',
        'tx_hash',
        'notes',
        'processed_at',
    ];

    protected $casts = [
        'amount' => 'decimal:8',
        'fee' => 'decimal:8',
        'net_amount' => 'decimal:8',
        'processed_at' => 'datetime',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        // Generate withdrawal_id after withdrawal is created
        // Format: WTD-0001, WTD-0010, WTD-0100, WTD-1000, etc.
        static::created(function ($withdrawal) {
            if (empty($withdrawal->withdrawal_id)) {
                // Generate withdrawal_id using the withdrawal's database ID
                $withdrawalId = 'WTD-' . str_pad($withdrawal->id, 4, '0', STR_PAD_LEFT);
                // Use updateQuietly to prevent triggering events again
                $withdrawal->updateQuietly(['withdrawal_id' => $withdrawalId]);
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isProcessing(): bool
    {
        return $this->status === 'processing';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }
}
