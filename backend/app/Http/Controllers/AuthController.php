<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Str;
use App\Models\User;
use Exception;

class AuthController extends Controller
{
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

            // Send verification email to ALL users
            try {
                $user->sendEmailVerificationNotification();
                Log::info('Email verification notification sent', [
                    'user_id' => $user->id,
                    'role' => $user->role,
                    'email' => $user->email
                ]);
            } catch (Exception $e) {
                Log::error('Failed to send verification email', [
                    'user_id' => $user->id,
                    'role' => $user->role,
                    'error' => $e->getMessage(),
                ]);
                
                return redirect()->route('verification.notice')
                    ->with('warning', 'Account created, but there was an issue sending the verification email.');
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
        
        // Temporary debug code
        if (!($user instanceof User)) {
            Log::error('User is not an instance of App\Models\User', [
                'user_class' => get_class($user),
                'user_data' => json_encode($user)
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
            
            Log::info('User logged in', [
                'user_id' => Auth::id(),
                'role' => Auth::user()->role
            ]);
            
            // Require email verification for ALL users
        if (!$user->hasVerifiedEmail()) {
    Log::info('User needs email verification', [
        'user_id' => Auth::id(),
        'role' => Auth::user()->role
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
        /** @var User $user */
        $user = Auth::user();

        if ($user && $user->hasVerifiedEmail()) {
            return redirect()->route('dashboard');
        }

        return view('auth.verify-email');
    }

    public function verifyEmail(Request $request)
    {
        $userId = $request->route('id');
        $hash = $request->route('hash');

        Log::info('Email verification attempt', [
            'user_id' => $userId,
            'hash' => $hash,
            'full_url' => $request->fullUrl()
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

        // Check if user implements MustVerifyEmail
        if (!($user instanceof MustVerifyEmail)) {
            Log::error('Email verification failed - user does not implement MustVerifyEmail', [
                'user_id' => $user->id
            ]);
            return redirect()->route('login')->withErrors(['email' => 'Invalid verification request.']);
        }

        // Verify the hash matches
        $expectedHash = sha1($user->getEmailForVerification());
        if (!hash_equals($expectedHash, $hash)) {
            Log::error('Email verification failed - hash mismatch', [
                'user_id' => $user->id,
                'expected_hash' => $expectedHash,
                'provided_hash' => $hash
            ]);
            return redirect()->route('login')->withErrors(['email' => 'Invalid verification link.']);
        }

        // Check if email is already verified
        if ($user->hasVerifiedEmail()) {
            Log::info('Email already verified', [
                'user_id' => $user->id,
                'role' => $user->role
            ]);
            
            // Log the user in if they're not already logged in
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
                'role' => $user->role
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

        return $this->redirectToDashboard($user)->with('status', 'Email verified successfully!');
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

    public function resendVerification(Request $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->route('dashboard');
        }

        try {
            $request->user()->sendEmailVerificationNotification();
            Log::info('Verification email resent', [
                'user_id' => $request->user()->id,
                'role' => $request->user()->role
            ]);
            return back()->with('status', 'Verification link sent!');
        } catch (Exception $e) {
            Log::error('Failed to resend verification email', [
                'user_id' => $request->user()->id,
                'role' => $request->user()->role,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->withErrors(['email' => 'Failed to send verification email. Please try again.']);
        }
    }

    public function showForgotPassword()
    {
        return view('auth.forgot-password');
    }

    public function sendResetLink(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        return $status === Password::RESET_LINK_SENT
            ? back()->with(['status' => __($status)])
            : back()->withErrors(['email' => __($status)]);
    }

    public function showResetPassword(string $token)
    {
        return view('auth.reset-password', ['token' => $token]);
    }

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

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('login')->with('status', __($status))
            : back()->withErrors(['email' => [__($status)]]);
    }

    public function dashboard()
    {
        return view('dashboard');
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

        $user = Auth::user();

        // Verify password
        if (!Hash::check($request->password, $user->password)) {
            return back()->withErrors([
                'password' => 'The provided password is incorrect.',
            ]);
        }

        try {
            /** @var \App\Models\User $user */
            $user = Auth::user();
            if (!$user) {
                return back()->withErrors(['deactivation' => 'No authenticated user found.']);
            }

            $user->status = 'deactivated';
            if ($user->save()) {
                Log::info('User account deactivated', [
                    'user_id' => $user->id,
                    'role' => $user->role,
                    'email' => $user->email
                ]);

                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login')->with('status', 'Your account has been deactivated successfully.');
            } else {
                return back()->withErrors(['deactivation' => 'Failed to deactivate account. Please try again.']);
            }
        } catch (Exception $e) {
            Log::error('Account deactivation failed', [
                'user_id' => $user->id ?? 'unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()->withErrors(['deactivation' => 'Failed to deactivate account. Please try again.']);
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
            ])->withInput();
        }

        if ($user->isActive()) {
            return back()->withErrors([
                'email' => 'This account is already active.',
            ])->withInput();
        }

        try {
            // Reactivate the account
            if ($user->reactivate()) {
                Log::info('User account reactivated', [
                    'user_id' => $user->id,
                    'role' => $user->role,
                    'email' => $user->email
                ]);

                return redirect()->route('login')->with('status', 'Your account has been reactivated successfully. You can now log in.');
            } else {
                return back()->withErrors(['reactivation' => 'Failed to reactivate account. Please try again.']);
            }
        } catch (Exception $e) {
            Log::error('Account reactivation failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()->withErrors(['reactivation' => 'Failed to reactivate account. Please try again.']);
        }
    }

    // Test email method for development
    public function testEmail()
    {
        try {
            Mail::raw('This is a test email from Laravel', function ($message) {
                $message->to('test@example.com')
                       ->subject('Test Email');
            });
            
            return response()->json(['message' => 'Test email sent successfully']);
        } catch (Exception $e) {
            Log::error('Test email failed', ['error' => $e->getMessage()]);
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}