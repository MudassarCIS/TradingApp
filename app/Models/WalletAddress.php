<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WalletAddress extends Model
{
    protected $fillable = [
        'name',
        'symbol',
        'wallet_address',
        'network',
        'qr_code_image',
        'instructions',
        'is_active',
        'sort_order'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer'
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    public function getQrCodeUrlAttribute()
    {
        if ($this->qr_code_image) {
            // Remove 'app/public/' prefix if it exists (legacy data)
            $path = str_replace('app/public/', '', $this->qr_code_image);
            // Remove leading slash if exists
            $path = ltrim($path, '/');
            return asset('storage/' . $path);
        }
        return null;
    }
}
