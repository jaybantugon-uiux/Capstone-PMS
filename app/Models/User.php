<?php
// ====================================
// 1. User Model (app/Models/User.php)
// ====================================

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'first_name',
        'last_name',
        'username',
        'email',
        'password',
        'role',
        'status',
        'email_verification_token',
        'email_verification_sent_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'email_verification_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'deactivated_at' => 'datetime',
        'email_verification_sent_at' => 'datetime',
    ];

    /**
     * Determine if the user has verified their email address.
     *
     * @return bool
     */
    public function hasVerifiedEmail()
    {
        return ! is_null($this->email_verified_at);
    }

    /**
     * Mark the given user's email as verified.
     *
     * @return bool
     */
    public function markEmailAsVerified()
    {
        return $this->forceFill([
            'email_verified_at' => $this->freshTimestamp(),
        ])->save();
    }

    /**
     * Send the email verification notification.
     *
     * @return void
     */
    public function sendEmailVerificationNotification()
    {
        $this->notify(new \Illuminate\Auth\Notifications\VerifyEmail);
    }

    /**
     * Get the email address that should be used for verification.
     *
     * @return string
     */
    public function getEmailForVerification()
    {
        return $this->email;
    }

    /**
     * Determine role based on email domain
     */
    public static function determineRole($email)
    {
        $email = strtolower($email);
        
        if (str_contains($email, 'main.dru@gmail.com') || str_contains($email, 'bantugonjaymain.dru@gmail.com')) {
            return 'admin';
        } elseif (str_contains($email, 'emp.dru@gmail.com') || str_contains($email, 'bantugonjayemp.dru@gmail.com')) {
            return 'emp';
        } elseif (str_contains($email, 'finance.dru@gmail.com') || str_contains($email, 'bantugonjayfinance.dru@gmail.com')) {
            return 'finance';
        } elseif (str_contains($email, 'pm.dru@gmail.com') || str_contains($email, 'bantugonjaypm.dru@gmail.com')) {
            return 'pm';
        } elseif (str_contains($email, 'sc.dru@gmail.com') || str_contains($email, 'bantugonjaysc.dru@gmail.com')) {
            return 'sc';
        } else {
            return 'client';
        }
    }

    /**
     * Account status methods
     */
    public function isActive()
    {
        return $this->status === 'active';
    }

    public function isDeactivated()
    {
        return $this->status === 'deactivated';
    }

    public function deactivate()
    {
        try {
            $this->status = 'deactivated';
            $this->deactivated_at = now();
            $this->tokens()->delete(); // Revoke all API tokens
            $this->save();
            
            Log::info('User account deactivated', [
                'user_id' => $this->id,
                'email' => $this->email,
                'role' => $this->role
            ]);
            
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to deactivate user', [
                'user_id' => $this->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    public function reactivate()
    {
        try {
            $this->status = 'active';
            $this->deactivated_at = null;
            $success = $this->save();

            if ($success) {
                Log::info('User account reactivated', [
                    'user_id' => $this->id,
                    'email' => $this->email,
                    'role' => $this->role
                ]);
            }

            return $success;
        } catch (\Exception $e) {
            Log::error('Failed to reactivate user account', [
                'user_id' => $this->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Email verification methods
     */
    public function canResendVerification()
    {
        // Allow resending if no previous email was sent or if 1 minute has passed
        return !$this->email_verification_sent_at || 
               $this->email_verification_sent_at->diffInMinutes(now()) >= 1;
    }

    public function markVerificationEmailSent()
    {
        $this->email_verification_sent_at = now();
        $this->save();
    }

    public function isVerificationExpired()
    {
        // Verification links expire after 60 minutes
        return $this->email_verification_sent_at && 
               $this->email_verification_sent_at->diffInMinutes(now()) > 60;
    }

    /**
     * Accessors and mutators
     */
    public function getFullNameAttribute()
    {
        return "{$this->first_name} {$this->last_name}";
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeDeactivated($query)
    {
        return $query->where('status', 'deactivated');
    }

    public function scopeVerified($query)
    {
        return $query->whereNotNull('email_verified_at');
    }

    public function scopeUnverified($query)
    {
        return $query->whereNull('email_verified_at');
    }

    public function projects()
    {
        return $this->hasMany(Project::class, 'created_by');
    }

    public function assignedTasks()
    {
        return $this->hasMany(Task::class, 'assigned_to');
    }

    /**
     * Boot method
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($user) {
            if (empty($user->status)) {
                $user->status = 'active';
            }
        });
    }
}