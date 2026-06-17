<?php

namespace App\Http\Controllers;

use App\Models\Teacher;
use App\Models\School;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class SettingsController extends Controller
{
    public function index()
    {
        $user    = Auth::user();
        $teacher = $user->teacher;
        $school  = $teacher ? $teacher->school : null;

        return view('settings', compact('user', 'teacher', 'school'));
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
        ]);

        // Update user
        $user->name  = $validated['first_name'] . ' ' . $validated['last_name'];
        $user->email = $validated['email'] ?? $user->email;
        $user->save();

        // Update teacher
        if ($teacher) {
            $teacher->first_name      = $validated['first_name'];
            $teacher->last_name       = $validated['last_name'];
            if (isset($validated['specialization'])) {
                $teacher->specialization = $validated['specialization'];
            }
            $teacher->save();
        }

        return back()->with('success', 'Profile updated successfully.');
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

    public function updatePassword(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'current_password' => 'required',
            'password'         => 'required|min:8|confirmed',
        ]);

        if (!Hash::check($validated['current_password'], $user->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect.'])->withInput();
        }

        $user->password = Hash::make($validated['password']);
        $user->save();

        return back()->with('success', 'Password updated successfully.');
    }
}
