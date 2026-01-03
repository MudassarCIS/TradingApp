<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TradeCredential extends Model
{
    protected $fillable = [
        'user_id',
        'account_name',
        'connector_id',
        'api_key',
        'secret_key',
        'active_credentials',
    ];

    protected $casts = [
        'active_credentials' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function connector(): BelongsTo
    {
        return $this->belongsTo(Connector::class);
    }
}
