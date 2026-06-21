<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StudentAuthController; // Add this

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// ADD THESE NEW ROUTES FOR MOBILE APP
Route::post('/student/login', [StudentAuthController::class, 'login']);
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/student/profile', [StudentAuthController::class, 'profile']);
    Route::post('/student/logout', [StudentAuthController::class, 'logout']);
    Route::post('/student/update-level', [StudentAuthController::class, 'updateLevel']);
    Route::post('/student/save-learning-path', [StudentAuthController::class, 'saveLearningPath']); 
    Route::get('/student/learning-path', [StudentAuthController::class, 'getLearningPath']);
});
