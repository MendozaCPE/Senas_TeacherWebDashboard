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

    Route::post('/student/update-profile-picture', [StudentAuthController::class, 'updateProfilePicture']);

     // Notification routes
    Route::get('/student/notifications', [StudentAuthController::class, 'getNotifications']);
    Route::post('/student/notifications/save', [StudentAuthController::class, 'saveNotifications']);
    Route::post('/student/notifications/{id}/read', [StudentAuthController::class, 'markNotificationRead']);
    Route::post('/student/notifications/read-all', [StudentAuthController::class, 'markAllNotificationsRead']);

    // Student Lessons
    Route::get('/student/all-lessons', [StudentAuthController::class, 'getAllLessons']);
    Route::get('/student/lessons', [StudentAuthController::class, 'getLessons']);

    // Lesson viewing routes
    Route::get('/student/lesson/{lessonId}', [StudentAuthController::class, 'getLessonById']);
    Route::post('/student/lesson/{lessonId}/progress', [StudentAuthController::class, 'updateLessonProgress']);
    Route::post('/student/lesson/{lessonId}/quiz/submit', [StudentAuthController::class, 'submitQuizAttempt']);
    Route::post('/student/lesson/{lessonId}/slide-xp', [StudentAuthController::class, 'awardSlideXp']);
    Route::get('/student/lesson/{lessonId}/attempts', [StudentAuthController::class, 'getAttempts']);
    
    Route::get('/student/learning-path/lessons', [StudentAuthController::class, 'getRecommendedLessons']);
    
    // Leaderboard
    Route::get('/student/lesson/{lessonId}/leaderboard', [StudentAuthController::class, 'getLessonLeaderboard']);

    // ─── GESTURE PERFORMANCE ROUTES ──────────────────────────────
    Route::post('/student/gesture-performance', [StudentAuthController::class, 'saveGesturePerformance']);
    Route::get('/student/gesture-performance', [StudentAuthController::class, 'getGesturePerformance']);
    Route::get('/student/struggling-letters', [StudentAuthController::class, 'getStrugglingLetters']);

    // ─── GESTURE PROGRESS ROUTES ─────────────────────────────────
    Route::get('/student/gesture-progress', [StudentAuthController::class, 'getGestureProgress']);
    Route::post('/student/award-module-xp', [StudentAuthController::class, 'awardModuleXp']);

    Route::post('/student/award-custom-xp', [StudentAuthController::class, 'awardCustomXp']);
    
    // ─── MODULE CHECKPOINT QUIZ ───────────────────────────────────
    Route::get('/student/module/{moduleId}/quiz', [StudentAuthController::class, 'getModuleQuiz']);
    Route::post('/student/module/{moduleId}/quiz/submit', [StudentAuthController::class, 'submitModuleQuiz']);

    Route::get('/student/weak-signs', [StudentAuthController::class, 'getWeakSigns']);
    Route::post('/student/award-challenge-xp', [StudentAuthController::class, 'awardChallengeXp']);

    // ─── STREAK ROUTE ─────────────────────────────────────────────
    // 🔥 ADD THIS LINE - Get student's current streak
    Route::get('/student/streak', [StudentAuthController::class, 'getStreak']);

    // ═══════════════════════════════════════════════════════════════
    // 🎯 PROMOTION ROUTES (for mobile app)
    // ═══════════════════════════════════════════════════════════════
    Route::get('/student/promotion', [StudentAuthController::class, 'checkPromotion']);
    Route::post('/student/promotion/{id}/viewed', [StudentAuthController::class, 'markPromotionViewed']);
    Route::get('/student/promotion/history', [StudentAuthController::class, 'getPromotionHistory']);
    Route::get('/student/promotion/status', [StudentAuthController::class, 'hasPendingPromotion']);
    Route::get('/student/promotion/{id}', [StudentAuthController::class, 'getPromotionDetails']);

    // ─── STUDENT ACHIEVEMENTS ROUTES ──────────────────────────────
    Route::get('/student/achievements', [StudentAuthController::class, 'getAchievements']);
    Route::get('/student/achievements/unlocked', [StudentAuthController::class, 'getUnlockedAchievements']);
    Route::post('/student/achievements/check', [StudentAuthController::class, 'checkAchievements']);

    Route::get('/student/daily-challenge', [StudentAuthController::class, 'getDailyChallenge']);
Route::post('/student/daily-challenge/progress', [StudentAuthController::class, 'updateChallengeProgress']);
Route::post('/student/daily-challenge/track-time', [StudentAuthController::class, 'trackChallengeTime']);
Route::get('/student/settings', [StudentAuthController::class, 'getSettings']);
Route::post('/student/settings', [StudentAuthController::class, 'updateSettings']);

Route::get('/student/mastery', [StudentAuthController::class, 'getMasteryData']);
    Route::post('/student/mastery/update', [StudentAuthController::class, 'updateMasteryAfterPractice']);
    
    // Updated route - replace the existing getRecommendedLessons
    Route::get('/student/adaptive-lessons', [StudentAuthController::class, 'getRecommendedLessons']);

});