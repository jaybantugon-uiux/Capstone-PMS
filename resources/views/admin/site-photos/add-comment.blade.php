{{-- Admin Site Photos Add Comment - views/admin/site-photos/add-comment.blade.php --}}
@extends('app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h2">Add Comment</h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('admin.site-photos.index') }}">Site Photos</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.site-photos.show', $sitePhoto) }}">{{ $sitePhoto->title }}</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Add Comment</li>
                        </ol>
                    </nav>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.site-photos.show', $sitePhoto) }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i>Back to Photo
                    </a>
                </div>
            </div>

            <div class="row">
                <!-- Photo Preview -->
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">Photo Preview</h6>
                        </div>
                        <div class="card-body p-0">
                            <img src="{{ $sitePhoto->photo_url }}" alt="{{ $sitePhoto->title }}" 
                                 class="img-fluid w-100" style="max-height: 300px; object-fit: contain;">
                        </div>
                        <div class="card-footer">
                            <h6 class="mb-1">{{ $sitePhoto->title }}</h6>
                            <p class="text-muted mb-0 small">{{ $sitePhoto->project->name }}</p>
                            <span class="badge bg-{{ $sitePhoto->submission_status_badge_color }} mt-1">
                                {{ $sitePhoto->formatted_submission_status }}
                            </span>
                        </div>
                    </div>

                    <!-- Photo Info -->
                    <div class="card mt-3">
                        <div class="card-header">
                            <h6 class="mb-0">Photo Information</h6>
                        </div>
                        <div class="card-body">
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <td><strong>Uploader:</strong></td>
                                    <td>{{ $sitePhoto->uploader->first_name }} {{ $sitePhoto->uploader->last_name }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Category:</strong></td>
                                    <td>
                                        <span class="badge bg-{{ $sitePhoto->photo_category_badge_color }}">
                                            {{ $sitePhoto->formatted_photo_category }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Photo Date:</strong></td>
                                    <td>{{ $sitePhoto->formatted_photo_date }}</td>
                                </tr>
                                @if($sitePhoto->submitted_at)
                                    <tr>
                                        <td><strong>Submitted:</strong></td>
                                        <td>{{ $sitePhoto->formatted_submitted_at }}</td>
                                    </tr>
                                @endif
                                @if($sitePhoto->location)
                                    <tr>
                                        <td><strong>Location:</strong></td>
                                        <td>{{ $sitePhoto->location }}</td>
                                    </tr>
                                @endif
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Comment Form -->
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-comment me-2"></i>Add Comment
                            </h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="{{ route('admin.site-photos.add-comment', $sitePhoto) }}">
                                @csrf

                                <!-- Comment Type Selection -->
                                <div class="mb-4">
                                    <label class="form-label">Comment Type <span class="text-danger">*</span></label>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="radio" name="comment_type" value="external" 
                                               id="commentExternal" {{ old('comment_type', 'external') === 'external' ? 'checked' : '' }} required>
                                        <label class="form-check-label" for="commentExternal">
                                            <i class="fas fa-eye text-info me-1"></i><strong>External Comment</strong>
                                            <br><small class="text-muted">Visible to the site coordinator who uploaded this photo</small>
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="comment_type" value="internal" 
                                               id="commentInternal" {{ old('comment_type') === 'internal' ? 'checked' : '' }} required>
                                        <label class="form-check-label" for="commentInternal">
                                            <i class="fas fa-lock text-warning me-1"></i><strong>Internal Comment</strong>
                                            <br><small class="text-muted">Only visible to admins and project managers</small>
                                        </label>
                                    </div>
                                </div>

                                <!-- Comment Text -->
                                <div class="mb-3">
                                    <label for="comment" class="form-label">Comment <span class="text-danger">*</span></label>
                                    <textarea class="form-control" id="comment" name="comment" rows="6" 
                                              placeholder="Enter your comment..." required>{{ old('comment') }}</textarea>
                                    <div class="form-text">
                                        <span id="commentHelp">
                                            This comment will be visible to the site coordinator and can help them understand your feedback.
                                        </span>
                                    </div>
                                    <div class="form-text mt-1">
                                        <small class="text-muted">Maximum 1000 characters. Be clear and constructive in your feedback.</small>
                                    </div>
                                </div>

                                <!-- Quick Comment Templates -->
                                <div class="mb-3">
                                    <label class="form-label">Quick Comment Templates (Click to use):</label>
                                    <div class="d-flex flex-wrap gap-2 mb-2">
                                        <button type="button" class="btn btn-sm btn-outline-success" 
                                                onclick="setComment('Great photo! Clear documentation of the project progress.')">
                                            Excellent Quality
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-info" 
                                                onclick="setComment('Good photo, clearly shows the current status. Thank you for the documentation.')">
                                            Good Documentation
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-primary" 
                                                onclick="setComment('Photo approved. Please continue documenting project progress.')">
                                            Standard Approval
                                        </button>
                                    </div>
                                    <div class="d-flex flex-wrap gap-2">
                                        <button type="button" class="btn btn-sm btn-outline-warning" 
                                                onclick="setComment('Please provide more context or description for this photo.')">
                                            Need More Context
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-warning" 
                                                onclick="setComment('Please improve photo quality - image is unclear or blurry.')">
                                            Quality Issues
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary" 
                                                onclick="setComment('Please retake this photo with better lighting and focus.')">
                                            Retake Needed
                                        </button>
                                    </div>
                                </div>

                                <!-- Internal Comment Additional Fields -->
                                <div id="internalFields" style="display: {{ old('comment_type') === 'internal' ? 'block' : 'none' }}">
                                    <div class="alert alert-warning">
                                        <i class="fas fa-lock me-2"></i>
                                        <strong>Internal Comment:</strong> This comment will only be visible to administrators and project managers. 
                                        Use this for internal notes, concerns, or administrative comments.
                                    </div>
                                </div>

                                <!-- Submit Buttons -->
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-comment me-1"></i>Add Comment
                                    </button>
                                    <a href="{{ route('admin.site-photos.show', $sitePhoto) }}" class="btn btn-outline-secondary">
                                        <i class="fas fa-times me-1"></i>Cancel
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Existing Comments -->
                    @if($sitePhoto->comments->count() > 0)
                        <div class="card mt-3">
                            <div class="card-header">
                                <h6 class="mb-0">
                                    <i class="fas fa-comments me-2"></i>Existing Comments ({{ $sitePhoto->comments->count() }})
                                </h6>
                            </div>
                            <div class="card-body">
                                @foreach($sitePhoto->comments->sortByDesc('created_at') as $comment)
                                    <div class="border-bottom pb-3 mb-3">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <div>
                                                <strong>{{ $comment->user->first_name }} {{ $comment->user->last_name }}</strong>
                                                @if($comment->is_internal)
                                                    <span class="badge bg-warning ms-2">
                                                        <i class="fas fa-lock"></i> Internal
                                                    </span>
                                                @else
                                                    <span class="badge bg-info ms-2">
                                                        <i class="fas fa-eye"></i> External
                                                    </span>
                                                @endif
                                            </div>
                                            <small class="text-muted">{{ $comment->formatted_created_at }}</small>
                                        </div>
                                        <p class="mb-0">{{ $comment->comment }}</p>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <!-- Quick Actions -->
                    <div class="card mt-3">
                        <div class="card-header">
                            <h6 class="mb-0">Quick Actions</h6>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                @if($sitePhoto->submission_status === 'submitted')
                                    <a href="{{ route('admin.site-photos.show', $sitePhoto) }}?action=approve" 
                                       class="btn btn-success btn-sm">
                                        <i class="fas fa-check me-1"></i>Review & Approve Photo
                                    </a>
                                    <a href="{{ route('admin.site-photos.show', $sitePhoto) }}?action=reject" 
                                       class="btn btn-danger btn-sm">
                                        <i class="fas fa-times me-1"></i>Review & Reject Photo
                                    </a>
                                @endif
                                <a href="{{ route('admin.site-photos.show', $sitePhoto) }}" 
                                   class="btn btn-outline-primary btn-sm">
                                    <i class="fas fa-eye me-1"></i>View Full Photo Details
                                </a>
                                <a href="{{ route('admin.site-photos.index') }}" 
                                   class="btn btn-outline-secondary btn-sm">
                                    <i class="fas fa-list me-1"></i>Back to Photo List
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
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
@endpush

@push('scripts')
<script>
// Show/hide internal fields based on comment type
document.querySelectorAll('input[name="comment_type"]').forEach(function(radio) {
    radio.addEventListener('change', function() {
        const internalFields = document.getElementById('internalFields');
        const commentHelp = document.getElementById('commentHelp');
        
        if (this.value === 'internal') {
            internalFields.style.display = 'block';
            commentHelp.textContent = 'This internal comment will only be visible to administrators and project managers.';
        } else {
            internalFields.style.display = 'none';
            commentHelp.textContent = 'This comment will be visible to the site coordinator and can help them understand your feedback.';
        }
    });
});

// Set comment from quick templates
function setComment(commentText) {
    document.getElementById('comment').value = commentText;
    document.getElementById('comment').focus();
}

// Character counter
const commentTextarea = document.getElementById('comment');
const maxLength = 1000;

commentTextarea.addEventListener('input', function() {
    const remaining = maxLength - this.value.length;
    const helpText = this.parentNode.querySelector('.form-text small');
    
    if (remaining < 100) {
        helpText.innerHTML = `<span class="text-warning">Maximum 1000 characters. ${remaining} characters remaining.</span>`;
    } else {
        helpText.innerHTML = 'Maximum 1000 characters. Be clear and constructive in your feedback.';
    }
    
    if (remaining < 0) {
        helpText.innerHTML = `<span class="text-danger">Character limit exceeded by ${Math.abs(remaining)} characters.</span>`;
        this.classList.add('is-invalid');
    } else {
        this.classList.remove('is-invalid');
    }
});

// Form validation
document.querySelector('form').addEventListener('submit', function(e) {
    const comment = document.getElementById('comment').value.trim();
    const commentType = document.querySelector('input[name="comment_type"]:checked');
    
    if (!comment) {
        e.preventDefault();
        alert('Please enter a comment.');
        document.getElementById('comment').focus();
        return;
    }
    
    if (comment.length > maxLength) {
        e.preventDefault();
        alert(`Comment is too long. Please reduce to ${maxLength} characters or less.`);
        document.getElementById('comment').focus();
        return;
    }
    
    if (!commentType) {
        e.preventDefault();
        alert('Please select a comment type (External or Internal).');
        return;
    }
    
    // Confirm internal comments
    if (commentType.value === 'internal') {
        if (!confirm('This will be posted as an internal comment, only visible to administrators. Continue?')) {
            e.preventDefault();
            return;
        }
    }
});

// Auto-focus on comment textarea when page loads
window.addEventListener('load', function() {
    document.getElementById('comment').focus();
});

// Keyboard shortcuts
document.addEventListener('keydown', function(e) {
    // Ctrl+Enter to submit form
    if (e.ctrlKey && e.key === 'Enter') {
        document.querySelector('form').submit();
    }
    
    // Escape to cancel
    if (e.key === 'Escape') {
        if (confirm('Are you sure you want to cancel? Your comment will be lost.')) {
            window.location.href = '{{ route("admin.site-photos.show", $sitePhoto) }}';
        }
    }
});

// Auto-save draft to localStorage
let autoSaveInterval;
const AUTOSAVE_KEY = 'site_photo_comment_draft_{{ $sitePhoto->id }}';

function autoSave() {
    const comment = document.getElementById('comment').value;
    const commentType = document.querySelector('input[name="comment_type"]:checked')?.value;
    
    if (comment.trim()) {
        localStorage.setItem(AUTOSAVE_KEY, JSON.stringify({
            comment: comment,
            commentType: commentType,
            timestamp: Date.now()
        }));
    }
}

// Start auto-save every 30 seconds
autoSaveInterval = setInterval(autoSave, 30000);

// Load draft on page load
window.addEventListener('load', function() {
    const draft = localStorage.getItem(AUTOSAVE_KEY);
    if (draft) {
        try {
            const parsed = JSON.parse(draft);
            // Only load if draft is less than 24 hours old
            if (Date.now() - parsed.timestamp < 24 * 60 * 60 * 1000) {
                if (confirm('A draft comment was found. Would you like to restore it?')) {
                    document.getElementById('comment').value = parsed.comment;
                    if (parsed.commentType) {
                        const radio = document.querySelector(`input[name="comment_type"][value="${parsed.commentType}"]`);
                        if (radio) {
                            radio.checked = true;
                            radio.dispatchEvent(new Event('change'));
                        }
                    }
                }
            }
        } catch (e) {
            console.log('Error loading draft:', e);
        }
    }
});

// Clear draft when form is submitted successfully
document.querySelector('form').addEventListener('submit', function() {
    clearInterval(autoSaveInterval);
    localStorage.removeItem(AUTOSAVE_KEY);
});

// Handle page unload
window.addEventListener('beforeunload', function(e) {
    const comment = document.getElementById('comment').value.trim();
    if (comment && comment.length > 10) {
        autoSave();
        e.preventDefault();
        e.returnValue = 'You have unsaved changes. Are you sure you want to leave?';
        return e.returnValue;
    }
});
</script>
@endpush