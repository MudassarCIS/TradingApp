<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Setting extends Model
{
    protected $fillable = [
        'company_name',
        'logo_path',
    ];

    /**
     * Get the logo URL
     */
    public function getLogoUrlAttribute()
    {
        if ($this->logo_path) {
            return Storage::disk('public')->url($this->logo_path);
        }
        return null;
    }

    /**
     * Get the first setting record (singleton pattern)
     */
    public static function get()
    {
        return static::firstOrCreate([], [
            'company_name' => config('app.name', 'AI Trading Bot'),
            'logo_path' => null,
        ]);
    }
}
