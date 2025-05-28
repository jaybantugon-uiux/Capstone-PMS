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
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $user = User::create([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'username' => $request->username,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);

            Log::info('User created successfully', ['user_id' => $user->id, 'email' => $user->email]);

            Auth::login($user);

            // Test mail configuration first
        try {
    // Send email verification notification
    if (config('queue.default') === 'sync') {
        // If using sync queue, send immediately
        $user->sendEmailVerificationNotification();
        Log::info('Email verification notification sent immediately', ['user_id' => $user->id]);
    } else {
        // If using queue, dispatch the job
        $user->sendEmailVerificationNotification();
        Log::info('Email verification notification queued', ['user_id' => $user->id]);
    }
    
} catch (Exception $e) {
    Log::error('Failed to send verification email', [
        'user_id' => $user->id,
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);
    
    // Still redirect to verification notice but show warning
    return redirect()->route('verification.notice')
        ->with('warning', 'Account created successfully, but there was an issue sending the verification email. Please try resending it.');
}

            return redirect()->route('verification.notice')
                ->with('status', 'Registration successful! Please check your email to verify your account.');

        } catch (Exception $e) {
            Log::error('User registration failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()
                ->withErrors(['general' => 'Registration failed. Please try again.'])
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
            $request->session()->regenerate();
            
            Log::info('User logged in', ['user_id' => Auth::id()]);
            
            // Check if email is verified
            if (!Auth::user()->hasVerifiedEmail()) {
                Log::info('User needs email verification', ['user_id' => Auth::id()]);
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
        if (Auth::user() && Auth::user()->hasVerifiedEmail()) {
            return redirect()->route('dashboard');
        }
        
        return view('auth.verify-email');
    }

    public function verifyEmail(Request $request)
    {
        $user = User::find($request->route('id'));

        if (!$user) {
            Log::error('Email verification failed - user not found', ['user_id' => $request->route('id')]);
            abort(404);
        }

        if (!hash_equals(sha1($user->getEmailForVerification()), $request->route('hash'))) {
            Log::error('Email verification failed - invalid hash', ['user_id' => $user->id]);
            abort(403);
        }

        if ($user->hasVerifiedEmail()) {
            Log::info('Email already verified', ['user_id' => $user->id]);
            return redirect()->route('dashboard')->with('status', 'Email already verified!');
        }

        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
            Log::info('Email verified successfully', ['user_id' => $user->id]);
        }

        return redirect()->route('dashboard')->with('status', 'Email verified successfully!');
    }

    public function resendVerification(Request $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->route('dashboard');
        }

        try {
            $request->user()->sendEmailVerificationNotification();
            Log::info('Verification email resent', ['user_id' => $request->user()->id]);
            return back()->with('status', 'Verification link sent!');
        } catch (Exception $e) {
            Log::error('Failed to resend verification email', [
                'user_id' => $request->user()->id,
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
        Log::info('User logged out', ['user_id' => Auth::id()]);
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }

    // Add this method for testing email configuration
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