<?php

namespace App\Http\Controllers;

use App\Models\School;
use App\Models\Teacher;
use App\Models\User;
use App\Services\AuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    protected AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    /** Show login form */
    public function showLogin()
    {
        if (Auth::check()) {
            return $this->redirectByRole(Auth::user());
        }
        return view('auth.login');
    }

    /** Return the correct post-login redirect based on user role */
    private function redirectByRole(\App\Models\User $user)
    {
        if ($user->role === 'admin') {
            return redirect()->route('admin.dashboard');
        }
        return redirect()->route('dashboard');
    }

    /** Lookup teacher name by email for the interactive loading screen */
    public function getTeacherName(Request $request)
    {
        $email = trim($request->query('email', ''));
        if (empty($email)) {
            return response()->json(['name' => null]);
        }

        $user = User::where('email', $email)->first();
        if ($user) {
            return response()->json(['name' => $user->name, 'role' => $user->role]);
        }

        return response()->json(['name' => null]);
    }

    /** Process login */
    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        // Domain restriction check
        if (!$this->authService->isEmailAllowed($request->email)) {
            return back()->withErrors([
                'email' => $this->authService->unauthorizedMessage(),
            ])->onlyInput('email');
        }

        $remember = $request->boolean('remember');

        if (Auth::attempt(['email' => $request->email, 'password' => $request->password], $remember)) {
            $user = Auth::user();

            $request->session()->regenerate();

            // Audit log: successful login
            \App\Models\AuditLog::record(
                action:      'login',
                module:      'Authentication',
                description: "{$user->name} logged in.",
                userId:      $user->id,
                userName:    $user->name,
                userRole:    $user->role,
            );

            return $this->redirectByRole($user);
        }

        // Audit log: failed login attempt
        \App\Models\AuditLog::record(
            action:      'login_failed',
            module:      'Authentication',
            description: "Failed login attempt for email: {$request->email}",
        );

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

    /** Process registration — Sends OTP BEFORE creating account */
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

        // Domain restriction check
        if (!$this->authService->isEmailAllowed($request->email)) {
            return back()->withErrors([
                'email' => $this->authService->unauthorizedMessage(),
            ])->onlyInput('email', 'name', 'school_id');
        }

        // Generate 6-digit OTP
        $otp = sprintf('%06d', mt_rand(100000, 999999));

        // Store pending registration data in session (Expires in 15 mins)
        session([
            'pending_registration' => [
                'name'       => $request->name,
                'email'      => $request->email,
                'password'   => Hash::make($request->password),
                'school_id'  => $request->school_id,
                'otp'        => $otp,
                'expires_at' => now()->addMinutes(15)->timestamp,
            ]
        ]);

        // Send OTP via email
        try {
            \Illuminate\Support\Facades\Notification::route('mail', $request->email)
                ->notify(new \App\Notifications\RegistrationOtpNotification($otp));
        } catch (\Throwable $e) {
            Log::error('OTP email send failed: ' . $e->getMessage());
        }

        return redirect()->route('register.show-verify-otp');
    }

    /** Show OTP verification page */
    public function showVerifyOtp()
    {
        $pending = session('pending_registration');
        if (!$pending) {
            return redirect()->route('register');
        }

        return view('auth.verify-otp', ['email' => $pending['email']]);
    }

    /** Verify OTP and finally create the account in database */
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'otp' => 'required|numeric|digits:6',
        ]);

        $pending = session('pending_registration');
        if (!$pending) {
            return redirect()->route('register')->withErrors(['email' => 'Session expired. Please fill out the registration form again.']);
        }

        if (now()->timestamp > $pending['expires_at']) {
            session()->forget('pending_registration');
            return redirect()->route('register')->withErrors(['email' => 'The verification code has expired. Please register again.']);
        }

        // ── OTP Brute-force protection: max 5 wrong guesses ─────────────────
        $attempts = session('otp_attempts', 0);
        if ($attempts >= 5) {
            session()->forget(['pending_registration', 'otp_attempts']);
            return redirect()->route('register')
                ->withErrors(['email' => 'Too many incorrect attempts. Your verification session has been invalidated. Please register again.']);
        }

        if ($request->otp !== $pending['otp']) {
            session(['otp_attempts' => $attempts + 1]);
            $remaining = 4 - $attempts;
            return back()->withErrors(['otp' => "Invalid verification code. {$remaining} attempt(s) remaining before lockout."]);
        }

        // --- OTP Validated! Now create User and Teacher records ---
        $email = $pending['email'];

        // Auto-generate unique username
        $base = strtolower(strtok($email, '@'));
        $username = $base;
        $i = 1;
        while (User::where('username', $username)->exists()) {
            $username = $base . $i++;
        }

        $user = User::create([
            'username'          => $username,
            'name'              => $pending['name'],
            'email'             => $email,
            'email_verified_at' => now(),
            'password'          => $pending['password'],
            'role'              => 'teacher',
            'status'            => 'active',
            'google_id'         => $pending['google_id'] ?? null,
            'profile_photo'     => $pending['profile_photo'] ?? null,
        ]);

        // Split full name into first / last
        $parts     = explode(' ', trim($pending['name']), 2);
        $firstName = $parts[0];
        $lastName  = $parts[1] ?? '';

        $teacher = Teacher::create([
            'user_id'        => $user->id,
            'school_id'      => $pending['school_id'],
            'first_name'     => $firstName,
            'last_name'      => $lastName,
            'specialization' => 'Regular',
        ]);

        // Give the new teacher their own independent copy of the default
        // curriculum so their account always has starter content. Wrapped
        // in try/catch so a cloning hiccup never blocks account creation.
        try {
            app(\App\Services\LessonTemplateService::class)->cloneTemplatesForTeacher($teacher->id);
        } catch (\Throwable $e) {
            Log::error('Default curriculum cloning failed for new teacher ' . $teacher->id . ': ' . $e->getMessage());
        }

        // Clear pending registration session and attempt counter
        session()->forget(['pending_registration', 'otp_attempts']);

        $isGoogle = $pending['is_google'] ?? false;
        $message  = $isGoogle
            ? 'Your Google account has been verified and your teacher account is now active! You can log in using Google.'
            : 'Email verified and account created successfully! You can now log in.';

        return redirect()->route('login')->with('status', $message);
    }

    /** Resend OTP code */
    public function resendOtp()
    {
        $pending = session('pending_registration');
        if (!$pending) {
            return redirect()->route('register');
        }

        $newOtp = sprintf('%06d', mt_rand(100000, 999999));
        $pending['otp'] = $newOtp;
        $pending['expires_at'] = now()->addMinutes(15)->timestamp;

        session(['pending_registration' => $pending]);
        // Reset attempt counter when a fresh OTP is issued
        session()->forget('otp_attempts');

        try {
            \Illuminate\Support\Facades\Notification::route('mail', $pending['email'])
                ->notify(new \App\Notifications\RegistrationOtpNotification($newOtp));
        } catch (\Throwable $e) {
            Log::error('Resend OTP email failed: ' . $e->getMessage());
        }

        return back()->with('status', 'A new 6-digit verification code has been sent to your email.');
    }

    /** Show the forgot password form */
    public function showForgotPasswordForm()
    {
        return view('auth.forgot-password');
    }

    /** Send the password reset link to the user's email */
    public function sendPasswordResetLink(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        $status = Password::sendResetLink($request->only('email'));

        return $status == Password::RESET_LINK_SENT
            ? back()->with('status', __($status))
            : back()->withErrors(['email' => __($status)]);
    }

    /** Show the password reset form */
    public function showResetPasswordForm($token, Request $request)
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->query('email'),
        ]);
    }

    /** Reset the user's password */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email|exists:users,email',
            'password' => 'required|min:8|confirmed',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->password = Hash::make($password);
                $user->setRememberToken(Str::random(60));
                $user->save();
                event(new PasswordReset($user));
                Auth::login($user);
            }
        );

        if ($status == Password::PASSWORD_RESET) {
            $dest = Auth::user()?->role === 'admin'
                ? route('admin.dashboard')
                : route('dashboard');
            return redirect($dest)->with('status', __($status));
        }

        return back()->withErrors(['email' => __($status)]);
    }

    /** Show terms & conditions */
    public function showTerms()
    {
        return view('auth.terms');
    }

    /** Logout */
    public function logout(Request $request)
    {
        $user = Auth::user();

        // Audit log: logout (before session is destroyed)
        if ($user) {
            \App\Models\AuditLog::record(
                action:      'logout',
                module:      'Authentication',
                description: "{$user->name} logged out.",
                userId:      $user->id,
                userName:    $user->name,
                userRole:    $user->role,
            );
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/')
            ->withHeaders([
                'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
                'Pragma'        => 'no-cache',
                'Expires'       => 'Sat, 01 Jan 2000 00:00:00 GMT',
            ]);
    }
}