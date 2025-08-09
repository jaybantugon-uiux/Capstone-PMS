{{-- Create admin/task-reports/index.blade.php --}}
@extends('app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h2">
                    <i class="fas fa-clipboard-check me-2"></i>Task Reports Management
                </h1>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.task-reports.export') }}" class="btn btn-success">
                        <i class="fas fa-download me-1"></i> Export CSV
                    </a>
                    <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Back to Dashboard
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
                    <div class="card bg-danger text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4 class="card-title">{{ $stats['overdue_reviews'] }}</h4>
                                    <p class="card-text">Overdue Reviews</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-exclamation-triangle fa-2x"></i>
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
                        <div class="col-md-2">
                            <label for="status" class="form-label">Review Status</label>
                            <select name="status" id="status" class="form-select">
                                <option value="">All Statuses</option>
                                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="reviewed" {{ request('status') === 'reviewed' ? 'selected' : '' }}>Reviewed</option>
                                <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                                <option value="needs_revision" {{ request('status') === 'needs_revision' ? 'selected' : '' }}>Needs Revision</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="user_id" class="form-label">Site Coordinator</label>
                            <select name="user_id" id="user_id" class="form-select">
                                <option value="">All Coordinators</option>
                                @foreach($siteCoordinators as $coordinator)
                                    <option value="{{ $coordinator->id }}" {{ request('user_id') == $coordinator->id ? 'selected' : '' }}>
                                        {{ $coordinator->full_name }}
                                    </option>
                                @endforeach
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
                        <div class="col-md-3">
                            <label for="search" class="form-label">Search</label>
                            <input type="text" name="search" id="search" class="form-control" placeholder="Search reports..." value="{{ request('search') }}">
                        </div>
                        <div class="col-md-1">
                            <label class="form-label">&nbsp;</label>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col-12">
                            <a href="{{ request()->url() }}" class="btn btn-outline-secondary btn-sm">
                                <i class="fas fa-times me-1"></i> Clear Filters
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
                                        <th>Submitted By</th>
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
                                                    <br><span class="badge bg-warning">Overdue Review</span>
                                                @endif
                                                @if($report->issues_encountered)
                                                    <br><span class="badge bg-danger">Has Issues</span>
                                                @endif
                                            </td>
                                            <td>
                                                <a href="{{ route('tasks.show', $report->task) }}" class="text-decoration-none">
                                                    {{ $report->task->task_name }}
                                                </a>
                                            </td>
                                            <td>
                                                <a href="{{ route('projects.show', $report->task->project) }}" class="text-decoration-none">
                                                    {{ $report->task->project->name }}
                                                </a>
                                            </td>
                                            <td>
                                                {{ $report->user->full_name }}
                                                <br><small class="text-muted">{{ $report->user->username }}</small>
                                            </td>
                                            <td>
                                                {{ $report->formatted_report_date }}
                                                <br><small class="text-muted">{{ $report->created_at->diffForHumans() }}</small>
                                            </td>
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
                                                @if($report->hours_worked)
                                                    <small class="text-muted">{{ $report->hours_worked }}h worked</small>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge bg-{{ $report->task_status_badge_color }}">
                                                    {{ $report->formatted_task_status }}
                                                </span>
                                                @if($report->weather_conditions)
                                                    <br><small class="text-muted">
                                                        <i class="{{ $report->weather_icon }}"></i>
                                                        {{ ucfirst($report->weather_conditions) }}
                                                    </small>
                                                @endif
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
                                                @if($report->reviewed_at)
                                                    <br><small class="text-muted">
                                                        by {{ $report->reviewer->full_name }}
                                                    </small>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <a href="{{ route('admin.task-reports.show', $report) }}" 
                                                       class="btn btn-outline-primary" title="View/Review">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    @if($report->review_status === 'pending')
                                                        <a href="{{ route('admin.task-reports.show', $report) }}#review-section" 
                                                           class="btn btn-outline-warning" title="Quick Review">
                                                            <i class="fas fa-clipboard-check"></i>
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
                            <p class="text-muted">No task reports match your current filters.</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Quick Review Modal -->
            <div class="modal fade" id="quickReviewModal" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Quick Review</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <form id="quickReviewForm" method="POST">
                            @csrf
                            @method('PATCH')
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label for="quick_review_status" class="form-label">Review Status</label>
                                    <select name="review_status" id="quick_review_status" class="form-select" required>
                                        <option value="reviewed">Reviewed</option>
                                        <option value="approved">Approved</option>
                                        <option value="needs_revision">Needs Revision</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="quick_admin_rating" class="form-label">Rating (Optional)</label>
                                    <select name="admin_rating" id="quick_admin_rating" class="form-select">
                                        <option value="">No rating</option>
                                        <option value="1">⭐ 1 Star</option>
                                        <option value="2">⭐⭐ 2 Stars</option>
                                        <option value="3">⭐⭐⭐ 3 Stars</option>
                                        <option value="4">⭐⭐⭐⭐ 4 Stars</option>
                                        <option value="5">⭐⭐⭐⭐⭐ 5 Stars</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="quick_admin_comments" class="form-label">Comments</label>
                                    <textarea name="admin_comments" id="quick_admin_comments" 
                                              class="form-control" rows="3"
                                              placeholder="Provide feedback or comments..."></textarea>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-primary">Submit Review</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Quick review functionality
    const quickReviewModal = new bootstrap.Modal(document.getElementById('quickReviewModal'));
    const quickReviewForm = document.getElementById('quickReviewForm');
    
    // Handle quick review buttons
    document.querySelectorAll('.btn-outline-warning').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const reportId = this.getAttribute('href').split('/')[3];
            quickReviewForm.action = `/admin/task-reports/${reportId}/review`;
            quickReviewModal.show();
        });
    });
    
    // Handle form submission
    quickReviewForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const action = this.action;
        
        fetch(action, {
            method: 'PATCH',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error: ' + (data.message || 'Failed to submit review'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while submitting the review');
        });
        
        quickReviewModal.hide();
    });
});
</script>
@endpush

@push('styles')
<style>
.table-warning {
    --bs-table-accent-bg: var(--bs-warning-bg-subtle);
}
.progress {
    min-width: 80px;
}
</style>
@endpush