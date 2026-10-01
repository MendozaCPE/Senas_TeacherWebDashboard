<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * GradeLeaderMiddleware
 *
 * Guards routes exclusively for the grade_leader role.
 * - Unauthenticated users → login page
 * - Admins             → admin dashboard
 * - Regular teachers   → teacher dashboard
 * - ict                → ict dashboard
 * - Anyone else        → 403
 * - grade_leader       → allowed through
 */
class GradeLeaderMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        $role = Auth::user()->role;

        if ($role === 'grade_leader') {
            return $next($request);
        }

        if ($role === 'admin') {
            return redirect()->route('admin.dashboard');
        }

        if ($role === 'ict') {
            return redirect()->route('ict.dashboard');
        }

        if ($role === 'teacher') {
            return redirect()->route('dashboard');
        }

        abort(403, 'Access denied.');
    }
}
