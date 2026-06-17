<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\SettingsController;
use Illuminate\Support\Facades\Route;

// ── Auth Routes (guests only) ────────────────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/login',    [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login',   [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register',[AuthController::class, 'register']);
});

// Terms & Conditions (accessible to everyone)
Route::get('/terms', [AuthController::class, 'showTerms'])->name('terms');

// ── Logout ───────────────────────────────────────────────────────────────────
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// ── Protected Routes (must be logged in) ─────────────────────────────────────
Route::middleware('auth')->group(function () {
    // Redirect / to /dashboard
    Route::get('/', fn() => redirect()->route('dashboard'));

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Students
    Route::get('/students',         [App\Http\Controllers\StudentsController::class, 'index'])->name('students');
    Route::post('/students',        [App\Http\Controllers\StudentsController::class, 'store'])->name('students.store');
    Route::post('/students/import', [App\Http\Controllers\StudentsController::class, 'import'])->name('students.import');

    // Lessons
    Route::get('/lessons', [App\Http\Controllers\LessonsController::class, 'index'])->name('lessons');

    // Analytics
    Route::get('/analytics',            [AnalyticsController::class, 'index'])->name('analytics');
    Route::get('/analytics/export-pdf', [ReportsController::class, 'exportAnalyticsPdf'])->name('analytics.export-pdf');

    // Reports
    Route::get('/reports',            [ReportsController::class, 'index'])->name('reports');
    Route::get('/reports/export-pdf', [ReportsController::class, 'exportPdf'])->name('reports.export-pdf');

    // Settings
    Route::get('/settings',            [SettingsController::class, 'index'])->name('settings');
    Route::patch('/settings/profile',  [SettingsController::class, 'updateProfile'])->name('settings.profile');
    Route::patch('/settings/school',   [SettingsController::class, 'updateSchool'])->name('settings.school');
    Route::patch('/settings/password', [SettingsController::class, 'updatePassword'])->name('settings.password');
});
