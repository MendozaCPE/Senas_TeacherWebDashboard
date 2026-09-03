<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GlobalSearchController;
use App\Http\Controllers\GoogleAuthController;
use App\Http\Controllers\LandingPageController; 
use App\Http\Controllers\Admin\LessonTemplatesController;
use App\Http\Controllers\LessonsController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\ModulesController;
use App\Http\Controllers\NotificationsController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\SettingsController;
 use App\Http\Controllers\TestingController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

// ─── LANDING PAGE ─────────────────────────────────────────────────────────
// Use the LandingPageController for the home page
Route::get('/', [LandingPageController::class, 'index'])->name('home');

// Keep /landing as an alias (also uses the controller)
Route::get('/landing', [LandingPageController::class, 'index']);

// API endpoint for live stats updates
Route::get('/api/landing-stats', [LandingPageController::class, 'getStatsJson']);

Route::post('/lessons/upload-media-test', function (Request $request) {
    return response()->json(['message' => 'Upload route is working!']);
})->name('lessons.upload-media-test');

// ── Auth Routes (guests only) ────────────────────────────────────────────────
Route::middleware(['guest', 'no.cache'])->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');  // Max 5 attempts/min
    Route::get('/forgot-password', [AuthController::class, 'showForgotPasswordForm'])->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'sendPasswordResetLink'])->name('password.email')->middleware('throttle:3,5'); // Max 3 requests per 5 min
    Route::get('/reset-password/{token}', [AuthController::class, 'showResetPasswordForm'])->name('password.reset');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:5,1'); // Max 5 attempts/min

    // OTP Account Verification (Before account creation)
    Route::get('/register/verify-otp', [AuthController::class, 'showVerifyOtp'])->name('register.show-verify-otp');
    Route::post('/register/verify-otp', [AuthController::class, 'verifyOtp'])->name('register.verify-otp')->middleware('throttle:5,5'); // Max 5 guesses per 5 min
    Route::post('/register/resend-otp', [AuthController::class, 'resendOtp'])->name('register.resend-otp')->middleware('throttle:3,10'); // Max 3 resends per 10 min

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

// ── APK Download (public, Android app) ───────────────────────────────────────
Route::get('/download/app', function () {
    $path = storage_path('app/public/downloads/senas_v2.apk');

    if (!file_exists($path)) {
        abort(404, 'APK file not found.');
    }

    return response()->download($path, 'SENAS.apk');
})->name('app.download');

// ── Video Proxy Layer ────────────────────────────────────────────────────────
// SECURITY: Path traversal protection — resolved path must stay within storage/app/public
Route::get('/video-proxy/{path}', function ($path) {
    $baseDir  = realpath(storage_path('app/public'));
    $fullPath = realpath(storage_path('app/public/' . $path));

    // Block traversal attempts like ../../.env
    if (!$fullPath || !$baseDir || !str_starts_with($fullPath, $baseDir . DIRECTORY_SEPARATOR) || !file_exists($fullPath)) {
        abort(404, 'Video path cannot be found.');
    }

    $response = new BinaryFileResponse($fullPath);
    BinaryFileResponse::trustXSendfileTypeHeader();

    $response->headers->set('Access-Control-Allow-Origin', '*');
    $response->headers->set('Access-Control-Allow-Methods', 'GET, HEAD, OPTIONS');
    $response->headers->set('Access-Control-Allow-Headers', '*');
    $response->headers->set('Cache-Control', 'public, max-age=86400');

    return $response;
})->where('path', '.*');

// ── Media Player Route (WebView) ─────────────────────────────────────────────
Route::get('/media-player', function () {
    return view('media-player');
});

// ── Protected Routes (must be logged in as teacher) ──────────────────────────
Route::middleware(['auth', 'no.cache', 'teacher'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

  // Students - SPECIFIC ROUTES FIRST
Route::get('/students', [App\Http\Controllers\StudentsController::class, 'index'])->name('students');
Route::post('/students/filter', [App\Http\Controllers\StudentsController::class, 'applyFilter'])->name('students.filter');
Route::get('/students/check-lrn', [App\Http\Controllers\StudentsController::class, 'checkLrn'])->name('students.check-lrn');
Route::post('/students', [App\Http\Controllers\StudentsController::class, 'store'])->name('students.store');
Route::post('/students/import', [App\Http\Controllers\StudentsController::class, 'import'])->name('students.import');

// 🔥 NEW ROUTE - MUST COME BEFORE THE WILDCARD
Route::get('/students/lessons-for-new-student', [App\Http\Controllers\StudentsController::class, 'getLessonsForNewStudent'])->name('students.lessons-for-new-student');

// 🔥 LESSON ASSIGNMENT ROUTES - BEFORE THE WILDCARD
Route::get('/students/{id}/available-lessons', [App\Http\Controllers\StudentsController::class, 'getAvailableLessons'])->name('students.available-lessons');
Route::post('/students/{id}/assign-lessons', [App\Http\Controllers\StudentsController::class, 'assignLessons'])->name('students.assign-lessons');

// ⚠️ WILDCARD ROUTES - MUST COME LAST
Route::get('/students/{id}', [App\Http\Controllers\StudentsController::class, 'show'])->name('students.show');
Route::put('/students/{id}', [App\Http\Controllers\StudentsController::class, 'update'])->name('students.update');
Route::post('/students/{id}/promote', [App\Http\Controllers\StudentsController::class, 'promote'])->name('students.promote');
Route::post('/students/{id}/demote', [App\Http\Controllers\StudentsController::class, 'demote'])->name('students.demote');
Route::post('/students/{id}/enroll', [App\Http\Controllers\StudentsController::class, 'enroll'])->name('students.enroll');
Route::post('/students/{id}/unenroll', [App\Http\Controllers\StudentsController::class, 'unenroll'])->name('students.unenroll');

// Lessons - ALL using LessonsController (plural)
    Route::get('/lessons', [LessonsController::class, 'index'])->name('lessons.index');

    Route::get('/lessons/create', [LessonsController::class, 'create'])->name('lessons.create');
    Route::post('/lessons', [LessonsController::class, 'store'])->name('lessons.store');
    Route::post('/lessons/preview', [LessonsController::class, 'preview'])->name('lessons.preview');
    Route::post('/lessons/ai-generate', [LessonsController::class, 'aiGenerate'])->name('lessons.ai-generate');
    Route::post('/lessons/ai-generate-pdf', [LessonsController::class, 'aiGeneratePdf'])->name('lessons.ai-generate-pdf');
    Route::post('/lessons/ai-generate-quiz', [LessonsController::class, 'aiGenerateQuiz'])->name('lessons.ai-generate-quiz');
    Route::post('/lessons/upload-media', [LessonsController::class, 'uploadMedia'])->name('lessons.upload-media');
    Route::post('/lessons/reorder', [LessonsController::class, 'reorder'])->name('lessons.reorder');

    // Lesson management
    Route::get('/lessons/{lesson}/view', [LessonsController::class, 'view'])->name('lessons.view');
    Route::get('/lessons/{lesson}/preview-modal', [LessonsController::class, 'previewModal'])->name('lessons.preview-modal');
    Route::get('/lessons/{lesson}/edit', [LessonsController::class, 'edit'])->name('lessons.edit');
    Route::put('/lessons/{lesson}', [LessonsController::class, 'update'])->name('lessons.update');

    Route::get('/lessons/{lesson}/publish-config', [LessonsController::class, 'showPublishConfig'])->name('lessons.publish.config');
    Route::post('/lessons/{lesson}/publish', [LessonsController::class, 'publishLesson'])->name('lessons.publish');
    
    Route::post('/lessons/{id}/soft-delete', [LessonsController::class, 'softDelete'])->name('lessons.soft-delete');
    Route::post('/lessons/{id}/hard-delete', [LessonsController::class, 'hardDelete'])->name('lessons.hard-delete');
    Route::post('/lessons/{id}/restore', [LessonsController::class, 'restore'])->name('lessons.restore');

    Route::delete('/lessons/{id}', [LessonsController::class, 'destroy'])->name('lessons.destroy');
    Route::get('/lessons/{id}/students', [LessonsController::class, 'manageStudents'])->name('lessons.students');
    Route::post('/lessons/{id}/students', [LessonsController::class, 'updateStudents'])->name('lessons.students.update');

    Route::get('/lessons/media-library', [LessonsController::class, 'mediaLibraryFolders'])
        ->name('lessons.media-library.folders');
 
    Route::get('/lessons/media-library/{folder}', [LessonsController::class, 'mediaLibraryFiles'])
        ->name('lessons.media-library.files');

    Route::get('/lessons/my-uploads', [LessonsController::class, 'mediaLibraryMyUploads'])
        ->name('lessons.media-library.my-uploads');

    // Media
    Route::get('/media', [MediaController::class, 'index'])->name('media.index');
    Route::post('/media/upload', [MediaController::class, 'upload'])->name('media.upload');
    Route::put('/media/{id}', [MediaController::class, 'update'])->name('media.update');
    Route::delete('/media/{id}', [MediaController::class, 'destroy'])->name('media.destroy');

    // Analytics
    Route::get('/analytics', [AnalyticsController::class, 'index'])->name('analytics');
    Route::post('/analytics/filter', [AnalyticsController::class, 'applyFilter'])->name('analytics.filter');
    Route::get('/analytics/export-pdf', [ReportsController::class, 'exportAnalyticsPdf'])->name('analytics.export-pdf');
    Route::post('/analytics/export-pdf', [ReportsController::class, 'exportAnalyticsPdf'])->name('analytics.export-pdf.post');

    // Reports
    Route::get('/reports', [ReportsController::class, 'index'])->name('reports');
    Route::post('/reports/filter', [ReportsController::class, 'applyFilter'])->name('reports.filter');
    Route::get('/reports/export-pdf', [ReportsController::class, 'exportPdf'])->name('reports.export-pdf');
    Route::post('/reports/export-pdf', [ReportsController::class, 'exportPdf'])->name('reports.export-pdf.post');

    // Teacher Help-Request (Student Reports) API
    Route::get('/reports/help-requests/{studentId}', [ReportsController::class, 'studentHelpRequests'])->name('reports.help-requests.student');
    Route::get('/reports/help-requests/{id}/detail', [ReportsController::class, 'getTeacherHelpRequest'])->name('reports.help-requests.show');
    Route::post('/reports/help-requests/{id}/review', [ReportsController::class, 'teacherReviewReport'])->name('reports.help-requests.review');
    Route::post('/reports/help-requests/{id}/escalate', [ReportsController::class, 'escalateReport'])->name('reports.help-requests.escalate');

    // Settings
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings');
    Route::patch('/settings/profile', [SettingsController::class, 'updateProfile'])->name('settings.profile');
    Route::delete('/settings/profile-photo', [SettingsController::class, 'removeProfilePhoto'])->name('settings.profile-photo.remove');
    Route::patch('/settings/school', [SettingsController::class, 'updateSchool'])->name('settings.school');
    Route::patch('/settings/password', [SettingsController::class, 'updatePassword'])->name('settings.password');
    Route::patch('/settings/notifications', [SettingsController::class, 'updateNotifications'])->name('settings.notifications');
    Route::post('/settings/logout-others', [SettingsController::class, 'logoutOthers'])->name('settings.logout-others');
    Route::post('/settings/rating', [SettingsController::class, 'submitRating'])->name('settings.rating');


    // ── Teacher Notifications ────────────────────────────────────────────────
    Route::get('/notifications', [NotificationsController::class, 'index'])->name('notifications.index');
    Route::get('/api/notifications/latest', [NotificationsController::class, 'latest'])->name('notifications.latest');
    Route::get('/api/notifications/unread-count', [NotificationsController::class, 'unreadCount'])->name('notifications.unread-count');
    Route::post('/notifications/{id}/read', [NotificationsController::class, 'markRead'])->name('notifications.read');
    Route::post('/notifications/{id}/unread', [NotificationsController::class, 'markUnread'])->name('notifications.unread');
    Route::post('/notifications/read-all', [NotificationsController::class, 'markAllRead'])->name('notifications.read-all');
    Route::post('/notifications/clear-read', [NotificationsController::class, 'clearRead'])->name('notifications.clear-read');
    Route::delete('/notifications/{id}', [NotificationsController::class, 'destroy'])->name('notifications.destroy');

    // Global Search Auto-complete API
    Route::get('/api/global-search', [GlobalSearchController::class, 'suggestions'])->name('api.global-search');

    // API: Get gestures for a gesture module
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
    

    // Checkpoint Exam Routes
    Route::prefix('lessons/checkpoint-exam')->name('lessons.checkpoint-exam.')->group(function () {
        Route::get('/create', [LessonsController::class, 'createCheckpointExam'])->name('create');
        Route::post('/', [LessonsController::class, 'storeCheckpointExam'])->name('store');
        Route::get('/{id}', [LessonsController::class, 'showCheckpointExam'])->name('show');
        Route::post('/{id}/publish', [LessonsController::class, 'publishCheckpointExam'])->name('publish');
        Route::delete('/{id}', [LessonsController::class, 'destroyCheckpointExam'])->name('destroy');
        Route::get('/available-questions', [LessonsController::class, 'getAvailableExamQuestions'])->name('available-questions');
    });

    // ── Module Management Routes ──────────────────────────────────────────────
    // Show delete options page
    Route::get('/modules/{id}/delete-options', [ModulesController::class, 'showDeleteOptions'])
        ->name('modules.delete-options');
    
    // Delete module with options (AJAX)
    Route::post('/modules/{id}/delete-with-options', [ModulesController::class, 'deleteWithOptions'])
        ->name('modules.delete-with-options');
    
    // Update module details (AJAX)
    Route::put('/modules/{id}', [ModulesController::class, 'update'])
        ->name('modules.update');
    Route::post('/modules/{id}/update', [ModulesController::class, 'update']);

    
    // Simple delete (redirects to options page)
    Route::delete('/modules/{id}', [ModulesController::class, 'destroy'])
        ->name('modules.destroy');

}); // END of auth middleware group

// ─────────────────────────────────────────────────────────────────────────────
// ADMIN ROUTES
// ─────────────────────────────────────────────────────────────────────────────
Route::middleware(['auth', 'no.cache', 'admin'])->prefix('admin')->name('admin.')->group(function () {

    // Dashboard
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');

    // Analytics
    Route::get('/analytics', [AdminController::class, 'analytics'])->name('analytics');

    // Account Management
    Route::get('/accounts', [AdminController::class, 'accounts'])->name('accounts');
    Route::patch('/accounts/{id}/status', [AdminController::class, 'updateAccountStatus'])->name('accounts.status');
    Route::patch('/accounts/{id}/role', [AdminController::class, 'updateAccountRole'])->name('accounts.role');
    Route::post('/accounts/{id}/reset-password', [AdminController::class, 'resetAccountPassword'])->name('accounts.reset-password');

    // Audit Logs
    Route::get('/audit-logs', [AdminController::class, 'auditLogs'])->name('audit-logs');

    // Reports (Escalated concerns from teachers only)
    Route::get('/reports', [AdminController::class, 'reports'])->name('reports');
    Route::get('/reports/{id}', [AdminController::class, 'getReport'])->name('reports.show');
    Route::post('/reports/{id}/respond', [AdminController::class, 'respondToReport'])->name('reports.respond');

    // Ratings (Teacher & Student — approval queue for the landing page)
    Route::get('/ratings', [AdminController::class, 'ratings'])->name('ratings');
    Route::patch('/ratings/{type}/{id}/approval', [AdminController::class, 'updateRatingApproval'])->name('ratings.approval');

// ── DEFAULT LESSONS (Admin) ────────────────────────────────────────────
Route::prefix('lessons')->name('lesson-templates.')->group(function () {
    // Listing + push/promote actions
    Route::get('/', [\App\Http\Controllers\Admin\LessonTemplatesController::class, 'index'])->name('index');
    Route::post('/push-all', [\App\Http\Controllers\Admin\LessonTemplatesController::class, 'pushAll'])->name('push-all');
    Route::post('/{moduleId}/push', [\App\Http\Controllers\Admin\LessonTemplatesController::class, 'pushModule'])->name('push');
    Route::post('/{moduleId}/promote', [\App\Http\Controllers\Admin\LessonTemplatesController::class, 'promote'])->name('promote');

    // ── NEW: Teacher selection for push ─────────────────────────────────
    Route::get('/teachers', [\App\Http\Controllers\Admin\LessonTemplatesController::class, 'getTeachers'])->name('teachers');  // ✅ ADD THIS
    Route::post('/push-selected', [\App\Http\Controllers\Admin\LessonTemplatesController::class, 'pushSelected'])->name('push-selected'); 


    // ── ADMIN LESSON CRUD (uses same LessonsController, but admin context) ──
    Route::get('/create', [LessonsController::class, 'create'])->name('create');
    Route::post('/', [LessonsController::class, 'store'])->name('store');
    Route::post('/preview', [LessonsController::class, 'preview'])->name('preview');
    Route::post('/ai-generate', [LessonsController::class, 'aiGenerate'])->name('ai-generate');
    Route::post('/ai-generate-pdf', [LessonsController::class, 'aiGeneratePdf'])->name('ai-generate-pdf');
    Route::post('/ai-generate-quiz', [LessonsController::class, 'aiGenerateQuiz'])->name('ai-generate-quiz');
    Route::post('/upload-media', [LessonsController::class, 'uploadMedia'])->name('upload-media');

    // Checkpoint exams for the default curriculum
    Route::prefix('checkpoint-exam')->name('checkpoint-exam.')->group(function () {
        Route::get('/create', [LessonsController::class, 'createCheckpointExam'])->name('create');
        Route::post('/', [LessonsController::class, 'storeCheckpointExam'])->name('store');
        Route::get('/available-questions', [LessonsController::class, 'getAvailableExamQuestions'])->name('available-questions');
        Route::get('/{id}', [LessonsController::class, 'showCheckpointExam'])->name('show');
        Route::post('/{id}/publish', [LessonsController::class, 'publishCheckpointExam'])->name('publish');
        Route::delete('/{id}', [LessonsController::class, 'destroyCheckpointExam'])->name('destroy');
    });

    Route::get('/{lesson}/view', [LessonsController::class, 'view'])->name('view');
    Route::get('/{lesson}/preview-modal', [LessonsController::class, 'previewModal'])->name('preview-modal');
    Route::get('/{lesson}/edit', [LessonsController::class, 'edit'])->name('edit');
    Route::put('/{lesson}', [LessonsController::class, 'update'])->name('update');

    // ── ADMIN PUBLISH CONFIG (different from teacher version) ──
    Route::get('/{lesson}/publish-config', [LessonsController::class, 'showAdminPublishConfig'])->name('publish.config');
    Route::post('/{lesson}/publish', [LessonsController::class, 'adminPublishLesson'])->name('publish');

    Route::post('/{id}/soft-delete', [LessonsController::class, 'softDelete'])->name('soft-delete');
    Route::post('/{id}/hard-delete', [LessonsController::class, 'hardDelete'])->name('hard-delete');
    Route::post('/{id}/restore', [LessonsController::class, 'restore'])->name('restore');
    Route::delete('/{id}', [LessonsController::class, 'destroy'])->name('destroy');

    Route::get('/media-library', [LessonsController::class, 'mediaLibraryFolders'])->name('media-library.folders');
    Route::get('/media-library/{folder}', [LessonsController::class, 'mediaLibraryFiles'])->name('media-library.files');
    Route::get('/my-uploads', [LessonsController::class, 'mediaLibraryMyUploads'])->name('media-library.my-uploads');
});

Route::get('/testing/alphabet', [TestingController::class, 'alphabetPage'])->name('testing.alphabet');
 
Route::get('/api/testing/signs', [TestingController::class, 'signs'])->name('api.testing.signs');
Route::get('/api/testing/trials', [TestingController::class, 'trials'])->name('api.testing.trials');
Route::post('/api/testing/trials', [TestingController::class, 'store'])->name('api.testing.trials.store');
Route::get('/api/testing/export', [TestingController::class, 'export'])->name('api.testing.export');
Route::get('/api/testing/metrics', [TestingController::class, 'metrics'])->name('api.testing.metrics');
Route::get('/api/testing/export-csv', [TestingController::class, 'exportCsv'])->name('api.testing.export-csv');
});