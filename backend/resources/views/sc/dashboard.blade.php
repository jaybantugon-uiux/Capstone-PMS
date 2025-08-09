{{-- Updated sc/dashboard.blade.php with Site Photos integration --}}
@extends('app')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1 class="h2">Site Coordinator Dashboard</h1>
                    <div class="d-flex gap-2">
                        <a href="{{ route('sc.task-reports.create') }}" class="btn btn-success">
                            <i class="fas fa-file-alt me-1"></i> Create Report
                        </a>
                        <a href="{{ route('sc.site-issues.create') }}" class="btn btn-danger">
                            <i class="fas fa-exclamation-triangle me-1"></i> Report Issue
                        </a>
                        <a href="{{ route('sc.site-photos.create') }}" class="btn btn-info">
                            <i class="fas fa-camera me-1"></i> Upload Photo
                        </a>
                        <a href="{{ route('notifications.index') }}" class="btn btn-outline-info">
                            <i class="fas fa-bell me-1"></i> Notifications 
                            @if(auth()->user()->unreadNotifications()->count() > 0)
                                <span class="badge bg-danger">{{ auth()->user()->unreadNotifications()->count() }}</span>
                            @endif
                        </a>
                    </div>
                </div>

                <!-- Statistics Cards -->
                <div class="row mb-4">
                    <div class="col-lg-3 col-md-6">
                        <div class="card bg-primary text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h4 class="card-title">{{ $totalTasks }}</h4>
                                        <p class="card-text">Total Tasks</p>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="fas fa-tasks fa-2x"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="card bg-warning text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h4 class="card-title">{{ $pendingTasks }}</h4>
                                        <p class="card-text">Pending Tasks</p>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="fas fa-clock fa-2x"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="card bg-info text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h4 class="card-title">{{ $inProgressTasks }}</h4>
                                        <p class="card-text">In Progress</p>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="fas fa-spinner fa-2x"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="card bg-success text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h4 class="card-title">{{ $completedTasks }}</h4>
                                        <p class="card-text">Completed</p>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="fas fa-check-circle fa-2x"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Site Issues, Reports, and Photos Statistics -->
                <div class="row mb-4">
                    <!-- Site Issues Statistics -->
                    @if(isset($siteIssuesStats))
                    <div class="col-lg-3 col-md-6">
                        <div class="card border-warning">
                            <div class="card-header bg-warning text-dark text-center">
                                <h6 class="mb-0"><i class="fas fa-exclamation-triangle me-1"></i>Site Issues</h6>
                            </div>
                            <div class="card-body p-2">
                                <div class="row text-center">
                                    <div class="col-6">
                                        <h5 class="mb-0 text-secondary">{{ $siteIssuesStats['total'] ?? 0 }}</h5>
                                        <small class="text-muted">Total</small>
                                    </div>
                                    <div class="col-6">
                                        <h5 class="mb-0 text-danger">{{ $siteIssuesStats['critical'] ?? 0 }}</h5>
                                        <small class="text-muted">Critical</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Task Reports Statistics -->
                    @if(isset($reportStats))
                    <div class="col-lg-3 col-md-6">
                        <div class="card border-success">
                            <div class="card-header bg-success text-white text-center">
                                <h6 class="mb-0"><i class="fas fa-file-alt me-1"></i>Task Reports</h6>
                            </div>
                            <div class="card-body p-2">
                                <div class="row text-center">
                                    <div class="col-6">
                                        <h5 class="mb-0 text-primary">{{ $reportStats['total_reports'] }}</h5>
                                        <small class="text-muted">Total</small>
                                    </div>
                                    <div class="col-6">
                                        <h5 class="mb-0 text-warning">{{ $reportStats['pending_review'] }}</h5>
                                        <small class="text-muted">Pending</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Site Photos Statistics -->
                    @if(isset($sitePhotosStats))
                    <div class="col-lg-3 col-md-6">
                        <div class="card border-info">
                            <div class="card-header bg-info text-white text-center">
                                <h6 class="mb-0"><i class="fas fa-camera me-1"></i>Site Photos</h6>
                            </div>
                            <div class="card-body p-2">
                                <div class="row text-center">
                                    <div class="col-6">
                                        <h5 class="mb-0 text-primary">{{ $sitePhotosStats['total'] ?? 0 }}</h5>
                                        <small class="text-muted">Total</small>
                                    </div>
                                    <div class="col-6">
                                        <h5 class="mb-0 text-success">{{ $sitePhotosStats['featured'] ?? 0 }}</h5>
                                        <small class="text-muted">Featured</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Average Rating -->
                    @if(isset($reportStats) && $reportStats['average_rating'])
                    <div class="col-lg-3 col-md-6">
                        <div class="card border-dark">
                            <div class="card-header bg-dark text-white text-center">
                                <h6 class="mb-0"><i class="fas fa-star me-1"></i>Performance</h6>
                            </div>
                            <div class="card-body p-2 text-center">
                                <h5 class="mb-0 text-warning">{{ number_format($reportStats['average_rating'] ?? 0, 1) }}</h5>
                                <small class="text-muted">Avg Rating</small>
                                <div class="mt-1">
                                    @for($i = 1; $i <= 5; $i++)
                                        @if($i <= round($reportStats['average_rating'] ?? 0))
                                            <i class="fas fa-star text-warning"></i>
                                        @else
                                            <i class="far fa-star text-muted"></i>
                                        @endif
                                    @endfor
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>

                <div class="row">
                    <!-- Critical Site Issues Alert -->
                    @if(isset($criticalSiteIssues) && $criticalSiteIssues->count() > 0)
                    <div class="col-md-12 mb-4">
                        <div class="alert alert-danger" role="alert">
                            <h5 class="alert-heading">
                                <i class="fas fa-exclamation-triangle me-2"></i>Critical Site Issues Requiring Attention
                            </h5>
                            <div class="row">
                                @foreach($criticalSiteIssues->take(3) as $issue)
                                <div class="col-md-4 mb-2">
                                    <div class="d-flex justify-content-between align-items-center p-2 bg-white rounded">
                                        <div>
                                            <strong>{{ $issue->issue_title }}</strong>
                                            <br><small>{{ $issue->project->name }}</small>
                                            <br><small class="text-danger">{{ $issue->age }}</small>
                                        </div>
                                        <a href="{{ route('sc.site-issues.show', $issue) }}" class="btn btn-sm btn-danger">
                                            View
                                        </a>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                            @if($criticalSiteIssues->count() > 3)
                                <div class="mt-2">
                                    <a href="{{ route('sc.site-issues.index', ['priority' => 'critical']) }}" class="btn btn-sm btn-outline-danger">
                                        View All {{ $criticalSiteIssues->count() }} Critical Issues
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                    @endif

                    <!-- Tasks Needing Reports -->
                    @if(isset($tasksNeedingReports) && $tasksNeedingReports->count() > 0)
                    <div class="col-lg-6 mb-4">
                        <div class="card border-warning">
                            <div class="card-header bg-warning text-dark">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-exclamation-triangle me-2"></i>Tasks Needing Reports
                                </h5>
                            </div>
                            <div class="card-body">
                                @foreach($tasksNeedingReports->take(4) as $task)
                                    <div class="d-flex justify-content-between align-items-center mb-2 p-2 bg-light rounded">
                                        <div>
                                            <strong>{{ $task->task_name }}</strong>
                                            <br><small class="text-muted">{{ $task->project->name }}</small>
                                        </div>
                                        <a href="{{ route('sc.task-reports.create', ['task_id' => $task->id]) }}" 
                                           class="btn btn-sm btn-warning">
                                            <i class="fas fa-plus"></i> Report
                                        </a>
                                    </div>
                                @endforeach
                                @if($tasksNeedingReports->count() > 4)
                                    <div class="text-center mt-2">
                                        <small class="text-muted">And {{ $tasksNeedingReports->count() - 4 }} more tasks...</small>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Recent Site Photos -->
                    @if(isset($recentSitePhotos) && $recentSitePhotos->count() > 0)
                    <div class="col-lg-{{ isset($tasksNeedingReports) && $tasksNeedingReports->count() > 0 ? '6' : '12' }} mb-4">
                        <div class="card border-info">
                            <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-camera me-2"></i>Recent Photos
                                </h5>
                                <a href="{{ route('sc.site-photos.index') }}" class="btn btn-sm btn-outline-light">View All</a>
                            </div>
                            <div class="card-body">
                                @foreach($recentSitePhotos as $photo)
                                    <div class="d-flex justify-content-between align-items-start mb-3 p-2 bg-light rounded">
                                        <div class="flex-grow-1">
                                            <a href="{{ route('sc.site-photos.show', $photo) }}" class="text-decoration-none fw-bold">
                                                {{ $photo->title }}
                                            </a>
                                            <br><small class="text-muted">{{ $photo->project->name }}</small>
                                            <br><small class="text-muted">{{ $photo->formatted_photo_date }}</small>
                                        </div>
                                        <div class="text-end">
                                            <span class="badge bg-{{ $photo->submission_status_badge_color }}">
                                                {{ $photo->formatted_submission_status }}
                                            </span>
                                            @if($photo->is_featured)
                                                <br><span class="badge bg-warning mt-1">
                                                    <i class="fas fa-star"></i> Featured
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    @endif
                </div>

                <div class="row">
                    <!-- Recent Site Issues -->
                    @if(isset($recentSiteIssues) && $recentSiteIssues->count() > 0)
                    <div class="col-lg-6 mb-4">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-exclamation-triangle me-2"></i>Recent Site Issues
                                </h5>
                                <a href="{{ route('sc.site-issues.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
                            </div>
                            <div class="card-body">
                                @foreach($recentSiteIssues as $issue)
                                    <div class="d-flex justify-content-between align-items-start mb-3 p-2 bg-light rounded">
                                        <div class="flex-grow-1">
                                            <a href="{{ route('sc.site-issues.show', $issue) }}" class="text-decoration-none fw-bold">
                                                {{ $issue->issue_title }}
                                            </a>
                                            <br><small class="text-muted">{{ $issue->project->name }}</small>
                                            <br><small class="text-muted">{{ $issue->formatted_reported_at }}</small>
                                        </div>
                                        <div class="text-end">
                                            <span class="badge bg-{{ $issue->status_badge_color }}">
                                                {{ $issue->formatted_status }}
                                            </span>
                                            <br><span class="badge bg-{{ $issue->priority_badge_color }} mt-1">
                                                {{ $issue->formatted_priority }}
                                            </span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Recent Task Reports -->
                    @if(isset($recentReports) && $recentReports->count() > 0)
                    <div class="col-lg-{{ isset($recentSiteIssues) && $recentSiteIssues->count() > 0 ? '6' : '12' }} mb-4">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-file-alt me-2"></i>Recent Reports
                                </h5>
                                <a href="{{ route('sc.task-reports.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
                            </div>
                            <div class="card-body">
                                @foreach($recentReports as $report)
                                    <div class="d-flex justify-content-between align-items-start mb-3 p-2 bg-light rounded">
                                        <div class="flex-grow-1">
                                            <a href="{{ route('sc.task-reports.show', $report) }}" class="text-decoration-none fw-bold">
                                                {{ $report->report_title }}
                                            </a>
                                            <br><small class="text-muted">{{ $report->task->task_name }}</small>
                                            <br><small class="text-muted">{{ $report->formatted_report_date }}</small>
                                        </div>
                                        <div class="text-end">
                                            <span class="badge bg-{{ $report->review_status_badge_color }}">
                                                {{ $report->formatted_review_status }}
                                            </span>
                                            @if($report->admin_rating)
                                                <br><div class="mt-1">{!! $report->rating_stars !!}</div>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    @endif
                </div>

                <!-- Projects -->
                <div class="row">
                    <div class="col-md-12 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-project-diagram me-2"></i>My Projects
                                </h5>
                            </div>
                            <div class="card-body">
                                @if($projects->count() > 0)
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <thead>
                                                <tr>
                                                    <th>Project Name</th>
                                                    <th>Tasks Assigned</th>
                                                    <th>Site Issues</th>
                                                    <th>Photos</th>
                                                    <th>Status</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($projects as $project)
                                                    <tr>
                                                        <td>
                                                            <a href="{{ route('projects.show', $project) }}" class="text-decoration-none">
                                                                {{ $project->name }}
                                                            </a>
                                                        </td>
                                                        <td>{{ $project->tasks_count }}</td>
                                                        <td>
                                                            @if($project->site_issues_count > 0)
                                                                <span class="badge bg-warning">{{ $project->site_issues_count }}</span>
                                                                @if($project->critical_site_issues_count > 0)
                                                                    <span class="badge bg-danger ms-1">{{ $project->critical_site_issues_count }} Critical</span>
                                                                @endif
                                                            @else
                                                                <span class="text-muted">None</span>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @php
                                                                $photoCount = \App\Models\SitePhoto::where('project_id', $project->id)
                                                                    ->where('user_id', auth()->id())
                                                                    ->count();
                                                                $featuredCount = \App\Models\SitePhoto::where('project_id', $project->id)
                                                                    ->where('user_id', auth()->id())
                                                                    ->where('is_featured', true)
                                                                    ->count();
                                                            @endphp
                                                            @if($photoCount > 0)
                                                                <span class="badge bg-info">{{ $photoCount }}</span>
                                                                @if($featuredCount > 0)
                                                                    <span class="badge bg-warning ms-1"><i class="fas fa-star"></i> {{ $featuredCount }}</span>
                                                                @endif
                                                            @else
                                                                <span class="text-muted">None</span>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            <span class="badge bg-{{ $project->status_badge_color }}">
                                                                {{ $project->formatted_status }}
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <div class="btn-group btn-group-sm">
                                                                <a href="{{ route('projects.show', $project) }}" class="btn btn-outline-primary" title="View Project">
                                                                    <i class="fas fa-eye"></i>
                                                                </a>
                                                                <a href="{{ route('sc.site-photos.create', ['project_id' => $project->id]) }}" class="btn btn-outline-info" title="Upload Photo">
                                                                    <i class="fas fa-camera"></i>
                                                                </a>
                                                                <a href="{{ route('sc.site-issues.create', ['project_id' => $project->id]) }}" class="btn btn-outline-danger" title="Report Issue">
                                                                    <i class="fas fa-exclamation-triangle"></i>
                                                                </a>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <p class="text-center text-muted">No projects found.</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Tasks -->
                <div class="row mt-4">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-tasks me-2"></i>Recent Tasks
                                </h5>
                                <a href="{{ route('tasks.index') }}" class="btn btn-sm btn-outline-primary">View All Tasks</a>
                            </div>
                            <div class="card-body">
                                @if($tasks->count() > 0)
                                    <div class="table-responsive">
                                        <table class="table">
                                            <thead>
                                                <tr>
                                                    <th>Name</th>
                                                    <th>Project</th>
                                                    <th>Status</th>
                                                    <th>Due Date</th>
                                                    <th>Progress</th>
                                                    <th>Site Issues</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($tasks as $task)
                                                    <tr class="{{ $task->is_overdue ? 'table-warning' : '' }}">
                                                        <td>
                                                            <a href="{{ route('tasks.show', $task) }}" class="text-decoration-none">
                                                                {{ $task->task_name }}
                                                            </a>
                                                        </td>
                                                        <td>{{ $task->project->name }}</td>
                                                        <td>
                                                            <span class="badge bg-{{ $task->status_badge_color }}">
                                                                {{ $task->formatted_status }}
                                                            </span>
                                                        </td>
                                                        <td>
                                                            {{ $task->formatted_due_date ?? 'N/A' }}
                                                            @if($task->is_overdue)
                                                                <br><small class="text-danger">Overdue</small>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @if($task->progress_percentage !== null)
                                                                <div class="progress" style="height: 20px;">
                                                                    <div class="progress-bar bg-{{ $task->progress_color }}" 
                                                                         role="progressbar" 
                                                                         style="width: {{ $task->progress_percentage }}%">
                                                                        {{ $task->progress_percentage }}%
                                                                    </div>
                                                                </div>
                                                            @else
                                                                <span class="text-muted">N/A</span>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @if($task->site_issues_count > 0)
                                                                <span class="badge bg-warning">{{ $task->site_issues_count }}</span>
                                                                @if($task->hasCriticalSiteIssues())
                                                                    <span class="badge bg-danger ms-1">Critical</span>
                                                                @endif
                                                            @else
                                                                <span class="text-muted">None</span>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            <div class="btn-group btn-group-sm">
                                                                <a href="{{ route('tasks.show', $task) }}" class="btn btn-outline-primary" title="View Task">
                                                                    <i class="fas fa-eye"></i>
                                                                </a>
                                                                <a href="{{ route('sc.site-photos.create', ['task_id' => $task->id]) }}" 
                                                                   class="btn btn-outline-info" title="Upload Photo">
                                                                    <i class="fas fa-camera"></i>
                                                                </a>
                                                                <a href="{{ route('sc.task-reports.create', ['task_id' => $task->id]) }}" 
                                                                   class="btn btn-outline-success" title="Create Report">
                                                                    <i class="fas fa-file-alt"></i>
                                                                </a>
                                                                <a href="{{ route('sc.site-issues.create', ['task_id' => $task->id]) }}" 
                                                                   class="btn btn-outline-danger" title="Report Issue">
                                                                    <i class="fas fa-exclamation-triangle"></i>
                                                                </a>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="d-flex justify-content-center">
                                        {{ $tasks->links() }}
                                    </div>
                                @else
                                    <p class="text-center text-muted">No tasks assigned.</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="row mt-4">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-bolt me-2"></i>Quick Actions
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-lg-2 col-md-4 col-6">
                                        <a href="{{ route('sc.task-reports.create') }}" class="btn btn-outline-success w-100 mb-2 h-100 d-flex flex-column justify-content-center align-items-center">
                                            <i class="fas fa-file-alt fa-2x mb-2"></i>
                                            <span>Create Report</span>
                                        </a>
                                    </div>
                                    <div class="col-lg-2 col-md-4 col-6">
                                        <a href="{{ route('sc.site-issues.create') }}" class="btn btn-outline-danger w-100 mb-2 h-100 d-flex flex-column justify-content-center align-items-center">
                                            <i class="fas fa-exclamation-triangle fa-2x mb-2"></i>
                                            <span>Report Issue</span>
                                        </a>
                                    </div>
                                    <div class="col-lg-2 col-md-4 col-6">
                                        <a href="{{ route('sc.site-photos.create') }}" class="btn btn-outline-info w-100 mb-2 h-100 d-flex flex-column justify-content-center align-items-center">
                                            <i class="fas fa-camera fa-2x mb-2"></i>
                                            <span>Upload Photo</span>
                                        </a>
                                    </div>
                                    <div class="col-lg-2 col-md-4 col-6">
                                        <a href="{{ route('sc.task-reports.index') }}" class="btn btn-outline-primary w-100 mb-2 h-100 d-flex flex-column justify-content-center align-items-center">
                                            <i class="fas fa-list fa-2x mb-2"></i>
                                            <span>My Reports</span>
                                        </a>
                                    </div>
                                    <div class="col-lg-2 col-md-4 col-6">
                                        <a href="{{ route('sc.site-issues.index') }}" class="btn btn-outline-warning w-100 mb-2 h-100 d-flex flex-column justify-content-center align-items-center">
                                            <i class="fas fa-exclamation-circle fa-2x mb-2"></i>
                                            <span>Site Issues</span>
                                        </a>
                                    </div>
                                    <div class="col-lg-2 col-md-4 col-6">
                                        <a href="{{ route('sc.site-photos.index') }}" class="btn btn-outline-secondary w-100 mb-2 h-100 d-flex flex-column justify-content-center align-items-center">
                                            <i class="fas fa-images fa-2x mb-2"></i>
                                            <span>My Photos</span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
<style>
.table-warning {
    --bs-table-accent-bg: var(--bs-warning-bg-subtle);
}
.card.border-primary {
    border-color: var(--bs-primary) !important;
}
.card.border-warning {
    border-color: var(--bs-warning) !important;
}
.card.border-success {
    border-color: var(--bs-success) !important;
}
.card.border-info {
    border-color: var(--bs-info) !important;
}
.card.border-danger {
    border-color: var(--bs-danger) !important;
}
.card.border-secondary {
    border-color: var(--bs-secondary) !important;
}
.card.border-dark {
    border-color: var(--bs-dark) !important;
}
.alert-danger .btn-danger {
    --bs-btn-bg: #dc3545;
    --bs-btn-border-color: #dc3545;
    --bs-btn-hover-bg: #bb2d3b;
    --bs-btn-hover-border-color: #b02a37;
}
.btn-group-sm .btn {
    padding: 0.25rem 0.5rem;
    font-size: 0.75rem;
}
.card-header h6 {
    font-weight: 600;
}
.quick-action-card {
    min-height: 120px;
}
@media (max-width: 768px) {
    .btn-group-sm {
        display: flex;
        flex-direction: column;
    }
    .btn-group-sm .btn {
        margin-bottom: 2px;
    }
    .table-responsive {
        font-size: 0.9rem;
    }
    .quick-action-card {
        min-height: 100px;
    }
}
</style>
@endpush