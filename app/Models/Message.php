<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Message extends Model
{
    protected $fillable = [
        'user_id',
        'admin_id',
        'subject',
        'message',
        'type',
        'status',
        'priority',
        'is_read_by_user',
        'is_read_by_admin',
        'last_reply_at',
    ];

    protected $casts = [
        'is_read_by_user' => 'boolean',
        'is_read_by_admin' => 'boolean',
        'last_reply_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function isOpen(): bool
    {
        return $this->status === 'open';
    }

    public function isInProgress(): bool
    {
        return $this->status === 'in_progress';
    }

    public function isResolved(): bool
    {
        return $this->status === 'resolved';
    }

    public function isClosed(): bool
    {
        return $this->status === 'closed';
    }

    public function isHighPriority(): bool
    {
        return $this->priority === 'high' || $this->priority === 'urgent';
    }

    public function markAsReadByUser(): bool
    {
        $this->is_read_by_user = true;
        return $this->save();
    }

    public function markAsReadByAdmin(): bool
    {
        $this->is_read_by_admin = true;
        return $this->save();
    }
}
