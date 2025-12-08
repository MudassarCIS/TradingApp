<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RentBotPackage extends Model
{
    use HasFactory;

    protected $table = 'rent_bot_packages';

    protected $fillable = [
        'package_name',
        'allowed_bots',
        'allowed_trades',
        'amount',
        'validity',
        'status',
    ];

    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('amount', 'asc');
    }
}


