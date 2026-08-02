<?php

use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GlobalSearchController;
use App\Http\Controllers\GoogleAuthController;
use App\Http\Controllers\LessonsController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\SettingsController;
use Illuminate\Support\Facades\Route;

Route::post('/lessons/upload-media-test', function (Request $request) {
    return response()->json(['message' => 'Upload route is working!']);
})->name('lessons.upload-media-test');
// ── Auth Routes (guests only) ────────────────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/forgot-password', [AuthController::class, 'showForgotPasswordForm'])->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'sendPasswordResetLink'])->name('password.email');
    Route::get('/reset-password/{token}', [AuthController::class, 'showResetPasswordForm'])->name('password.reset');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);

    // OTP Account Verification (Before account creation)
    Route::get('/register/verify-otp', [AuthController::class, 'showVerifyOtp'])->name('register.show-verify-otp');
    Route::post('/register/verify-otp', [AuthController::class, 'verifyOtp'])->name('register.verify-otp');
    Route::post('/register/resend-otp', [AuthController::class, 'resendOtp'])->name('register.resend-otp');

    // API Helper for Login Loading State
    Route::get('/api/teacher-name', [AuthController::class, 'getTeacherName'])->name('api.teacher-name');

    // Google OAuth
    Route::get('/auth/google', [GoogleAuthController::class, 'redirect'])->name('auth.google');
    Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback'])->name('auth.google.callback');
});

// Terms & Conditions (accessible to everyone)
Route::get('/terms', [AuthController::class, 'showTerms'])->name('terms');

// ── Logout ───────────────────────────────────────────────────────────────────
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// ── Protected Routes (must be logged in) ─────────────────────────────────────
Route::middleware(['auth', 'no.cache'])->group(function () {
    // Redirect / to /dashboard
    Route::get('/', fn () => redirect()->route('dashboard'));

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Students
    Route::get('/students', [App\Http\Controllers\StudentsController::class, 'index'])->name('students');
    Route::post('/students/filter', [App\Http\Controllers\StudentsController::class, 'applyFilter'])->name('students.filter');
    Route::get('/students/check-lrn', [App\Http\Controllers\StudentsController::class, 'checkLrn'])->name('students.check-lrn');
    Route::post('/students', [App\Http\Controllers\StudentsController::class, 'store'])->name('students.store');
    Route::post('/students/import', [App\Http\Controllers\StudentsController::class, 'import'])->name('students.import');
    Route::post('/students/{id}/promote', [App\Http\Controllers\StudentsController::class, 'promote'])->name('students.promote');
    Route::post('/students/{id}/demote', [App\Http\Controllers\StudentsController::class, 'demote'])->name('students.demote');

    // Lessons - ALL using LessonsController (plural)
    Route::get('/lessons', [LessonsController::class, 'index'])->name('lessons.index');

    Route::get('/lessons/create', [LessonsController::class, 'create'])->name('lessons.create');
    Route::post('/lessons', [LessonsController::class, 'store'])->name('lessons.store');
    Route::post('/lessons/preview', [LessonsController::class, 'preview'])->name('lessons.preview');
    Route::post('/lessons/ai-generate', [LessonsController::class, 'aiGenerate'])->name('lessons.ai-generate');
    Route::post('/lessons/ai-generate-pdf', [LessonsController::class, 'aiGeneratePdf'])->name('lessons.ai-generate-pdf');
    Route::post('/lessons/ai-generate-quiz', [LessonsController::class, 'aiGenerateQuiz'])->name('lessons.ai-generate-quiz');
    Route::post('/lessons/upload-media', [LessonsController::class, 'uploadMedia'])->name('lessons.upload-media');


    // Add these new routes:
    Route::get('/lessons/{lesson}/view', [LessonsController::class, 'view'])->name('lessons.view');
    Route::get('/lessons/{lesson}/preview-modal', [LessonsController::class, 'previewModal'])->name('lessons.preview-modal');
    Route::get('/lessons/{lesson}/edit', [LessonsController::class, 'edit'])->name('lessons.edit');
    Route::put('/lessons/{lesson}', [LessonsController::class, 'update'])->name('lessons.update');

    Route::get('/lessons/{lesson}/publish-config', [LessonsController::class, 'showPublishConfig'])->name('lessons.publish.config');
    Route::post('/lessons/{lesson}/publish', [LessonsController::class, 'publishLesson'])->name('lessons.publish');
    Route::delete('/lessons/{id}', [LessonsController::class, 'destroy'])->name('lessons.destroy');
    Route::get('/lessons/{id}/students', [LessonsController::class, 'manageStudents'])->name('lessons.students');
    Route::post('/lessons/{id}/students', [LessonsController::class, 'updateStudents'])->name('lessons.students.update');
    // Analytics
    Route::get('/analytics', [AnalyticsController::class, 'index'])->name('analytics');
    Route::post('/analytics/filter', [AnalyticsController::class, 'applyFilter'])->name('analytics.filter');
    Route::get('/analytics/export-pdf', [ReportsController::class, 'exportAnalyticsPdf'])->name('analytics.export-pdf');

    // Reports
    Route::get('/reports', [ReportsController::class, 'index'])->name('reports');
    Route::post('/reports/filter', [ReportsController::class, 'applyFilter'])->name('reports.filter');
    Route::get('/reports/export-pdf', [ReportsController::class, 'exportPdf'])->name('reports.export-pdf');

    // Settings
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings');
    Route::patch('/settings/profile', [SettingsController::class, 'updateProfile'])->name('settings.profile');
    Route::delete('/settings/profile-photo', [SettingsController::class, 'removeProfilePhoto'])->name('settings.profile-photo.remove');
    Route::patch('/settings/school', [SettingsController::class, 'updateSchool'])->name('settings.school');
    Route::patch('/settings/password', [SettingsController::class, 'updatePassword'])->name('settings.password');

    // Global Search Auto-complete API
    Route::get('/api/global-search', [GlobalSearchController::class, 'suggestions'])->name('api.global-search');

    Route::get('/api/gesture-modules/{moduleId}/gestures', function ($moduleId) {
    $module = App\Models\GestureModule::with('gestures')->find($moduleId);
    if (!$module) {
        return response()->json(['gestures' => []]);
    }
    return response()->json([
        'module' => $module,
        'gestures' => $module->gestures->map(function ($g) {
            return [
                'gesture_id' => $g->gesture_id,
                'name' => $g->name,
                'display_name' => $g->display_name ?? $g->name,
            ];
        })
    ]);
})->name('api.gesture-module.gestures');

Route::prefix('lessons/checkpoint-exam')->name('lessons.checkpoint-exam.')->group(function () {
    Route::get('/create', [LessonsController::class, 'createCheckpointExam'])->name('create');
    Route::post('/', [LessonsController::class, 'storeCheckpointExam'])->name('store');
    Route::get('/{id}', [LessonsController::class, 'showCheckpointExam'])->name('show');
    Route::post('/{id}/publish', [LessonsController::class, 'publishCheckpointExam'])->name('publish');
    Route::delete('/{id}', [LessonsController::class, 'destroyCheckpointExam'])->name('destroy');
    Route::get('/available-questions', [LessonsController::class, 'getAvailableExamQuestions'])->name('available-questions');
});

});
