<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserPlanHistory extends Model
{
    protected $table = 'user_plan_history';

    protected $fillable = [
        'user_id',
        'plan_id',
        'plan_name',
        'joining_fee',
        'investment_amount',
        'notes',
    ];

    protected $casts = [
        'joining_fee' => 'decimal:2',
        'investment_amount' => 'decimal:8',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }
}

