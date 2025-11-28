<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserInvoice extends Model
{
    protected $fillable = [
        'user_id',
        'plan_id',
        'invoice_type',
        'amount',
        'due_date',
        'status',
        'invoice_id',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'due_date' => 'date',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($invoice) {
            if (empty($invoice->invoice_id)) {
                $invoice->invoice_id = static::generateUniqueInvoiceId();
            }
        });
    }

    /**
     * Generate a unique invoice ID
     * Format: INV-YYYYMMDD-HHMMSS-XXXX
     * Where XXXX is a random 4-digit number
     */
    public static function generateUniqueInvoiceId(): string
    {
        do {
            $year = date('Y');
            $date = date('Ymd');
            $time = date('His');
            $random = str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);
            $invoiceId = "INV-{$date}-{$time}-{$random}";
        } while (static::where('invoice_id', $invoiceId)->exists());

        return $invoiceId;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    /**
     * Get the formatted invoice ID with prefix
     * Format: Inv-YYYY-000.id (for backward compatibility)
     * If invoice_id exists, return it, otherwise use the formatted version
     */
    public function getFormattedInvoiceIdAttribute()
    {
        // If invoice_id column exists and has a value, use it
        if (!empty($this->invoice_id)) {
            return $this->invoice_id;
        }
        
        // Otherwise, use the old format for backward compatibility
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
