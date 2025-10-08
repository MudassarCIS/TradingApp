<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Referral extends Model
{
    protected $fillable = [
        'referrer_id',
        'referred_id',
        'commission_rate',
        'total_commission',
        'pending_commission',
        'status',
        'joined_at',
    ];

    protected $casts = [
        'commission_rate' => 'decimal:2',
        'total_commission' => 'decimal:8',
        'pending_commission' => 'decimal:8',
        'joined_at' => 'datetime',
    ];

    public function referrer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referrer_id');
    }

    public function referred(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referred_id');
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function addCommission(float $amount): bool
    {
        $this->pending_commission += $amount;
        return $this->save();
    }

    public function processCommission(): bool
    {
        $this->total_commission += $this->pending_commission;
        $this->pending_commission = 0;
        return $this->save();
    }
}
