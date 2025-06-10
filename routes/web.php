<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProjectController;

Route::get('/', function () {
    return view('welcome');
});

// Authentication Routes
Route::middleware('guest')->group(function () {
    // Registration routes
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.post');
    
    // Login routes
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
    
    // Password reset routes
    Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'sendResetLink'])->name('password.email');
    
    Route::get('/reset-password/{token}', [AuthController::class, 'showResetPassword'])->name('password.reset');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');
    
    // Account reactivation routes (for deactivated accounts)
    Route::get('/reactivate-account', [AuthController::class, 'showReactivateAccount'])->name('account.reactivate.form');
    Route::post('/reactivate-account', [AuthController::class, 'reactivateAccount'])->name('account.reactivate');
});

// Email Verification Routes (accessible without authentication but with signed URLs)
Route::get('/email/verify/{id}/{hash}', [AuthController::class, 'verifyEmail'])
    ->middleware(['signed', 'throttle:6,1'])
    ->name('verification.verify');

Route::middleware('auth')->group(function () {
    Route::get('/email/verify', [AuthController::class, 'showVerificationNotice'])->name('verification.notice');
    Route::post('/email/verification-notification', [AuthController::class, 'resendVerification'])
        ->middleware('throttle:6,1')
        ->name('verification.send');
});

// Protected Routes
Route::middleware(['auth', 'verified'])->group(function () {
    // Main dashboard route - redirects to role-specific dashboard
    Route::get('/dashboard', function() {
        $user = auth()->user();
        switch ($user->role) {
            case 'admin':
                return redirect()->route('admin.dashboard');
            case 'emp':
                return redirect()->route('employee.dashboard');
            case 'finance':
                return redirect()->route('finance.dashboard');
            case 'pm':
                return redirect()->route('pm.dashboard');
            case 'sc':
                return redirect()->route('sc.dashboard');
            case 'client':
            default:
                return redirect()->route('client.dashboard');
        }
    })->name('dashboard');
    
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    
    // Account Management Routes
    Route::get('/account/deactivate', [AuthController::class, 'showDeactivateAccount'])->name('account.deactivate.form');
    Route::post('/account/deactivate', [AuthController::class, 'deactivateAccount'])->name('account.deactivate');
    
    // Role-specific dashboard routes
    Route::get('/admin-dashboard', function() {
        return view('admin.dashboard');
    })->middleware('role:admin')->name('admin.dashboard');
    
    Route::get('/employee-dashboard', function() {
        return view('employee.dashboard');
    })->middleware('role:emp')->name('employee.dashboard');
    
    Route::get('/finance-dashboard', function() {
        return view('finance.dashboard');
    })->middleware('role:finance')->name('finance.dashboard');
    
    Route::get('/pm-dashboard', function() {
        return view('pm.dashboard');
    })->middleware('role:pm')->name('pm.dashboard');
    
    Route::get('/sc-dashboard', function() {
        return view('sc.dashboard');
    })->middleware('role:sc')->name('sc.dashboard');
    
    Route::get('/client-dashboard', function() {
        return view('dashboard');
    })->middleware('role:client')->name('client.dashboard');
    
    // Project Management Routes - Fixed routing
  Route::prefix('projects')->name('projects.')->group(function () {
    Route::middleware('role:pm,admin')->group(function () {
        Route::get('/', [ProjectController::class, 'index'])->name('index');
        Route::get('/create', [ProjectController::class, 'create'])->name('create');
        Route::post('/', [ProjectController::class, 'store'])->name('store');
        Route::get('/{project}', [ProjectController::class, 'show'])->name('show');
        Route::get('/{project}/edit', [ProjectController::class, 'edit'])->name('edit');
        Route::put('/{project}', [ProjectController::class, 'update'])->name('update');
        Route::post('/{id}/archive', [ProjectController::class, 'archive'])->name('archive');
        Route::post('/{id}/restore', [ProjectController::class, 'restore'])->name('restore');
    });
});
});

// Test Email Route (only in development)
if (app()->environment('local')) {
    Route::get('/test-email', [AuthController::class, 'testEmail'])->name('test.email');
}