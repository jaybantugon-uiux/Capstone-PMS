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
use App\Http\Controllers\TaskReportController;
use App\Http\Controllers\SiteIssueController;
use App\Http\Controllers\SitePhotoController;
use App\Http\Controllers\ProgressReportController;
use App\Http\Controllers\ClientProjectController; 
use App\Http\Controllers\ClientNotificationPreferencesController; 
use App\Http\Controllers\EquipmentMonitoringController; 
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Models\ProgressReport;
use Illuminate\Support\Facades\Log;

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
    // Include site issues and site photos statistics
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

    // Site Issues Statistics
    $siteIssuesStats = [
        'total' => \App\Models\SiteIssue::count(),
        'open' => \App\Models\SiteIssue::where('status', 'open')->count(),
        'critical' => \App\Models\SiteIssue::where('priority', 'critical')->whereNotIn('status', ['resolved', 'closed'])->count(),
        'unacknowledged' => \App\Models\SiteIssue::whereNull('acknowledged_at')->count(),
    ];

    // Recent Site Issues
    $recentSiteIssues = \App\Models\SiteIssue::with(['reporter', 'project'])
        ->latest('reported_at')
        ->take(5)
        ->get();

    // Task Reports Statistics
    $taskReportsStats = [
        'total' => \App\Models\TaskReport::count(),
        'pending' => \App\Models\TaskReport::where('review_status', 'pending')->count(),
        'approved' => \App\Models\TaskReport::where('review_status', 'approved')->count(),
        'overdue_reviews' => \App\Models\TaskReport::where('review_status', 'pending')
            ->where('created_at', '<', now()->subDays(2))->count(),
    ];

    $recentTaskReports = \App\Models\TaskReport::with(['user', 'task'])
        ->latest()
        ->take(5)
        ->get();

    // Site Photos Statistics
    $sitePhotosStats = [
        'total' => \App\Models\SitePhoto::count(),
        'submitted' => \App\Models\SitePhoto::where('submission_status', 'submitted')->count(),
        'approved' => \App\Models\SitePhoto::where('submission_status', 'approved')->count(),
        'featured' => \App\Models\SitePhoto::where('is_featured', true)->count(),
        'overdue_reviews' => \App\Models\SitePhoto::where('submission_status', 'submitted')
            ->where('submitted_at', '<', now()->subDays(3))->count(),
    ];

    // Recent Site Photos
    $recentSitePhotos = \App\Models\SitePhoto::with(['uploader', 'project'])
        ->latest('submitted_at')
        ->take(5)
        ->get();

    // Progress Reports Statistics
    $progressReportsStats = [
        'total' => ProgressReport::count(),
        'sent' => ProgressReport::where('status', 'sent')->count(),
        'viewed' => ProgressReport::where('status', 'viewed')->count(),
        'recent' => ProgressReport::where('created_at', '>=', now()->subDays(7))->count(),
    ];

    // Recent Progress Reports
    $recentProgressReports = ProgressReport::with(['client', 'project', 'creator'])
        ->latest()
        ->take(5)
        ->get();

    // ====================================================================
    // ADMIN PERSONAL EQUIPMENT STATISTICS (Like Site Coordinator)
    // ====================================================================
    
    $user = auth()->user();
    
    // Admin's personal equipment statistics
    $adminEquipmentStats = [
        // Admin's own equipment requests
        'pending_requests' => \App\Models\EquipmentRequest::where('user_id', $user->id)
            ->where('status', 'pending')->count(),
        'approved_requests' => \App\Models\EquipmentRequest::where('user_id', $user->id)
            ->where('status', 'approved')->count(),
        'declined_requests' => \App\Models\EquipmentRequest::where('user_id', $user->id)
            ->where('status', 'declined')->count(),
        
        // Admin's own equipment
        'total_equipment' => \App\Models\MonitoredEquipment::where('user_id', $user->id)->count(),
        'active_equipment' => \App\Models\MonitoredEquipment::where('user_id', $user->id)
            ->where('status', 'active')->count(),
        'personal_equipment' => \App\Models\MonitoredEquipment::where('user_id', $user->id)
            ->where('usage_type', 'personal')->count(),
        'project_equipment' => \App\Models\MonitoredEquipment::where('user_id', $user->id)
            ->where('usage_type', 'project_site')->count(),
        
        // Admin's maintenance tasks
        'scheduled_maintenance' => \App\Models\EquipmentMaintenance::whereHas('monitoredEquipment', function($q) use ($user) {
                $q->where('user_id', $user->id);
            })->where('status', 'scheduled')->count(),
        'overdue_maintenance' => \App\Models\EquipmentMaintenance::whereHas('monitoredEquipment', function($q) use ($user) {
                $q->where('user_id', $user->id);
            })->where('status', 'scheduled')->where('scheduled_date', '<', now())->count(),
    ];

    // ====================================================================
    // EQUIPMENT MONITORING MANAGEMENT STATISTICS (System-wide)
    // ====================================================================
    
    // Equipment Monitoring Statistics - Fixed calculations
    $equipmentMonitoringStats = [
        // Equipment Request Statistics
        'total_requests' => \App\Models\EquipmentRequest::count(),
        'pending_requests' => \App\Models\EquipmentRequest::where('status', 'pending')->count(),
        'approved_requests' => \App\Models\EquipmentRequest::where('status', 'approved')->count(),
        'declined_requests' => \App\Models\EquipmentRequest::where('status', 'declined')->count(),
        
        // Monitored Equipment Statistics
        'total_equipment' => \App\Models\MonitoredEquipment::count(),
        'active_equipment' => \App\Models\MonitoredEquipment::where('status', 'active')->count(),
        'pending_equipment' => \App\Models\MonitoredEquipment::where('status', 'pending_approval')->count(),
        'personal_equipment' => \App\Models\MonitoredEquipment::where('usage_type', 'personal')->count(),
        'project_equipment' => \App\Models\MonitoredEquipment::where('usage_type', 'project_site')->count(),
        
        // Maintenance Statistics
        'maintenance_scheduled' => \App\Models\EquipmentMaintenance::where('status', 'scheduled')->count(),
        'maintenance_overdue' => \App\Models\EquipmentMaintenance::where('status', 'scheduled')
            ->where('scheduled_date', '<', now())->count(),
        'maintenance_completed' => \App\Models\EquipmentMaintenance::where('status', 'completed')->count(),
        'maintenance_this_week' => \App\Models\EquipmentMaintenance::where('status', 'scheduled')
            ->whereBetween('scheduled_date', [now(), now()->addDays(7)])->count(),
            
        // Equipment Status Breakdown
        'equipment_available' => \App\Models\MonitoredEquipment::where('availability_status', 'available')->count(),
        'equipment_in_use' => \App\Models\MonitoredEquipment::where('availability_status', 'in_use')->count(),
        'equipment_maintenance' => \App\Models\MonitoredEquipment::where('availability_status', 'maintenance')->count(),
        'equipment_out_of_order' => \App\Models\MonitoredEquipment::where('availability_status', 'out_of_order')->count(),
        
        // Recent activity
        'recent_requests' => \App\Models\EquipmentRequest::where('created_at', '>=', now()->subDays(7))->count(),
        'urgent_requests' => \App\Models\EquipmentRequest::where('status', 'pending')
            ->whereIn('urgency_level', ['high', 'critical'])->count(),
    ];

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
        'overdueTasks',
        'siteIssuesStats',
        'recentSiteIssues',
        'taskReportsStats',
        'recentTaskReports',
        'sitePhotosStats',
        'recentSitePhotos',
        'progressReportsStats',
        'recentProgressReports',
        'equipmentMonitoringStats',
        'adminEquipmentStats'  
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
    
Route::get('/sc-dashboard', function() {
    $user = auth()->user();
    
    // Existing statistics
    $totalTasks = Task::where('assigned_to', $user->id)->count();
    $pendingTasks = Task::where('assigned_to', $user->id)->where('status', 'pending')->count();
    $inProgressTasks = Task::where('assigned_to', $user->id)->where('status', 'in_progress')->count();
    $completedTasks = Task::where('assigned_to', $user->id)->where('status', 'completed')->count();
    
    $projects = Project::whereHas('tasks', function($query) use ($user) {
        $query->where('assigned_to', $user->id);
    })->withCount(['tasks' => function($query) use ($user) {
        $query->where('assigned_to', $user->id);
    }])->get();
    
    $tasks = Task::where('assigned_to', $user->id)
        ->with('project')
        ->latest()
        ->paginate(10);

    // Site Issues Statistics
    $siteIssuesStats = [
        'total' => \App\Models\SiteIssue::where('user_id', $user->id)->count(),
        'open' => \App\Models\SiteIssue::where('user_id', $user->id)->where('status', 'open')->count(),
        'critical' => \App\Models\SiteIssue::where('user_id', $user->id)->where('priority', 'critical')->whereNotIn('status', ['resolved', 'closed'])->count(),
        'resolved' => \App\Models\SiteIssue::where('user_id', $user->id)->where('status', 'resolved')->count(),
    ];

    // Recent Site Issues
    $recentSiteIssues = \App\Models\SiteIssue::where('user_id', $user->id)
        ->with('project')
        ->latest('reported_at')
        ->take(5)
        ->get();

    // Critical Site Issues
    $criticalSiteIssues = \App\Models\SiteIssue::where('user_id', $user->id)
        ->where('priority', 'critical')
        ->whereNotIn('status', ['resolved', 'closed'])
        ->with('project')
        ->latest('reported_at')
        ->get();

    // Task Reports Statistics
    $reportStats = [
        'total_reports' => \App\Models\TaskReport::where('user_id', $user->id)->count(),
        'pending_review' => \App\Models\TaskReport::where('user_id', $user->id)->where('review_status', 'pending')->count(),
        'approved_reports' => \App\Models\TaskReport::where('user_id', $user->id)->where('review_status', 'approved')->count(),
        'average_rating' => \App\Models\TaskReport::where('user_id', $user->id)->whereNotNull('admin_rating')->avg('admin_rating'),
    ];

    // Recent Task Reports
    $recentReports = \App\Models\TaskReport::where('user_id', $user->id)
        ->with('task')
        ->latest()
        ->take(5)
        ->get();

    // Tasks Needing Reports
    $tasksNeedingReports = Task::where('assigned_to', $user->id)
        ->where('status', 'in_progress')
        ->where('archived', false)
        ->whereDoesntHave('taskReports', function($q) {
            $q->where('report_date', '>=', now()->subDays(7));
        })
        ->with('project')
        ->take(10)
        ->get();

    // Site Photos Statistics
    $sitePhotosStats = [
        'total' => \App\Models\SitePhoto::where('user_id', $user->id)->count(),
        'submitted' => \App\Models\SitePhoto::where('user_id', $user->id)->where('submission_status', 'submitted')->count(),
        'approved' => \App\Models\SitePhoto::where('user_id', $user->id)->where('submission_status', 'approved')->count(),
        'featured' => \App\Models\SitePhoto::where('user_id', $user->id)->where('is_featured', true)->count(),
    ];

    // Recent Site Photos
    $recentSitePhotos = \App\Models\SitePhoto::where('user_id', $user->id)
        ->with('project')
        ->latest()
        ->take(5)
        ->get();

    // ====================================================================
    // EQUIPMENT MONITORING STATISTICS
    // ====================================================================

    // Equipment Statistics
    $equipmentStats = [
        'total_equipment' => \App\Models\MonitoredEquipment::where('user_id', $user->id)->count(),
        'active_equipment' => \App\Models\MonitoredEquipment::where('user_id', $user->id)->where('status', 'active')->count(),
        'personal_equipment' => \App\Models\MonitoredEquipment::where('user_id', $user->id)->where('usage_type', 'personal')->count(),
        'project_equipment' => \App\Models\MonitoredEquipment::where('user_id', $user->id)->where('usage_type', 'project_site')->count(),
        'maintenance_equipment' => \App\Models\MonitoredEquipment::where('user_id', $user->id)->where('availability_status', 'maintenance')->count(),
        'out_of_order_equipment' => \App\Models\MonitoredEquipment::where('user_id', $user->id)->where('availability_status', 'out_of_order')->count(),
    ];

    // Equipment Request Statistics
    $equipmentRequestStats = [
        'total_requests' => \App\Models\EquipmentRequest::where('user_id', $user->id)->count(),
        'pending_requests' => \App\Models\EquipmentRequest::where('user_id', $user->id)->where('status', 'pending')->count(),
        'approved_requests' => \App\Models\EquipmentRequest::where('user_id', $user->id)->where('status', 'approved')->count(),
        'declined_requests' => \App\Models\EquipmentRequest::where('user_id', $user->id)->where('status', 'declined')->count(),
        'personal_requests' => \App\Models\EquipmentRequest::where('user_id', $user->id)->where('usage_type', 'personal')->count(),
        'project_requests' => \App\Models\EquipmentRequest::where('user_id', $user->id)->where('usage_type', 'project_site')->count(),
    ];

    // Personal Equipment (active)
    $personalEquipment = \App\Models\MonitoredEquipment::where('user_id', $user->id)
        ->where('usage_type', 'personal')
        ->where('status', 'active')
        ->with(['equipmentRequest'])
        ->orderBy('created_at', 'desc')
        ->get();

    // Project Equipment (active and approved)
    $projectEquipment = \App\Models\MonitoredEquipment::where('user_id', $user->id)
        ->where('usage_type', 'project_site')
        ->where('status', 'active')
        ->whereHas('equipmentRequest', function($q) {
            $q->where('status', 'approved');
        })
        ->with(['equipmentRequest', 'project'])
        ->orderBy('created_at', 'desc')
        ->get();

    // Pending Equipment Requests
    $pendingEquipmentRequests = \App\Models\EquipmentRequest::where('user_id', $user->id)
        ->where('status', 'pending')
        ->with(['project'])
        ->orderBy('urgency_level', 'desc')
        ->orderBy('created_at', 'desc')
        ->get();

    // Upcoming Maintenance
    $upcomingMaintenance = \App\Models\EquipmentMaintenance::whereHas('monitoredEquipment', function($q) use ($user) {
            $q->where('user_id', $user->id);
        })
        ->where('status', 'scheduled')
        ->where('scheduled_date', '>=', now())
        ->where('scheduled_date', '<=', now()->addDays(30))
        ->with(['monitoredEquipment'])
        ->orderBy('scheduled_date', 'asc')
        ->take(5)
        ->get();

    // Overdue Maintenance Count
    $overdueMaintenance = \App\Models\EquipmentMaintenance::whereHas('monitoredEquipment', function($q) use ($user) {
            $q->where('user_id', $user->id);
        })
        ->where('status', 'scheduled')
        ->where('scheduled_date', '<', now())
        ->count();

    // Recent Equipment Requests
    $recentEquipmentRequests = \App\Models\EquipmentRequest::where('user_id', $user->id)
        ->with(['monitoredEquipment', 'project'])
        ->orderBy('created_at', 'desc')
        ->take(10)
        ->get();

    // Equipment needing attention (maintenance due, out of order, etc.)
    $equipmentNeedingAttention = \App\Models\MonitoredEquipment::where('user_id', $user->id)
        ->where('status', 'active')
        ->where(function($query) {
            $query->where('availability_status', 'out_of_order')
                  ->orWhere('availability_status', 'maintenance')
                  ->orWhere('next_maintenance_date', '<=', now()->addDays(7));
        })
        ->with(['project'])
        ->get();

    return view('sc.dashboard', compact(
        // Existing variables
        'totalTasks', 'pendingTasks', 'inProgressTasks', 'completedTasks',
        'projects', 'tasks', 
        'siteIssuesStats', 'recentSiteIssues', 'criticalSiteIssues',
        'reportStats', 'recentReports', 'tasksNeedingReports',
        'sitePhotosStats', 'recentSitePhotos',
        
        // Equipment monitoring variables
        'equipmentStats', 'equipmentRequestStats',
        'personalEquipment', 'projectEquipment',
        'pendingEquipmentRequests', 'upcomingMaintenance', 'overdueMaintenance',
        'recentEquipmentRequests', 'equipmentNeedingAttention'
    ));
})->middleware('role:sc')->name('sc.dashboard');
    
    // Client dashboard with progress reports
    Route::get('/client-dashboard', function() {
        $user = auth()->user();
        
        if ($user->role !== 'client') {
            return redirect()->route('dashboard');
        }

        // Get client's accessible projects
        $projects = $user->clientProjects()
            ->with(['projectClients' => function($query) use ($user) {
                $query->where('client_id', $user->id);
            }])
            ->paginate(6);

        // Get recent progress reports
        $recentReports = ProgressReport::forClient($user->id)
            ->with(['creator', 'project'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Get progress reports statistics
        $reportStats = [
            'total_reports' => ProgressReport::forClient($user->id)->count(),
            'unread_reports' => ProgressReport::forClient($user->id)->where('status', 'sent')->count(),
            'recent_reports' => ProgressReport::forClient($user->id)->recent(7)->count(),
        ];

        // Get recent project updates
        $recentUpdates = [];
        foreach ($projects as $project) {
            $updates = $project->publicUpdates()->limit(3)->get();
            if ($updates->isNotEmpty()) {
                $recentUpdates = array_merge($recentUpdates, $updates->toArray());
            }
        }

        // Sort recent updates by date
        usort($recentUpdates, function($a, $b) {
            return strtotime($b['posted_at']) - strtotime($a['posted_at']);
        });
        $recentUpdates = array_slice($recentUpdates, 0, 10);

        // Get recent activity count for notifications
        $recentActivityCount = $user->unreadNotifications()->count();

        return view('client.dashboard', compact(
            'projects', 
            'recentUpdates', 
            'recentActivityCount',
            'recentReports',
            'reportStats'
        ));
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
        
        // Public photo gallery for projects
        Route::get('/{project}/photos', function(Project $project) {
            $user = auth()->user();
            
            // Check if user can view project photos
            if (!in_array($user->role, ['admin', 'pm', 'client', 'sc'])) {
                abort(403);
            }
            
            // For site coordinators, only show if they have tasks in this project
            if ($user->role === 'sc') {
                $hasAccess = $user->tasks()->where('project_id', $project->id)->exists();
                if (!$hasAccess) {
                    abort(403);
                }
            }
            
            // For clients, check if they have access to this project
            if ($user->role === 'client') {
                $hasAccess = $user->clientProjects()->where('project_id', $project->id)->exists();
                if (!$hasAccess) {
                    abort(403, 'You do not have access to this project.');
                }
            }
            
            $photos = \App\Models\SitePhoto::where('project_id', $project->id)
                ->where('is_public', true)
                ->where('submission_status', 'approved')
                ->with(['uploader'])
                ->orderBy('photo_date', 'desc')
                ->paginate(12);
            
            return view('projects.photos', compact('project', 'photos'));
        })->name('photos');
    });

    // ====================================================================
    // NEW: CLIENT-SPECIFIC PROJECT ROUTES
    // ====================================================================
    
    // Client project management routes
    Route::middleware('role:client')->prefix('client')->name('client.')->group(function () {
        // Project routes for clients
        Route::prefix('projects')->name('projects.')->group(function () {
            Route::get('/', [ClientProjectController::class, 'index'])->name('index');
            Route::get('/{project}', [ClientProjectController::class, 'show'])->name('show');
            Route::get('/{project}/photos', [ClientProjectController::class, 'photos'])->name('photos');
            Route::get('/{project}/progress', [ClientProjectController::class, 'progress'])->name('progress');
            
            // AJAX routes for client project interactions
            Route::post('/{project}/mark-viewed', [ClientProjectController::class, 'markAsViewed'])->name('mark-viewed');
            Route::get('/{project}/updates', [ClientProjectController::class, 'getUpdates'])->name('updates');
        });
        
        // Notification preferences for clients
        Route::prefix('notification-preferences')->name('notification-preferences.')->group(function () {
            Route::get('/', [ClientNotificationPreferencesController::class, 'index'])->name('index');
            Route::post('/global', [ClientNotificationPreferencesController::class, 'updateGlobal'])->name('update-global');
            Route::post('/project', [ClientNotificationPreferencesController::class, 'createProjectPreferences'])->name('create-project');
            Route::put('/{preferences}', [ClientNotificationPreferencesController::class, 'updateProject'])->name('update-project');
            Route::delete('/{preferences}', [ClientNotificationPreferencesController::class, 'deleteProject'])->name('delete-project');
            Route::post('/bulk-update', [ClientNotificationPreferencesController::class, 'bulkUpdate'])->name('bulk-update');
            Route::get('/export', [ClientNotificationPreferencesController::class, 'export'])->name('export');
            
            // AJAX endpoints
            Route::get('/api/statistics', [ClientNotificationPreferencesController::class, 'getStatistics'])->name('api.statistics');
        });
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
        
        // Task report related routes
        Route::get('/{task}/reports', [TaskReportController::class, 'taskReports'])->name('reports');
        Route::get('/{task}/create-report', [TaskReportController::class, 'createFromTask'])->name('create-report');
    });

    // ====================================================================
    // ENHANCED TASK REPORT ROUTES FOR SITE COORDINATORS
    // ====================================================================
    Route::middleware('role:sc')->prefix('sc')->name('sc.')->group(function () {
        Route::prefix('task-reports')->name('task-reports.')->group(function () {
            Route::get('/', [TaskReportController::class, 'index'])->name('index');
            Route::get('/create', [TaskReportController::class, 'create'])->name('create');
            Route::post('/', [TaskReportController::class, 'store'])->name('store');
            Route::get('/{taskReport}', [TaskReportController::class, 'show'])->name('show');
            Route::get('/{taskReport}/edit', [TaskReportController::class, 'edit'])->name('edit');
            Route::put('/{taskReport}', [TaskReportController::class, 'update'])->name('update');
            Route::delete('/{taskReport}', [TaskReportController::class, 'destroy'])->name('destroy');
            
            // Dashboard and statistics for site coordinators
            Route::get('/dashboard/stats', [TaskReportController::class, 'dashboard'])->name('dashboard.stats');
        });

        // Site Issue Routes for Site Coordinators
        Route::prefix('site-issues')->name('site-issues.')->group(function () {
            Route::get('/', [SiteIssueController::class, 'index'])->name('index');
            Route::get('/create', [SiteIssueController::class, 'create'])->name('create');
            Route::post('/', [SiteIssueController::class, 'store'])->name('store');
            Route::get('/{siteIssue}', [SiteIssueController::class, 'show'])->name('show');
            Route::get('/{siteIssue}/edit', [SiteIssueController::class, 'edit'])->name('edit');
            Route::put('/{siteIssue}', [SiteIssueController::class, 'update'])->name('update');
            Route::post('/{siteIssue}/comments', [SiteIssueController::class, 'addComment'])->name('add-comment');
            
            // AJAX route for getting project tasks
            Route::get('/ajax/project-tasks', [SiteIssueController::class, 'getProjectTasks'])->name('get-project-tasks');
        });

        // Site Photo Routes for Site Coordinators
        Route::prefix('site-photos')->name('site-photos.')->group(function () {
            Route::get('/', [SitePhotoController::class, 'index'])->name('index');
            Route::get('/create', [SitePhotoController::class, 'create'])->name('create');
            Route::post('/', [SitePhotoController::class, 'store'])->name('store');
            Route::get('/{sitePhoto}', [SitePhotoController::class, 'show'])->name('show');
            Route::get('/{sitePhoto}/edit', [SitePhotoController::class, 'edit'])->name('edit');
            Route::put('/{sitePhoto}', [SitePhotoController::class, 'update'])->name('update');
            Route::delete('/{sitePhoto}', [SitePhotoController::class, 'destroy'])->name('destroy');
            Route::post('/{sitePhoto}/comments', [SitePhotoController::class, 'addComment'])->name('add-comment');
            
            // AJAX route for getting project tasks
            Route::get('/ajax/project-tasks', [SitePhotoController::class, 'getProjectTasks'])->name('get-project-tasks');
        });
    });

    // ====================================================================
    // ENHANCED PROJECT MANAGER (PM) SPECIFIC ROUTES FOR PROGRESS REPORTS
    // ====================================================================
    Route::middleware('role:pm')->prefix('pm')->name('pm.')->group(function () {
        // PM-specific progress reports routes
        Route::prefix('progress-reports')->name('progress-reports.')->group(function () {
            Route::get('/', [ProgressReportController::class, 'pmIndex'])->name('index');
            
            // Export functionality for PM
            Route::get('/export/csv', function(\Illuminate\Http\Request $request) {
                $user = auth()->user();
                $filename = 'my_progress_reports_' . date('Y-m-d_H-i-s') . '.csv';
                
                $headers = [
                    'Content-Type' => 'text/csv',
                    'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                ];
                
                $callback = function() use ($request, $user) {
                    $file = fopen('php://output', 'w');
                    
                    // CSV headers
                    fputcsv($file, [
                        'ID', 'Title', 'Client', 'Project', 'Status', 'Created At', 
                        'Sent At', 'Views', 'Has Attachment', 'Description'
                    ]);
                    
                    // Build query with filters - PM only sees their own reports
                    $query = ProgressReport::with(['client', 'project'])
                        ->where('created_by', $user->id);
                    
                    // Apply filters from request
                    if ($request->filled('status')) {
                        $query->where('status', $request->status);
                    }
                    if ($request->filled('client_id')) {
                        $query->where('client_id', $request->client_id);
                    }
                    if ($request->filled('project_id')) {
                        // Ensure PM can only export from their own projects
                        $project = Project::find($request->project_id);
                        if ($project && $project->created_by === $user->id) {
                            $query->where('project_id', $request->project_id);
                        }
                    }
                    if ($request->filled('date_from')) {
                        $query->whereDate('created_at', '>=', $request->date_from);
                    }
                    if ($request->filled('date_to')) {
                        $query->whereDate('created_at', '<=', $request->date_to);
                    }
                    
                    // Export data
                    $query->orderBy('created_at', 'desc')->chunk(1000, function($reports) use ($file) {
                        foreach ($reports as $report) {
                            fputcsv($file, [
                                $report->id,
                                $report->title,
                                $report->client->first_name . ' ' . $report->client->last_name,
                                $report->project ? $report->project->name : 'General',
                                ucfirst($report->status),
                                $report->created_at ? $report->created_at->format('M d, Y g:i A') : '',
                                $report->sent_at ? $report->sent_at->format('M d, Y g:i A') : '',
                                $report->view_count,
                                $report->hasAttachment() ? 'Yes' : 'No',
                                strip_tags($report->description)
                            ]);
                        }
                    });
                    
                    fclose($file);
                };
                
                return response()->stream($callback, 200, $headers);
            })->name('export');
        });

        // ====================================================================
        // NEW: PM-SPECIFIC TASK REPORT MANAGEMENT ROUTES
        // ====================================================================
        Route::prefix('task-reports')->name('task-reports.')->group(function () {
            // PM can view all task reports from their projects
            Route::get('/', [TaskReportController::class, 'adminIndex'])->name('index');
            Route::get('/{taskReport}', [TaskReportController::class, 'adminShow'])->name('show');
            Route::patch('/{taskReport}/review', [TaskReportController::class, 'updateReview'])->name('update-review');
            Route::delete('/{taskReport}', [TaskReportController::class, 'destroy'])->name('destroy');
            
            // PM dashboard and statistics
            Route::get('/dashboard/overview', [TaskReportController::class, 'pmDashboard'])->name('dashboard');
            
            // Quick status updates (AJAX)
            Route::post('/{taskReport}/quick-review', [TaskReportController::class, 'quickStatusUpdate'])->name('quick-review');
            
            // Bulk operations for PM
            Route::post('/bulk-approve', [TaskReportController::class, 'bulkApprove'])->name('bulk-approve');
            
            // API endpoints for PM dashboard
            Route::get('/api/stats', [TaskReportController::class, 'getStats'])->name('api.stats');
            Route::get('/api/recent', [TaskReportController::class, 'getRecentReports'])->name('api.recent');
            Route::get('/api/overdue', [TaskReportController::class, 'getOverdueReports'])->name('api.overdue');
            
            // Export functionality for PM
            Route::get('/export/csv', [TaskReportController::class, 'export'])->name('export');
            
            // Advanced search and filtering
            Route::get('/search/advanced', [TaskReportController::class, 'search'])->name('search');
            
            // Summary reports for PM
            Route::get('/reports/summary', [TaskReportController::class, 'getSummaryReport'])->name('summary-report');
            
            // Validation endpoint
            Route::post('/validate', [TaskReportController::class, 'validateReport'])->name('validate');
        });

        // ====================================================================
        // PM-SPECIFIC SITE ISSUE MANAGEMENT ROUTES
        // ====================================================================
        Route::prefix('site-issues')->name('site-issues.')->group(function () {
            // PM can view all site issues from their projects
            Route::get('/', [SiteIssueController::class, 'pmIndex'])->name('index');
            Route::get('/{siteIssue}', [SiteIssueController::class, 'pmShow'])->name('show');
            Route::get('/{siteIssue}/edit', [SiteIssueController::class, 'edit'])->name('edit');
            Route::put('/{siteIssue}', [SiteIssueController::class, 'update'])->name('update');
            Route::post('/{siteIssue}/comments', [SiteIssueController::class, 'addComment'])->name('add-comment');
            
            
            // API endpoints for PM dashboard
            Route::get('/api/stats', [SiteIssueController::class, 'getPMStats'])->name('api.stats');
            Route::get('/api/recent', [SiteIssueController::class, 'getPMRecentIssues'])->name('api.recent');
            
            // Export functionality for PM
            Route::get('/export/csv', [SiteIssueController::class, 'pmExport'])->name('export');
            
            // Quick actions for PM
            Route::post('/{siteIssue}/assign', function(\App\Models\SiteIssue $siteIssue, \Illuminate\Http\Request $request) {
                $user = auth()->user();
                
                // Ensure PM manages this project
                if (!$user->canManageProject($siteIssue->project_id)) {
                    abort(403);
                }
                
                $request->validate(['assigned_to' => 'required|exists:users,id']);
                
                 $siteIssue->update([
                    'assigned_to' => $request->assigned_to,
                    'status' => $siteIssue->status === 'open' ? 'in_progress' : $siteIssue->status,
                    'acknowledged_at' => now(),
                    'acknowledged_by' => $user->id
                ]);

                // Notify assigned user
                $assignedUser = User::find($request->assigned_to);
                if ($assignedUser) {
                    $assignedUser->notify(new \App\Notifications\SiteIssueAssigned($siteIssue));
                }

                return back()->with('success', 'Issue assigned successfully.');
            })->name('assign');

            Route::post('/{siteIssue}/acknowledge', function(\App\Models\SiteIssue $siteIssue) {
                $user = auth()->user();
                
                // Ensure PM manages this project
                if (!$user->canManageProject($siteIssue->project_id)) {
                    abort(403);
                }
                
                $siteIssue->update([
                    'acknowledged_at' => now(),
                    'acknowledged_by' => $user->id
                ]);
                
                return back()->with('success', 'Issue acknowledged.');
            })->name('acknowledge');

            Route::post('/{siteIssue}/resolve', function(\App\Models\SiteIssue $siteIssue, \Illuminate\Http\Request $request) {
                $user = auth()->user();
                
                // Ensure PM manages this project
                if (!$user->canManageProject($siteIssue->project_id)) {
                    abort(403);
                }
                
                $request->validate(['resolution_description' => 'required|string']);
                
                $siteIssue->update([
                    'status' => 'resolved',
                    'resolution_description' => $request->resolution_description,
                    'resolved_at' => now(),
                    'resolved_by' => $user->id
                ]);
                
                // Notify reporter
                if ($siteIssue->reporter) {
                    $siteIssue->reporter->notify(new \App\Notifications\SiteIssueUpdated($siteIssue, 'resolved'));
                }

                return back()->with('success', 'Issue marked as resolved.');
            })->name('resolve');
        });

    // ====================================================================
    // PM-SPECIFIC SITE PHOTOS MANAGEMENT ROUTES
    // ====================================================================
    Route::prefix('site-photos')->name('site-photos.')->group(function () {
        
        // Main PM site photos index - view all photos from managed projects
        Route::get('/', [SitePhotoController::class, 'pmIndex'])->name('index');

        // Export photos data
        Route::get('/export', function(\Illuminate\Http\Request $request) {
            $user = auth()->user();
            $managedProjectIds = $user->getManagedProjects()->pluck('id')->toArray();
            $filename = 'pm_site_photos_' . date('Y-m-d_H-i-s') . '.csv';
            
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ];
            
            $callback = function() use ($request, $managedProjectIds) {
                $file = fopen('php://output', 'w');
                
                // CSV headers
                fputcsv($file, [
                    'ID', 'Title', 'Project', 'Task', 'Uploader', 'Category', 
                    'Photo Date', 'Status', 'Submitted At', 'Reviewed At', 
                    'Rating', 'Featured', 'Public', 'File Size'
                ]);
                
                // Query with PM restrictions
                $query = \App\Models\SitePhoto::whereIn('project_id', $managedProjectIds)
                    ->with(['project', 'task', 'uploader']);
                
                // Apply filters
                if ($request->filled('status')) {
                    $query->where('submission_status', $request->status);
                }
                if ($request->filled('project_id') && in_array($request->project_id, $managedProjectIds)) {
                    $query->where('project_id', $request->project_id);
                }
                if ($request->filled('category')) {
                    $query->where('photo_category', $request->category);
                }
                
                // Export data
                $query->orderBy('photo_date', 'desc')->chunk(1000, function($photos) use ($file) {
                    foreach ($photos as $photo) {
                        fputcsv($file, [
                            $photo->id,
                            $photo->title,
                            $photo->project->name,
                            $photo->task ? $photo->task->task_name : '',
                            $photo->uploader->first_name . ' ' . $photo->uploader->last_name,
                            ucfirst($photo->photo_category),
                            $photo->photo_date ? $photo->photo_date->format('M d, Y') : '',
                            ucfirst($photo->submission_status),
                            $photo->submitted_at ? $photo->submitted_at->format('M d, Y g:i A') : '',
                            $photo->reviewed_at ? $photo->reviewed_at->format('M d, Y g:i A') : '',
                            $photo->admin_rating ?? '',
                            $photo->is_featured ? 'Yes' : 'No',
                            $photo->is_public ? 'Yes' : 'No',
                            $photo->file_size ? number_format($photo->file_size / 1024, 2) . ' KB' : ''
                        ]);
                    }
                });
                
                fclose($file);
            };
            
            return response()->stream($callback, 200, $headers);
        })->name('export');
        
        // Show individual photo with PM management options
        Route::get('/{sitePhoto}', [SitePhotoController::class, 'pmShow'])->name('show');
        
        // Photo review and approval
        Route::patch('/{sitePhoto}/review', [SitePhotoController::class, 'updateReview'])->name('update-review');
        
        // Quick approve/reject actions
        Route::post('/{sitePhoto}/quick-approve', function(\App\Models\SitePhoto $sitePhoto, \Illuminate\Http\Request $request) {
            $user = auth()->user();
            
            // Ensure PM manages this project
            if (!$user->canManageProject($sitePhoto->project_id)) {
                abort(403);
            }
            
            if ($sitePhoto->submission_status !== 'submitted') {
                return back()->withErrors(['error' => 'Photo is not in submitted status.']);
            }
            
            $sitePhoto->update([
                'submission_status' => 'approved',
                'reviewed_by' => $user->id,
                'reviewed_at' => now(),
                'admin_comments' => $request->get('comments', 'Quick approved by PM'),
                'is_public' => $request->boolean('make_public', false),
                'is_featured' => $request->boolean('make_featured', false),
            ]);
            
            // Notify uploader
            try {
                $sitePhoto->uploader->notify(new \App\Notifications\SitePhotoApproved($sitePhoto, $user->full_name));
            } catch (\Exception $e) {
                Log::warning('Failed to send approval notification: ' . $e->getMessage());
            }
            
            return back()->with('success', 'Photo approved successfully.');
        })->name('quick-approve');
        
        Route::post('/{sitePhoto}/quick-reject', function(\App\Models\SitePhoto $sitePhoto, \Illuminate\Http\Request $request) {
            $user = auth()->user();
            
            // Ensure PM manages this project
            if (!$user->canManageProject($sitePhoto->project_id)) {
                abort(403);
            }
            
            $request->validate(['reason' => 'required|string|max:500']);
            
            if ($sitePhoto->submission_status !== 'submitted') {
                return back()->withErrors(['error' => 'Photo is not in submitted status.']);
            }
            
            $sitePhoto->update([
                'submission_status' => 'rejected',
                'reviewed_by' => $user->id,
                'reviewed_at' => now(),
                'rejection_reason' => $request->reason,
                'admin_comments' => $request->get('comments'),
            ]);
            
            // Notify uploader
            try {
                $sitePhoto->uploader->notify(new \App\Notifications\SitePhotoRejected($sitePhoto, $user->full_name));
            } catch (\Exception $e) {
                Log::warning('Failed to send rejection notification: ' . $e->getMessage());
            }
            
            return back()->with('success', 'Photo rejected successfully.');
        })->name('quick-reject');
        
        // Toggle featured status
        Route::post('/{sitePhoto}/toggle-feature', function(\App\Models\SitePhoto $sitePhoto, \Illuminate\Http\Request $request) {
            $user = auth()->user();
            
            // Ensure PM manages this project
            if (!$user->canManageProject($sitePhoto->project_id)) {
                abort(403);
            }
            
            $request->validate(['is_featured' => 'required|boolean']);
            
            $sitePhoto->update(['is_featured' => $request->boolean('is_featured')]);
            
            return response()->json([
                'success' => true,
                'message' => $request->boolean('is_featured') ? 'Photo marked as featured' : 'Photo unmarked as featured',
                'is_featured' => $sitePhoto->is_featured
            ]);
        })->name('toggle-feature');
        
        // Toggle public visibility
        Route::post('/{sitePhoto}/toggle-public', function(\App\Models\SitePhoto $sitePhoto, \Illuminate\Http\Request $request) {
            $user = auth()->user();
            
            // Ensure PM manages this project
            if (!$user->canManageProject($sitePhoto->project_id)) {
                abort(403);
            }
            
            $request->validate(['is_public' => 'required|boolean']);
            
            $sitePhoto->update(['is_public' => $request->boolean('is_public')]);
            
            return response()->json([
                'success' => true,
                'message' => $request->boolean('is_public') ? 'Photo made public' : 'Photo made private',
                'is_public' => $sitePhoto->is_public
            ]);
        })->name('toggle-public');
        
        // Add admin comments
        Route::post('/{sitePhoto}/comments', [SitePhotoController::class, 'addAdminComment'])->name('add-comment');
        
        // Bulk actions for photos
        Route::post('/bulk-action', [SitePhotoController::class, 'bulkAction'])->name('bulk-action');
        
        
        // API endpoint for PM dashboard stats
        Route::get('/api/stats', function() {
            $user = auth()->user();
            $managedProjectIds = $user->getManagedProjects()->pluck('id')->toArray();
            
            return response()->json([
                'total' => \App\Models\SitePhoto::whereIn('project_id', $managedProjectIds)->count(),
                'pending_review' => \App\Models\SitePhoto::whereIn('project_id', $managedProjectIds)->where('submission_status', 'submitted')->count(),
                'approved' => \App\Models\SitePhoto::whereIn('project_id', $managedProjectIds)->where('submission_status', 'approved')->count(),
                'rejected' => \App\Models\SitePhoto::whereIn('project_id', $managedProjectIds)->where('submission_status', 'rejected')->count(),
                'featured' => \App\Models\SitePhoto::whereIn('project_id', $managedProjectIds)->where('is_featured', true)->count(),
                'public' => \App\Models\SitePhoto::whereIn('project_id', $managedProjectIds)->where('is_public', true)->count(),
                'overdue_reviews' => \App\Models\SitePhoto::whereIn('project_id', $managedProjectIds)
                    ->where('submission_status', 'submitted')
                    ->where('submitted_at', '<', now()->subDays(3))
                    ->count(),
            ]);
        })->name('api.stats');
    });
    });

    // ====================================================================
    // ENHANCED ADMIN/PM TASK REPORT ROUTES
    // ====================================================================
    Route::middleware('role:admin,pm')->prefix('admin')->name('admin.')->group(function () {
        Route::prefix('task-reports')->name('task-reports.')->group(function () {
            Route::get('/', [TaskReportController::class, 'adminIndex'])->name('index');
            Route::get('/{taskReport}', [TaskReportController::class, 'adminShow'])->name('show');
            Route::patch('/{taskReport}/review', [TaskReportController::class, 'updateReview'])->name('update-review');
            Route::get('/export/csv', [TaskReportController::class, 'export'])->name('export');
            
            // Enhanced bulk operations
            Route::post('/bulk-action', [TaskReportController::class, 'bulkApprove'])->name('bulk-action');
            
            // Analytics and reporting endpoints
            Route::get('/analytics/overview', [TaskReportController::class, 'getStats'])->name('analytics.overview');
            Route::get('/analytics/trends', [TaskReportController::class, 'getSummaryReport'])->name('analytics.trends');
            
            // Advanced filtering and search
            Route::post('/filter', [TaskReportController::class, 'search'])->name('filter');
        });

        // Site Issue Routes for Admin/PM
        Route::prefix('site-issues')->name('site-issues.')->group(function () {
            Route::get('/', [SiteIssueController::class, 'adminIndex'])->name('index');
            Route::get('/{siteIssue}', [SiteIssueController::class, 'adminShow'])->name('show');
            Route::get('/{siteIssue}/edit', [SiteIssueController::class, 'edit'])->name('edit');
            Route::put('/{siteIssue}', [SiteIssueController::class, 'update'])->name('update');
            Route::post('/{siteIssue}/comments', [SiteIssueController::class, 'addComment'])->name('add-comment');
            
            // Quick actions
            Route::post('/{siteIssue}/assign', function(\App\Models\SiteIssue $siteIssue, \Illuminate\Http\Request $request) {
                $request->validate(['assigned_to' => 'required|exists:users,id']);
                
                $siteIssue->update([
                    'assigned_to' => $request->assigned_to,
                    'status' => $siteIssue->status === 'open' ? 'in_progress' : $siteIssue->status,
                    'acknowledged_at' => now(),
                    'acknowledged_by' => auth()->id()
                ]);

                // Notify assigned user
                $assignedUser = User::find($request->assigned_to);
                if ($assignedUser) {
                    $assignedUser->notify(new \App\Notifications\SiteIssueAssigned($siteIssue));
                }

                return back()->with('success', 'Issue assigned successfully.');
            })->name('assign');

            Route::post('/{siteIssue}/acknowledge', function(\App\Models\SiteIssue $siteIssue) {
                $siteIssue->update([
                    'acknowledged_at' => now(),
                    'acknowledged_by' => auth()->id()
                ]);
                return back()->with('success', 'Issue acknowledged.');
            })->name('acknowledge');

            Route::post('/{siteIssue}/resolve', function(\App\Models\SiteIssue $siteIssue, \Illuminate\Http\Request $request) {
                $request->validate(['resolution_description' => 'required|string']);
                
                $siteIssue->update([
                    'status' => 'resolved',
                    'resolution_description' => $request->resolution_description,
                    'resolved_at' => now(),
                    'resolved_by' => auth()->id()
                ]);
                
                // Notify reporter
                if ($siteIssue->reporter) {
                    $siteIssue->reporter->notify(new \App\Notifications\SiteIssueUpdated($siteIssue, 'resolved'));
                }

                return back()->with('success', 'Issue marked as resolved.');
            })->name('resolve');
        });

        // Site Photo Management Routes for Admin/PM
        Route::prefix('site-photos')->name('site-photos.')->group(function () {
            Route::get('/', [SitePhotoController::class, 'adminIndex'])->name('index');
            Route::get('/{sitePhoto}', [SitePhotoController::class, 'adminShow'])->name('show');
            Route::patch('/{sitePhoto}/review', [SitePhotoController::class, 'updateReview'])->name('update-review');
            Route::post('/{sitePhoto}/comments', [SitePhotoController::class, 'addAdminComment'])->name('add-comment');
            Route::delete('/{sitePhoto}', [SitePhotoController::class, 'destroy'])->name('destroy');
            
            // Bulk operations
            Route::post('/bulk-action', [SitePhotoController::class, 'bulkAction'])->name('bulk-action');
            Route::post('/bulk-delete', [SitePhotoController::class, 'bulkDelete'])->name('bulk-delete');
            
            // Quick actions via AJAX
            Route::post('/{sitePhoto}/toggle-feature', function(\App\Models\SitePhoto $sitePhoto, \Illuminate\Http\Request $request) {
                $request->validate(['is_featured' => 'required|boolean']);
                
                $sitePhoto->update(['is_featured' => $request->boolean('is_featured')]);
                
                return response()->json(['success' => true]);
            })->name('toggle-feature');
            
            Route::post('/{sitePhoto}/toggle-public', function(\App\Models\SitePhoto $sitePhoto, \Illuminate\Http\Request $request) {
                $request->validate(['is_public' => 'required|boolean']);
                
                $sitePhoto->update(['is_public' => $request->boolean('is_public')]);
                
                return response()->json(['success' => true]);
            })->name('toggle-public');
            
            // Export functionality
            Route::get('/export/csv', function(\Illuminate\Http\Request $request) {
                $filename = 'site_photos_' . date('Y-m-d_H-i-s') . '.csv';
                
                $headers = [
                    'Content-Type' => 'text/csv',
                    'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                ];
                
                $callback = function() use ($request) {
                    $file = fopen('php://output', 'w');
                    
                    // CSV headers
                    fputcsv($file, [
                        'ID', 'Title', 'Project', 'Task', 'Uploader', 'Category', 
                        'Photo Date', 'Location', 'Weather', 'Submission Status', 
                        'Submitted At', 'Reviewed At', 'Admin Rating', 'Is Featured', 
                        'Is Public', 'File Size', 'Tags'
                    ]);
                    
                    // Build query with filters
                    $query = \App\Models\SitePhoto::with(['project', 'task', 'uploader']);
                    
                    if ($request->filled('status')) {
                        $query->where('submission_status', $request->status);
                    }
                    if ($request->filled('project_id')) {
                        $query->where('project_id', $request->project_id);
                    }
                    if ($request->filled('category')) {
                        $query->where('photo_category', $request->category);
                    }
                    if ($request->filled('uploader_id')) {
                        $query->where('user_id', $request->uploader_id);
                    }
                    if ($request->filled('date_from')) {
                        $query->where('photo_date', '>=', $request->date_from);
                    }
                    if ($request->filled('date_to')) {
                        $query->where('photo_date', '<=', $request->date_to);
                    }
                    
                    // Export data
                    $query->orderBy('photo_date', 'desc')->chunk(1000, function($photos) use ($file) {
                        foreach ($photos as $photo) {
                            fputcsv($file, [
                                $photo->id,
                                $photo->title,
                                $photo->project->name,
                                $photo->task ? $photo->task->task_name : '',
                                $photo->uploader->first_name . ' ' . $photo->uploader->last_name,
                                ucfirst($photo->photo_category),
                                $photo->photo_date ? $photo->photo_date->format('M d, Y') : '',
                                $photo->location ?? '',
                                $photo->weather_conditions ? ucfirst($photo->weather_conditions) : '',
                                ucfirst($photo->submission_status),
                                $photo->submitted_at ? $photo->submitted_at->format('M d, Y g:i A') : '',
                                $photo->reviewed_at ? $photo->reviewed_at->format('M d, Y g:i A') : '',
                                $photo->admin_rating ?? '',
                                $photo->is_featured ? 'Yes' : 'No',
                                $photo->is_public ? 'Yes' : 'No',
                                $photo->file_size ? number_format($photo->file_size / 1024, 2) . ' KB' : '',
                                $photo->tags ? implode(', ', $photo->tags) : ''
                            ]);
                        }
                    });
                    
                    fclose($file);
                };
                
                return response()->stream($callback, 200, $headers);
            })->name('export');
        });

        // Progress Reports Routes for Admin/PM
        Route::prefix('progress-reports')->name('progress-reports.')->group(function () {
            Route::get('/', [ProgressReportController::class, 'index'])->name('index');
            Route::get('/create', [ProgressReportController::class, 'create'])->name('create');
            Route::post('/', [ProgressReportController::class, 'store'])->name('store');
            Route::get('/{progressReport}', [ProgressReportController::class, 'show'])->name('show');
            Route::get('/{progressReport}/edit', function(ProgressReport $progressReport) {
                $user = auth()->user();
                
                // PMs can only edit reports they created, admins can edit all
                if ($user->role === 'pm' && $progressReport->created_by !== $user->id) {
                    abort(403, 'You can only edit progress reports you created.');
                }
                
                $clients = User::where('role', 'client')->where('status', 'active')->orderBy('first_name')->get();
                $projects = Project::active()->orderBy('name')->get();
                
                // For PMs, limit projects to those they can manage
                if ($user->role === 'pm') {
                    $projects = $projects->filter(function($project) use ($user) {
                        return $user->canManageProject($project->id);
                    });
                }
                
                return view('admin.progress-reports.edit', compact('progressReport', 'clients', 'projects'));
            })->name('edit');
            Route::put('/{progressReport}', [ProgressReportController::class, 'update'])->name('update');
            Route::delete('/{progressReport}', [ProgressReportController::class, 'destroy'])->name('destroy');
            Route::get('/{progressReport}/download', [ProgressReportController::class, 'downloadAttachment'])->name('download-attachment');
            
            // Bulk actions
            Route::post('/bulk-action', function(\Illuminate\Http\Request $request) {
                $user = auth()->user();
                $action = $request->input('action');
                $ids = explode(',', $request->input('ids'));
                
                $query = ProgressReport::whereIn('id', $ids);
                
                // PMs can only perform bulk actions on their own reports
                if ($user->role === 'pm') {
                    $query->where('created_by', $user->id);
                }
                
                $reports = $query->get();
                
                if ($reports->isEmpty()) {
                    return back()->withErrors(['error' => 'No reports found or you do not have permission to modify these reports.']);
                }
                
                switch ($action) {
                    case 'mark-viewed':
                        $reports->each(function($report) {
                            $report->update(['status' => 'viewed']);
                        });
                        $message = 'Reports marked as viewed successfully.';
                        break;
                        
                    case 'archive':
                        $reports->each(function($report) {
                            $report->update(['status' => 'archived']);
                        });
                        $message = 'Reports archived successfully.';
                        break;
                        
                    case 'delete':
                        $reports->each(function($report) {
                            $report->delete();
                        });
                        $message = 'Reports deleted successfully.';
                        break;
                        
                    default:
                        return back()->withErrors(['error' => 'Invalid action.']);
                }
                
                return back()->with('success', $message);
            })->name('bulk-action');
            
            // AJAX endpoints
            Route::get('/api/stats', [ProgressReportController::class, 'getReportStats'])->name('api.stats');
            
            // Export functionality
            Route::get('/export/csv', function(\Illuminate\Http\Request $request) {
                $user = auth()->user();
                $filename = 'progress_reports_' . $user->role . '_' . date('Y-m-d_H-i-s') . '.csv';
                
                $headers = [
                    'Content-Type' => 'text/csv',
                    'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                ];
                
                $callback = function() use ($request, $user) {
                    $file = fopen('php://output', 'w');
                    
                    // CSV headers
                    fputcsv($file, [
                        'ID', 'Title', 'Client', 'Project', 'Creator', 'Creator Role', 
                        'Status', 'Created At', 'Sent At', 'Views', 'Has Attachment', 
                        'Attachment Name', 'File Size', 'Description'
                    ]);
                    
                    // Build query with filters and permissions
                    $query = ProgressReport::with(['client', 'project', 'creator']);
                    
                    // PMs can only export their own reports
                    if ($user->role === 'pm') {
                        $query->where('created_by', $user->id);
                    }
                    
                    // Apply filters from request
                    if ($request->filled('status')) {
                        $query->where('status', $request->status);
                    }
                    if ($request->filled('client_id')) {
                        $query->where('client_id', $request->client_id);
                    }
                    if ($request->filled('project_id')) {
                        $query->where('project_id', $request->project_id);
                    }
                    if ($request->filled('creator_role') && $user->role === 'admin') {
                        $query->where('created_by_role', $request->creator_role);
                    }
                    if ($request->filled('date_from')) {
                        $query->whereDate('created_at', '>=', $request->date_from);
                    }
                    if ($request->filled('date_to')) {
                        $query->whereDate('created_at', '<=', $request->date_to);
                    }
                    
                    // Export data
                    $query->orderBy('created_at', 'desc')->chunk(1000, function($reports) use ($file) {
                        foreach ($reports as $report) {
                            fputcsv($file, [
                                $report->id,
                                $report->title,
                                $report->client->first_name . ' ' . $report->client->last_name,
                                $report->project ? $report->project->name : 'General',
                                $report->creator->first_name . ' ' . $report->creator->last_name,
                                $report->formatted_creator_role,
                                ucfirst($report->status),
                                $report->created_at ? $report->created_at->format('M d, Y g:i A') : '',
                                $report->sent_at ? $report->sent_at->format('M d, Y g:i A') : '',
                                $report->view_count,
                                $report->hasAttachment() ? 'Yes' : 'No',
                                $report->original_filename ?? '',
                                $report->file_size ? number_format($report->file_size / 1024, 2) . ' KB' : '',
                                strip_tags($report->description)
                            ]);
                        }
                    });
                    
                    fclose($file);
                };
                
                return response()->stream($callback, 200, $headers);
            })->name('export');
        });
    });

    // Client Progress Reports Routes
    Route::middleware('role:client')->prefix('client')->name('client.')->group(function () {
        Route::prefix('reports')->name('reports.')->group(function () {
            Route::get('/', [ProgressReportController::class, 'clientIndex'])->name('index');
            Route::get('/{progressReport}', [ProgressReportController::class, 'clientShow'])->name('show');
            Route::get('/{progressReport}/download', [ProgressReportController::class, 'clientDownloadAttachment'])->name('download-attachment');
            Route::post('/mark-all-read', [ProgressReportController::class, 'markAllAsRead'])->name('mark-all-read');
            Route::get('/export/csv', [ProgressReportController::class, 'exportClientReports'])->name('export');
        });
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

    // ====================================================================
    // ENHANCED NOTIFICATION ROUTES FOR TASK REPORTS AND SITE ISSUES
    // ====================================================================
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [TaskController::class, 'notifications'])->name('index');
        Route::post('/{id}/mark-read', [TaskController::class, 'markNotificationAsRead'])->name('mark.read');
        Route::post('/mark-all-read', [TaskController::class, 'markAllNotificationsAsRead'])->name('mark.all.read');
        Route::delete('/{id}', [TaskController::class, 'deleteNotification'])->name('delete');
        
        // Enhanced task report specific notifications
        Route::get('/task-reports', [TaskReportController::class, 'notifications'])->name('task-reports');
        Route::post('/task-reports/{id}/mark-read', [TaskReportController::class, 'markTaskReportNotificationRead'])->name('task-reports.mark-read');
        
        // Site issue specific notifications
        Route::get('/site-issues', function() {
            $user = auth()->user();
            $notifications = $user->notifications()
                ->whereIn('type', [
                    'App\Notifications\SiteIssueReported',
                    'App\Notifications\SiteIssueUpdated',
                    'App\Notifications\SiteIssueAssigned'
                ])
                ->orderBy('created_at', 'desc')
                ->paginate(20);
                
            return view('notifications.site-issues', compact('notifications'));
        })->name('site-issues');

        Route::post('/site-issues/{id}/mark-read', function($id) {
            $notification = auth()->user()->notifications()->findOrFail($id);
            $notification->markAsRead();
            
            return response()->json(['success' => true]);
        })->name('site-issues.mark-read');

        // Site photo specific notifications
        Route::get('/site-photos', function() {
            $user = auth()->user();
            $notifications = $user->notifications()
                ->whereIn('type', [
                    'App\Notifications\SitePhotoSubmitted',
                    'App\Notifications\SitePhotoApproved', 
                    'App\Notifications\SitePhotoRejected',
                    'App\Notifications\SitePhotoCommentAdded'
                ])
                ->orderBy('created_at', 'desc')
                ->paginate(20);
                
            return view('notifications.site-photos', compact('notifications'));
        })->name('site-photos');
        
        Route::post('/site-photos/{id}/mark-read', function($id) {
            $notification = auth()->user()->notifications()->findOrFail($id);
            $notification->markAsRead();
            
            return response()->json(['success' => true]);
        })->name('site-photos.mark-read');

        // Progress Reports specific notifications
        Route::get('/progress-reports', function() {
            $user = auth()->user();
            $notifications = $user->notifications()
                ->where('type', 'App\Notifications\ProgressReportShared')
                ->orderBy('created_at', 'desc')
                ->paginate(20);
                
            return view('notifications.progress-reports', compact('notifications'));
        })->name('progress-reports');

        Route::post('/progress-reports/{id}/mark-read', function($id) {
            $notification = auth()->user()->notifications()->findOrFail($id);
            $notification->markAsRead();
            
            return response()->json(['success' => true]);
        })->name('progress-reports.mark-read');
    });

    Route::get('/activities', [ActivityController::class, 'index'])->name('activity.index');
    
    // Equipment Inventory Routes
    Route::prefix('equipment')->name('equipment.')->middleware('role:admin,emp')->group(function () {
        // Main equipment routes
        Route::get('/', [EquipmentController::class, 'index'])->name('index');
        Route::get('/create', [EquipmentController::class, 'create'])->name('create');
        Route::post('/', [EquipmentController::class, 'store'])->name('store');
        Route::get('/{id}', [EquipmentController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [EquipmentController::class, 'edit'])->name('edit');
        Route::put('/{id}', [EquipmentController::class, 'update'])->name('update');
        
        // Archive and restore
        Route::post('/{id}/archive', [EquipmentController::class, 'archive'])->name('archive');
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

    // Public photo viewing routes for all authenticated users
    Route::middleware('auth')->group(function () {
        // Featured photos showcase
        Route::get('/photos/featured', function() {
            $photos = \App\Models\SitePhoto::where('is_featured', true)
                ->where('is_public', true)
                ->where('submission_status', 'approved')
                ->with(['project', 'uploader'])
                ->orderBy('photo_date', 'desc')
                ->paginate(16);
            
            return view('photos.featured', compact('photos'));
        })->name('photos.featured');

        // Photo gallery by category
        Route::get('/photos/category/{category}', function($category) {
            $validCategories = ['progress', 'quality', 'safety', 'equipment', 'materials', 'workers', 'documentation', 'issues', 'completion', 'other'];
            
            if (!in_array($category, $validCategories)) {
                abort(404);
            }

            $photos = \App\Models\SitePhoto::where('photo_category', $category)
                ->where('is_public', true)
                ->where('submission_status', 'approved')
                ->with(['project', 'uploader'])
                ->orderBy('photo_date', 'desc')
                ->paginate(12);
            
            $categoryName = ucfirst($category);
            
            return view('photos.category', compact('photos', 'category', 'categoryName'));
        })->name('photos.category');

        // Search photos
        Route::get('/photos/search', function(\Illuminate\Http\Request $request) {
            $query = $request->get('q');
            $project = $request->get('project');
            $category = $request->get('category');
            
            $photosQuery = \App\Models\SitePhoto::where('is_public', true)
                ->where('submission_status', 'approved')
                ->with(['project', 'uploader']);
            
            if ($query) {
                $photosQuery->where(function($q) use ($query) {
                    $q->where('title', 'like', "%{$query}%")
                      ->orWhere('description', 'like', "%{$query}%")
                      ->orWhereJsonContains('tags', $query);
                });
            }
            
            if ($project) {
                $photosQuery->where('project_id', $project);
            }
            
            if ($category) {
                $photosQuery->where('photo_category', $category);
            }
            
            $photos = $photosQuery->orderBy('photo_date', 'desc')->paginate(12);
            
            $projects = Project::orderBy('name')->get();
            
            return view('photos.search', compact('photos', 'query', 'project', 'category', 'projects'));
        })->name('photos.search');

        // Individual photo view
        Route::get('/photos/{sitePhoto}', function(\App\Models\SitePhoto $sitePhoto) {
            // Check if photo is public and approved, or if user has permission
            $user = auth()->user();
            
            if (!$sitePhoto->is_public || $sitePhoto->submission_status !== 'approved') {
                // Check if user has permission to view non-public photos
                if (!in_array($user->role, ['admin', 'pm'])) {
                    if ($user->role === 'sc' && $sitePhoto->user_id !== $user->id) {
                        abort(403);
                    } elseif (!in_array($user->role, ['admin', 'pm', 'sc'])) {
                        abort(403);
                    }
                }
            }
            
            $sitePhoto->load(['project', 'task', 'uploader', 'comments.user']);
            
            // Get related photos from the same project
            $relatedPhotos = \App\Models\SitePhoto::where('project_id', $sitePhoto->project_id)
                ->where('id', '!=', $sitePhoto->id)
                ->where('is_public', true)
                ->where('submission_status', 'approved')
                ->orderBy('photo_date', 'desc')
                ->take(6)
                ->get();
            
            return view('photos.show', compact('sitePhoto', 'relatedPhotos'));
        })->name('photos.show');
    });
});

// ====================================================================
// EQUIPMENT MONITORING ROUTES 
// ====================================================================

Route::middleware(['auth', 'verified'])->group(function () {
    
    // ====================================================================
    // ADMIN EQUIPMENT MONITORING ROUTES
    // ====================================================================
Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
    Route::prefix('equipment-monitoring')->name('equipment-monitoring.')->group(function () {
        
        // Main personal equipment dashboard 
        Route::get('/my-dashboard', [EquipmentMonitoringController::class, 'adminMyDashboard'])->name('my-dashboard');
        // Create new equipment request
        Route::get('/create-request', [EquipmentMonitoringController::class, 'adminCreateRequest'])->name('create-request');
        // Store equipment request 
        Route::post('/requests', [EquipmentMonitoringController::class, 'adminStoreRequest'])->name('store-request');
        // View all admin's equipment requests 
        Route::get('/my-requests', [EquipmentMonitoringController::class, 'adminMyRequests'])->name('my-requests');
        // Show specific equipment request
        Route::get('/my-requests/{equipmentRequest}', function(App\Models\EquipmentRequest $equipmentRequest) {
            // Verify ownership - admin can only view their own requests
            if ($equipmentRequest->user_id !== auth()->id()) {
                abort(403, 'Access denied.');
            }
            
            $equipmentRequest->load(['monitoredEquipment', 'project', 'approvedBy']);
            return view('admin.equipment-monitoring.show-my-request', compact('equipmentRequest'));
        })->name('show-my-request');
        
        // ============================================================
        // ADMIN EQUIPMENT MANAGEMENT (Personal Equipment)
        // ============================================================

        Route::get('/my-equipment', [EquipmentMonitoringController::class, 'adminMyEquipment'])->name('my-equipment');
        Route::get('/my-equipment/{monitoredEquipment}', function(App\Models\MonitoredEquipment $monitoredEquipment) {
            // Verify ownership - admin can only view their own equipment
            if ($monitoredEquipment->user_id !== auth()->id()) {
                abort(403, 'Access denied.');
            }
            
            $monitoredEquipment->load(['project', 'equipmentRequest', 'maintenanceSchedules']);
            return view('admin.equipment-monitoring.show-my-equipment', compact('monitoredEquipment'));
        })->name('show-my-equipment');
        
        // Update equipment availability status (mirrors sc.equipment-monitoring.update-availability)
        Route::post('/my-equipment/{monitoredEquipment}/availability', [EquipmentMonitoringController::class, 'adminUpdateAvailability'])->name('update-my-availability');
        
        // ============================================================
        // ADMIN MAINTENANCE MANAGEMENT (Personal Equipment)
        // ============================================================
        Route::get('/my-maintenance', [EquipmentMonitoringController::class, 'adminMyMaintenance'])->name('my-maintenance');
        Route::get('/create-maintenance', [EquipmentMonitoringController::class, 'adminCreateMaintenance'])->name('create-maintenance');
        Route::post('/maintenance', [EquipmentMonitoringController::class, 'adminStoreMaintenance'])->name('store-maintenance');
        Route::get('/my-maintenance/{equipmentMaintenance}', function(App\Models\EquipmentMaintenance $equipmentMaintenance) {
            // Verify ownership through equipment
            if ($equipmentMaintenance->monitoredEquipment->user_id !== auth()->id()) {
                abort(403, 'Access denied.');
            }
            
            $equipmentMaintenance->load(['monitoredEquipment.project', 'performedBy']);
            return view('admin.equipment-monitoring.show-my-maintenance', compact('equipmentMaintenance'));
        })->name('show-my-maintenance');
        
        // ============================================================
        // AJAX ENDPOINTS FOR ADMIN PERSONAL EQUIPMENT
        // ============================================================
        
        // Get admin's equipment for specific project (mirrors sc.equipment-monitoring.ajax.project-equipment)
        Route::get('/ajax/my-project-equipment', function(Illuminate\Http\Request $request) {
            $projectId = $request->get('project_id');
            $user = auth()->user();
            
            if (!$projectId) {
                return response()->json(['error' => 'Project ID required'], 400);
            }
            
            // Admin has access to all projects, so no need to verify access
            $equipment = \App\Models\MonitoredEquipment::where('user_id', $user->id)
                ->where('project_id', $projectId)
                ->where('status', 'active')
                ->get(['id', 'equipment_name', 'availability_status']);
                
            return response()->json($equipment);
        })->name('ajax.my-project-equipment');

        // ============================================================
        // SYSTEM-WIDE EQUIPMENT MONITORING MANAGEMENT (Admin Only)
        // ============================================================
              
        // Main admin management dashboard for all equipment 
        Route::get('/', [EquipmentMonitoringController::class, 'adminIndex'])->name('index');
        
        // Equipment requests management 
        Route::get('/requests', [EquipmentMonitoringController::class, 'adminIndex'])->name('requests');
        Route::get('/requests/{equipmentRequest}', [EquipmentMonitoringController::class, 'adminShowRequest'])->name('show-request');
        Route::post('/requests/{equipmentRequest}/approve', [EquipmentMonitoringController::class, 'approveRequest'])->name('approve-request');
        Route::post('/requests/{equipmentRequest}/decline', [EquipmentMonitoringController::class, 'declineRequest'])->name('decline-request');
        
        // System-wide equipment management
        Route::get('/equipment', [EquipmentMonitoringController::class, 'adminEquipmentList'])->name('equipment-list');
        Route::get('/equipment/{monitoredEquipment}', function(App\Models\MonitoredEquipment $monitoredEquipment) {
            $monitoredEquipment->load(['user', 'project', 'equipmentRequest', 'maintenanceSchedules']);
            return view('admin.equipment-monitoring.show-equipment', compact('monitoredEquipment'));
        })->name('show-equipment');
        
        // System-wide maintenance management 
        Route::get('/maintenance', [EquipmentMonitoringController::class, 'adminMaintenanceList'])->name('maintenance-list');
        Route::get('/maintenance/{equipmentMaintenance}', function(App\Models\EquipmentMaintenance $equipmentMaintenance) {
            $equipmentMaintenance->load(['monitoredEquipment.user', 'monitoredEquipment.project', 'performedBy']);
            return view('admin.equipment-monitoring.show-maintenance', compact('equipmentMaintenance'));
        })->name('show-maintenance');
        
        // System-wide maintenance status updates
        Route::post('/maintenance/{equipmentMaintenance}/complete', function(App\Models\EquipmentMaintenance $equipmentMaintenance, Illuminate\Http\Request $request) {
            $request->validate([
                'actual_duration' => 'nullable|integer|min:1|max:480',
                'cost' => 'nullable|numeric|min:0|max:999999.99',
                'completion_notes' => 'nullable|string|max:1000',
            ]);
            
            $equipmentMaintenance->markAsCompleted(
                auth()->id(),
                $request->actual_duration,
                $request->cost,
                $request->completion_notes
            );
            
            return back()->with('success', 'Maintenance marked as completed successfully.');
        })->name('complete-maintenance');
        
        Route::post('/maintenance/{equipmentMaintenance}/cancel', function(App\Models\EquipmentMaintenance $equipmentMaintenance, Illuminate\Http\Request $request) {
            $request->validate([
                'cancel_reason' => 'required|string|max:500',
            ]);
            
            $equipmentMaintenance->cancel($request->cancel_reason);
            
            return back()->with('success', 'Maintenance cancelled successfully.');
        })->name('cancel-maintenance');
        
        // Bulk operations 
        Route::post('/requests/bulk-action', function(Illuminate\Http\Request $request) {
            $request->validate([
                'action' => 'required|in:approve,decline',
                'request_ids' => 'required|array',
                'request_ids.*' => 'exists:equipment_requests,id',
                'bulk_notes' => 'nullable|string|max:1000',
                'bulk_decline_reason' => 'required_if:action,decline|string|max:1000',
            ]);
            
            $requests = \App\Models\EquipmentRequest::whereIn('id', $request->request_ids)
                ->where('status', 'pending')
                ->get();
            
            $successCount = 0;
            foreach ($requests as $equipmentRequest) {
                try {
                    if ($request->action === 'approve') {
                        $equipmentRequest->update([
                            'status' => 'approved',
                            'approved_by' => auth()->id(),
                            'approved_at' => now(),
                            'admin_notes' => $request->bulk_notes,
                        ]);
                        
                        if ($equipmentRequest->monitoredEquipment) {
                            $equipmentRequest->monitoredEquipment->update(['status' => 'active']);
                        }
                        
                        $equipmentRequest->user->notify(new \App\Notifications\EquipmentRequestApproved($equipmentRequest));
                    } else {
                        $equipmentRequest->update([
                            'status' => 'declined',
                            'approved_by' => auth()->id(),
                            'approved_at' => now(),
                            'decline_reason' => $request->bulk_decline_reason,
                        ]);
                        
                        if ($equipmentRequest->monitoredEquipment) {
                            $equipmentRequest->monitoredEquipment->update(['status' => 'declined']);
                        }
                        
                        $equipmentRequest->user->notify(new \App\Notifications\EquipmentRequestDeclined($equipmentRequest));
                    }
                    $successCount++;
                } catch (\Exception $e) {
                    Log::error('Bulk action failed for request ' . $equipmentRequest->id, ['error' => $e->getMessage()]);
                }
            }
            
            $action = $request->action === 'approve' ? 'approved' : 'declined';
            return back()->with('success', "{$successCount} equipment requests {$action} successfully.");
        })->name('bulk-action');
        
        // Reports and exports 
        Route::get('/reports/equipment-status', function(Illuminate\Http\Request $request) {
            $statusFilter = $request->get('status');
            $typeFilter = $request->get('usage_type');
            $dateFrom = $request->get('date_from');
            $dateTo = $request->get('date_to');
            
            $query = \App\Models\MonitoredEquipment::with(['user', 'project', 'equipmentRequest']);
            
            if ($statusFilter) $query->where('status', $statusFilter);
            if ($typeFilter) $query->where('usage_type', $typeFilter);
            if ($dateFrom) $query->whereDate('created_at', '>=', $dateFrom);
            if ($dateTo) $query->whereDate('created_at', '<=', $dateTo);
            
            $equipment = $query->orderBy('created_at', 'desc')->get();
            
            if ($request->get('export') === 'csv') {
                $filename = 'equipment_status_report_' . date('Y-m-d_H-i-s') . '.csv';
                
                $headers = [
                    'Content-Type' => 'text/csv',
                    'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                ];
                
                $callback = function() use ($equipment) {
                    $file = fopen('php://output', 'w');
                    fputcsv($file, [
                        'Equipment Name', 'Site Coordinator', 'Usage Type', 'Project', 
                        'Status', 'Availability', 'Quantity', 'Location', 'Created Date'
                    ]);
                    
                    foreach ($equipment as $item) {
                        fputcsv($file, [
                            $item->equipment_name,
                            $item->user->full_name,
                            $item->formatted_usage_type,
                            $item->project ? $item->project->name : 'N/A',
                            ucfirst($item->status),
                            ucfirst($item->availability_status),
                            $item->quantity,
                            $item->location ?: 'Not specified',
                            $item->created_at->format('M d, Y'),
                        ]);
                    }
                    fclose($file);
                };
                
                return response()->stream($callback, 200, $headers);
            }
            
            return view('admin.equipment-monitoring.reports.equipment-status', compact(
                'equipment', 'statusFilter', 'typeFilter', 'dateFrom', 'dateTo'
            ));
        })->name('report-equipment-status');
    });
});
    
    // ====================================================================
    // SITE COORDINATOR (SC) EQUIPMENT MONITORING ROUTES  
    // ====================================================================
    Route::middleware('role:sc')->prefix('sc/equipment-monitoring')->name('sc.')->group(function () {
        Route::prefix('equipment-monitoring')->name('equipment-monitoring.')->group(function () {
        // Main SC dashboard
        Route::get('/', [EquipmentMonitoringController::class, 'scIndex'])->name('index');
        
        // Equipment requests
        Route::get('/requests', [EquipmentMonitoringController::class, 'scRequests'])->name('requests');
        Route::get('/requests/create', [EquipmentMonitoringController::class, 'scCreateRequest'])->name('create-request');
        Route::post('/requests', [EquipmentMonitoringController::class, 'scStoreRequest'])->name('store-request');
        Route::get('/requests/{equipmentRequest}', function(App\Models\EquipmentRequest $equipmentRequest) {
            // Verify ownership
            if ($equipmentRequest->user_id !== auth()->id()) {
                abort(403, 'Access denied.');
            }
            
            $equipmentRequest->load(['monitoredEquipment', 'project', 'approvedBy']);
            return view('sc.equipment-monitoring.show-request', compact('equipmentRequest'));
        })->name('show-request');
        
        // Equipment management
        Route::get('/equipment', function(Illuminate\Http\Request $request) {
            $user = auth()->user();
            $statusFilter = $request->get('status');
            $typeFilter = $request->get('usage_type');
            
            $equipmentQuery = \App\Models\MonitoredEquipment::where('user_id', $user->id)
                ->with(['project', 'equipmentRequest'])
                ->orderBy('created_at', 'desc');
                
            if ($statusFilter) $equipmentQuery->where('status', $statusFilter);
            if ($typeFilter) $equipmentQuery->where('usage_type', $typeFilter);
            
            $equipment = $equipmentQuery->paginate(15);
            
            return view('sc.equipment-monitoring.equipment', compact('equipment', 'statusFilter', 'typeFilter'));
        })->name('equipment');
        
        Route::get('/equipment/{monitoredEquipment}', function(App\Models\MonitoredEquipment $monitoredEquipment) {
            // Verify ownership
            if ($monitoredEquipment->user_id !== auth()->id()) {
                abort(403, 'Access denied.');
            }
            
            $monitoredEquipment->load(['project', 'equipmentRequest', 'maintenanceSchedules']);
            return view('sc.equipment-monitoring.show-equipment', compact('monitoredEquipment'));
        })->name('show-equipment');
        
        // Equipment availability updates
        Route::post('/equipment/{monitoredEquipment}/availability', [EquipmentMonitoringController::class, 'scUpdateAvailability'])->name('update-availability');
        
        // Maintenance scheduling
        Route::get('/maintenance', [EquipmentMonitoringController::class, 'scMaintenance'])->name('maintenance');
        Route::get('/maintenance/create', [EquipmentMonitoringController::class, 'scCreateMaintenance'])->name('create-maintenance');
        Route::post('/maintenance', [EquipmentMonitoringController::class, 'scStoreMaintenance'])->name('store-maintenance');
        
        Route::get('/maintenance/{equipmentMaintenance}', function(App\Models\EquipmentMaintenance $equipmentMaintenance) {
            // Verify ownership through equipment
            if ($equipmentMaintenance->monitoredEquipment->user_id !== auth()->id()) {
                abort(403, 'Access denied.');
            }
            
            $equipmentMaintenance->load(['monitoredEquipment.project', 'performedBy']);
            return view('sc.equipment-monitoring.show-maintenance', compact('equipmentMaintenance'));
        })->name('show-maintenance');
        
        // AJAX endpoints
        Route::get('/ajax/project-equipment', function(Illuminate\Http\Request $request) {
            $projectId = $request->get('project_id');
            $user = auth()->user();
            
            if (!$projectId) {
                return response()->json(['error' => 'Project ID required'], 400);
            }
            
            // Verify SC has access to this project
            $hasAccess = Task::where('assigned_to', $user->id)
                ->where('project_id', $projectId)
                ->exists();
                
            if (!$hasAccess) {
                return response()->json(['error' => 'Access denied'], 403);
            }
            
            $equipment = \App\Models\MonitoredEquipment::where('user_id', $user->id)
                ->where('project_id', $projectId)
                ->where('status', 'active')
                ->whereHas('equipmentRequest', function($q) {
                    $q->where('status', 'approved');
                })
                ->get(['id', 'equipment_name', 'availability_status']);
                
            return response()->json($equipment);
        })->name('ajax.project-equipment');
    });
});
    
    // ====================================================================
    // PROJECT MANAGER (PM) EQUIPMENT MONITORING ROUTES - VIEW ONLY
    // ====================================================================
    Route::middleware('role:pm')->prefix('pm/equipment-monitoring')->name('pm.')->group(function () {
        Route::prefix('equipment-monitoring')->name('equipment-monitoring.')->group(function () {
        // Main PM dashboard - view only
        Route::get('/', [EquipmentMonitoringController::class, 'pmIndex'])->name('index');
        
        // Equipment lists - view only
        Route::get('/equipment', [EquipmentMonitoringController::class, 'pmEquipmentList'])->name('equipment-list');
        Route::get('/equipment/{monitoredEquipment}', function(App\Models\MonitoredEquipment $monitoredEquipment) {
            $user = auth()->user();
            $managedProjectIds = $user->getManagedProjects()->pluck('id')->toArray();
            
            // Verify PM has access to this equipment's project
            if ($monitoredEquipment->project_id && !in_array($monitoredEquipment->project_id, $managedProjectIds)) {
                abort(403, 'Access denied.');
            }
            
            $monitoredEquipment->load(['user', 'project', 'equipmentRequest', 'maintenanceSchedules']);
            return view('pm.equipment-monitoring.show-equipment', compact('monitoredEquipment'));
        })->name('show-equipment');
        
        // Maintenance schedules - view only
        Route::get('/maintenance', [EquipmentMonitoringController::class, 'pmMaintenanceList'])->name('maintenance-list');
        Route::get('/maintenance/{equipmentMaintenance}', function(App\Models\EquipmentMaintenance $equipmentMaintenance) {
            $user = auth()->user();
            $managedProjectIds = $user->getManagedProjects()->pluck('id')->toArray();
            
            // Verify PM has access to this maintenance's project
            if ($equipmentMaintenance->monitoredEquipment->project_id && 
                !in_array($equipmentMaintenance->monitoredEquipment->project_id, $managedProjectIds)) {
                abort(403, 'Access denied.');
            }
            
            $equipmentMaintenance->load(['monitoredEquipment.user', 'monitoredEquipment.project', 'performedBy']);
            return view('pm.equipment-monitoring.show-maintenance', compact('equipmentMaintenance'));
        })->name('show-maintenance');
        
        // Equipment requests - view only
        Route::get('/requests', function(Illuminate\Http\Request $request) {
            $user = auth()->user();
            $managedProjectIds = $user->getManagedProjects()->pluck('id')->toArray();
            
            $statusFilter = $request->get('status');
            $projectFilter = $request->get('project_id');
            
            $requestsQuery = \App\Models\EquipmentRequest::with(['user', 'project', 'monitoredEquipment'])
                ->whereIn('project_id', $managedProjectIds)
                ->orderBy('created_at', 'desc');
                
            if ($statusFilter) $requestsQuery->where('status', $statusFilter);
            if ($projectFilter && in_array($projectFilter, $managedProjectIds)) {
                $requestsQuery->where('project_id', $projectFilter);
            }
            
            $equipmentRequests = $requestsQuery->paginate(15);
            $managedProjects = $user->getManagedProjects();
            
            return view('pm.equipment-monitoring.requests', compact(
                'equipmentRequests', 'managedProjects', 'statusFilter', 'projectFilter'
            ));
        })->name('requests');
        
        Route::get('/requests/{equipmentRequest}', function(App\Models\EquipmentRequest $equipmentRequest) {
            $user = auth()->user();
            $managedProjectIds = $user->getManagedProjects()->pluck('id')->toArray();
            
            // Verify PM has access to this request's project
            if ($equipmentRequest->project_id && !in_array($equipmentRequest->project_id, $managedProjectIds)) {
                abort(403, 'Access denied.');
            }
            
            $equipmentRequest->load(['user', 'project', 'monitoredEquipment', 'approvedBy']);
            return view('pm.equipment-monitoring.show-request', compact('equipmentRequest'));
        })->name('show-request');
        
        // Reports - view only
        Route::get('/reports/summary', function(Illuminate\Http\Request $request) {
            $user = auth()->user();
            $managedProjectIds = $user->getManagedProjects()->pluck('id')->toArray();
            
            $dateFrom = $request->get('date_from', now()->startOfMonth());
            $dateTo = $request->get('date_to', now()->endOfMonth());
            
            // Get statistics for managed projects
            $stats = [
                'total_equipment' => \App\Models\MonitoredEquipment::whereIn('project_id', $managedProjectIds)->count(),
                'active_equipment' => \App\Models\MonitoredEquipment::whereIn('project_id', $managedProjectIds)->where('status', 'active')->count(),
                'pending_requests' => \App\Models\EquipmentRequest::whereIn('project_id', $managedProjectIds)->where('status', 'pending')->count(),
                'scheduled_maintenance' => \App\Models\EquipmentMaintenance::whereHas('monitoredEquipment', function($q) use ($managedProjectIds) {
                    $q->whereIn('project_id', $managedProjectIds);
                })->where('status', 'scheduled')->count(),
            ];
            
            $managedProjects = $user->getManagedProjects();
            
            return view('pm.equipment-monitoring.report-summary', compact('stats', 'managedProjects', 'dateFrom', 'dateTo'));
        })->name('report-summary');

            // API endpoint for PM dashboard stats (add this to existing PM equipment monitoring routes)
    Route::get('/api/stats', function() {
        $user = auth()->user();
        $managedProjectIds = $user->getManagedProjects()->pluck('id')->toArray();
        
        return response()->json([
            // Equipment Request Statistics
            'total_requests' => \App\Models\EquipmentRequest::whereIn('project_id', $managedProjectIds)->count(),
            'pending_requests' => \App\Models\EquipmentRequest::whereIn('project_id', $managedProjectIds)->where('status', 'pending')->count(),
            'approved_requests' => \App\Models\EquipmentRequest::whereIn('project_id', $managedProjectIds)->where('status', 'approved')->count(),
            'declined_requests' => \App\Models\EquipmentRequest::whereIn('project_id', $managedProjectIds)->where('status', 'declined')->count(),
            
            // Monitored Equipment Statistics
            'total_equipment' => \App\Models\MonitoredEquipment::whereIn('project_id', $managedProjectIds)->count(),
            'active_equipment' => \App\Models\MonitoredEquipment::whereIn('project_id', $managedProjectIds)->where('status', 'active')->count(),
            'pending_equipment' => \App\Models\MonitoredEquipment::whereIn('project_id', $managedProjectIds)->where('status', 'pending_approval')->count(),
            'personal_equipment' => \App\Models\MonitoredEquipment::where('usage_type', 'personal')
                ->whereHas('user.tasks', function($q) use ($managedProjectIds) {
                    $q->whereIn('project_id', $managedProjectIds);
                })->count(),
            
            // Equipment availability in managed projects
            'equipment_available' => \App\Models\MonitoredEquipment::whereIn('project_id', $managedProjectIds)->where('availability_status', 'available')->count(),
            'equipment_in_use' => \App\Models\MonitoredEquipment::whereIn('project_id', $managedProjectIds)->where('availability_status', 'in_use')->count(),
            'equipment_maintenance' => \App\Models\MonitoredEquipment::whereIn('project_id', $managedProjectIds)->where('availability_status', 'maintenance')->count(),
            'equipment_out_of_order' => \App\Models\MonitoredEquipment::whereIn('project_id', $managedProjectIds)->where('availability_status', 'out_of_order')->count(),
            
            // Maintenance Statistics
            'maintenance_scheduled' => \App\Models\EquipmentMaintenance::whereHas('monitoredEquipment', function($q) use ($managedProjectIds) {
                $q->whereIn('project_id', $managedProjectIds);
            })->where('status', 'scheduled')->count(),
            'maintenance_overdue' => \App\Models\EquipmentMaintenance::whereHas('monitoredEquipment', function($q) use ($managedProjectIds) {
                $q->whereIn('project_id', $managedProjectIds);
            })->where('status', 'scheduled')->where('scheduled_date', '<', now())->count(),
            'maintenance_this_week' => \App\Models\EquipmentMaintenance::whereHas('monitoredEquipment', function($q) use ($managedProjectIds) {
                $q->whereIn('project_id', $managedProjectIds);
            })->where('status', 'scheduled')->whereBetween('scheduled_date', [now(), now()->addDays(7)])->count(),
            
            // Recent activity
            'recent_requests' => \App\Models\EquipmentRequest::whereIn('project_id', $managedProjectIds)->where('created_at', '>=', now()->subDays(7))->count(),
            'urgent_requests' => \App\Models\EquipmentRequest::whereIn('project_id', $managedProjectIds)->where('status', 'pending')
                ->whereIn('urgency_level', ['high', 'critical'])->count(),
        ]);
    })->name('api.stats');
    
    // API endpoint for recent equipment activity
    Route::get('/api/recent-activity', function() {
        $user = auth()->user();
        $managedProjectIds = $user->getManagedProjects()->pluck('id')->toArray();
        
        $recentRequests = \App\Models\EquipmentRequest::whereIn('project_id', $managedProjectIds)
            ->with(['user', 'project'])
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();
            
        $upcomingMaintenance = \App\Models\EquipmentMaintenance::whereHas('monitoredEquipment', function($q) use ($managedProjectIds) {
                $q->whereIn('project_id', $managedProjectIds);
            })
            ->where('status', 'scheduled')
            ->where('scheduled_date', '>=', now())
            ->where('scheduled_date', '<=', now()->addDays(7))
            ->with(['monitoredEquipment.user', 'monitoredEquipment.project'])
            ->orderBy('scheduled_date', 'asc')
            ->take(5)
            ->get();
            
        return response()->json([
            'recent_requests' => $recentRequests,
            'upcoming_maintenance' => $upcomingMaintenance,
        ]);
    })->name('api.recent-activity');
    
    // API endpoint for equipment needing attention
    Route::get('/api/attention-needed', function() {
        $user = auth()->user();
        $managedProjectIds = $user->getManagedProjects()->pluck('id')->toArray();
        
        $equipmentNeedingAttention = \App\Models\MonitoredEquipment::whereIn('project_id', $managedProjectIds)
            ->where('status', 'active')
            ->where(function($query) {
                $query->where('availability_status', 'out_of_order')
                      ->orWhere('availability_status', 'maintenance')
                      ->orWhere('next_maintenance_date', '<=', now()->addDays(7));
            })
            ->with(['user', 'project'])
            ->get();
            
        return response()->json([
            'equipment_needing_attention' => $equipmentNeedingAttention,
            'count' => $equipmentNeedingAttention->count(),
        ]);
    })->name('api.attention-needed');
        });
    });
});

    
    // ====================================================================
    // SHARED NOTIFICATION ROUTES FOR EQUIPMENT MONITORING
    // ====================================================================
    Route::prefix('equipment-monitoring/notifications')->name('equipment-monitoring.notifications.')->group(function () {
        Route::get('/', function() {
            $user = auth()->user();
            $notifications = $user->notifications()
                ->whereIn('type', [
                    'App\\Notifications\\EquipmentRequestApproved',
                    'App\\Notifications\\EquipmentRequestDeclined', 
                    'App\\Notifications\\MaintenanceReminder',
                    'App\\Notifications\\MaintenanceCompleted'
                ])
                ->orderBy('created_at', 'desc')
                ->paginate(20);
                
            return view('equipment-monitoring.notifications.index', compact('notifications'));
        })->name('index');
        
        Route::post('/{id}/mark-read', function($id) {
            $notification = auth()->user()->notifications()->findOrFail($id);
            $notification->markAsRead();
            
            return response()->json(['success' => true]);
        })->name('mark-read');
        
        Route::post('/mark-all-read', function() {
            auth()->user()->notifications()
                ->whereIn('type', [
                    'App\\Notifications\\EquipmentRequestApproved',
                    'App\\Notifications\\EquipmentRequestDeclined',
                    'App\\Notifications\\MaintenanceReminder', 
                    'App\\Notifications\\MaintenanceCompleted'
                ])
                ->whereNull('read_at')
                ->update(['read_at' => now()]);
                
            return response()->json(['success' => true]);
        })->name('mark-all-read');
    });

if (app()->environment('local')) {
    Route::get('/test-email', [AuthController::class, 'testEmail'])->name('test.email');
}