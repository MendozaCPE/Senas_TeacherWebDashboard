<?php

use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LessonsController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\SettingsController;
use Illuminate\Support\Facades\Route;

// ── Auth Routes (guests only) ────────────────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

// Terms & Conditions (accessible to everyone)
Route::get('/terms', [AuthController::class, 'showTerms'])->name('terms');

// ── Logout ───────────────────────────────────────────────────────────────────
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// ── Protected Routes (must be logged in) ─────────────────────────────────────
Route::middleware('auth')->group(function () {
    // Redirect / to /dashboard
    Route::get('/', fn () => redirect()->route('dashboard'));

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Students
    Route::get('/students', [App\Http\Controllers\StudentsController::class, 'index'])->name('students');
    Route::get('/students/check-lrn', [App\Http\Controllers\StudentsController::class, 'checkLrn'])->name('students.check-lrn');
    Route::post('/students', [App\Http\Controllers\StudentsController::class, 'store'])->name('students.store');
    Route::post('/students/import', [App\Http\Controllers\StudentsController::class, 'import'])->name('students.import');

    // Lessons - ALL using LessonsController (plural)
    Route::get('/lessons', [LessonsController::class, 'index'])->name('lessons.index');

    Route::get('/lessons/create', [LessonsController::class, 'create'])->name('lessons.create');
    Route::post('/lessons', [LessonsController::class, 'store'])->name('lessons.store');
    Route::post('/lessons/preview', [LessonsController::class, 'preview'])->name('lessons.preview');
    Route::post('/lessons/ai-generate', [LessonsController::class, 'aiGenerate'])->name('lessons.ai-generate');
    Route::post('/lessons/ai-generate-pdf', [LessonsController::class, 'aiGeneratePdf'])->name('lessons.ai-generate-pdf');
    Route::post('/lessons/upload-media', [LessonsController::class, 'uploadMedia'])->name('lessons.upload-media');


    // Add these new routes:
    Route::get('/lessons/{lesson}/view', [LessonsController::class, 'view'])->name('lessons.view');
    Route::get('/lessons/{lesson}/edit', [LessonsController::class, 'edit'])->name('lessons.edit');
    Route::put('/lessons/{lesson}', [LessonsController::class, 'update'])->name('lessons.update');

    Route::get('/lessons/{id}/publish-config', [LessonsController::class, 'showPublishConfig'])->name('lessons.publish.config');
    Route::post('/lessons/{id}/publish', [LessonsController::class, 'publishLesson'])->name('lessons.publish');
    Route::delete('/lessons/{id}', [LessonsController::class, 'destroy'])->name('lessons.destroy');
    Route::get('/lessons/{id}/students', [LessonsController::class, 'manageStudents'])->name('lessons.students');
    Route::post('/lessons/{id}/students', [LessonsController::class, 'updateStudents'])->name('lessons.students.update');
    // Analytics
    Route::get('/analytics', [AnalyticsController::class, 'index'])->name('analytics');
    Route::get('/analytics/export-pdf', [ReportsController::class, 'exportAnalyticsPdf'])->name('analytics.export-pdf');

    // Reports
    Route::get('/reports', [ReportsController::class, 'index'])->name('reports');
    Route::get('/reports/export-pdf', [ReportsController::class, 'exportPdf'])->name('reports.export-pdf');

    // Settings
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings');
    Route::patch('/settings/profile', [SettingsController::class, 'updateProfile'])->name('settings.profile');
    Route::patch('/settings/school', [SettingsController::class, 'updateSchool'])->name('settings.school');
    Route::patch('/settings/password', [SettingsController::class, 'updatePassword'])->name('settings.password');
});
