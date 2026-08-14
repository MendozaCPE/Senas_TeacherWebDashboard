<?php

namespace App\Http\Controllers;

use App\Models\School;
use App\Models\Teacher;
use App\Models\User;
use App\Services\AuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{
    protected AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Redirect the user to the Google OAuth consent screen.
     * Captures the intent parameter ('login' vs 'register').
     */
    public function redirect(Request $request)
    {
        $intent = $request->query('intent', 'login');
        session(['google_auth_intent' => $intent]);

        return Socialite::driver('google')->redirect();
    }

    /**
     * Handle the callback from Google after authentication.
     */
    public function callback()
    {
        $intent = session()->pull('google_auth_intent', 'login');

        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (\Throwable $e) {
            return redirect()->route('login')
                ->withErrors(['email' => 'Google authentication failed or was cancelled. Please try again.']);
        }

        $email = $googleUser->getEmail();

        // 1. Domain restriction check
        if (!$this->authService->isEmailAllowed($email)) {
            return redirect()->route('login')
                ->withErrors(['email' => $this->authService->unauthorizedMessage()]);
        }

        $user = User::where('email', $email)->first();

        // 2. Flow differentiation based on intent
        if ($intent === 'login') {
            // User clicked Google Sign-In on the Login page
            if (!$user) {
                return redirect()->route('login')
                    ->withErrors([
                        'email' => 'No registered account found for this Google email address. Please sign up first to create your teacher account.'
                    ]);
            }

            // Update google_id or profile picture if missing
            $updates = [];
            if (!$user->google_id) {
                $updates['google_id'] = $googleUser->getId();
            }
            if (!$user->profile_photo && $googleUser->getAvatar()) {
                $updates['profile_photo'] = $googleUser->getAvatar();
            }
            if (!empty($updates)) {
                $user->update($updates);
            }

            Auth::login($user, true);
            request()->session()->regenerate();

            // Audit log: Google login
            \App\Models\AuditLog::record(
                action:      'login',
                module:      'Authentication',
                description: "{$user->name} logged in via Google.",
                userId:      $user->id,
                userName:    $user->name,
                userRole:    $user->role,
            );

            if ($user->role === 'admin') {
                return redirect()->route('admin.dashboard');
            }
            return redirect()->intended(route('dashboard'));
        } else {
            // ── REGISTER intent: Google sign-up ──
            if ($user) {
                return redirect()->route('login')
                    ->with('status', 'An account with this email address already exists. Please log in using your Google account or password.');
            }

            // Generate 6-digit OTP
            $otp = sprintf('%06d', mt_rand(100000, 999999));

            // Store pending Google registration in session (same structure as manual)
            session([
                'pending_registration' => [
                    'name'        => $googleUser->getName() ?? strtolower(strtok($email, '@')),
                    'email'       => $email,
                    'password'    => bcrypt(Str::random(32)), // Placeholder; Google users won't use it
                    'school_id'   => School::first()?->id,   // Default school; user can update in Settings
                    'google_id'   => $googleUser->getId(),
                    'profile_photo' => $googleUser->getAvatar(),
                    'is_google'   => true,
                    'otp'         => $otp,
                    'expires_at'  => now()->addMinutes(15)->timestamp,
                ]
            ]);

            // Send OTP to the Google account email
            try {
                Notification::route('mail', $email)
                    ->notify(new \App\Notifications\RegistrationOtpNotification($otp));
            } catch (\Throwable $e) {
                Log::error('Google signup OTP email failed: ' . $e->getMessage());
            }

            return redirect()->route('register.show-verify-otp');
        }
    }
}
