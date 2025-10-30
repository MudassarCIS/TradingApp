<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserActiveBot extends Model
{
    protected $fillable = [
        'user_id',
        'buy_type',
        'buy_plan_details',
    ];

    protected $casts = [
        'buy_plan_details' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
