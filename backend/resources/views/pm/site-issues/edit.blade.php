@extends('app')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('pm.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('pm.site-issues.index') }}">Site Issues</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('pm.site-issues.show', $siteIssue) }}">Issue #{{ $siteIssue->id }}</a></li>
                    <li class="breadcrumb-item active">Edit</li>
                </ol>
            </nav>
            <h1>Edit Site Issue</h1>
            <p class="text-muted">Update issue details and management information</p>
        </div>
        <div>
            <a href="{{ route('pm.site-issues.show', $siteIssue) }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i>Back to Issue
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <!-- Main Form -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Issue Information</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('pm.site-issues.update', $siteIssue) }}">
                        @csrf
                        @method('PUT')
                        
                        <!-- Basic Information -->
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label for="issue_title" class="form-label">Issue Title <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('issue_title') is-invalid @enderror" 
                                           id="issue_title" name="issue_title" value="{{ old('issue_title', $siteIssue->issue_title) }}" 
                                           required maxlength="255">
                                    @error('issue_title')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="issue_type" class="form-label">Issue Type <span class="text-danger">*</span></label>
                                    <select class="form-select @error('issue_type') is-invalid @enderror" 
                                            id="issue_type" name="issue_type" required>
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
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="priority" class="form-label">Priority <span class="text-danger">*</span></label>
                                    <select class="form-select @error('priority') is-invalid @enderror" 
                                            id="priority" name="priority" required>
                                        <option value="">Select Priority</option>
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
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                                    <select class="form-select @error('status') is-invalid @enderror" 
                                            id="status" name="status" required>
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
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="assigned_to" class="form-label">Assign To</label>
                                    <select class="form-select @error('assigned_to') is-invalid @enderror" 
                                            id="assigned_to" name="assigned_to">
                                        <option value="">Unassigned</option>
                                        @foreach($assignableUsers as $user)
                                        <option value="{{ $user->id }}" 
                                                {{ old('assigned_to', $siteIssue->assigned_to) == $user->id ? 'selected' : '' }}>
                                            {{ $user->first_name }} {{ $user->last_name }} ({{ ucfirst($user->role) }})
                                        </option>
                                        @endforeach
                                    </select>
                                    @error('assigned_to')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="estimated_cost" class="form-label">Estimated Cost (₱)</label>
                                    <input type="number" class="form-control @error('estimated_cost') is-invalid @enderror" 
                                           id="estimated_cost" name="estimated_cost" 
                                           value="{{ old('estimated_cost', $siteIssue->estimated_cost) }}" 
                                           min="0" step="0.01">
                                    @error('estimated_cost')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label for="location" class="form-label">Location</label>
                                    <input type="text" class="form-control @error('location') is-invalid @enderror" 
                                           id="location" name="location" value="{{ old('location', $siteIssue->location) }}" 
                                           maxlength="255" placeholder="Specific location where the issue occurred">
                                    @error('location')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Description -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="mb-3">
                                    <label for="description" class="form-label">Description <span class="text-danger">*</span></label>
                                    <textarea class="form-control @error('description') is-invalid @enderror" 
                                              id="description" name="description" rows="4" required 
                                              placeholder="Detailed description of the issue">{{ old('description', $siteIssue->description) }}</textarea>
                                    @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Affected Areas -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="mb-3">
                                    <label for="affected_areas" class="form-label">Affected Areas</label>
                                    <textarea class="form-control @error('affected_areas') is-invalid @enderror" 
                                              id="affected_areas" name="affected_areas" rows="3" 
                                              placeholder="Areas or systems affected by this issue">{{ old('affected_areas', $siteIssue->affected_areas) }}</textarea>
                                    @error('affected_areas')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Immediate Actions Taken -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="mb-3">
                                    <label for="immediate_actions_taken" class="form-label">Immediate Actions Taken</label>
                                    <textarea class="form-control @error('immediate_actions_taken') is-invalid @enderror" 
                                              id="immediate_actions_taken" name="immediate_actions_taken" rows="3" 
                                              placeholder="What immediate actions were taken to address this issue?">{{ old('immediate_actions_taken', $siteIssue->immediate_actions_taken) }}</textarea>
                                    @error('immediate_actions_taken')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Suggested Solutions -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="mb-3">
                                    <label for="suggested_solutions" class="form-label">Suggested Solutions</label>
                                    <textarea class="form-control @error('suggested_solutions') is-invalid @enderror" 
                                              id="suggested_solutions" name="suggested_solutions" rows="3" 
                                              placeholder="Proposed solutions or recommendations">{{ old('suggested_solutions', $siteIssue->suggested_solutions) }}</textarea>
                                    @error('suggested_solutions')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Admin Notes -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="mb-3">
                                    <label for="admin_notes" class="form-label">Admin Notes</label>
                                    <textarea class="form-control @error('admin_notes') is-invalid @enderror" 
                                              id="admin_notes" name="admin_notes" rows="3" 
                                              placeholder="Internal notes (not visible to reporter)">{{ old('admin_notes', $siteIssue->admin_notes) }}</textarea>
                                    @error('admin_notes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">These notes are only visible to admins and project managers.</small>
                                </div>
                            </div>
                        </div>

                        <!-- Resolution Description (if resolved) -->
                        <div class="row mb-4" id="resolutionSection" style="{{ $siteIssue->status === 'resolved' ? '' : 'display: none;' }}">
                            <div class="col-12">
                                <div class="mb-3">
                                    <label for="resolution_description" class="form-label">Resolution Description</label>
                                    <textarea class="form-control @error('resolution_description') is-invalid @enderror" 
                                              id="resolution_description" name="resolution_description" rows="3" 
                                              placeholder="Describe how this issue was resolved">{{ old('resolution_description', $siteIssue->resolution_description) }}</textarea>
                                    @error('resolution_description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>Update Issue
                            </button>
                            <a href="{{ route('pm.site-issues.show', $siteIssue) }}" class="btn btn-secondary">
                                <i class="fas fa-times me-1"></i>Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Current Issue Info -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Current Issue Status</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <small class="text-muted">Issue ID</small>
                        <p class="fw-bold">#{{ $siteIssue->id }}</p>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted">Current Status</small>
                        <p><span class="badge bg-{{ $siteIssue->status_badge_color }}">{{ ucfirst(str_replace('_', ' ', $siteIssue->status)) }}</span></p>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted">Current Priority</small>
                        <p><span class="badge bg-{{ $siteIssue->priority_badge_color }}">{{ ucfirst($siteIssue->priority) }}</span></p>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted">Reported</small>
                        <p>{{ $siteIssue->reported_at->format('M d, Y g:i A') }}</p>
                        <small class="text-muted">{{ $siteIssue->reported_at->diffForHumans() }}</small>
                    </div>
                    @if($siteIssue->acknowledged_at)
                    <div class="mb-3">
                        <small class="text-muted">Acknowledged</small>
                        <p>{{ $siteIssue->acknowledged_at->format('M d, Y g:i A') }}</p>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Project & Task Info -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Project Information</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <small class="text-muted">Project</small>
                        <p><a href="{{ route('projects.show', $siteIssue->project) }}" class="text-decoration-none">
                            {{ $siteIssue->project->name }}
                        </a></p>
                    </div>
                    @if($siteIssue->task)
                    <div class="mb-3">
                        <small class="text-muted">Task</small>
                        <p><a href="{{ route('tasks.show', $siteIssue->task) }}" class="text-decoration-none">
                            {{ $siteIssue->task->task_name }}
                        </a></p>
                    </div>
                    @endif
                    <div class="mb-3">
                        <small class="text-muted">Reporter</small>
                        <p>{{ $siteIssue->reporter->first_name }} {{ $siteIssue->reporter->last_name }}</p>
                        <small class="text-muted">{{ ucfirst($siteIssue->reporter->role) }}</small>
                    </div>
                </div>
            </div>

            <!-- Current Photos -->
            @if($siteIssue->photos && count($siteIssue->photos) > 0)
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Current Photos ({{ count($siteIssue->photos) }})</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach($siteIssue->photos as $photo)
                        <div class="col-6 mb-2">
                            <img src="{{ Storage::url($photo) }}" class="img-fluid rounded" style="height: 80px; width: 100%; object-fit: cover;">
                        </div>
                        @endforeach
                    </div>
                    <small class="text-muted">To modify photos, please add a comment with new attachments.</small>
                </div>
            </div>
            @endif

            <!-- Quick Actions -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Quick Actions</h5>
                </div>
                <div class="card-body">
                    @if(!$siteIssue->acknowledged_at)
                    <button type="button" class="btn btn-warning btn-sm w-100 mb-2" onclick="acknowledgeAndContinue()">
                        <i class="fas fa-check me-1"></i>Acknowledge & Continue Editing
                    </button>
                    @endif
                    
                    @if($siteIssue->status !== 'resolved')
                    <button type="button" class="btn btn-success btn-sm w-100 mb-2" onclick="quickResolve()">
                        <i class="fas fa-check-circle me-1"></i>Quick Resolve
                    </button>
                    @endif
                    
                    <a href="{{ route('pm.site-issues.show', $siteIssue) }}" class="btn btn-info btn-sm w-100 mb-2">
                        <i class="fas fa-eye me-1"></i>View Details
                    </a>
                    
                    <a href="{{ route('pm.site-issues.index') }}" class="btn btn-outline-secondary btn-sm w-100">
                        <i class="fas fa-list me-1"></i>Back to List
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Resolve Modal -->
<div class="modal fade" id="quickResolveModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Quick Resolve Issue</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>This will mark the issue as resolved and save your current changes.</p>
                <div class="mb-3">
                    <label class="form-label">Resolution Description</label>
                    <textarea id="quickResolutionText" class="form-control" rows="3" required 
                              placeholder="Briefly describe how this issue was resolved..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" onclick="applyQuickResolve()">Resolve Issue</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Show/hide resolution section based on status
    const statusSelect = document.getElementById('status');
    const resolutionSection = document.getElementById('resolutionSection');
    
    statusSelect.addEventListener('change', function() {
        if (this.value === 'resolved') {
            resolutionSection.style.display = 'block';
            document.getElementById('resolution_description').required = true;
        } else {
            resolutionSection.style.display = 'none';
            document.getElementById('resolution_description').required = false;
        }
    });

    // Auto-acknowledge when assigning
    const assignedToSelect = document.getElementById('assigned_to');
    assignedToSelect.addEventListener('change', function() {
        if (this.value && statusSelect.value === 'open') {
            statusSelect.value = 'in_progress';
        }
    });

    // Priority color coding
    const prioritySelect = document.getElementById('priority');
    prioritySelect.addEventListener('change', function() {
        this.className = this.className.replace(/border-\w+/, '');
        switch(this.value) {
            case 'critical':
                this.classList.add('border-danger');
                break;
            case 'high':
                this.classList.add('border-warning');
                break;
            case 'medium':
                this.classList.add('border-info');
                break;
            case 'low':
                this.classList.add('border-success');
                break;
        }
    });
    
    // Trigger initial priority styling
    prioritySelect.dispatchEvent(new Event('change'));
});

function acknowledgeAndContinue() {
    fetch('{{ route("pm.site-issues.acknowledge", $siteIssue) }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => {
        if (response.ok) {
            // Show success message and update UI
            const alertDiv = document.createElement('div');
            alertDiv.className = 'alert alert-success alert-dismissible fade show';
            alertDiv.innerHTML = '<i class="fas fa-check me-1"></i>Issue acknowledged successfully! <button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
            document.querySelector('.container-fluid').insertBefore(alertDiv, document.querySelector('.row'));
            
            // Remove acknowledge button
            const ackButton = document.querySelector('button[onclick="acknowledgeAndContinue()"]');
            if (ackButton) {
                ackButton.remove();
            }
        } else {
            alert('Error acknowledging issue');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error acknowledging issue');
    });
}

function quickResolve() {
    new bootstrap.Modal(document.getElementById('quickResolveModal')).show();
}

function applyQuickResolve() {
    const resolutionText = document.getElementById('quickResolutionText').value;
    if (!resolutionText.trim()) {
        alert('Please provide a resolution description');
        return;
    }
    
    // Update form fields
    document.getElementById('status').value = 'resolved';
    document.getElementById('resolution_description').value = resolutionText;
    
    // Show resolution section and submit form
    document.getElementById('resolutionSection').style.display = 'block';
    document.querySelector('form').submit();
}

// Auto-save draft every 30 seconds
let autoSaveTimer;
function startAutoSave() {
    autoSaveTimer = setInterval(function() {
        const formData = new FormData(document.querySelector('form'));
        
        // Save to localStorage as draft
        const draft = {};
        for (let [key, value] of formData.entries()) {
            draft[key] = value;
        }
        localStorage.setItem('siteIssueEdit_{{ $siteIssue->id }}', JSON.stringify(draft));
        
        // Show saved indicator
        const indicator = document.getElementById('autoSaveIndicator');
        if (!indicator) {
            const saveIndicator = document.createElement('small');
            saveIndicator.id = 'autoSaveIndicator';
            saveIndicator.className = 'text-muted';
            saveIndicator.innerHTML = '<i class="fas fa-save me-1"></i>Draft saved';
            document.querySelector('h1').appendChild(saveIndicator);
            
            setTimeout(() => saveIndicator.remove(), 3000);
        }
    }, 30000);
}

// Load draft on page load
window.addEventListener('load', function() {
    const draft = localStorage.getItem('siteIssueEdit_{{ $siteIssue->id }}');
    if (draft) {
        try {
            const draftData = JSON.parse(draft);
            for (let [key, value] of Object.entries(draftData)) {
                const field = document.querySelector(`[name="${key}"]`);
                if (field && field.value === '' && value !== '') {
                    field.value = value;
                }
            }
        } catch (e) {
            console.log('Error loading draft:', e);
        }
    }
    
    startAutoSave();
});

// Clear draft on successful submit
document.querySelector('form').addEventListener('submit', function() {
    localStorage.removeItem('siteIssueEdit_{{ $siteIssue->id }}');
    clearInterval(autoSaveTimer);
});
</script>
@endpush

@push('styles')
<style>
.card {
    border: none;
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}

.form-control:focus, .form-select:focus {
    border-color: #86b7fe;
    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
}

.border-danger {
    border-color: #dc3545 !important;
}

.border-warning {
    border-color: #ffc107 !important;
}

.border-info {
    border-color: #0dcaf0 !important;
}

.border-success {
    border-color: #198754 !important;
}

.badge {
    font-size: 0.75em;
}

.alert {
    border-radius: 0.5rem;
}

.btn {
    border-radius: 0.375rem;
}

.form-control, .form-select {
    border-radius: 0.375rem;
}

#autoSaveIndicator {
    display: block;
    margin-top: 0.25rem;
    animation: fadeIn 0.3s ease-in;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

.timeline-marker.bg-success {
    background-color: #198754 !important;
}

.timeline-marker.bg-info {
    background-color: #0dcaf0 !important;
}

.timeline-marker.bg-primary {
    background-color: #0d6efd !important;
}
</style>
@endpush
@endsection