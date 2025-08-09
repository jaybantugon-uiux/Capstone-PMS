<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();

        // Check if user has any of the required roles
        if (empty($roles) || in_array($user->role, $roles)) {
            return $next($request);
        }

        // Role-specific redirects for better UX
        switch ($user->role) {
            case 'admin':
                return redirect()->route('admin.dashboard')->with('error', 'Access denied to that section.');
            case 'pm':
                return redirect()->route('pm.dashboard')->with('error', 'Access denied to that section.');
            case 'sc':
                return redirect()->route('sc.dashboard')->with('error', 'Access denied to that section.');
            case 'client':
                return redirect()->route('client.dashboard')->with('error', 'Access denied to that section.');
            case 'emp':
                return redirect()->route('employee.dashboard')->with('error', 'Access denied to that section.');
            case 'finance':
                return redirect()->route('finance.dashboard')->with('error', 'Access denied to that section.');
            default:
                return redirect()->route('dashboard')->with('error', 'Access denied.');
        }
    }
}