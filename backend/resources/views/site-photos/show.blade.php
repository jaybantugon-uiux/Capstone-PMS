{{-- resources/views/sc/site-photos/show.blade.php --}}
@extends('app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h2 mb-1">{{ $sitePhoto->title }}</h1>
                    <p class="text-muted mb-0">
                        <i class="fas fa-project-diagram me-1"></i>
                        {{ $sitePhoto->project->name }}
                        @if($sitePhoto->task)
                            <span class="mx-2">•</span>
                            <i class="fas fa-tasks me-1"></i>
                            {{ $sitePhoto->task->task_name }}
                        @endif
                    </p>
                </div>
                <div class="d-flex gap-2">
    @if(in_array($sitePhoto->submission_status, ['draft', 'rejected']))
        <a href="{{ route('sc.site-photos.edit', $sitePhoto) }}" class="btn btn-warning">
            <i class="fas fa-edit me-1"></i> Edit Photo
        </a>
        
        <button type="button" class="btn btn-danger" onclick="confirmDeletePhoto()">
            <i class="fas fa-trash me-1"></i> Delete Photo
        </button>
    @endif
            <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="{{ route('sc.site-photos.edit', $sitePhoto) }}">
                    <i class="fas fa-edit me-2"></i> Edit
                </a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item text-danger" href="#" onclick="confirmDeletePhoto()">
                    <i class="fas fa-trash me-2"></i> Delete Photo
                </a></li>
            </ul>
        </div>
    
    <a href="{{ route('sc.site-photos.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i> Back to Photos
    </a>
</div>

            <div class="row">
                <!-- Photo Display -->
                <div class="col-lg-8 mb-4">
                    <div class="card">
                        <div class="card-body p-0">
                            <!-- Photo with status overlay -->
                            <div class="position-relative">
                                @if(Storage::disk('public')->exists($sitePhoto->photo_path))
                                    <img src="{{ Storage::url($sitePhoto->photo_path) }}" 
                                         alt="{{ $sitePhoto->title }}" 
                                         class="img-fluid w-100"
                                         style="max-height: 600px; object-fit: contain;">
                                @else
                                    <div class="d-flex align-items-center justify-content-center bg-light" style="height: 400px;">
                                        <div class="text-center text-muted">
                                            <i class="fas fa-image fa-3x mb-3"></i>
                                            <p>Photo not found</p>
                                        </div>
                                    </div>
                                @endif
                                
                                <!-- Status Badge -->
                                <div class="position-absolute top-0 end-0 m-3">
                                    <span class="badge bg-{{ $sitePhoto->submission_status_badge_color ?? 'secondary' }} fs-6">
                                        {{ ucfirst($sitePhoto->submission_status) }}
                                    </span>
                                </div>

                                <!-- Featured Badge -->
                                @if($sitePhoto->is_featured)
                                    <div class="position-absolute top-0 start-0 m-3">
                                        <span class="badge bg-warning fs-6">
                                            <i class="fas fa-star"></i> Featured
                                        </span>
                                    </div>
                                @endif
                            </div>

                            <!-- Photo Actions -->
                            <div class="card-footer bg-light">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <small class="text-muted">
                                            <i class="fas fa-calendar me-1"></i>
                                            Photo taken: {{ $sitePhoto->photo_date ? $sitePhoto->photo_date->format('M d, Y') : 'N/A' }}
                                        </small>
                                    </div>
                                    <div>
                                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="downloadPhoto()">
                                            <i class="fas fa-download me-1"></i> Download
                                        </button>
                                        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="sharePhoto()">
                                            <i class="fas fa-share me-1"></i> Share
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Photo Details -->
                <div class="col-lg-4">
                    <!-- Basic Information -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h6 class="card-title mb-0">
                                <i class="fas fa-info-circle me-2"></i>Photo Details
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-sm-4 text-muted">Category:</div>
                                <div class="col-sm-8">
                                    <span class="badge bg-{{ $sitePhoto->photo_category_badge_color ?? 'secondary' }}">
                                        {{ ucfirst($sitePhoto->photo_category) }}
                                    </span>
                                </div>
                            </div>

                            @if($sitePhoto->description)
                                <div class="row mb-3">
                                    <div class="col-sm-4 text-muted">Description:</div>
                                    <div class="col-sm-8">{{ $sitePhoto->description }}</div>
                                </div>
                            @endif

                            @if($sitePhoto->location)
                                <div class="row mb-3">
                                    <div class="col-sm-4 text-muted">Location:</div>
                                    <div class="col-sm-8">
                                        <i class="fas fa-map-marker-alt me-1"></i>
                                        {{ $sitePhoto->location }}
                                    </div>
                                </div>
                            @endif

                            @if($sitePhoto->weather_conditions)
                                <div class="row mb-3">
                                    <div class="col-sm-4 text-muted">Weather:</div>
                                    <div class="col-sm-8">
                                        @php
                                            $weatherIcons = [
                                                'sunny' => 'fas fa-sun text-warning',
                                                'cloudy' => 'fas fa-cloud text-secondary',
                                                'rainy' => 'fas fa-cloud-rain text-primary',
                                                'stormy' => 'fas fa-bolt text-warning',
                                                'windy' => 'fas fa-wind text-info'
                                            ];
                                            $weatherIcon = $weatherIcons[$sitePhoto->weather_conditions] ?? 'fas fa-question text-muted';
                                        @endphp
                                        <i class="{{ $weatherIcon }} me-1"></i>
                                        {{ ucfirst($sitePhoto->weather_conditions) }}
                                    </div>
                                </div>
                            @endif

                            <div class="row mb-3">
                                <div class="col-sm-4 text-muted">File Size:</div>
                                <div class="col-sm-8">
                                    @php
                                        $bytes = $sitePhoto->file_size;
                                        if ($bytes >= 1073741824) {
                                            $fileSize = number_format($bytes / 1073741824, 2) . ' GB';
                                        } elseif ($bytes >= 1048576) {
                                            $fileSize = number_format($bytes / 1048576, 2) . ' MB';
                                        } elseif ($bytes >= 1024) {
                                            $fileSize = number_format($bytes / 1024, 2) . ' KB';
                                        } else {
                                            $fileSize = $bytes . ' bytes';
                                        }
                                    @endphp
                                    {{ $fileSize }}
                                </div>
                            </div>

                            @if($sitePhoto->camera_info && isset($sitePhoto->camera_info['width'], $sitePhoto->camera_info['height']))
                                <div class="row mb-3">
                                    <div class="col-sm-4 text-muted">Dimensions:</div>
                                    <div class="col-sm-8">{{ $sitePhoto->camera_info['width'] }} × {{ $sitePhoto->camera_info['height'] }}</div>
                                </div>
                            @endif

                            @if($sitePhoto->tags && count($sitePhoto->tags) > 0)
                                <div class="row mb-3">
                                    <div class="col-sm-4 text-muted">Tags:</div>
                                    <div class="col-sm-8">
                                        @foreach($sitePhoto->tags as $tag)
                                            <span class="badge bg-light text-dark me-1 mb-1">{{ $tag }}</span>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            <!-- Visibility Status -->
                            <div class="row mb-0">
                                <div class="col-sm-4 text-muted">Visibility:</div>
                                <div class="col-sm-8">
                                    @if($sitePhoto->is_public)
                                        <span class="badge bg-success">
                                            <i class="fas fa-globe me-1"></i>Public
                                        </span>
                                    @else
                                        <span class="badge bg-secondary">
                                            <i class="fas fa-lock me-1"></i>Private
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Submission & Review Information -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h6 class="card-title mb-0">
                                <i class="fas fa-clock me-2"></i>Submission Status
                            </h6>
                        </div>
                        <div class="card-body">
                            @if($sitePhoto->submitted_at)
                                <div class="row mb-3">
                                    <div class="col-sm-4 text-muted">Submitted:</div>
                                    <div class="col-sm-8">{{ $sitePhoto->submitted_at->format('M d, Y g:i A') }}</div>
                                </div>
                            @endif

                            @if($sitePhoto->reviewed_at)
                                <div class="row mb-3">
                                    <div class="col-sm-4 text-muted">Reviewed:</div>
                                    <div class="col-sm-8">
                                        {{ $sitePhoto->reviewed_at->format('M d, Y g:i A') }}
                                        @if($sitePhoto->reviewer)
                                            <br><small class="text-muted">by {{ $sitePhoto->reviewer->first_name }} {{ $sitePhoto->reviewer->last_name }}</small>
                                        @endif
                                    </div>
                                </div>
                            @endif

                            @if($sitePhoto->admin_rating)
                                <div class="row mb-3">
                                    <div class="col-sm-4 text-muted">Rating:</div>
                                    <div class="col-sm-8">
                                        @for($i = 1; $i <= 5; $i++)
                                            @if($i <= $sitePhoto->admin_rating)
                                                <i class="fas fa-star text-warning"></i>
                                            @else
                                                <i class="far fa-star text-muted"></i>
                                            @endif
                                        @endfor
                                        <span class="ms-2 text-muted">{{ $sitePhoto->admin_rating }}/5</span>
                                    </div>
                                </div>
                            @endif

                            @if($sitePhoto->admin_comments)
                                <div class="row mb-3">
                                    <div class="col-sm-4 text-muted">Admin Comments:</div>
                                    <div class="col-sm-8">
                                        <div class="alert alert-info p-2 mb-0">
                                            {{ $sitePhoto->admin_comments }}
                                        </div>
                                    </div>
                                </div>
                            @endif

                            @if($sitePhoto->rejection_reason)
                                <div class="row mb-0">
                                    <div class="col-sm-4 text-muted">Rejection Reason:</div>
                                    <div class="col-sm-8">
                                        <div class="alert alert-danger p-2 mb-0">
                                            {{ $sitePhoto->rejection_reason }}
                                        </div>
                                    </div>
                                </div>
                            @endif

                            @if($sitePhoto->submission_status === 'submitted')
                                <div class="alert alert-warning p-2 mb-0">
                                    <i class="fas fa-clock me-1"></i>
                                    Your photo is currently under review. You'll be notified once it's been reviewed.
                                </div>
                            @endif
                        </div>
                    </div>
                    <!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-exclamation-triangle text-danger me-2"></i>
                    Confirm Delete
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-4">
                        <img src="{{ $sitePhoto->thumbnail_url }}" 
                             alt="{{ $sitePhoto->title }}" 
                             class="img-thumbnail">
                    </div>
                    <div class="col-md-8">
                        <h6>{{ $sitePhoto->title }}</h6>
                        <p class="text-muted mb-2">{{ $sitePhoto->project->name }}</p>
                        <div class="alert alert-warning">
                            <strong>Warning:</strong> This action cannot be undone. The photo and all associated data will be permanently deleted.
                        </div>
                        
                        @if($sitePhoto->submission_status === 'approved')
                            <div class="alert alert-danger">
                                <strong>Notice:</strong> This is an approved photo. Deleting it may affect project documentation.
                            </div>
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" id="confirmApprovedDelete">
                                <label class="form-check-label" for="confirmApprovedDelete">
                                    I understand this is an approved photo and still want to delete it
                                </label>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i> Cancel
                </button>
                <button type="button" class="btn btn-danger" id="confirmDeleteButton" onclick="deletePhoto()">
                    <i class="fas fa-trash me-1"></i> Delete Photo
                    <span class="spinner-border spinner-border-sm ms-2 d-none" id="deleteSpinner"></span>
                </button>
            </div>
        </div>
    </div>
</div>

                    <!-- Camera Information -->
                    @if($sitePhoto->camera_info && count($sitePhoto->camera_info) > 0)
                        <div class="card mb-4">
                            <div class="card-header">
                                <h6 class="card-title mb-0">
                                    <i class="fas fa-camera me-2"></i>Camera Information
                                </h6>
                            </div>
                            <div class="card-body">
                                @if(isset($sitePhoto->camera_info['camera_make']) && isset($sitePhoto->camera_info['camera_model']))
                                    <div class="row mb-2">
                                        <div class="col-sm-4 text-muted">Camera:</div>
                                        <div class="col-sm-8">
                                            {{ $sitePhoto->camera_info['camera_make'] }} {{ $sitePhoto->camera_info['camera_model'] }}
                                        </div>
                                    </div>
                                @endif

                                @if(isset($sitePhoto->camera_info['datetime']))
                                    <div class="row mb-2">
                                        <div class="col-sm-4 text-muted">Captured:</div>
                                        <div class="col-sm-8">{{ $sitePhoto->camera_info['datetime'] }}</div>
                                    </div>
                                @endif

                                @if(isset($sitePhoto->camera_info['gps_latitude']) && isset($sitePhoto->camera_info['gps_longitude']))
                                    <div class="row mb-0">
                                        <div class="col-sm-4 text-muted">GPS:</div>
                                        <div class="col-sm-8">
                                            <small>
                                                {{ number_format($sitePhoto->camera_info['gps_latitude'], 6) }}, 
                                                {{ number_format($sitePhoto->camera_info['gps_longitude'], 6) }}
                                            </small>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Comments Section -->
            @if($sitePhoto->comments->where('is_internal', false)->count() > 0 || auth()->user()->role === 'sc')
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="card-title mb-0">
                                    <i class="fas fa-comments me-2"></i>Comments ({{ $sitePhoto->comments->where('is_internal', false)->count() }})
                                </h6>
                            </div>
                            <div class="card-body">
                                <!-- Existing Comments -->
                                @forelse($sitePhoto->comments->where('is_internal', false) as $comment)
                                    <div class="comment-item {{ !$loop->last ? 'border-bottom' : '' }} pb-3 {{ !$loop->last ? 'mb-3' : '' }}">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div class="flex-grow-1">
                                                <strong>{{ $comment->user->first_name }} {{ $comment->user->last_name }}</strong>
                                                <small class="text-muted ms-2">{{ $comment->created_at->format('M d, Y g:i A') }}</small>
                                                <p class="mb-0 mt-1">{{ $comment->comment }}</p>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <p class="text-muted mb-3">No comments yet.</p>
                                @endforelse

                                <!-- Add Comment Form -->
                                @if(auth()->user()->role === 'sc')
                                    <form action="{{ route('sc.site-photos.add-comment', $sitePhoto) }}" method="POST" class="mt-3">
                                        @csrf
                                        <div class="mb-3">
                                            <label for="comment" class="form-label">Add a comment</label>
                                            <textarea class="form-control @error('comment') is-invalid @enderror" 
                                                      id="comment" 
                                                      name="comment" 
                                                      rows="3" 
                                                      placeholder="Share your thoughts about this photo..."
                                                      required>{{ old('comment') }}</textarea>
                                            @error('comment')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-comment me-1"></i> Add Comment
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.comment-item {
    font-size: 0.9rem;
}

.card-title {
    color: #495057;
    font-weight: 600;
}

.row.mb-3:last-child {
    margin-bottom: 0 !important;
}

.badge {
    font-size: 0.8rem;
}

.alert.p-2 {
    font-size: 0.9rem;
}

@media (max-width: 768px) {
    .position-absolute {
        position: relative !important;
        top: auto !important;
        end: auto !important;
        start: auto !important;
        margin: 1rem 0 !important;
    }
    
    .d-flex.justify-content-between {
        flex-direction: column;
        gap: 1rem;
    }
    
    .btn {
        width: 100%;
    }
    
    .col-sm-4, .col-sm-8 {
        margin-bottom: 0.5rem;
    }
}
</style>
@endpush

@push('scripts')
<script>
function downloadPhoto() {
    @if(Storage::disk('public')->exists($sitePhoto->photo_path))
        const photoUrl = '{{ Storage::url($sitePhoto->photo_path) }}';
        const photoTitle = '{{ $sitePhoto->title }}';
        
        // Create temporary link element
        const link = document.createElement('a');
        link.href = photoUrl;
        link.download = `${photoTitle}.{{ pathinfo($sitePhoto->photo_path, PATHINFO_EXTENSION) }}`;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    @else
        alert('Photo file not found.');
    @endif
}

function sharePhoto() {
    const photoUrl = window.location.href;
    const photoTitle = '{{ $sitePhoto->title }}';
    
    if (navigator.share) {
        // Use native Web Share API if available
        navigator.share({
            title: photoTitle,
            text: 'Check out this site photo from {{ $sitePhoto->project->name }}',
            url: photoUrl
        }).catch(err => console.log('Error sharing:', err));
    } else {
        // Fallback: copy link to clipboard
        navigator.clipboard.writeText(photoUrl).then(() => {
            // Show success message
            showToast('Link copied to clipboard!', 'success');
        }).catch(err => {
            console.log('Error copying to clipboard:', err);
            alert('Unable to copy link. Please copy the URL manually.');
        });
    }
}

function showToast(message, type = 'success') {
    const toast = document.createElement('div');
    toast.className = 'toast-notification';
    toast.innerHTML = `<i class="fas fa-${type === 'success' ? 'check' : 'exclamation-triangle'} me-2"></i>${message}`;
    toast.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: ${type === 'success' ? '#28a745' : '#dc3545'};
        color: white;
        padding: 12px 20px;
        border-radius: 4px;
        z-index: 9999;
        font-size: 14px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    `;
    
    document.body.appendChild(toast);
    
    setTimeout(() => {
        if (document.body.contains(toast)) {
            document.body.removeChild(toast);
        }
    }, 3000);
}

// Auto-refresh status for photos under review (optional)
@if($sitePhoto->submission_status === 'submitted')
    // Check for status updates every 30 seconds
    let statusCheckInterval = setInterval(() => {
        fetch(window.location.href, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            if (response.ok) {
                return response.json();
            }
            throw new Error('Network response was not ok');
        })
        .then(data => {
            if (data.status && data.status !== 'submitted') {
                // Status has changed, reload the page
                clearInterval(statusCheckInterval);
                window.location.reload();
            }
        })
        .catch(error => {
            // Silently fail - this is just a convenience feature
            console.log('Status check failed:', error);
        });
    }, 30000);
@endif

let deleteModal = null;

document.addEventListener('DOMContentLoaded', function() {
    deleteModal = new bootstrap.Modal(document.getElementById('deleteConfirmModal'));
    
    @if($sitePhoto->submission_status === 'approved')
    // Enable/disable delete button based on confirmation checkbox
    const confirmCheckbox = document.getElementById('confirmApprovedDelete');
    const deleteButton = document.getElementById('confirmDeleteButton');
    
    if (confirmCheckbox) {
        confirmCheckbox.addEventListener('change', function() {
            deleteButton.disabled = !this.checked;
        });
        deleteButton.disabled = true; // Initially disabled for approved photos
    }
    @endif
});

function confirmDeletePhoto() {
    deleteModal.show();
}

function deletePhoto() {
    const confirmBtn = document.getElementById('confirmDeleteButton');
    const spinner = document.getElementById('deleteSpinner');
    
    // Show loading state
    confirmBtn.disabled = true;
    spinner.classList.remove('d-none');
    
    // Create and submit delete form
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '{{ route("sc.site-photos.destroy", $sitePhoto) }}';
    form.style.display = 'none';
    
    // Add CSRF token
    const csrfInput = document.createElement('input');
    csrfInput.type = 'hidden';
    csrfInput.name = '_token';
    csrfInput.value = '{{ csrf_token() }}';
    form.appendChild(csrfInput);
    
    // Add method override
    const methodInput = document.createElement('input');
    methodInput.type = 'hidden';
    methodInput.name = '_method';
    methodInput.value = 'DELETE';
    form.appendChild(methodInput);
    
    @if($sitePhoto->submission_status === 'approved')
    // Add confirmation flag for approved photos
    const confirmInput = document.createElement('input');
    confirmInput.type = 'hidden';
    confirmInput.name = 'confirm_delete';
    confirmInput.value = '1';
    form.appendChild(confirmInput);
    @endif
    
    document.body.appendChild(form);
    form.submit();
}
</script>
@endpush