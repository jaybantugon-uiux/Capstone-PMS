<?php
namespace App\Services;

use App\Models\User;
use App\Notifications\CustomVerifyEmail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Arr;
use Exception;

class EmailService
{
    /**
     * Send verification email with proper error handling and rate limiting
     */
    public function sendVerificationEmail(User $user, $resend = false)
    {
        try {
            // Check if user needs verification
            if ($user->hasVerifiedEmail() && !$resend) {
                return [
                    'success' => false,
                    'message' => 'Email already verified',
                    'code' => 'ALREADY_VERIFIED'
                ];
            }

            // Check rate limiting
            if (!$resend && !$user->canResendVerification()) {
                $nextAvailable = $user->email_verification_sent_at ? 
                    $user->email_verification_sent_at->addMinute() : now();
                    
                return [
                    'success' => false,
                    'message' => 'Please wait at least 1 minute before requesting another verification email',
                    'code' => 'RATE_LIMITED',
                    'next_resend_available' => $nextAvailable
                ];
            }

            // Get optimal mailer configuration
            $mailerConfig = $this->getOptimalMailerConfig();
            
            // Temporarily switch mailer if needed
            $originalMailer = Config::get('mail.default');
            if ($mailerConfig['mailer'] !== $originalMailer) {
                Config::set('mail.default', $mailerConfig['mailer']);
                
                // Also update the specific mailer configuration if needed
                $this->configureMailer($mailerConfig['mailer']);
            }

            // Send notification
            $user->notify(new CustomVerifyEmail());
            
            // Mark verification email as sent
            $user->markVerificationEmailSent();
            
            // Restore original mailer
            if ($mailerConfig['mailer'] !== $originalMailer) {
                Config::set('mail.default', $originalMailer);
            }
            
            Log::info('Email verification sent successfully', [
                'user_id' => $user->id,
                'email' => $user->email,
                'role' => $user->role,
                'mailer' => $mailerConfig['mailer'],
                'resend' => $resend,
                'environment' => app()->environment(),
                'verification_url_type' => $this->getVerificationUrlType()
            ]);

            return [
                'success' => true,
                'message' => 'Verification email sent successfully',
                'mailer_used' => $mailerConfig['mailer'],
                'next_resend_available' => now()->addMinute(),
                'expires_in_minutes' => Config::get('auth.verification.expire', 60)
            ];

        } catch (Exception $e) {
            // Restore original mailer on error
            if (isset($originalMailer) && $mailerConfig['mailer'] !== $originalMailer) {
                Config::set('mail.default', $originalMailer);
            }
            
            Log::error('Failed to send verification email', [
                'user_id' => $user->id,
                'email' => $user->email,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'mailer_config' => $mailerConfig ?? null,
                'environment' => app()->environment()
            ]);

            return [
                'success' => false,
                'message' => 'Failed to send verification email: ' . $e->getMessage(),
                'code' => 'SEND_FAILED',
                'debug_info' => app()->environment('local') ? [
                    'error' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ] : null
            ];
        }
    }

    /**
     * Get optimal mailer configuration based on environment
     */
    private function getOptimalMailerConfig()
    {
        $environment = app()->environment();
        
        switch ($environment) {
            case 'production':
                // Use Gmail for production
                if ($this->isGmailConfigured()) {
                    return [
                        'mailer' => 'gmail',
                        'description' => 'Gmail SMTP for production'
                    ];
                }
                // Fallback to SMTP if Gmail not configured
                return [
                    'mailer' => 'smtp',
                    'description' => 'SMTP for production (Gmail fallback)'
                ];
                
            case 'staging':
                // Prefer Mailtrap for staging, fallback to Gmail
                if ($this->isMailtrapConfigured()) {
                    return [
                        'mailer' => 'mailtrap',
                        'description' => 'Mailtrap for staging environment'
                    ];
                }
                if ($this->isGmailConfigured()) {
                    return [
                        'mailer' => 'gmail',
                        'description' => 'Gmail for staging (Mailtrap not configured)'
                    ];
                }
                return [
                    'mailer' => 'log',
                    'description' => 'Log driver (no email service configured)'
                ];
                
            case 'local':
            case 'development':
            default:
                // Check configurations in order of preference for local development
                if ($this->isMailtrapConfigured()) {
                    return [
                        'mailer' => 'mailtrap',
                        'description' => 'Mailtrap for local development'
                    ];
                }
                if ($this->isGmailConfigured()) {
                    return [
                        'mailer' => 'gmail',
                        'description' => 'Gmail for local development'
                    ];
                }
                return [
                    'mailer' => 'log',
                    'description' => 'Log driver for local development'
                ];
        }
    }

    /**
     * Check if Gmail is properly configured
     */
    private function isGmailConfigured()
    {
        return !empty(Config::get('mail.mailers.gmail.username')) && 
               !empty(Config::get('mail.mailers.gmail.password'));
    }

    /**
     * Check if Mailtrap is properly configured
     */
    private function isMailtrapConfigured()
    {
        return !empty(Config::get('mail.mailers.mailtrap.username')) && 
               !empty(Config::get('mail.mailers.mailtrap.password'));
    }

    /**
     * Configure mailer-specific settings
     */
    private function configureMailer($mailer)
    {
        switch ($mailer) {
            case 'gmail':
                // Ensure Gmail-specific settings are applied
                Config::set('mail.mailers.gmail.host', 'smtp.gmail.com');
                Config::set('mail.mailers.gmail.port', 587);
                Config::set('mail.mailers.gmail.encryption', 'tls');
                break;
                
            case 'mailtrap':
                // Ensure Mailtrap-specific settings are applied
                Config::set('mail.mailers.mailtrap.host', Config::get('mail.mailers.mailtrap.host', 'sandbox.smtp.mailtrap.io'));
                Config::set('mail.mailers.mailtrap.port', Config::get('mail.mailers.mailtrap.port', 2525));
                Config::set('mail.mailers.mailtrap.encryption', Config::get('mail.mailers.mailtrap.encryption', 'tls'));
                break;
        }
    }

    /**
     * Get verification URL type for logging
     */
    private function getVerificationUrlType()
    {
        $isApiRequest = request()->expectsJson() || 
                       request()->is('api/*') || 
                       request()->header('Accept') === 'application/json';
        
        return $isApiRequest ? 'api' : 'web';
    }

    /**
     * Test email configuration
     */
    public function testEmailConfiguration()
    {
        try {
            $mailerConfig = $this->getOptimalMailerConfig();
            $mailer = $mailerConfig['mailer'];
            $config = Config::get("mail.mailers.{$mailer}");
            
            // Set the mailer temporarily
            $originalMailer = Config::get('mail.default');
            Config::set('mail.default', $mailer);
            $this->configureMailer($mailer);
            
            // Test connection by sending a test email
            Mail::raw('This is a test email from Laravel Email Verification Service', function ($message) {
                $testEmail = Config::get('mail.from.address', 'test@example.com');
                $message->to($testEmail)
                       ->subject('Email Configuration Test - ' . config('app.name'));
            });
            
            // Restore original mailer
            Config::set('mail.default', $originalMailer);
            
            return [
                'success' => true,
                'mailer' => $mailer,
                'config' => Arr::except($config, ['password', 'username']),
                'from_address' => Config::get('mail.from.address'),
                'from_name' => Config::get('mail.from.name'),
                'environment' => app()->environment(),
                'description' => $mailerConfig['description']
            ];
        } catch (Exception $e) {
            // Restore original mailer on error
            if (isset($originalMailer)) {
                Config::set('mail.default', $originalMailer);
            }
            
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'mailer' => $mailer ?? 'unknown',
                'environment' => app()->environment()
            ];
        }
    }

    /**
     * Get email statistics for a user
     */
    public function getEmailStats(User $user)
    {
        $nextResendTime = $user->email_verification_sent_at ? 
            $user->email_verification_sent_at->addMinute() : now();
            
        return [
            'is_verified' => $user->hasVerifiedEmail(),
            'verification_sent_at' => $user->email_verification_sent_at,
            'can_resend' => $user->canResendVerification(),
            'next_resend_available' => $nextResendTime,
            'is_expired' => $user->isVerificationExpired(),
            'expires_in_minutes' => Config::get('auth.verification.expire', 60),
            'time_until_next_resend' => $user->email_verification_sent_at ? 
                max(0, 60 - $user->email_verification_sent_at->diffInSeconds(now())) : 0
        ];
    }

    /**
     * Get current mailer status
     */
    public function getMailerStatus()
    {
        $mailerConfig = $this->getOptimalMailerConfig();
        
        return [
            'current_mailer' => $mailerConfig['mailer'],
            'description' => $mailerConfig['description'],
            'environment' => app()->environment(),
            'gmail_configured' => $this->isGmailConfigured(),
            'mailtrap_configured' => $this->isMailtrapConfigured(),
            'from_address' => Config::get('mail.from.address'),
            'from_name' => Config::get('mail.from.name')
        ];
    }
}