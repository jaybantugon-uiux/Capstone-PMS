<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail as BaseVerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Log;

class CustomVerifyEmail extends BaseVerifyEmail
{
    /**
     * Build the mail representation of the notification.
     */
    public function toMail($notifiable)
    {
        $verificationUrl = $this->verificationUrl($notifiable);
        $appName = config('app.name');
        $expirationMinutes = Config::get('auth.verification.expire', 60);
        
        Log::info('Building verification email', [
            'user_id' => $notifiable->id,
            'email' => $notifiable->email,
            'verification_url' => $verificationUrl,
            'expires_in_minutes' => $expirationMinutes,
            'mailer' => Config::get('mail.default'),
            'environment' => app()->environment()
        ]);

        return (new MailMessage)
            ->subject('Verify Your Email Address - ' . $appName)
            ->greeting('Hello ' . $notifiable->first_name . '!')
            ->line('Welcome to ' . $appName . '! We\'re excited to have you on board.')
            ->line('Please click the button below to verify your email address and activate your account.')
            ->action('Verify Email Address', $verificationUrl)
            ->line('This verification link will expire in ' . $expirationMinutes . ' minutes for security purposes.')
            ->line('If you did not create an account with us, please ignore this email - no further action is required.')
            ->line('If you\'re having trouble clicking the "Verify Email Address" button, copy and paste the URL below into your web browser:')
            ->line($verificationUrl)
            ->salutation('Best regards,<br>The ' . $appName . ' Team')
            ->priority(1) // High priority for verification emails
            ->metadata('user_id', $notifiable->id)
            ->metadata('user_role', $notifiable->role)
            ->metadata('environment', app()->environment());
    }

    /**
     * Get the verification URL for the given notifiable.
     */
    protected function verificationUrl($notifiable)
    {
        $expirationMinutes = Config::get('auth.verification.expire', 60);
        $expiresAt = Carbon::now()->addMinutes($expirationMinutes);
        
        $isApiRequest = $this->isApiRequest();

        $routeName = $isApiRequest ? 'api.verification.verify' : 'verification.verify';
        
        $url = URL::temporarySignedRoute(
            $routeName,
            $expiresAt,
            [
                'id' => $notifiable->getKey(),
                'hash' => sha1($notifiable->getEmailForVerification()),
            ]
        );

        Log::info('Generated verification URL', [
            'user_id' => $notifiable->id,
            'route_name' => $routeName,
            'expires_at' => $expiresAt->toISOString(),
            'is_api_request' => $isApiRequest,
            'url_length' => strlen($url)
        ]);

        return $url;
    }

    /**
     * Determine if this is an API request
     */
    private function isApiRequest()
    {
        if (!request()) {
            return false;
        }

        return request()->expectsJson() || 
               request()->is('api/*') || 
               request()->header('Accept') === 'application/json' ||
               request()->header('Content-Type') === 'application/json';
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray($notifiable)
    {
        return [
            'user_id' => $notifiable->id,
            'email' => $notifiable->email,
            'verification_url' => $this->verificationUrl($notifiable),
            'expires_at' => Carbon::now()->addMinutes(Config::get('auth.verification.expire', 60))->toISOString(),
            'sent_at' => now()->toISOString()
        ];
    }

    /**
     * Determine the notification's delivery channels.
     */
    public function via($notifiable)
    {
        $channels = ['mail'];
        
        // Add database channel for logging if needed
        if (Config::get('app.log_notifications', false)) {
            $channels[] = 'database';
        }
        
        return $channels;
    }
}