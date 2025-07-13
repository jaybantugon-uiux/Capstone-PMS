
@extends('app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h2 mb-1">My Site Photos</h1>
                    <p class="text-muted mb-0">Manage and track your uploaded site photos</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('sc.site-photos.create') }}" class="btn btn-primary">
                        <i class="fas fa-camera me-1"></i> Upload Photo
                    </a>
                    <a href="{{ route('sc.dashboard') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Back to Dashboard
                    </a>
                </div>
            </div>

            <!-- Statistics Cards -->
            @if(isset($stats))
            <div class="row mb-4">
                <div class="col-lg-2 col-md-4 col-6">
                    <div class="card text-center">
                        <div class="card-body py-3">
                            <h4 class="text-primary mb-1">{{ $stats['total'] }}</h4>
                            <small class="text-muted">Total Photos</small>
                        </div>
                    </div>
                </div>
                <div class="col-lg-2 col-md-4 col-6">
                    <div class="card text-center">
                        <div class="card-body py-3">
                            <h4 class="text-warning mb-1">{{ $stats['submitted'] ?? 0 }}</h4>
                            <small class="text-muted">Under Review</small>
                        </div>
                    </div>
                </div>
                <div class="col-lg-2 col-md-4 col-6">
                    <div class="card text-center">
                        <div class="card-body py-3">
                            <h4 class="text-success mb-1">{{ $stats['approved'] ?? 0 }}</h4>
                            <small class="text-muted">Approved</small>
                        </div>
                    </div>
                </div>
                <div class="col-lg-2 col-md-4 col-6">
                    <div class="card text-center">
                        <div class="card-body py-3">
                            <h4 class="text-danger mb-1">{{ $stats['rejected'] ?? 0 }}</h4>
                            <small class="text-muted">Rejected</small>
                        </div>
                    </div>
                </div>
                <div class="col-lg-2 col-md-4 col-6">
                    <div class="card text-center">
                        <div class="card-body py-3">
                            <h4 class="text-warning mb-1">{{ $stats['featured'] ?? 0 }}</h4>
                            <small class="text-muted">Featured</small>
                        </div>
                    </div>
                </div>
                <div class="col-lg-2 col-md-4 col-6">
                    <div class="card text-center">
                        <div class="card-body py-3">
                            <h4 class="text-info mb-1">{{ $stats['public'] ?? 0 }}</h4>
                            <small class="text-muted">Public</small>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Filters -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-3">
                            <label for="status" class="form-label">Status</label>
                            <select name="status" id="status" class="form-select">
                                <option value="">All Statuses</option>
                                <option value="submitted" {{ request('status') == 'submitted' ? 'selected' : '' }}>Under Review</option>
                                <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                                <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="project_id" class="form-label">Project</label>
                            <select name="project_id" id="project_id" class="form-select">
                                <option value="">All Projects</option>
                                @foreach($projects as $project)
                                    <option value="{{ $project->id }}" {{ request('project_id') == $project->id ? 'selected' : '' }}>
                                        {{ $project->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="category" class="form-label">Category</label>
                            <select name="category" id="category" class="form-select">
                                <option value="">All Categories</option>
                                <option value="progress" {{ request('category') == 'progress' ? 'selected' : '' }}>Progress</option>
                                <option value="quality" {{ request('category') == 'quality' ? 'selected' : '' }}>Quality</option>
                                <option value="safety" {{ request('category') == 'safety' ? 'selected' : '' }}>Safety</option>
                                <option value="equipment" {{ request('category') == 'equipment' ? 'selected' : '' }}>Equipment</option>
                                <option value="materials" {{ request('category') == 'materials' ? 'selected' : '' }}>Materials</option>
                                <option value="workers" {{ request('category') == 'workers' ? 'selected' : '' }}>Workers</option>
                                <option value="documentation" {{ request('category') == 'documentation' ? 'selected' : '' }}>Documentation</option>
                                <option value="issues" {{ request('category') == 'issues' ? 'selected' : '' }}>Issues</option>
                                <option value="completion" {{ request('category') == 'completion' ? 'selected' : '' }}>Completion</option>
                                <option value="other" {{ request('category') == 'other' ? 'selected' : '' }}>Other</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="date_from" class="form-label">From Date</label>
                            <input type="date" name="date_from" id="date_from" class="form-control" value="{{ request('date_from') }}">
                        </div>
                        <div class="col-md-2">
                            <label for="date_to" class="form-label">To Date</label>
                            <input type="date" name="date_to" id="date_to" class="form-control" value="{{ request('date_to') }}">
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-filter me-1"></i> Apply Filters
                            </button>
                            <a href="{{ route('sc.site-photos.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-1"></i> Clear Filters
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Photos Grid -->
            @if($photos->count() > 0)
                <div class="row">
                    @foreach($photos as $photo)
                        <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                            <div class="card h-100 photo-card">
                                <!-- Photo Thumbnail -->
                                <div class="photo-thumbnail-container">
                                    <img src="{{ $photo->thumbnail_url }}" 
                                         alt="{{ $photo->title }}" 
                                         class="card-img-top photo-thumbnail"
                                         onclick="openPhotoModal('{{ $photo->photo_url }}', '{{ $photo->title }}')">
                                    
                                    <!-- Status Badge -->
                                    <div class="photo-status-badge">
                                        <span class="badge bg-{{ $photo->submission_status_badge_color }}">
                                            {{ $photo->formatted_submission_status }}
                                        </span>
                                    </div>

                                    <!-- Featured Badge -->
                                    @if($photo->is_featured)
                                        <div class="photo-featured-badge">
                                            <span class="badge bg-warning">
                                                <i class="fas fa-star"></i> Featured
                                            </span>
                                        </div>
                                    @endif

                                    <!-- Category Badge -->
                                    <div class="photo-category-badge">
                                        <span class="badge bg-{{ $photo->photo_category_badge_color }}">
                                            {{ $photo->formatted_photo_category }}
                                        </span>
                                    </div>
                                </div>

                                <!-- Card Body -->
                                <div class="card-body d-flex flex-column">
                                    <h6 class="card-title mb-2">{{ Str::limit($photo->title, 40) }}</h6>
                                    
                                    <div class="photo-details mb-2">
                                        <small class="text-muted d-block">
                                            <i class="fas fa-project-diagram me-1"></i>
                                            {{ $photo->project->name }}
                                        </small>
                                        
                                        @if($photo->task)
                                            <small class="text-muted d-block">
                                                <i class="fas fa-tasks me-1"></i>
                                                {{ Str::limit($photo->task->task_name, 30) }}
                                            </small>
                                        @endif
                                        
                                        <small class="text-muted d-block">
                                            <i class="fas fa-calendar me-1"></i>
                                            {{ $photo->formatted_photo_date }}
                                        </small>

                                        @if($photo->location)
                                            <small class="text-muted d-block">
                                                <i class="fas fa-map-marker-alt me-1"></i>
                                                {{ Str::limit($photo->location, 25) }}
                                            </small>
                                        @endif
                                    </div>

                                    <!-- Rating Display -->
                                    @if($photo->admin_rating)
                                        <div class="photo-rating mb-2">
                                            <small class="text-muted">Rating: </small>
                                            {!! $photo->rating_stars !!}
                                        </div>
                                    @endif

                                    <!-- Action Buttons -->
                                    <div class="mt-auto">
                                        <div class="btn-group w-100" role="group">
                                            <a href="{{ route('sc.site-photos.show', $photo) }}" 
                                               class="btn btn-outline-primary btn-sm">
                                                <i class="fas fa-eye"></i> View
                                            </a>
                                            
                                            @if(in_array($photo->submission_status, ['draft', 'rejected']))
                                                <a href="{{ route('sc.site-photos.edit', $photo) }}" 
                                                   class="btn btn-outline-warning btn-sm">
                                                    <i class="fas fa-edit"></i> Edit
                                                </a>

                                                <button type="button" 
                                            class="btn btn-outline-danger btn-sm" 
                                            onclick="confirmDelete({{ $photo->id }}, '{{ addslashes($photo->title) }}')">
                                            <i class="fas fa-trash"></i> Delete
                                            </button>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <!-- Card Footer with submission info -->
                                <div class="card-footer bg-light">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <small class="text-muted">
                                            @if($photo->submitted_at)
                                                Submitted: {{ $photo->submitted_at->format('M d') }}
                                            @else
                                                Not submitted
                                            @endif
                                        </small>
                                        
                                        @if($photo->reviewed_at)
                                            <small class="text-muted">
                                                Reviewed: {{ $photo->reviewed_at->format('M d') }}
                                            </small>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-center mt-4">
                    {{ $photos->appends(request()->query())->links() }}
                </div>
            @else
                <!-- Empty State -->
                <div class="text-center py-5">
                    <i class="fas fa-camera fa-4x text-muted mb-3"></i>
                    <h4 class="text-muted">No Photos Found</h4>
                    <p class="text-muted mb-4">
                        @if(request()->hasAny(['status', 'project_id', 'category', 'date_from', 'date_to']))
                            No photos match your current filters. Try adjusting your search criteria.
                        @else
                            You haven't uploaded any site photos yet. Start documenting your project progress!
                        @endif
                    </p>
                    <a href="{{ route('sc.site-photos.create') }}" class="btn btn-primary">
                        <i class="fas fa-camera me-1"></i> Upload Your First Photo
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>
<!-- Delete Photo Confirmation Modal -->
<div class="modal fade" id="deletePhotoModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-exclamation-triangle text-warning me-2"></i>
                    Confirm Delete
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this photo?</p>
                <div class="alert alert-warning">
                    <strong>Photo:</strong> <span id="deletePhotoTitle"></span><br>
                    <small class="text-muted">This action cannot be undone. The photo file will be permanently removed from storage.</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i> Cancel
                </button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn" onclick="deletePhoto()">
                    <i class="fas fa-trash me-1"></i> Delete Photo
                    <span class="spinner-border spinner-border-sm ms-2 d-none" id="deleteSpinner"></span>
                </button>
            </div>
        </div>
    </div>
</div>
<!-- Photo Modal -->
<div class="modal fade" id="photoModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="photoModalTitle">Photo Preview</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <img id="photoModalImage" src="" alt="" class="img-fluid">
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.photo-card {
    transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
}

.photo-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.photo-thumbnail-container {
    position: relative;
    height: 200px;
    overflow: hidden;
}

.photo-thumbnail {
    width: 100%;
    height: 100%;
    object-fit: cover;
    cursor: pointer;
    transition: transform 0.3s ease;
}

.photo-thumbnail:hover {
    transform: scale(1.05);
}

.photo-status-badge {
    position: absolute;
    top: 8px;
    right: 8px;
    z-index: 10;
}

.photo-featured-badge {
    position: absolute;
    top: 8px;
    left: 8px;
    z-index: 10;
}

.photo-category-badge {
    position: absolute;
    bottom: 8px;
    left: 8px;
    z-index: 10;
}

.photo-details small {
    line-height: 1.4;
    margin-bottom: 2px;
}

.photo-rating {
    font-size: 0.9rem;
}

.card-footer {
    font-size: 0.8rem;
    padding: 0.5rem 1rem;
}

.btn-group .btn-sm {
    padding: 0.25rem 0.5rem;
    font-size: 0.8rem;
}

@media (max-width: 768px) {
    .photo-thumbnail-container {
        height: 150px;
    }
    
    .col-sm-6 {
        margin-bottom: 1rem;
    }
    
    .btn-group {
        flex-direction: column;
    }
    
    .btn-group .btn {
        border-radius: 0.25rem !important;
        margin-bottom: 2px;
    }
}

@media (max-width: 576px) {
    .photo-details small {
        font-size: 0.7rem;
    }
    
    .card-title {
        font-size: 0.9rem;
    }
}
</style>
@endpush

@push('scripts')
<script>
function openPhotoModal(imageUrl, title) {
    document.getElementById('photoModalImage').src = imageUrl;
    document.getElementById('photoModalTitle').textContent = title;
    
    const photoModal = new bootstrap.Modal(document.getElementById('photoModal'));
    photoModal.show();
}

// Auto-submit form when filters change (optional)
document.addEventListener('DOMContentLoaded', function() {
    const filterInputs = document.querySelectorAll('#status, #project_id, #category');
    
    filterInputs.forEach(input => {
        input.addEventListener('change', function() {
            // Uncomment the line below if you want auto-submit on filter change
            // this.form.submit();
        });
    });
});
let photoToDelete = null;
let deleteModal = null;

document.addEventListener('DOMContentLoaded', function() {
    deleteModal = new bootstrap.Modal(document.getElementById('deletePhotoModal'));
});

function confirmDelete(photoId, photoTitle) {
    photoToDelete = photoId;
    document.getElementById('deletePhotoTitle').textContent = photoTitle;
    deleteModal.show();
}

function deletePhoto() {
    if (!photoToDelete) return;
    
    const confirmBtn = document.getElementById('confirmDeleteBtn');
    const spinner = document.getElementById('deleteSpinner');
    
    // Show loading state
    confirmBtn.disabled = true;
    spinner.classList.remove('d-none');
    
    // Create form and submit
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = `{{ route('sc.site-photos.index') }}/${photoToDelete}`;
    form.style.display = 'none';
    
    // Add CSRF token
    const csrfToken = document.createElement('input');
    csrfToken.type = 'hidden';
    csrfToken.name = '_token';
    csrfToken.value = '{{ csrf_token() }}';
    form.appendChild(csrfToken);
    
    // Add method override
    const methodField = document.createElement('input');
    methodField.type = 'hidden';
    methodField.name = '_method';
    methodField.value = 'DELETE';
    form.appendChild(methodField);
    
    // Submit form
    document.body.appendChild(form);
    form.submit();
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
        animation: slideInRight 0.3s ease;
    `;
    
    document.body.appendChild(toast);
    
    setTimeout(() => {
        if (document.body.contains(toast)) {
            toast.style.animation = 'slideOutRight 0.3s ease';
            setTimeout(() => {
                if (document.body.contains(toast)) {
                    document.body.removeChild(toast);
                }
            }, 300);
        }
    }, 3000);
}

// Add CSS animations
const style = document.createElement('style');
style.textContent = `
    @keyframes slideInRight {
        from { transform: translateX(100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
    @keyframes slideOutRight {
        from { transform: translateX(0); opacity: 1; }
        to { transform: translateX(100%); opacity: 0; }
    }
`;
document.head.appendChild(style);
</script>
@endpush