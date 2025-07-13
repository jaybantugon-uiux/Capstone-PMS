@extends('app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1>Task Reports Management</h1>
            <p class="text-muted">Review and manage task reports from your project teams</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('pm.task-reports.export') }}" class="btn btn-outline-secondary">
                <i class="fas fa-download me-1"></i>Export CSV
            </a>
            <a href="{{ route('pm.dashboard') }}" class="btn btn-outline-primary">
                <i class="fas fa-arrow-left me-1"></i>Back to Dashboard
            </a>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-2">
            <div class="card text-center border-0 shadow-sm">
                <div class="card-body">
                    <div class="text-primary mb-2">
                        <i class="fas fa-clipboard-list fa-2x"></i>
                    </div>
                    <h4 class="mb-1">{{ $stats['total'] ?? 0 }}</h4>
                    <p class="mb-0 text-muted small">Total Reports</p>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card text-center border-0 shadow-sm">
                <div class="card-body">
                    <div class="text-warning mb-2">
                        <i class="fas fa-clock fa-2x"></i>
                    </div>
                    <h4 class="mb-1">{{ $stats['pending'] ?? 0 }}</h4>
                    <p class="mb-0 text-muted small">Pending Review</p>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card text-center border-0 shadow-sm">
                <div class="card-body">
                    <div class="text-success mb-2">
                        <i class="fas fa-check-circle fa-2x"></i>
                    </div>
                    <h4 class="mb-1">{{ $stats['approved'] ?? 0 }}</h4>
                    <p class="mb-0 text-muted small">Approved</p>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card text-center border-0 shadow-sm">
                <div class="card-body">
                    <div class="text-info mb-2">
                        <i class="fas fa-edit fa-2x"></i>
                    </div>
                    <h4 class="mb-1">{{ $stats['needs_revision'] ?? 0 }}</h4>
                    <p class="mb-0 text-muted small">Need Revision</p>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card text-center border-0 shadow-sm">
                <div class="card-body">
                    <div class="text-danger mb-2">
                        <i class="fas fa-exclamation-triangle fa-2x"></i>
                    </div>
                    <h4 class="mb-1">{{ $stats['overdue_reviews'] ?? 0 }}</h4>
                    <p class="mb-0 text-muted small">Overdue</p>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card text-center border-0 shadow-sm">
                <div class="card-body">
                    <div class="text-secondary mb-2">
                        <i class="fas fa-chart-line fa-2x"></i>
                    </div>
                    <h4 class="mb-1">{{ number_format(($stats['approved'] ?? 0) / max(($stats['total'] ?? 1), 1) * 100, 1) }}%</h4>
                    <p class="mb-0 text-muted small">Approval Rate</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Review Status</label>
                    <select name="status" class="form-select">
                        <option value="">All Statuses</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending Review</option>
                        <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="needs_revision" {{ request('status') == 'needs_revision' ? 'selected' : '' }}>Needs Revision</option>
                        <option value="reviewed" {{ request('status') == 'reviewed' ? 'selected' : '' }}>Reviewed</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Site Coordinator</label>
                    <select name="user_id" class="form-select">
                        <option value="">All Coordinators</option>
                        @foreach($siteCoordinators as $coordinator)
                            <option value="{{ $coordinator->id }}" {{ request('user_id') == $coordinator->id ? 'selected' : '' }}>
                                {{ $coordinator->first_name }} {{ $coordinator->last_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Project</label>
                    <select name="project_id" class="form-select">
                        <option value="">All Projects</option>
                        @foreach($projects as $project)
                            <option value="{{ $project->id }}" {{ request('project_id') == $project->id ? 'selected' : '' }}>
                                {{ $project->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Search</label>
                    <div class="input-group">
                        <input type="text" name="search" class="form-control" placeholder="Search reports..." value="{{ request('search') }}">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Task Reports Table -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Task Reports</h5>
        </div>
        <div class="card-body p-0">
            @if($reports->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>
                                    <input type="checkbox" id="selectAll" class="form-check-input">
                                </th>
                                <th>Report</th>
                                <th>Task & Project</th>
                                <th>Coordinator</th>
                                <th>Progress</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($reports as $report)
                                <tr class="{{ $report->is_overdue_for_review ? 'table-warning' : '' }}">
                                    <td>
                                        <input type="checkbox" name="report_ids[]" value="{{ $report->id }}" class="form-check-input report-checkbox">
                                    </td>
                                    <td>
                                        <div>
                                            <strong>{{ Str::limit($report->report_title, 40) }}</strong>
                                            @if($report->is_overdue_for_review)
                                                <span class="badge bg-danger ms-1">Overdue</span>
                                            @endif
                                            @if($report->issues_encountered)
                                                <span class="badge bg-warning ms-1">
                                                    <i class="fas fa-exclamation-triangle"></i> Issues
                                                </span>
                                            @endif
                                            @if($report->photos && count($report->photos) > 0)
                                                <span class="badge bg-info ms-1">
                                                    <i class="fas fa-camera"></i> {{ count($report->photos) }}
                                                </span>
                                            @endif
                                        </div>
                                        <small class="text-muted">{{ $report->formatted_report_date }}</small>
                                    </td>
                                    <td>
                                        <div>
                                            <strong>{{ Str::limit($report->task->task_name, 30) }}</strong>
                                            <br>
                                            <small class="text-muted">{{ $report->task->project->name }}</small>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-sm bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2">
                                                {{ substr($report->user->first_name, 0, 1) }}{{ substr($report->user->last_name, 0, 1) }}
                                            </div>
                                            <div>
                                                <small>{{ $report->user->first_name }} {{ $report->user->last_name }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="progress me-2" style="width: 60px; height: 8px;">
                                                <div class="progress-bar bg-{{ $report->progress_color }}" 
                                                     style="width: {{ $report->progress_percentage }}%"></div>
                                            </div>
                                            <small>{{ $report->progress_percentage }}%</small>
                                        </div>
                                        <small class="text-muted">
                                            Status: <span class="badge bg-{{ $report->task_status_badge_color }}">{{ $report->formatted_task_status }}</span>
                                        </small>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $report->review_status_badge_color }}">
                                            {{ $report->formatted_review_status }}
                                        </span>
                                        @if($report->admin_rating)
                                            <div class="mt-1">
                                                <small class="text-muted">
                                                    Rating: {!! $report->rating_stars !!}
                                                </small>
                                            </div>
                                        @endif
                                    </td>
                                    <td>
                                        <small>{{ $report->created_at->diffForHumans() }}</small>
                                        @if($report->hours_worked)
                                            <br><small class="text-muted">{{ $report->hours_worked }}h worked</small>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('pm.task-reports.show', $report) }}" 
                                               class="btn btn-outline-primary" title="View Report">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            @if($report->review_status === 'pending')
                                                <button type="button" class="btn btn-outline-success quick-approve-btn" 
                                                        data-report-id="{{ $report->id }}" title="Quick Approve">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                            @endif
                                            <a href="{{ route('tasks.show', $report->task) }}" 
                                               class="btn btn-outline-info" title="View Task">
                                                <i class="fas fa-tasks"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Bulk Actions -->
                <div class="card-footer">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <button type="button" class="btn btn-success btn-sm" id="bulkApproveBtn" disabled>
                                <i class="fas fa-check me-1"></i>Bulk Approve
                            </button>
                            <button type="button" class="btn btn-warning btn-sm" id="bulkReviewBtn" disabled>
                                <i class="fas fa-eye me-1"></i>Bulk Review
                            </button>
                            <span class="text-muted ms-2" id="selectedCount">0 selected</span>
                        </div>
                        <div>
                            {{ $reports->appends(request()->query())->links() }}
                        </div>
                    </div>
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-clipboard-list fa-3x text-muted mb-3"></i>
                    <h5>No Task Reports Found</h5>
                    <p class="text-muted">No task reports match your current filters.</p>
                    <a href="{{ route('pm.task-reports.index') }}" class="btn btn-primary">
                        <i class="fas fa-refresh me-1"></i>Clear Filters
                    </a>
                </div>
            @endif
        </div>
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
                <button type="button" class="btn btn-primary" id="submitReviewBtn">Submit Review</button>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
.avatar-sm {
    width: 32px;
    height: 32px;
    font-size: 12px;
    font-weight: bold;
}

.table-warning {
    background-color: rgba(255, 193, 7, 0.1) !important;
}

.card {
    border: none;
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}

.badge {
    font-size: 0.75em;
}

.progress {
    height: 8px;
}

.btn-group-sm .btn {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
}

.table th {
    border-top: none;
    border-bottom: 2px solid #dee2e6;
    font-weight: 600;
    color: #495057;
}

.table td {
    vertical-align: middle;
}

.quick-approve-btn:hover {
    background-color: #198754;
    color: white;
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Checkbox selection
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.report-checkbox');
    const bulkButtons = ['bulkApproveBtn', 'bulkReviewBtn'];
    const selectedCount = document.getElementById('selectedCount');

    selectAll.addEventListener('change', function() {
        checkboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
        updateBulkButtons();
    });

    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateBulkButtons);
    });

    function updateBulkButtons() {
        const selected = document.querySelectorAll('.report-checkbox:checked').length;
        selectedCount.textContent = `${selected} selected`;
        
        bulkButtons.forEach(btnId => {
            const btn = document.getElementById(btnId);
            if (btn) {
                btn.disabled = selected === 0;
            }
        });
    }

    // Quick approve buttons
    document.querySelectorAll('.quick-approve-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const reportId = this.dataset.reportId;
            quickReview(reportId, 'approved');
        });
    });

    // Quick review function
    function quickReview(reportId, status, comments = '', rating = '') {
        fetch(`{{ route('pm.task-reports.index') }}/${reportId}/quick-review`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                status: status,
                comments: comments,
                rating: rating
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error updating report status');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error updating report status');
        });
    }

    // Bulk approve
    document.getElementById('bulkApproveBtn').addEventListener('click', function() {
        const selected = Array.from(document.querySelectorAll('.report-checkbox:checked'))
                             .map(cb => cb.value);
        
        if (selected.length === 0) return;

        if (confirm(`Are you sure you want to approve ${selected.length} reports?`)) {
            fetch('{{ route("pm.task-reports.bulk-approve") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    report_ids: selected,
                    admin_comments: 'Bulk approved by PM',
                    admin_rating: null
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
    });

    // Modal review form
    const quickReviewModal = new bootstrap.Modal(document.getElementById('quickReviewModal'));
    const quickReviewForm = document.getElementById('quickReviewForm');
    const submitReviewBtn = document.getElementById('submitReviewBtn');

    document.getElementById('bulkReviewBtn').addEventListener('click', function() {
        const selected = document.querySelectorAll('.report-checkbox:checked');
        if (selected.length === 1) {
            document.getElementById('reviewReportId').value = selected[0].value;
            quickReviewModal.show();
        } else {
            alert('Please select exactly one report for detailed review');
        }
    });

    submitReviewBtn.addEventListener('click', function() {
        const formData = new FormData(quickReviewForm);
        const reportId = document.getElementById('reviewReportId').value;
        
        fetch(`{{ route('pm.task-reports.index') }}/${reportId}/quick-review`, {
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
                quickReviewModal.hide();
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
});
</script>
@endpush
@endsection