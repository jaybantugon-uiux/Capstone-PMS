<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ActivityController;
use App\Http\Controllers\EquipmentController;
use App\Models\Project;
use App\Models\Task;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware('guest')->group(function () {
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.post');
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
    Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'sendResetLink'])->name('password.email');
    Route::get('/reset-password/{token}', [AuthController::class, 'showResetPassword'])->name('password.reset');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');
    Route::get('/reactivate-account', [AuthController::class, 'showReactivateAccount'])->name('account.reactivate.form');
    Route::post('/reactivate-account', [AuthController::class, 'reactivateAccount'])->name('account.reactivate');
});

Route::get('/email/verify/{id}/{hash}', [AuthController::class, 'verifyEmail'])
    ->middleware(['signed', 'throttle:6,1'])
    ->name('verification.verify');

Route::middleware('auth')->group(function () {
    Route::get('/email/verify', [AuthController::class, 'showVerificationNotice'])->name('verification.notice');
    Route::post('/email/verification-notification', [AuthController::class, 'resendVerification'])
        ->middleware('throttle:6,1')
        ->name('verification.send');
});

Route::middleware(['auth', 'verified'])->group(function () {
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
    Route::get('/account/deactivate', [AuthController::class, 'showDeactivateAccount'])->name('account.deactivate.form');
    Route::post('/account/deactivate', [AuthController::class, 'deactivateAccount'])->name('account.deactivate');
    Route::get('account/edit', [AccountController::class, 'edit'])->name('account.edit');
    Route::post('account/update', [AccountController::class, 'update'])->name('account.update');

    Route::get('/admin-dashboard', function() {
        $totalProjects = Project::count();
        $activeProjects = Project::where('archived', false)->count();
        $archivedProjects = Project::where('archived', true)->count();
        $totalTasks = Task::count();
        $pendingTasks = Task::where('status', 'pending')->count();
        $inProgressTasks = Task::where('status', 'in_progress')->count();
        $completedTasks = Task::where('status', 'completed')->count();
        $overdueTasksCount = Task::where('due_date', '<', now())->where('status', '!=', 'completed')->count();
        $recentProjects = Project::latest()->take(5)->get();
        $overdueTasks = Task::where('due_date', '<', now())->where('status', '!=', 'completed')->take(5)->get();

        return view('admin.dashboard', compact(
            'totalProjects',
            'activeProjects',
            'archivedProjects',
            'totalTasks',
            'pendingTasks',
            'inProgressTasks',
            'completedTasks',
            'overdueTasksCount',
            'recentProjects',
            'overdueTasks'
        ));
    })->middleware('role:admin')->name('admin.dashboard');
    
    Route::get('/employee-dashboard', function() { 
        return view('employee.dashboard'); 
    })->middleware('role:emp')->name('employee.dashboard');
    
    Route::get('/finance-dashboard', function() { 
        return view('finance.dashboard'); 
    })->middleware('role:finance')->name('finance.dashboard');
    
    Route::get('/pm-dashboard', [DashboardController::class, 'pmDashboard'])
    ->middleware('role:pm')->name('pm.dashboard');
    
    Route::get('/sc-dashboard', [TaskController::class, 'dashboard'])
        ->middleware('role:sc')->name('sc.dashboard');
    
    Route::get('/client-dashboard', function() { 
        return view('dashboard'); 
    })->middleware('role:client')->name('client.dashboard');

    Route::prefix('projects')->name('projects.')->group(function () {
        Route::middleware('role:pm,admin')->group(function () {
            Route::get('/', [ProjectController::class, 'index'])->name('index');
            Route::get('/create', [ProjectController::class, 'create'])->name('create');
            Route::post('/', [ProjectController::class, 'store'])->name('store');
            Route::get('/archived', [ProjectController::class, 'archived'])->name('archived');
            Route::get('/{project}/edit', [ProjectController::class, 'edit'])->name('edit');
            Route::put('/{project}', [ProjectController::class, 'update'])->name('update');
            Route::post('/{project}/archive', [ProjectController::class, 'archive'])->name('archive');
            Route::post('/{project}/restore', [ProjectController::class, 'restore'])->name('restore');
        });
        Route::get('/{project}', [ProjectController::class, 'show'])->name('show');
    });

    Route::prefix('tasks')->name('tasks.')->group(function () {
        Route::middleware('role:pm,admin')->group(function () {
            Route::get('/', [TaskController::class, 'index'])->name('index');
            Route::get('/create', [TaskController::class, 'create'])->name('create');
            Route::post('/', [TaskController::class, 'store'])->name('store');
            Route::get('/archived', [TaskController::class, 'archived'])->name('archived');
            Route::get('/{task}/edit', [TaskController::class, 'edit'])->name('edit');
            Route::put('/{task}', [TaskController::class, 'update'])->name('update');
            Route::post('/{task}/archive', [TaskController::class, 'archive'])->name('archive');
            Route::post('/{task}/restore', [TaskController::class, 'restore'])->name('restore');
            Route::get('/calendar', [TaskController::class, 'calendar'])->name('calendar');
        });
        Route::get('/{task}', [TaskController::class, 'show'])->name('show');
        Route::patch('/{task}/status', [TaskController::class, 'updateStatus'])->name('update-status');
    });

    Route::middleware(['auth', 'role:admin,pm'])->group(function () {
        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/generate', [ReportController::class, 'generateReport'])->name('reports.generate');
        Route::get('/reports/project', [ReportController::class, 'projectReport'])->name('reports.project');
        Route::get('/reports/task', [ReportController::class, 'taskReport'])->name('reports.task');
        Route::get('/reports/performance', [ReportController::class, 'performanceReport'])->name('reports.performance');
        Route::get('/reports/project/export', [ReportController::class, 'exportProjectReport'])->name('reports.project.export');
        Route::get('/reports/task/export', [ReportController::class, 'exportTaskReport'])->name('reports.task.export');
        Route::get('/reports/staff-workload', [ReportController::class, 'viewAvailableStaff'])->name('reports.view-staff');
        Route::get('/reports/staff-workload/{user}', [ReportController::class, 'staffWorkloadDetail'])->name('reports.staff-workload.detail');
    });

    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [TaskController::class, 'notifications'])->name('index');
        Route::post('/{id}/mark-read', [TaskController::class, 'markNotificationAsRead'])->name('mark.read');
        Route::post('/mark-all-read', [TaskController::class, 'markAllNotificationsAsRead'])->name('mark.all.read');
        Route::delete('/{id}', [TaskController::class, 'deleteNotification'])->name('delete');
    });

    Route::get('/activities', [ActivityController::class, 'index'])->name('activity.index');
    
// Equipment Inventory Routes
Route::prefix('equipment')->name('equipment.')->middleware(['auth', 'verified'])->group(function () {
    // Apply role middleware to the entire equipment group
    Route::middleware('role:admin,emp')->group(function () {
        // Main equipment routes
        Route::get('/', [EquipmentController::class, 'index'])->name('index');
        Route::get('/create', [EquipmentController::class, 'create'])->name('create');
        Route::post('/', [EquipmentController::class, 'store'])->name('store');
        Route::get('/{id}', [EquipmentController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [EquipmentController::class, 'edit'])->name('edit');
        Route::put('/{id}', [EquipmentController::class, 'update'])->name('update');
        
        // Archive and restore
        Route::post('/{id}/archive', [EquipmentController::class, 'archive'])->name('archive');
        Route::post('/{id}/restore', [EquipmentController::class, 'restore'])->name('restore');
        Route::get('/archived/list', [EquipmentController::class, 'archived'])->name('archived');
        
        // Stock management
        Route::get('/{id}/restock', [EquipmentController::class, 'restockForm'])->name('restock.form');
        Route::post('/{id}/restock', [EquipmentController::class, 'restock'])->name('restock');
        Route::get('/{id}/use-form', [EquipmentController::class, 'useForm'])->name('use.form');
        Route::post('/{id}/use', [EquipmentController::class, 'useEquipment'])->name('use');
        
        // Bulk operations
        Route::get('/bulk/restock', [EquipmentController::class, 'bulkRestockForm'])->name('bulk-restock.form');
        Route::post('/bulk/restock', [EquipmentController::class, 'bulkRestock'])->name('bulk-restock');
        
        // Reports and logs
        Route::get('/reports/low-stock/{threshold?}', [EquipmentController::class, 'lowStock'])->name('low-stock');
        Route::get('/{id}/logs', [EquipmentController::class, 'logs'])->name('logs');
    });
});
});


if (app()->environment('local')) {
    Route::get('/test-email', [AuthController::class, 'testEmail'])->name('test.email');
}