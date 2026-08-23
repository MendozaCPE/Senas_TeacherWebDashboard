<?php

namespace App\Http\Controllers;

use App\Models\Teacher;
use App\Models\School;
use App\Models\TeacherRating;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class SettingsController extends Controller
{
    public function index()
    {
        $user          = Auth::user();
        $teacher       = $user->teacher;
        $school        = $teacher ? $teacher->school : null;
        $teacherRating = $teacher ? TeacherRating::where('teacher_id', $teacher->id)->first() : null;

        return view('settings', compact('user', 'teacher', 'school', 'teacherRating'));
    }

    public function updateProfile(Request $request)
    {
        $user    = Auth::user();
        $teacher = $user->teacher;

        $validated = $request->validate([
            'first_name'     => 'required|string|max:100',
            'last_name'      => 'required|string|max:100',
            'email'          => 'nullable|email|max:255|unique:users,email,' . $user->id,
            'specialization' => 'sometimes|in:SNED,Regular',
            'profile_photo'  => 'sometimes|nullable|file|mimes:jpg,jpeg,png,gif,webp|max:5120',
        ]);

        // Handle profile photo upload
        if ($request->hasFile('profile_photo') && $request->file('profile_photo')->isValid()) {
            // Only delete from disk if old photo is a local storage file (not a Google URL)
            if ($user->profile_photo && !str_starts_with($user->profile_photo, 'http')) {
                \Storage::disk('public')->delete($user->profile_photo);
            }

            $file     = $request->file('profile_photo');
            $safeName = preg_replace('/[^a-zA-Z0-9._-]/', '_', $file->getClientOriginalName());
            $filename = 'profile_' . $user->id . '_' . time() . '_' . $safeName;
            $path     = $file->storeAs('profile_photos', $filename, 'public');

            // Explicitly update and save only the photo field first
            $user->profile_photo = $path;
        }

        // Update user name and email
        $user->name  = trim($validated['first_name'] . ' ' . $validated['last_name']);
        if (!empty($validated['email'])) {
            $user->email = $validated['email'];
        }
        $user->save();

        // Update teacher record
        if ($teacher) {
            $teacher->first_name = $validated['first_name'];
            $teacher->last_name  = $validated['last_name'];
            if (isset($validated['specialization'])) {
                $teacher->specialization = $validated['specialization'];
            }
            $teacher->save();
        }

        return back()->with('success', 'Profile updated successfully.');
    }

    /**
     * Remove the profile photo and revert to the generated avatar.
     * Note: Google OAuth avatars are URLs, not stored files, so just clear the field.
     */
    public function removeProfilePhoto(Request $request)
    {
        $user = Auth::user();
        if ($user->profile_photo) {
            // Only delete from disk if it's a local storage file, not a URL
            if (!str_starts_with($user->profile_photo, 'http')) {
                \Storage::disk('public')->delete($user->profile_photo);
            }
            $user->profile_photo = null;
            $user->save();
        }
        return back()->with('success', 'Profile photo removed.');
    }

    public function updateSchool(Request $request)
    {
        $user    = Auth::user();
        $teacher = $user->teacher;

        if (!$teacher) {
            return back()->with('error', 'Teacher record not found.');
        }

        $validated = $request->validate([
            'school_name'    => 'required|string|max:255',
            'school_address' => 'nullable|string|max:255',
            'region'         => 'nullable|string|max:50',
            'division'       => 'nullable|string|max:100',
        ]);

        $school = $teacher->school;
        if ($school) {
            $school->name    = $validated['school_name'];
            $school->address = $validated['school_address'] ?? $school->address;
            $school->region  = $validated['region'] ?? $school->region;
            $school->division = $validated['division'] ?? $school->division;
            $school->save();
        }

        return back()->with('success', 'Institution details updated.');
    }

    public function updateNotifications(Request $request)
    {
        $teacher = Auth::user()->teacher;

        if (!$teacher) {
            return back()->with('error', 'Teacher record not found.');
        }

        $teacher->notification_prefs = [
            'email_alerts' => $request->boolean('email_alerts'),
        ];
        $teacher->save();

        return back()->with('success', 'Notification preferences saved.');
    }

    public function logoutOthers(Request $request)
    {
        // Invalidate all other sessions by cycling the session ID.
        // Any other browser/device holding the old session will be
        // treated as unauthenticated on their next request.
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Log the user back in with a fresh session on this device
        Auth::login(Auth::user());
        $request->session()->regenerate();

        return back()->with('success', 'All other sessions have been signed out.');
    }

    public function updatePassword(Request $request)    {
        $user = Auth::user();

        // Google-only accounts do not have a password
        if ($user->google_id && empty($user->password)) {
            return back()->with('error', 'Your account uses Google Sign-In. Please manage your password through your Google account settings.');
        }

        $validated = $request->validate([
            'current_password' => 'required',
            'password'         => 'required|min:8|confirmed',
        ]);

        if (!Hash::check($validated['current_password'], $user->password)) {
            return back()->withErrors(['current_password' => 'The current password you entered is incorrect. Please try again.'])->withInput();
        }

        if (Hash::check($validated['password'], $user->password)) {
            return back()->withErrors(['password' => 'Your new password cannot be the same as your current password.'])->withInput();
        }

        $user->password = Hash::make($validated['password']);
        $user->save();

        return back()->with('success', 'Password updated successfully. Please use your new password on your next login.');
    }

    public function submitRating(Request $request)
    {
        $teacher = Auth::user()->teacher;

        if (!$teacher) {
            return back()->with('error', 'Teacher record not found.');
        }

        $validated = $request->validate([
            'rating'   => 'required|integer|between:1,5',
            'feedback' => 'nullable|string|max:1000',
        ]);

        TeacherRating::updateOrCreate(
            ['teacher_id' => $teacher->id],
            [
                'rating'   => $validated['rating'],
                'feedback' => $validated['feedback'] ?? null,
            ]
        );

        return back()->with('success', 'Thank you for your rating! Your feedback means a lot to us.')
                     ->with('active_tab', 'rateus');
    }
}
