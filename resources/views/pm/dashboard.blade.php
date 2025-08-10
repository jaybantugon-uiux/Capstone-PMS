@extends('app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1>Project Manager Dashboard</h1>
            <p class="text-muted">Welcome back, {{ auth()->user()->full_name }}</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('projects.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i>New Project
            </a>
            <a href="{{ route('tasks.create') }}" class="btn btn-success">
                <i class="fas fa-tasks me-1"></i>Add Task
            </a>
            <a href="{{ route('admin.progress-reports.create') }}" class="btn btn-info">
                <i class="fas fa-file-alt me-1"></i>New Report
            </a>
            <a href="{{ route('notifications.index') }}" class="btn btn-outline-warning">
                <i class="fas fa-bell me-1"></i>Notifications
                @if(auth()->user()->unreadNotifications->count() > 0)
                    <span class="badge bg-danger">{{ auth()->user()->unreadNotifications->count() }}</span>
                @endif
            </a>
        </div>
    </div>

    <!-- Key Metrics -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body text-center">
                    <i class="fas fa-project-diagram fa-2x mb-2"></i>
                    <h3>{{ $myProjects }}</h3>
                    <p class="mb-0">My Projects</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body text-center">
                    <i class="fas fa-check-circle fa-2x mb-2"></i>
                    <h3>{{ $activeProjects }}</h3>
                    <p class="mb-0">Active Projects</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body text-center">
                    <i class="fas fa-tasks fa-2x mb-2"></i>
                    <h3>{{ $tasksAssigned }}</h3>
                    <p class="mb-0">Total Tasks</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body text-center">
                    <i class="fas fa-clock fa-2x mb-2"></i>
                    <h3>{{ $upcomingDeadlines }}</h3>
                    <p class="mb-0">Due This Week</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Task Reports Management Card -->
    @if(isset($taskReportsStats) && !empty($taskReportsStats))
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h5 class="card-title text-warning mb-2">
                                <i class="fas fa-clipboard-check me-2"></i>Task Reports Management
                            </h5>
                            <p class="text-muted mb-0">Review and manage task reports from your project teams</p>
                        </div>
                        <div class="text-warning">
                            <i class="fas fa-tasks fa-3x opacity-75"></i>
                        </div>
                    </div>
                    
                    <!-- Task Reports Statistics -->
                    <div class="row mb-3">
                        <div class="col-md-2">
                            <div class="bg-light p-3 rounded text-center">
                                <h5 class="text-primary mb-1">{{ $taskReportsStats['total'] ?? 0 }}</h5>
                                <small class="text-muted">Total Reports</small>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="bg-light p-3 rounded text-center">
                                <h5 class="text-warning mb-1">{{ $taskReportsStats['pending_review'] ?? 0 }}</h5>
                                <small class="text-muted">Pending Review</small>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="bg-light p-3 rounded text-center">
                                <h5 class="text-success mb-1">{{ $taskReportsStats['approved'] ?? 0 }}</h5>
                                <small class="text-muted">Approved</small>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="bg-light p-3 rounded text-center">
                                <h5 class="text-info mb-1">{{ $taskReportsStats['needs_revision'] ?? 0 }}</h5>
                                <small class="text-muted">Need Revision</small>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="bg-light p-3 rounded text-center">
                                <h5 class="text-danger mb-1">{{ $taskReportsStats['overdue_reviews'] ?? 0 }}</h5>
                                <small class="text-muted">Overdue</small>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="bg-light p-3 rounded text-center">
                                <h5 class="text-dark mb-1">{{ isset($taskReportsStats['approved'], $taskReportsStats['total']) && $taskReportsStats['total'] > 0 ? number_format(($taskReportsStats['approved'] / $taskReportsStats['total']) * 100, 1) : 0 }}%</h5>
                                <small class="text-muted">Approval Rate</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-flex gap-2 flex-wrap">
                        <a href="{{ route('pm.task-reports.index') }}" class="btn btn-warning">
                            <i class="fas fa-list me-1"></i>All Reports
                        </a>
                        <a href="{{ route('pm.task-reports.index', ['status' => 'pending']) }}" class="btn btn-outline-warning">
                            <i class="fas fa-clock me-1"></i>Pending Review
                            @if(isset($taskReportsStats['pending_review']) && $taskReportsStats['pending_review'] > 0)
                                <span class="badge bg-danger ms-1">{{ $taskReportsStats['pending_review'] }}</span>
                            @endif
                        </a>
                        
                        @if(isset($taskReportsStats['overdue_reviews']) && $taskReportsStats['overdue_reviews'] > 0)
                            <a href="{{ route('pm.task-reports.index', ['review_status' => 'pending']) }}" class="btn btn-outline-danger">
                                <i class="fas fa-exclamation-triangle me-1"></i>Overdue ({{ $taskReportsStats['overdue_reviews'] }})
                            </a>
                        @endif
                        
                        <a href="{{ route('pm.task-reports.bulk-approve') }}" class="btn btn-outline-primary">
                            <i class="fas fa-check-double me-1"></i>Bulk Approve
                        </a>
                        
                        <a href="{{ route('pm.task-reports.export') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-download me-1"></i>Export
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

   <!-- Site Issues Management Card -->
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h5 class="card-title text-danger mb-2">
                            <i class="fas fa-exclamation-triangle me-2"></i>Site Issues Management
                        </h5>
                        <p class="text-muted mb-0">Monitor and manage site issues from your projects</p>
                    </div>
                    <div class="text-danger">
                        <i class="fas fa-hard-hat fa-3x opacity-75"></i>
                    </div>
                </div>
                
                <!-- Site Issues Statistics -->
                @if(isset($siteIssuesStats) && !empty($siteIssuesStats))
                <div class="row mb-3">
                    <div class="col-md-2">
                        <div class="bg-light p-3 rounded text-center">
                            <h5 class="text-primary mb-1">{{ $siteIssuesStats['total'] ?? 0 }}</h5>
                            <small class="text-muted">Total Issues</small>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="bg-light p-3 rounded text-center">
                            <h5 class="text-warning mb-1">{{ $siteIssuesStats['open'] ?? 0 }}</h5>
                            <small class="text-muted">Open</small>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="bg-light p-3 rounded text-center">
                            <h5 class="text-info mb-1">{{ $siteIssuesStats['in_progress'] ?? 0 }}</h5>
                            <small class="text-muted">In Progress</small>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="bg-light p-3 rounded text-center">
                            <h5 class="text-danger mb-1">{{ $siteIssuesStats['critical'] ?? 0 }}</h5>
                            <small class="text-muted">Critical</small>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="bg-light p-3 rounded text-center">
                            <h5 class="text-secondary mb-1">{{ $siteIssuesStats['unacknowledged'] ?? 0 }}</h5>
                            <small class="text-muted">Unacknowledged</small>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="bg-light p-3 rounded text-center">
                            <h5 class="text-success mb-1">{{ $siteIssuesStats['resolved'] ?? 0 }}</h5>
                            <small class="text-muted">Resolved</small>
                        </div>
                    </div>
                </div>
                @endif
                
                <div class="d-flex gap-2 flex-wrap">
                    <a href="{{ route('pm.site-issues.index') }}" class="btn btn-danger btn-sm">
                        <i class="fas fa-list me-1"></i>All Issues
                    </a>
                    @if(isset($siteIssuesStats['critical']) && $siteIssuesStats['critical'] > 0)
                        <a href="{{ route('pm.site-issues.index', ['priority' => 'critical']) }}" class="btn btn-outline-danger btn-sm">
                            <i class="fas fa-fire me-1"></i>Critical ({{ $siteIssuesStats['critical'] }})
                        </a>
                    @endif
                    @if(isset($siteIssuesStats['unacknowledged']) && $siteIssuesStats['unacknowledged'] > 0)
                        <a href="{{ route('pm.site-issues.index', ['unacknowledged' => 1]) }}" class="btn btn-outline-warning btn-sm">
                            <i class="fas fa-eye-slash me-1"></i>Unacknowledged ({{ $siteIssuesStats['unacknowledged'] }})
                        </a>
                    @endif
                    <a href="{{ route('pm.site-issues.export') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-download me-1"></i>Export
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

    <!-- Site Photos Management Card -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h5 class="card-title text-primary mb-2">
                                <i class="fas fa-camera me-2"></i>Site Photos Management
                            </h5>
                            <p class="text-muted mb-0">Review and manage site photos from your projects</p>
                        </div>
                        <div class="text-primary">
                            <i class="fas fa-images fa-3x opacity-75"></i>
                        </div>
                    </div>
                    
                    <!-- Site Photos Statistics -->
                    <div class="row mb-3">
                        <div class="col-md-2">
                            <div class="bg-light p-3 rounded text-center">
                                <h5 class="text-primary mb-1">{{ $sitePhotosStats['total'] ?? 0 }}</h5>
                                <small class="text-muted">Total Photos</small>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="bg-light p-3 rounded text-center">
                                <h5 class="text-warning mb-1">{{ $sitePhotosStats['submitted'] ?? 0 }}</h5>
                                <small class="text-muted">Submitted</small>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="bg-light p-3 rounded text-center">
                                <h5 class="text-success mb-1">{{ $sitePhotosStats['approved'] ?? 0 }}</h5>
                                <small class="text-muted">Approved</small>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="bg-light p-3 rounded text-center">
                                <h5 class="text-danger mb-1">{{ $sitePhotosStats['rejected'] ?? 0 }}</h5>
                                <small class="text-muted">Rejected</small>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="bg-light p-3 rounded text-center">
                                <h5 class="text-info mb-1">{{ $sitePhotosStats['featured'] ?? 0 }}</h5>
                                <small class="text-muted">Featured</small>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="bg-light p-3 rounded text-center">
                                <h5 class="text-secondary mb-1">{{ $sitePhotosStats['public'] ?? 0 }}</h5>
                                <small class="text-muted">Public</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-flex gap-2 flex-wrap">
                        <a href="{{ route('pm.site-photos.index') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-list me-1"></i>All Photos
                        </a>
                        @if(isset($sitePhotosStats['submitted']) && $sitePhotosStats['submitted'] > 0)
                            <a href="{{ route('pm.site-photos.index', ['status' => 'submitted']) }}" class="btn btn-outline-warning btn-sm">
                                <i class="fas fa-clock me-1"></i>Pending Review ({{ $sitePhotosStats['submitted'] }})
                            </a>
                        @endif
                        @if(isset($sitePhotosStats['overdue_reviews']) && $sitePhotosStats['overdue_reviews'] > 0)
                            <a href="{{ route('pm.site-photos.index', ['overdue' => 1]) }}" class="btn btn-outline-danger btn-sm">
                                <i class="fas fa-exclamation-triangle me-1"></i>Overdue ({{ $sitePhotosStats['overdue_reviews'] }})
                            </a>
                        @endif
                        <a href="{{ route('photos.featured') }}" class="btn btn-outline-info btn-sm">
                            <i class="fas fa-star me-1"></i>Featured Photos
                        </a>
                        <a href="{{ route('pm.site-photos.export') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-download me-1"></i>Export
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ====================================================================
         NEW: EQUIPMENT MONITORING MANAGEMENT CARD
         ==================================================================== -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h5 class="card-title text-secondary mb-2">
                                <i class="fas fa-tools me-2"></i>Equipment Monitoring Management
                            </h5>
                            <p class="text-muted mb-0">Monitor and oversee equipment across your managed projects</p>
                        </div>
                        <div class="text-secondary">
                            <i class="fas fa-cogs fa-3x opacity-75"></i>
                        </div>
                    </div>
                    
                    <!-- Equipment Monitoring Statistics -->
                    <div class="row mb-3">
                        <div class="col-md-2">
                            <div class="bg-light p-3 rounded text-center">
                                <h5 class="text-primary mb-1">{{ $equipmentMonitoringStats['total_equipment'] ?? 0 }}</h5>
                                <small class="text-muted">Total Equipment</small>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="bg-light p-3 rounded text-center">
                                <h5 class="text-success mb-1">{{ $equipmentMonitoringStats['active_equipment'] ?? 0 }}</h5>
                                <small class="text-muted">Active</small>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="bg-light p-3 rounded text-center">
                                <h5 class="text-warning mb-1">{{ $equipmentMonitoringStats['pending_requests'] ?? 0 }}</h5>
                                <small class="text-muted">Pending Requests</small>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="bg-light p-3 rounded text-center">
                                <h5 class="text-info mb-1">{{ $equipmentMonitoringStats['equipment_in_use'] ?? 0 }}</h5>
                                <small class="text-muted">In Use</small>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="bg-light p-3 rounded text-center">
                                <h5 class="text-danger mb-1">{{ $equipmentMonitoringStats['maintenance_overdue'] ?? 0 }}</h5>
                                <small class="text-muted">Maintenance Overdue</small>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="bg-light p-3 rounded text-center">
                                <h5 class="text-secondary mb-1">{{ $equipmentMonitoringStats['maintenance_this_week'] ?? 0 }}</h5>
                                <small class="text-muted">Maintenance This Week</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-flex gap-2 flex-wrap">
                        <a href="{{ route('pm.equipment-monitoring.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-list me-1"></i>Equipment Overview
                        </a>
                        <a href="{{ route('pm.equipment-monitoring.equipment-list') }}" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-tools me-1"></i>View Equipment
                        </a>
                        @if(isset($equipmentMonitoringStats['pending_requests']) && $equipmentMonitoringStats['pending_requests'] > 0)
                            <a href="{{ route('pm.equipment-monitoring.requests', ['status' => 'pending']) }}" class="btn btn-outline-warning btn-sm">
                                <i class="fas fa-clock me-1"></i>Pending Requests ({{ $equipmentMonitoringStats['pending_requests'] }})
                            </a>
                        @endif
                        <a href="{{ route('pm.equipment-monitoring.maintenance-list') }}" class="btn btn-outline-info btn-sm">
                            <i class="fas fa-wrench me-1"></i>Maintenance Schedule
                        </a>
                        @if(isset($equipmentMonitoringStats['maintenance_overdue']) && $equipmentMonitoringStats['maintenance_overdue'] > 0)
                            <a href="{{ route('pm.equipment-monitoring.maintenance-list', ['status' => 'overdue']) }}" class="btn btn-outline-danger btn-sm">
                                <i class="fas fa-exclamation-triangle me-1"></i>Overdue Maintenance ({{ $equipmentMonitoringStats['maintenance_overdue'] }})
                            </a>
                        @endif
                        <a href="{{ route('pm.equipment-monitoring.report-summary') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-chart-bar me-1"></i>Reports
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Progress Reports Management Card -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h5 class="card-title text-info mb-2">
                                <i class="fas fa-file-chart-line me-2"></i>My Progress Reports
                            </h5>
                            <p class="text-muted mb-0">Create and manage progress reports for your projects</p>
                        </div>
                        <div class="text-info">
                            <i class="fas fa-chart-line fa-3x opacity-75"></i>
                        </div>
                    </div>
                    
                    <!-- Progress Reports Statistics -->
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <div class="bg-light p-3 rounded text-center">
                                <h5 class="text-primary mb-1">{{ $progressReportsStats['total'] ?? 0 }}</h5>
                                <small class="text-muted">Total Reports</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="bg-light p-3 rounded text-center">
                                <h5 class="text-warning mb-1">{{ $progressReportsStats['sent'] ?? 0 }}</h5>
                                <small class="text-muted">Sent</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="bg-light p-3 rounded text-center">
                                <h5 class="text-success mb-1">{{ $progressReportsStats['viewed'] ?? 0 }}</h5>
                                <small class="text-muted">Viewed</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="bg-light p-3 rounded text-center">
                                <h5 class="text-info mb-1">{{ $progressReportsStats['recent'] ?? 0 }}</h5>
                                <small class="text-muted">This Week</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-flex gap-2">
                        <a href="{{ route('pm.progress-reports.index') }}" class="btn btn-info btn-sm">
                            <i class="fas fa-list me-1"></i>View My Reports
                        </a>
                        <a href="{{ route('admin.progress-reports.create') }}" class="btn btn-outline-info btn-sm">
                            <i class="fas fa-plus me-1"></i>New Report
                        </a>
                        <a href="{{ route('pm.progress-reports.index', ['status' => 'sent']) }}" class="btn btn-outline-warning btn-sm">
                            <i class="fas fa-paper-plane me-1"></i>Sent Reports
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Project Progress and Recent Task Reports -->
    <div class="row mb-4">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5>Project Progress Overview</h5>
                </div>
                <div class="card-body">
                    @if($projectProgress->count() > 0)
                        @foreach($projectProgress as $project)
                            <div class="mb-3">
                                <div class="d-flex justify-content-between">
                                    <h6><a href="{{ route('projects.show', $project) }}">{{ $project->name }}</a></h6>
                                    <span class="badge bg-{{ $project->priority_badge_color ?? 'secondary' }}">{{ $project->priority ?? 'Normal' }}</span>
                                </div>
                                <div class="progress mb-1">
                                    <div class="progress-bar bg-{{ $project->progress_color }}" 
                                         role="progressbar" style="width: {{ $project->completion_percentage }}%">
                                    </div>
                                </div>
                                <small class="text-muted">{{ $project->completion_percentage }}% Complete • {{ $project->total_tasks }} tasks • <a href="{{ route('projects.photos', $project) }}">View Photos</a></small>
                            </div>
                        @endforeach
                    @else
                        <p class="text-muted text-center">No projects to display</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Recent Task Reports</h5>
                    <a href="{{ route('pm.task-reports.index') }}" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-list me-1"></i>View All
                    </a>
                </div>
                <div class="card-body">
                    @if(isset($recentTaskReports) && $recentTaskReports->count() > 0)
                        @foreach($recentTaskReports->take(5) as $report)
                            <div class="mb-2 pb-2 border-bottom">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1">
                                            <a href="{{ route('pm.task-reports.show', $report) }}">{{ Str::limit($report->report_title, 30) }}</a>
                                        </h6>
                                        <small class="text-muted">
                                            By {{ $report->user->first_name }} {{ $report->user->last_name }}
                                        </small>
                                        <br>
                                        <small class="text-muted">
                                            {{ $report->task->task_name }} • {{ $report->task->project->name }}
                                        </small>
                                    </div>
                                    <div class="text-end">
                                        <span class="badge bg-{{ 
                                            $report->review_status === 'pending' ? 'warning' : 
                                            ($report->review_status === 'approved' ? 'success' : 
                                            ($report->review_status === 'needs_revision' ? 'danger' : 'info')) 
                                        }}">
                                            {{ ucfirst(str_replace('_', ' ', $report->review_status)) }}
                                        </span>
                                        @if($report->review_status === 'pending')
                                            <br>
                                            <button class="btn btn-success btn-xs mt-1" onclick="quickApprove({{ $report->id }})">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        @endif
                                    </div>
                                </div>
                                <div class="mt-1">
                                    <small class="text-muted">
                                        {{ $report->created_at->diffForHumans() }} • {{ $report->progress_percentage }}% Progress
                                    </small>
                                    @if($report->issues_encountered)
                                        <span class="badge bg-warning ms-1">Has Issues</span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                        <div class="text-center mt-2">
                            <a href="{{ route('pm.task-reports.index') }}" class="btn btn-sm btn-outline-warning">View All Reports</a>
                        </div>
                    @else
                        <div class="text-center py-3">
                            <i class="fas fa-clipboard-list fa-2x text-muted mb-2"></i>
                            <p class="text-muted mb-2">No recent task reports</p>
                            <a href="{{ route('pm.task-reports.index') }}" class="btn btn-sm btn-outline-primary">Check All Reports</a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Site Issues, Progress Reports, Site Photos, and Equipment Overview -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card">
                <div class="card-header">
                    <h5>Recent Site Issues</h5>
                </div>
                <div class="card-body">
                    @if(isset($recentSiteIssues) && $recentSiteIssues->count() > 0)
                        @foreach($recentSiteIssues->take(5) as $issue)
                            <div class="mb-2 pb-2 border-bottom">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1">
                                            <a href="{{ route('pm.site-issues.show', $issue) }}">{{ Str::limit($issue->issue_title, 30) }}</a>
                                        </h6>
                                        <small class="text-muted">
                                            By {{ $issue->reporter->first_name }} {{ $issue->reporter->last_name }}
                                        </small>
                                        <br>
                                        <small class="text-muted">
                                            {{ $issue->project->name }} • {{ ucfirst($issue->issue_type) }}
                                        </small>
                                    </div>
                                    <div class="text-end">
                                        <span class="badge bg-{{ $issue->priority_badge_color }}">
                                            {{ ucfirst($issue->priority) }}
                                        </span>
                                        <br>
                                        <span class="badge bg-{{ $issue->status_badge_color }} mt-1">
                                            {{ ucfirst(str_replace('_', ' ', $issue->status)) }}
                                        </span>
                                    </div>
                                </div>
                                <div class="mt-1">
                                    <small class="text-muted">
                                        {{ $issue->reported_at->diffForHumans() }}
                                        @if($issue->issue_type === 'safety')
                                            <span class="badge bg-danger ms-1">Safety</span>
                                        @endif
                                        @if(!$issue->acknowledged_at)
                                            <span class="badge bg-warning ms-1">Unacknowledged</span>
                                        @endif
                                    </small>
                                </div>
                            </div>
                        @endforeach
                        <div class="text-center mt-2">
                            <a href="{{ route('pm.site-issues.index') }}" class="btn btn-sm btn-outline-danger">View All Issues</a>
                        </div>
                    @else
                        <p class="text-muted text-center">No recent site issues</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card">
                <div class="card-header">
                    <h5>Recent Progress Reports</h5>
                </div>
                <div class="card-body">
                    @if(isset($recentProgressReports) && $recentProgressReports->count() > 0)
                        @foreach($recentProgressReports->take(5) as $report)
                            <div class="mb-2 pb-2 border-bottom">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1">
                                            <a href="{{ route('admin.progress-reports.show', $report) }}">{{ Str::limit($report->title, 30) }}</a>
                                        </h6>
                                        <small class="text-muted">
                                            For {{ $report->client->first_name }} {{ $report->client->last_name }}
                                        </small>
                                        <br>
                                        <small class="text-muted">
                                            {{ $report->project ? $report->project->name : 'General Report' }}
                                        </small>
                                    </div>
                                    <div class="text-end">
                                        <span class="badge bg-{{ $report->status === 'sent' ? 'warning' : ($report->status === 'viewed' ? 'success' : 'secondary') }}">
                                            {{ ucfirst($report->status) }}
                                        </span>
                                        <br>
                                        <small class="text-muted">{{ $report->view_count }} views</small>
                                    </div>
                                </div>
                                <div class="mt-1">
                                    <small class="text-muted">
                                        {{ $report->created_at->diffForHumans() }}
                                        @if($report->attachment_path)
                                            <span class="badge bg-info ms-1">
                                                <i class="fas fa-paperclip"></i>
                                            </span>
                                        @endif
                                    </small>
                                </div>
                            </div>
                        @endforeach
                        <div class="text-center mt-2">
                            <a href="{{ route('pm.progress-reports.index') }}" class="btn btn-sm btn-outline-info">View All Reports</a>
                        </div>
                    @else
                        <p class="text-muted text-center">No recent progress reports</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card">
                <div class="card-header">
                    <h5>Recent Site Photos</h5>
                </div>
                <div class="card-body">
                    @if(isset($recentSitePhotos) && $recentSitePhotos->count() > 0)
                        @foreach($recentSitePhotos->take(5) as $photo)
                            <div class="mb-2 pb-2 border-bottom">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1">
                                            <a href="{{ route('pm.site-photos.show', $photo) }}">{{ Str::limit($photo->title, 25) }}</a>
                                        </h6>
                                        <small class="text-muted">
                                            By {{ $photo->uploader->first_name }} {{ $photo->uploader->last_name }}
                                        </small>
                                        <br>
                                        <small class="text-muted">
                                            {{ $photo->project->name }} • {{ ucfirst($photo->photo_category) }}
                                        </small>
                                    </div>
                                    <div class="text-end">
                                        <span class="badge bg-{{ $photo->submission_status_badge_color }}">
                                            {{ $photo->formatted_submission_status }}
                                        </span>
                                        @if($photo->submission_status === 'submitted')
                                            <br>
                                            <button class="btn btn-success btn-xs mt-1" onclick="quickApprovePhoto({{ $photo->id }})">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        @endif
                                    </div>
                                </div>
                                <div class="mt-1">
                                    <small class="text-muted">
                                        {{ $photo->submitted_at ? $photo->submitted_at->diffForHumans() : $photo->created_at->diffForHumans() }}
                                        @if($photo->is_featured)
                                            <span class="badge bg-warning ms-1">
                                                <i class="fas fa-star"></i>
                                            </span>
                                        @endif
                                        @if($photo->is_public)
                                            <span class="badge bg-info ms-1">
                                                <i class="fas fa-eye"></i>
                                            </span>
                                        @endif
                                    </small>
                                </div>
                            </div>
                        @endforeach
                        <div class="text-center mt-2">
                            <a href="{{ route('pm.site-photos.index') }}" class="btn btn-sm btn-outline-primary">View All Photos</a>
                        </div>
                    @else
                        <div class="text-center py-3">
                            <i class="fas fa-camera fa-2x text-muted mb-2"></i>
                            <p class="text-muted mb-2">No recent site photos</p>
                            <a href="{{ route('pm.site-photos.index') }}" class="btn btn-sm btn-outline-primary">Check All Photos</a>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- NEW: Equipment Overview Card -->
        <div class="col-md-3">
            <div class="card">
                <div class="card-header">
                    <h5>Equipment Overview</h5>
                </div>
                <div class="card-body">
                    @if(isset($recentEquipmentRequests) && $recentEquipmentRequests->count() > 0)
                        @foreach($recentEquipmentRequests->take(4) as $request)
                            <div class="mb-2 pb-2 border-bottom">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1">
                                            <a href="{{ route('pm.equipment-monitoring.show-request', $request) }}">{{ Str::limit($request->equipment_name, 20) }}</a>
                                        </h6>
                                        <small class="text-muted">
                                            By {{ $request->user->first_name }} {{ $request->user->last_name }}
                                        </small>
                                        <br>
                                        <small class="text-muted">
                                            {{ $request->project ? $request->project->name : 'Personal Use' }}
                                        </small>
                                    </div>
                                    <div class="text-end">
                                        <span class="badge bg-{{ $request->status_badge_color }}">
                                            {{ $request->formatted_status }}
                                        </span>
                                        @if($request->urgency_level === 'critical' || $request->urgency_level === 'high')
                                            <br>
                                            <span class="badge bg-{{ $request->urgency_badge_color }} mt-1">
                                                {{ $request->formatted_urgency }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                <div class="mt-1">
                                    <small class="text-muted">
                                        {{ $request->created_at->diffForHumans() }}
                                        @if($request->usage_type === 'project_site')
                                            <span class="badge bg-info ms-1">Project</span>
                                        @endif
                                    </small>
                                </div>
                            </div>
                        @endforeach
                        <div class="text-center mt-2">
                            <a href="{{ route('pm.equipment-monitoring.requests') }}" class="btn btn-sm btn-outline-secondary">View All Requests</a>
                        </div>
                    @else
                        <div class="text-center py-3">
                            <i class="fas fa-tools fa-2x text-muted mb-2"></i>
                            <p class="text-muted mb-2">No recent equipment requests</p>
                            <a href="{{ route('pm.equipment-monitoring.index') }}" class="btn btn-sm btn-outline-secondary">View Equipment</a>
                        </div>
                    @endif

                    @if(isset($equipmentNeedingAttention) && $equipmentNeedingAttention->count() > 0)
                        <div class="mt-3 pt-3 border-top">
                            <h6 class="text-warning">
                                <i class="fas fa-exclamation-triangle me-1"></i>Needs Attention
                            </h6>
                            @foreach($equipmentNeedingAttention->take(2) as $equipment)
                                <div class="mb-1">
                                    <small class="text-muted">
                                        <strong>{{ Str::limit($equipment->equipment_name, 15) }}</strong><br>
                                        {{ $equipment->project->name ?? 'Personal' }} - 
                                        @if($equipment->availability_status === 'out_of_order')
                                            <span class="text-danger">Out of Order</span>
                                        @elseif($equipment->availability_status === 'maintenance')
                                            <span class="text-warning">Under Maintenance</span>
                                        @elseif($equipment->next_maintenance_date && $equipment->next_maintenance_date <= now()->addDays(7))
                                            <span class="text-info">Maintenance Due</span>
                                        @endif
                                    </small>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5>Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-2">
                            <a href="{{ route('projects.create') }}" class="btn btn-outline-primary w-100 mb-2 h-100 d-flex flex-column justify-content-center align-items-center">
                                <i class="fas fa-plus fa-2x mb-2"></i>
                                <span>Create Project</span>
                            </a>
                        </div>
                        <div class="col-md-2">
                            <a href="{{ route('tasks.create') }}" class="btn btn-outline-success w-100 mb-2 h-100 d-flex flex-column justify-content-center align-items-center">
                                <i class="fas fa-tasks fa-2x mb-2"></i>
                                <span>Add Task</span>
                            </a>
                        </div>
                        <div class="col-md-2">
                            <a href="{{ route('pm.task-reports.index') }}" class="btn btn-outline-warning w-100 mb-2 h-100 d-flex flex-column justify-content-center align-items-center position-relative">
                                <i class="fas fa-clipboard-check fa-2x mb-2"></i>
                                <span>Task Reports</span>
                                @if(isset($taskReportsStats['pending_review']) && $taskReportsStats['pending_review'] > 0)
                                    <span class="badge bg-danger position-absolute top-0 end-0 translate-middle">{{ $taskReportsStats['pending_review'] }}</span>
                                @endif
                            </a>
                        </div>
                        <div class="col-md-2">
                            <a href="{{ route('pm.site-issues.index') }}" class="btn btn-outline-danger w-100 mb-2 h-100 d-flex flex-column justify-content-center align-items-center position-relative">
                                <i class="fas fa-exclamation-triangle fa-2x mb-2"></i>
                                <span>Site Issues</span>
                                @if(isset($siteIssuesStats['unacknowledged']) && $siteIssuesStats['unacknowledged'] > 0)
                                    <span class="badge bg-danger position-absolute top-0 end-0 translate-middle">{{ $siteIssuesStats['unacknowledged'] }}</span>
                                @endif
                            </a>
                        </div>
                        <div class="col-md-2">
                            <a href="{{ route('pm.site-photos.index') }}" class="btn btn-outline-primary w-100 mb-2 h-100 d-flex flex-column justify-content-center align-items-center position-relative">
                                <i class="fas fa-camera fa-2x mb-2"></i>
                                <span>Site Photos</span>
                                @if(isset($sitePhotosStats['submitted']) && $sitePhotosStats['submitted'] > 0)
                                    <span class="badge bg-warning position-absolute top-0 end-0 translate-middle">{{ $sitePhotosStats['submitted'] }}</span>
                                @endif
                            </a>
                        </div>
                        <div class="col-md-2">
                            <a href="{{ route('admin.progress-reports.create') }}" class="btn btn-outline-info w-100 mb-2 h-100 d-flex flex-column justify-content-center align-items-center">
                                <i class="fas fa-file-chart-line fa-2x mb-2"></i>
                                <span>New Report</span>
                            </a>
                        </div>
                    </div>
                    <!-- NEW: Second Row for Equipment Monitoring -->
                    <div class="row mt-3">
                        <div class="col-md-2">
                            <a href="{{ route('pm.equipment-monitoring.index') }}" class="btn btn-outline-secondary w-100 mb-2 h-100 d-flex flex-column justify-content-center align-items-center position-relative">
                                <i class="fas fa-tools fa-2x mb-2"></i>
                                <span>Equipment</span>
                                @if(isset($equipmentMonitoringStats['pending_requests']) && $equipmentMonitoringStats['pending_requests'] > 0)
                                    <span class="badge bg-warning position-absolute top-0 end-0 translate-middle">{{ $equipmentMonitoringStats['pending_requests'] }}</span>
                                @endif
                            </a>
                        </div>
                        <div class="col-md-2">
                            <a href="{{ route('pm.equipment-monitoring.maintenance-list') }}" class="btn btn-outline-info w-100 mb-2 h-100 d-flex flex-column justify-content-center align-items-center position-relative">
                                <i class="fas fa-wrench fa-2x mb-2"></i>
                                <span>Maintenance</span>
                                @if(isset($equipmentMonitoringStats['maintenance_overdue']) && $equipmentMonitoringStats['maintenance_overdue'] > 0)
                                    <span class="badge bg-danger position-absolute top-0 end-0 translate-middle">{{ $equipmentMonitoringStats['maintenance_overdue'] }}</span>
                                @endif
                            </a>
                        </div>
                        <div class="col-md-2">
                            <a href="{{ route('pm.equipment-monitoring.requests') }}" class="btn btn-outline-warning w-100 mb-2 h-100 d-flex flex-column justify-content-center align-items-center position-relative">
                                <i class="fas fa-clipboard-list fa-2x mb-2"></i>
                                <span>Equipment Requests</span>
                                @if(isset($equipmentMonitoringStats['urgent_requests']) && $equipmentMonitoringStats['urgent_requests'] > 0)
                                    <span class="badge bg-danger position-absolute top-0 end-0 translate-middle">{{ $equipmentMonitoringStats['urgent_requests'] }}</span>
                                @endif
                            </a>
                        </div>
                        <div class="col-md-2">
                            <a href="{{ route('pm.equipment-monitoring.report-summary') }}" class="btn btn-outline-dark w-100 mb-2 h-100 d-flex flex-column justify-content-center align-items-center">
                                <i class="fas fa-chart-bar fa-2x mb-2"></i>
                                <span>Equipment Reports</span>
                            </a>
                        </div>
                        <div class="col-md-4">
                            <!-- Spacer or additional actions can be added here -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Alert Cards -->
    @if(isset($criticalSiteIssues) && $criticalSiteIssues->count() > 0)
        <div class="row mt-4">
            <div class="col-md-12">
                <div class="alert alert-danger">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-exclamation-circle fa-2x me-3"></i>
                        <div class="flex-grow-1">
                            <strong>Critical Site Issues Require Attention!</strong><br>
                            You have {{ $criticalSiteIssues->count() }} critical site issues that need immediate attention.
                        </div>
                        <a href="{{ route('pm.site-issues.index', ['priority' => 'critical']) }}" class="btn btn-danger btn-sm ms-2">
                            <i class="fas fa-tools me-1"></i>Review Issues
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if(isset($taskReportsStats['overdue_reviews']) && $taskReportsStats['overdue_reviews'] > 0)
        <div class="row mt-4">
            <div class="col-md-12">
                <div class="alert alert-warning">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-exclamation-triangle fa-2x me-3"></i>
                        <div class="flex-grow-1">
                            <strong>Overdue Task Reports!</strong><br>
                            You have {{ $taskReportsStats['overdue_reviews'] }} task reports that have been waiting for review for more than 2 days.
                        </div>
                        <div class="d-flex gap-2">
                            <a href="{{ route('pm.task-reports.index', ['review_status' => 'pending']) }}" class="btn btn-warning btn-sm">
                                <i class="fas fa-eye me-1"></i>Review Now
                            </a>
                            <a href="{{ route('pm.task-reports.bulk-approve') }}" class="btn btn-outline-warning btn-sm">
                                <i class="fas fa-check-double me-1"></i>Bulk Approve
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if(isset($siteIssuesStats['unacknowledged']) && $siteIssuesStats['unacknowledged'] > 0)
        <div class="row mt-4">
            <div class="col-md-12">
                <div class="alert alert-info">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-eye-slash fa-2x me-3"></i>
                        <div class="flex-grow-1">
                            <strong>Unacknowledged Site Issues!</strong><br>
                            {{ $siteIssuesStats['unacknowledged'] }} site issues haven't been acknowledged yet.
                        </div>
                        <a href="{{ route('pm.site-issues.index', ['status' => 'open']) }}" class="btn btn-info btn-sm ms-2">
                            <i class="fas fa-search me-1"></i>Check Issues
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if(isset($sitePhotosStats['overdue_reviews']) && $sitePhotosStats['overdue_reviews'] > 0)
        <div class="row mt-4">
            <div class="col-md-12">
                <div class="alert alert-primary">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-camera fa-2x me-3"></i>
                        <div class="flex-grow-1">
                            <strong>Overdue Photo Reviews!</strong><br>
                            {{ $sitePhotosStats['overdue_reviews'] }} site photos have been waiting for review for more than 3 days.
                        </div>
                        <div class="d-flex gap-2">
                            <a href="{{ route('pm.site-photos.index', ['status' => 'submitted']) }}" class="btn btn-primary btn-sm">
                                <i class="fas fa-eye me-1"></i>Review Photos
                            </a>
                            <a href="{{ route('pm.site-photos.index') }}#bulk-approve" class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-check-double me-1"></i>Bulk Approve
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- NEW: Equipment Monitoring Alerts -->
    @if(isset($overdueEquipmentMaintenance) && $overdueEquipmentMaintenance->count() > 0)
        <div class="row mt-4">
            <div class="col-md-12">
                <div class="alert alert-danger">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-wrench fa-2x me-3"></i>
                        <div class="flex-grow-1">
                            <strong>Overdue Equipment Maintenance!</strong><br>
                            {{ $overdueEquipmentMaintenance->count() }} equipment maintenance schedules are overdue and require immediate attention.
                        </div>
                        <div class="d-flex gap-2">
                            <a href="{{ route('pm.equipment-monitoring.maintenance-list', ['status' => 'overdue']) }}" class="btn btn-danger btn-sm">
                                <i class="fas fa-exclamation-triangle me-1"></i>View Overdue
                            </a>
                            <a href="{{ route('pm.equipment-monitoring.maintenance-list') }}" class="btn btn-outline-danger btn-sm">
                                <i class="fas fa-calendar-alt me-1"></i>Full Schedule
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if(isset($pendingEquipmentRequests) && $pendingEquipmentRequests->count() > 0)
        <div class="row mt-4">
            <div class="col-md-12">
                <div class="alert alert-warning">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-clipboard-list fa-2x me-3"></i>
                        <div class="flex-grow-1">
                            <strong>Pending Equipment Requests for Review!</strong><br>
                            {{ $pendingEquipmentRequests->count() }} equipment requests from your projects are awaiting admin approval.
                            @if($pendingEquipmentRequests->where('urgency_level', 'critical')->count() > 0)
                                <span class="text-danger">({{ $pendingEquipmentRequests->where('urgency_level', 'critical')->count() }} marked as critical)</span>
                            @endif
                        </div>
                        <div class="d-flex gap-2">
                            <a href="{{ route('pm.equipment-monitoring.requests', ['status' => 'pending']) }}" class="btn btn-warning btn-sm">
                                <i class="fas fa-eye me-1"></i>Review Requests
                            </a>
                            @if($pendingEquipmentRequests->where('urgency_level', 'critical')->count() > 0)
                                <a href="{{ route('pm.equipment-monitoring.requests', ['urgency' => 'critical']) }}" class="btn btn-outline-danger btn-sm">
                                    <i class="fas fa-fire me-1"></i>Critical Only
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if(isset($equipmentNeedingAttention) && $equipmentNeedingAttention->count() > 0)
        <div class="row mt-4">
            <div class="col-md-12">
                <div class="alert alert-secondary">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-tools fa-2x me-3"></i>
                        <div class="flex-grow-1">
                            <strong>Equipment Requires Attention!</strong><br>
                            {{ $equipmentNeedingAttention->count() }} pieces of equipment in your projects need attention 
                            (maintenance due, out of order, or under maintenance).
                        </div>
                        <a href="{{ route('pm.equipment-monitoring.equipment-list', ['needs_attention' => 1]) }}" class="btn btn-secondary btn-sm ms-2">
                            <i class="fas fa-search me-1"></i>View Equipment
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if(isset($overdueReports) && $overdueReports->count() > 0)
        <div class="row mt-4">
            <div class="col-md-12">
                <div class="alert alert-secondary">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-clock fa-2x me-3"></i>
                        <div class="flex-grow-1">
                            <strong>Tasks Missing Reports!</strong><br>
                            {{ $overdueReports->count() }} tasks haven't submitted reports in the last 3 days.
                        </div>
                        <a href="{{ route('pm.task-reports.index') }}" class="btn btn-secondary btn-sm ms-2">
                            <i class="fas fa-search me-1"></i>Check Tasks
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @endif
    
    @if(isset($upcomingDeadlines) && $upcomingDeadlines > 5)
        <div class="row mt-4">
            <div class="col-md-12">
                <div class="alert alert-primary">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-calendar-alt fa-2x me-3"></i>
                        <div class="flex-grow-1">
                            <strong>Multiple Deadlines This Week!</strong><br>
                            You have {{ $upcomingDeadlines }} tasks due this week. Consider reviewing priorities and workload distribution.
                        </div>
                        <a href="{{ route('tasks.index', ['due_this_week' => 1]) }}" class="btn btn-primary btn-sm ms-2">
                            <i class="fas fa-list me-1"></i>View Tasks
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

<!-- Quick Review Modal -->
<div class="modal fade" id="quickReviewModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Quick Review</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="quickReviewForm">
                    <input type="hidden" id="reviewReportId">
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select" required>
                            <option value="approved">Approve</option>
                            <option value="reviewed">Mark as Reviewed</option>
                            <option value="needs_revision">Needs Revision</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Comments (Optional)</label>
                        <textarea name="comments" class="form-control" rows="3" 
                                  placeholder="Add your feedback..."></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Rating (Optional)</label>
                        <select name="rating" class="form-select">
                            <option value="">No Rating</option>
                            <option value="5">★★★★★ Excellent</option>
                            <option value="4">★★★★☆ Good</option>
                            <option value="3">★★★☆☆ Average</option>
                            <option value="2">★★☆☆☆ Poor</option>
                            <option value="1">★☆☆☆☆ Very Poor</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="submitQuickReview">Submit Review</button>
            </div>
        </div>
    </div>
</div>

<!-- Quick Photo Approve Modal -->
<div class="modal fade" id="quickPhotoApproveModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Quick Approve Photo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="quickPhotoApproveForm">
                    <input type="hidden" id="approvePhotoId">
                    <div class="mb-3">
                        <label class="form-label">Comments (Optional)</label>
                        <textarea name="comments" class="form-control" rows="3" 
                                  placeholder="Add approval comments..."></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Rating (Optional)</label>
                        <select name="rating" class="form-select">
                            <option value="">No Rating</option>
                            <option value="5">★★★★★ Excellent</option>
                            <option value="4">★★★★☆ Good</option>
                            <option value="3">★★★☆☆ Average</option>
                            <option value="2">★★☆☆☆ Poor</option>
                            <option value="1">★☆☆☆☆ Very Poor</option>
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-6">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="make_public" id="makePublic">
                                <label class="form-check-label" for="makePublic">
                                    Make Public
                                </label>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="make_featured" id="makeFeatured">
                                <label class="form-check-label" for="makeFeatured">
                                    Mark as Featured
                                </label>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" id="submitQuickPhotoApprove">Approve Photo</button>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
.opacity-75 {
    opacity: 0.75;
}
.card {
    border: none;
    border-radius: 0.5rem;
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    transition: box-shadow 0.15s ease-in-out;
}
.card:hover {
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
}
.btn-group .btn {
    margin-right: 2px;
}
.btn-group .btn:last-child {
    margin-right: 0;
}
.alert {
    border-radius: 0.5rem;
}
.badge {
    font-size: 0.75em;
}
.progress {
    height: 8px;
}
.border-bottom {
    border-bottom: 1px solid #dee2e6 !important;
}
.btn-xs {
    padding: 0.125rem 0.25rem;
    font-size: 0.75rem;
    line-height: 1;
    border-radius: 0.25rem;
}
.position-relative {
    position: relative !important;
}
.photo-thumbnail {
    border-radius: 0.25rem;
    transition: transform 0.2s;
}
.photo-thumbnail:hover {
    transform: scale(1.05);
}
/* NEW: Equipment monitoring specific styles */
.equipment-status-indicator {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    display: inline-block;
    margin-right: 5px;
}
.equipment-available { background-color: #28a745; }
.equipment-in-use { background-color: #007bff; }
.equipment-maintenance { background-color: #ffc107; }
.equipment-out-of-order { background-color: #dc3545; }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-refresh notification counts every 5 minutes
    setInterval(function() {
        // Refresh site issues stats
        fetch('{{ route("pm.site-issues.api.stats") }}')
            .then(response => response.json())
            .then(data => {
                // Update site issues notification badge
                const siteIssuesBadge = document.querySelector('a[href*="site-issues"] .badge');
                if (siteIssuesBadge && data.unacknowledged !== undefined) {
                    if (data.unacknowledged > 0) {
                        siteIssuesBadge.textContent = data.unacknowledged;
                        siteIssuesBadge.style.display = 'inline';
                    } else {
                        siteIssuesBadge.style.display = 'none';
                    }
                }
            })
            .catch(error => console.log('Error fetching task reports stats:', error));

        // Refresh site photos stats
        fetch('{{ route("pm.site-photos.api.stats") }}')
            .then(response => response.json())
            .then(data => {
                // Update site photos notification badge
                const sitePhotosBadge = document.querySelector('a[href*="site-photos"] .badge');
                if (sitePhotosBadge && data.pending_review !== undefined) {
                    if (data.pending_review > 0) {
                        sitePhotosBadge.textContent = data.pending_review;
                        sitePhotosBadge.style.display = 'inline';
                    } else {
                        sitePhotosBadge.style.display = 'none';
                    }
                }
            })
            .catch(error => console.log('Error fetching site photos stats:', error));

        // NEW: Refresh equipment monitoring stats
        fetch('{{ route("pm.equipment-monitoring.api.stats") }}')
            .then(response => response.json())
            .then(data => {
                // Update equipment monitoring notification badges
                const equipmentBadge = document.querySelector('a[href*="equipment-monitoring.index"] .badge');
                if (equipmentBadge && data.pending_requests !== undefined) {
                    if (data.pending_requests > 0) {
                        equipmentBadge.textContent = data.pending_requests;
                        equipmentBadge.style.display = 'inline';
                    } else {
                        equipmentBadge.style.display = 'none';
                    }
                }

                const maintenanceBadge = document.querySelector('a[href*="maintenance-list"] .badge');
                if (maintenanceBadge && data.maintenance_overdue !== undefined) {
                    if (data.maintenance_overdue > 0) {
                        maintenanceBadge.textContent = data.maintenance_overdue;
                        maintenanceBadge.style.display = 'inline';
                    } else {
                        maintenanceBadge.style.display = 'none';
                    }
                }

                const urgentRequestsBadge = document.querySelector('a[href*="equipment-monitoring.requests"] .badge');
                if (urgentRequestsBadge && data.urgent_requests !== undefined) {
                    if (data.urgent_requests > 0) {
                        urgentRequestsBadge.textContent = data.urgent_requests;
                        urgentRequestsBadge.style.display = 'inline';
                    } else {
                        urgentRequestsBadge.style.display = 'none';
                    }
                }
            })
            .catch(error => console.log('Error fetching equipment monitoring stats:', error));
    }, 300000); // 5 minutes

    // Quick approve functionality for task reports
    window.quickApprove = function(reportId) {
        if (confirm('Are you sure you want to approve this report?')) {
            fetch(`/pm/task-reports/${reportId}/quick-review`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    status: 'approved',
                    comments: 'Quick approved from dashboard',
                    rating: null
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Error approving report');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error approving report');
            });
        }
    };

    // Quick approve functionality for site photos
    window.quickApprovePhoto = function(photoId) {
        document.getElementById('approvePhotoId').value = photoId;
        const modal = new bootstrap.Modal(document.getElementById('quickPhotoApproveModal'));
        modal.show();
    };

    // Submit quick photo approve
    document.getElementById('submitQuickPhotoApprove')?.addEventListener('click', function() {
        const form = document.getElementById('quickPhotoApproveForm');
        const formData = new FormData(form);
        const photoId = document.getElementById('approvePhotoId').value;
        
        fetch(`/pm/site-photos/${photoId}/quick-approve`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                comments: formData.get('comments'),
                rating: formData.get('rating'),
                make_public: formData.has('make_public'),
                make_featured: formData.has('make_featured')
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success || response.ok) {
                bootstrap.Modal.getInstance(document.getElementById('quickPhotoApproveModal')).hide();
                location.reload();
            } else {
                alert('Error approving photo: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error approving photo');
        });
    });

    // Quick approve all functionality
    window.quickApproveAll = function() {
        if (confirm('Are you sure you want to approve all recent pending reports?')) {
            fetch('{{ route("pm.task-reports.bulk-approve") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    action: 'approve_recent',
                    approval_type: 'standard',
                    approval_comments: 'Bulk approved from dashboard'
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message || 'Error during bulk approval');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error during bulk approval');
            });
        }
    };

    // Show review modal functionality
    window.showReviewModal = function() {
        // Fetch the next pending report
        fetch('{{ route("pm.task-reports.index") }}?status=pending&limit=1')
            .then(response => response.json())
            .then(data => {
                if (data.reports && data.reports.length > 0) {
                    const report = data.reports[0];
                    document.getElementById('reviewReportId').value = report.id;
                    const modal = new bootstrap.Modal(document.getElementById('quickReviewModal'));
                    modal.show();
                } else {
                    alert('No pending reports to review');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error fetching pending reports');
            });
    };

    // Submit quick review
    document.getElementById('submitQuickReview')?.addEventListener('click', function() {
        const formData = new FormData(document.getElementById('quickReviewForm'));
        const reportId = document.getElementById('reviewReportId').value;
        
        fetch(`/pm/task-reports/${reportId}/quick-review`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                status: formData.get('status'),
                comments: formData.get('comments'),
                rating: formData.get('rating')
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                bootstrap.Modal.getInstance(document.getElementById('quickReviewModal')).hide();
                location.reload();
            } else {
                alert('Error updating report status');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error updating report status');
        });
    });

    // Helper function to update stats display
    function updateStatsDisplay(type, data) {
        Object.keys(data).forEach(function(key) {
            const element = document.querySelector(`[data-stat="${type}-${key}"]`);
            if (element) {
                element.textContent = data[key];
            }
        });
    }

    // NEW: Equipment monitoring specific functions
    window.quickApproveEquipmentRequest = function(requestId) {
        if (confirm('Are you sure you want to approve this equipment request?')) {
            fetch(`/admin/equipment-monitoring/requests/${requestId}/approve`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    admin_notes: 'Quick approved from PM dashboard'
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Error approving equipment request');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error approving equipment request');
            });
        }
    };

    // Equipment status update function
    window.updateEquipmentStatus = function(equipmentId, newStatus) {
        fetch(`/sc/equipment-monitoring/equipment/${equipmentId}/availability`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                availability_status: newStatus
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update the UI element
                const statusElement = document.querySelector(`[data-equipment-id="${equipmentId}"] .equipment-status`);
                if (statusElement) {
                    statusElement.textContent = newStatus.replace('_', ' ').toUpperCase();
                    statusElement.className = `equipment-status badge bg-${getStatusColor(newStatus)}`;
                }
            } else {
                alert('Error updating equipment status');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error updating equipment status');
        });
    };

    // Helper function for equipment status colors
    function getStatusColor(status) {
        switch(status) {
            case 'available': return 'success';
            case 'in_use': return 'primary';
            case 'maintenance': return 'warning';
            case 'out_of_order': return 'danger';
            default: return 'secondary';
        }
    }

    // Equipment maintenance reminder
    window.scheduleMaintenanceReminder = function(equipmentId, days) {
        const reminderDate = new Date();
        reminderDate.setDate(reminderDate.getDate() + days);
        
        // This would typically involve creating a calendar event or notification
        alert(`Maintenance reminder set for ${reminderDate.toDateString()}`);
    };

    // Auto-dismiss alerts after 10 seconds
    setTimeout(function() {
        document.querySelectorAll('.alert').forEach(function(alert) {
            if (!alert.classList.contains('alert-danger')) {
                alert.style.transition = 'opacity 0.5s';
                alert.style.opacity = '0.7';
            }
        });
    }, 10000);

    // Equipment monitoring dashboard specific initialization
    initializeEquipmentMonitoringDashboard();
});

// NEW: Equipment monitoring dashboard initialization function
function initializeEquipmentMonitoringDashboard() {
    // Add equipment status indicators
    document.querySelectorAll('.equipment-item').forEach(function(item) {
        const status = item.dataset.status;
        const indicator = item.querySelector('.equipment-status-indicator');
        if (indicator) {
            indicator.classList.add(`equipment-${status.replace('_', '-')}`);
        }
    });

    // Initialize equipment tooltips
    const equipmentTooltips = document.querySelectorAll('[data-bs-toggle="tooltip"][data-equipment-info]');
    equipmentTooltips.forEach(function(tooltip) {
        new bootstrap.Tooltip(tooltip);
    });

    // Add hover effects for equipment cards
    document.querySelectorAll('.equipment-card').forEach(function(card) {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-2px)';
            this.style.boxShadow = '0 4px 8px rgba(0,0,0,0.1)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
            this.style.boxShadow = '0 2px 4px rgba(0,0,0,0.05)';
        });
    });

    // Initialize maintenance calendar if present
    const maintenanceCalendar = document.getElementById('maintenanceCalendar');
    if (maintenanceCalendar) {
        // Initialize calendar with maintenance events
        // This would typically use a calendar library like FullCalendar
        console.log('Maintenance calendar initialized');
    }
}
</script>
@endpush
@endsection