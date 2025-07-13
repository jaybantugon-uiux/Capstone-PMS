{{-- Create task-reports/index.blade.php --}}
@extends('app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h2">
                    <i class="fas fa-file-alt me-2"></i>
                    @if(auth()->user()->role === 'sc')
                        My Task Reports
                    @else
                        All Task Reports
                    @endif
                </h1>
                @if(auth()->user()->role === 'sc')
                    <a href="{{ route('sc.task-reports.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i> Create New Report
                    </a>
                @endif
            </div>

            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card bg-primary text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4 class="card-title">{{ $stats['total'] }}</h4>
                                    <p class="card-text">Total Reports</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-file-alt fa-2x"></i>
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
                                    <h4 class="card-title">{{ $stats['pending'] }}</h4>
                                    <p class="card-text">Pending Review</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-clock fa-2x"></i>
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
                                    <h4 class="card-title">{{ $stats['approved'] }}</h4>
                                    <p class="card-text">Approved</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-check-circle fa-2x"></i>
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
                                    <h4 class="card-title">{{ number_format($stats['total'] > 0 ? ($stats['approved'] / $stats['total']) * 100 : 0, 1) }}%</h4>
                                    <p class="card-text">Approval Rate</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-chart-line fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" action="{{ request()->url() }}" class="row g-3">
                        <div class="col-md-3">
                            <label for="status" class="form-label">Review Status</label>
                            <select name="status" id="status" class="form-select">
                                <option value="">All Statuses</option>
                                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="reviewed" {{ request('status') === 'reviewed' ? 'selected' : '' }}>Reviewed</option>
                                <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                                <option value="needs_revision" {{ request('status') === 'needs_revision' ? 'selected' : '' }}>Needs Revision</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="task_status" class="form-label">Task Status</label>
                            <select name="task_status" id="task_status" class="form-select">
                                <option value="">All Task Statuses</option>
                                <option value="pending" {{ request('task_status') === 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="in_progress" {{ request('task_status') === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                <option value="completed" {{ request('task_status') === 'completed' ? 'selected' : '' }}>Completed</option>
                                <option value="on_hold" {{ request('task_status') === 'on_hold' ? 'selected' : '' }}>On Hold</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="date_from" class="form-label">From Date</label>
                            <input type="date" name="date_from" id="date_from" class="form-control" value="{{ request('date_from') }}">
                        </div>
                        <div class="col-md-2">
                            <label for="date_to" class="form-label">To Date</label>
                            <input type="date" name="date_to" id="date_to" class="form-control" value="{{ request('date_to') }}">
                        </div>
                        <div class="col-md-2">
                            <label for="search" class="form-label">Search</label>
                            <input type="text" name="search" id="search" class="form-control" placeholder="Search reports..." value="{{ request('search') }}">
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="fas fa-search me-1"></i> Filter
                            </button>
                            <a href="{{ request()->url() }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-1"></i> Clear
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Reports Table -->
            <div class="card">
                <div class="card-body">
                    @if($reports->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Report Title</th>
                                        <th>Task</th>
                                        <th>Project</th>
                                        @if(auth()->user()->role !== 'sc')
                                            <th>Submitted By</th>
                                        @endif
                                        <th>Date</th>
                                        <th>Progress</th>
                                        <th>Status</th>
                                        <th>Review Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($reports as $report)
                                        <tr class="{{ $report->is_overdue_for_review ? 'table-warning' : '' }}">
                                            <td>
                                                <strong>{{ $report->report_title }}</strong>
                                                @if($report->is_overdue_for_review)
                                                    <span class="badge bg-warning ms-1">Overdue Review</span>
                                                @endif
                                            </td>
                                            <td>{{ $report->task->task_name }}</td>
                                            <td>{{ $report->task->project->name }}</td>
                                            @if(auth()->user()->role !== 'sc')
                                                <td>{{ $report->user->full_name }}</td>
                                            @endif
                                            <td>{{ $report->formatted_report_date }}</td>
                                            <td>
                                                <div class="progress" style="height: 20px;">
                                                    <div class="progress-bar bg-{{ $report->progress_color }}" 
                                                         role="progressbar" 
                                                         style="width: {{ $report->progress_percentage }}%"
                                                         aria-valuenow="{{ $report->progress_percentage }}" 
                                                         aria-valuemin="0" 
                                                         aria-valuemax="100">
                                                        {{ $report->progress_percentage }}%
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-{{ $report->task_status_badge_color }}">
                                                    {{ $report->formatted_task_status }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-{{ $report->review_status_badge_color }}">
                                                    {{ $report->formatted_review_status }}
                                                </span>
                                                @if($report->admin_rating)
                                                    <div class="mt-1">
                                                        {!! $report->rating_stars !!}
                                                    </div>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <a href="{{ auth()->user()->role === 'sc' ? route('sc.task-reports.show', $report) : route('admin.task-reports.show', $report) }}" 
                                                       class="btn btn-outline-primary">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    @if($report->canBeEditedBy(auth()->user()))
                                                        <a href="{{ route('sc.task-reports.edit', $report) }}" 
                                                           class="btn btn-outline-success">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="d-flex justify-content-center">
                            {{ $reports->appends(request()->query())->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-file-alt fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No task reports found</h5>
                            <p class="text-muted">
                                @if(auth()->user()->role === 'sc')
                                    You haven't submitted any task reports yet.
                                @else
                                    No task reports have been submitted yet.
                                @endif
                            </p>
                            @if(auth()->user()->role === 'sc')
                                <a href="{{ route('sc.task-reports.create') }}" class="btn btn-primary">
                                    <i class="fas fa-plus me-1"></i> Create Your First Report
                                </a>
                            @endif
                        </div>
                    @endif
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
</style>
@endpush