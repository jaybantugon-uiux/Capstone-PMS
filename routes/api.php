<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ApiAuthController;

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
});

// Email verification route (signed URL)
Route::get('/email/verify/{id}/{hash}', [ApiAuthController::class, 'verifyEmail'])
    ->middleware(['signed', 'throttle:6,1'])
    ->name('api.verification.verify');

// Protected API Routes
Route::middleware('auth:sanctum')->group(function () {
    // User routes
    Route::get('/user', [ApiAuthController::class, 'profile']);
    Route::post('/logout', [ApiAuthController::class, 'logout']);
    Route::post('/logout-all', [ApiAuthController::class, 'logoutAll']);
    
    // Email verification
    Route::post('/email/verification-notification', [ApiAuthController::class, 'sendVerification'])
        ->middleware('throttle:6,1');
});

// Health check endpoint
Route::get('/health', function () {
    return response()->json([
        'success' => true,
        'message' => 'API is working',
        'timestamp' => now()->toISOString()
    ]);
});