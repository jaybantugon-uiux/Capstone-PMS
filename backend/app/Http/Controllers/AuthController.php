<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Str;
use App\Models\User;
use App\Services\EmailService;
use Exception;

class AuthController extends Controller
{
    protected $emailService;

    public function __construct(EmailService $emailService)
    {
        $this->emailService = $emailService;
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        try {
            $user = User::create([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'username' => $request->username,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => User::determineRole($request->email),
                'status' => 'active',
            ]);

            Auth::login($user);

            // Send verification email using the service
            $emailResult = $this->emailService->sendVerificationEmail($user);
            
            if (!$emailResult['success']) {
                Log::warning('Email verification failed during registration', [
                    'user_id' => $user->id,
                    'email_result' => $emailResult
                ]);
                
                return redirect()->route('verification.notice')
                    ->with('warning', 'Account created successfully, but there was an issue sending the verification email. You can request a new one below.');
            }

            return redirect()->route('verification.notice')
                ->with('status', 'Registration successful! Please check your email to verify your account.');
                
        } catch (Exception $e) {
            Log::error('User registration failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()->withErrors(['registration' => 'Registration failed. Please try again.'])
                        ->withInput();
        }
    }

    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $user = Auth::user();
            
            if (!($user instanceof User)) {
                Log::error('User is not an instance of App\Models\User', [
                    'user_class' => get_class($user),
                ]);
                return back()->withErrors(['email' => 'Internal server error: Invalid user instance']);
            }

            // Check if account is deactivated
            if ($user->isDeactivated()) {
                Auth::logout();
                return back()->withErrors([
                    'email' => 'Your account has been deactivated. You can reactivate it using the "Reactivate Account" option.',
                ])->onlyInput('email');
            }
            
            $request->session()->regenerate();
            
            Log::info('User logged in successfully', [
                'user_id' => $user->id,
                'role' => $user->role,
                'email_verified' => $user->hasVerifiedEmail()
            ]);
            
            // Require email verification for ALL users
            if (!$user->hasVerifiedEmail()) {
                Log::info('Redirecting to email verification', [
                    'user_id' => $user->id,
                    'role' => $user->role
                ]);
                return redirect()->route('verification.notice');
            }
            
            return redirect()->intended('dashboard');
        }

        Log::warning('Failed login attempt', ['email' => $request->email]);

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    public function showVerificationNotice()
    {
        $user = Auth::user();

        if ($user && $user->hasVerifiedEmail()) {
            return redirect()->route('dashboard');
        }

        $emailStats = $user ? $this->emailService->getEmailStats($user) : null;

        return view('auth.verify-email', compact('emailStats'));
    }

    public function verifyEmail(Request $request)
    {
        $userId = $request->route('id');
        $hash = $request->route('hash');

        Log::info('Email verification attempt', [
            'user_id' => $userId,
            'hash' => substr($hash, 0, 10) . '...' // Log only part of hash for security
        ]);

        $user = User::find($userId);

        if (!$user) {
            Log::error('Email verification failed - user not found', [
                'user_id' => $userId
            ]);
            return redirect()->route('login')->withErrors(['email' => 'User not found.']);
        }

        // Check if account is deactivated
        if ($user->isDeactivated()) {
            Log::error('Email verification failed - account deactivated', [
                'user_id' => $user->id
            ]);
            return redirect()->route('login')->withErrors(['email' => 'Account has been deactivated. Please reactivate your account first.']);
        }

        // Verify the hash matches
        if (!hash_equals(sha1($user->getEmailForVerification()), $hash)) {
            Log::error('Email verification failed - hash mismatch', [
                'user_id' => $user->id
            ]);
            return redirect()->route('login')->withErrors(['email' => 'Invalid or expired verification link.']);
        }

        // Check if email is already verified
        if ($user->hasVerifiedEmail()) {
            Log::info('Email already verified', [
                'user_id' => $user->id,
                'role' => $user->role
            ]);
            
            if (!Auth::check()) {
                Auth::login($user);
                $request->session()->regenerate();
            }
            
            return $this->redirectToDashboard($user)->with('status', 'Email already verified!');
        }

        // Mark email as verified
        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
            Log::info('Email verified successfully', [
                'user_id' => $user->id,
                'role' => $user->role,
                'email' => $user->email
            ]);
        }

        // Log the user in after verification
        if (!Auth::check()) {
            Auth::login($user);
            $request->session()->regenerate();
            Log::info('User automatically logged in after email verification', [
                'user_id' => $user->id,
                'role' => $user->role
            ]);
        }

        return $this->redirectToDashboard($user)->with('status', 'Email verified successfully! Welcome to your dashboard.');
    }

    public function resendVerification(Request $request)
    {
        $user = $request->user();
        
        if ($user->hasVerifiedEmail()) {
            return redirect()->route('dashboard');
        }

        $emailResult = $this->emailService->sendVerificationEmail($user, true);

        if ($emailResult['success']) {
            return back()->with('status', 'Verification email sent successfully! Please check your inbox.');
        } else {
            $errorMessage = $emailResult['code'] === 'RATE_LIMITED' 
                ? $emailResult['message'] 
                : 'Failed to send verification email. Please try again.';
                
            return back()->withErrors(['email' => $errorMessage]);
        }
    }

    private function redirectToDashboard($user)
    {
        switch ($user->role) {
            case 'admin':
                return redirect()->route('admin.dashboard');
            case 'emp':
                return redirect()->route('employee.dashboard');
            case 'finance':
                return redirect()->route('finance.dashboard');
            case 'pm':
                return redirect()->route('pm.dashboard');
            case 'sc':
                return redirect()->route('sc.dashboard');
            case 'client':
            default:
                return redirect()->route('client.dashboard');
        }
    }

    /**
 * Show the forgot password form
 */
public function showForgotPassword()
{
    return view('auth.forgot-password');
}

/**
 * Send password reset link
 */
public function sendResetLink(Request $request)
{
    $request->validate([
        'email' => 'required|email',
    ]);

    $status = Password::sendResetLink($request->only('email'));

    if ($status === Password::RESET_LINK_SENT) {
        return back()->with('status', 'Password reset link sent to your email.');
    }

    return back()->withErrors([
        'email' => 'Unable to send password reset link. Please check your email address.',
    ]);
}

/**
 * Show the reset password form
 */
public function showResetPassword(Request $request, $token = null)
{
    return view('auth.reset-password', [
        'token' => $token,
        'email' => $request->email
    ]);
}

/**
 * Reset password
 */
public function resetPassword(Request $request)
{
    $request->validate([
        'token' => 'required',
        'email' => 'required|email',
        'password' => 'required|min:8|confirmed',
    ]);

    $status = Password::reset(
        $request->only('email', 'password', 'password_confirmation', 'token'),
        function (User $user, string $password) {
            $user->forceFill([
                'password' => Hash::make($password)
            ])->setRememberToken(Str::random(60));

            $user->save();
            event(new PasswordReset($user));
        }
    );

    if ($status === Password::PASSWORD_RESET) {
        return redirect()->route('login')->with('status', 'Password reset successfully. You can now log in with your new password.');
    }

    return back()->withErrors([
        'email' => 'Unable to reset password. The token may be invalid or expired.',
    ]);
}

/**
 * Show account deactivation form
 */
public function showDeactivateAccount()
{
    return view('auth.deactivate-account');
}

/**
 * Deactivate user account
 */
public function deactivateAccount(Request $request)
{
    $request->validate([
        'password' => 'required',
        'confirmation' => 'required|in:DEACTIVATE',
    ], [
        'confirmation.in' => 'Please type "DEACTIVATE" to confirm account deactivation.',
    ]);

    $user = $request->user();

    if (!Hash::check($request->password, $user->password)) {
        return back()->withErrors([
            'password' => 'The provided password is incorrect.',
        ]);
    }

    try {
        $user->deactivate();

        Log::info('User account deactivated', [
            'user_id' => $user->id,
            'role' => $user->role,
            'email' => $user->email
        ]);

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('status', 'Your account has been deactivated successfully.');
    } catch (Exception $e) {
        Log::error('Account deactivation failed', [
            'user_id' => $user->id,
            'error' => $e->getMessage()
        ]);
        
        return back()->withErrors([
            'deactivation' => 'Failed to deactivate account. Please try again.'
        ]);
    }
}

/**
 * Show account reactivation form
 */
public function showReactivateAccount()
{
    return view('auth.reactivate-account');
}

/**
 * Reactivate user account
 */
public function reactivateAccount(Request $request)
{
    $request->validate([
        'email' => 'required|email',
        'password' => 'required',
    ]);

    $user = User::where('email', $request->email)->first();

    if (!$user || !Hash::check($request->password, $user->password)) {
        return back()->withErrors([
            'email' => 'The provided credentials are incorrect.',
        ])->onlyInput('email');
    }

    if ($user->isActive()) {
        return back()->withErrors([
            'email' => 'This account is already active.',
        ])->onlyInput('email');
    }

    try {
        $user->reactivate();

        Log::info('User account reactivated', [
            'user_id' => $user->id,
            'role' => $user->role,
            'email' => $user->email
        ]);

        return redirect()->route('login')->with('status', 'Your account has been reactivated successfully. You can now log in.');
    } catch (Exception $e) {
        Log::error('Account reactivation failed', [
            'user_id' => $user->id,
            'error' => $e->getMessage()
        ]);
        
        return back()->withErrors([
            'reactivation' => 'Failed to reactivate account. Please try again.'
        ])->onlyInput('email');
    }
}
    
    public function logout(Request $request)
    {
        Log::info('User logged out', [
            'user_id' => Auth::id(),
            'role' => Auth::user()->role
        ]);
        
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect('/');
    }
}