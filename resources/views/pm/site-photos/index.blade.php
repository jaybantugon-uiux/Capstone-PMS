@extends('app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1>Site Photos Management</h1>
            <p class="text-muted">Review and manage site photos from your projects</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('pm.site-photos.export') }}" class="btn btn-outline-success">
                <i class="fas fa-download me-1"></i>Export Photos
            </a>
            <a href="{{ route('photos.featured') }}" class="btn btn-info">
                <i class="fas fa-star me-1"></i>Featured Gallery
            </a>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-2">
            <div class="card bg-primary text-white">
                <div class="card-body text-center">
                    <i class="fas fa-images fa-2x mb-2"></i>
                    <h4>{{ $stats['total'] ?? 0 }}</h4>
                    <small>Total Photos</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-warning text-white">
                <div class="card-body text-center">
                    <i class="fas fa-clock fa-2x mb-2"></i>
                    <h4>{{ $stats['pending_review'] ?? 0 }}</h4>
                    <small>Pending Review</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-success text-white">
                <div class="card-body text-center">
                    <i class="fas fa-check-circle fa-2x mb-2"></i>
                    <h4>{{ $stats['approved'] ?? 0 }}</h4>
                    <small>Approved</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-danger text-white">
                <div class="card-body text-center">
                    <i class="fas fa-times-circle fa-2x mb-2"></i>
                    <h4>{{ $stats['rejected'] ?? 0 }}</h4>
                    <small>Rejected</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-info text-white">
                <div class="card-body text-center">
                    <i class="fas fa-star fa-2x mb-2"></i>
                    <h4>{{ $stats['featured'] ?? 0 }}</h4>
                    <small>Featured</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-secondary text-white">
                <div class="card-body text-center">
                    <i class="fas fa-eye fa-2x mb-2"></i>
                    <h4>{{ $stats['public'] ?? 0 }}</h4>
                    <small>Public</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Overdue Reviews Alert -->
    @if(isset($stats['overdue_reviews']) && $stats['overdue_reviews'] > 0)
        <div class="alert alert-warning">
            <div class="d-flex align-items-center">
                <i class="fas fa-exclamation-triangle fa-2x me-3"></i>
                <div class="flex-grow-1">
                    <strong>Overdue Photo Reviews!</strong><br>
                    {{ $stats['overdue_reviews'] }} photos have been waiting for review for more than 3 days.
                </div>
                <a href="{{ route('pm.site-photos.index', ['status' => 'submitted']) }}" class="btn btn-warning btn-sm">
                    <i class="fas fa-eye me-1"></i>Review Now
                </a>
            </div>
        </div>
    @endif

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="fas fa-filter me-2"></i>Filters
            </h5>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('pm.site-photos.index') }}">
                <div class="row">
                    <div class="col-md-2">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="">All Statuses</option>
                            <option value="submitted" {{ request('status') === 'submitted' ? 'selected' : '' }}>Pending Review</option>
                            <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                            <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Project</label>
                        <select name="project_id" class="form-select">
                            <option value="">All Projects</option>
                            @foreach($projects as $project)
                                <option value="{{ $project->id }}" {{ request('project_id') == $project->id ? 'selected' : '' }}>
                                    {{ $project->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Category</label>
                        <select name="category" class="form-select">
                            <option value="">All Categories</option>
                            <option value="progress" {{ request('category') === 'progress' ? 'selected' : '' }}>Progress</option>
                            <option value="quality" {{ request('category') === 'quality' ? 'selected' : '' }}>Quality</option>
                            <option value="safety" {{ request('category') === 'safety' ? 'selected' : '' }}>Safety</option>
                            <option value="equipment" {{ request('category') === 'equipment' ? 'selected' : '' }}>Equipment</option>
                            <option value="materials" {{ request('category') === 'materials' ? 'selected' : '' }}>Materials</option>
                            <option value="workers" {{ request('category') === 'workers' ? 'selected' : '' }}>Workers</option>
                            <option value="completion" {{ request('category') === 'completion' ? 'selected' : '' }}>Completion</option>
                            <option value="other" {{ request('category') === 'other' ? 'selected' : '' }}>Other</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Uploader</label>
                        <select name="uploader_id" class="form-select">
                            <option value="">All Uploaders</option>
                            @foreach($uploaders as $uploader)
                                <option value="{{ $uploader->id }}" {{ request('uploader_id') == $uploader->id ? 'selected' : '' }}>
                                    {{ $uploader->first_name }} {{ $uploader->last_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Date From</label>
                        <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Date To</label>
                        <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-md-12">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search me-1"></i>Apply Filters
                        </button>
                        <a href="{{ route('pm.site-photos.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times me-1"></i>Clear Filters
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Photos Grid -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Site Photos ({{ $photos->total() }} total)</h5>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-success btn-sm" onclick="bulkApprove()">
                    <i class="fas fa-check-double me-1"></i>Bulk Approve
                </button>
                <button type="button" class="btn btn-warning btn-sm" onclick="bulkReject()">
                    <i class="fas fa-times me-1"></i>Bulk Reject
                </button>
            </div>
        </div>
        <div class="card-body">
            @if($photos->count() > 0)
                <div class="row">
                    @foreach($photos as $photo)
                        <div class="col-md-4 col-lg-3 mb-4">
                            <div class="card h-100 photo-card" data-photo-id="{{ $photo->id }}">
                                <div class="position-relative">
                                    <img src="{{ asset('storage/' . $photo->photo_path) }}" 
                                         class="card-img-top photo-thumbnail" 
                                         alt="{{ $photo->title }}"
                                         style="height: 200px; object-fit: cover; cursor: pointer;"
                                         onclick="showPhotoModal('{{ $photo->id }}')">
                                    
                                    <!-- Status Badge -->
                                    <span class="badge bg-{{ $photo->submission_status_badge_color }} position-absolute top-0 start-0 m-2">
                                        {{ $photo->formatted_submission_status }}
                                    </span>
                                    
                                    <!-- Category Badge -->
                                    <span class="badge bg-{{ $photo->photo_category_badge_color }} position-absolute top-0 end-0 m-2">
                                        {{ $photo->formatted_photo_category }}
                                    </span>
                                    
                                    <!-- Featured/Public Icons -->
                                    @if($photo->is_featured)
                                        <i class="fas fa-star text-warning position-absolute" style="bottom: 5px; left: 5px; font-size: 1.2em;"></i>
                                    @endif
                                    @if($photo->is_public)
                                        <i class="fas fa-eye text-info position-absolute" style="bottom: 5px; {{ $photo->is_featured ? 'left: 25px' : 'left: 5px' }}; font-size: 1.2em;"></i>
                                    @endif
                                    
                                    <!-- Selection Checkbox -->
                                    <div class="form-check position-absolute" style="bottom: 5px; right: 5px;">
                                        <input class="form-check-input photo-select" type="checkbox" value="{{ $photo->id }}">
                                    </div>
                                </div>
                                
                                <div class="card-body p-3">
                                    <h6 class="card-title mb-2">{{ Str::limit($photo->title, 30) }}</h6>
                                    <p class="card-text small text-muted mb-2">
                                        <strong>Project:</strong> {{ $photo->project->name }}<br>
                                        <strong>Uploader:</strong> {{ $photo->uploader->first_name }} {{ $photo->uploader->last_name }}<br>
                                        <strong>Date:</strong> {{ $photo->formatted_photo_date }}
                                    </p>
                                    
                                    @if($photo->admin_rating)
                                        <div class="mb-2">
                                            <small class="text-muted">Rating:</small>
                                            <div class="d-inline-block">
                                                @for($i = 1; $i <= 5; $i++)
                                                    <i class="fas fa-star {{ $i <= $photo->admin_rating ? 'text-warning' : 'text-muted' }}"></i>
                                                @endfor
                                            </div>
                                        </div>
                                    @endif
                                    
                                    <div class="d-flex gap-1">
                                        <a href="{{ route('pm.site-photos.show', $photo) }}" class="btn btn-primary btn-sm flex-grow-1">
                                            <i class="fas fa-eye me-1"></i>View
                                        </a>
                                        
                                        @if($photo->submission_status === 'submitted')
                                            <button type="button" class="btn btn-success btn-sm" onclick="quickApprove('{{ $photo->id }}')">
                                                <i class="fas fa-check"></i>
                                            </button>
                                            <button type="button" class="btn btn-danger btn-sm" onclick="quickReject('{{ $photo->id }}')">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                
                <!-- Pagination -->
                <div class="d-flex justify-content-center">
                    {{ $photos->appends(request()->query())->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-camera fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No photos found</h5>
                    <p class="text-muted">No site photos match your current filter criteria.</p>
                    <a href="{{ route('pm.site-photos.index') }}" class="btn btn-primary">
                        <i class="fas fa-refresh me-1"></i>Reset Filters
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Photo Modal -->
<div class="modal fade" id="photoModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Photo Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="photoModalContent">
                    <!-- Content will be loaded via AJAX -->
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Review Modals -->
<div class="modal fade" id="quickApproveModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Quick Approve Photo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="quickApproveForm">
                    <input type="hidden" id="approvePhotoId">
                    <div class="mb-3">
                        <label class="form-label">Comments (Optional)</label>
                        <textarea name="comments" class="form-control" rows="3" placeholder="Add approval comments..."></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Rating (Optional)</label>
                        <select name="rating" class="form-select">
                            <option value="">No Rating</option>
                            <option value="5">★★★★★ Excellent</option>
                            <option value="4">★★★★☆ Good</option>
                            <option value="3">★★★☆☆ Average</option>
                            <option value="2">★★☆☆☆ Poor</option>
                            <option value="1">★☆☆☆☆ Very Poor</option>
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-6">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="make_public" id="makePublic">
                                <label class="form-check-label" for="makePublic">
                                    Make Public
                                </label>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="make_featured" id="makeFeatured">
                                <label class="form-check-label" for="makeFeatured">
                                    Mark as Featured
                                </label>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" onclick="submitQuickApprove()">Approve Photo</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="quickRejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Quick Reject Photo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="quickRejectForm">
                    <input type="hidden" id="rejectPhotoId">
                    <div class="mb-3">
                        <label class="form-label">Rejection Reason <span class="text-danger">*</span></label>
                        <textarea name="reason" class="form-control" rows="3" placeholder="Please provide a reason for rejection..." required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Additional Comments (Optional)</label>
                        <textarea name="comments" class="form-control" rows="2" placeholder="Additional feedback..."></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" onclick="submitQuickReject()">Reject Photo</button>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
.photo-card {
    transition: transform 0.2s, box-shadow 0.2s;
}

.photo-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.photo-thumbnail:hover {
    opacity: 0.9;
}

.form-check-input:checked {
    background-color: #0d6efd;
    border-color: #0d6efd;
}

.badge {
    font-size: 0.75em;
}

.position-absolute i {
    text-shadow: 1px 1px 2px rgba(0,0,0,0.5);
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Select all functionality
    document.getElementById('selectAll')?.addEventListener('change', function() {
        const checkboxes = document.querySelectorAll('.photo-select');
        checkboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
    });
});

function showPhotoModal(photoId) {
    const modal = new bootstrap.Modal(document.getElementById('photoModal'));
    
    // Load photo details via AJAX
    fetch(`/pm/site-photos/${photoId}`)
        .then(response => response.text())
        .then(html => {
            document.getElementById('photoModalContent').innerHTML = html;
            modal.show();
        })
        .catch(error => {
            console.error('Error loading photo details:', error);
            alert('Error loading photo details');
        });
}

function quickApprove(photoId) {
    document.getElementById('approvePhotoId').value = photoId;
    const modal = new bootstrap.Modal(document.getElementById('quickApproveModal'));
    modal.show();
}

function quickReject(photoId) {
    document.getElementById('rejectPhotoId').value = photoId;
    const modal = new bootstrap.Modal(document.getElementById('quickRejectModal'));
    modal.show();
}

function submitQuickApprove() {
    const form = document.getElementById('quickApproveForm');
    const formData = new FormData(form);
    const photoId = document.getElementById('approvePhotoId').value;
    
    fetch(`/pm/site-photos/${photoId}/quick-approve`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            comments: formData.get('comments'),
            rating: formData.get('rating'),
            make_public: formData.has('make_public'),
            make_featured: formData.has('make_featured')
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success || response.ok) {
            bootstrap.Modal.getInstance(document.getElementById('quickApproveModal')).hide();
            location.reload();
        } else {
            alert('Error approving photo: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error approving photo');
    });
}

function submitQuickReject() {
    const form = document.getElementById('quickRejectForm');
    const formData = new FormData(form);
    const photoId = document.getElementById('rejectPhotoId').value;
    const reason = formData.get('reason');
    
    if (!reason.trim()) {
        alert('Please provide a reason for rejection');
        return;
    }
    
    fetch(`/pm/site-photos/${photoId}/quick-reject`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            reason: reason,
            comments: formData.get('comments')
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success || response.ok) {
            bootstrap.Modal.getInstance(document.getElementById('quickRejectModal')).hide();
            location.reload();
        } else {
            alert('Error rejecting photo: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error rejecting photo');
    });
}

function bulkApprove() {
    const selectedPhotos = Array.from(document.querySelectorAll('.photo-select:checked')).map(cb => cb.value);
    
    if (selectedPhotos.length === 0) {
        alert('Please select photos to approve');
        return;
    }
    
    if (confirm(`Are you sure you want to approve ${selectedPhotos.length} selected photos?`)) {
        fetch('/pm/site-photos/bulk-action', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                action: 'approve',
                photo_ids: selectedPhotos
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success || response.ok) {
                location.reload();
            } else {
                alert('Error during bulk approval: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error during bulk approval');
        });
    }
}

function bulkReject() {
    const selectedPhotos = Array.from(document.querySelectorAll('.photo-select:checked')).map(cb => cb.value);
    
    if (selectedPhotos.length === 0) {
        alert('Please select photos to reject');
        return;
    }
    
    const reason = prompt(`Please provide a reason for rejecting ${selectedPhotos.length} photos:`);
    if (!reason || !reason.trim()) {
        alert('Rejection reason is required');
        return;
    }
    
    fetch('/pm/site-photos/bulk-action', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            action: 'reject',
            photo_ids: selectedPhotos,
            bulk_rejection_reason: reason
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success || response.ok) {
            location.reload();
        } else {
            alert('Error during bulk rejection: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error during bulk rejection');
    });
}
</script>
@endpush
@endsection