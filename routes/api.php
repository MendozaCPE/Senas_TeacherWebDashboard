<?php

use App\Http\Controllers\StudentAuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Student Auth Routes
Route::post('/student/login', [StudentAuthController::class, 'login']);

// Gesture route
Route::get('/gesture', function () {
    return view('gesture');
});

// Protected Routes (requires authentication)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/student/profile', [StudentAuthController::class, 'profile']);
    Route::post('/student/logout', [StudentAuthController::class, 'logout']);
    Route::post('/student/update-level', [StudentAuthController::class, 'updateLevel']);
    Route::post('/student/save-learning-path', [StudentAuthController::class, 'saveLearningPath']);
    Route::get('/student/learning-path', [StudentAuthController::class, 'getLearningPath']);

    // Student Lessons
        Route::get('/student/all-lessons', [StudentAuthController::class, 'getAllLessons']);
    Route::get('/student/lessons', [StudentAuthController::class, 'getLessons']);

    // Lesson viewing routes
    Route::get('/student/lesson/{lessonId}', [StudentAuthController::class, 'getLessonById']);
    Route::post('/student/lesson/{lessonId}/progress', [StudentAuthController::class, 'updateLessonProgress']);
    Route::post('/student/lesson/{lessonId}/quiz/submit', [StudentAuthController::class, 'submitQuizAttempt']);
    Route::post('/student/lesson/{lessonId}/slide-xp', [StudentAuthController::class, 'awardSlideXp']);
    Route::get('/student/lesson/{lessonId}/attempts', [StudentAuthController::class, 'getAttempts']);
    
    
    // ✅ FIXED: Match the frontend URL pattern
    Route::get('/student/lesson/{lessonId}/leaderboard', [StudentAuthController::class, 'getLessonLeaderboard']);


    // ─── GESTURE PERFORMANCE ROUTES ──────────────────────────────
    Route::post('/student/gesture-performance', [StudentAuthController::class, 'saveGesturePerformance']);
    Route::get('/student/gesture-performance', [StudentAuthController::class, 'getGesturePerformance']);
    Route::get('/student/struggling-letters', [StudentAuthController::class, 'getStrugglingLetters']);

        // ─── GESTURE PROGRESS ROUTES (NEW) ───────────────────────────
    Route::get('/student/gesture-progress', [StudentAuthController::class, 'getGestureProgress']);
     Route::post('/student/award-module-xp', [StudentAuthController::class, 'awardModuleXp']);

    // ─── MODULE CHECKPOINT QUIZ ───────────────────────────────────
    Route::get('/student/module/{moduleId}/quiz', [StudentAuthController::class, 'getModuleQuiz']);
    Route::post('/student/module/{moduleId}/quiz/submit', [StudentAuthController::class, 'submitModuleQuiz']);
   
});