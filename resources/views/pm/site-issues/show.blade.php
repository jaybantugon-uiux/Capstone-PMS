@extends('app')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-start mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('pm.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('pm.site-issues.index') }}">Site Issues</a></li>
                    <li class="breadcrumb-item active">Issue #{{ $siteIssue->id }}</li>
                </ol>
            </nav>
            <h1>{{ $siteIssue->issue_title }}</h1>
            <div class="d-flex gap-2 mt-2">
                <span class="badge bg-{{ $siteIssue->priority_badge_color }} fs-6">
                    {{ ucfirst($siteIssue->priority) }} Priority
                </span>
                <span class="badge bg-{{ $siteIssue->status_badge_color }} fs-6">
                    {{ ucfirst(str_replace('_', ' ', $siteIssue->status)) }}
                </span>
                <span class="badge bg-{{ $siteIssue->issue_type_badge_color }} fs-6">
                    {{ ucfirst($siteIssue->issue_type) }}
                </span>
                @if($siteIssue->issue_type === 'safety')
                    <span class="badge bg-danger fs-6">Safety Issue</span>
                @endif
                @if(!$siteIssue->acknowledged_at)
                    <span class="badge bg-warning fs-6">Unacknowledged</span>
                @endif
            </div>
        </div>
        <div class="d-flex gap-2">
            @if(!$siteIssue->acknowledged_at)
            <button type="button" class="btn btn-warning" onclick="acknowledgeIssue()">
                <i class="fas fa-check me-1"></i>Acknowledge
            </button>
            @endif
            @if(!$siteIssue->assignedTo)
            <button type="button" class="btn btn-info" data-bs-toggle="modal" data-bs-target="#assignModal">
                <i class="fas fa-user-plus me-1"></i>Assign
            </button>
            @endif
            @if($siteIssue->status !== 'resolved')
            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#resolveModal">
                <i class="fas fa-check-circle me-1"></i>Mark Resolved
            </button>
            @endif
            <a href="{{ route('pm.site-issues.edit', $siteIssue) }}" class="btn btn-primary">
                <i class="fas fa-edit me-1"></i>Edit
            </a>
            <a href="{{ route('pm.site-issues.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i>Back to List
            </a>
        </div>
    </div>

    <!-- Alert for critical/overdue issues -->
    @if($siteIssue->priority === 'critical' || $siteIssue->is_overdue_for_acknowledgment)
    <div class="alert alert-{{ $siteIssue->priority === 'critical' ? 'danger' : 'warning' }} mb-4">
        <div class="d-flex align-items-center">
            <i class="fas fa-{{ $siteIssue->priority === 'critical' ? 'fire' : 'clock' }} fa-2x me-3"></i>
            <div>
                @if($siteIssue->priority === 'critical')
                    <strong>Critical Issue!</strong> This issue requires immediate attention.
                @else
                    <strong>Overdue Issue!</strong> This issue has been waiting for acknowledgment for 
                    {{ $siteIssue->reported_at->diffForHumans() }}.
                @endif
            </div>
        </div>
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
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6 class="text-muted">Project</h6>
                            <p><a href="{{ route('projects.show', $siteIssue->project) }}" class="text-decoration-none">
                                {{ $siteIssue->project->name }}
                            </a></p>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted">Task</h6>
                            <p>
                                @if($siteIssue->task)
                                    <a href="{{ route('tasks.show', $siteIssue->task) }}" class="text-decoration-none">
                                        {{ $siteIssue->task->task_name }}
                                    </a>
                                @else
                                    <span class="text-muted">Not associated with specific task</span>
                                @endif
                            </p>
                        </div>
                    </div>

                    @if($siteIssue->location)
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="text-muted">Location</h6>
                            <p><i class="fas fa-map-marker-alt text-danger me-1"></i>{{ $siteIssue->location }}</p>
                        </div>
                    </div>
                    @endif

                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="text-muted">Description</h6>
                            <div class="bg-light p-3 rounded">
                                {!! nl2br(e($siteIssue->description)) !!}
                            </div>
                        </div>
                    </div>

                    @if($siteIssue->affected_areas)
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="text-muted">Affected Areas</h6>
                            <div class="bg-light p-3 rounded">
                                {!! nl2br(e($siteIssue->affected_areas)) !!}
                            </div>
                        </div>
                    </div>
                    @endif

                    @if($siteIssue->immediate_actions_taken)
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="text-muted">Immediate Actions Taken</h6>
                            <div class="bg-light p-3 rounded">
                                {!! nl2br(e($siteIssue->immediate_actions_taken)) !!}
                            </div>
                        </div>
                    </div>
                    @endif

                    @if($siteIssue->suggested_solutions)
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="text-muted">Suggested Solutions</h6>
                            <div class="bg-light p-3 rounded">
                                {!! nl2br(e($siteIssue->suggested_solutions)) !!}
                            </div>
                        </div>
                    </div>
                    @endif

                    @if($siteIssue->estimated_cost)
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="text-muted">Estimated Cost</h6>
                            <p class="fs-5 text-success">{{ $siteIssue->formatted_estimated_cost }}</p>
                        </div>
                    </div>
                    @endif

                    @if($siteIssue->resolution_description)
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="text-muted">Resolution</h6>
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle me-2"></i>
                                {!! nl2br(e($siteIssue->resolution_description)) !!}
                            </div>
                        </div>
                    </div>
                    @endif

                    @if($siteIssue->admin_notes)
                    <div class="row">
                        <div class="col-12">
                            <h6 class="text-muted">Admin Notes</h6>
                            <div class="alert alert-info">
                                <i class="fas fa-sticky-note me-2"></i>
                                {!! nl2br(e($siteIssue->admin_notes)) !!}
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Photos -->
            @if($siteIssue->photos && count($siteIssue->photos) > 0)
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Photos ({{ count($siteIssue->photos) }})</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach($siteIssue->photos as $photo)
                        <div class="col-md-4 mb-3">
                            <div class="card">
                                <img src="{{ Storage::url($photo) }}" class="card-img-top" style="height: 200px; object-fit: cover;" 
                                     data-bs-toggle="modal" data-bs-target="#photoModal" onclick="showPhoto('{{ Storage::url($photo) }}')">
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

            <!-- Attachments -->
            @if($siteIssue->attachments && count($siteIssue->attachments) > 0)
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Attachments ({{ count($siteIssue->attachments) }})</h5>
                </div>
                <div class="card-body">
                    @foreach($siteIssue->attachments as $attachment)
                    <div class="d-flex align-items-center mb-2">
                        <i class="fas fa-file me-2"></i>
                        <a href="{{ Storage::url($attachment['path']) }}" target="_blank" class="text-decoration-none">
                            {{ $attachment['original_name'] }}
                        </a>
                        <small class="text-muted ms-2">({{ number_format($attachment['size'] / 1024, 2) }} KB)</small>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Comments Section -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Comments ({{ $siteIssue->comments->count() }})</h5>
                </div>
                <div class="card-body">
                    @if($siteIssue->comments->count() > 0)
                        @foreach($siteIssue->comments as $comment)
                        <div class="d-flex mb-3 {{ $comment->is_internal ? 'bg-light p-3 rounded' : '' }}">
                            <div class="flex-shrink-0 me-3">
                                <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center text-white" style="width: 40px; height: 40px;">
                                    {{ substr($comment->user->first_name, 0, 1) }}{{ substr($comment->user->last_name, 0, 1) }}
                                </div>
                            </div>
                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h6 class="mb-1">{{ $comment->user->first_name }} {{ $comment->user->last_name }}</h6>
                                        <small class="text-muted">
                                            {{ $comment->created_at->format('M d, Y g:i A') }}
                                            @if($comment->is_internal)
                                                <span class="badge bg-warning ms-1">Internal</span>
                                            @endif
                                        </small>
                                    </div>
                                </div>
                                <p class="mt-2 mb-0">{!! nl2br(e($comment->comment)) !!}</p>
                                
                                @if($comment->attachments && count($comment->attachments) > 0)
                                <div class="mt-2">
                                    @foreach($comment->attachments as $attachment)
                                    <div class="d-flex align-items-center mb-1">
                                        <i class="fas fa-paperclip me-1"></i>
                                        <a href="{{ Storage::url($attachment['path']) }}" target="_blank" class="text-decoration-none">
                                            {{ $attachment['original_name'] }}
                                        </a>
                                    </div>
                                    @endforeach
                                </div>
                                @endif
                            </div>
                        </div>
                        @if(!$loop->last)
                        <hr>
                        @endif
                        @endforeach
                    @else
                        <p class="text-muted text-center">No comments yet.</p>
                    @endif
                    
                    <!-- Add Comment Form -->
                    <hr>
                    <form method="POST" action="{{ route('pm.site-issues.add-comment', $siteIssue) }}" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Add Comment</label>
                            <textarea name="comment" class="form-control" rows="3" required placeholder="Enter your comment..."></textarea>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Attachments (optional)</label>
                                    <input type="file" name="attachments[]" class="form-control" multiple>
                                    <small class="text-muted">Max 10MB per file</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <div class="form-check mt-4">
                                        <input class="form-check-input" type="checkbox" name="is_internal" value="1" id="isInternal">
                                        <label class="form-check-label" for="isInternal">
                                            Internal comment (not visible to reporter)
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-comment me-1"></i>Add Comment
                        </button>
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
                    <div class="row">
                        <div class="col-6">
                            <small class="text-muted">Issue ID</small>
                            <p class="fw-bold">#{{ $siteIssue->id }}</p>
                        </div>
                        <div class="col-6">
                            <small class="text-muted">Type</small>
                            <p><span class="badge bg-{{ $siteIssue->issue_type_badge_color }}">{{ ucfirst($siteIssue->issue_type) }}</span></p>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-6">
                            <small class="text-muted">Priority</small>
                            <p><span class="badge bg-{{ $siteIssue->priority_badge_color }}">{{ ucfirst($siteIssue->priority) }}</span></p>
                        </div>
                        <div class="col-6">
                            <small class="text-muted">Status</small>
                            <p><span class="badge bg-{{ $siteIssue->status_badge_color }}">{{ ucfirst(str_replace('_', ' ', $siteIssue->status)) }}</span></p>
                        </div>
                    </div>
                    @if($siteIssue->estimated_cost)
                    <div class="row">
                        <div class="col-12">
                            <small class="text-muted">Estimated Cost</small>
                            <p class="fs-5 text-success">{{ $siteIssue->formatted_estimated_cost }}</p>
                        </div>
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
                    <div class="d-flex align-items-center mb-3">
                        <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center text-white me-3" style="width: 40px; height: 40px;">
                            {{ substr($siteIssue->reporter->first_name, 0, 1) }}{{ substr($siteIssue->reporter->last_name, 0, 1) }}
                        </div>
                        <div>
                            <h6 class="mb-0">{{ $siteIssue->reporter->first_name }} {{ $siteIssue->reporter->last_name }}</h6>
                            <small class="text-muted">Reporter • {{ ucfirst($siteIssue->reporter->role) }}</small>
                        </div>
                    </div>

                    <!-- Assigned To -->
                    @if($siteIssue->assignedTo)
                    <div class="d-flex align-items-center mb-3">
                        <div class="bg-success rounded-circle d-flex align-items-center justify-content-center text-white me-3" style="width: 40px; height: 40px;">
                            {{ substr($siteIssue->assignedTo->first_name, 0, 1) }}{{ substr($siteIssue->assignedTo->last_name, 0, 1) }}
                        </div>
                        <div>
                            <h6 class="mb-0">{{ $siteIssue->assignedTo->first_name }} {{ $siteIssue->assignedTo->last_name }}</h6>
                            <small class="text-muted">Assigned • {{ ucfirst($siteIssue->assignedTo->role) }}</small>
                        </div>
                    </div>
                    @else
                    <div class="alert alert-warning">
                        <i class="fas fa-user-plus me-1"></i>
                        No one assigned to this issue yet.
                    </div>
                    @endif

                    <!-- Acknowledged By -->
                    @if($siteIssue->acknowledgedBy)
                    <div class="d-flex align-items-center mb-3">
                        <div class="bg-info rounded-circle d-flex align-items-center justify-content-center text-white me-3" style="width: 40px; height: 40px;">
                            {{ substr($siteIssue->acknowledgedBy->first_name, 0, 1) }}{{ substr($siteIssue->acknowledgedBy->last_name, 0, 1) }}
                        </div>
                        <div>
                            <h6 class="mb-0">{{ $siteIssue->acknowledgedBy->first_name }} {{ $siteIssue->acknowledgedBy->last_name }}</h6>
                            <small class="text-muted">Acknowledged • {{ ucfirst($siteIssue->acknowledgedBy->role) }}</small>
                        </div>
                    </div>
                    @endif

                    <!-- Resolved By -->
                    @if($siteIssue->resolvedBy)
                    <div class="d-flex align-items-center">
                        <div class="bg-success rounded-circle d-flex align-items-center justify-content-center text-white me-3" style="width: 40px; height: 40px;">
                            {{ substr($siteIssue->resolvedBy->first_name, 0, 1) }}{{ substr($siteIssue->resolvedBy->last_name, 0, 1) }}
                        </div>
                        <div>
                            <h6 class="mb-0">{{ $siteIssue->resolvedBy->first_name }} {{ $siteIssue->resolvedBy->last_name }}</h6>
                            <small class="text-muted">Resolved • {{ ucfirst($siteIssue->resolvedBy->role) }}</small>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Timeline -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Timeline</h5>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        <!-- Reported -->
                        <div class="timeline-item">
                            <div class="timeline-marker bg-primary"></div>
                            <div class="timeline-content">
                                <h6 class="mb-1">Issue Reported</h6>
                                <p class="mb-0 text-muted">{{ $siteIssue->reported_at->format('M d, Y g:i A') }}</p>
                                <small class="text-muted">{{ $siteIssue->reported_at->diffForHumans() }}</small>
                            </div>
                        </div>

                        <!-- Acknowledged -->
                        @if($siteIssue->acknowledged_at)
                        <div class="timeline-item">
                            <div class="timeline-marker bg-info"></div>
                            <div class="timeline-content">
                                <h6 class="mb-1">Acknowledged</h6>
                                <p class="mb-0 text-muted">{{ $siteIssue->acknowledged_at->format('M d, Y g:i A') }}</p>
                                <small class="text-muted">{{ $siteIssue->acknowledged_at->diffForHumans() }}</small>
                                @if($siteIssue->acknowledgedBy)
                                <br><small class="text-muted">by {{ $siteIssue->acknowledgedBy->first_name }} {{ $siteIssue->acknowledgedBy->last_name }}</small>
                                @endif
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
                                <small class="text-muted">{{ $siteIssue->resolved_at->diffForHumans() }}</small>
                                @if($siteIssue->resolvedBy)
                                <br><small class="text-muted">by {{ $siteIssue->resolvedBy->first_name }} {{ $siteIssue->resolvedBy->last_name }}</small>
                                @endif
                                @if($siteIssue->resolution_time)
                                <br><small class="text-success">Resolved in {{ $siteIssue->resolution_time }} days</small>
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
            <form method="POST" action="{{ route('pm.site-issues.resolve', $siteIssue) }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Resolve Issue</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Resolution Description</label>
                        <textarea name="resolution_description" class="form-control" rows="4" required 
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

<!-- Photo Modal -->
<div class="modal fade" id="photoModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Issue Photo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <img id="modalImage" src="" class="img-fluid">
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function acknowledgeIssue() {
    if (confirm('Are you sure you want to acknowledge this issue?')) {
        fetch('{{ route("pm.site-issues.acknowledge", $siteIssue) }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(response => {
            if (response.ok) {
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

function showPhoto(src) {
    document.getElementById('modalImage').src = src;
}
</script>
@endpush

@push('styles')
<style>
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
    margin-bottom: 30px;
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

.bg-light {
    background-color: #f8f9fa !important;
}

.fs-6 {
    font-size: 1rem !important;
}

.avatar-circle {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: bold;
}
</style>
@endpush
@endsection