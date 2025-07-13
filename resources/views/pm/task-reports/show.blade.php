@extends('app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1>Task Report Details</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('pm.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('pm.task-reports.index') }}">Task Reports</a></li>
                    <li class="breadcrumb-item active">{{ $taskReport->report_title }}</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2">
            @if($taskReport->review_status === 'pending')
                <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#reviewModal">
                    <i class="fas fa-check me-1"></i>Review Report
                </button>
            @endif
            <a href="{{ route('tasks.show', $taskReport->task) }}" class="btn btn-outline-info">
                <i class="fas fa-tasks me-1"></i>View Task
            </a>
            <a href="{{ route('pm.task-reports.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i>Back to Reports
            </a>
        </div>
    </div>

    <!-- Status Alert -->
    @if($taskReport->is_overdue_for_review)
        <div class="alert alert-warning mb-4">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <strong>Overdue Review:</strong> This report has been pending review for over 2 days.
        </div>
    @endif

    <div class="row">
        <!-- Main Report Content -->
        <div class="col-lg-8">
            <!-- Report Header -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <h4 class="mb-2">{{ $taskReport->report_title }}</h4>
                            <div class="d-flex gap-3 flex-wrap">
                                <span class="badge bg-{{ $taskReport->review_status_badge_color }} fs-6">
                                    {{ $taskReport->formatted_review_status }}
                                </span>
                                <span class="badge bg-{{ $taskReport->task_status_badge_color }} fs-6">
                                    Task: {{ $taskReport->formatted_task_status }}
                                </span>
                                @if($taskReport->weather_conditions)
                                    <span class="badge bg-light text-dark fs-6">
                                        {!! $taskReport->weather_icon !!} {{ ucfirst($taskReport->weather_conditions) }}
                                    </span>
                                @endif
                            </div>
                        </div>
                        @if($taskReport->admin_rating)
                            <div class="text-end">
                                <div class="mb-1">
                                    {!! $taskReport->rating_stars !!}
                                </div>
                                <small class="text-muted">PM Rating</small>
                            </div>
                        @endif
                    </div>

                    <!-- Progress Bar -->
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="form-label mb-0">Task Progress</label>
                            <span class="badge bg-{{ $taskReport->progress_color }}">{{ $taskReport->progress_percentage }}%</span>
                        </div>
                        <div class="progress" style="height: 12px;">
                            <div class="progress-bar bg-{{ $taskReport->progress_color }}" 
                                 style="width: {{ $taskReport->progress_percentage }}%"></div>
                        </div>
                    </div>

                    <!-- Key Metrics -->
                    <div class="row text-center">
                        <div class="col-md-3">
                            <div class="border-end">
                                <h5 class="text-primary mb-0">{{ $taskReport->formatted_report_date }}</h5>
                                <small class="text-muted">Report Date</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="border-end">
                                <h5 class="text-success mb-0">{{ $taskReport->hours_worked ?? 'N/A' }}</h5>
                                <small class="text-muted">Hours Worked</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="border-end">
                                <h5 class="text-info mb-0">{{ $taskReport->photos ? count($taskReport->photos) : 0 }}</h5>
                                <small class="text-muted">Photos</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <h5 class="text-warning mb-0">{{ $taskReport->issues_encountered ? 'Yes' : 'No' }}</h5>
                            <small class="text-muted">Issues Found</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Work Description -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Work Description</h5>
                </div>
                <div class="card-body">
                    <p class="mb-0">{{ $taskReport->work_description }}</p>
                </div>
            </div>

            <!-- Issues Encountered -->
            @if($taskReport->issues_encountered)
                <div class="card mb-4 border-warning">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="mb-0">
                            <i class="fas fa-exclamation-triangle me-2"></i>Issues Encountered
                        </h5>
                    </div>
                    <div class="card-body">
                        <p class="mb-0">{{ $taskReport->issues_encountered }}</p>
                    </div>
                </div>
            @endif

            <!-- Next Steps -->
            @if($taskReport->next_steps)
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Next Steps</h5>
                    </div>
                    <div class="card-body">
                        <p class="mb-0">{{ $taskReport->next_steps }}</p>
                    </div>
                </div>
            @endif

            <!-- Materials and Equipment -->
            @if($taskReport->materials_used || $taskReport->equipment_used)
                <div class="row mb-4">
                    @if($taskReport->materials_used)
                        <div class="col-md-6">
                            <div class="card h-100">
                                <div class="card-header">
                                    <h6 class="mb-0">Materials Used</h6>
                                </div>
                                <div class="card-body">
                                    <p class="mb-0">{{ $taskReport->materials_used }}</p>
                                </div>
                            </div>
                        </div>
                    @endif
                    @if($taskReport->equipment_used)
                        <div class="col-md-6">
                            <div class="card h-100">
                                <div class="card-header">
                                    <h6 class="mb-0">Equipment Used</h6>
                                </div>
                                <div class="card-body">
                                    <p class="mb-0">{{ $taskReport->equipment_used }}</p>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            @endif

            <!-- Photos -->
            @if($taskReport->photos && count($taskReport->photos) > 0)
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-camera me-2"></i>Photos ({{ count($taskReport->photos) }})
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            @foreach($taskReport->photos as $photo)
                                <div class="col-md-4">
                                    <div class="position-relative">
                                        <img src="{{ Storage::url($photo) }}" 
                                             class="img-fluid rounded shadow-sm" 
                                             alt="Task Report Photo"
                                             style="height: 200px; object-fit: cover; width: 100%; cursor: pointer;"
                                             data-bs-toggle="modal" 
                                             data-bs-target="#photoModal"
                                             data-photo-src="{{ Storage::url($photo) }}">
                                        <div class="position-absolute top-0 end-0 m-2">
                                            <a href="{{ Storage::url($photo) }}" 
                                               download 
                                               class="btn btn-sm btn-light rounded-circle"
                                               title="Download">
                                                <i class="fas fa-download"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            <!-- Additional Notes -->
            @if($taskReport->additional_notes)
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Additional Notes</h5>
                    </div>
                    <div class="card-body">
                        <p class="mb-0">{{ $taskReport->additional_notes }}</p>
                    </div>
                </div>
            @endif

            <!-- Admin Comments -->
            @if($taskReport->admin_comments)
                <div class="card mb-4 border-info">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-comment me-2"></i>PM Comments
                        </h5>
                    </div>
                    <div class="card-body">
                        <p class="mb-2">{{ $taskReport->admin_comments }}</p>
                        @if($taskReport->reviewer)
                            <small class="text-muted">
                                By {{ $taskReport->reviewer->first_name }} {{ $taskReport->reviewer->last_name }} 
                                on {{ $taskReport->formatted_reviewed_at }}
                            </small>
                        @endif
                    </div>
                </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Report Information -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Report Information</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <strong>Submitted by:</strong>
                        <div class="d-flex align-items-center mt-1">
                            <div class="avatar-sm bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2">
                                {{ substr($taskReport->user->first_name, 0, 1) }}{{ substr($taskReport->user->last_name, 0, 1) }}
                            </div>
                            <div>
                                <div>{{ $taskReport->user->first_name }} {{ $taskReport->user->last_name }}</div>
                                <small class="text-muted">Site Coordinator</small>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <strong>Submitted:</strong>
                        <div>{{ $taskReport->created_at->format('M d, Y g:i A') }}</div>
                        <small class="text-muted">{{ $taskReport->created_at->diffForHumans() }}</small>
                    </div>

                    @if($taskReport->reviewed_at)
                        <div class="mb-3">
                            <strong>Reviewed:</strong>
                            <div>{{ $taskReport->formatted_reviewed_at }}</div>
                            @if($taskReport->reviewer)
                                <small class="text-muted">by {{ $taskReport->reviewer->first_name }} {{ $taskReport->reviewer->last_name }}</small>
                            @endif
                        </div>
                    @endif

                    <div class="mb-3">
                        <strong>Last Updated:</strong>
                        <div>{{ $taskReport->updated_at->format('M d, Y g:i A') }}</div>
                        <small class="text-muted">{{ $taskReport->updated_at->diffForHumans() }}</small>
                    </div>
                </div>
            </div>

            <!-- Task Information -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Task Information</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <strong>Task:</strong>
                        <div>
                            <a href="{{ route('tasks.show', $taskReport->task) }}">
                                {{ $taskReport->task->task_name }}
                            </a>
                        </div>
                    </div>

                    <div class="mb-3">
                        <strong>Project:</strong>
                        <div>
                            <a href="{{ route('projects.show', $taskReport->task->project) }}">
                                {{ $taskReport->task->project->name }}
                            </a>
                        </div>
                    </div>

                    @if($taskReport->task->due_date)
                        <div class="mb-3">
                            <strong>Due Date:</strong>
                            <div class="{{ $taskReport->task->due_date->isPast() && $taskReport->task->status !== 'completed' ? 'text-danger' : '' }}">
                                {{ $taskReport->task->due_date->format('M d, Y') }}
                            </div>
                            @if($taskReport->task->due_date->isPast() && $taskReport->task->status !== 'completed')
                                <small class="text-danger">Overdue</small>
                            @endif
                        </div>
                    @endif

                    <div class="mb-0">
                        <strong>Current Status:</strong>
                        <div>
                            <span class="badge bg-{{ $taskReport->task_status_badge_color }}">
                                {{ $taskReport->formatted_task_status }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        @if($taskReport->review_status === 'pending')
                            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#reviewModal">
                                <i class="fas fa-check me-1"></i>Review Report
                            </button>
                        @endif
                        
                        <a href="{{ route('tasks.show', $taskReport->task) }}" class="btn btn-outline-info">
                            <i class="fas fa-tasks me-1"></i>View Task Details
                        </a>
                        
                        <a href="{{ route('projects.show', $taskReport->task->project) }}" class="btn btn-outline-primary">
                            <i class="fas fa-project-diagram me-1"></i>View Project
                        </a>
                        
                        <a href="{{ route('pm.task-reports.index', ['user_id' => $taskReport->user_id]) }}" class="btn btn-outline-secondary">
                            <i class="fas fa-user me-1"></i>Other Reports by {{ $taskReport->user->first_name }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Review Modal -->
<div class="modal fade" id="reviewModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Review Task Report</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('pm.task-reports.update-review', $taskReport) }}" method="POST">
                @csrf
                @method('PATCH')
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Review Status <span class="text-danger">*</span></label>
                        <select name="review_status" class="form-select" required>
                            <option value="">Select Status</option>
                            <option value="approved">Approve</option>
                            <option value="reviewed">Mark as Reviewed</option>
                            <option value="needs_revision">Needs Revision</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Comments</label>
                        <textarea name="admin_comments" class="form-control" rows="4" 
                                  placeholder="Add your feedback or comments about this report..."></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Rating (Optional)</label>
                        <select name="admin_rating" class="form-select">
                            <option value="">No Rating</option>
                            <option value="5">★★★★★ Excellent (5/5)</option>
                            <option value="4">★★★★☆ Good (4/5)</option>
                            <option value="3">★★★☆☆ Average (3/5)</option>
                            <option value="2">★★☆☆☆ Poor (2/5)</option>
                            <option value="1">★☆☆☆☆ Very Poor (1/5)</option>
                        </select>
                    </div>

                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        The site coordinator will be notified of your review and any comments you provide.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i>Submit Review
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Photo Modal -->
<div class="modal fade" id="photoModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Photo View</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <img id="modalPhoto" src="" class="img-fluid" alt="Photo">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <a id="downloadPhotoBtn" href="" download class="btn btn-primary">
                    <i class="fas fa-download me-1"></i>Download
                </a>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
.avatar-sm {
    width: 40px;
    height: 40px;
    font-size: 14px;
    font-weight: bold;
}

.card {
    border: none;
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}

.badge {
    font-size: 0.875em;
}

.progress {
    height: 12px;
}

.border-end {
    border-right: 1px solid #dee2e6 !important;
}

.alert {
    border-radius: 0.5rem;
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Photo modal functionality
    const photoModal = document.getElementById('photoModal');
    if (photoModal) {
        photoModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const photoSrc = button.getAttribute('data-photo-src');
            
            const modalPhoto = document.getElementById('modalPhoto');
            const downloadBtn = document.getElementById('downloadPhotoBtn');
            
            modalPhoto.src = photoSrc;
            downloadBtn.href = photoSrc;
        });
    }

    // Form validation for review modal
    const reviewForm = document.querySelector('#reviewModal form');
    if (reviewForm) {
        reviewForm.addEventListener('submit', function(e) {
            const status = this.querySelector('select[name="review_status"]').value;
            if (!status) {
                e.preventDefault();
                alert('Please select a review status.');
                return false;
            }

            if (status === 'needs_revision') {
                const comments = this.querySelector('textarea[name="admin_comments"]').value.trim();
                if (!comments) {
                    e.preventDefault();
                    alert('Please provide comments when marking a report as needing revision.');
                    return false;
                }
            }
        });
    }
});
</script>
@endpush
@endsection