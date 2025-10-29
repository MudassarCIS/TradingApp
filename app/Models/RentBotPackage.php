<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RentBotPackage extends Model
{
    use HasFactory;

    protected $table = 'rent_bot_packages';

    protected $fillable = [
        'allowed_bots',
        'allowed_trades',
        'amount',
        'validity',
        'status',
    ];
}


