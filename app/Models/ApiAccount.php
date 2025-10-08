<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApiAccount extends Model
{
    protected $fillable = [
        'user_id',
        'exchange',
        'api_key',
        'secret_key',
        'passphrase',
        'is_active',
        'is_verified',
        'permissions',
        'last_used_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_verified' => 'boolean',
        'permissions' => 'array',
        'last_used_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isActive(): bool
    {
        return $this->is_active;
    }

    public function isVerified(): bool
    {
        return $this->is_verified;
    }

    public function canTrade(): bool
    {
        return $this->is_active && $this->is_verified && 
               in_array('trading', $this->permissions ?? []);
    }

    public function canRead(): bool
    {
        return $this->is_active && $this->is_verified && 
               in_array('reading', $this->permissions ?? []);
    }

    public function updateLastUsed(): bool
    {
        $this->last_used_at = now();
        return $this->save();
    }
}
