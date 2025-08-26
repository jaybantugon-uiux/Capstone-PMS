<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Models\SiteIssue;
use App\Models\TaskReport;
use App\Models\ProgressReport;
use App\Models\SitePhoto;
use App\Models\MonitoredEquipment;
use App\Models\EquipmentRequest;
use App\Models\EquipmentMaintenance;
use App\Models\DailyExpenditure;
use App\Models\LiquidatedForm;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
public function pmDashboard()
{
    $user = Auth::user();
    
    // Get PM's projects and tasks data
    $managedProjectIds = $user->getManagedProjects()->pluck('id')->toArray();
    $myProjects = Project::whereIn('id', $managedProjectIds)->where('archived', false)->count();
    $activeProjects = Project::whereIn('id', $managedProjectIds)->where('archived', false)->count();
    
    // Get tasks in PM's managed projects
    $tasksAssigned = Task::whereIn('project_id', $managedProjectIds)->where('archived', false)->count();
    
    // Get upcoming deadlines (tasks due within 7 days in PM's projects)
    $upcomingDeadlines = Task::whereIn('project_id', $managedProjectIds)
        ->where('archived', false)
        ->where('status', '!=', 'completed')
        ->where('due_date', '>=', now())
        ->where('due_date', '<=', now()->addDays(7))
        ->count();
    
    // Get project progress for PM's projects
    $projectProgress = Project::whereIn('id', $managedProjectIds)
        ->where('archived', false)
        ->with(['tasks' => function($query) {
            $query->where('archived', false);
        }])
        ->get()
        ->map(function($project) {
            $totalTasks = $project->tasks->count();
            $completedTasks = $project->tasks->where('status', 'completed')->count();
            
            $project->total_tasks = $totalTasks;
            $project->completed_tasks = $completedTasks;
            $project->completion_percentage = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100) : 0;
            $project->formatted_due_date = $project->end_date ? $project->end_date->format('M d, Y') : 'No due date';
            $project->team_members_count = $project->tasks->pluck('assigned_to')->unique()->count();
            $project->priority = 'Medium';
            $project->priority_badge_color = 'warning';
            $project->is_behind_schedule = $project->end_date && $project->end_date->isPast() && $project->completion_percentage < 100;
            $project->progress_color = $project->completion_percentage >= 75 ? 'success' : ($project->completion_percentage >= 50 ? 'warning' : 'danger');
            
            return $project;
        });

    // Recent notifications
    $recentNotifications = $user->notifications()
        ->orderBy('created_at', 'desc')
        ->take(10)
        ->get();

    // Progress Reports Statistics for PM
    $progressReportsStats = [
        'total' => ProgressReport::where('created_by', $user->id)->count(),
        'sent' => ProgressReport::where('created_by', $user->id)->where('status', 'sent')->count(),
        'viewed' => ProgressReport::where('created_by', $user->id)->where('status', 'viewed')->count(),
        'recent' => ProgressReport::where('created_by', $user->id)->where('created_at', '>=', now()->subDays(7))->count(),
    ];

    // Recent Progress Reports created by this PM
    $recentProgressReports = ProgressReport::where('created_by', $user->id)
        ->with(['client', 'project'])
        ->latest()
        ->take(5)
        ->get();

    // Site Issues Statistics for PM
    $siteIssuesStats = [
        'total' => SiteIssue::whereIn('project_id', $managedProjectIds)->count(),
        'open' => SiteIssue::whereIn('project_id', $managedProjectIds)->where('status', 'open')->count(),
        'in_progress' => SiteIssue::whereIn('project_id', $managedProjectIds)->where('status', 'in_progress')->count(),
        'resolved' => SiteIssue::whereIn('project_id', $managedProjectIds)->where('status', 'resolved')->count(),
        'critical' => SiteIssue::whereIn('project_id', $managedProjectIds)->where('priority', 'critical')->whereNotIn('status', ['resolved', 'closed'])->count(),
        'unacknowledged' => SiteIssue::whereIn('project_id', $managedProjectIds)->whereNull('acknowledged_at')->count(),
        'assigned_to_me' => SiteIssue::where('assigned_to', $user->id)->whereNotIn('status', ['resolved', 'closed'])->count(),
        'recent' => SiteIssue::whereIn('project_id', $managedProjectIds)->where('reported_at', '>=', now()->subDays(7))->count(),
        'safety_issues' => SiteIssue::whereIn('project_id', $managedProjectIds)->where('issue_type', 'safety')->whereNotIn('status', ['resolved', 'closed'])->count(),
    ];

    // Recent Site Issues in PM's projects
    $recentSiteIssues = SiteIssue::whereIn('project_id', $managedProjectIds)
        ->with(['reporter', 'project', 'assignedTo'])
        ->latest('reported_at')
        ->take(5)
        ->get();

    // Critical site issues requiring immediate attention
    $criticalSiteIssues = SiteIssue::whereIn('project_id', $managedProjectIds)
        ->where('priority', 'critical')
        ->whereNotIn('status', ['resolved', 'closed'])
        ->with(['reporter', 'project'])
        ->latest('reported_at')
        ->get();

    // Task Reports Statistics for PM
    $taskReportsStats = [];
    if (class_exists(\App\Models\TaskReport::class)) {
        $pmTaskReportsQuery = TaskReport::whereHas('task', function($q) use ($managedProjectIds) {
            $q->whereIn('project_id', $managedProjectIds);
        });

        $taskReportsStats = [
            'total' => $pmTaskReportsQuery->count(),
            'pending_review' => $pmTaskReportsQuery->where('review_status', 'pending')->count(),
            'approved' => $pmTaskReportsQuery->where('review_status', 'approved')->count(),
            'needs_revision' => $pmTaskReportsQuery->where('review_status', 'needs_revision')->count(),
            'overdue_reviews' => $pmTaskReportsQuery->where('review_status', 'pending')
                                                   ->where('created_at', '<', now()->subDays(2))
                                                   ->count(),
        ];
    }

    // Recent Task Reports in PM's projects
    $recentTaskReports = collect();
    if (class_exists(\App\Models\TaskReport::class)) {
        $recentTaskReports = TaskReport::whereHas('task', function($q) use ($managedProjectIds) {
            $q->whereIn('project_id', $managedProjectIds);
        })
        ->with(['task.project', 'user'])
        ->latest()
        ->take(5)
        ->get();
    }

    // Overdue task reports in PM's projects
    $overdueReports = collect();
    if (class_exists(\App\Models\TaskReport::class)) {
        $overdueReports = Task::whereIn('project_id', $managedProjectIds)
            ->where('status', 'in_progress')
            ->where('archived', false)
            ->whereDoesntHave('taskReports', function($q) {
                $q->where('created_at', '>=', now()->subDays(3));
            })
            ->with('project')
            ->get();
    }

    // Site Photos Statistics for PM
    $sitePhotosStats = [
        'total' => SitePhoto::whereIn('project_id', $managedProjectIds)->count(),
        'submitted' => SitePhoto::whereIn('project_id', $managedProjectIds)->where('submission_status', 'submitted')->count(),
        'approved' => SitePhoto::whereIn('project_id', $managedProjectIds)->where('submission_status', 'approved')->count(),
        'rejected' => SitePhoto::whereIn('project_id', $managedProjectIds)->where('submission_status', 'rejected')->count(),
        'featured' => SitePhoto::whereIn('project_id', $managedProjectIds)->where('is_featured', true)->count(),
        'public' => SitePhoto::whereIn('project_id', $managedProjectIds)->where('is_public', true)->count(),
        'overdue_reviews' => SitePhoto::whereIn('project_id', $managedProjectIds)
            ->where('submission_status', 'submitted')
            ->where('submitted_at', '<', now()->subDays(3))
            ->count(),
        'projects_with_photos' => Project::whereIn('id', $managedProjectIds)
            ->whereHas('sitePhotos')
            ->count(),
    ];

    // Recent Site Photos in PM's projects
    $recentSitePhotos = SitePhoto::whereIn('project_id', $managedProjectIds)
        ->with(['project', 'uploader'])
        ->orderBy('submitted_at', 'desc')
        ->take(5)
        ->get();

    // ====================================================================
    // NEW: EQUIPMENT MONITORING INTEGRATION FOR PM DASHBOARD
    // ====================================================================
    
    // Equipment Monitoring Statistics for PM's managed projects
    $equipmentMonitoringStats = [
        // Equipment Request Statistics
        'total_requests' => EquipmentRequest::whereIn('project_id', $managedProjectIds)->count(),
        'pending_requests' => EquipmentRequest::whereIn('project_id', $managedProjectIds)->where('status', 'pending')->count(),
        'approved_requests' => EquipmentRequest::whereIn('project_id', $managedProjectIds)->where('status', 'approved')->count(),
        'declined_requests' => EquipmentRequest::whereIn('project_id', $managedProjectIds)->where('status', 'declined')->count(),
        
        // Monitored Equipment Statistics
        'total_equipment' => MonitoredEquipment::whereIn('project_id', $managedProjectIds)->count(),
        'active_equipment' => MonitoredEquipment::whereIn('project_id', $managedProjectIds)->where('status', 'active')->count(),
        'pending_equipment' => MonitoredEquipment::whereIn('project_id', $managedProjectIds)->where('status', 'pending_approval')->count(),
        'personal_equipment' => MonitoredEquipment::where('usage_type', 'personal')
            ->whereHas('user.tasks', function($q) use ($managedProjectIds) {
                $q->whereIn('project_id', $managedProjectIds);
            })->count(),
        
        // Equipment availability in managed projects
        'equipment_available' => MonitoredEquipment::whereIn('project_id', $managedProjectIds)->where('availability_status', 'available')->count(),
        'equipment_in_use' => MonitoredEquipment::whereIn('project_id', $managedProjectIds)->where('availability_status', 'in_use')->count(),
        'equipment_maintenance' => MonitoredEquipment::whereIn('project_id', $managedProjectIds)->where('availability_status', 'maintenance')->count(),
        'equipment_out_of_order' => MonitoredEquipment::whereIn('project_id', $managedProjectIds)->where('availability_status', 'out_of_order')->count(),
        
        // Maintenance Statistics
        'maintenance_scheduled' => EquipmentMaintenance::whereHas('monitoredEquipment', function($q) use ($managedProjectIds) {
            $q->whereIn('project_id', $managedProjectIds);
        })->where('status', 'scheduled')->count(),
        'maintenance_overdue' => EquipmentMaintenance::whereHas('monitoredEquipment', function($q) use ($managedProjectIds) {
            $q->whereIn('project_id', $managedProjectIds);
        })->where('status', 'scheduled')->where('scheduled_date', '<', now())->count(),
        'maintenance_this_week' => EquipmentMaintenance::whereHas('monitoredEquipment', function($q) use ($managedProjectIds) {
            $q->whereIn('project_id', $managedProjectIds);
        })->where('status', 'scheduled')->whereBetween('scheduled_date', [now(), now()->addDays(7)])->count(),
        
        // Recent activity
        'recent_requests' => EquipmentRequest::whereIn('project_id', $managedProjectIds)->where('created_at', '>=', now()->subDays(7))->count(),
        'urgent_requests' => EquipmentRequest::whereIn('project_id', $managedProjectIds)->where('status', 'pending')
            ->whereIn('urgency_level', ['high', 'critical'])->count(),
    ];

    // Recent Equipment Requests in PM's projects
    $recentEquipmentRequests = EquipmentRequest::whereIn('project_id', $managedProjectIds)
        ->with(['user', 'project', 'monitoredEquipment'])
        ->orderBy('created_at', 'desc')
        ->take(5)
        ->get();

    // Pending Equipment Requests requiring PM attention
    $pendingEquipmentRequests = EquipmentRequest::whereIn('project_id', $managedProjectIds)
        ->where('status', 'pending')
        ->with(['user', 'project'])
        ->orderBy('urgency_level', 'desc')
        ->orderBy('created_at', 'asc')
        ->get();

    // Upcoming Equipment Maintenance in PM's projects
    $upcomingEquipmentMaintenance = EquipmentMaintenance::whereHas('monitoredEquipment', function($q) use ($managedProjectIds) {
            $q->whereIn('project_id', $managedProjectIds);
        })
        ->where('status', 'scheduled')
        ->where('scheduled_date', '>=', now())
        ->where('scheduled_date', '<=', now()->addDays(30))
        ->with(['monitoredEquipment.user', 'monitoredEquipment.project'])
        ->orderBy('scheduled_date', 'asc')
        ->take(5)
        ->get();

    // Overdue Equipment Maintenance in PM's projects
    $overdueEquipmentMaintenance = EquipmentMaintenance::whereHas('monitoredEquipment', function($q) use ($managedProjectIds) {
            $q->whereIn('project_id', $managedProjectIds);
        })
        ->where('status', 'scheduled')
        ->where('scheduled_date', '<', now())
        ->with(['monitoredEquipment.user', 'monitoredEquipment.project'])
        ->orderBy('scheduled_date', 'asc')
        ->get();

    // Equipment needing attention in PM's projects
    $equipmentNeedingAttention = MonitoredEquipment::whereIn('project_id', $managedProjectIds)
        ->where('status', 'active')
        ->where(function($query) {
            $query->where('availability_status', 'out_of_order')
                  ->orWhere('availability_status', 'maintenance')
                  ->orWhere('next_maintenance_date', '<=', now()->addDays(7));
        })
        ->with(['user', 'project'])
        ->get();

    // Project Equipment Summary - Equipment distribution across PM's projects
    $projectEquipmentSummary = Project::whereIn('id', $managedProjectIds)
        ->with(['monitoredEquipment' => function($q) {
            $q->where('status', 'active');
        }])
        ->get()
        ->map(function($project) {
            $project->equipment_count = $project->monitoredEquipment->count();
            $project->equipment_in_use = $project->monitoredEquipment->where('availability_status', 'in_use')->count();
            $project->equipment_available = $project->monitoredEquipment->where('availability_status', 'available')->count();
            $project->equipment_issues = $project->monitoredEquipment->whereIn('availability_status', ['maintenance', 'out_of_order'])->count();
            return $project;
        })
        ->sortByDesc('equipment_count')
        ->take(10);

    // End Equipment Monitoring Integration
    // ====================================================================
    
    // Get team performance (users assigned to tasks in PM's projects)
    $teamPerformance = User::whereIn('id', function($query) use ($managedProjectIds) {
        $query->select('assigned_to')
            ->from('tasks')
            ->whereIn('project_id', $managedProjectIds)
            ->where('archived', false);
    })
    ->get()
    ->map(function($member) use ($managedProjectIds) {
        $totalTasks = Task::whereIn('project_id', $managedProjectIds)
            ->where('assigned_to', $member->id)
            ->where('archived', false)
            ->count();
        
        $completedTasks = Task::whereIn('project_id', $managedProjectIds)
            ->where('assigned_to', $member->id)
            ->where('archived', false)
            ->where('status', 'completed')
            ->count();
        
        $activeTasks = Task::whereIn('project_id', $managedProjectIds)
            ->where('assigned_to', $member->id)
            ->where('archived', false)
            ->where('status', '!=', 'completed')
            ->count();
        
        $member->completion_rate = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100) : 0;
        $member->active_tasks = $activeTasks;
        
        return $member;
    });
    
    // Get upcoming tasks (tasks due soon in PM's projects)
    $upcomingTasks = Task::whereIn('project_id', function($query) use ($user) {
        $query->select('id')
              ->from('projects')
              ->where('created_by', $user->id);
    })
    ->where('archived', false)
    ->where('status', '!=', 'completed')
    ->where('due_date', '>=', now())
    ->orderBy('due_date', 'asc')
    ->with(['project', 'siteCoordinator'])
    ->take(10)
    ->get()
    ->map(function($task) {
        $task->formatted_due_date = $task->due_date ? $task->due_date->format('M d, Y') : 'No due date';
        $task->is_overdue = $task->due_date && $task->due_date->isPast();
        $task->priority = 'Medium'; // Adjust based on your logic
        $task->priority_badge_color = 'warning'; // Adjust based on priority
        $task->assignee = $task->siteCoordinator; // Assuming tasks are assigned to site coordinators
        return $task;
    });
    
    // Get recent activity (you might want to create an Activity model for this)
    // For now, we'll create a simple collection
    $recentActivity = collect([
        (object)[
            'description' => 'New project created',
            'icon' => 'plus-circle',
            'color' => 'success',
            'user' => $user,
            'created_at' => now()->subHours(2)
        ],
        (object)[
            'description' => 'Task completed',
            'icon' => 'check-circle',
            'color' => 'primary',
            'user' => $user,
            'created_at' => now()->subHours(5)
        ]
    ]);

            // Expense Liquidation Statistics for PM
        $expenseLiquidationStats = [
            'total_expenditures' => DailyExpenditure::where('submitted_by', $user->id)->count(),
            'draft_expenditures' => DailyExpenditure::where('submitted_by', $user->id)->where('status', 'draft')->count(),
            'submitted_expenditures' => DailyExpenditure::where('submitted_by', $user->id)->where('status', 'submitted')->count(),
            'total_amount' => DailyExpenditure::where('submitted_by', $user->id)->sum('amount')
        ];

        // Liquidated Forms Statistics for PM
        $liquidatedFormsStats = [
            'total' => LiquidatedForm::whereIn('project_id', $managedProjectIds)->count(),
            'pending' => LiquidatedForm::whereIn('project_id', $managedProjectIds)->where('status', 'pending')->count(),
            'under_review' => LiquidatedForm::whereIn('project_id', $managedProjectIds)->where('status', 'under_review')->count(),
            'flagged' => LiquidatedForm::whereIn('project_id', $managedProjectIds)->where('status', 'flagged')->count(),
            'clarification_requested' => LiquidatedForm::whereIn('project_id', $managedProjectIds)->where('status', 'clarification_requested')->count(),
            'total_amount' => LiquidatedForm::whereIn('project_id', $managedProjectIds)->sum('total_amount')
        ];
        
    return view('pm.dashboard', compact(
        'myProjects',
        'activeProjects', 
        'tasksAssigned',
        'upcomingDeadlines',
        'projectProgress',
        'recentNotifications',
        'progressReportsStats',      
        'recentProgressReports',
        'taskReportsStats',          
        'recentTaskReports',
        'siteIssuesStats',
        'recentSiteIssues',
        'criticalSiteIssues',
        'overdueReports',           
        'sitePhotosStats',
        'recentSitePhotos',
        // NEW: Equipment Monitoring Variables
        'equipmentMonitoringStats',
        'recentEquipmentRequests',
        'pendingEquipmentRequests',
        'upcomingEquipmentMaintenance',
        'overdueEquipmentMaintenance',
        'equipmentNeedingAttention',
        'projectEquipmentSummary',
        // NEW: Expense Liquidation Variables
        'expenseLiquidationStats',
        // NEW: Liquidated Forms Variables
        'liquidatedFormsStats',
        // Existing variables
        'teamPerformance',
        'upcomingTasks',
        'recentActivity'
    ));
    }
}