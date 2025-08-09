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
                    <li class="breadcrumb-item active">Add Comment</li>
                </ol>
            </nav>
            <h1>Add Comment to Issue</h1>
            <p class="text-muted">Add your comment or feedback to this site issue</p>
        </div>
        <div>
            <a href="{{ route('pm.site-issues.show', $siteIssue) }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i>Back to Issue
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <!-- Add Comment Form -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Add Your Comment</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('pm.site-issues.add-comment', $siteIssue) }}" enctype="multipart/form-data">
                        @csrf
                        
                        <!-- Comment Text -->
                        <div class="mb-4">
                            <label for="comment" class="form-label">Comment <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('comment') is-invalid @enderror" 
                                      id="comment" name="comment" rows="6" required 
                                      placeholder="Enter your comment, feedback, or instructions...">{{ old('comment') }}</textarea>
                            @error('comment')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">You can provide updates, ask questions, or give instructions regarding this issue.</small>
                        </div>

                        <!-- Comment Type -->
                        <div class="mb-4">
                            <label class="form-label">Comment Type</label>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="comment_type" id="public_comment" value="public" checked>
                                        <label class="form-check-label" for="public_comment">
                                            <strong>Public Comment</strong>
                                            <br><small class="text-muted">Visible to the issue reporter and all team members</small>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="comment_type" id="internal_comment" value="internal">
                                        <label class="form-check-label" for="internal_comment">
                                            <strong>Internal Comment</strong>
                                            <br><small class="text-muted">Only visible to admins and project managers</small>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <input type="hidden" name="is_internal" id="is_internal" value="0">
                        </div>

                        <!-- Quick Action Buttons -->
                        <div class="mb-4">
                            <label class="form-label">Quick Actions (Optional)</label>
                            <div class="d-flex gap-2 flex-wrap">
                                <button type="button" class="btn btn-outline-success btn-sm" onclick="addQuickText('issue_resolved')">
                                    <i class="fas fa-check-circle me-1"></i>Issue Resolved
                                </button>
                                <button type="button" class="btn btn-outline-info btn-sm" onclick="addQuickText('need_more_info')">
                                    <i class="fas fa-question-circle me-1"></i>Need More Info
                                </button>
                                <button type="button" class="btn btn-outline-warning btn-sm" onclick="addQuickText('in_progress')">
                                    <i class="fas fa-cog me-1"></i>Working on It
                                </button>
                                <button type="button" class="btn btn-outline-primary btn-sm" onclick="addQuickText('assigned_team')">
                                    <i class="fas fa-users me-1"></i>Assigned to Team
                                </button>
                                <button type="button" class="btn btn-outline-danger btn-sm" onclick="addQuickText('escalate')">
                                    <i class="fas fa-exclamation-triangle me-1"></i>Escalating Issue
                                </button>
                            </div>
                            <small class="text-muted">Click these buttons to quickly add common responses to your comment.</small>
                        </div>

                        <!-- File Attachments -->
                        <div class="mb-4">
                            <label for="attachments" class="form-label">Attachments (Optional)</label>
                            <input type="file" class="form-control @error('attachments.*') is-invalid @enderror" 
                                   id="attachments" name="attachments[]" multiple accept=".jpg,.jpeg,.png,.pdf,.doc,.docx,.txt">
                            @error('attachments.*')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">
                                You can attach photos, documents, or other files. Max 10MB per file. 
                                Supported formats: JPG, PNG, PDF, DOC, DOCX, TXT
                            </small>
                            <div id="file-preview" class="mt-2"></div>
                        </div>

                        <!-- Status Update Options -->
                        <div class="mb-4">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="card-title">Update Issue Status (Optional)</h6>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="update_status" name="update_status" value="1">
                                                <label class="form-check-label" for="update_status">
                                                    Update issue status after adding comment
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-md-6" id="status_options" style="display: none;">
                                            <select class="form-select form-select-sm" name="new_status" id="new_status">
                                                <option value="">Select new status</option>
                                                <option value="in_progress" {{ $siteIssue->status === 'open' ? 'selected' : '' }}>In Progress</option>
                                                <option value="resolved">Resolved</option>
                                                <option value="closed">Closed</option>
                                                <option value="escalated">Escalated</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-comment me-1"></i>Add Comment
                            </button>
                            <button type="button" class="btn btn-success" onclick="addCommentAndResolve()">
                                <i class="fas fa-check-circle me-1"></i>Add Comment & Mark Resolved
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
            <!-- Issue Summary -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Issue Summary</h5>
                </div>
                <div class="card-body">
                    <h6 class="fw-bold">{{ $siteIssue->issue_title }}</h6>
                    <div class="mb-3">
                        <span class="badge bg-{{ $siteIssue->priority_badge_color }} me-1">{{ ucfirst($siteIssue->priority) }}</span>
                        <span class="badge bg-{{ $siteIssue->status_badge_color }} me-1">{{ ucfirst(str_replace('_', ' ', $siteIssue->status)) }}</span>
                        <span class="badge bg-{{ $siteIssue->issue_type_badge_color }}">{{ ucfirst($siteIssue->issue_type) }}</span>
                    </div>
                    
                    <div class="mb-2">
                        <small class="text-muted">Project:</small>
                        <p class="mb-1">{{ $siteIssue->project->name }}</p>
                    </div>
                    
                    <div class="mb-2">
                        <small class="text-muted">Reporter:</small>
                        <p class="mb-1">{{ $siteIssue->reporter->first_name }} {{ $siteIssue->reporter->last_name }}</p>
                    </div>
                    
                    <div class="mb-2">
                        <small class="text-muted">Reported:</small>
                        <p class="mb-0">{{ $siteIssue->reported_at->format('M d, Y g:i A') }}</p>
                    </div>
                </div>
            </div>

            <!-- Recent Comments -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Recent Comments ({{ $siteIssue->comments->count() }})</h5>
                </div>
                <div class="card-body">
                    @if($siteIssue->comments->count() > 0)
                        @foreach($siteIssue->comments->take(3) as $comment)
                        <div class="mb-3 {{ $comment->is_internal ? 'bg-light p-2 rounded' : '' }}">
                            <div class="d-flex justify-content-between align-items-start mb-1">
                                <small class="fw-semibold">{{ $comment->user->first_name }} {{ $comment->user->last_name }}</small>
                                <small class="text-muted">{{ $comment->created_at->diffForHumans() }}</small>
                            </div>
                            <p class="mb-1 small">{{ Str::limit($comment->comment, 100) }}</p>
                            @if($comment->is_internal)
                                <span class="badge bg-warning">Internal</span>
                            @endif
                        </div>
                        @if(!$loop->last)<hr>@endif
                        @endforeach
                        
                        @if($siteIssue->comments->count() > 3)
                        <div class="text-center">
                            <a href="{{ route('pm.site-issues.show', $siteIssue) }}" class="btn btn-outline-primary btn-sm">
                                View All {{ $siteIssue->comments->count() }} Comments
                            </a>
                        </div>
                        @endif
                    @else
                        <p class="text-muted text-center">No comments yet. Be the first to comment!</p>
                    @endif
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Quick Actions</h5>
                </div>
                <div class="card-body">
                    @if(!$siteIssue->acknowledged_at)
                    <button type="button" class="btn btn-warning btn-sm w-100 mb-2" onclick="acknowledgeIssue()">
                        <i class="fas fa-check me-1"></i>Acknowledge Issue
                    </button>
                    @endif
                    
                    @if(!$siteIssue->assignedTo)
                    <button type="button" class="btn btn-info btn-sm w-100 mb-2" data-bs-toggle="modal" data-bs-target="#assignModal">
                        <i class="fas fa-user-plus me-1"></i>Assign Issue
                    </button>
                    @endif
                    
                    <a href="{{ route('pm.site-issues.edit', $siteIssue) }}" class="btn btn-primary btn-sm w-100 mb-2">
                        <i class="fas fa-edit me-1"></i>Edit Issue Details
                    </a>
                    
                    <a href="{{ route('pm.site-issues.show', $siteIssue) }}" class="btn btn-outline-secondary btn-sm w-100">
                        <i class="fas fa-eye me-1"></i>View Full Details
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Assign Modal -->
<div class="modal fade" id="assignModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('pm.site-issues.assign', $siteIssue) }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Assign Issue</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Assign To</label>
                        <select name="assigned_to" class="form-select" required>
                            <option value="">Select User</option>
                            @foreach($assignableUsers ?? [] as $user)
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

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle comment type change
    document.querySelectorAll('input[name="comment_type"]').forEach(radio => {
        radio.addEventListener('change', function() {
            document.getElementById('is_internal').value = this.value === 'internal' ? '1' : '0';
        });
    });

    // Handle status update checkbox
    document.getElementById('update_status').addEventListener('change', function() {
        const statusOptions = document.getElementById('status_options');
        if (this.checked) {
            statusOptions.style.display = 'block';
        } else {
            statusOptions.style.display = 'none';
        }
    });

    // File preview
    document.getElementById('attachments').addEventListener('change', function() {
        const preview = document.getElementById('file-preview');
        preview.innerHTML = '';
        
        Array.from(this.files).forEach(file => {
            const fileDiv = document.createElement('div');
            fileDiv.className = 'alert alert-info alert-dismissible fade show py-2';
            fileDiv.innerHTML = `
                <i class="fas fa-file me-1"></i>${file.name} (${(file.size / 1024).toFixed(1)} KB)
                <button type="button" class="btn-close btn-close-sm" onclick="removeFile(this, '${file.name}')"></button>
            `;
            preview.appendChild(fileDiv);
        });
    });
});

// Quick text templates
const quickTexts = {
    issue_resolved: "This issue has been resolved. The necessary actions have been taken and the problem should no longer occur.",
    need_more_info: "Thank you for reporting this issue. We need additional information to proceed. Please provide more details about when this occurred and any steps that led to the problem.",
    in_progress: "We have acknowledged this issue and are currently working on a solution. We will update you on our progress.",
    assigned_team: "This issue has been assigned to the appropriate team for resolution. They will review and take necessary action.",
    escalate: "This issue requires escalation due to its severity/impact. We are involving additional resources to ensure quick resolution."
};

function addQuickText(type) {
    const commentField = document.getElementById('comment');
    const currentText = commentField.value.trim();
    const quickText = quickTexts[type];
    
    if (currentText) {
        commentField.value = currentText + '\n\n' + quickText;
    } else {
        commentField.value = quickText;
    }
    
    // Focus and scroll to end
    commentField.focus();
    commentField.setSelectionRange(commentField.value.length, commentField.value.length);
}

function addCommentAndResolve() {
    // Set status update options
    document.getElementById('update_status').checked = true;
    document.getElementById('status_options').style.display = 'block';
    document.getElementById('new_status').value = 'resolved';
    
    // Add resolution text if comment is empty
    const commentField = document.getElementById('comment');
    if (!commentField.value.trim()) {
        commentField.value = 'This issue has been resolved and is now closed.';
    }
    
    // Submit form
    document.querySelector('form').submit();
}

function acknowledgeIssue() {
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
            // Remove acknowledge button
            const ackButton = document.querySelector('button[onclick="acknowledgeIssue()"]');
            if (ackButton) {
                ackButton.remove();
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

function removeFile(button, fileName) {
    button.closest('.alert').remove();
    // Note: This is just visual removal. File input reset would be needed for actual removal
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

// Auto-save comment to localStorage
let autoSaveTimer;
document.getElementById('comment').addEventListener('input', function() {
    clearTimeout(autoSaveTimer);
    autoSaveTimer = setTimeout(() => {
        localStorage.setItem('siteIssueComment_{{ $siteIssue->id }}', this.value);
    }, 1000);
});

// Load saved comment on page load
window.addEventListener('load', function() {
    const savedComment = localStorage.getItem('siteIssueComment_{{ $siteIssue->id }}');
    if (savedComment && !document.getElementById('comment').value) {
        document.getElementById('comment').value = savedComment;
    }
});

// Clear saved comment on form submit
document.querySelector('form').addEventListener('submit', function() {
    localStorage.removeItem('siteIssueComment_{{ $siteIssue->id }}');
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

.position-fixed {
    position: fixed !important;
}

.btn-close-sm {
    padding: 0.25rem;
    font-size: 0.75rem;
}

.bg-light {
    background-color: #f8f9fa !important;
}

.text-decoration-none:hover {
    text-decoration: underline !important;
}
</style>
@endpush
@endsection