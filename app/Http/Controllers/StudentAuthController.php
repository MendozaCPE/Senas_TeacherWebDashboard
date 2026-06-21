<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\User;
use App\Models\LearningPath; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Laravel\Sanctum\HasApiTokens;


class StudentAuthController extends Controller
{
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'lrn' => 'required|string|size:12',
            'pin' => 'required|string|size:4',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Invalid input', 
                'errors' => $validator->errors()
            ], 422);
        }

        // Find student by LRN
        $student = Student::where('lrn', $request->lrn)->first();

        if (!$student) {
            return response()->json(['message' => 'Student not found'], 404);
        }

        // Verify PIN
        if ($student->pin !== $request->pin) {
            return response()->json(['message' => 'Invalid PIN'], 401);
        }

        // Get user data
        $user = User::find($student->user_id);

        if (!$user) {
            return response()->json(['message' => 'User account not found'], 404);
        }

        // Create token for mobile app
        $token = $user->createToken('mobile-app')->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'username' => $user->username,
                'name' => $user->name,
                'role' => $user->role,
                'student' => [
                    'id' => $student->student_id,
                    'lrn' => $student->lrn,
                    'first_name' => $student->first_name,
                    'last_name' => $student->last_name,
                    'program_type' => $student->program_type,
                    'grade_level' => $student->grade_level,
                    'section' => $student->section,
                    'fsl_mastery_level' => $student->fsl_mastery_level, // ✅ ADD THIS LINE
                ]
            ]
        ]);
    }

    public function profile(Request $request)
    {
        $user = $request->user();
        $student = Student::where('user_id', $user->id)->first();

        return response()->json([
            'user' => $user,
            'student' => [
                'id' => $student->student_id,
                'lrn' => $student->lrn,
                'first_name' => $student->first_name,
                'last_name' => $student->last_name,
                'program_type' => $student->program_type,
                'grade_level' => $student->grade_level,
                'section' => $student->section,
                'fsl_mastery_level' => $student->fsl_mastery_level, // ✅ ADD THIS LINE
            ]
        ]);
    }

    public function updateLevel(Request $request)
    {
        $user = $request->user();
        $student = Student::where('user_id', $user->id)->first();
        
        if (!$student) {
            return response()->json(['message' => 'Student not found'], 404);
        }
        
        $validator = Validator::make($request->all(), [
            'fsl_mastery_level' => 'required|string|in:Beginner,Intermediate,Advanced',
        ]);
        
        if ($validator->fails()) {
            return response()->json(['message' => 'Invalid level'], 422);
        }
        
        $student->fsl_mastery_level = $request->fsl_mastery_level;
        $student->save();
        
        return response()->json([
            'message' => 'Level updated successfully',
            'fsl_mastery_level' => $student->fsl_mastery_level
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logged out successfully']);
    }
    public function saveLearningPath(Request $request)
{
    $user = $request->user();
    $student = Student::where('user_id', $user->id)->first();
    
    if (!$student) {
        return response()->json(['message' => 'Student not found'], 404);
    }
    
    $validator = Validator::make($request->all(), [
        'fsl_level' => 'required|string|in:Beginner,Intermediate,Advanced',
        'learning_goal' => 'required|string|in:Alphabet_Numbers,Greetings,Classroom_Words,Everything',
        'practice_time' => 'required|string|in:5_10_min,15_20_min,30_min,1_hour_plus',
    ]);
    
    if ($validator->fails()) {
        return response()->json(['message' => 'Invalid data', 'errors' => $validator->errors()], 422);
    }
    
    // ✅ Use student_id since that's the primary key
    $learningPath = LearningPath::updateOrCreate(
        ['student_id' => $student->student_id],  // ← Use student_id
        [
            'fsl_level' => $request->fsl_level,
            'learning_goal' => $request->learning_goal,
            'practice_time' => $request->practice_time,
            'is_completed' => true,
            'completed_at' => now()
        ]
    );
    
    // Also update the student's fsl_mastery_level
    $student->fsl_mastery_level = $request->fsl_level;
    $student->save();
    
    return response()->json([
        'message' => 'Learning path saved successfully',
        'learning_path' => $learningPath
    ]);
}
public function getLearningPath(Request $request)
{
    try {
        $user = $request->user();
        $student = Student::where('user_id', $user->id)->first();
        
        if (!$student) {
            return response()->json(['message' => 'Student not found'], 404);
        }
        
        $learningPath = LearningPath::where('student_id', $student->student_id)->first();
        
        return response()->json([
            'learning_path' => $learningPath,
            'student_level' => $student->fsl_mastery_level
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'message' => 'Error fetching learning path',
            'error' => $e->getMessage()
        ], 500);
    }
}
}