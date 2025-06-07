<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\Log;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'username',
        'email',
        'password',
        'role',
        'status',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'deactivated_at' => 'datetime',
    ];

    /**
     * Determine role based on email domain
     */
    public static function determineRole($email)
    {
        // Check for specific role-based emails
        if (str_contains($email, 'admin.dru@gmail.com') || str_contains($email, 'bantugonjayadmin.dru@gmail.com')) {
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
            return 'client'; // Default role
        }
    }

    /**
     * Check if user account is active
     */
    public function isActive()
    {
        return $this->status === 'active';
    }

    /**
     * Check if user account is deactivated
     */
    public function isDeactivated()
    {
        return $this->status === 'deactivated';
    }

   /**
     * Deactivate the user account.
     *
     * @return bool
     */
    public function deactivate()
    {
        try {
            $this->status = 'deactivated';
            $this->save();
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to deactivate user', [
                'user_id' => $this->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Reactivate user account
     */
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
     * Get the user's full name
     */
    public function getFullNameAttribute()
    {
        return "{$this->first_name} {$this->last_name}";
    }

    /**
     * Scope query to only include active users
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope query to only include deactivated users
     */
    public function scopeDeactivated($query)
    {
        return $query->where('status', 'deactivated');
    }

    /**
     * Boot method to set default values
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