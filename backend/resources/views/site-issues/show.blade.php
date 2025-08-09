
@extends('app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h2">{{ $siteIssue->issue_title }}</h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('sc.site-issues.index') }}">Site Issues</a></li>
                            <li class="breadcrumb-item active">{{ $siteIssue->issue_title }}</li>
                        </ol>
                    </nav>
                </div>
                <div class="d-flex gap-2">
                    @if(!in_array($siteIssue->status, ['resolved', 'closed']))
                        <a href="{{ route('sc.site-issues.edit', $siteIssue) }}" class="btn btn-outline-primary">
                            <i class="fas fa-edit me-1"></i> Edit Issue
                        </a>
                    @endif
                    <a href="{{ route('sc.site-issues.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Back to Issues
                    </a>
                </div>
            </div>

            <!-- Status Alert -->
            @if($siteIssue->priority === 'critical' && !in_array($siteIssue->status, ['resolved', 'closed']))
                <div class="alert alert-danger" role="alert">
                    <h5 class="alert-heading">
                        <i class="fas fa-exclamation-triangle me-2"></i>Critical Issue
                    </h5>
                    <p class="mb-0">This is a critical priority issue that requires immediate attention.</p>
                    @if($siteIssue->is_overdue_for_acknowledgment)
                        <hr>
                        <p class="mb-0 fw-bold">⚠️ This issue is overdue for acknowledgment by administration.</p>
                    @endif
                </div>
            @endif

            <div class="row">
                <!-- Main Content -->
                <div class="col-lg-8">
                    <!-- Issue Details Card -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">Issue Details</h5>
                        </div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <strong>Status:</strong>
                                    <span class="badge bg-{{ $siteIssue->status_badge_color }} ms-2">
                                        {{ $siteIssue->formatted_status }}
                                    </span>
                                </div>
                                <div class="col-md-6">
                                    <strong>Priority:</strong>
                                    <span class="badge bg-{{ $siteIssue->priority_badge_color }} ms-2">
                                        {{ $siteIssue->formatted_priority }}
                                    </span>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <strong>Type:</strong>
                                    <span class="badge bg-{{ $siteIssue->issue_type_badge_color }} ms-2">
                                        {{ $siteIssue->formatted_issue_type }}
                                    </span>
                                </div>
                                <div class="col-md-6">
                                    <strong>Reported:</strong>
                                    <span class="ms-2">{{ $siteIssue->formatted_reported_at }}</span>
                                    <br><small class="text-muted">{{ $siteIssue->age }}</small>
                                </div>
                            </div>

                            @if($siteIssue->location)
                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <strong>Location:</strong>
                                    <span class="ms-2">
                                        <i class="fas fa-map-marker-alt me-1"></i>{{ $siteIssue->location }}
                                    </span>
                                </div>
                            </div>
                            @endif

                            @if($siteIssue->estimated_cost)
                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <strong>Estimated Cost:</strong>
                                    <span class="ms-2 text-success fw-bold">{{ $siteIssue->formatted_estimated_cost }}</span>
                                </div>
                            </div>
                            @endif

                            <div class="mb-3">
                                <strong>Description:</strong>
                                <div class="mt-2 p-3 bg-light rounded">
                                    {{ $siteIssue->description }}
                                </div>
                            </div>

                            @if($siteIssue->affected_areas)
                            <div class="mb-3">
                                <strong>Affected Areas:</strong>
                                <div class="mt-2 p-3 bg-light rounded">
                                    {{ $siteIssue->affected_areas }}
                                </div>
                            </div>
                            @endif

                            @if($siteIssue->immediate_actions_taken)
                            <div class="mb-3">
                                <strong>Immediate Actions Taken:</strong>
                                <div class="mt-2 p-3 bg-success bg-opacity-10 rounded">
                                    {{ $siteIssue->immediate_actions_taken }}
                                </div>
                            </div>
                            @endif

                            @if($siteIssue->suggested_solutions)
                            <div class="mb-3">
                                <strong>Suggested Solutions:</strong>
                                <div class="mt-2 p-3 bg-info bg-opacity-10 rounded">
                                    {{ $siteIssue->suggested_solutions }}
                                </div>
                            </div>
                            @endif

                            <!-- Photos -->
                            @if($siteIssue->photos && count($siteIssue->photos) > 0)
                            <div class="mb-3">
                                <strong>Photos:</strong>
                                <div class="row mt-2">
                                    @foreach($siteIssue->photos as $photo)
                                        <div class="col-md-4 mb-2">
                                            <a href="{{ Storage::url($photo) }}" target="_blank">
                                                <img src="{{ Storage::url($photo) }}" 
                                                     class="img-thumbnail" 
                                                     style="height: 150px; width: 100%; object-fit: cover;">
                                            </a>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                            @endif

                            <!-- Attachments -->
                            @if($siteIssue->attachments && count($siteIssue->attachments) > 0)
                            <div class="mb-3">
                                <strong>Attachments:</strong>
                                <div class="mt-2">
                                    @foreach($siteIssue->attachments as $attachment)
                                        <div class="d-flex align-items-center mb-1">
                                            <i class="fas fa-file me-2"></i>
                                            <a href="{{ Storage::url($attachment['path']) }}" target="_blank">
                                                {{ $attachment['original_name'] }}
                                            </a>
                                            <small class="text-muted ms-2">
                                                ({{ number_format($attachment['size'] / 1024, 1) }} KB)
                                            </small>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>

                    <!-- Admin Response -->
                    @if($siteIssue->admin_notes || $siteIssue->resolution_description)
                    <div class="card mb-4">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">
                                <i class="fas fa-user-tie me-2"></i>Management Response
                            </h5>
                        </div>
                        <div class="card-body">
                            @if($siteIssue->admin_notes)
                            <div class="mb-3">
                                <strong>Admin Notes:</strong>
                                <div class="mt-2 p-3 bg-light rounded">
                                    {{ $siteIssue->admin_notes }}
                                </div>
                            </div>
                            @endif

                            @if($siteIssue->resolution_description)
                            <div class="mb-3">
                                <strong>Resolution Details:</strong>
                                <div class="mt-2 p-3 bg-success bg-opacity-10 rounded">
                                    {{ $siteIssue->resolution_description }}
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                    @endif

                    <!-- Comments Section -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-comments me-2"></i>Comments ({{ $siteIssue->comments->count() }})
                            </h5>
                        </div>
                        <div class="card-body">
                            @if($siteIssue->comments->count() > 0)
                                @foreach($siteIssue->comments as $comment)
                                    <div class="d-flex mb-3 {{ $comment->is_internal ? 'opacity-75' : '' }}">
                                        <div class="flex-shrink-0">
                                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                                {{ strtoupper(substr($comment->user->first_name ?? $comment->user->username, 0, 1)) }}
                                            </div>
                                        </div>
                                        <div class="flex-grow-1 ms-3">
                                            <div class="d-flex justify-content-between">
                                                <div>
                                                    <strong>{{ $comment->user->first_name }} {{ $comment->user->last_name }}</strong>
                                                    <small class="text-muted ms-2">{{ $comment->formatted_created_at }}</small>
                                                    @if($comment->is_internal)
                                                        <span class="badge bg-warning ms-2">Internal</span>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="mt-2 p-3 bg-light rounded">
                                                {{ $comment->comment }}
                                            </div>
                                            @if($comment->attachments && count($comment->attachments) > 0)
                                                <div class="mt-2">
                                                    <small class="text-muted">Attachments:</small>
                                                    @foreach($comment->attachments as $attachment)
                                                        <div class="d-flex align-items-center mt-1">
                                                            <i class="fas fa-paperclip me-1"></i>
                                                            <a href="{{ Storage::url($attachment['path']) }}" target="_blank" class="small">
                                                                {{ $attachment['original_name'] }}
                                                            </a>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            @else
                                <p class="text-muted text-center">No comments yet.</p>
                            @endif

                            <!-- Add Comment Form -->
                            <hr>
                            <form action="{{ route('sc.site-issues.add-comment', $siteIssue) }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <div class="mb-3">
                                    <label for="comment" class="form-label">Add Comment</label>
                                    <textarea name="comment" class="form-control" rows="3" required placeholder="Add your comment here..."></textarea>
                                </div>
                                <div class="mb-3">
                                    <label for="attachments" class="form-label">Attachments (optional)</label>
                                    <input type="file" name="attachments[]" class="form-control" multiple accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                                    <small class="text-muted">Max 10MB per file. Supported: PDF, DOC, DOCX, JPG, PNG</small>
                                </div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-paper-plane me-1"></i> Add Comment
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="col-lg-4">
                    <!-- Quick Info -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h6 class="mb-0">Quick Information</h6>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <strong>Project:</strong>
                                <br><a href="{{ route('projects.show', $siteIssue->project) }}" class="text-decoration-none">
                                    {{ $siteIssue->project->name }}
                                </a>
                            </div>

                            @if($siteIssue->task)
                            <div class="mb-3">
                                <strong>Related Task:</strong>
                                <br><a href="{{ route('tasks.show', $siteIssue->task) }}" class="text-decoration-none">
                                    {{ $siteIssue->task->task_name }}
                                </a>
                            </div>
                            @endif

                            <div class="mb-3">
                                <strong>Reporter:</strong>
                                <br>{{ $siteIssue->reporter->first_name }} {{ $siteIssue->reporter->last_name }}
                                <br><small class="text-muted">{{ $siteIssue->reporter->email }}</small>
                            </div>

                            @if($siteIssue->assignedTo)
                            <div class="mb-3">
                                <strong>Assigned To:</strong>
                                <br>{{ $siteIssue->assignedTo->first_name }} {{ $siteIssue->assignedTo->last_name }}
                                <br><small class="text-muted">{{ $siteIssue->assignedTo->email }}</small>
                            </div>
                            @endif

                            @if($siteIssue->acknowledged_at)
                            <div class="mb-3">
                                <strong>Acknowledged:</strong>
                                <br><small class="text-success">
                                    <i class="fas fa-check me-1"></i>{{ $siteIssue->formatted_acknowledged_at }}
                                </small>
                                @if($siteIssue->acknowledgedBy)
                                    <br><small class="text-muted">by {{ $siteIssue->acknowledgedBy->first_name }}</small>
                                @endif
                            </div>
                            @endif

                            @if($siteIssue->resolved_at)
                            <div class="mb-3">
                                <strong>Resolved:</strong>
                                <br><small class="text-success">
                                    <i class="fas fa-check-circle me-1"></i>{{ $siteIssue->formatted_resolved_at }}
                                </small>
                                @if($siteIssue->resolvedBy)
                                    <br><small class="text-muted">by {{ $siteIssue->resolvedBy->first_name }}</small>
                                @endif
                            </div>
                            @endif
                        </div>
                    </div>

                    <!-- Timeline -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="fas fa-clock me-1"></i>Timeline
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="timeline">
                                <div class="timeline-item">
                                    <div class="timeline-marker bg-primary"></div>
                                    <div class="timeline-content">
                                        <strong>Issue Reported</strong>
                                        <br><small class="text-muted">{{ $siteIssue->formatted_reported_at }}</small>
                                    </div>
                                </div>

                                @if($siteIssue->acknowledged_at)
                                <div class="timeline-item">
                                    <div class="timeline-marker bg-info"></div>
                                    <div class="timeline-content">
                                        <strong>Acknowledged</strong>
                                        <br><small class="text-muted">{{ $siteIssue->formatted_acknowledged_at }}</small>
                                        @if($siteIssue->acknowledgedBy)
                                            <br><small class="text-muted">by {{ $siteIssue->acknowledgedBy->first_name }}</small>
                                        @endif
                                    </div>
                                </div>
                                @endif

                                @if($siteIssue->assignedTo && $siteIssue->status !== 'open')
                                <div class="timeline-item">
                                    <div class="timeline-marker bg-warning"></div>
                                    <div class="timeline-content">
                                        <strong>Assigned</strong>
                                        <br><small class="text-muted">to {{ $siteIssue->assignedTo->first_name }}</small>
                                    </div>
                                </div>
                                @endif

                                @if($siteIssue->status === 'in_progress')
                                <div class="timeline-item">
                                    <div class="timeline-marker bg-info"></div>
                                    <div class="timeline-content">
                                        <strong>In Progress</strong>
                                        <br><small class="text-muted">Work started</small>
                                    </div>
                                </div>
                                @endif

                                @if($siteIssue->resolved_at)
                                <div class="timeline-item">
                                    <div class="timeline-marker bg-success"></div>
                                    <div class="timeline-content">
                                        <strong>Resolved</strong>
                                        <br><small class="text-muted">{{ $siteIssue->formatted_resolved_at }}</small>
                                        @if($siteIssue->resolvedBy)
                                            <br><small class="text-muted">by {{ $siteIssue->resolvedBy->first_name }}</small>
                                        @endif
                                    </div>
                                </div>
                                @endif

                                @if($siteIssue->status === 'closed')
                                <div class="timeline-item">
                                    <div class="timeline-marker bg-secondary"></div>
                                    <div class="timeline-content">
                                        <strong>Closed</strong>
                                        <br><small class="text-muted">Issue closed</small>
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="fas fa-cog me-1"></i>Actions
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                @if(!in_array($siteIssue->status, ['resolved', 'closed']))
                                    <a href="{{ route('sc.site-issues.edit', $siteIssue) }}" class="btn btn-outline-primary">
                                        <i class="fas fa-edit me-1"></i> Edit Issue
                                    </a>
                                @endif

                                <a href="{{ route('sc.site-issues.create', ['project_id' => $siteIssue->project_id]) }}" class="btn btn-outline-danger">
                                    <i class="fas fa-exclamation-triangle me-1"></i> Report New Issue
                                </a>

                                @if($siteIssue->task)
                                    <a href="{{ route('sc.task-reports.create', ['task_id' => $siteIssue->task_id]) }}" class="btn btn-outline-success">
                                        <i class="fas fa-file-alt me-1"></i> Create Task Report
                                    </a>
                                @endif

                                <a href="{{ route('projects.show', $siteIssue->project) }}" class="btn btn-outline-info">
                                    <i class="fas fa-project-diagram me-1"></i> View Project
                                </a>

                                <a href="{{ route('sc.site-issues.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-list me-1"></i> All Issues
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
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

.timeline::before {
    content: '';
    position: absolute;
    left: 10px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #dee2e6;
}

.timeline-item {
    position: relative;
    margin-bottom: 20px;
}

.timeline-marker {
    position: absolute;
    left: -24px;
    top: 5px;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    border: 2px solid #fff;
    box-shadow: 0 0 0 2px #dee2e6;
}

.timeline-content {
    background: #f8f9fa;
    padding: 10px 15px;
    border-radius: 8px;
    border-left: 3px solid #007bff;
}

.bg-light.rounded {
    white-space: pre-wrap;
}

.img-thumbnail:hover {
    transform: scale(1.05);
    transition: transform 0.2s;
}

@media (max-width: 768px) {
    .timeline {
        padding-left: 20px;
    }
    
    .timeline-marker {
        left: -19px;
        width: 10px;
        height: 10px;
    }
    
    .timeline-content {
        padding: 8px 12px;
    }
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-resize textarea
    const textarea = document.querySelector('textarea[name="comment"]');
    if (textarea) {
        textarea.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = this.scrollHeight + 'px';
        });
    }

    // File upload preview
    const fileInput = document.querySelector('input[name="attachments[]"]');
    if (fileInput) {
        fileInput.addEventListener('change', function() {
            const files = this.files;
            if (files.length > 0) {
                const fileNames = Array.from(files).map(file => file.name).join(', ');
                console.log('Selected files:', fileNames);
            }
        });
    }
});
</script>
@endpush