<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    protected $fillable = [
        'name',
        'investment_amount',
        'joining_fee',
        'bots_allowed',
        'trades_per_day',
        'direct_bonus',
        'referral_level_1',
        'referral_level_2',
        'referral_level_3',
        'is_active',
        'sort_order',
        'description'
    ];

    protected $casts = [
        'investment_amount' => 'decimal:2',
        'joining_fee' => 'decimal:2',
        'direct_bonus' => 'decimal:2',
        'referral_level_1' => 'decimal:2',
        'referral_level_2' => 'decimal:2',
        'referral_level_3' => 'decimal:2',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
        'bots_allowed' => 'integer',
        'trades_per_day' => 'integer'
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('investment_amount');
    }
}
