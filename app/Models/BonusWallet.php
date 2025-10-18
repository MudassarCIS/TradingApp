<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BonusWallet extends Model
{
    protected $fillable = [
        'deposit_id',
        'investment_amount',
        'user_id',
        'parent_id',
        'parent_level',
        'bonus_amount',
        'currency',
        'package_id',
    ];

    protected $casts = [
        'investment_amount' => 'decimal:8',
        'bonus_amount' => 'decimal:8',
        'parent_level' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'parent_id');
    }

    public function deposit(): BelongsTo
    {
        return $this->belongsTo(Deposit::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class, 'package_id');
    }
}
