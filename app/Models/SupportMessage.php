<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupportMessage extends Model
{
    protected $fillable = [
        'thread_id',
        'user_id',
        'admin_id',
        'message',
        'attachment',
        'attachment_name',
        'attachment_type',
        'sender_type',
        'is_read_by_customer',
        'is_read_by_admin',
        'read_at',
    ];

    protected $casts = [
        'is_read_by_customer' => 'boolean',
        'is_read_by_admin' => 'boolean',
        'read_at' => 'datetime',
    ];

    /**
     * Get the customer user
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the admin user
     */
    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    /**
     * Generate a unique thread ID for a user
     */
    public static function generateThreadId(int $userId): string
    {
        return 'user_' . $userId;
    }

    /**
     * Mark message as read by customer
     */
    public function markAsReadByCustomer(): bool
    {
        $this->is_read_by_customer = true;
        $this->read_at = now();
        return $this->save();
    }

    /**
     * Mark message as read by admin
     */
    public function markAsReadByAdmin(): bool
    {
        $this->is_read_by_admin = true;
        $this->read_at = now();
        return $this->save();
    }

    /**
     * Scope: Unread messages by customer
     */
    public function scopeUnreadByCustomer($query)
    {
        return $query->where('is_read_by_customer', false)
            ->where('sender_type', 'admin');
    }

    /**
     * Scope: Unread messages by admin
     */
    public function scopeUnreadByAdmin($query)
    {
        return $query->where('is_read_by_admin', false)
            ->where('sender_type', 'customer');
    }

    /**
     * Scope: Recent messages (within last 7 days)
     */
    public function scopeRecent($query)
    {
        return $query->where('created_at', '>=', now()->subDays(7));
    }

    /**
     * Scope: Old messages (older than 7 days)
     */
    public function scopeOld($query)
    {
        return $query->where('created_at', '<', now()->subDays(7));
    }
}
