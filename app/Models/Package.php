<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Package extends Model
{
    protected $fillable = [
        'name',
        'description',
        'price',
        'currency',
        'min_investment',
        'max_investment',
        'daily_return_rate',
        'duration_days',
        'profit_share',
        'is_active',
        'features',
    ];

    protected $casts = [
        'price' => 'decimal:8',
        'min_investment' => 'decimal:8',
        'max_investment' => 'decimal:8',
        'daily_return_rate' => 'decimal:4',
        'profit_share' => 'decimal:2',
        'is_active' => 'boolean',
        'features' => 'array',
    ];

    public function isActive(): bool
    {
        return $this->is_active;
    }

    public function getExpectedReturnAttribute(): float
    {
        return $this->min_investment * ($this->daily_return_rate / 100) * $this->duration_days;
    }

    public function getAdminProfitAttribute(): float
    {
        return $this->expected_return * ($this->profit_share / 100);
    }

    public function getCustomerProfitAttribute(): float
    {
        return $this->expected_return * ((100 - $this->profit_share) / 100);
    }
}
