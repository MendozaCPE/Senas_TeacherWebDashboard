<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user    = Auth::user();
        $teacher = $user->teacher;

        // Build a display name: use teacher first_name if available, else user name
        if ($teacher && $teacher->first_name) {
            $displayName = $teacher->first_name . ($teacher->last_name ? ' ' . $teacher->last_name : '');
        } else {
            $displayName = $user->name;
        }

        return view('dashboard', compact('displayName', 'user', 'teacher'));
    }
}
