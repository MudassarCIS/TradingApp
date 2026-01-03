<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Connector extends Model
{
    protected $fillable = [
        'connector_name',
        'connector_code',
        'is_active',
        'synced_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'synced_at' => 'datetime',
    ];

    public function tradeCredentials(): HasMany
    {
        return $this->hasMany(TradeCredential::class);
    }
}
