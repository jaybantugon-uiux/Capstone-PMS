<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Log;

class VerifyEmail extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * The number of times the job may be attempted.
     */
    public $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     */
    public $backoff = [60, 120, 300];

    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable)
    {
        $verificationUrl = $this->verificationUrl($notifiable);
        
        Log::info('Sending verification email', [
            'user_id' => $notifiable->id,
            'email' => $notifiable->email,
            'verification_url' => $verificationUrl
        ]);

        return (new MailMessage)
            ->subject('Verify Your Email Address - ' . config('app.name'))
            ->greeting('Hello ' . $notifiable->full_name . '!')
            ->line('Thank you for registering with ' . config('app.name') . '!')
            ->line('Please click the button below to verify your email address.')
            ->action('Verify Email', $verificationUrl)
            ->line('This verification link will expire in ' . Config::get('auth.verification.expire', 60) . ' minutes.')
            ->line('If you did not create an account, no further action is required.')
            ->salutation('Regards, ' . config('app.name') . ' Team');
    }

    /**
     * Get the verification URL for the given notifiable.
     */
    protected function verificationUrl($notifiable): string
    {
        // Get expiration time in minutes, default to 60 minutes
        $expireMinutes = Config::get('auth.verification.expire', 60);
        
        // Generate the full absolute URL with proper domain
        $url = URL::temporarySignedRoute(
            'verification.verify',
            Carbon::now()->addMinutes($expireMinutes),
            [
                'id' => $notifiable->getKey(),
                'hash' => sha1($notifiable->getEmailForVerification()),
            ],
            true // absolute = true for full URLs
        );
        
        // Ensure the URL uses the correct APP_URL from config
        $appUrl = config('app.url');
        if ($appUrl && !str_starts_with($url, $appUrl)) {
            $url = str_replace(url(''), $appUrl, $url);
        }
        
        Log::info('Generated verification URL', [
            'user_id' => $notifiable->id,
            'url' => $url,
            'expires_in_minutes' => $expireMinutes,
            'app_url' => $appUrl
        ]);
        
        return $url;
    }
}