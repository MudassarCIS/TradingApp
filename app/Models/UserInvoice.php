<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserInvoice extends Model
{
    protected $fillable = [
        'user_id',
        'invoice_type',
        'amount',
        'due_date',
        'status',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'due_date' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the formatted invoice ID with prefix
     * Format: Inv-YYYY-000.id
     */
    public function getFormattedInvoiceIdAttribute()
    {
        $year = $this->created_at->format('Y');
        $paddedId = str_pad($this->id, 3, '0', STR_PAD_LEFT);
        return "Inv-{$year}-{$paddedId}";
    }

    /**
     * Accessor for formatted invoice ID (for backward compatibility)
     */
    public function getInvoiceNumberAttribute()
    {
        return $this->formatted_invoice_id;
    }
}
