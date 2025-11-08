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
        'active_plan_id',
        'active_investment_amount',
    ];
    
    // Dynamically add active_plan_name if column exists
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        
        // Add active_plan_name to fillable if column exists
        try {
            if (\Illuminate\Support\Facades\Schema::hasColumn('users', 'active_plan_name')) {
                if (!in_array('active_plan_name', $this->fillable)) {
                    $this->fillable[] = 'active_plan_name';
                }
            }
        } catch (\Exception $e) {
            // Column doesn't exist yet, skip it
        }
    }

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
            'active_investment_amount' => 'decimal:8',
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

    public function deposits()
    {
        return $this->hasMany(Deposit::class);
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

    public function activePlan()
    {
        return $this->belongsTo(Plan::class, 'active_plan_id');
    }

    public function parentReferral()
    {
        return $this->hasOne(Referral::class, 'referred_id', 'id')->where('status', 'active');
    }

    public function referrer()
    {
        return $this->belongsTo(User::class, 'referred_by');
    }

    public function activeBots()
    {
        return $this->hasMany(UserActiveBot::class);
    }

    public function invoices()
    {
        return $this->hasMany(UserInvoice::class);
    }

    public function planHistory()
    {
        return $this->hasMany(UserPlanHistory::class);
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

    /**
     * Get active packages (UserActiveBot with paid invoices)
     */
    public function getActivePackages()
    {
        return $this->activeBots()
            ->latest()
            ->get()
            ->filter(function($bot) {
                // Check if there's a paid invoice matching this bot's type
                $botCreatedAt = $bot->created_at;
                $startTime = $botCreatedAt->copy()->subMinutes(10);
                $endTime = $botCreatedAt->copy()->addMinutes(10);
                
                $invoice = $this->invoices()
                    ->where('invoice_type', $bot->buy_type)
                    ->where('status', 'Paid')
                    ->where('created_at', '>=', $startTime)
                    ->where('created_at', '<=', $endTime)
                    ->first();
                
                return $invoice !== null;
            })
            ->map(function($bot) {
                $planDetails = $bot->buy_plan_details ?? [];
                $packageTitle = $bot->buy_type;
                
                // Get number of available bots from plan details
                $availableBots = 0;
                if ($bot->buy_type === 'Rent A Bot') {
                    $availableBots = $planDetails['allowed_bots'] ?? 0;
                } elseif ($bot->buy_type === 'Sharing Nexa') {
                    $availableBots = $planDetails['bots_allowed'] ?? 0;
                }
                
                return [
                    'id' => $bot->id,
                    'type' => $bot->buy_type,
                    'title' => $packageTitle,
                    'available_bots' => $availableBots,
                    'plan_details' => $planDetails,
                    'created_at' => $bot->created_at,
                ];
            })
            ->values();
    }

    public function generateReferralCode()
    {
        if (!$this->referral_code) {
            do {
                $code = strtoupper(\Illuminate\Support\Str::random(8));
            } while (User::where('referral_code', $code)->exists());
            $this->referral_code = $code;
            $this->save();
        }
        return $this->referral_code;
    }

    protected static function booted()
    {
        static::creating(function ($user) {
            if (empty($user->referral_code)) {
                do {
                    $code = strtoupper(\Illuminate\Support\Str::random(8));
                } while (User::where('referral_code', $code)->exists());
                $user->referral_code = $code;
            }
            
            // Set default Starter plan for new customer users
            // Check if user is customer by checking if they don't have admin/manager/moderator roles
            if (empty($user->active_plan_id)) {
                try {
                    $starterPlan = \App\Models\Plan::where('name', 'Starter')
                        ->where('is_active', true)
                        ->first();
                    
                    if ($starterPlan) {
                        $user->active_plan_id = $starterPlan->id;
                        // Only set active_plan_name if column exists
                        try {
                            if (\Illuminate\Support\Facades\Schema::hasColumn('users', 'active_plan_name')) {
                                $user->active_plan_name = $starterPlan->name;
                            }
                        } catch (\Exception $e) {
                            // Column doesn't exist, skip it
                        }
                    }
                } catch (\Exception $e) {
                    // If there's an error, just continue without setting plan
                    \Log::warning('Could not set default Starter plan: ' . $e->getMessage());
                }
            }
        });
    }
}
