{{-- Admin Site Photos Update Review - views/admin/site-photos/update-review.blade.php --}}
@extends('app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h2">Review Photo</h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('admin.site-photos.index') }}">Site Photos</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.site-photos.show', $sitePhoto) }}">{{ $sitePhoto->title }}</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Review</li>
                        </ol>
                    </nav>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.site-photos.show', $sitePhoto) }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i>Back to Photo
                    </a>
                    <a href="{{ route('admin.site-photos.index') }}" class="btn btn-outline-primary">
                        <i class="fas fa-list me-1"></i>Back to List
                    </a>
                </div>
            </div>

            <div class="row">
                <!-- Photo Display -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                {{ $sitePhoto->title }}
                                <span class="badge bg-{{ $sitePhoto->submission_status_badge_color }} ms-2">
                                    {{ $sitePhoto->formatted_submission_status }}
                                </span>
                                @if($sitePhoto->is_overdue_for_review)
                                    <span class="badge bg-danger ms-2">
                                        <i class="fas fa-clock"></i> Overdue ({{ $sitePhoto->days_since_submission }} days)
                                    </span>
                                @endif
                            </h5>
                        </div>
                        <div class="card-body p-0">
                            <img src="{{ $sitePhoto->photo_url }}" alt="{{ $sitePhoto->title }}" 
                                 class="img-fluid w-100" style="max-height: 400px; object-fit: contain;">
                        </div>
                        <div class="card-footer">
                            <div class="row text-center">
                                <div class="col-md-4">
                                    <small class="text-muted">
                                        <i class="fas fa-calendar me-1"></i>{{ $sitePhoto->formatted_photo_date }}
                                    </small>
                                </div>
                                <div class="col-md-4">
                                    <small class="text-muted">
                                        <i class="fas fa-file me-1"></i>{{ $sitePhoto->formatted_file_size }}
                                    </small>
                                </div>
                                <div class="col-md-4">
                                    <span class="badge bg-{{ $sitePhoto->photo_category_badge_color }}">
                                        {{ $sitePhoto->formatted_photo_category }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Photo Details -->
                    <div class="card mt-3">
                        <div class="card-header">
                            <h6 class="mb-0">Photo Details</h6>
                        </div>
                        <div class="card-body">
                            @if($sitePhoto->description)
                                <div class="mb-3">
                                    <strong>Description:</strong>
                                    <p class="mt-1 mb-0">{{ $sitePhoto->description }}</p>
                                </div>
                            @endif

                            <div class="row">
                                <div class="col-md-6">
                                    <strong>Project:</strong><br>
                                    <a href="{{ route('projects.show', $sitePhoto->project) }}" class="text-decoration-none">
                                        {{ $sitePhoto->project->name }}
                                    </a>
                                </div>
                                <div class="col-md-6">
                                    <strong>Uploader:</strong><br>
                                    {{ $sitePhoto->uploader->first_name }} {{ $sitePhoto->uploader->last_name }}
                                </div>
                            </div>

                            @if($sitePhoto->task)
                                <div class="mt-2">
                                    <strong>Related Task:</strong><br>
                                    <a href="{{ route('tasks.show', $sitePhoto->task) }}" class="text-decoration-none">
                                        {{ $sitePhoto->task->task_name }}
                                    </a>
                                </div>
                            @endif

                            @if($sitePhoto->location)
                                <div class="mt-2">
                                    <strong>Location:</strong><br>
                                    <i class="fas fa-map-marker-alt me-1"></i>{{ $sitePhoto->location }}
                                </div>
                            @endif

                            @if($sitePhoto->weather_conditions)
                                <div class="mt-2">
                                    <strong>Weather:</strong><br>
                                    <i class="{{ $sitePhoto->weather_icon }}"></i>
                                    {{ $sitePhoto->formatted_weather_conditions }}
                                </div>
                            @endif

                            @if($sitePhoto->tags && count($sitePhoto->tags) > 0)
                                <div class="mt-2">
                                    <strong>Tags:</strong><br>
                                    @foreach($sitePhoto->tags as $tag)
                                        <span class="badge bg-secondary me-1"># {{ $tag }}</span>
                                    @endforeach
                                </div>
                            @endif

                            <div class="mt-2">
                                <strong>Submitted:</strong><br>
                                {{ $sitePhoto->formatted_submitted_at }} ({{ $sitePhoto->submitted_at->diffForHumans() }})
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Review Form -->
                <div class="col-md-6">
                    @if($sitePhoto->submission_status === 'submitted')
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="fas fa-clipboard-check me-2"></i>Review This Photo
                                </h5>
                            </div>
                            <div class="card-body">
                                <form method="POST" action="{{ route('admin.site-photos.update-review', $sitePhoto) }}">
                                    @csrf
                                    @method('PATCH')

                                    <!-- Review Action -->
                                    <div class="mb-4">
                                        <label class="form-label">Review Decision <span class="text-danger">*</span></label>
                                        <div class="form-check mb-2">
                                            <input class="form-check-input" type="radio" name="action" value="approve" 
                                                   id="actionApprove" {{ old('action') === 'approve' ? 'checked' : '' }} required>
                                            <label class="form-check-label text-success" for="actionApprove">
                                                <i class="fas fa-check me-1"></i><strong>Approve Photo</strong>
                                                <br><small class="text-muted">Photo meets quality standards and can be published</small>
                                            </label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="action" value="reject" 
                                                   id="actionReject" {{ old('action') === 'reject' ? 'checked' : '' }} required>
                                            <label class="form-check-label text-danger" for="actionReject">
                                                <i class="fas fa-times me-1"></i><strong>Reject Photo</strong>
                                                <br><small class="text-muted">Photo needs revision or doesn't meet standards</small>
                                            </label>
                                        </div>
                                    </div>

                                    <!-- General Comments -->
                                    <div class="mb-3">
                                        <label for="admin_comments" class="form-label">Review Comments (Optional)</label>
                                        <textarea class="form-control" id="admin_comments" name="admin_comments" rows="3" 
                                                  placeholder="Add your review comments...">{{ old('admin_comments') }}</textarea>
                                        <div class="form-text">
                                            These comments will be visible to the site coordinator who uploaded the photo.
                                        </div>
                                    </div>

                                    <!-- Approval Fields -->
                                    <div id="approveFields" style="display: {{ old('action') === 'approve' ? 'block' : 'none' }}">
                                        <div class="alert alert-success">
                                            <i class="fas fa-info-circle me-2"></i>
                                            <strong>Approval Options:</strong> Configure how this approved photo will be displayed.
                                        </div>

                                        <div class="mb-3">
                                            <label for="admin_rating" class="form-label">Quality Rating (Optional)</label>
                                            <select class="form-select" id="admin_rating" name="admin_rating">
                                                <option value="">No Rating</option>
                                                <option value="1" {{ old('admin_rating') == '1' ? 'selected' : '' }}>⭐ 1 Star - Basic Quality</option>
                                                <option value="2" {{ old('admin_rating') == '2' ? 'selected' : '' }}>⭐⭐ 2 Stars - Fair Quality</option>
                                                <option value="3" {{ old('admin_rating') == '3' ? 'selected' : '' }}>⭐⭐⭐ 3 Stars - Good Quality</option>
                                                <option value="4" {{ old('admin_rating') == '4' ? 'selected' : '' }}>⭐⭐⭐⭐ 4 Stars - High Quality</option>
                                                <option value="5" {{ old('admin_rating') == '5' ? 'selected' : '' }}>⭐⭐⭐⭐⭐ 5 Stars - Excellent Quality</option>
                                            </select>
                                            <div class="form-text">
                                                Rate the overall quality and usefulness of this photo.
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" id="is_featured" 
                                                           name="is_featured" {{ old('is_featured') ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="is_featured">
                                                        <i class="fas fa-star text-warning me-1"></i><strong>Mark as Featured</strong>
                                                    </label>
                                                    <div class="form-text small">
                                                        Featured photos are highlighted in project galleries and reports.
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" id="is_public" 
                                                           name="is_public" {{ old('is_public') ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="is_public">
                                                        <i class="fas fa-eye text-info me-1"></i><strong>Make Public</strong>
                                                    </label>
                                                    <div class="form-text small">
                                                        Public photos can be viewed by clients and appear in public galleries.
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Rejection Fields -->
                                    <div id="rejectFields" style="display: {{ old('action') === 'reject' ? 'block' : 'none' }}">
                                        <div class="alert alert-warning">
                                            <i class="fas fa-exclamation-triangle me-2"></i>
                                            <strong>Rejection Reason Required:</strong> Please explain why this photo is being rejected.
                                        </div>

                                        <div class="mb-3">
                                            <label for="rejection_reason" class="form-label">Rejection Reason <span class="text-danger">*</span></label>
                                            <textarea class="form-control" id="rejection_reason" name="rejection_reason" rows="3" 
                                                      placeholder="Please provide a clear reason for rejecting this photo...">{{ old('rejection_reason') }}</textarea>
                                            <div class="form-text">
                                                This reason will be sent to the site coordinator so they can understand what needs to be improved.
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">Common Rejection Reasons (Click to use):</label>
                                            <div class="d-flex flex-wrap gap-2">
                                                <button type="button" class="btn btn-sm btn-outline-secondary" 
                                                        onclick="setRejectionReason('Image quality is too low or blurry')">
                                                    Poor Quality
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-secondary" 
                                                        onclick="setRejectionReason('Photo does not clearly show the intended subject or progress')">
                                                    Unclear Subject
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-secondary" 
                                                        onclick="setRejectionReason('Photo is not relevant to the project or task')">
                                                    Not Relevant
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-secondary" 
                                                        onclick="setRejectionReason('Photo contains inappropriate content or safety violations')">
                                                    Inappropriate
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-secondary" 
                                                        onclick="setRejectionReason('Photo lacks proper documentation or context information')">
                                                    Missing Context
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-secondary" 
                                                        onclick="setRejectionReason('Photo appears to be duplicate or very similar to existing photos')">
                                                    Duplicate
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Submit Buttons -->
                                    <div class="d-flex gap-2 mt-4">
                                        <button type="submit" class="btn btn-primary" id="submitReview">
                                            <i class="fas fa-paper-plane me-1"></i>Submit Review
                                        </button>
                                        <a href="{{ route('admin.site-photos.show', $sitePhoto) }}" class="btn btn-outline-secondary">
                                            <i class="fas fa-times me-1"></i>Cancel
                                        </a>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <!-- Quick Actions -->
                        <div class="card mt-3">
                            <div class="card-header">
                                <h6 class="mb-0">Quick Actions</h6>
                            </div>
                            <div class="card-body">
                                <div class="d-grid gap-2">
                                    <button type="button" class="btn btn-success btn-sm" onclick="quickApprove()">
                                        <i class="fas fa-check me-1"></i>Quick Approve (No Rating)
                                    </button>
                                    <button type="button" class="btn btn-warning btn-sm" onclick="quickApproveWithFeature()">
                                        <i class="fas fa-star me-1"></i>Approve & Feature
                                    </button>
                                    <button type="button" class="btn btn-info btn-sm" onclick="quickApprovePublic()">
                                        <i class="fas fa-eye me-1"></i>Approve & Make Public
                                    </button>
                                    <hr>
                                    <button type="button" class="btn btn-outline-danger btn-sm" onclick="quickReject()">
                                        <i class="fas fa-times me-1"></i>Quick Reject
                                    </button>
                                </div>
                            </div>
                        </div>

                    @else
                        <!-- Already Reviewed -->
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="fas fa-info-circle me-2"></i>Review Status
                                </h5>
                            </div>
                            <div class="card-body">
                                @if($sitePhoto->submission_status === 'approved')
                                    <div class="alert alert-success">
                                        <i class="fas fa-check-circle me-2"></i>
                                        <strong>This photo has been approved.</strong>
                                    </div>
                                @elseif($sitePhoto->submission_status === 'rejected')
                                    <div class="alert alert-danger">
                                        <i class="fas fa-times-circle me-2"></i>
                                        <strong>This photo has been rejected.</strong>
                                    </div>
                                @else
                                    <div class="alert alert-secondary">
                                        <i class="fas fa-question-circle me-2"></i>
                                        <strong>This photo is not submitted for review.</strong>
                                    </div>
                                @endif

                                @if($sitePhoto->reviewed_at)
                                    <table class="table table-sm table-borderless mb-0">
                                        <tr>
                                            <td><strong>Reviewed by:</strong></td>
                                            <td>{{ $sitePhoto->reviewer->first_name }} {{ $sitePhoto->reviewer->last_name }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Review Date:</strong></td>
                                            <td>{{ $sitePhoto->formatted_reviewed_at }}</td>
                                        </tr>
                                        @if($sitePhoto->admin_rating)
                                            <tr>
                                                <td><strong>Rating:</strong></td>
                                                <td>{!! $sitePhoto->rating_stars !!} ({{ $sitePhoto->admin_rating }}/5)</td>
                                            </tr>
                                        @endif
                                        @if($sitePhoto->admin_comments)
                                            <tr>
                                                <td><strong>Comments:</strong></td>
                                                <td>{{ $sitePhoto->admin_comments }}</td>
                                            </tr>
                                        @endif
                                        @if($sitePhoto->rejection_reason)
                                            <tr>
                                                <td><strong>Rejection Reason:</strong></td>
                                                <td class="text-danger">{{ $sitePhoto->rejection_reason }}</td>
                                            </tr>
                                        @endif
                                    </table>
                                @endif

                                <div class="mt-3">
                                    <a href="{{ route('admin.site-photos.show', $sitePhoto) }}" class="btn btn-primary">
                                        <i class="fas fa-eye me-1"></i>View Photo Details
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Navigation -->
                    <div class="card mt-3">
                        <div class="card-header">
                            <h6 class="mb-0">Navigation</h6>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <a href="{{ route('admin.site-photos.index', ['status' => 'submitted']) }}" 
                                   class="btn btn-outline-warning btn-sm">
                                    <i class="fas fa-clock me-1"></i>Next Pending Review
                                </a>
                                <a href="{{ route('admin.site-photos.index') }}" 
                                   class="btn btn-outline-primary btn-sm">
                                    <i class="fas fa-list me-1"></i>All Photos
                                </a>
                                <a href="{{ route('projects.show', $sitePhoto->project) }}" 
                                   class="btn btn-outline-info btn-sm">
                                    <i class="fas fa-project-diagram me-1"></i>View Project
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
// Show/hide fields based on review action
document.querySelectorAll('input[name="action"]').forEach(function(radio) {
    radio.addEventListener('change', function() {
        const approveFields = document.getElementById('approveFields');
        const rejectFields = document.getElementById('rejectFields');
        const submitButton = document.getElementById('submitReview');
        const rejectionReason = document.getElementById('rejection_reason');
        
        if (this.value === 'approve') {
            approveFields.style.display = 'block';
            rejectFields.style.display = 'none';
            submitButton.className = 'btn btn-success';
            submitButton.innerHTML = '<i class="fas fa-check me-1"></i>Approve Photo';
            rejectionReason.required = false;
        } else {
            approveFields.style.display = 'none';
            rejectFields.style.display = 'block';
            submitButton.className = 'btn btn-danger';
            submitButton.innerHTML = '<i class="fas fa-times me-1"></i>Reject Photo';
            rejectionReason.required = true;
        }
    });
});

// Set rejection reason from quick buttons
function setRejectionReason(reason) {
    document.getElementById('rejection_reason').value = reason;
    document.getElementById('actionReject').checked = true;
    document.getElementById('actionReject').dispatchEvent(new Event('change'));
}

// Quick action functions
function quickApprove() {
    document.getElementById('actionApprove').checked = true;
    document.getElementById('actionApprove').dispatchEvent(new Event('change'));
    document.querySelector('form').submit();
}

function quickApproveWithFeature() {
    document.getElementById('actionApprove').checked = true;
    document.getElementById('actionApprove').dispatchEvent(new Event('change'));
    document.getElementById('is_featured').checked = true;
    document.querySelector('form').submit();
}

function quickApprovePublic() {
    document.getElementById('actionApprove').checked = true;
    document.getElementById('actionApprove').dispatchEvent(new Event('change'));
    document.getElementById('is_public').checked = true;
    document.querySelector('form').submit();
}

function quickReject() {
    const reason = prompt('Please provide a rejection reason:');
    if (reason && reason.trim()) {
        document.getElementById('actionReject').checked = true;
        document.getElementById('actionReject').dispatchEvent(new Event('change'));
        document.getElementById('rejection_reason').value = reason;
        document.querySelector('form').submit();
    }
}

// Form validation
document.querySelector('form').addEventListener('submit', function(e) {
    const action = document.querySelector('input[name="action"]:checked');
    if (!action) {
        e.preventDefault();
        alert('Please select an action (Approve or Reject).');
        return;
    }
    
    if (action.value === 'reject') {
        const rejectionReason = document.getElementById('rejection_reason').value.trim();
        if (!rejectionReason) {
            e.preventDefault();
            alert('Please provide a rejection reason.');
            document.getElementById('rejection_reason').focus();
            return;
        }
    }
});

// Auto-focus on page load
window.addEventListener('load', function() {
    const firstRadio = document.querySelector('input[name="action"]');
    if (firstRadio && !document.querySelector('input[name="action"]:checked')) {
        firstRadio.focus();
    }
});
</script>
@endpush