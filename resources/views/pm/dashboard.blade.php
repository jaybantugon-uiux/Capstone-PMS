@extends('app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h2">Project Manager Dashboard</h1>
                    <p class="text-muted mb-0">Welcome back, {{ auth()->user()->full_name }}</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('projects.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> New Project
                    </a>
                    <a href="{{ route('tasks.create') }}" class="btn btn-success">
                        <i class="fas fa-tasks"></i> Add Task
                    </a>
                    <a href="{{ route('reports.index') }}" class="btn btn-outline-info">
                        <i class="fas fa-chart-line"></i> Reports
                    </a>
                </div>
            </div>

            <!-- Key Metrics -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h3 class="text-primary mb-1">{{ $myProjects }}</h3>
                                    <p class="text-muted mb-0 small">My Projects</p>
                                </div>
                                <div class="text-primary">
                                    <i class="fas fa-briefcase fa-2x opacity-75"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h3 class="text-success mb-1">{{ $activeProjects }}</h3>
                                    <p class="text-muted mb-0 small">Active Projects</p>
                                </div>
                                <div class="text-success">
                                    <i class="fas fa-play-circle fa-2x opacity-75"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h3 class="text-info mb-1">{{ $tasksAssigned }}</h3>
                                    <p class="text-muted mb-0 small">Tasks Assigned</p>
                                </div>
                                <div class="text-info">
                                    <i class="fas fa-user-check fa-2x opacity-75"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h3 class="text-warning mb-1">{{ $upcomingDeadlines }}</h3>
                                    <p class="text-muted mb-0 small">Due This Week</p>
                                </div>
                                <div class="text-warning">
                                    <i class="fas fa-clock fa-2x opacity-75"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Project Progress Overview -->
            <div class="row mb-4">
                <div class="col-md-8">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white border-bottom">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="card-title mb-0">Project Progress Overview</h5>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                        Filter
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" href="#">All Projects</a></li>
                                        <li><a class="dropdown-item" href="#">Active Only</a></li>
                                        <li><a class="dropdown-item" href="#">Behind Schedule</a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            @if($projectProgress->count() > 0)
                                @foreach($projectProgress as $project)
                                    <div class="mb-3">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <div>
                                                <h6 class="mb-0">
                                                    <a href="{{ route('projects.show', $project) }}" class="text-decoration-none">
                                                        {{ $project->name }}
                                                    </a>
                                                </h6>
                                                <small class="text-muted">
                                                    Due: {{ $project->formatted_due_date }} • 
                                                    {{ $project->team_members_count }} members
                                                </small>
                                            </div>
                                            <div class="text-end">
                                                <span class="badge bg-{{ $project->priority_badge_color }}">
                                                    {{ $project->priority }}
                                                </span>
                                                @if($project->is_behind_schedule)
                                                    <span class="badge bg-danger ms-1">Behind</span>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="progress" style="height: 8px;">
                                            <div class="progress-bar bg-{{ $project->progress_color }}" 
                                                 role="progressbar" 
                                                 aria-valuenow="{{ $project->completion_percentage }}" 
                                                 aria-valuemin="0" 
                                                 aria-valuemax="100">
                                            </div>
                                        </div>
                                        <div class="d-flex justify-content-between mt-1">
                                            <small class="text-muted">{{ $project->completed_tasks }}/{{ $project->total_tasks }} tasks</small>
                                            <small class="text-muted">{{ $project->completion_percentage }}%</small>
                                        </div>
                                    </div>
                                @endforeach
                            @else
                                <div class="text-center py-4">
                                    <i class="fas fa-project-diagram fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">No projects to display</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Team Performance -->
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white border-bottom">
                            <h5 class="card-title mb-0">Team Performance</h5>
                        </div>
                        <div class="card-body">
                            @if($teamPerformance->count() > 0)
                                @foreach($teamPerformance as $member)
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-circle me-3">
                                                {{ substr($member->full_name, 0, 1) }}
                                            </div>
                                            <div>
                                                <h6 class="mb-0">{{ $member->full_name }}</h6>
                                                <small class="text-muted">{{ $member->role }}</small>
                                            </div>
                                        </div>
                                        <div class="text-end">
                                            <div class="text-success fw-bold">{{ $member->completion_rate }}%</div>
                                            <small class="text-muted">{{ $member->active_tasks }} active</small>
                                        </div>
                                    </div>
                                @endforeach
                            @else
                                <p class="text-muted text-center py-3">No team data available</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Upcoming Deadlines -->
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">Upcoming Deadlines</h5>
                            <a href="{{ route('tasks.calendar') }}" class="btn btn-sm btn-outline-primary">Calendar View</a>
                        </div>
                        <div class="card-body">
                            @if($upcomingTasks->count() > 0)
                                <div class="list-group list-group-flush">
                                    @foreach($upcomingTasks as $task)
                                        <div class="list-group-item px-0 border-0 {{ $task->is_overdue ? 'bg-danger bg-opacity-10' : '' }}">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div class="flex-grow-1">
                                                    <h6 class="mb-1">
                                                        <a href="{{ route('tasks.show', $task) }}" class="text-decoration-none">
                                                            {{ $task->task_name }}
                                                        </a>
                                                    </h6>
                                                    <p class="mb-1 text-muted small">
                                                        <i class="fas fa-project-diagram me-1"></i>
                                                        {{ $task->project->name }}
                                                    </p>
                                                    <div class="d-flex align-items-center">
                                                        <small class="text-muted me-2">
                                                            <i class="fas fa-user me-1"></i>
                                                            {{ $task->assignee->full_name }}
                                                        </small>
                                                        <small class="text-muted">
                                                            <i class="fas fa-calendar me-1"></i>
                                                            {{ $task->formatted_due_date }}
                                                        </small>
                                                    </div>
                                                </div>
                                                <div class="text-end">
                                                    <span class="badge bg-{{ $task->priority_badge_color }}">
                                                        {{ $task->priority }}
                                                    </span>
                                                    @if($task->is_overdue)
                                                        <br><span class="badge bg-danger mt-1">Overdue</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-center py-4">
                                    <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                                    <p class="text-muted">All caught up!</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Recent Activity -->
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">Recent Activity</h5>
                            <a href="{{ route('activity.index') }}" class="btn btn-sm btn-outline-secondary">View All</a>
                        </div>
                        <div class="card-body">
                            @if($recentActivity->count() > 0)
                                <div class="timeline">
                                    @foreach($recentActivity as $activity)
                                        <div class="timeline-item mb-3">
                                            <div class="d-flex">
                                                <div class="timeline-marker me-3">
                                                    <i class="fas fa-{{ $activity->icon }} text-{{ $activity->color }}"></i>
                                                </div>
                                                <div class="flex-grow-1">
                                                    <div class="timeline-content">
                                                        <p class="mb-1">{{ $activity->description }}</p>
                                                        <small class="text-muted">
                                                            {{ $activity->user->full_name }} • 
                                                            {{ $activity->created_at->diffForHumans() }}
                                                        </small>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-center py-4">
                                    <i class="fas fa-history fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">No recent activity</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="row mt-4">
                <div class="col-md-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white border-bottom">
                            <h5 class="card-title mb-0">Quick Actions</h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-2">
                                    <a href="{{ route('projects.create') }}" class="btn btn-outline-primary w-100 h-100 d-flex flex-column justify-content-center align-items-center">
                                        <i class="fas fa-plus-circle fa-2x mb-2"></i>
                                        <span>Create Project</span>
                                    </a>
                                </div>
                                <div class="col-md-2">
                                    <a href="{{ route('reports.generate') }}" class="btn btn-outline-info w-100 h-100 d-flex flex-column justify-content-center align-items-center">
                                        <i class="fas fa-chart-bar fa-2x mb-2"></i>
                                        <span>Generate Report</span>
                                    </a>
                                </div>
                                <div class="col-md-2">
                                    <a href="{{ route('tasks.calendar') }}" class="btn btn-outline-secondary w-100 h-100 d-flex flex-column justify-content-center align-items-center">
                                        <i class="fas fa-calendar-alt fa-2x mb-2"></i>
                                        <span>Calendar</span>
                                    </a>
                                </div>
                                <div class="col-md-2">
                                 <a href="{{ route('reports.view-staff') }}" class="btn btn-outline-info w-100 h-100 d-flex flex-column justify-content-center align-items-center">
                                    <i class="fas fa-users fa-2x mb-2"></i>
                                    <span>View Available Staffs</span>
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
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<style>
    .avatar-circle {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: bold;
        font-size: 14px;
    }
    
    .timeline-marker {
        width: 30px;
        height: 30px;
        border-radius: 50%;
        background-color: #f8f9fa;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 2px solid #dee2e6;
    }
    
    .card {
        transition: transform 0.2s ease-in-out;
    }
    
    .card:hover {
        transform: translateY(-2px);
    }
    
    .progress {
        border-radius: 10px;
        background-color: #f8f9fa;
    }
    
    .progress-bar {
        border-radius: 10px;
    }
    
    .opacity-75 {
        opacity: 0.75;
    }
</style>
@endpush