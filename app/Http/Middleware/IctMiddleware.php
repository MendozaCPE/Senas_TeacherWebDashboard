<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * IctMiddleware
 *
 * Guards routes exclusively for the `ict` role (School ICT Coordinator).
 * All other authenticated roles are bounced to their own portals.
 */
class IctMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        $role = Auth::user()->role;

        if ($role === 'ict') {
            return $next($request);
        }

        if ($role === 'admin') {
            return redirect()->route('admin.dashboard');
        }

        if ($role === 'teacher_leader') {
            return redirect()->route('teacher-leader.dashboard');
        }

        if ($role === 'teacher') {
            return redirect()->route('dashboard');
        }

        abort(403, 'Access denied.');
    }
}
