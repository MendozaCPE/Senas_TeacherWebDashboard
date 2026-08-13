<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TeacherMiddleware
{
    /**
     * Prevent admin accounts from accessing teacher-only pages.
     * Admins are redirected to their own dashboard.
     */
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        if (Auth::user()->role === 'admin') {
            return redirect()->route('admin.dashboard');
        }

        return $next($request);
    }
}
