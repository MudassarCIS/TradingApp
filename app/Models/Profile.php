<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Profile extends Model
{
    protected $fillable = [
        'user_id',
        'first_name',
        'last_name',
        'phone',
        'date_of_birth',
        'address',
        'city',
        'state',
        'country',
        'postal_code',
        'id_document_type',
        'id_document_number',
        'id_document_front',
        'id_document_back',
        'kyc_status',
        'kyc_notes',
        'transaction_password',
        'referral_code',
        'referred_by',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function referredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referred_by');
    }

    public function getFullNameAttribute(): string
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }

    public function isKycApproved(): bool
    {
        return $this->kyc_status === 'approved';
    }

    public function isKycPending(): bool
    {
        return $this->kyc_status === 'pending';
    }

    public function isKycRejected(): bool
    {
        return $this->kyc_status === 'rejected';
    }
}
