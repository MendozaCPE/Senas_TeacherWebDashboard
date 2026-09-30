<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * TeacherLeaderMiddleware
 *
 * Guards routes that are exclusively for the teacher_leader role.
 * - Unauthenticated users → login page
 * - Admins             → admin dashboard
 * - Regular teachers   → teacher dashboard
 * - Anyone else        → 403
 * - teacher_leader     → allowed through
 */
class TeacherLeaderMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        $role = Auth::user()->role;

        if ($role === 'teacher_leader') {
            return $next($request);
        }

        if ($role === 'admin') {
            return redirect()->route('admin.dashboard');
        }

        if ($role === 'teacher') {
            return redirect()->route('dashboard');
        }

        abort(403, 'Access denied.');
    }
}
