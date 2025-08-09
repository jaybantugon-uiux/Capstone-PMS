@extends('app')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-edit me-2"></i>Update Issue: {{ Str::limit($siteIssue->issue_title, 50) }}
                    </h5>
                    <div class="d-flex gap-2">
                        <a href="{{ route('admin.site-issues.show', $siteIssue) }}" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-arrow-left me-1"></i> Back to Issue
                        </a>
                        <a href="{{ route('admin.site-issues.index') }}" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-list me-1"></i> All Issues
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Current Status Display -->
                    <div class="alert alert-info">
                        <div class="row text-center">
                            <div class="col-md-2">
                                <strong>Current Status:</strong><br>
                                <span class="badge bg-{{ $siteIssue->status_badge_color }} fs-6">
                                    {{ $siteIssue->formatted_status }}
                                </span>
                            </div>
                            <div class="col-md-2">
                                <strong>Priority:</strong><br>
                                <span class="badge bg-{{ $siteIssue->priority_badge_color }}">
                                    {{ $siteIssue->formatted_priority }}
                                </span>
                            </div>
                            <div class="col-md-2">
                                <strong>Type:</strong><br>
                                <span class="badge bg-{{ $siteIssue->issue_type_badge_color }}">
                                    {{ $siteIssue->formatted_issue_type }}
                                </span>
                            </div>
                            <div class="col-md-2">
                                <strong>Assigned To:</strong><br>
                                {{ $siteIssue->assignedTo ? $siteIssue->assignedTo->full_name : 'Unassigned' }}
                            </div>
                            <div class="col-md-2">
                                <strong>Reported By:</strong><br>
                                {{ $siteIssue->reporter->full_name }}
                            </div>
                            <div class="col-md-2">
                                <strong>Days Open:</strong><br>
                                <span class="{{ $siteIssue->days_since_reported > 7 ? 'text-danger' : 'text-muted' }}">
                                    {{ $siteIssue->days_since_reported }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <form action="{{ route('admin.site-issues.update', $siteIssue) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <!-- Admin Management Section -->
                        <div class="card mb-4">
                            <div class="card-header bg-primary text-white">
                                <h6 class="mb-0"><i class="fas fa-user-shield me-1"></i> Admin Management</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-3 mb-3">
                                        <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                                        <select name="status" id="status" class="form-select @error('status') is-invalid @enderror" required>
                                            <option value="open" {{ old('status', $siteIssue->status) === 'open' ? 'selected' : '' }}>Open</option>
                                            <option value="in_progress" {{ old('status', $siteIssue->status) === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                            <option value="resolved" {{ old('status', $siteIssue->status) === 'resolved' ? 'selected' : '' }}>Resolved</option>
                                            <option value="closed" {{ old('status', $siteIssue->status) === 'closed' ? 'selected' : '' }}>Closed</option>
                                            <option value="escalated" {{ old('status', $siteIssue->status) === 'escalated' ? 'selected' : '' }}>Escalated</option>
                                        </select>
                                        @error('status')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-3 mb-3">
                                        <label for="priority" class="form-label">Priority <span class="text-danger">*</span></label>
                                        <select name="priority" id="priority" class="form-select @error('priority') is-invalid @enderror" required>
                                            <option value="low" {{ old('priority', $siteIssue->priority) === 'low' ? 'selected' : '' }}>Low</option>
                                            <option value="medium" {{ old('priority', $siteIssue->priority) === 'medium' ? 'selected' : '' }}>Medium</option>
                                            <option value="high" {{ old('priority', $siteIssue->priority) === 'high' ? 'selected' : '' }}>High</option>
                                            <option value="critical" {{ old('priority', $siteIssue->priority) === 'critical' ? 'selected' : '' }}>Critical</option>
                                        </select>
                                        @error('priority')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-3 mb-3">
                                        <label for="assigned_to" class="form-label">Assign To</label>
                                        <select name="assigned_to" id="assigned_to" class="form-select @error('assigned_to') is-invalid @enderror">
                                            <option value="">Unassigned</option>
                                            @php
                                                $assignableUsers = \App\Models\User::whereIn('role', ['admin', 'pm'])->get();
                                            @endphp
                                            @foreach($assignableUsers as $user)
                                                <option value="{{ $user->id }}" {{ old('assigned_to', $siteIssue->assigned_to) == $user->id ? 'selected' : '' }}>
                                                    {{ $user->full_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('assigned_to')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-3 mb-3">
                                        <label for="issue_type" class="form-label">Issue Type <span class="text-danger">*</span></label>
                                        <select name="issue_type" id="issue_type" class="form-select @error('issue_type') is-invalid @enderror" required>
                                            <option value="safety" {{ old('issue_type', $siteIssue->issue_type) === 'safety' ? 'selected' : '' }}>Safety</option>
                                            <option value="equipment" {{ old('issue_type', $siteIssue->issue_type) === 'equipment' ? 'selected' : '' }}>Equipment</option>
                                            <option value="environmental" {{ old('issue_type', $siteIssue->issue_type) === 'environmental' ? 'selected' : '' }}>Environmental</option>
                                            <option value="personnel" {{ old('issue_type', $siteIssue->issue_type) === 'personnel' ? 'selected' : '' }}>Personnel</option>
                                            <option value="quality" {{ old('issue_type', $siteIssue->issue_type) === 'quality' ? 'selected' : '' }}>Quality</option>
                                            <option value="timeline" {{ old('issue_type', $siteIssue->issue_type) === 'timeline' ? 'selected' : '' }}>Timeline</option>
                                            <option value="other" {{ old('issue_type', $siteIssue->issue_type) === 'other' ? 'selected' : '' }}>Other</option>
                                        </select>
                                        @error('issue_type')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Issue Details Section -->
                        <div class="card mb-4">
                            <div class="card-header bg-secondary text-white">
                                <h6 class="mb-0"><i class="fas fa-info-circle me-1"></i> Issue Details</h6>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label for="issue_title" class="form-label">Issue Title <span class="text-danger">*</span></label>
                                    <input type="text" name="issue_title" id="issue_title" class="form-control @error('issue_title') is-invalid @enderror" 
                                           value="{{ old('issue_title', $siteIssue->issue_title) }}" required>
                                    @error('issue_title')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="location" class="form-label">Location</label>
                                        <input type="text" name="location" id="location" class="form-control @error('location') is-invalid @enderror" 
                                               value="{{ old('location', $siteIssue->location) }}" placeholder="e.g., Building A, Floor 2">
                                        @error('location')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="estimated_cost" class="form-label">Estimated Cost (₱)</label>
                                        <input type="number" name="estimated_cost" id="estimated_cost" step="0.01" min="0" 
                                               class="form-control @error('estimated_cost') is-invalid @enderror" 
                                               value="{{ old('estimated_cost', $siteIssue->estimated_cost) }}" placeholder="0.00">
                                        @error('estimated_cost')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="description" class="form-label">Issue Description <span class="text-danger">*</span></label>
                                    <textarea name="description" id="description" rows="4" class="form-control @error('description') is-invalid @enderror" 
                                              required placeholder="Detailed description of the issue...">{{ old('description', $siteIssue->description) }}</textarea>
                                    @error('description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="affected_areas" class="form-label">Affected Areas</label>
                                    <textarea name="affected_areas" id="affected_areas" rows="2" class="form-control @error('affected_areas') is-invalid @enderror" 
                                              placeholder="Areas or operations affected by this issue...">{{ old('affected_areas', $siteIssue->affected_areas) }}</textarea>
                                    @error('affected_areas')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="immediate_actions_taken" class="form-label">Immediate Actions Taken</label>
                                    <textarea name="immediate_actions_taken" id="immediate_actions_taken" rows="3" class="form-control @error('immediate_actions_taken') is-invalid @enderror" 
                                              placeholder="What immediate actions were taken to address this issue?">{{ old('immediate_actions_taken', $siteIssue->immediate_actions_taken) }}</textarea>
                                    @error('immediate_actions_taken')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="suggested_solutions" class="form-label">Suggested Solutions</label>
                                    <textarea name="suggested_solutions" id="suggested_solutions" rows="3" class="form-control @error('suggested_solutions') is-invalid @enderror" 
                                              placeholder="Suggested solutions for resolving this issue...">{{ old('suggested_solutions', $siteIssue->suggested_solutions) }}</textarea>
                                    @error('suggested_solutions')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Admin Notes & Resolution Section -->
                        <div class="card mb-4">
                            <div class="card-header bg-warning text-dark">
                                <h6 class="mb-0"><i class="fas fa-sticky-note me-1"></i> Admin Notes & Resolution</h6>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label for="admin_notes" class="form-label">Admin Notes (Internal)</label>
                                    <textarea name="admin_notes" id="admin_notes" rows="3" class="form-control @error('admin_notes') is-invalid @enderror" 
                                              placeholder="Internal notes for admin team (not visible to site coordinator)...">{{ old('admin_notes', $siteIssue->admin_notes) }}</textarea>
                                    @error('admin_notes')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">
                                        <i class="fas fa-lock me-1"></i>
                                        These notes are only visible to admin team members
                                    </div>
                                </div>

                                <!-- Resolution Description (shows when status is resolved) -->
                                <div id="resolution-section" class="mb-3" style="{{ $siteIssue->status === 'resolved' ? '' : 'display: none;' }}">
                                    <label for="resolution_description" class="form-label">
                                        Resolution Description <span class="text-danger">*</span>
                                    </label>
                                    <textarea name="resolution_description" id="resolution_description" rows="4" 
                                              class="form-control @error('resolution_description') is-invalid @enderror" 
                                              placeholder="Describe how the issue was resolved...">{{ old('resolution_description', $siteIssue->resolution_description) }}</textarea>
                                    @error('resolution_description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">
                                        <i class="fas fa-info-circle me-1"></i>
                                        This field is required when setting status to "Resolved". The site coordinator will be notified automatically.
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-save me-1"></i> Update Issue
                            </button>
                            <a href="{{ route('admin.site-issues.show', $siteIssue) }}" class="btn btn-outline-secondary btn-lg">
                                Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Sidebar with Quick Actions and Info -->
        <div class="col-md-2">
            <!-- Quick Actions -->
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-bolt me-1"></i> Quick Actions
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        @if(!$siteIssue->acknowledged_at && $siteIssue->status === 'open')
                            <form method="POST" action="{{ route('admin.site-issues.acknowledge', $siteIssue) }}">
                                @csrf
                                <button type="submit" class="btn btn-info w-100 btn-sm">
                                    <i class="fas fa-check me-1"></i> Acknowledge
                                </button>
                            </form>
                        @endif

                        @if(!$siteIssue->assigned_to)
                            <form method="POST" action="{{ route('admin.site-issues.assign', $siteIssue) }}">
                                @csrf
                                <input type="hidden" name="assigned_to" value="{{ auth()->id() }}">
                                <button type="submit" class="btn btn-success w-100 btn-sm">
                                    <i class="fas fa-user me-1"></i> Assign to Me
                                </button>
                            </form>
                        @endif

                        <a href="{{ route('admin.site-issues.add-comment', $siteIssue) }}" class="btn btn-warning w-100 btn-sm">
                            <i class="fas fa-comment me-1"></i> Add Comment
                        </a>

                        <a href="mailto:{{ $siteIssue->reporter->email }}" class="btn btn-outline-primary w-100 btn-sm">
                            <i class="fas fa-envelope me-1"></i> Email Reporter
                        </a>
                    </div>
                </div>
            </div>

            <!-- Issue Statistics -->
            <div class="card mt-3">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-chart-bar me-1"></i> Issue Info
                    </h6>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <h5 class="text-primary">{{ $siteIssue->days_since_reported }}</h5>
                        <small class="text-muted">Days Open</small>
                    </div>
                    
                    <hr>
                    
                    <div class="text-center mb-3">
                        <h5 class="text-info">{{ $siteIssue->comments->count() }}</h5>
                        <small class="text-muted">Comments</small>
                    </div>

                    @if($siteIssue->resolution_time)
                        <hr>
                        <div class="text-center">
                            <h5 class="text-success">{{ $siteIssue->resolution_time }}</h5>
                            <small class="text-muted">Resolution Time</small>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Attention Alerts -->
            @if($siteIssue->needs_attention)
                <div class="card mt-3 border-warning">
                    <div class="card-header bg-warning text-dark">
                        <h6 class="card-title mb-0">
                            <i class="fas fa-exclamation-triangle me-1"></i> Attention!
                        </h6>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled mb-0 small">
                            @if($siteIssue->is_overdue_for_acknowledgment)
                                <li><i class="fas fa-clock text-warning me-1"></i> Acknowledgment overdue</li>
                            @endif
                            @if($siteIssue->is_overdue_for_resolution)
                                <li><i class="fas fa-exclamation-circle text-danger me-1"></i> Resolution overdue</li>
                            @endif
                            @if($siteIssue->priority === 'critical')
                                <li><i class="fas fa-fire text-danger me-1"></i> Critical priority</li>
                            @endif
                        </ul>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('status').addEventListener('change', function() {
    const resolutionSection = document.getElementById('resolution-section');
    const resolutionField = document.getElementById('resolution_description');
    
    if (this.value === 'resolved') {
        resolutionSection.style.display = 'block';
        resolutionField.required = true;
        resolutionField.focus();
    } else {
        resolutionSection.style.display = 'none';
        resolutionField.required = false;
    }
});

// Auto-assign when changing to in_progress from open
document.getElementById('status').addEventListener('change', function() {
    const assignField = document.getElementById('assigned_to');
    
    if (this.value === 'in_progress' && assignField.value === '' && '{{ $siteIssue->status }}' === 'open') {
        // Optionally auto-assign to current user
        const currentUserId = '{{ auth()->id() }}';
        if (confirm('This issue is being set to "In Progress". Would you like to assign it to yourself?')) {
            assignField.value = currentUserId;
        }
    }
});

// Highlight priority changes
document.getElementById('priority').addEventListener('change', function() {
    const originalPriority = '{{ $siteIssue->priority }}';
    
    if (this.value === 'critical' && originalPriority !== 'critical') {
        if (!confirm('Setting priority to CRITICAL will send urgent notifications to all admin users. Continue?')) {
            this.value = originalPriority;
        }
    }
});
</script>
@endpush

@push('styles')
<style>
.fs-6 {
    font-size: 1rem !important;
}
</style>
@endpush
<i class="fas fa-info-circle me-1"></i>
                                These notes are only visible to admin team members
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> Update Issue
                            </button>
                            <a href="{{ route('admin.site-issues.show', $siteIssue) }}" class="btn btn-outline-secondary">
                                Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card mt-3">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-bolt me-2"></i>Quick Actions
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        @if(!$siteIssue->acknowledged_at && $siteIssue->status === 'open')
                            <div class="col-md-3">
                                <form method="POST" action="{{ route('admin.site-issues.acknowledge', $siteIssue) }}">
                                    @csrf
                                    <button type="submit" class="btn btn-info w-100">
                                        <i class="fas fa-check me-1"></i> Acknowledge
                                    </button>
                                </form>
                            </div>
                        @endif

                        @if(!$siteIssue->assigned_to)
                            <div class="col-md-3">
                                <form method="POST" action="{{ route('admin.site-issues.assign', $siteIssue) }}">
                                    @csrf
                                    <input type="hidden" name="assigned_to" value="{{ auth()->id() }}">
                                    <button type="submit" class="btn btn-success w-100">
                                        <i class="fas fa-user me-1"></i> Assign to Me
                                    </button>
                                </form>
                            </div>
                        @endif

                        <div class="col-md-3">
                            <a href="{{ route('admin.site-issues.add-comment', $siteIssue) }}" class="btn btn-warning w-100">
                                <i class="fas fa-comment me-1"></i> Add Comment
                            </a>
                        </div>

                        <div class="col-md-3">
                            <a href="mailto:{{ $siteIssue->reporter->email }}" class="btn btn-outline-primary w-100">
                                <i class="fas fa-envelope me-1"></i> Email Reporter
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('status').addEventListener('change', function() {
    const resolutionSection = document.getElementById('resolution-section');
    const resolutionField = document.getElementById('resolution_description');
    
    if (this.value === 'resolved') {
        resolutionSection.style.display = 'block';
        resolutionField.required = true;
        resolutionField.focus();
    } else {
        resolutionSection.style.display = 'none';
        resolutionField.required = false;
    }
});
</script>
@endpush

@push('styles')
<style>
.fs-6 {
    font-size: 1rem !important;
}
</style>
@endpush