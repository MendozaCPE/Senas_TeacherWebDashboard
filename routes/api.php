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

// Protected Routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/student/profile', [StudentAuthController::class, 'profile']);
    Route::post('/student/logout', [StudentAuthController::class, 'logout']);
    Route::post('/student/update-level', [StudentAuthController::class, 'updateLevel']);
    Route::post('/student/save-learning-path', [StudentAuthController::class, 'saveLearningPath']);
    Route::get('/student/learning-path', [StudentAuthController::class, 'getLearningPath']);

    // Student Lessons - using the same controller
    Route::get('/student/lessons', [StudentAuthController::class, 'getLessons']);

    // Lesson viewing routes
    Route::get('/student/lesson/{lessonId}', [StudentAuthController::class, 'getLessonById']);
    Route::post('/student/lesson/{lessonId}/progress', [StudentAuthController::class, 'updateLessonProgress']);
    Route::post('/student/lesson/{lessonId}/quiz/submit', [StudentAuthController::class, 'submitQuizAttempt']);
});
