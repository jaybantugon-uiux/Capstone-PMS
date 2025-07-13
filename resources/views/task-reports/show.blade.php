{{-- Create task-reports/show.blade.php --}}
@extends('app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h2">
                    <i class="fas fa-file-alt me-2"></i>{{ $taskReport->report_title }}
                </h1>
                <div class="d-flex gap-2">
                    @if($taskReport->canBeEditedBy(auth()->user()))
                        <a href="{{ route('sc.task-reports.edit', $taskReport) }}" class="btn btn-outline-primary">
                            <i class="fas fa-edit me-1"></i> Edit Report
                        </a>
                    @endif
                    <a href="{{ auth()->user()->role === 'sc' ? route('sc.task-reports.index') : route('admin.task-reports.index') }}" 
                       class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Back to Reports
                    </a>
                </div>
            </div>

            <div class="row">
                <!-- Main Content -->
                <div class="col-md-8">
                    <!-- Header Information -->
                    <div class="card mb-4">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <h6 class="text-muted mb-1">Task</h6>
                                    <p class="mb-3">
                                        <a href="{{ route('tasks.show', $taskReport->task) }}" class="text-decoration-none">
                                            {{ $taskReport->task->task_name }}
                                        </a>
                                    </p>
                                    
                                    <h6 class="text-muted mb-1">Project</h6>
                                    <p class="mb-3">
                                        <a href="{{ route('projects.show', $taskReport->task->project) }}" class="text-decoration-none">
                                            {{ $taskReport->task->project->name }}
                                        </a>
                                    </p>
                                    
                                    <h6 class="text-muted mb-1">Submitted By</h6>
                                    <p class="mb-0">{{ $taskReport->user->full_name }}</p>
                                </div>
                                <div class="col-md-6">
                                    <h6 class="text-muted mb-1">Report Date</h6>
                                    <p class="mb-3">{{ $taskReport->formatted_report_date }}</p>
                                    
                                    <h6 class="text-muted mb-1">Task Status</h6>
                                    <p class="mb-3">
                                        <span class="badge bg-{{ $taskReport->task_status_badge_color }} fs-6">
                                            {{ $taskReport->formatted_task_status }}
                                        </span>
                                    </p>
                                    
                                    <h6 class="text-muted mb-1">Progress</h6>
                                    <div class="progress mb-2" style="height: 25px;">
                                        <div class="progress-bar bg-{{ $taskReport->progress_color }}" 
                                             role="progressbar" 
                                             style="width: {{ $taskReport->progress_percentage }}%"
                                             aria-valuenow="{{ $taskReport->progress_percentage }}" 
                                             aria-valuemin="0" 
                                             aria-valuemax="100">
                                            {{ $taskReport->progress_percentage }}%
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Work Description -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-hammer me-2"></i>Work Description
                            </h5>
                        </div>
                        <div class="card-body">
                            <p class="mb-0">{!! nl2br(e($taskReport->work_description)) !!}</p>
                        </div>
                    </div>

                    <!-- Work Details -->
                    @if($taskReport->hours_worked || $taskReport->weather_conditions || $taskReport->materials_used || $taskReport->equipment_used)
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-info-circle me-2"></i>Work Details
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    @if($taskReport->hours_worked)
                                        <div class="col-md-6 mb-3">
                                            <h6 class="text-muted mb-1">Hours Worked</h6>
                                            <p class="mb-0">
                                                <i class="fas fa-clock me-1"></i>
                                                {{ $taskReport->hours_worked }} hours
                                            </p>
                                        </div>
                                    @endif
                                    
                                    @if($taskReport->weather_conditions)
                                        <div class="col-md-6 mb-3">
                                            <h6 class="text-muted mb-1">Weather Conditions</h6>
                                            <p class="mb-0">
                                                <i class="{{ $taskReport->weather_icon }} me-1"></i>
                                                {{ ucfirst($taskReport->weather_conditions) }}
                                            </p>
                                        </div>
                                    @endif
                                </div>
                                
                                @if($taskReport->materials_used)
                                    <div class="mb-3">
                                        <h6 class="text-muted mb-1">Materials Used</h6>
                                        <p class="mb-0">{!! nl2br(e($taskReport->materials_used)) !!}</p>
                                    </div>
                                @endif
                                
                                @if($taskReport->equipment_used)
                                    <div class="mb-0">
                                        <h6 class="text-muted mb-1">Equipment Used</h6>
                                        <p class="mb-0">{!! nl2br(e($taskReport->equipment_used)) !!}</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif

                    <!-- Issues and Planning -->
                    @if($taskReport->issues_encountered || $taskReport->next_steps)
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-exclamation-triangle me-2"></i>Issues & Planning
                                </h5>
                            </div>
                            <div class="card-body">
                                @if($taskReport->issues_encountered)
                                    <div class="mb-3">
                                        <h6 class="text-muted mb-1">Issues Encountered</h6>
                                        <div class="alert alert-warning">
                                            <i class="fas fa-exclamation-triangle me-1"></i>
                                            {!! nl2br(e($taskReport->issues_encountered)) !!}
                                        </div>
                                    </div>
                                @endif
                                
                                @if($taskReport->next_steps)
                                    <div class="mb-0">
                                        <h6 class="text-muted mb-1">Next Steps</h6>
                                        <div class="alert alert-info">
                                            <i class="fas fa-arrow-right me-1"></i>
                                            {!! nl2br(e($taskReport->next_steps)) !!}
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif

                    <!-- Additional Notes -->
                    @if($taskReport->additional_notes)
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-sticky-note me-2"></i>Additional Notes
                                </h5>
                            </div>
                            <div class="card-body">
                                <p class="mb-0">{!! nl2br(e($taskReport->additional_notes)) !!}</p>
                            </div>
                        </div>
                    @endif

                    <!-- Photos -->
                    @if($taskReport->photos && count($taskReport->photos) > 0)
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-camera me-2"></i>Photos ({{ count($taskReport->photos) }})
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    @foreach($taskReport->photos as $photo)
                                        <div class="col-md-4">
                                            <div class="card">
                                                <img src="{{ Storage::url($photo) }}" 
                                                     class="card-img-top" 
                                                     style="height: 200px; object-fit: cover; cursor: pointer;"
                                                     data-bs-toggle="modal" 
                                                     data-bs-target="#photoModal{{ $loop->index }}"
                                                     alt="Task photo">
                                            </div>
                                            
                                            <!-- Photo Modal -->
                                            <div class="modal fade" id="photoModal{{ $loop->index }}" tabindex="-1">
                                                <div class="modal-dialog modal-lg">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Photo {{ $loop->iteration }}</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <div class="modal-body text-center">
                                                            <img src="{{ Storage::url($photo) }}" class="img-fluid" alt="Task photo">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Admin Review Section (for admins/PMs) -->
                    @if(auth()->user()->role === 'admin' || auth()->user()->role === 'pm')
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-clipboard-check me-2"></i>Admin Review
                                </h5>
                            </div>
                            <div class="card-body">
                                @if($taskReport->review_status === 'pending')
                                    <form action="{{ route('admin.task-reports.update-review', $taskReport) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="review_status" class="form-label">Review Status</label>
                                                    <select name="review_status" id="review_status" class="form-select" required>
                                                        <option value="reviewed">Reviewed</option>
                                                        <option value="approved">Approved</option>
                                                        <option value="needs_revision">Needs Revision</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="admin_rating" class="form-label">Rating (1-5 stars)</label>
                                                    <select name="admin_rating" id="admin_rating" class="form-select">
                                                        <option value="">No rating</option>
                                                        <option value="1">⭐ 1 Star</option>
                                                        <option value="2">⭐⭐ 2 Stars</option>
                                                        <option value="3">⭐⭐⭐ 3 Stars</option>
                                                        <option value="4">⭐⭐⭐⭐ 4 Stars</option>
                                                        <option value="5">⭐⭐⭐⭐⭐ 5 Stars</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="admin_comments" class="form-label">Comments</label>
                                            <textarea name="admin_comments" id="admin_comments" 
                                                      class="form-control" rows="4"
                                                      placeholder="Provide feedback, suggestions, or comments..."></textarea>
                                        </div>
                                        
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-check me-1"></i> Submit Review
                                        </button>
                                    </form>
                                @else
                                    <div class="alert alert-info">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <h6 class="alert-heading mb-1">Review Completed</h6>
                                                <p class="mb-1">
                                                    <strong>Status:</strong> 
                                                    <span class="badge bg-{{ $taskReport->review_status_badge_color }}">
                                                        {{ $taskReport->formatted_review_status }}
                                                    </span>
                                                </p>
                                                <p class="mb-1">
                                                    <strong>Reviewed by:</strong> {{ $taskReport->reviewer->full_name }}
                                                </p>
                                                <p class="mb-0">
                                                    <strong>Reviewed on:</strong> {{ $taskReport->formatted_reviewed_at }}
                                                </p>
                                                @if($taskReport->admin_rating)
                                                    <p class="mb-0 mt-2">
                                                        <strong>Rating:</strong> {!! $taskReport->rating_stars !!}
                                                    </p>
                                                @endif
                                            </div>
                                        </div>
                                        
                                        @if($taskReport->admin_comments)
                                            <hr>
                                            <h6 class="mb-1">Admin Comments:</h6>
                                            <p class="mb-0">{!! nl2br(e($taskReport->admin_comments)) !!}</p>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Sidebar -->
                <div class="col-md-4">
                    <!-- Status Card -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-info-circle me-2"></i>Report Status
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <h6 class="text-muted mb-1">Review Status</h6>
                                <span class="badge bg-{{ $taskReport->review_status_badge_color }} fs-6">
                                    {{ $taskReport->formatted_review_status }}
                                </span>
                                @if($taskReport->is_overdue_for_review)
                                    <br><small class="text-warning mt-1">
                                        <i class="fas fa-exclamation-triangle me-1"></i>Overdue for review
                                    </small>
                                @endif
                            </div>
                            
                            <div class="mb-3">
                                <h6 class="text-muted mb-1">Submitted</h6>
                                <p class="mb-0">{{ $taskReport->created_at->format('M d, Y g:i A') }}</p>
                                <small class="text-muted">{{ $taskReport->created_at->diffForHumans() }}</small>
                            </div>
                            
                            @if($taskReport->reviewed_at)
                                <div class="mb-3">
                                    <h6 class="text-muted mb-1">Reviewed</h6>
                                    <p class="mb-0">{{ $taskReport->formatted_reviewed_at }}</p>
                                    <small class="text-muted">by {{ $taskReport->reviewer->full_name }}</small>
                                </div>
                            @endif
                            
                            @if($taskReport->admin_rating && auth()->user()->role === 'sc')
                                <div class="mb-0">
                                    <h6 class="text-muted mb-1">Rating</h6>
                                    <div>{!! $taskReport->rating_stars !!}</div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-bolt me-2"></i>Quick Actions
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <a href="{{ route('tasks.show', $taskReport->task) }}" class="btn btn-outline-primary btn-sm">
                                    <i class="fas fa-tasks me-1"></i> View Task
                                </a>
                                <a href="{{ route('projects.show', $taskReport->task->project) }}" class="btn btn-outline-info btn-sm">
                                    <i class="fas fa-project-diagram me-1"></i> View Project
                                </a>
                                @if(auth()->user()->role === 'sc')
                                    <a href="{{ route('sc.task-reports.create', ['task_id' => $taskReport->task_id]) }}" class="btn btn-outline-success btn-sm">
                                        <i class="fas fa-plus me-1"></i> New Report for Task
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Task Information -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-tasks me-2"></i>Task Information
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <h6 class="text-muted mb-1">Task Status</h6>
                                <span class="badge bg-{{ $taskReport->task->status_badge_color }}">
                                    {{ $taskReport->task->formatted_status }}
                                </span>
                            </div>
                            
                            @if($taskReport->task->due_date)
                                <div class="mb-3">
                                    <h6 class="text-muted mb-1">Due Date</h6>
                                    <p class="mb-0">{{ $taskReport->task->formatted_due_date }}</p>
                                    @if($taskReport->task->is_overdue)
                                        <small class="text-danger">
                                            <i class="fas fa-exclamation-triangle me-1"></i>Overdue
                                        </small>
                                    @endif
                                </div>
                            @endif
                            
                            <div class="mb-3">
                                <h6 class="text-muted mb-1">Assigned To</h6>
                                <p class="mb-0">{{ $taskReport->task->siteCoordinator->full_name }}</p>
                            </div>
                            
                            <div class="mb-0">
                                <h6 class="text-muted mb-1">Created By</h6>
                                <p class="mb-0">{{ $taskReport->task->creator->full_name }}</p>
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
.alert {
    border-left: 4px solid;
}
.alert-warning {
    border-left-color: var(--bs-warning);
}
.alert-info {
    border-left-color: var(--bs-info);
}
.card-img-top:hover {
    transform: scale(1.02);
    transition: transform 0.2s ease-in-out;
}
</style>
@endpush