<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Exception;

class AccountService
{
    /**
     * Deactivate a user account with proper validation and logging
     *
     * @param User $user
     * @param string $password
     * @param string $confirmation
     * @return array
     * @throws ValidationException
     */
    public function deactivateAccount(User $user, string $password, string $confirmation): array
    {
        // Validate confirmation text
        if ($confirmation !== 'DEACTIVATE') {
            throw ValidationException::withMessages([
                'confirmation' => ['Please type "DEACTIVATE" to confirm account deactivation.']
            ]);
        }

        // Validate password
        if (!Hash::check($password, $user->password)) {
            throw ValidationException::withMessages([
                'password' => ['The provided password is incorrect.']
            ]);
        }

        // Check if already deactivated
        if ($user->isDeactivated()) {
            throw ValidationException::withMessages([
                'account' => ['Account is already deactivated.']
            ]);
        }

        try {
            // Deactivate the account
            $success = $user->deactivate();

            if ($success) {
                Log::info('Account deactivated successfully', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'role' => $user->role,
                    'deactivated_at' => now()
                ]);

                return [
                    'success' => true,
                    'message' => 'Your account has been deactivated successfully. All access tokens have been revoked.',
                    'data' => [
                        'user_id' => $user->id,
                        'deactivated_at' => $user->deactivated_at
                    ]
                ];
            } else {
                throw new Exception('Failed to update account status in database');
            }
        } catch (Exception $e) {
            Log::error('Account deactivation failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'message' => 'Failed to deactivate account. Please try again.',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Reactivate a user account with proper validation and logging
     *
     * @param string $email
     * @param string $password
     * @return array
     * @throws ValidationException
     */
    public function reactivateAccount(string $email, string $password): array
    {
        // Find user by email
        $user = User::where('email', $email)->first();

        if (!$user) {
            throw ValidationException::withMessages([
                'email' => ['No account found with this email address.']
            ]);
        }

        // Validate password
        if (!Hash::check($password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.']
            ]);
        }

        // Check if already active
        if ($user->isActive()) {
            throw ValidationException::withMessages([
                'email' => ['This account is already active.']
            ]);
        }

        try {
            // Reactivate the account
            $success = $user->reactivate();

            if ($success) {
                Log::info('Account reactivated successfully', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'role' => $user->role,
                    'reactivated_at' => now()
                ]);

                return [
                    'success' => true,
                    'message' => 'Your account has been reactivated successfully. You can now log in.',
                    'data' => [
                        'user_id' => $user->id,
                        'reactivated_at' => now()
                    ]
                ];
            } else {
                throw new Exception('Failed to update account status in database');
            }
        } catch (Exception $e) {
            Log::error('Account reactivation failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'message' => 'Failed to reactivate account. Please try again.',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get account status information
     *
     * @param User $user
     * @return array
     */
    public function getAccountStatus(User $user): array
    {
        return [
            'user_id' => $user->id,
            'email' => $user->email,
            'status' => $user->status,
            'is_active' => $user->isActive(),
            'is_deactivated' => $user->isDeactivated(),
            'email_verified' => $user->hasVerifiedEmail(),
            'deactivated_at' => $user->deactivated_at,
            'created_at' => $user->created_at,
            'last_login' => $user->updated_at // You might want to add a last_login field
        ];
    }

    /**
     * Check if user can deactivate their account
     *
     * @param User $user
     * @return array
     */
    public function canDeactivateAccount(User $user): array
    {
        $restrictions = [];

        // Check if already deactivated
        if ($user->isDeactivated()) {
            $restrictions[] = 'Account is already deactivated';
        }

        // Check if email is verified (optional restriction)
        if (!$user->hasVerifiedEmail()) {
            $restrictions[] = 'Email must be verified before deactivation';
        }

        // Add any other business logic restrictions here
        // For example, you might not allow certain roles to deactivate
        if ($user->role === 'admin' && User::where('role', 'admin')->where('status', 'active')->count() <= 1) {
            $restrictions[] = 'Cannot deactivate the last active admin account';
        }

        return [
            'can_deactivate' => empty($restrictions),
            'restrictions' => $restrictions
        ];
    }

    /**
     * Get statistics about account status
     *
     * @return array
     */
    public function getAccountStatistics(): array
    {
        return [
            'total_users' => User::count(),
            'active_users' => User::active()->count(),
            'deactivated_users' => User::deactivated()->count(),
            'verified_users' => User::whereNotNull('email_verified_at')->count(),
            'unverified_users' => User::whereNull('email_verified_at')->count(),
            'users_by_role' => User::selectRaw('role, status, COUNT(*) as count')
                ->groupBy('role', 'status')
                ->get()
                ->groupBy('role')
                ->map(function ($roleGroup) {
                    return $roleGroup->keyBy('status')->map(fn($item) => $item->count);
                })
        ];
    }
}