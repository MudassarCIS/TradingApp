<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Setting extends Model
{
    protected $fillable = [
        'company_name',
        'logo_path',
        'timezone',
    ];

    /**
     * Get the logo URL
     * Logo is stored in public/images/logo directory
     */
    public function getLogoUrlAttribute()
    {
        if ($this->logo_path) {
            // Remove any leading slashes or 'public/' prefix
            $path = ltrim($this->logo_path, '/');
            $path = str_replace('public/', '', $path);
            
            // Use asset() to generate URL pointing to public directory
            return asset($path);
        }
        return null;
    }

    /**
     * Get the first setting record (singleton pattern)
     */
    public static function get()
    {
        return static::firstOrCreate([], [
            'company_name' => 'TSG Trades - THE SMART GROWTH',
            'logo_path' => null,
            'timezone' => 'Asia/Dubai',
        ]);
    }
}
