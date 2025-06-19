@extends('app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h2">Admin Dashboard</h1>
                <div class="d-flex gap-2">
                    <a href="{{ route('projects.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> New Project
                    </a>
                    <a href="{{ route('tasks.create') }}" class="btn btn-success">
                        <i class="fas fa-tasks"></i> New Task
                    </a>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card bg-primary text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4 class="card-title">{{ $totalProjects }}</h4>
                                    <p class="card-text">Total Projects</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-project-diagram fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-success text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4 class="card-title">{{ $activeProjects }}</h4>
                                    <p class="card-text">Active Projects</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-play-circle fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-info text-white">
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
                <div class="col-md-3">
                    <div class="card bg-warning text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4 class="card-title">{{ $overdueTasksCount }}</h4>
                                    <p class="card-text">Overdue Tasks</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-exclamation-triangle fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Equipment Management Card -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="card-title text-primary">
                                        <i class="fas fa-boxes me-2"></i>Equipment Inventory
                                    </h5>
                                    <p class="text-muted mb-3">Manage and monitor equipment stock levels</p>
                                    <div class="d-flex gap-2">
                                        <a href="{{ route('equipment.index') }}" class="btn btn-primary btn-sm">
                                            <i class="fas fa-list me-1"></i>View All
                                        </a>
                                        <a href="{{ route('equipment.create') }}" class="btn btn-outline-primary btn-sm">
                                            <i class="fas fa-plus me-1"></i>Add New
                                        </a>
                                        <a href="{{ route('equipment.low-stock') }}" class="btn btn-outline-warning btn-sm">
                                            <i class="fas fa-exclamation-triangle me-1"></i>Low Stock
                                        </a>
                                    </div>
                                </div>
                                <div class="text-primary">
                                    <i class="fas fa-warehouse fa-3x opacity-75"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="card-title text-success">
                                        <i class="fas fa-chart-line me-2"></i>Quick Reports
                                    </h5>
                                    <p class="text-muted mb-3">Generate project and task reports</p>
                                    <div class="d-flex gap-2">
                                        <a href="{{ route('reports.generate') }}" class="btn btn-success btn-sm">
                                            <i class="fas fa-file-alt me-1"></i>Generate
                                        </a>
                                        <a href="{{ route('reports.view-staff') }}" class="btn btn-outline-success btn-sm">
                                            <i class="fas fa-users me-1"></i>Staff Report
                                        </a>
                                    </div>
                                </div>
                                <div class="text-success">
                                    <i class="fas fa-chart-pie fa-3x opacity-75"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Task Status Overview -->
            <div class="row mb-4">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Task Status Overview</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3 text-center">
                                    <div class="bg-light p-3 rounded">
                                        <h4 class="text-secondary">{{ $pendingTasks }}</h4>
                                        <p class="mb-0">Pending</p>
                                    </div>
                                </div>
                                <div class="col-md-3 text-center">
                                    <div class="bg-light p-3 rounded">
                                        <h4 class="text-warning">{{ $inProgressTasks }}</h4>
                                        <p class="mb-0">In Progress</p>
                                    </div>
                                </div>
                                <div class="col-md-3 text-center">
                                    <div class="bg-light p-3 rounded">
                                        <h4 class="text-success">{{ $completedTasks }}</h4>
                                        <p class="mb-0">Completed</p>
                                    </div>
                                </div>
                                <div class="col-md-3 text-center">
                                    <div class="bg-light p-3 rounded">
                                        <h4 class="text-danger">{{ $overdueTasksCount }}</h4>
                                        <p class="mb-0">Overdue</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Recent Projects -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">Recent Projects</h5>
                            <a href="{{ route('projects.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
                        </div>
                        <div class="card-body">
                            @if($recentProjects->count() > 0)
                                <div class="list-group list-group-flush">
                                    @foreach($recentProjects as $project)
                                        <div class="list-group-item px-0">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div class="flex-grow-1">
                                                    <h6 class="mb-1">
                                                        <a href="{{ route('projects.show', $project) }}" class="text-decoration-none">
                                                            {{ $project->name }}
                                                        </a>
                                                    </h6>
                                                    <p class="mb-1 text-muted small">{{ Str::limit($project->description, 80) }}</p>
                                                    <small class="text-muted">
                                                        Created by {{ $project->creator->full_name }} • 
                                                        {{ $project->created_at->diffForHumans() }}
                                                    </small>
                                                </div>
                                                <div class="text-end">
                                                    <span class="badge bg-{{ $project->status == 'Completed' ? 'success' : ($project->status == 'In Progress' ? 'warning' : 'secondary') }}">
                                                        {{ $project->status }}
                                                    </span>
                                                    @if($project->is_overdue)
                                                        <br><span class="badge bg-danger mt-1">Overdue</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-muted text-center py-3">No projects available</p>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Overdue Tasks -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">Overdue Tasks</h5>
                            <a href="{{ route('tasks.index') }}" class="btn btn-sm btn-outline-danger">View All</a>
                        </div>
                        <div class="card-body">
                            @if($overdueTasks->count() > 0)
                                <div class="list-group list-group-flush">
                                    @foreach($overdueTasks as $task)
                                        <div class="list-group-item px-0">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div class="flex-grow-1">
                                                    <h6 class="mb-1">
                                                        <a href="{{ route('tasks.show', $task) }}" class="text-decoration-none">
                                                            {{ $task->task_name }}
                                                        </a>
                                                    </h6>
                                                    <p class="mb-1 text-muted small">{{ $task->project->name }}</p>
                                                    <small class="text-muted">
                                                        Assigned to {{ $task->siteCoordinator->full_name }} • 
                                                        Due: {{ $task->formatted_due_date }}
                                                    </small>
                                                </div>
                                                <div class="text-end">
                                                    <span class="badge bg-{{ $task->status_badge_color }}">
                                                        {{ $task->formatted_status }}
                                                    </span>
                                                    <br><span class="badge bg-danger mt-1">Overdue</span>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-muted text-center py-3">No overdue tasks</p>
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
                            <h5 class="card-title mb-0">Quick Actions</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-2">
                                    <a href="{{ route('projects.index') }}" class="btn btn-outline-primary w-100 mb-2 h-100 d-flex flex-column justify-content-center align-items-center">
                                        <i class="fas fa-project-diagram fa-2x mb-2"></i>
                                        <span>Manage Projects</span>
                                    </a>
                                </div>
                                <div class="col-md-2">
                                    <a href="{{ route('tasks.index') }}" class="btn btn-outline-success w-100 mb-2 h-100 d-flex flex-column justify-content-center align-items-center">
                                        <i class="fas fa-tasks fa-2x mb-2"></i>
                                        <span>Manage Tasks</span>
                                    </a>
                                </div>
                                <div class="col-md-2">
                                    <a href="{{ route('equipment.index') }}" class="btn btn-outline-info w-100 mb-2 h-100 d-flex flex-column justify-content-center align-items-center">
                                        <i class="fas fa-boxes fa-2x mb-2"></i>
                                        <span>Equipment</span>
                                    </a>
                                </div>
                                <div class="col-md-2">
                                    <a href="{{ route('projects.archived') }}" class="btn btn-outline-secondary w-100 mb-2 h-100 d-flex flex-column justify-content-center align-items-center">
                                        <i class="fas fa-archive fa-2x mb-2"></i>
                                        <span>Archived Projects</span>
                                    </a>
                                </div>
                                <div class="col-md-2">
                                    <a href="{{ route('tasks.archived') }}" class="btn btn-outline-secondary w-100 mb-2 h-100 d-flex flex-column justify-content-center align-items-center">
                                        <i class="fas fa-archive fa-2x mb-2"></i>
                                        <span>Archived Tasks</span>
                                    </a>
                                </div>
                                <div class="col-md-2">
                                    <a href="{{ route('reports.view-staff') }}" class="btn btn-outline-info w-100 mb-2 h-100 d-flex flex-column justify-content-center align-items-center">
                                        <i class="fas fa-users fa-2x mb-2"></i>
                                        <span>View Staff</span>
                                    </a>
                                </div>
                                <div class="col-md-2">
                                    <a href="{{ route('tasks.calendar') }}" class="btn btn-outline-secondary w-100 mb-2 h-100 d-flex flex-column justify-content-center align-items-center">
                                        <i class="fas fa-calendar-alt fa-2x mb-2"></i>
                                        <span>Calendar</span>
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
.opacity-75 {
    opacity: 0.75;
}
</style>
@endpush