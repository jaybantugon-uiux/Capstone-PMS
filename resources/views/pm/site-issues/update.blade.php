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
                    <li class="breadcrumb-item active">Update Status</li>
                </ol>
            </nav>
            <h1>Update Issue Status</h1>
            <p class="text-muted">Change the status and management details of this site issue</p>
        </div>
        <div>
            <a href="{{ route('pm.site-issues.show', $siteIssue) }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i>Back to Issue
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <!-- Status Update Form -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Update Issue Status & Details</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('pm.site-issues.update', $siteIssue) }}">
                        @csrf
                        @method('PUT')
                        
                        <!-- Current Status Display -->
                        <div class="alert alert-info mb-4">
                            <div class="row">
                                <div class="col-md-4">
                                    <strong>Current Status:</strong><br>
                                    <span class="badge bg-{{ $siteIssue->status_badge_color }} fs-6">{{ ucfirst(str_replace('_', ' ', $siteIssue->status)) }}</span>
                                </div>
                                <div class="col-md-4">
                                    <strong>Current Priority:</strong><br>
                                    <span class="badge bg-{{ $siteIssue->priority_badge_color }} fs-6">{{ ucfirst($siteIssue->priority) }}</span>
                                </div>
                                <div class="col-md-4">
                                    <strong>Assigned To:</strong><br>
                                    @if($siteIssue->assignedTo)
                                        {{ $siteIssue->assignedTo->first_name }} {{ $siteIssue->assignedTo->last_name }}
                                    @else
                                        <span class="text-muted">Unassigned</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Status Update Section -->
                        <div class="row mb-4">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="status" class="form-label">New Status <span class="text-danger">*</span></label>
                                    <select class="form-select @error('status') is-invalid @enderror" 
                                            id="status" name="status" required>
                                        <option value="open" {{ $siteIssue->status === 'open' ? 'selected' : '' }}>Open</option>
                                        <option value="in_progress" {{ $siteIssue->status === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                        <option value="resolved" {{ $siteIssue->status === 'resolved' ? 'selected' : '' }}>Resolved</option>
                                        <option value="closed" {{ $siteIssue->status === 'closed' ? 'selected' : '' }}>Closed</option>
                                        <option value="escalated" {{ $siteIssue->status === 'escalated' ? 'selected' : '' }}>Escalated</option>
                                    </select>
                                    @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="priority" class="form-label">Priority</label>
                                    <select class="form-select @error('priority') is-invalid @enderror" 
                                            id="priority" name="priority">
                                        <option value="low" {{ $siteIssue->priority === 'low' ? 'selected' : '' }}>Low</option>
                                        <option value="medium" {{ $siteIssue->priority === 'medium' ? 'selected' : '' }}>Medium</option>
                                        <option value="high" {{ $siteIssue->priority === 'high' ? 'selected' : '' }}>High</option>
                                        <option value="critical" {{ $siteIssue->priority === 'critical' ? 'selected' : '' }}>Critical</option>
                                    </select>
                                    @error('priority')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="assigned_to" class="form-label">Assign To</label>
                                    <select class="form-select @error('assigned_to') is-invalid @enderror" 
                                            id="assigned_to" name="assigned_to">
                                        <option value="">Unassigned</option>
                                        @foreach($assignableUsers ?? [] as $user)
                                        <option value="{{ $user->id }}" 
                                                {{ $siteIssue->assigned_to == $user->id ? 'selected' : '' }}>
                                            {{ $user->first_name }} {{ $user->last_name }} ({{ ucfirst($user->role) }})
                                        </option>
                                        @endforeach
                                    </select>
                                    @error('assigned_to')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Resolution Section (shown only when status is resolved) -->
                        <div class="card mb-4" id="resolutionSection" style="{{ $siteIssue->status === 'resolved' ? '' : 'display: none;' }}">
                            <div class="card-header bg-success text-white">
                                <h6 class="mb-0"><i class="fas fa-check-circle me-1"></i>Resolution Details</h6>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label for="resolution_description" class="form-label">Resolution Description <span class="text-danger">*</span></label>
                                    <textarea class="form-control @error('resolution_description') is-invalid @enderror" 
                                              id="resolution_description" name="resolution_description" rows="4" 
                                              placeholder="Describe how this issue was resolved, what actions were taken, and any preventive measures implemented...">{{ old('resolution_description', $siteIssue->resolution_description) }}</textarea>
                                    @error('resolution_description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <!-- Quick Resolution Templates -->
                <div class="mb-3">
                    <label class="form-label">Quick Resolution Templates</label>
                    <div class="d-flex gap-2 flex-wrap">
                        <button type="button" class="btn btn-outline-success btn-sm" onclick="addResolutionTemplate('equipment_fixed')">
                            <i class="fas fa-wrench me-1"></i>Equipment Fixed
                        </button>
                        <button type="button" class="btn btn-outline-success btn-sm" onclick="addResolutionTemplate('safety_resolved')">
                            <i class="fas fa-shield-alt me-1"></i>Safety Issue Resolved
                        </button>
                        <button type="button" class="btn btn-outline-success btn-sm" onclick="addResolutionTemplate('training_provided')">
                            <i class="fas fa-graduation-cap me-1"></i>Training Provided
                        </button>
                        <button type="button" class="btn btn-outline-success btn-sm" onclick="addResolutionTemplate('policy_updated')">
                            <i class="fas fa-file-contract me-1"></i>Policy Updated
                        </button>
                        <button type="button" class="btn btn-outline-success btn-sm" onclick="addResolutionTemplate('contractor_contacted')">
                            <i class="fas fa-phone me-1"></i>Contractor Contacted
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Admin Notes Section -->
        <div class="mb-4">
            <label for="admin_notes" class="form-label">Management Notes (Internal)</label>
            <textarea class="form-control @error('admin_notes') is-invalid @enderror" 
                      id="admin_notes" name="admin_notes" rows="3" 
                      placeholder="Add internal notes about this issue (only visible to admins and project managers)...">{{ old('admin_notes', $siteIssue->admin_notes) }}</textarea>
            @error('admin_notes')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
            <small class="text-muted">These notes are only visible to admins and project managers.</small>
        </div>

        <!-- Notification Options -->
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-bell me-1"></i>Notification Options</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="notify_reporter" name="notify_reporter" value="1" checked>
                            <label class="form-check-label" for="notify_reporter">
                                Notify Issue Reporter
                            </label>
                        </div>
                        <small class="text-muted">Send notification to {{ $siteIssue->reporter->first_name }} {{ $siteIssue->reporter->last_name }}</small>
                    </div>
                    <div class="col-md-6">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="notify_assigned" name="notify_assigned" value="1" checked>
                            <label class="form-check-label" for="notify_assigned">
                                Notify Assigned Person
                            </label>
                        </div>
                        <small class="text-muted">Send notification to assigned team member (if any)</small>
                    </div>
                </div>
                <div class="row mt-2">
                    <div class="col-md-6">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="notify_team" name="notify_team" value="1">
                            <label class="form-check-label" for="notify_team">
                                Notify Project Team
                            </label>
                        </div>
                        <small class="text-muted">Send notification to all team members in this project</small>
                    </div>
                    <div class="col-md-6">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="add_comment" name="add_comment" value="1">
                            <label class="form-check-label" for="add_comment">
                                Add Status Update Comment
                            </label>
                        </div>
                        <small class="text-muted">Automatically add a comment describing the status change</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Custom Message for Notifications -->
        <div class="mb-4" id="customMessageSection" style="display: none;">
            <label for="custom_message" class="form-label">Custom Message for Notifications</label>
            <textarea class="form-control" id="custom_message" name="custom_message" rows="3" 
                      placeholder="Add a custom message that will be included in the notifications..."></textarea>
            <small class="text-muted">This message will be included in all notifications sent.</small>
        </div>

        <!-- Form Actions -->
        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save me-1"></i>Update Issue Status
            </button>
            <button type="button" class="btn btn-success" onclick="quickResolve()">
                <i class="fas fa-check-circle me-1"></i>Quick Resolve
            </button>
            <button type="button" class="btn btn-warning" onclick="quickAcknowledge()" 
                    {{ $siteIssue->acknowledged_at ? 'disabled' : '' }}>
                <i class="fas fa-check me-1"></i>{{ $siteIssue->acknowledged_at ? 'Already Acknowledged' : 'Acknowledge' }}
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
    <!-- Issue Information -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Issue Information</h5>
        </div>
        <div class="card-body">
            <h6 class="fw-bold">{{ $siteIssue->issue_title }}</h6>
            
            <div class="mb-3">
                <small class="text-muted">Issue ID:</small>
                <p class="mb-1">#{{ $siteIssue->id }}</p>
            </div>
            
            <div class="mb-3">
                <small class="text-muted">Project:</small>
                <p class="mb-1">
                    <a href="{{ route('projects.show', $siteIssue->project) }}" class="text-decoration-none">
                        {{ $siteIssue->project->name }}
                    </a>
                </p>
            </div>
            
            @if($siteIssue->task)
            <div class="mb-3">
                <small class="text-muted">Related Task:</small>
                <p class="mb-1">
                    <a href="{{ route('tasks.show', $siteIssue->task) }}" class="text-decoration-none">
                        {{ $siteIssue->task->task_name }}
                    </a>
                </p>
            </div>
            @endif
            
            <div class="mb-3">
                <small class="text-muted">Issue Type:</small>
                <p class="mb-1">
                    <span class="badge bg-{{ $siteIssue->issue_type_badge_color }}">{{ ucfirst($siteIssue->issue_type) }}</span>
                </p>
            </div>
            
            @if($siteIssue->location)
            <div class="mb-3">
                <small class="text-muted">Location:</small>
                <p class="mb-1"><i class="fas fa-map-marker-alt text-danger me-1"></i>{{ $siteIssue->location }}</p>
            </div>
            @endif
            
            <div class="mb-3">
                <small class="text-muted">Reported:</small>
                <p class="mb-1">{{ $siteIssue->reported_at->format('M d, Y g:i A') }}</p>
                <small class="text-muted">{{ $siteIssue->reported_at->diffForHumans() }}</small>
            </div>
            
            @if($siteIssue->acknowledged_at)
            <div class="mb-3">
                <small class="text-muted">Acknowledged:</small>
                <p class="mb-1">{{ $siteIssue->acknowledged_at->format('M d, Y g:i A') }}</p>
                @if($siteIssue->acknowledgedBy)
                <small class="text-muted">by {{ $siteIssue->acknowledgedBy->first_name }} {{ $siteIssue->acknowledgedBy->last_name }}</small>
                @endif
            </div>
            @endif
        </div>
    </div>

    <!-- People Involved -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">People Involved</h5>
        </div>
        <div class="card-body">
            <!-- Reporter -->
            <div class="mb-3">
                <small class="text-muted">Reporter:</small>
                <div class="d-flex align-items-center mt-1">
                    <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center text-white me-2" style="width: 32px; height: 32px;">
                        {{ substr($siteIssue->reporter->first_name, 0, 1) }}{{ substr($siteIssue->reporter->last_name, 0, 1) }}
                    </div>
                    <div>
                        <div class="fw-semibold">{{ $siteIssue->reporter->first_name }} {{ $siteIssue->reporter->last_name }}</div>
                        <small class="text-muted">{{ ucfirst($siteIssue->reporter->role) }}</small>
                    </div>
                </div>
            </div>

            <!-- Assigned To -->
            @if($siteIssue->assignedTo)
            <div class="mb-3">
                <small class="text-muted">Assigned To:</small>
                <div class="d-flex align-items-center mt-1">
                    <div class="bg-success rounded-circle d-flex align-items-center justify-content-center text-white me-2" style="width: 32px; height: 32px;">
                        {{ substr($siteIssue->assignedTo->first_name, 0, 1) }}{{ substr($siteIssue->assignedTo->last_name, 0, 1) }}
                    </div>
                    <div>
                        <div class="fw-semibold">{{ $siteIssue->assignedTo->first_name }} {{ $siteIssue->assignedTo->last_name }}</div>
                        <small class="text-muted">{{ ucfirst($siteIssue->assignedTo->role) }}</small>
                    </div>
                </div>
            </div>
            @else
            <div class="alert alert-warning py-2">
                <small><i class="fas fa-user-plus me-1"></i>No one assigned to this issue yet.</small>
            </div>
            @endif
        </div>
    </div>

    <!-- Status History -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Status History</h5>
        </div>
        <div class="card-body">
            <div class="timeline">
                <!-- Reported -->
                <div class="timeline-item">
                    <div class="timeline-marker bg-primary"></div>
                    <div class="timeline-content">
                        <h6 class="mb-1">Issue Reported</h6>
                        <p class="mb-0 text-muted">{{ $siteIssue->reported_at->format('M d, Y g:i A') }}</p>
                        <small class="text-muted">by {{ $siteIssue->reporter->first_name }} {{ $siteIssue->reporter->last_name }}</small>
                    </div>
                </div>

                <!-- Acknowledged -->
                @if($siteIssue->acknowledged_at)
                <div class="timeline-item">
                    <div class="timeline-marker bg-info"></div>
                    <div class="timeline-content">
                        <h6 class="mb-1">Acknowledged</h6>
                        <p class="mb-0 text-muted">{{ $siteIssue->acknowledged_at->format('M d, Y g:i A') }}</p>
                        @if($siteIssue->acknowledgedBy)
                        <small class="text-muted">by {{ $siteIssue->acknowledgedBy->first_name }} {{ $siteIssue->acknowledgedBy->last_name }}</small>
                        @endif
                    </div>
                </div>
                @endif

                <!-- Assigned -->
                @if($siteIssue->assignedTo && $siteIssue->assigned_at)
                <div class="timeline-item">
                    <div class="timeline-marker bg-warning"></div>
                    <div class="timeline-content">
                        <h6 class="mb-1">Assigned</h6>
                        <p class="mb-0 text-muted">{{ $siteIssue->assigned_at->format('M d, Y g:i A') }}</p>
                        <small class="text-muted">to {{ $siteIssue->assignedTo->first_name }} {{ $siteIssue->assignedTo->last_name }}</small>
                    </div>
                </div>
                @endif

                <!-- Resolved -->
                @if($siteIssue->resolved_at)
                <div class="timeline-item">
                    <div class="timeline-marker bg-success"></div>
                    <div class="timeline-content">
                        <h6 class="mb-1">Resolved</h6>
                        <p class="mb-0 text-muted">{{ $siteIssue->resolved_at->format('M d, Y g:i A') }}</p>
                        @if($siteIssue->resolvedBy)
                        <small class="text-muted">by {{ $siteIssue->resolvedBy->first_name }} {{ $siteIssue->resolvedBy->last_name }}</small>
                        @endif
                    </div>
                </div>
                @endif
            </div>
        </div>
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
    const resolutionDescription = document.getElementById('resolution_description');
    
    statusSelect.addEventListener('change', function() {
        if (this.value === 'resolved') {
            resolutionSection.style.display = 'block';
            resolutionDescription.required = true;
        } else {
            resolutionSection.style.display = 'none';
            resolutionDescription.required = false;
        }
    });

    // Show custom message section when notifications are enabled
    const notificationCheckboxes = document.querySelectorAll('input[name^="notify_"]');
    const customMessageSection = document.getElementById('customMessageSection');
    
    function toggleCustomMessage() {
        const anyChecked = Array.from(notificationCheckboxes).some(cb => cb.checked);
        if (anyChecked) {
            customMessageSection.style.display = 'block';
        } else {
            customMessageSection.style.display = 'none';
        }
    }
    
    notificationCheckboxes.forEach(cb => {
        cb.addEventListener('change', toggleCustomMessage);
    });
    
    // Initial check
    toggleCustomMessage();

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

// Resolution templates
const resolutionTemplates = {
    equipment_fixed: "The equipment issue has been identified and repaired. All necessary parts have been replaced and the equipment is now functioning properly. Preventive maintenance schedule has been updated to prevent similar issues.",
    safety_resolved: "The safety concern has been addressed through immediate corrective actions. Safety protocols have been reviewed and reinforced with the team. Additional safety measures have been implemented as necessary.",
    training_provided: "Additional training has been provided to the relevant personnel to address the knowledge gap that led to this issue. Training materials have been updated and competency assessments completed.",
    policy_updated: "Company policies and procedures have been reviewed and updated to address this issue. All affected personnel have been notified of the policy changes and provided with updated documentation.",
    contractor_contacted: "The contractor responsible has been contacted and corrective actions have been implemented. Work quality has been verified and meets project standards. Contractor performance has been documented."
};

function addResolutionTemplate(type) {
    const resolutionField = document.getElementById('resolution_description');
    const currentText = resolutionField.value.trim();
    const template = resolutionTemplates[type];
    
    if (currentText) {
        resolutionField.value = currentText + '\n\n' + template;
    } else {
        resolutionField.value = template;
    }
    
    // Focus and scroll to end
    resolutionField.focus();
    resolutionField.setSelectionRange(resolutionField.value.length, resolutionField.value.length);
}

function quickResolve() {
    // Set status to resolved
    document.getElementById('status').value = 'resolved';
    document.getElementById('status').dispatchEvent(new Event('change'));
    
    // Add default resolution if none exists
    const resolutionField = document.getElementById('resolution_description');
    if (!resolutionField.value.trim()) {
        resolutionField.value = 'This issue has been resolved and corrective actions have been taken to prevent recurrence.';
    }
    
    // Enable notifications
    document.getElementById('notify_reporter').checked = true;
    document.getElementById('add_comment').checked = true;
    
    // Focus on resolution description
    resolutionField.focus();
}

function quickAcknowledge() {
    fetch('{{ route("pm.site-issues.acknowledge", $siteIssue) }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => {
        if (response.ok) {
            showNotification('Issue acknowledged successfully', 'success');
            // Update status to in_progress if currently open
            if (document.getElementById('status').value === 'open') {
                document.getElementById('status').value = 'in_progress';
            }
            // Disable acknowledge button
            const ackButton = document.querySelector('button[onclick="quickAcknowledge()"]');
            if (ackButton) {
                ackButton.disabled = true;
                ackButton.innerHTML = '<i class="fas fa-check me-1"></i>Already Acknowledged';
            }
        } else {
            showNotification('Error acknowledging issue', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error acknowledging issue', 'error');
    });
}

function showNotification(message, type) {
    const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
    const icon = type === 'success' ? 'check-circle' : 'exclamation-triangle';
    
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert ${alertClass} alert-dismissible fade show position-fixed`;
    alertDiv.style.cssText = 'top: 20px; right: 20px; z-index: 1050; min-width: 300px;';
    alertDiv.innerHTML = `
        <i class="fas fa-${icon} me-2"></i>${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(alertDiv);
    
    setTimeout(() => {
        alertDiv.remove();
    }, 5000);
}

// Auto-save form to localStorage
function autoSave() {
    const formData = {
        status: document.getElementById('status').value,
        priority: document.getElementById('priority').value,
        assigned_to: document.getElementById('assigned_to').value,
        resolution_description: document.getElementById('resolution_description').value,
        admin_notes: document.getElementById('admin_notes').value,
        custom_message: document.getElementById('custom_message').value
    };
    
    localStorage.setItem('siteIssueUpdate_{{ $siteIssue->id }}', JSON.stringify(formData));
}

// Auto-save every 30 seconds
setInterval(autoSave, 30000);

// Load saved data on page load
window.addEventListener('load', function() {
    const savedData = localStorage.getItem('siteIssueUpdate_{{ $siteIssue->id }}');
    if (savedData) {
        try {
            const data = JSON.parse(savedData);
            Object.keys(data).forEach(key => {
                const field = document.getElementById(key);
                if (field && data[key] && !field.value) {
                    field.value = data[key];
                }
            });
        } catch (e) {
            console.log('Error loading saved data:', e);
        }
    }
});

// Clear saved data on form submit
document.querySelector('form').addEventListener('submit', function() {
    localStorage.removeItem('siteIssueUpdate_{{ $siteIssue->id }}');
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

.fs-6 {
    font-size: 1rem !important;
}

.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 15px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #dee2e6;
}

.timeline-item {
    position: relative;
    margin-bottom: 25px;
}

.timeline-marker {
    position: absolute;
    left: -22px;
    top: 0;
    width: 15px;
    height: 15px;
    border-radius: 50%;
    border: 3px solid #fff;
    box-shadow: 0 0 0 3px #dee2e6;
}

.timeline-content {
    padding-left: 15px;
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

.position-fixed {
    position: fixed !important;
}

.bg-light {
    background-color: #f8f9fa !important;
}
</style>
@endpush
@endsection