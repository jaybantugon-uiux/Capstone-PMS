@extends('app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1>Site Issues Management</h1>
            <p class="text-muted">Monitor and manage site issues from your projects</p>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#filterModal">
                <i class="fas fa-filter me-1"></i>Filters
            </button>
            <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#bulkActionModal">
                <i class="fas fa-tasks me-1"></i>Bulk Actions
            </button>
            <a href="{{ route('pm.site-issues.export') }}" class="btn btn-success">
                <i class="fas fa-download me-1"></i>Export
            </a>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-2">
            <div class="card bg-primary text-white">
                <div class="card-body text-center">
                    <i class="fas fa-list fa-2x mb-2"></i>
                    <h3>{{ $stats['total'] }}</h3>
                    <p class="mb-0">Total Issues</p>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-warning text-white">
                <div class="card-body text-center">
                    <i class="fas fa-exclamation-circle fa-2x mb-2"></i>
                    <h3>{{ $stats['open'] }}</h3>
                    <p class="mb-0">Open Issues</p>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-info text-white">
                <div class="card-body text-center">
                    <i class="fas fa-cog fa-2x mb-2"></i>
                    <h3>{{ $stats['in_progress'] }}</h3>
                    <p class="mb-0">In Progress</p>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-danger text-white">
                <div class="card-body text-center">
                    <i class="fas fa-fire fa-2x mb-2"></i>
                    <h3>{{ $stats['critical'] }}</h3>
                    <p class="mb-0">Critical</p>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-secondary text-white">
                <div class="card-body text-center">
                    <i class="fas fa-eye-slash fa-2x mb-2"></i>
                    <h3>{{ $stats['unacknowledged'] }}</h3>
                    <p class="mb-0">Unacknowledged</p>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-success text-white">
                <div class="card-body text-center">
                    <i class="fas fa-check fa-2x mb-2"></i>
                    <h3>{{ $stats['resolved'] }}</h3>
                    <p class="mb-0">Resolved</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Filters -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex flex-wrap gap-2">
                        <a href="{{ route('pm.site-issues.index') }}" 
                           class="btn btn-sm {{ !request()->has('status') && !request()->has('priority') ? 'btn-primary' : 'btn-outline-primary' }}">
                            All Issues
                        </a>
                        <a href="{{ route('pm.site-issues.index', ['status' => 'open']) }}" 
                           class="btn btn-sm {{ request('status') === 'open' ? 'btn-warning' : 'btn-outline-warning' }}">
                            Open ({{ $stats['open'] }})
                        </a>
                        <a href="{{ route('pm.site-issues.index', ['priority' => 'critical']) }}" 
                           class="btn btn-sm {{ request('priority') === 'critical' ? 'btn-danger' : 'btn-outline-danger' }}">
                            Critical ({{ $stats['critical'] }})
                        </a>
                        <a href="{{ route('pm.site-issues.index', ['status' => 'in_progress']) }}" 
                           class="btn btn-sm {{ request('status') === 'in_progress' ? 'btn-info' : 'btn-outline-info' }}">
                            In Progress ({{ $stats['in_progress'] }})
                        </a>
                        @if($stats['unacknowledged'] > 0)
                        <a href="{{ route('pm.site-issues.index', ['unacknowledged' => 1]) }}" 
                           class="btn btn-sm {{ request('unacknowledged') ? 'btn-secondary' : 'btn-outline-secondary' }}">
                            Unacknowledged ({{ $stats['unacknowledged'] }})
                        </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Issues Table -->
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Site Issues ({{ $siteIssues->total() }} found)</h5>
                <div class="d-flex gap-2">
                    <input type="checkbox" id="selectAll" class="form-check-input">
                    <label for="selectAll" class="form-check-label">Select All</label>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            @if($siteIssues->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 40px;">
                                <input type="checkbox" id="selectAllHeader" class="form-check-input">
                            </th>
                            <th>Issue</th>
                            <th>Project</th>
                            <th>Reporter</th>
                            <th>Type</th>
                            <th>Priority</th>
                            <th>Status</th>
                            <th>Assigned To</th>
                            <th>Reported</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($siteIssues as $issue)
                        <tr class="{{ $issue->priority === 'critical' ? 'table-danger' : ($issue->is_overdue_for_acknowledgment ? 'table-warning' : '') }}">
                            <td>
                                <input type="checkbox" class="form-check-input issue-checkbox" value="{{ $issue->id }}">
                            </td>
                            <td>
                                <div>
                                    <a href="{{ route('pm.site-issues.show', $issue) }}" class="fw-semibold text-decoration-none">
                                        {{ Str::limit($issue->issue_title, 40) }}
                                    </a>
                                    @if($issue->issue_type === 'safety')
                                        <span class="badge bg-danger ms-1">Safety</span>
                                    @endif
                                    @if(!$issue->acknowledged_at)
                                        <span class="badge bg-warning ms-1">New</span>
                                    @endif
                                </div>
                                @if($issue->location)
                                    <small class="text-muted">
                                        <i class="fas fa-map-marker-alt"></i> {{ $issue->location }}
                                    </small>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('projects.show', $issue->project) }}" class="text-decoration-none">
                                    {{ Str::limit($issue->project->name, 30) }}
                                </a>
                                @if($issue->task)
                                    <br><small class="text-muted">{{ Str::limit($issue->task->task_name, 25) }}</small>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar-sm me-2">
                                        <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center text-white" style="width: 32px; height: 32px;">
                                            {{ substr($issue->reporter->first_name, 0, 1) }}{{ substr($issue->reporter->last_name, 0, 1) }}
                                        </div>
                                    </div>
                                    <div>
                                        <div class="fw-semibold">{{ $issue->reporter->first_name }} {{ $issue->reporter->last_name }}</div>
                                        <small class="text-muted">{{ $issue->reporter->role }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-{{ $issue->issue_type_badge_color }}">
                                    {{ ucfirst($issue->issue_type) }}
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-{{ $issue->priority_badge_color }}">
                                    {{ ucfirst($issue->priority) }}
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-{{ $issue->status_badge_color }}">
                                    {{ ucfirst(str_replace('_', ' ', $issue->status)) }}
                                </span>
                                @if($issue->is_overdue_for_acknowledgment)
                                    <br><small class="text-danger">Overdue</small>
                                @endif
                            </td>
                            <td>
                                @if($issue->assignedTo)
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-sm me-2">
                                            <div class="bg-success rounded-circle d-flex align-items-center justify-content-center text-white" style="width: 28px; height: 28px; font-size: 0.8em;">
                                                {{ substr($issue->assignedTo->first_name, 0, 1) }}{{ substr($issue->assignedTo->last_name, 0, 1) }}
                                            </div>
                                        </div>
                                        <small>{{ $issue->assignedTo->first_name }} {{ $issue->assignedTo->last_name }}</small>
                                    </div>
                                @else
                                    <span class="text-muted">Unassigned</span>
                                @endif
                            </td>
                            <td>
                                <div>{{ $issue->reported_at->format('M d, Y') }}</div>
                                <small class="text-muted">{{ $issue->reported_at->diffForHumans() }}</small>
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-sm btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown">
                                        Actions
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" href="{{ route('pm.site-issues.show', $issue) }}">
                                            <i class="fas fa-eye me-1"></i>View Details</a></li>
                                        @if(!$issue->acknowledged_at)
                                        <li><a class="dropdown-item" href="#" onclick="acknowledgeIssue({{ $issue->id }})">
                                            <i class="fas fa-check me-1"></i>Acknowledge</a></li>
                                        @endif
                                        @if(!$issue->assignedTo)
                                        <li><a class="dropdown-item" href="#" onclick="showAssignModal({{ $issue->id }}, '{{ $issue->issue_title }}')">
                                            <i class="fas fa-user-plus me-1"></i>Assign</a></li>
                                        @endif
                                        @if($issue->status !== 'resolved')
                                        <li><a class="dropdown-item" href="#" onclick="showResolveModal({{ $issue->id }}, '{{ $issue->issue_title }}')">
                                            <i class="fas fa-check-circle me-1"></i>Mark Resolved</a></li>
                                        @endif
                                        <li><hr class="dropdown-divider"></li>
                                        <li><a class="dropdown-item" href="{{ route('pm.site-issues.edit', $issue) }}">
                                            <i class="fas fa-edit me-1"></i>Edit</a></li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="text-center py-5">
                <i class="fas fa-exclamation-triangle fa-3x text-muted mb-3"></i>
                <h5>No site issues found</h5>
                <p class="text-muted">No site issues match your current filters.</p>
                <a href="{{ route('pm.site-issues.index') }}" class="btn btn-primary">Clear Filters</a>
            </div>
            @endif
        </div>
        @if($siteIssues->hasPages())
        <div class="card-footer">
            {{ $siteIssues->links() }}
        </div>
        @endif
    </div>
</div>

<!-- Filter Modal -->
<div class="modal fade" id="filterModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="GET" action="{{ route('pm.site-issues.index') }}">
                <div class="modal-header">
                    <h5 class="modal-title">Filter Site Issues</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-select">
                                    <option value="">All Statuses</option>
                                    <option value="open" {{ request('status') === 'open' ? 'selected' : '' }}>Open</option>
                                    <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                    <option value="resolved" {{ request('status') === 'resolved' ? 'selected' : '' }}>Resolved</option>
                                    <option value="closed" {{ request('status') === 'closed' ? 'selected' : '' }}>Closed</option>
                                    <option value="escalated" {{ request('status') === 'escalated' ? 'selected' : '' }}>Escalated</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Priority</label>
                                <select name="priority" class="form-select">
                                    <option value="">All Priorities</option>
                                    <option value="critical" {{ request('priority') === 'critical' ? 'selected' : '' }}>Critical</option>
                                    <option value="high" {{ request('priority') === 'high' ? 'selected' : '' }}>High</option>
                                    <option value="medium" {{ request('priority') === 'medium' ? 'selected' : '' }}>Medium</option>
                                    <option value="low" {{ request('priority') === 'low' ? 'selected' : '' }}>Low</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Issue Type</label>
                                <select name="issue_type" class="form-select">
                                    <option value="">All Types</option>
                                    <option value="safety" {{ request('issue_type') === 'safety' ? 'selected' : '' }}>Safety</option>
                                    <option value="equipment" {{ request('issue_type') === 'equipment' ? 'selected' : '' }}>Equipment</option>
                                    <option value="environmental" {{ request('issue_type') === 'environmental' ? 'selected' : '' }}>Environmental</option>
                                    <option value="personnel" {{ request('issue_type') === 'personnel' ? 'selected' : '' }}>Personnel</option>
                                    <option value="quality" {{ request('issue_type') === 'quality' ? 'selected' : '' }}>Quality</option>
                                    <option value="timeline" {{ request('issue_type') === 'timeline' ? 'selected' : '' }}>Timeline</option>
                                    <option value="other" {{ request('issue_type') === 'other' ? 'selected' : '' }}>Other</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
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
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Assigned To</label>
                                <select name="assigned_to" class="form-select">
                                    <option value="">All Assignees</option>
                                    <option value="unassigned" {{ request('assigned_to') === 'unassigned' ? 'selected' : '' }}>Unassigned</option>
                                    @foreach($assignableUsers as $user)
                                    <option value="{{ $user->id }}" {{ request('assigned_to') == $user->id ? 'selected' : '' }}>
                                        {{ $user->first_name }} {{ $user->last_name }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="unacknowledged" value="1" id="unacknowledged" {{ request('unacknowledged') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="unacknowledged">
                                        Unacknowledged Only
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <a href="{{ route('pm.site-issues.index') }}" class="btn btn-outline-danger">Clear</a>
                    <button type="submit" class="btn btn-primary">Apply Filters</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Bulk Action Modal -->
<div class="modal fade" id="bulkActionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="bulkActionForm" method="POST" action="{{ route('pm.site-issues.bulk-action') }}">
                @csrf
                <input type="hidden" name="ids" id="selectedIds">
                <div class="modal-header">
                    <h5 class="modal-title">Bulk Actions</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Action</label>
                        <select name="action" id="bulkAction" class="form-select" required>
                            <option value="">Select Action</option>
                            <option value="assign">Assign Issues</option>
                            <option value="acknowledge">Acknowledge Issues</option>
                            <option value="change_status">Change Status</option>
                            <option value="change_priority">Change Priority</option>
                        </select>
                    </div>
                    
                    <div id="assignFields" style="display: none;">
                        <div class="mb-3">
                            <label class="form-label">Assign To</label>
                            <select name="assigned_to" class="form-select">
                                <option value="">Select User</option>
                                @foreach($assignableUsers as $user)
                                <option value="{{ $user->id }}">{{ $user->first_name }} {{ $user->last_name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    
                    <div id="statusFields" style="display: none;">
                        <div class="mb-3">
                            <label class="form-label">New Status</label>
                            <select name="status" class="form-select">
                                <option value="">Select Status</option>
                                <option value="open">Open</option>
                                <option value="in_progress">In Progress</option>
                                <option value="resolved">Resolved</option>
                                <option value="closed">Closed</option>
                                <option value="escalated">Escalated</option>
                            </select>
                        </div>
                    </div>
                    
                    <div id="priorityFields" style="display: none;">
                        <div class="mb-3">
                            <label class="form-label">New Priority</label>
                            <select name="priority" class="form-select">
                                <option value="">Select Priority</option>
                                <option value="critical">Critical</option>
                                <option value="high">High</option>
                                <option value="medium">Medium</option>
                                <option value="low">Low</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="alert alert-info">
                        <span id="selectedCount">0</span> issue(s) selected
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Apply Action</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Assign Modal -->
<div class="modal fade" id="assignModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="assignForm" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Assign Issue</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Assign issue: <strong id="assignIssueTitle"></strong></p>
                    <div class="mb-3">
                        <label class="form-label">Assign To</label>
                        <select name="assigned_to" class="form-select" required>
                            <option value="">Select User</option>
                            @foreach($assignableUsers as $user)
                            <option value="{{ $user->id }}">{{ $user->first_name }} {{ $user->last_name }} ({{ ucfirst($user->role) }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Assign Issue</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Resolve Modal -->
<div class="modal fade" id="resolveModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="resolveForm" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Resolve Issue</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Mark issue as resolved: <strong id="resolveIssueTitle"></strong></p>
                    <div class="mb-3">
                        <label class="form-label">Resolution Description</label>
                        <textarea name="resolution_description" class="form-control" rows="3" required 
                                  placeholder="Describe how this issue was resolved..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Mark as Resolved</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Select all functionality
    const selectAllHeader = document.getElementById('selectAllHeader');
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.issue-checkbox');
    
    [selectAllHeader, selectAll].forEach(checkbox => {
        if (checkbox) {
            checkbox.addEventListener('change', function() {
                checkboxes.forEach(cb => cb.checked = this.checked);
                updateSelectedCount();
                // Sync both select all checkboxes
                if (selectAllHeader && selectAll) {
                    selectAllHeader.checked = this.checked;
                    selectAll.checked = this.checked;
                }
            });
        }
    });
    
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateSelectedCount);
    });
    
    // Bulk action modal show
    const bulkActionModal = document.getElementById('bulkActionModal');
    if (bulkActionModal) {
        bulkActionModal.addEventListener('show.bs.modal', function() {
            const selectedIds = Array.from(document.querySelectorAll('.issue-checkbox:checked')).map(cb => cb.value);
            document.getElementById('selectedIds').value = selectedIds.join(',');
            updateSelectedCount();
        });
    }
    
    // Bulk action field toggling
    const bulkActionSelect = document.getElementById('bulkAction');
    if (bulkActionSelect) {
        bulkActionSelect.addEventListener('change', function() {
            // Hide all conditional fields
            document.getElementById('assignFields').style.display = 'none';
            document.getElementById('statusFields').style.display = 'none';
            document.getElementById('priorityFields').style.display = 'none';
            
            // Show relevant fields
            switch(this.value) {
                case 'assign':
                    document.getElementById('assignFields').style.display = 'block';
                    break;
                case 'change_status':
                    document.getElementById('statusFields').style.display = 'block';
                    break;
                case 'change_priority':
                    document.getElementById('priorityFields').style.display = 'block';
                    break;
            }
        });
    }
    
    function updateSelectedCount() {
        const count = document.querySelectorAll('.issue-checkbox:checked').length;
        const countElement = document.getElementById('selectedCount');
        if (countElement) {
            countElement.textContent = count;
        }
    }
});

function showAssignModal(issueId, issueTitle) {
    document.getElementById('assignIssueTitle').textContent = issueTitle;
    document.getElementById('assignForm').action = `/pm/site-issues/${issueId}/assign`;
    new bootstrap.Modal(document.getElementById('assignModal')).show();
}

function showResolveModal(issueId, issueTitle) {
    document.getElementById('resolveIssueTitle').textContent = issueTitle;
    document.getElementById('resolveForm').action = `/pm/site-issues/${issueId}/resolve`;
    new bootstrap.Modal(document.getElementById('resolveModal')).show();
}

function acknowledgeIssue(issueId) {
    if (confirm('Are you sure you want to acknowledge this issue?')) {
        fetch(`/pm/site-issues/${issueId}/acknowledge`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error acknowledging issue');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error acknowledging issue');
        });
    }
}

// Auto-refresh page every 5 minutes to show new issues
setInterval(function() {
    // Only refresh if no modals are open
    const openModals = document.querySelectorAll('.modal.show');
    if (openModals.length === 0) {
        // Check for new critical issues
        fetch('/pm/site-issues/api/stats')
            .then(response => response.json())
            .then(data => {
                // Update critical count if it increased
                const currentCritical = parseInt(document.querySelector('.bg-danger h3').textContent);
                if (data.critical > currentCritical) {
                    location.reload();
                }
            })
            .catch(error => console.log('Error checking for updates:', error));
    }
}, 300000); // 5 minutes
</script>
@endpush

@push('styles')
<style>
.table-danger {
    background-color: #f8d7da !important;
}
.table-warning {
    background-color: #fff3cd !important;
}
.avatar-sm {
    width: 32px;
    height: 32px;
}
.card {
    border: none;
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}
.card:hover {
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
}
.badge {
    font-size: 0.75em;
}
.btn-group .dropdown-toggle::after {
    margin-left: 0.5em;
}
</style>
@endpush
@endsection