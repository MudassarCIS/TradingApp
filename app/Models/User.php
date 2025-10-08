<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasRoles, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'user_type',
        'is_active',
        'last_login_at',
        'referral_code',
        'referred_by',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'last_login_at' => 'datetime',
        ];
    }

    // Relationships
    public function profile()
    {
        return $this->hasOne(Profile::class);
    }

    public function wallets()
    {
        return $this->hasMany(Wallet::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function trades()
    {
        return $this->hasMany(Trade::class);
    }

    public function agents()
    {
        return $this->hasMany(Agent::class);
    }

    public function apiAccounts()
    {
        return $this->hasMany(ApiAccount::class);
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    // Referral relationships
    public function referrals()
    {
        return $this->hasMany(Referral::class, 'referrer_id');
    }

    public function referredBy()
    {
        return $this->belongsTo(User::class, 'referred_by');
    }

    public function referredUsers()
    {
        return $this->hasMany(User::class, 'referred_by');
    }

    // Helper methods - Updated to use Spatie roles instead of user_type
    public function isAdmin()
    {
        return $this->hasRole('admin');
    }

    public function isCustomer()
    {
        return $this->hasRole('customer');
    }

    public function isManager()
    {
        return $this->hasRole('manager');
    }

    public function isModerator()
    {
        return $this->hasRole('moderator');
    }

    public function isStaff()
    {
        return $this->hasAnyRole(['admin', 'manager', 'moderator']);
    }

    public function getMainWallet($currency = 'USDT')
    {
        return $this->wallets()->where('currency', $currency)->first();
    }

    public function generateReferralCode()
    {
        if (!$this->referral_code) {
            $this->referral_code = strtoupper(substr($this->name, 0, 3) . rand(1000, 9999));
            $this->save();
        }
        return $this->referral_code;
    }
}
