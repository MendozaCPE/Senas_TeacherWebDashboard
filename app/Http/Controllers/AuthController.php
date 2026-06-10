<?php

namespace App\Http\Controllers;

use App\Models\School;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /** Show login form */
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect('/');
        }
        return view('auth.login');
    }

    /** Process login */
    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        $remember = $request->boolean('remember');

        if (Auth::attempt(['email' => $request->email, 'password' => $request->password], $remember)) {
            $request->session()->regenerate();
            return redirect()->intended(route('dashboard'));
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    /** Show registration form */
    public function showRegister()
    {
        $schools = School::orderBy('name')->get();
        return view('auth.register', compact('schools'));
    }

    /** Process registration */
    public function register(Request $request)
    {
        $request->validate([
            'name'      => 'required|string|max:255',
            'email'     => 'required|email|unique:users,email',
            'password'  => 'required|min:8|confirmed',
            'school_id' => 'required|exists:schools,id',
            'terms'     => 'accepted',
        ], [
            'terms.accepted' => 'You must agree to the Terms and Conditions.',
        ]);

        // Auto-generate unique username from email
        $base = strtolower(strtok($request->email, '@'));
        $username = $base;
        $i = 1;
        while (User::where('username', $username)->exists()) {
            $username = $base . $i++;
        }

        $user = User::create([
            'username' => $username,
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'role'     => 'teacher',
            'status'   => 'active',
        ]);

        // Split full name into first / last
        $parts     = explode(' ', trim($request->name), 2);
        $firstName = $parts[0];
        $lastName  = $parts[1] ?? '';

        Teacher::create([
            'user_id'        => $user->id,
            'school_id'      => $request->school_id,
            'first_name'     => $firstName,
            'last_name'      => $lastName,
            'specialization' => 'Regular',
        ]);

        Auth::login($user);

        return redirect(route('dashboard'));
    }

    /** Show terms & conditions */
    public function showTerms()
    {
        return view('auth.terms');
    }

    /** Logout */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }
}
