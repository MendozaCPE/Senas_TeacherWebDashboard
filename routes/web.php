<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
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

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/students',  [App\Http\Controllers\StudentsController::class, 'index'])->name('students');
    Route::get('/lessons',   [App\Http\Controllers\LessonsController::class, 'index'])->name('lessons');
    Route::get('/analytics', fn() => view('analytics'));
    Route::get('/reports',   fn() => view('reports'));
    Route::get('/settings',  fn() => view('settings'));
});
