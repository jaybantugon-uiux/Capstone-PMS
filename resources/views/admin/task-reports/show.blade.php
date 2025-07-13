{{-- Create admin/task-reports/show.blade.php --}}
@extends('app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h2">
                        <i class="fas fa-clipboard-check me-2"></i>Task Report Details
                    </h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.task-reports.index') }}">Task Reports</a></li>
                            <li class="breadcrumb-item active">{{ $taskReport->report_title }}</li>
                        </ol>
                    </nav>
                </div>
                <div class="d-flex gap-2">
                    @if($taskReport->review_status === 'pending')
                        <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#reviewModal">
                            <i class="fas fa-clipboard-check me-1"></i> Review Report
                        </button>
                    @endif
                    <a href="{{ route('admin.task-reports.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Back to Reports
                    </a>
                </div>
            </div>

            <!-- Alert Messages -->
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <!-- Report Overview Cards -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card bg-primary text-white">
                        <div class="card-body text-center">
                            <i class="fas fa-percentage fa-2x mb-2"></i>
                            <h3 class="card-title">{{ $taskReport->progress_percentage }}%</h3>
                            <p class="card-text">Progress Completed</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-{{ $taskReport->review_status_badge_color ?? 'secondary' }} text-white">
                        <div class="card-body text-center">
                            <i class="fas fa-clipboard-check fa-2x mb-2"></i>
                            <h6 class="card-title">{{ $taskReport->formatted_review_status ?? ucfirst($taskReport->review_status) }}</h6>
                            <p class="card-text">Review Status</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-info text-white">
                        <div class="card-body text-center">
                            <i class="fas fa-clock fa-2x mb-2"></i>
                            <h6 class="card-title">{{ $taskReport->hours_worked ?? 'N/A' }}</h6>
                            <p class="card-text">Hours Worked</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-{{ $taskReport->task_status_badge_color ?? 'secondary' }} text-white">
                        <div class="card-body text-center">
                            <i class="fas fa-tasks fa-2x mb-2"></i>
                            <h6 class="card-title">{{ $taskReport->formatted_task_status ?? ucfirst($taskReport->task_status) }}</h6>
                            <p class="card-text">Task Status</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Main Report Details -->
                <div class="col-md-8">
                    <!-- Basic Information -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-info-circle me-2"></i>Report Information
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <strong>Report Title:</strong>
                                    <p class="mb-2">{{ $taskReport->report_title }}</p>
                                </div>
                                <div class="col-md-6">
                                    <strong>Report Date:</strong>
                                    <p class="mb-2">{{ $taskReport->formatted_report_date ?? $taskReport->report_date->format('M d, Y') }}</p>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <strong>Submitted By:</strong>
                                    <p class="mb-2">
                                        {{ $taskReport->user->full_name ?? $taskReport->user->first_name . ' ' . $taskReport->user->last_name }}
                                        <br><small class="text-muted">{{ $taskReport->user->username ?? $taskReport->user->email }}</small>
                                    </p>
                                </div>
                                <div class="col-md-6">
                                    <strong>Submission Time:</strong>
                                    <p class="mb-2">{{ $taskReport->created_at->format('M d, Y h:i A') }}</p>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <strong>Progress:</strong>
                                    <div class="progress mt-1" style="height: 25px;">
                                        <div class="progress-bar bg-{{ $taskReport->progress_color ?? ($taskReport->progress_percentage < 30 ? 'danger' : ($taskReport->progress_percentage < 70 ? 'warning' : 'success')) }}" 
                                             role="progressbar" 
                                             style="width: {{ $taskReport->progress_percentage }}%"
                                             aria-valuenow="{{ $taskReport->progress_percentage }}" 
                                             aria-valuemin="0" 
                                             aria-valuemax="100">
                                            {{ $taskReport->progress_percentage }}%
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    @if($taskReport->weather_conditions)
                                        <strong>Weather Conditions:</strong>
                                        <p class="mb-2">
                                            <i class="{{ $taskReport->weather_icon ?? 'fas fa-sun' }} me-1"></i>
                                            {{ ucfirst($taskReport->weather_conditions) }}
                                        </p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Work Description -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-clipboard-list me-2"></i>Work Description
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <strong>Work Summary:</strong>
                                <div class="mt-2 p-3 bg-light rounded">
                                    {{ $taskReport->work_description ?: 'No work description provided.' }}
                                </div>
                            </div>
                            @if($taskReport->accomplishments)
                                <div class="mb-3">
                                    <strong>Key Accomplishments:</strong>
                                    <div class="mt-2 p-3 bg-light rounded">
                                        {{ $taskReport->accomplishments }}
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Issues and Challenges -->
                    @if($taskReport->issues_encountered || $taskReport->challenges_faced)
                        <div class="card mb-4">
                            <div class="card-header bg-warning text-dark">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-exclamation-triangle me-2"></i>Issues & Challenges
                                </h5>
                            </div>
                            <div class="card-body">
                                @if($taskReport->issues_encountered)
                                    <div class="mb-3">
                                        <strong>Issues Encountered:</strong>
                                        <div class="mt-2 p-3 bg-light rounded border-start border-warning border-3">
                                            {{ $taskReport->issues_encountered }}
                                        </div>
                                    </div>
                                @endif
                                @if($taskReport->challenges_faced)
                                    <div class="mb-3">
                                        <strong>Challenges Faced:</strong>
                                        <div class="mt-2 p-3 bg-light rounded border-start border-warning border-3">
                                            {{ $taskReport->challenges_faced }}
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif

                    <!-- Next Steps and Recommendations -->
                    @if($taskReport->next_steps || $taskReport->recommendations)
                        <div class="card mb-4">
                            <div class="card-header bg-info text-white">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-lightbulb me-2"></i>Next Steps & Recommendations
                                </h5>
                            </div>
                            <div class="card-body">
                                @if($taskReport->next_steps)
                                    <div class="mb-3">
                                        <strong>Next Steps:</strong>
                                        <div class="mt-2 p-3 bg-light rounded">
                                            {{ $taskReport->next_steps }}
                                        </div>
                                    </div>
                                @endif
                                @if($taskReport->recommendations)
                                    <div class="mb-3">
                                        <strong>Recommendations:</strong>
                                        <div class="mt-2 p-3 bg-light rounded">
                                            {{ $taskReport->recommendations }}
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif

                    <!-- Resources Used -->
                    @if($taskReport->materials_used || $taskReport->equipment_used)
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-tools me-2"></i>Resources & Equipment
                                </h5>
                            </div>
                            <div class="card-body">
                                @if($taskReport->materials_used)
                                    <div class="mb-3">
                                        <strong>Materials Used:</strong>
                                        <div class="mt-2 p-3 bg-light rounded">
                                            {{ $taskReport->materials_used }}
                                        </div>
                                    </div>
                                @endif
                                @if($taskReport->equipment_used)
                                    <div class="mb-3">
                                        <strong>Equipment Used:</strong>
                                        <div class="mt-2 p-3 bg-light rounded">
                                            {{ $taskReport->equipment_used }}
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
                                <div class="p-3 bg-light rounded">
                                    {{ $taskReport->additional_notes }}
                                </div>
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
                                <div class="row">
                                    @foreach($taskReport->photos as $photo)
                                        <div class="col-md-4 mb-3">
                                            <div class="position-relative">
                                                <img src="{{ Storage::url($photo) }}" 
                                                     class="img-fluid rounded shadow-sm" 
                                                     alt="Task Report Photo"
                                                     style="cursor: pointer;"
                                                     onclick="openImageModal('{{ Storage::url($photo) }}')">
                                                <div class="position-absolute top-0 end-0 p-2">
                                                    <a href="{{ Storage::url($photo) }}" 
                                                       download 
                                                       class="btn btn-sm btn-dark btn-sm rounded-circle"
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
                </div>

                <!-- Sidebar -->
                <div class="col-md-4">
                    <!-- Task Information -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-tasks me-2"></i>Related Task
                            </h5>
                        </div>
                        <div class="card-body">
                            <h6>
                                <a href="{{ route('tasks.show', $taskReport->task) }}" class="text-decoration-none">
                                    {{ $taskReport->task->task_name }}
                                </a>
                            </h6>
                            <p class="text-muted mb-2">{{ Str::limit($taskReport->task->description, 100) }}</p>
                            <div class="mb-2">
                                <strong>Project:</strong>
                                <a href="{{ route('projects.show', $taskReport->task->project) }}" class="text-decoration-none">
                                    {{ $taskReport->task->project->name }}
                                </a>
                            </div>
                            <div class="mb-2">
                                <strong>Due Date:</strong>
                                <span class="{{ $taskReport->task->is_overdue ?? false ? 'text-danger' : '' }}">
                                    {{ $taskReport->task->formatted_due_date ?? $taskReport->task->due_date->format('M d, Y') }}
                                </span>
                            </div>
                            <div class="mb-2">
                                <strong>Priority:</strong>
                                <span class="badge bg-{{ $taskReport->task->priority_badge_color ?? 'secondary' }}">
                                    {{ ucfirst($taskReport->task->priority) }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Review Status -->
                    <div class="card mb-4" id="review-section">
                        <div class="card-header bg-{{ $taskReport->review_status_badge_color ?? 'secondary' }} text-white">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-star me-2"></i>Review Status
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <strong>Status:</strong>
                                <span class="badge bg-{{ $taskReport->review_status_badge_color ?? 'secondary' }} ms-2">
                                    {{ $taskReport->formatted_review_status ?? ucfirst($taskReport->review_status) }}
                                </span>
                                @if($taskReport->is_overdue_for_review ?? false)
                                    <br><span class="badge bg-warning mt-1">Overdue for Review</span>
                                @endif
                            </div>

                            @if($taskReport->admin_rating)
                                <div class="mb-3">
                                    <strong>Rating:</strong>
                                    <div class="mt-1">
                                        @if(isset($taskReport->rating_stars))
                                            {!! $taskReport->rating_stars !!}
                                        @else
                                            @for($i = 1; $i <= 5; $i++)
                                                @if($i <= $taskReport->admin_rating)
                                                    <i class="fas fa-star text-warning"></i>
                                                @else
                                                    <i class="far fa-star text-muted"></i>
                                                @endif
                                            @endfor
                                            <span class="ms-2">({{ $taskReport->admin_rating }}/5)</span>
                                        @endif
                                    </div>
                                </div>
                            @endif

                            @if($taskReport->reviewed_at)
                                <div class="mb-3">
                                    <strong>Reviewed By:</strong>
                                    <p class="mb-1">{{ $taskReport->reviewer->full_name ?? $taskReport->reviewer->first_name . ' ' . $taskReport->reviewer->last_name }}</p>
                                    <small class="text-muted">{{ $taskReport->reviewed_at->format('M d, Y h:i A') }}</small>
                                </div>
                            @endif

                            @if($taskReport->admin_comments)
                                <div class="mb-3">
                                    <strong>Admin Comments:</strong>
                                    <div class="mt-2 p-3 bg-light rounded">
                                        {{ $taskReport->admin_comments }}
                                    </div>
                                </div>
                            @endif

                            @if($taskReport->review_status === 'pending')
                                <button type="button" class="btn btn-warning w-100" data-bs-toggle="modal" data-bs-target="#reviewModal">
                                    <i class="fas fa-clipboard-check me-1"></i> Review This Report
                                </button>
                            @endif
                        </div>
                    </div>

                    <!-- Report Timeline -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-history me-2"></i>Timeline
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="timeline">
                                <div class="timeline-item">
                                    <div class="timeline-marker bg-primary"></div>
                                    <div class="timeline-content">
                                        <h6 class="timeline-title">Report Submitted</h6>
                                        <p class="timeline-text">{{ $taskReport->created_at->format('M d, Y h:i A') }}</p>
                                    </div>
                                </div>
                                @if($taskReport->reviewed_at)
                                    <div class="timeline-item">
                                        <div class="timeline-marker bg-{{ $taskReport->review_status_badge_color ?? 'secondary' }}"></div>
                                        <div class="timeline-content">
                                            <h6 class="timeline-title">Report Reviewed</h6>
                                            <p class="timeline-text">{{ $taskReport->reviewed_at->format('M d, Y h:i A') }}</p>
                                            <small class="text-muted">by {{ $taskReport->reviewer->full_name ?? $taskReport->reviewer->first_name . ' ' . $taskReport->reviewer->last_name }}</small>
                                        </div>
                                    </div>
                                @endif
                                @if($taskReport->updated_at != $taskReport->created_at)
                                    <div class="timeline-item">
                                        <div class="timeline-marker bg-info"></div>
                                        <div class="timeline-content">
                                            <h6 class="timeline-title">Last Updated</h6>
                                            <p class="timeline-text">{{ $taskReport->updated_at->format('M d, Y h:i A') }}</p>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-bolt me-2"></i>Quick Actions
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <a href="{{ route('tasks.show', $taskReport->task) }}" class="btn btn-outline-primary btn-sm">
                                    <i class="fas fa-eye me-1"></i> View Task
                                </a>
                                <a href="{{ route('projects.show', $taskReport->task->project) }}" class="btn btn-outline-info btn-sm">
                                    <i class="fas fa-project-diagram me-1"></i> View Project
                                </a>
                                <a href="{{ route('admin.task-reports.index', ['user_id' => $taskReport->user->id]) }}" class="btn btn-outline-secondary btn-sm">
                                    <i class="fas fa-user me-1"></i> User's Reports
                                </a>
                                @if($taskReport->review_status === 'needs_revision')
                                    <a href="#" class="btn btn-outline-warning btn-sm">
                                        <i class="fas fa-edit me-1"></i> Request Changes
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Image Modal -->
<div class="modal fade" id="imageModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Report Photo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <img id="modalImage" src="" class="img-fluid" alt="Report Photo">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <a id="downloadLink" href="" download class="btn btn-primary">
                    <i class="fas fa-download me-1"></i> Download
                </a>
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
            <form method="POST" action="{{ route('admin.task-reports.update-review', $taskReport) }}">
                @csrf
                @method('PATCH')
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="review_status" class="form-label">Review Status *</label>
                            <select name="review_status" id="review_status" class="form-select" required>
                                <option value="">Select Status</option>
                                <option value="reviewed">Reviewed</option>
                                <option value="approved">Approved</option>
                                <option value="needs_revision">Needs Revision</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="admin_rating" class="form-label">Rating (Optional)</label>
                            <select name="admin_rating" id="admin_rating" class="form-select">
                                <option value="">No rating</option>
                                <option value="1">⭐ 1 Star - Poor</option>
                                <option value="2">⭐⭐ 2 Stars - Below Average</option>
                                <option value="3">⭐⭐⭐ 3 Stars - Average</option>
                                <option value="4">⭐⭐⭐⭐ 4 Stars - Good</option>
                                <option value="5">⭐⭐⭐⭐⭐ 5 Stars - Excellent</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="admin_comments" class="form-label">Comments & Feedback</label>
                        <textarea name="admin_comments" id="admin_comments" 
                                  class="form-control" rows="5"
                                  placeholder="Provide detailed feedback, suggestions, or comments about this report..."></textarea>
                        <div class="form-text">
                            Be specific about what was done well and what could be improved.
                        </div>
                    </div>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Review Guidelines:</strong>
                        <ul class="mb-0 mt-2">
                            <li><strong>Reviewed:</strong> Report has been seen and acknowledged</li>
                            <li><strong>Approved:</strong> Report meets all requirements and standards</li>
                            <li><strong>Needs Revision:</strong> Report requires changes or additional information</li>
                        </ul>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Submit Review
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline-item {
    position: relative;
    margin-bottom: 20px;
}

.timeline-item:not(:last-child)::before {
    content: '';
    position: absolute;
    left: -21px;
    top: 20px;
    height: calc(100% + 10px);
    width: 2px;
    background-color: #dee2e6;
}

.timeline-marker {
    position: absolute;
    left: -25px;
    top: 5px;
    width: 10px;
    height: 10px;
    border-radius: 50%;
    border: 2px solid #fff;
    box-shadow: 0 0 0 2px #dee2e6;
}

.timeline-title {
    font-size: 0.9rem;
    font-weight: 600;
    margin-bottom: 5px;
}

.timeline-text {
    font-size: 0.85rem;
    margin-bottom: 0;
    color: #6c757d;
}

.progress {
    min-width: 120px;
}

.bg-light {
    background-color: #f8f9fa !important;
}

.border-start {
    border-left: 0.25rem solid !important;
}

.border-3 {
    border-width: 3px !important;
}

.position-relative:hover .position-absolute {
    opacity: 1;
}

.position-absolute {
    opacity: 0;
    transition: opacity 0.3s ease;
}

#modalImage {
    max-height: 70vh;
    object-fit: contain;
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-scroll to review section if there's a hash
    if (window.location.hash === '#review-section') {
        document.getElementById('review-section').scrollIntoView({ behavior: 'smooth' });
    }

    // Form validation for review modal
    const reviewForm = document.querySelector('#reviewModal form');
    if (reviewForm) {
        reviewForm.addEventListener('submit', function(e) {
            const status = document.getElementById('review_status').value;
            if (!status) {
                e.preventDefault();
                alert('Please select a review status.');
                return false;
            }

            // Confirm submission
            if (!confirm('Are you sure you want to submit this review? This action cannot be undone.')) {
                e.preventDefault();
                return false;
            }
        });
    }

    // Show appropriate guidance based on review status selection
    document.getElementById('review_status')?.addEventListener('change', function() {
        const commentsField = document.getElementById('admin_comments');
        const status = this.value;
        
        switch(status) {
            case 'needs_revision':
                commentsField.placeholder = 'Please specify what needs to be revised and provide clear guidance for improvement...';
                commentsField.setAttribute('required', 'required');
                break;
            case 'approved':
                commentsField.placeholder = 'Optional: Provide positive feedback or acknowledge good work...';
                commentsField.removeAttribute('required');
                break;
            case 'reviewed':
                commentsField.placeholder = 'Optional: Add any general comments or observations...';
                commentsField.removeAttribute('required');
                break;
            default:
                commentsField.placeholder = 'Provide detailed feedback, suggestions, or comments about this report...';
                commentsField.removeAttribute('required');
        }
    });
});

// Function to open image modal
function openImageModal(imageSrc) {
    const modal = new bootstrap.Modal(document.getElementById('imageModal'));
    document.getElementById('modalImage').src = imageSrc;
    document.getElementById('downloadLink').href = imageSrc;
    modal.show();
}
</script>
@endpush