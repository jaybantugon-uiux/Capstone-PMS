<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApiAuthController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\EquipmentController;
use App\Http\Controllers\TaskReportController;
use App\Http\Controllers\SiteIssueController;

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
    Route::get('projects', [ProjectController::class, 'apiIndex']);
    Route::post('projects', [ProjectController::class, 'apiStore']);
    
    // User routes
    Route::get('users', [ApiAuthController::class, 'apiUsers']);

    // Equipment
    Route::get('/equipment', [EquipmentController::class, 'apiIndex']);
    Route::post('/equipment', [EquipmentController::class, 'apiStore']);
    Route::put('/equipment/{id}', [EquipmentController::class, 'apiUpdate']);
    Route::post('/equipment/{id}/archived', [EquipmentController::class, 'archived']);
    Route::get('/equipment/{id}/archive', [EquipmentController::class, 'archive']);
    Route::post('/equipment/{id}/archived', [EquipmentController::class, 'apiArchive']);
    Route::get('/equipment/archived', [EquipmentController::class, 'apiArchived']);
    Route::post('/equipment/{id}/unarchive', [EquipmentController::class, 'apiUnarchive']);
    Route::post('/equipment/{id}/restock', [EquipmentController::class, 'apiRestock']);

    // Task routes
    Route::get('tasks', [TaskController::class, 'apiIndex']);
    Route::post('tasks', [TaskController::class, 'apiStore']);
    Route::put('tasks/{task}', [TaskController::class, 'apiUpdate']);
    Route::get('tasks/archived', [TaskController::class, 'archived']);
    Route::post('tasks/active', [TaskController::class, 'active']);
    Route::post('tasks/{task}/archived', [TaskController::class, 'apiArchive']);
    Route::post('tasks/{task}/unarchive', [TaskController::class, 'apiUnarchive']);

    //Projects
    Route::post('reportIssue', [SiteIssueController::class, 'apiSiteIssue']);
    Route::post('siteIssue', [SiteIssueController::class, 'apiIndex']);
    Route::post('submitReport', [TaskReportController::class, 'apiReportTask']);
    Route::post('taskReport', [TaskReportController::class, 'apiIndex']);
    Route::get('tasks/archived', [ProjectController::class, 'archived']);
    Route::post('tasks/active', [ProjectController::class, 'active']);
    Route::get('/projects/archived', [ProjectController::class, 'apiArchived']);
    Route::post('/projects/{project}/unarchive', [ProjectController::class, 'apiUnarchive']);
    Route::post('/projects/{project}/archive', [ProjectController::class, 'apiArchive']);


});

// Health check endpoint
Route::get('/health', function () {
    return response()->json([
        'success' => true,
        'message' => 'API is working',
        'timestamp' => now()->toISOString()
    ]);
});
