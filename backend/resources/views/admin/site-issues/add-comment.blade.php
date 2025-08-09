@extends('app')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-comment me-2"></i>Add Comment: {{ Str::limit($siteIssue->issue_title, 40) }}
                    </h5>
                    <a href="{{ route('admin.site-issues.show', $siteIssue) }}" class="btn btn-sm btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Back
                    </a>
                </div>
                <div class="card-body">
                    <!-- Issue Summary -->
                    <div class="alert alert-info">
                        <div class="row">
                            <div class="col-md-8">
                                <h6 class="mb-1">{{ $siteIssue->issue_title }}</h6>
                                <small class="text-muted">
                                    <i class="fas fa-user me-1"></i>Reported by: {{ $siteIssue->reporter->full_name }} |
                                    <i class="fas fa-calendar me-1"></i>{{ $siteIssue->formatted_reported_at }} |
                                    <i class="fas fa-project-diagram me-1"></i>{{ $siteIssue->project->name }}
                                </small>
                            </div>
                            <div class="col-md-4 text-end">
                                <span class="badge bg-{{ $siteIssue->status_badge_color }}">
                                    {{ $siteIssue->formatted_status }}
                                </span>
                                <span class="badge bg-{{ $siteIssue->priority_badge_color }}">
                                    {{ $siteIssue->formatted_priority }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <form action="{{ route('admin.site-issues.add-comment', $siteIssue) }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="mb-3">
                            <label for="comment" class="form-label">Comment <span class="text-danger">*</span></label>
                            <textarea name="comment" id="comment" rows="5" 
                                      class="form-control @error('comment') is-invalid @enderror" 
                                      required placeholder="Enter your comment or update about this issue...">{{ old('comment') }}</textarea>
                            @error('comment')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-check mb-3">
                                    <input type="checkbox" name="is_internal" id="is_internal" 
                                           class="form-check-input" value="1" {{ old('is_internal') ? 'checked' : '' }}>
                                    <label for="is_internal" class="form-check-label">
                                        <i class="fas fa-lock me-1"></i>Internal Comment (Admin Only)
                                    </label>
                                    <div class="form-text">
                                        Internal comments are only visible to admin team members and not shown to the site coordinator
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="attachments" class="form-label">Attachments (Optional)</label>
                                    <input type="file" name="attachments[]" id="attachments" 
                                           class="form-control form-control-sm @error('attachments.*') is-invalid @enderror" multiple>
                                    <div class="form-text">
                                        Upload relevant documents or images (max 10MB each)
                                    </div>
                                    @error('attachments.*')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Quick Comment Templates -->
                        <div class="mb-3">
                            <label class="form-label">Quick Templates (Click to use)</label>
                            <div class="d-flex flex-wrap gap-2">
                                <button type="button" class="btn btn-sm btn-outline-primary template-btn" 
                                        data-template="Issue has been acknowledged and assigned to our team for review.">
                                    Acknowledged
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-warning template-btn" 
                                        data-template="We are currently investigating this issue and will provide updates as they become available.">
                                    Investigating
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-info template-btn" 
                                        data-template="Work is currently in progress to resolve this issue. Expected completion time will be updated shortly.">
                                    In Progress
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-success template-btn" 
                                        data-template="This issue has been resolved. Please verify and let us know if you need any further assistance.">
                                    Resolved
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-danger template-btn" 
                                        data-template="This issue requires additional resources and has been escalated to management for priority handling.">
                                    Escalated
                                </button>
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-comment me-1"></i> Add Comment
                            </button>
                            <a href="{{ route('admin.site-issues.show', $siteIssue) }}" class="btn btn-outline-secondary">
                                Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Recent Comments -->
            @if($siteIssue->comments->count() > 0)
                <div class="card mt-3">
                    <div class="card-header">
                        <h6 class="card-title mb-0">
                            <i class="fas fa-comments me-2"></i>Recent Comments ({{ $siteIssue->comments->count() }})
                        </h6>
                    </div>
                    <div class="card-body">
                        @foreach($siteIssue->comments->take(3) as $comment)
                            <div class="border p-3 rounded mb-3 {{ $comment->is_internal ? 'bg-warning bg-opacity-10' : '' }}">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <strong>{{ $comment->user->full_name }}</strong>
                                        <small class="text-muted">({{ ucfirst($comment->user->role) }})</small>
                                        @if($comment->is_internal)
                                            <span class="badge bg-warning text-dark">Internal</span>
                                        @endif
                                    </div>
                                    <small class="text-muted">{{ $comment->formatted_created_at }}</small>
                                </div>
                                <div>{!! nl2br(e($comment->comment)) !!}</div>
                            </div>
                        @endforeach
                        
                        @if($siteIssue->comments->count() > 3)
                            <div class="text-center">
                                <a href="{{ route('admin.site-issues.show', $siteIssue) }}" class="btn btn-sm btn-outline-primary">
                                    View All {{ $siteIssue->comments->count() }} Comments
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Template button functionality
document.querySelectorAll('.template-btn').forEach(button => {
    button.addEventListener('click', function() {
        const template = this.getAttribute('data-template');
        const commentField = document.getElementById('comment');
        
        // If field is empty, use template directly, otherwise append
        if (commentField.value.trim() === '') {
            commentField.value = template;
        } else {
            commentField.value += '\n\n' + template;
        }
        
        commentField.focus();
        commentField.scrollTop = commentField.scrollHeight;
    });
});

// Auto-resize textarea
document.getElementById('comment').addEventListener('input', function() {
    this.style.height = 'auto';
    this.style.height = (this.scrollHeight) + 'px';
});
</script>
@endpush

@push('styles')
<style>
.template-btn {
    font-size: 0.875rem;
}
.bg-opacity-10 {
    background-color: rgba(var(--bs-warning-rgb), 0.1) !important;
}
</style>
@endpush