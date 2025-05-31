<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Str;
use Illuminate\Auth\MustVerifyEmail;
use App\Notifications\VerifyEmail as VerifyEmailNotification; // Import custom notification

class User extends Authenticatable
{
    use HasApiTokens, Notifiable, MustVerifyEmail;

    protected $fillable = [
        'first_name',
        'last_name',
        'username',
        'email',
        'password',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * Send the email verification notification using the custom class.
     */
    public function sendEmailVerificationNotification()
    {
        $this->notify(new VerifyEmailNotification);
    }

    /**
     * Get the full name attribute for notifications.
     */
    public function getFullNameAttribute()
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    public static function determineRole($email)
    {
        $parts = explode('@', $email);
        if (count($parts) !== 2 || $parts[1] !== 'gmail.com') {
            return 'client';
        }
        $localPart = $parts[0];
        if (Str::endsWith($localPart, 'admin.dru')) {
            return 'admin';
        } elseif (Str::endsWith($localPart, 'emp.dru')) {
            return 'emp';
        } elseif (Str::endsWith($localPart, 'finance.dru')) {
            return 'finance';
        } elseif (Str::endsWith($localPart, 'pm.dru')) {
            return 'pm';
        } elseif (Str::endsWith($localPart, 'sc.dru')) {
            return 'sc';
        } else {
            return 'client';
        }
    }

    /**
     * Route notifications for the mail channel.
     */
    public function routeNotificationForMail($notification)
    {
        return $this->email;
    }
}