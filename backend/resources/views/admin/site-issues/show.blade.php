@extends('app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h2">
                    <i class="fas fa-exclamation-triangle me-2"></i>Site Issue Details
                </h1>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.site-issues.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Back to Issues
                    </a>
                    <a href="{{ route('admin.site-issues.edit', $siteIssue) }}" class="btn btn-warning">
                        <i class="fas fa-edit me-1"></i> Edit Issue
                    </a>
                </div>
            </div>

            <div class="row">
                <!-- Main Issue Details -->
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="card-title mb-0">{{ $siteIssue->issue_title }}</h5>
                                <div class="d-flex gap-1">
                                    <span class="badge bg-{{ $siteIssue->priority_badge_color }}">
                                        {{ $siteIssue->formatted_priority }}
                                    </span>
                                    <span class="badge bg-{{ $siteIssue->issue_type_badge_color }}">
                                        {{ $siteIssue->formatted_issue_type }}
                                    </span>
                                    <span class="badge bg-{{ $siteIssue->status_badge_color }}">
                                        {{ $siteIssue->formatted_status }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <!-- Quick Actions for Admins -->
                            @if($siteIssue->status !== 'resolved' && $siteIssue->status !== 'closed')
                                <div class="alert alert-info mb-4">
                                    <h6><i class="fas fa-bolt me-1"></i> Quick Actions</h6>
                                    <div class="d-flex gap-2 flex-wrap">
                                        @if(!$siteIssue->acknowledged_at)
                                            <form method="POST" action="{{ route('admin.site-issues.acknowledge', $siteIssue) }}" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-info">
                                                    <i class="fas fa-check me-1"></i> Acknowledge
                                                </button>
                                            </form>
                                        @endif
                                        
                                        @if(!$siteIssue->assigned_to)
                                            <div class="d-inline">
                                                <form method="POST" action="{{ route('admin.site-issues.assign', $siteIssue) }}" class="d-inline">
                                                    @csrf
                                                    <select name="assigned_to" class="form-select form-select-sm d-inline-block w-auto me-1" onchange="this.form.submit()">
                                                        <option value="">Assign to...</option>
                                                        @foreach($assignableUsers as $user)
                                                            <option value="{{ $user->id }}">{{ $user->full_name }}</option>
                                                        @endforeach
                                                    </select>
                                                </form>
                                            </div>
                                        @endif

                                        @if($siteIssue->status !== 'resolved')
                                            <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#resolveModal">
                                                <i class="fas fa-check-circle me-1"></i> Mark Resolved
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
                                                <button type="submit" class="btn btn-sm btn-danger">
                                                    <i class="fas fa-exclamation-triangle me-1"></i> Escalate
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                            @endif

                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <h6><i class="fas fa-info-circle me-1"></i> Issue Information</h6>
                                    <table class="table table-sm">
                                        <tr>
                                            <td><strong>Project:</strong></td>
                                            <td>
                                                <a href="{{ route('projects.show', $siteIssue->project) }}" class="text-decoration-none">
                                                    {{ $siteIssue->project->name }}
                                                </a>
                                            </td>
                                        </tr>
                                        @if($siteIssue->task)
                                            <tr>
                                                <td><strong>Related Task:</strong></td>
                                                <td>
                                                    <a href="{{ route('tasks.show', $siteIssue->task) }}" class="text-decoration-none">
                                                        {{ $siteIssue->task->task_name }}
                                                    </a>
                                                </td>
                                            </tr>
                                        @endif
                                        <tr>
                                            <td><strong>Location:</strong></td>
                                            <td>{{ $siteIssue->location ?: 'Not specified' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Estimated Cost:</strong></td>
                                            <td>{{ $siteIssue->formatted_estimated_cost ?: 'Not specified' }}</td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <h6><i class="fas fa-users me-1"></i> People Involved</h6>
                                    <table class="table table-sm">
                                        <tr>
                                            <td><strong>Reported by:</strong></td>
                                            <td>{{ $siteIssue->reporter->full_name }} ({{ $siteIssue->reporter->role }})</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Assigned to:</strong></td>
                                            <td>{{ $siteIssue->assignedTo ? $siteIssue->assignedTo->full_name : 'Unassigned' }}</td>
                                        </tr>
                                        @if($siteIssue->acknowledgedBy)
                                            <tr>
                                                <td><strong>Acknowledged by:</strong></td>
                                                <td>{{ $siteIssue->acknowledgedBy->full_name }}</td>
                                            </tr>
                                        @endif
                                        @if($siteIssue->resolvedBy)
                                            <tr>
                                                <td><strong>Resolved by:</strong></td>
                                                <td>{{ $siteIssue->resolvedBy->full_name }}</td>
                                            </tr>
                                        @endif
                                    </table>
                                </div>
                            </div>

                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <h6><i class="fas fa-clock me-1"></i> Timeline</h6>
                                    <table class="table table-sm">
                                        <tr>
                                            <td><strong>Reported:</strong></td>
                                            <td>{{ $siteIssue->formatted_reported_at }}</td>
                                        </tr>
                                        @if($siteIssue->acknowledged_at)
                                            <tr>
                                                <td><strong>Acknowledged:</strong></td>
                                                <td>{{ $siteIssue->formatted_acknowledged_at }}</td>
                                            </tr>
                                        @endif
                                        @if($siteIssue->resolved_at)
                                            <tr>
                                                <td><strong>Resolved:</strong></td>
                                                <td>{{ $siteIssue->formatted_resolved_at }}</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Resolution Time:</strong></td>
                                                <td>{{ $siteIssue->resolution_time }} day(s)</td>
                                            </tr>
                                        @endif
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <h6><i class="fas fa-chart-line me-1"></i> Status Information</h6>
                                    <table class="table table-sm">
                                        <tr>
                                            <td><strong>Days Open:</strong></td>
                                            <td>{{ $siteIssue->days_since_reported }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Needs Attention:</strong></td>
                                            <td>
                                                @if($siteIssue->needs_attention)
                                                    <span class="badge bg-warning">Yes</span>
                                                @else
                                                    <span class="badge bg-success">No</span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>Comments:</strong></td>
                                            <td>{{ $siteIssue->comments->count() }} total</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>

                            <h6><i class="fas fa-file-alt me-1"></i> Description</h6>
                            <div class="bg-light p-3 rounded mb-3">
                                {!! nl2br(e($siteIssue->description)) !!}
                            </div>

                            @if($siteIssue->affected_areas)
                                <h6><i class="fas fa-map me-1"></i> Affected Areas</h6>
                                <div class="bg-light p-3 rounded mb-3">
                                    {!! nl2br(e($siteIssue->affected_areas)) !!}
                                </div>
                            @endif

                            @if($siteIssue->immediate_actions_taken)
                                <h6><i class="fas fa-first-aid me-1"></i> Immediate Actions Taken</h6>
                                <div class="bg-light p-3 rounded mb-3">
                                    {!! nl2br(e($siteIssue->immediate_actions_taken)) !!}
                                </div>
                            @endif

                            @if($siteIssue->suggested_solutions)
                                <h6><i class="fas fa-lightbulb me-1"></i> Suggested Solutions</h6>
                                <div class="bg-light p-3 rounded mb-3">
                                    {!! nl2br(e($siteIssue->suggested_solutions)) !!}
                                </div>
                            @endif

                            @if($siteIssue->admin_notes)
                                <h6><i class="fas fa-sticky-note me-1"></i> Admin Notes</h6>
                                <div class="bg-light p-3 rounded mb-3">
                                    {!! nl2br(e($siteIssue->admin_notes)) !!}
                                </div>
                            @endif

                            @if($siteIssue->resolution_description)
                                <h6><i class="fas fa-check-circle me-1"></i> Resolution Details</h6>
                                <div class="bg-success bg-opacity-10 p-3 rounded mb-3 border border-success">
                                    {!! nl2br(e($siteIssue->resolution_description)) !!}
                                </div>
                            @endif

                            <!-- Photos -->
                            @if($siteIssue->photos && count($siteIssue->photos) > 0)
                                <h6><i class="fas fa-images me-1"></i> Photos</h6>
                                <div class="row mb-3">
                                    @foreach($siteIssue->photos as $photo)
                                        <div class="col-md-3 mb-2">
                                            <img src="{{ Storage::url($photo) }}" class="img-fluid rounded" 
                                                 style="max-height: 200px; cursor: pointer;" 
                                                 data-bs-toggle="modal" data-bs-target="#photoModal{{ $loop->index }}">
                                        </div>

                                        <!-- Photo Modal -->
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
                                </div>
                            @endif

                            <!-- Attachments -->
                            @if($siteIssue->attachments && count($siteIssue->attachments) > 0)
                                <h6><i class="fas fa-paperclip me-1"></i> Attachments</h6>
                                <div class="list-group mb-3">
                                    @foreach($siteIssue->attachments as $attachment)
                                        <div class="list-group-item d-flex justify-content-between align-items-center">
                                            <div>
                                                <i class="fas fa-file me-2"></i>
                                                {{ $attachment['original_name'] ?? 'Attachment ' . ($loop->iteration) }}
                                                <small class="text-muted">({{ number_format(($attachment['size'] ?? 0) / 1024, 1) }} KB)</small>
                                            </div>
                                            <a href="{{ Storage::url($attachment['path']) }}" class="btn btn-sm btn-outline-primary" target="_blank">
                                                <i class="fas fa-download"></i> Download
                                            </a>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Comments Section -->
                    <div class="card mt-4">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-comments me-2"></i>Comments ({{ $siteIssue->comments->count() }})
                            </h5>
                        </div>
                        <div class="card-body">
                            @if($siteIssue->comments->count() > 0)
                                @foreach($siteIssue->comments as $comment)
                                    <div class="border p-3 rounded mb-3 {{ $comment->is_internal ? 'bg-warning bg-opacity-10' : '' }}">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <div>
                                                <strong>{{ $comment->user->full_name }}</strong>
                                                <small class="text-muted">({{ $comment->user->role }})</small>
                                                {!! $comment->type_badge !!}
                                            </div>
                                            <small class="text-muted">{{ $comment->formatted_created_at }}</small>
                                        </div>
                                        <div>{!! nl2br(e($comment->comment)) !!}</div>
                                        
                                        @if($comment->attachments && count($comment->attachments) > 0)
                                            <div class="mt-2">
                                                <small class="text-muted">Attachments:</small>
                                                @foreach($comment->attachments as $attachment)
                                                    <a href="{{ Storage::url($attachment['path']) }}" class="btn btn-sm btn-outline-secondary ms-1" target="_blank">
                                                        <i class="fas fa-paperclip"></i> {{ $attachment['original_name'] ?? 'File' }}
                                                    </a>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            @else
                                <p class="text-muted text-center">No comments yet.</p>
                            @endif

                            <!-- Add Comment Form -->
                            <form action="{{ route('admin.site-issues.add-comment', $siteIssue) }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <div class="mb-3">
                                    <label for="comment" class="form-label">Add Comment</label>
                                    <textarea name="comment" id="comment" rows="3" class="form-control @error('comment') is-invalid @enderror" 
                                              required placeholder="Enter your comment...">{{ old('comment') }}</textarea>
                                    @error('comment')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-check mb-3">
                                            <input type="checkbox" name="is_internal" id="is_internal" class="form-check-input" value="1" {{ old('is_internal') ? 'checked' : '' }}>
                                            <label for="is_internal" class="form-check-label">
                                                Internal Comment (Admin only)
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="attachments" class="form-label">Attachments (optional)</label>
                                            <input type="file" name="attachments[]" id="attachments" class="form-control form-control-sm" multiple>
                                        </div>
                                    </div>
                                </div>
                                
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-comment me-1"></i> Add Comment
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="card-title mb-0">
                                <i class="fas fa-cog me-1"></i> Quick Actions
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <a href="{{ route('admin.site-issues.edit', $siteIssue) }}" class="btn btn-warning">
                                    <i class="fas fa-edit me-1"></i> Edit Issue
                                </a>
                                
                                @if($siteIssue->project)
                                    <a href="{{ route('projects.show', $siteIssue->project) }}" class="btn btn-outline-primary">
                                        <i class="fas fa-project-diagram me-1"></i> View Project
                                    </a>
                                @endif
                                
                                @if($siteIssue->task)
                                    <a href="{{ route('tasks.show', $siteIssue->task) }}" class="btn btn-outline-success">
                                        <i class="fas fa-tasks me-1"></i> View Task
                                    </a>
                                @endif
                                
                                <a href="mailto:{{ $siteIssue->reporter->email }}" class="btn btn-outline-info">
                                    <i class="fas fa-envelope me-1"></i> Email Reporter
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="card mt-3">
                        <div class="card-header">
                            <h6 class="card-title mb-0">
                                <i class="fas fa-chart-bar me-1"></i> Issue Statistics
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row text-center">
                                <div class="col-6">
                                    <div class="border-end">
                                        <h6 class="text-primary">{{ $siteIssue->days_since_reported }}</h6>
                                        <small class="text-muted">Days Open</small>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <h6 class="text-info">{{ $siteIssue->comments->count() }}</h6>
                                    <small class="text-muted">Comments</small>
                                </div>
                            </div>
                            
                            @if($siteIssue->resolution_time)
                                <hr>
                                <div class="text-center">
                                    <h6 class="text-success">{{ $siteIssue->resolution_time }}</h6>
                                    <small class="text-muted">Days to Resolution</small>
                                </div>
                            @endif
                        </div>
                    </div>

                    @if($siteIssue->needs_attention)
                        <div class="card mt-3 border-warning">
                            <div class="card-header bg-warning text-dark">
                                <h6 class="card-title mb-0">
                                    <i class="fas fa-exclamation-triangle me-1"></i> Attention Required
                                </h6>
                            </div>
                            <div class="card-body">
                                <p class="mb-2">This issue requires immediate attention due to:</p>
                                <ul class="list-unstyled mb-0">
                                    @if($siteIssue->is_overdue_for_acknowledgment)
                                        <li><i class="fas fa-clock text-warning me-1"></i> Overdue for acknowledgment</li>
                                    @endif
                                    @if($siteIssue->is_overdue_for_resolution)
                                        <li><i class="fas fa-exclamation-circle text-danger me-1"></i> Overdue for resolution</li>
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
    </div>
</div>

<!-- Resolve Issue Modal -->
<div class="modal fade" id="resolveModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.site-issues.resolve', $siteIssue) }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Mark Issue as Resolved</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="resolution_description" class="form-label">Resolution Description <span class="text-danger">*</span></label>
                        <textarea name="resolution_description" id="resolution_description" rows="4" class="form-control" 
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
@endsection

@push('styles')
<style>
.bg-opacity-10 {
    background-color: rgba(var(--bs-warning-rgb), 0.1) !important;
}
</style>
@endpush