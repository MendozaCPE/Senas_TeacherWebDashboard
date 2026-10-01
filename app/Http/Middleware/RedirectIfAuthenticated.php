<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RedirectIfAuthenticated
{
    /**
     * Redirect already-authenticated users away from guest-only pages.
     * Admins go to /admin/dashboard, everyone else to /dashboard.
     */
    public function handle(Request $request, Closure $next, ...$guards)
    {
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                $user = Auth::guard($guard)->user();

                if ($user->role === 'admin') {
                    return redirect()->route('admin.dashboard');
                }

                if ($user->role === 'grade_leader') {
                    return redirect()->route('grade-leader.dashboard');
                }

                if ($user->role === 'ict') {
                    return redirect()->route('ict.dashboard');
                }

                return redirect()->route('dashboard');
            }
        }

        return $next($request);
    }
}
