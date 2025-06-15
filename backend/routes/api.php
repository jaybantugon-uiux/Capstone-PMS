<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Auth\ApiAuthController;
use App\Http\Controllers\Api\Projects\ProjectController;
use App\Http\Controllers\Api\Tasks\TaskController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Public API Routes
Route::prefix('auth')->group(function () {
    Route::post('/register', [ApiAuthController::class, 'register']);
    Route::post('/login', [ApiAuthController::class, 'login']);
    Route::post('/forgot-password', [ApiAuthController::class, 'sendResetLink']);
    Route::post('/reset-password', [ApiAuthController::class, 'resetPassword']);
    
    // Account reactivation routes
    Route::post('/reactivate-account', [ApiAuthController::class, 'reactivateAccount']);
});

// Email verification route (FIXED - accessible without authentication for verification links)
Route::get('/email/verify/{id}/{hash}', [ApiAuthController::class, 'verifyEmail'])
    ->middleware(['signed:relative', 'throttle:6,1'])
    ->name('api.verification.verify');

// Protected API Routes
Route::middleware('auth:sanctum')->group(function () {
    // User routes
    Route::get('/user', [ApiAuthController::class, 'profile']);
    Route::post('/logout', [ApiAuthController::class, 'logout']);
    Route::post('/logout-all', [ApiAuthController::class, 'logoutAll']);
    
    // Account management routes
    Route::post('/account/deactivate', [ApiAuthController::class, 'deactivateAccount']);
    
    // Email verification
    Route::post('/email/verification-notification', [ApiAuthController::class, 'sendVerification'])
        ->middleware('throttle:6,1');
    
    // Project routes
    Route::get('/projects', [ProjectController::class, 'index']); // Fetch active and archived projects
    Route::post('/projects', [ProjectController::class, 'store']); // Create a new project
    Route::put('/projects/{project}', [ProjectController::class, 'update']); // Update a project
    Route::post('/projects/{id}/archive', [ProjectController::class, 'archive']); // Archive a project
    Route::post('/projects/{id}/restore', [ProjectController::class, 'restore']); // Restore a project

    // Task routes
    Route::get('/tasks', [TaskController::class, 'index']); // Fetch tasks
    Route::post('/tasks', [TaskController::class, 'store']); // Create a new task
    Route::put('/tasks/{task}', [TaskController::class, 'update']); // Update a task
    Route::delete('/tasks/{task}', [TaskController::class, 'destroy']); // Delete a task
});

// Health check endpoint
Route::get('/health', function () {
    return response()->json([
        'success' => true,
        'message' => 'API is working',
        'timestamp' => now()->toISOString()
    ]);
});