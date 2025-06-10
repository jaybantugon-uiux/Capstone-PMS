<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @param  string  ...$roles
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        // Check if the user is authenticated
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Check if the user's role is among the allowed roles
        if (!in_array($user->role, $roles)) {
            return $this->redirectToDashboard($user);
        }

        // Check if the account is deactivated
        if ($user->isDeactivated()) {
            Auth::logout();
            return redirect()->route('login')
                ->withErrors(['email' => 'Your account has been deactivated.']);
        }

        return $next($request);
    }

    /**
     * Redirect user to their role-specific dashboard
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\RedirectResponse
     */
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
}