@extends('app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h2">
                    <i class="fas fa-edit me-2"></i>Manage Site Issue
                </h1>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.site-issues.show', $siteIssue) }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Back to Issue
                    </a>
                    <a href="{{ route('admin.site-issues.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-list me-1"></i> All Issues
                    </a>
                </div>
            </div>

            <div class="row">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Issue Management</h5>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('admin.site-issues.update', $siteIssue) }}" method="POST">
                                @csrf
                                @method('PUT')

                                <!-- Admin Management Section -->
                                <div class="alert alert-info">
                                    <h6><i class="fas fa-user-shield me-1"></i> Admin Management</h6>
                                    <div class="row">
                                        <div class="col-md-4">
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
                                        <div class="col-md-4">
                                            <label for="assigned_to" class="form-label">Assign To</label>
                                            <select name="assigned_to" id="assigned_to" class="form-select @error('assigned_to') is-invalid @enderror">
                                                <option value="">Unassigned</option>
                                                @foreach($availableTasks ?? [] as $user)
                                                    @if(is_object($user))
                                                        <option value="{{ $user->id }}" {{ old('assigned_to', $siteIssue->assigned_to) == $user->id ? 'selected' : '' }}>
                                                            {{ $user->full_name }}
                                                        </option>
                                                    @endif
                                                @endforeach
                                            </select>
                                            @error('assigned_to')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-md-4">
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
                                    </div>
                                </div>

                                <!-- Issue Details Section -->
                                <h6><i class="fas fa-info-circle me-1"></i> Issue Details</h6>
                                
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
                                        <label for="issue_type" class="form-label">Issue Type <span class="text-danger">*</span></label>
                                        <select name="issue_type" id="issue_type" class="form-select @error('issue_type') is-invalid @enderror" required>
                                            <option value="">Select Type</option>
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

                                    <div class="col-md-6 mb-3">
                                        <label for="location" class="form-label">Location</label>
                                        <input type="text" name="location" id="location" class="form-control @error('location') is-invalid @enderror" 
                                               value="{{ old('location', $siteIssue->location) }}" placeholder="e.g., Building A, Floor 2">
                                        @error('location')
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

                                <div class="mb-3">
                                    <label for="estimated_cost" class="form-label">Estimated Cost (₱)</label>
                                    <input type="number" name="estimated_cost" id="estimated_cost" step="0.01" min="0" 
                                           class="form-control @error('estimated_cost') is-invalid @enderror" 
                                           value="{{ old('estimated_cost', $siteIssue->estimated_cost) }}" placeholder="0.00">
                                    @error('estimated_cost')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Admin Notes Section -->
                                <div class="alert alert-secondary">
                                    <h6><i class="fas fa-sticky-note me-1"></i> Admin Notes & Resolution</h6>
                                    
                                    <div class="mb-3">
                                        <label for="admin_notes" class="form-label">Admin Notes</label>
                                        <textarea name="admin_notes" id="admin_notes" rows="3" class="form-control @error('admin_notes') is-invalid @enderror" 
                                                  placeholder="Internal admin notes about this issue...">{{ old('admin_notes', $siteIssue->admin_notes) }}</textarea>
                                        @error('admin_notes')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="mb-3">
                                        <label for="resolution_description" class="form-label">Resolution Description</label>
                                        <textarea name="resolution_description" id="resolution_description" rows="4" class="form-control @error('resolution_description') is-invalid @enderror" 
                                                  placeholder="Describe how the issue was resolved (required when marking as resolved)...">{{ old('resolution_description', $siteIssue->resolution_description) }}</textarea>
                                        @error('resolution_description')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <div class="form-text">
                                            <i class="fas fa-info-circle me-1"></i>
                                            This field is required when setting status to "Resolved"
                                        </div>
                                    </div>
                                </div>

                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-1"></i> Update Issue
                                    </button>
                                    <a href="{{ route('admin.site-issues.show', $siteIssue) }}" class="btn btn-outline-secondary">Cancel</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <!-- Current Status -->
                    <div class="card">
                        <div class="card-header">
                            <h6 class="card-title mb-0">
                                <i class="fas fa-info-circle me-1"></i> Current Status
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="text-center mb-3">
                                <div class="mb-2">
                                    <span class="badge bg-{{ $siteIssue->status_badge_color }} fs-6">
                                        {{ $siteIssue->formatted_status }}
                                    </span>
                                </div>
                                <div class="mb-2">
                                    <span class="badge bg-{{ $siteIssue->priority_badge_color }}">
                                        {{ $siteIssue->formatted_priority }} Priority
                                    </span>
                                </div>
                                <div>
                                    <span class="badge bg-{{ $siteIssue->issue_type_badge_color }}">
                                        {{ $siteIssue->formatted_issue_type }}
                                    </span>
                                </div>
                            </div>

                            <h6>Issue Information:</h6>
                            <ul class="list-unstyled">
                                <li><strong>Project:</strong> {{ $siteIssue->project->name }}</li>
                                <li><strong>Reported by:</strong> {{ $siteIssue->reporter->full_name }}</li>
                                <li><strong>Reported:</strong> {{ $siteIssue->formatted_reported_at }}</li>
                                <li><strong>Days Open:</strong> {{ $siteIssue->days_since_reported }}</li>
                                @if($siteIssue->assignedTo)
                                    <li><strong>Assigned to:</strong> {{ $siteIssue->assignedTo->full_name }}</li>
                                @endif
                                @if($siteIssue->acknowledged_at)
                                    <li><strong>Acknowledged:</strong> {{ $siteIssue->formatted_acknowledged_at }}</li>
                                @endif
                            </ul>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="card mt-3">
                        <div class="card-header">
                            <h6 class="card-title mb-0">
                                <i class="fas fa-bolt me-1"></i> Quick Actions
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                @if(!$siteIssue->acknowledged_at && $siteIssue->status === 'open')
                                    <form method="POST" action="{{ route('admin.site-issues.acknowledge', $siteIssue) }}" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-info w-100">
                                            <i class="fas fa-check me-1"></i> Acknowledge Issue
                                        </button>
                                    </form>
                                @endif

                                @if($siteIssue->status !== 'resolved')
                                    <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#quickResolveModal">
                                        <i class="fas fa-check-circle me-1"></i> Quick Resolve
                                    </button>
                                @endif

                                @if($siteIssue->status !== 'escalated')
                                    <form method="POST" action="{{ route('admin.site-issues.update', $siteIssue) }}" class="d-inline">
                                        @csrf
                                        @method('PUT')
                                        <input type="hidden" name="status" value="escalated">
                                        <input type="hidden" name="issue_title" value="{{ $siteIssue->issue_title }}">
                                        <input type="hidden" name="issue_type" value="{{ $siteIssue->issue_type }}">
                                        <input type="hidden" name="priority" value="{{ $siteIssue->priority }}">
                                        <input type="hidden" name="description" value="{{ $siteIssue->description }}">
                                        <button type="submit" class="btn btn-warning w-100">
                                            <i class="fas fa-exclamation-triangle me-1"></i> Escalate Issue
                                        </button>
                                    </form>
                                @endif

                                <a href="mailto:{{ $siteIssue->reporter->email }}" class="btn btn-outline-primary">
                                    <i class="fas fa-envelope me-1"></i> Email Reporter
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Warning Messages -->
                    @if($siteIssue->needs_attention)
                        <div class="card mt-3 border-warning">
                            <div class="card-header bg-warning text-dark">
                                <h6 class="card-title mb-0">
                                    <i class="fas fa-exclamation-triangle me-1"></i> Attention Required
                                </h6>
                            </div>
                            <div class="card-body">
                                <ul class="list-unstyled mb-0">
                                    @if($siteIssue->is_overdue_for_acknowledgment)
                                        <li><i class="fas fa-clock text-warning me-1"></i> Acknowledgment overdue</li>
                                    @endif
                                    @if($siteIssue->is_overdue_for_resolution)
                                        <li><i class="fas fa-exclamation-circle text-danger me-1"></i> Resolution overdue</li>
                                    @endif
                                    @if($siteIssue->priority === 'critical')
                                        <li><i class="fas fa-fire text-danger me-1"></i> Critical priority issue</li>
                                    @endif
                                </ul>
                            </div>
                        </div>
                    @endif

                    <!-- Photos Preview -->
                    @if($siteIssue->photos && count($siteIssue->photos) > 0)
                        <div class="card mt-3">
                            <div class="card-header">
                                <h6 class="card-title mb-0">
                                    <i class="fas fa-images me-1"></i> Photos ({{ count($siteIssue->photos) }})
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    @foreach($siteIssue->photos->take(4) as $photo)
                                        <div class="col-6 mb-2">
                                            <img src="{{ Storage::url($photo) }}" class="img-fluid rounded" style="max-height: 80px; cursor: pointer;"
                                                 data-bs-toggle="modal" data-bs-target="#photoModal{{ $loop->index }}">
                                        </div>
                                    @endforeach
                                </div>
                                @if(count($siteIssue->photos) > 4)
                                    <small class="text-muted">{{ count($siteIssue->photos) - 4 }} more photos available</small>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Resolve Modal -->
<div class="modal fade" id="quickResolveModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.site-issues.resolve', $siteIssue) }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Quick Resolve Issue</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="resolution_description_modal" class="form-label">Resolution Description <span class="text-danger">*</span></label>
                        <textarea name="resolution_description" id="resolution_description_modal" rows="4" class="form-control" 
                                  required placeholder="Describe how the issue was resolved..."></textarea>
                    </div>
                    <div class="alert alert-info">
                        <small><i class="fas fa-info-circle me-1"></i> 
                        The site coordinator will be automatically notified when this issue is marked as resolved.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-check-circle me-1"></i> Mark as Resolved
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Photo Modals -->
@if($siteIssue->photos && count($siteIssue->photos) > 0)
    @foreach($siteIssue->photos as $photo)
        <div class="modal fade" id="photoModal{{ $loop->index }}" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Issue Photo {{ $loop->iteration }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body text-center">
                        <img src="{{ Storage::url($photo) }}" class="img-fluid">
                    </div>
                </div>
            </div>
        </div>
    @endforeach
@endif
@endsection

@push('scripts')
<script>
// Auto-require resolution description when status is set to resolved
document.getElementById('status').addEventListener('change', function() {
    const resolutionField = document.getElementById('resolution_description');
    const resolutionLabel = resolutionField.previousElementSibling;
    
    if (this.value === 'resolved') {
        resolutionField.required = true;
        resolutionLabel.innerHTML = 'Resolution Description <span class="text-danger">*</span>';
        resolutionField.focus();
    } else {
        resolutionField.required = false;
        resolutionLabel.innerHTML = 'Resolution Description';
    }
});

// Auto-assign when changing to in_progress from open
document.getElementById('status').addEventListener('change', function() {
    const assignField = document.getElementById('assigned_to');
    
    if (this.value === 'in_progress' && assignField.value === '') {
        // Optionally auto-assign to current user
        // assignField.value = '{{ auth()->id() }}';
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