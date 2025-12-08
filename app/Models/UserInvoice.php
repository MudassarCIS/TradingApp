<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserInvoice extends Model
{
    protected $fillable = [
        'user_id',
        'plan_id',
        'rent_bot_package_id',
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

        // Generate invoice_id after the invoice is created
        // Format: INV-00.id (e.g., INV-0001, INV-0010, INV-0100)
        static::created(function ($invoice) {
            if (empty($invoice->invoice_id)) {
                // Generate invoice_id using the invoice's database ID
                // Format: INV-0001, INV-0010, INV-0100, INV-1000, etc.
                $invoiceId = 'INV-' . str_pad($invoice->id, 4, '0', STR_PAD_LEFT);
                // Use updateQuietly to prevent triggering events again
                $invoice->updateQuietly(['invoice_id' => $invoiceId]);
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function rentBotPackage(): BelongsTo
    {
        return $this->belongsTo(RentBotPackage::class, 'rent_bot_package_id');
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
