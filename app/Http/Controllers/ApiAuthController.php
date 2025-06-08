<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Str;
use App\Models\User;
use App\Notifications\CustomVerifyEmail;
use Exception;

class ApiAuthController extends Controller
{
    /**
     * Register a new user
     */
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
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

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

            // Send verification email
            try {
                $user->notify(new CustomVerifyEmail());
                Log::info('Email verification notification sent via API', [
                    'user_id' => $user->id,
                    'role' => $user->role,
                    'email' => $user->email,
                    'mailer' => config('mail.default')
                ]);
            } catch (Exception $e) {
                Log::error('Failed to send verification email via API', [
                    'user_id' => $user->id,
                    'role' => $user->role,
                    'error' => $e->getMessage()
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'User registered successfully. Please check your email to verify your account.',
                'user' => [
                    'id' => $user->id,
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                    'username' => $user->username,
                    'email' => $user->email,
                    'role' => $user->role,
                    'status' => $user->status,
                    'email_verified_at' => $user->email_verified_at,
                ],
                'needs_verification' => true,
            ], 201);
        } catch (Exception $e) {
            Log::error('User registration failed via API', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Registration failed. Please try again.'
            ], 500);
        }
    }

    /**
     * Login user
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        if (Auth::attempt($request->only('email', 'password'))) {
            $user = Auth::user();
            
            if (!($user instanceof User)) {
                Log::error('User is not an instance of App\Models\User', [
                    'user_class' => get_class($user),
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Internal server error: Invalid user instance'
                ], 500);
            }

            // Check if account is deactivated
            if ($user->isDeactivated()) { 
                Auth::logout();
                return response()->json([
                    'success' => false,
                    'message' => 'Your account has been deactivated. Please contact support or reactivate your account.',
                    'account_deactivated' => true
                ], 403);
            }
            
            // Require email verification
            if (!$user->hasVerifiedEmail()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Email not verified. Please verify your email to log in.',
                    'needs_verification' => true
                ], 403);
            }
            
            $token = $user->createToken('auth_token')->plainTextToken;
            
            Log::info('User logged in via API', [
                'user_id' => $user->id,
                'role' => $user->role
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Login successful',
                'user' => [
                    'id' => $user->id,
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                    'username' => $user->username,
                    'email' => $user->email,
                    'role' => $user->role,
                    'status' => $user->status,
                    'email_verified_at' => $user->email_verified_at,
                ],
                'token' => $token,
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Invalid credentials'
        ], 401);
    }

    /**
     * Verify email
     */
    public function verifyEmail(Request $request)
    {
        $user = User::find($request->route('id'));

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found.'
            ], 404);
        }

        if (!hash_equals(sha1($user->getEmailForVerification()), $request->route('hash'))) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid verification link.'
            ], 403);
        }

        if ($user->hasVerifiedEmail()) {
            $token = $user->createToken('auth_token')->plainTextToken;
            
            return response()->json([
                'success' => true,
                'message' => 'Email already verified.',
                'user' => [
                    'id' => $user->id,
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                    'username' => $user->username,
                    'email' => $user->email,
                    'role' => $user->role,
                    'status' => $user->status,
                    'email_verified_at' => $user->email_verified_at,
                ],
                'token' => $token,
                'dashboard_url' => $this->getDashboardUrl($user->role)
            ]);
        }

        if ($user->markEmailAsVerified()) {
            Log::info('Email verified successfully via API', [
                'user_id' => $user->id,
                'role' => $user->role,
                'email' => $user->email
            ]);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Email verified successfully! You can now access your account.',
            'user' => [
                'id' => $user->id,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'username' => $user->username,
                'email' => $user->email,
                'role' => $user->role,
                'status' => $user->status,
                'email_verified_at' => $user->email_verified_at,
            ],
            'token' => $token,
            'dashboard_url' => $this->getDashboardUrl($user->role)
        ]);
    }

    /**
     * Send email verification notification
     */
    public function sendVerification(Request $request)
    {
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'success' => false,
                'message' => 'Email already verified.'
            ], 400);
        }

        try {
            $user->notify(new CustomVerifyEmail());
            Log::info('Verification email resent via API', [
                'user_id' => $user->id,
                'role' => $user->role,
                'email' => $user->email
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Verification email sent successfully. Please check your inbox.'
            ]);
        } catch (Exception $e) {
            Log::error('Failed to resend verification email via API', [
                'user_id' => $user->id,
                'role' => $user->role,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to send verification email. Please try again.'
            ], 500);
        }
    }

    /**
     * Get user profile
     */
    public function profile(Request $request)
    {
        $user = $request->user();
        return response()->json([
            'success' => true,
            'user' => [
                'id' => $user->id,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'username' => $user->username,
                'email' => $user->email,
                'role' => $user->role,
                'status' => $user->status,
                'email_verified_at' => $user->email_verified_at,
                'deactivated_at' => $user->deactivated_at,
            ]
        ]);
    }

    /**
     * Deactivate user account
     */
    public function deactivateAccount(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'password' => 'required',
            'confirmation' => 'required|in:DEACTIVATE',
        ], [
            'confirmation.in' => 'Please provide "DEACTIVATE" to confirm account deactivation.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();

        if (!Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'The provided password is incorrect.',
                'errors' => [
                    'password' => ['The provided password is incorrect.']
                ]
            ], 422);
        }

        try {
            $user->deactivate();

            Log::info('User account deactivated via API', [
                'user_id' => $user->id,
                'role' => $user->role,
                'email' => $user->email
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Your account has been deactivated successfully. All your access tokens have been revoked.'
            ]);
        } catch (Exception $e) {
            Log::error('Account deactivation failed via API', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to deactivate account. Please try again.'
            ], 500);
        }
    }

    /**
     * Reactivate user account
     */
    public function reactivateAccount(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'The provided credentials are incorrect.'
            ], 422);
        }

        if ($user->isActive()) {
            return response()->json([
                'success' => false,
                'message' => 'This account is already active.'
            ], 422);
        }

        try {
            $user->reactivate();

            Log::info('User account reactivated via API', [
                'user_id' => $user->id,
                'role' => $user->role,
                'email' => $user->email
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Your account has been reactivated successfully. You can now log in.'
            ]);
        } catch (Exception $e) {
            Log::error('Account reactivation failed via API', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to reactivate account. Please try again.'
            ], 500);
        }
    }

    private function getDashboardUrl($role)
    {
        $baseUrl = config('app.url');
        
        switch ($role) {
            case 'admin':
                return $baseUrl . '/admin-dashboard';
            case 'emp':
                return $baseUrl . '/employee-dashboard';
            case 'finance':
                return $baseUrl . '/finance-dashboard';
            case 'pm':
                return $baseUrl . '/pm-dashboard';
            case 'sc':
                return $baseUrl . '/sc-dashboard';
            case 'client':
            default:
                return $baseUrl . '/client-dashboard';
        }
    }

    /**
     * Logout user
     */
    public function logout(Request $request)
    {
        Log::info('User logged out via API', ['user_id' => $request->user()->id]);
        
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully.'
        ]);
    }

    /**
     * Logout from all devices
     */
    public function logoutAll(Request $request)
    {
        Log::info('User logged out from all devices via API', ['user_id' => $request->user()->id]);
        
        $request->user()->tokens()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logged out from all devices successfully.'
        ]);
    }

    /**
     * Send password reset link
     */
    public function sendResetLink(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $status = Password::sendResetLink($request->only('email'));

        if ($status === Password::RESET_LINK_SENT) {
            return response()->json([
                'success' => true,
                'message' => 'Password reset link sent to your email.'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Unable to send password reset link.'
        ], 400);
    }

    /**
     * Reset password
     */
    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

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
            return response()->json([
                'success' => true,
                'message' => 'Password reset successfully.'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Unable to reset password.'
        ], 400);
    }
}