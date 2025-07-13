{{-- Admin Site Photos Show - views/admin/site-photos/show.blade.php --}}
@extends('app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h2">Photo Details</h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('admin.site-photos.index') }}">Site Photos</a></li>
                            <li class="breadcrumb-item active" aria-current="page">{{ $sitePhoto->title }}</li>
                        </ol>
                    </nav>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.site-photos.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i>Back to List
                    </a>
                    @if($sitePhoto->submission_status === 'submitted')
                        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#reviewModal">
                            <i class="fas fa-check me-1"></i>Review Photo
                        </button>
                    @endif
                    @if($sitePhoto->canBeDeletedBy(auth()->user()))
                        <button type="button" class="btn btn-outline-danger" onclick="deletePhoto()">
                            <i class="fas fa-trash me-1"></i>Delete
                        </button>
                    @endif
                </div>
            </div>

            <div class="row">
                <!-- Photo Display -->
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                {{ $sitePhoto->title }}
                                @if($sitePhoto->is_featured)
                                    <span class="badge bg-warning ms-2">
                                        <i class="fas fa-star"></i> Featured
                                    </span>
                                @endif
                                @if($sitePhoto->is_public)
                                    <span class="badge bg-info ms-2">
                                        <i class="fas fa-eye"></i> Public
                                    </span>
                                @endif
                                <span class="badge bg-{{ $sitePhoto->submission_status_badge_color }} ms-2">
                                    {{ $sitePhoto->formatted_submission_status }}
                                </span>
                            </h5>
                        </div>
                        <div class="card-body p-0">
                            <img src="{{ $sitePhoto->photo_url }}" alt="{{ $sitePhoto->title }}" 
                                 class="img-fluid w-100" style="max-height: 600px; object-fit: contain;">
                        </div>
                        <div class="card-footer">
                            <div class="row">
                                <div class="col-md-6">
                                    <small class="text-muted">
                                        <i class="fas fa-calendar me-1"></i>Photo Date: {{ $sitePhoto->formatted_photo_date }}
                                    </small>
                                </div>
                                <div class="col-md-6 text-end">
                                    <small class="text-muted">
                                        <i class="fas fa-file me-1"></i>{{ $sitePhoto->formatted_file_size }}
                                        @if($sitePhoto->image_dimensions)
                                            • {{ $sitePhoto->image_dimensions }}
                                        @endif
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Description -->
                    @if($sitePhoto->description)
                        <div class="card mt-3">
                            <div class="card-header">
                                <h6 class="mb-0">Description</h6>
                            </div>
                            <div class="card-body">
                                <p class="mb-0">{{ $sitePhoto->description }}</p>
                            </div>
                        </div>
                    @endif

                    <!-- Admin Review Section -->
                    @if($sitePhoto->submission_status !== 'draft')
                        <div class="card mt-3">
                            <div class="card-header">
                                <h6 class="mb-0">Admin Review</h6>
                            </div>
                            <div class="card-body">
                                @if($sitePhoto->submission_status === 'submitted')
                                    <div class="alert alert-warning">
                                        <i class="fas fa-clock me-2"></i>
                                        This photo is pending review.
                                        @if($sitePhoto->is_overdue_for_review)
                                            <strong>Overdue for review!</strong> ({{ $sitePhoto->days_since_submission }} days)
                                        @endif
                                    </div>
                                @elseif($sitePhoto->submission_status === 'approved')
                                    <div class="alert alert-success">
                                        <i class="fas fa-check-circle me-2"></i>
                                        This photo has been approved.
                                    </div>
                                @elseif($sitePhoto->submission_status === 'rejected')
                                    <div class="alert alert-danger">
                                        <i class="fas fa-times-circle me-2"></i>
                                        This photo has been rejected.
                                    </div>
                                @endif

                                @if($sitePhoto->reviewed_at)
                                    <div class="row">
                                        <div class="col-md-6">
                                            <strong>Reviewed by:</strong> {{ $sitePhoto->reviewer->first_name }} {{ $sitePhoto->reviewer->last_name }}
                                        </div>
                                        <div class="col-md-6">
                                            <strong>Review Date:</strong> {{ $sitePhoto->formatted_reviewed_at }}
                                        </div>
                                    </div>

                                    @if($sitePhoto->admin_rating)
                                        <div class="mt-2">
                                            <strong>Rating:</strong> {!! $sitePhoto->rating_stars !!} ({{ $sitePhoto->admin_rating }}/5)
                                        </div>
                                    @endif

                                    @if($sitePhoto->admin_comments)
                                        <div class="mt-2">
                                            <strong>Admin Comments:</strong>
                                            <p class="mt-1 mb-0">{{ $sitePhoto->admin_comments }}</p>
                                        </div>
                                    @endif

                                    @if($sitePhoto->rejection_reason)
                                        <div class="mt-2">
                                            <strong>Rejection Reason:</strong>
                                            <p class="mt-1 mb-0 text-danger">{{ $sitePhoto->rejection_reason }}</p>
                                        </div>
                                    @endif
                                @endif
                            </div>
                        </div>
                    @endif

                    <!-- Comments Section -->
                    <div class="card mt-3">
                        <div class="card-header">
                            <h6 class="mb-0">Comments ({{ $sitePhoto->comments->count() }})</h6>
                        </div>
                        <div class="card-body">
                            @if($sitePhoto->comments->count() > 0)
                                @foreach($sitePhoto->comments as $comment)
                                    <div class="border-bottom pb-2 mb-2">
                                        <div class="d-flex justify-content-between">
                                            <strong>{{ $comment->user->first_name }} {{ $comment->user->last_name }}</strong>
                                            <div>
                                                @if($comment->is_internal)
                                                    <span class="badge bg-warning me-2">Internal</span>
                                                @endif
                                                <small class="text-muted">{{ $comment->formatted_created_at }}</small>
                                            </div>
                                        </div>
                                        <p class="mb-0 mt-1">{{ $comment->comment }}</p>
                                    </div>
                                @endforeach
                            @else
                                <p class="text-muted mb-0">No comments yet.</p>
                            @endif

                            <!-- Add Comment Form -->
                            <div class="mt-3">
                                <button type="button" class="btn btn-outline-primary btn-sm" 
                                        data-bs-toggle="modal" data-bs-target="#commentModal">
                                    <i class="fas fa-comment me-1"></i>Add Comment
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Photo Details Sidebar -->
                <div class="col-md-4">
                    <!-- Photo Information -->
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">Photo Information</h6>
                        </div>
                        <div class="card-body">
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <td><strong>Project:</strong></td>
                                    <td>
                                        <a href="{{ route('projects.show', $sitePhoto->project) }}" 
                                           class="text-decoration-none">
                                            {{ $sitePhoto->project->name }}
                                        </a>
                                    </td>
                                </tr>
                                @if($sitePhoto->task)
                                    <tr>
                                        <td><strong>Task:</strong></td>
                                        <td>
                                            <a href="{{ route('tasks.show', $sitePhoto->task) }}" 
                                               class="text-decoration-none">
                                                {{ $sitePhoto->task->task_name }}
                                            </a>
                                        </td>
                                    </tr>
                                @endif
                                <tr>
                                    <td><strong>Category:</strong></td>
                                    <td>
                                        <span class="badge bg-{{ $sitePhoto->photo_category_badge_color }}">
                                            {{ $sitePhoto->formatted_photo_category }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Uploader:</strong></td>
                                    <td>{{ $sitePhoto->uploader->first_name }} {{ $sitePhoto->uploader->last_name }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Upload Date:</strong></td>
                                    <td>{{ $sitePhoto->created_at->format('M d, Y g:i A') }}</td>
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
                                @if($sitePhoto->weather_conditions)
                                    <tr>
                                        <td><strong>Weather:</strong></td>
                                        <td>
                                            <i class="{{ $sitePhoto->weather_icon }}"></i>
                                            {{ $sitePhoto->formatted_weather_conditions }}
                                        </td>
                                    </tr>
                                @endif
                            </table>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="card mt-3">
                        <div class="card-header">
                            <h6 class="mb-0">Quick Actions</h6>
                        </div>
                        <div class="card-body">
                            @if($sitePhoto->submission_status === 'approved')
                                <div class="d-grid gap-2">
                                    <button type="button" class="btn btn-outline-warning btn-sm" 
                                            onclick="toggleFeatured({{ $sitePhoto->is_featured ? 'false' : 'true' }})">
                                        <i class="fas fa-star me-1"></i>
                                        {{ $sitePhoto->is_featured ? 'Remove from Featured' : 'Mark as Featured' }}
                                    </button>
                                    <button type="button" class="btn btn-outline-info btn-sm" 
                                            onclick="togglePublic({{ $sitePhoto->is_public ? 'false' : 'true' }})">
                                        <i class="fas fa-eye me-1"></i>
                                        {{ $sitePhoto->is_public ? 'Make Private' : 'Make Public' }}
                                    </button>
                                </div>
                            @endif
                            
                            <div class="d-grid gap-2 mt-2">
                                <a href="{{ route('photos.show', $sitePhoto) }}" target="_blank" 
                                   class="btn btn-outline-secondary btn-sm">
                                    <i class="fas fa-external-link-alt me-1"></i>View Full Size
                                </a>
                                <a href="{{ $sitePhoto->photo_url }}" download="{{ $sitePhoto->original_filename }}" 
                                   class="btn btn-outline-primary btn-sm">
                                    <i class="fas fa-download me-1"></i>Download Original
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Tags -->
                    @if($sitePhoto->tags && count($sitePhoto->tags) > 0)
                        <div class="card mt-3">
                            <div class="card-header">
                                <h6 class="mb-0">Tags</h6>
                            </div>
                            <div class="card-body">
                                @foreach($sitePhoto->tags as $tag)
                                    <span class="badge bg-secondary me-1 mb-1"># {{ $tag }}</span>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <!-- Camera Information -->
                    @if($sitePhoto->camera_info && count($sitePhoto->camera_info) > 0)
                        <div class="card mt-3">
                            <div class="card-header">
                                <h6 class="mb-0">Camera Information</h6>
                            </div>
                            <div class="card-body">
                                <table class="table table-sm table-borderless">
                                    @if(isset($sitePhoto->camera_info['camera_make']))
                                        <tr>
                                            <td><strong>Camera:</strong></td>
                                            <td>{{ $sitePhoto->camera_info['camera_make'] }} {{ $sitePhoto->camera_info['camera_model'] ?? '' }}</td>
                                        </tr>
                                    @endif
                                    @if(isset($sitePhoto->camera_info['width']) && isset($sitePhoto->camera_info['height']))
                                        <tr>
                                            <td><strong>Dimensions:</strong></td>
                                            <td>{{ $sitePhoto->camera_info['width'] }} × {{ $sitePhoto->camera_info['height'] }}</td>
                                        </tr>
                                    @endif
                                    @if(isset($sitePhoto->camera_info['datetime']))
                                        <tr>
                                            <td><strong>Date Taken:</strong></td>
                                            <td>{{ $sitePhoto->camera_info['datetime'] }}</td>
                                        </tr>
                                    @endif
                                    @if(isset($sitePhoto->camera_info['gps_latitude']) && isset($sitePhoto->camera_info['gps_longitude']))
                                        <tr>
                                            <td><strong>GPS:</strong></td>
                                            <td>
                                                <small>
                                                    {{ number_format($sitePhoto->camera_info['gps_latitude'], 6) }}, 
                                                    {{ number_format($sitePhoto->camera_info['gps_longitude'], 6) }}
                                                </small>
                                            </td>
                                        </tr>
                                    @endif
                                </table>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Review Modal -->
@if($sitePhoto->submission_status === 'submitted')
    <div class="modal fade" id="reviewModal" tabindex="-1" aria-labelledby="reviewModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="reviewModalLabel">Review Photo</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="{{ route('admin.site-photos.update-review', $sitePhoto) }}">
                    @csrf
                    @method('PATCH')
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Action <span class="text-danger">*</span></label>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="action" value="approve" id="actionApprove" required>
                                <label class="form-check-label text-success" for="actionApprove">
                                    <i class="fas fa-check me-1"></i>Approve Photo
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="action" value="reject" id="actionReject" required>
                                <label class="form-check-label text-danger" for="actionReject">
                                    <i class="fas fa-times me-1"></i>Reject Photo
                                </label>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="admin_comments" class="form-label">Comments (Optional)</label>
                            <textarea class="form-control" id="admin_comments" name="admin_comments" rows="3" 
                                      placeholder="Add your review comments..."></textarea>
                        </div>

                        <div class="mb-3" id="approveFields" style="display: none;">
                            <div class="row">
                                <div class="col-md-6">
                                    <label for="admin_rating" class="form-label">Rating (Optional)</label>
                                    <select class="form-select" id="admin_rating" name="admin_rating">
                                        <option value="">No Rating</option>
                                        <option value="1">1 Star</option>
                                        <option value="2">2 Stars</option>
                                        <option value="3">3 Stars</option>
                                        <option value="4">4 Stars</option>
                                        <option value="5">5 Stars</option>
                                    </select>
                                </div>
                            </div>
                            <div class="row mt-3">
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="is_featured" name="is_featured">
                                        <label class="form-check-label" for="is_featured">
                                            Mark as Featured
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="is_public" name="is_public">
                                        <label class="form-check-label" for="is_public">
                                            Make Public
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3" id="rejectFields" style="display: none;">
                            <label for="rejection_reason" class="form-label">Rejection Reason <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="rejection_reason" name="rejection_reason" rows="3" 
                                      placeholder="Please provide a reason for rejection..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" id="submitReview">Submit Review</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endif

<!-- Comment Modal -->
<div class="modal fade" id="commentModal" tabindex="-1" aria-labelledby="commentModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="commentModalLabel">Add Comment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="{{ route('admin.site-photos.add-comment', $sitePhoto) }}">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="comment" class="form-label">Comment <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="comment" name="comment" rows="4" 
                                  placeholder="Enter your comment..." required></textarea>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="is_internal" name="is_internal">
                        <label class="form-check-label" for="is_internal">
                            Internal comment (not visible to site coordinator)
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Comment</button>
                </div>
            </form>
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

function toggleFeatured(isFeatured) {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '{{ route("admin.site-photos.toggle-feature", $sitePhoto) }}';
    
    const csrfToken = document.createElement('input');
    csrfToken.type = 'hidden';
    csrfToken.name = '_token';
    csrfToken.value = '{{ csrf_token() }}';
    
    const featuredInput = document.createElement('input');
    featuredInput.type = 'hidden';
    featuredInput.name = 'is_featured';
    featuredInput.value = isFeatured;
    
    form.appendChild(csrfToken);
    form.appendChild(featuredInput);
    document.body.appendChild(form);
    form.submit();
}

function togglePublic(isPublic) {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '{{ route("admin.site-photos.toggle-public", $sitePhoto) }}';
    
    const csrfToken = document.createElement('input');
    csrfToken.type = 'hidden';
    csrfToken.name = '_token';
    csrfToken.value = '{{ csrf_token() }}';
    
    const publicInput = document.createElement('input');
    publicInput.type = 'hidden';
    publicInput.name = 'is_public';
    publicInput.value = isPublic;
    
    form.appendChild(csrfToken);
    form.appendChild(publicInput);
    document.body.appendChild(form);
    form.submit();
}

function deletePhoto() {
    if (confirm('Are you sure you want to delete this photo? This action cannot be undone.')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route("admin.site-photos.destroy", $sitePhoto) }}';
        
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = '{{ csrf_token() }}';
        
        const methodField = document.createElement('input');
        methodField.type = 'hidden';
        methodField.name = '_method';
        methodField.value = 'DELETE';
        
        form.appendChild(csrfToken);
        form.appendChild(methodField);
        document.body.appendChild(form);
        form.submit();
    }
}
</script>
@endpush