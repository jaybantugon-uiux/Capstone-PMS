@extends('app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1>Bulk Photo Management</h1>
            <p class="text-muted">Review and manage multiple site photos at once</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('pm.site-photos.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-1"></i>Back to Photos
            </a>
            <button type="button" class="btn btn-primary" onclick="selectAllPhotos()">
                <i class="fas fa-check-square me-1"></i>Select All
            </button>
            <button type="button" class="btn btn-outline-secondary" onclick="clearSelection()">
                <i class="fas fa-times me-1"></i>Clear Selection
            </button>
        </div>
    </div>

    <!-- Bulk Action Controls -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="fas fa-cogs me-2"></i>Bulk Actions
                <span id="selectedCount" class="badge bg-primary ms-2">0 selected</span>
            </h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-12">
                    <div class="d-flex gap-2 flex-wrap">
                        <button type="button" class="btn btn-success" onclick="showBulkApproveModal()" disabled id="bulkApproveBtn">
                            <i class="fas fa-check me-1"></i>Bulk Approve
                        </button>
                        <button type="button" class="btn btn-danger" onclick="showBulkRejectModal()" disabled id="bulkRejectBtn">
                            <i class="fas fa-times me-1"></i>Bulk Reject
                        </button>
                        <button type="button" class="btn btn-warning" onclick="showBulkFeatureModal()" disabled id="bulkFeatureBtn">
                            <i class="fas fa-star me-1"></i>Toggle Featured
                        </button>
                        <button type="button" class="btn btn-info" onclick="showBulkPublicModal()" disabled id="bulkPublicBtn">
                            <i class="fas fa-eye me-1"></i>Toggle Public
                        </button>
                        <button type="button" class="btn btn-outline-primary" onclick="exportSelected()" disabled id="exportBtn">
                            <i class="fas fa-download me-1"></i>Export Selected
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Controls -->
    <div class="card mb-4">
        <div class="card-header">
            <h6 class="mb-0">
                <i class="fas fa-filter me-2"></i>Quick Filters
            </h6>
        </div>
        <div class="card-body">
            <div class="d-flex gap-2 flex-wrap">
                <button type="button" class="btn btn-outline-warning btn-sm filter-btn" data-filter="submitted">
                    <i class="fas fa-clock me-1"></i>Pending Review ({{ $stats['submitted'] ?? 0 }})
                </button>
                <button type="button" class="btn btn-outline-success btn-sm filter-btn" data-filter="approved">
                    <i class="fas fa-check me-1"></i>Approved ({{ $stats['approved'] ?? 0 }})
                </button>
                <button type="button" class="btn btn-outline-danger btn-sm filter-btn" data-filter="rejected">
                    <i class="fas fa-times me-1"></i>Rejected ({{ $stats['rejected'] ?? 0 }})
                </button>
                <button type="button" class="btn btn-outline-info btn-sm filter-btn" data-filter="featured">
                    <i class="fas fa-star me-1"></i>Featured ({{ $stats['featured'] ?? 0 }})
                </button>
                <button type="button" class="btn btn-outline-secondary btn-sm filter-btn" data-filter="overdue">
                    <i class="fas fa-exclamation-triangle me-1"></i>Overdue ({{ $stats['overdue_reviews'] ?? 0 }})
                </button>
                <button type="button" class="btn btn-outline-dark btn-sm filter-btn active" data-filter="all">
                    <i class="fas fa-list me-1"></i>All Photos
                </button>
            </div>
        </div>
    </div>

    <!-- Photos Grid -->
    <div class="card">
        <div class="card-header">
            <h6 class="mb-0">
                <span id="photoCount">{{ $photos->total() }}</span> Photos Available for Bulk Actions
            </h6>
        </div>
        <div class="card-body">
            <div class="row" id="photosGrid">
                @forelse($photos as $photo)
                    <div class="col-md-3 col-lg-2 mb-3" data-photo-id="{{ $photo->id }}" data-status="{{ $photo->submission_status }}">
                        <div class="card photo-card h-100" data-photo-id="{{ $photo->id }}">
                            <div class="position-relative">
                                <!-- Selection Checkbox -->
                                <div class="form-check position-absolute top-0 start-0 m-2" style="z-index: 10;">
                                    <input class="form-check-input photo-checkbox" type="checkbox" 
                                           value="{{ $photo->id }}" 
                                           data-status="{{ $photo->submission_status }}"
                                           onchange="updateSelection()">
                                </div>

                                <!-- Photo Thumbnail -->
                                <img src="{{ asset('storage/' . $photo->photo_path) }}" 
                                     class="card-img-top photo-thumbnail" 
                                     alt="{{ $photo->title }}"
                                     style="height: 150px; object-fit: cover;">
                                
                                <!-- Status Badge -->
                                <span class="badge bg-{{ $photo->submission_status_badge_color }} position-absolute top-0 end-0 m-2">
                                    {{ $photo->formatted_submission_status }}
                                </span>

                                <!-- Quick Action Overlay -->
                                <div class="photo-overlay position-absolute bottom-0 start-0 end-0 p-2 d-flex gap-1">
                                    @if($photo->submission_status === 'submitted')
                                        <button type="button" class="btn btn-success btn-xs" onclick="quickApprove('{{ $photo->id }}')">
                                            <i class="fas fa-check"></i>
                                        </button>
                                        <button type="button" class="btn btn-danger btn-xs" onclick="quickReject('{{ $photo->id }}')">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    @endif
                                    <a href="{{ route('pm.site-photos.show', $photo) }}" class="btn btn-primary btn-xs">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </div>
                            </div>
                            
                            <div class="card-body p-2">
                                <h6 class="card-title mb-1" style="font-size: 0.85rem;">{{ Str::limit($photo->title, 25) }}</h6>
                                <small class="text-muted d-block">
                                    <strong>Project:</strong> {{ Str::limit($photo->project->name, 20) }}
                                </small>
                                <small class="text-muted d-block">
                                    <strong>Date:</strong> {{ $photo->formatted_photo_date }}
                                </small>
                                <small class="text-muted d-block">
                                    <strong>By:</strong> {{ $photo->uploader->first_name }} {{ $photo->uploader->last_name }}
                                </small>
                                
                                <!-- Photo Attributes -->
                                <div class="mt-2 d-flex gap-1">
                                    @if($photo->is_featured)
                                        <span class="badge bg-warning" style="font-size: 0.6rem;">
                                            <i class="fas fa-star"></i>
                                        </span>
                                    @endif
                                    @if($photo->is_public)
                                        <span class="badge bg-info" style="font-size: 0.6rem;">
                                            <i class="fas fa-eye"></i>
                                        </span>
                                    @endif
                                    @if($photo->admin_rating)
                                        <span class="badge bg-success" style="font-size: 0.6rem;">
                                            {{ $photo->admin_rating }}★
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12 text-center py-5">
                        <i class="fas fa-camera fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No photos found</h5>
                        <p class="text-muted">No photos match the current filter criteria.</p>
                        <a href="{{ route('pm.site-photos.index') }}" class="btn btn-primary">
                            <i class="fas fa-list me-1"></i>View All Photos
                        </a>
                    </div>
                @endforelse
            </div>

            <!-- Pagination -->
            @if($photos->hasPages())
                <div class="d-flex justify-content-center mt-4">
                    {{ $photos->appends(request()->query())->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Bulk Approve Modal -->
<div class="modal fade" id="bulkApproveModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Bulk Approve Photos</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="bulkApproveForm">
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        You are about to approve <span id="approveCount">0</span> photos.
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Comments (Optional)</label>
                        <textarea name="comments" class="form-control" rows="3" 
                                  placeholder="Add approval comments for all selected photos..."></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-6">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="make_public" id="bulkMakePublic">
                                <label class="form-check-label" for="bulkMakePublic">
                                    Make all photos public
                                </label>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="make_featured" id="bulkMakeFeatured">
                                <label class="form-check-label" for="bulkMakeFeatured">
                                    Mark all as featured
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-success" onclick="submitBulkApprove()">
                        <i class="fas fa-check me-1"></i>Approve Photos
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Bulk Reject Modal -->
<div class="modal fade" id="bulkRejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Bulk Reject Photos</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="bulkRejectForm">
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        You are about to reject <span id="rejectCount">0</span> photos.
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Rejection Reason <span class="text-danger">*</span></label>
                        <textarea name="bulk_rejection_reason" class="form-control" rows="3" 
                                  placeholder="Please provide a reason for rejecting these photos..." required></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Additional Comments (Optional)</label>
                        <textarea name="comments" class="form-control" rows="2" 
                                  placeholder="Additional feedback for the uploaders..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" onclick="submitBulkReject()">
                        <i class="fas fa-times me-1"></i>Reject Photos
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Bulk Feature Toggle Modal -->
<div class="modal fade" id="bulkFeatureModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Toggle Featured Status</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Choose how to update the featured status for <span id="featureCount">0</span> selected photos:</p>
                <div class="d-grid gap-2">
                    <button type="button" class="btn btn-warning" onclick="submitBulkFeature('feature')">
                        <i class="fas fa-star me-1"></i>Mark as Featured
                    </button>
                    <button type="button" class="btn btn-outline-warning" onclick="submitBulkFeature('unfeature')">
                        <i class="far fa-star me-1"></i>Remove Featured
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bulk Public Toggle Modal -->
<div class="modal fade" id="bulkPublicModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Toggle Public Visibility</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Choose how to update the public visibility for <span id="publicCount">0</span> selected photos:</p>
                <div class="d-grid gap-2">
                    <button type="button" class="btn btn-info" onclick="submitBulkPublic('make_public')">
                        <i class="fas fa-eye me-1"></i>Make Public
                    </button>
                    <button type="button" class="btn btn-outline-info" onclick="submitBulkPublic('make_private')">
                        <i class="fas fa-eye-slash me-1"></i>Make Private
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
.photo-card {
    transition: transform 0.2s, box-shadow 0.2s;
    cursor: pointer;
}

.photo-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.photo-overlay {
    background: linear-gradient(transparent, rgba(0,0,0,0.8));
    opacity: 0;
    transition: opacity 0.2s;
}

.photo-card:hover .photo-overlay {
    opacity: 1;
}

.btn-xs {
    padding: 0.125rem 0.25rem;
    font-size: 0.7rem;
    line-height: 1;
    border-radius: 0.2rem;
}

.filter-btn.active {
    background-color: var(--bs-primary);
    color: white;
    border-color: var(--bs-primary);
}

.photo-checkbox {
    transform: scale(1.2);
}

.photo-checkbox:checked {
    background-color: #0d6efd;
    border-color: #0d6efd;
}
</style>
@endpush

@push('scripts')
<script>
let selectedPhotos = [];

document.addEventListener('DOMContentLoaded', function() {
    // Initialize filter buttons
    initializeFilters();
    updateSelection();
});

function initializeFilters() {
    document.querySelectorAll('.filter-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            // Remove active class from all buttons
            document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
            // Add active class to clicked button
            this.classList.add('active');
            
            const filter = this.dataset.filter;
            filterPhotos(filter);
        });
    });
}

function filterPhotos(filter) {
    const photos = document.querySelectorAll('[data-photo-id]');
    let visibleCount = 0;
    
    photos.forEach(photo => {
        const status = photo.dataset.status;
        let show = false;
        
        switch(filter) {
            case 'all':
                show = true;
                break;
            case 'submitted':
                show = status === 'submitted';
                break;
            case 'approved':
                show = status === 'approved';
                break;
            case 'rejected':
                show = status === 'rejected';
                break;
            case 'featured':
                show = photo.querySelector('.fa-star') !== null;
                break;
            case 'overdue':
                // You might need to add data attribute for overdue status
                show = status === 'submitted'; // Simplified for now
                break;
        }
        
        if (show) {
            photo.style.display = 'block';
            visibleCount++;
        } else {
            photo.style.display = 'none';
            // Uncheck hidden photos
            const checkbox = photo.querySelector('.photo-checkbox');
            if (checkbox && checkbox.checked) {
                checkbox.checked = false;
            }
        }
    });
    
    document.getElementById('photoCount').textContent = visibleCount;
    updateSelection();
}

function selectAllPhotos() {
    const visibleCheckboxes = document.querySelectorAll('[data-photo-id]:not([style*="display: none"]) .photo-checkbox');
    visibleCheckboxes.forEach(checkbox => {
        checkbox.checked = true;
    });
    updateSelection();
}

function clearSelection() {
    document.querySelectorAll('.photo-checkbox').forEach(checkbox => {
        checkbox.checked = false;
    });
    updateSelection();
}

function updateSelection() {
    selectedPhotos = Array.from(document.querySelectorAll('.photo-checkbox:checked')).map(cb => cb.value);
    const count = selectedPhotos.length;
    
    // Update counter
    document.getElementById('selectedCount').textContent = `${count} selected`;
    
    // Enable/disable buttons
    const buttons = ['bulkApproveBtn', 'bulkRejectBtn', 'bulkFeatureBtn', 'bulkPublicBtn', 'exportBtn'];
    buttons.forEach(btnId => {
        const btn = document.getElementById(btnId);
        if (btn) {
            btn.disabled = count === 0;
        }
    });
    
    // Update approve button - only enable if submitted photos are selected
    const submittedSelected = Array.from(document.querySelectorAll('.photo-checkbox:checked'))
        .filter(cb => cb.dataset.status === 'submitted').length;
    
    const approveBtn = document.getElementById('bulkApproveBtn');
    const rejectBtn = document.getElementById('bulkRejectBtn');
    
    if (approveBtn) approveBtn.disabled = submittedSelected === 0;
    if (rejectBtn) rejectBtn.disabled = submittedSelected === 0;
}

function showBulkApproveModal() {
    const submittedCount = Array.from(document.querySelectorAll('.photo-checkbox:checked'))
        .filter(cb => cb.dataset.status === 'submitted').length;
    
    document.getElementById('approveCount').textContent = submittedCount;
    const modal = new bootstrap.Modal(document.getElementById('bulkApproveModal'));
    modal.show();
}

function showBulkRejectModal() {
    const submittedCount = Array.from(document.querySelectorAll('.photo-checkbox:checked'))
        .filter(cb => cb.dataset.status === 'submitted').length;
    
    document.getElementById('rejectCount').textContent = submittedCount;
    const modal = new bootstrap.Modal(document.getElementById('bulkRejectModal'));
    modal.show();
}

function showBulkFeatureModal() {
    document.getElementById('featureCount').textContent = selectedPhotos.length;
    const modal = new bootstrap.Modal(document.getElementById('bulkFeatureModal'));
    modal.show();
}

function showBulkPublicModal() {
    document.getElementById('publicCount').textContent = selectedPhotos.length;
    const modal = new bootstrap.Modal(document.getElementById('bulkPublicModal'));
    modal.show();
}

function submitBulkApprove() {
    const form = document.getElementById('bulkApproveForm');
    const formData = new FormData(form);
    
    const submittedPhotos = Array.from(document.querySelectorAll('.photo-checkbox:checked'))
        .filter(cb => cb.dataset.status === 'submitted')
        .map(cb => cb.value);
    
    if (submittedPhotos.length === 0) {
        alert('No submitted photos selected for approval.');
        return;
    }
    
    const data = {
        action: 'approve',
        photo_ids: submittedPhotos,
        comments: formData.get('comments'),
        make_public: formData.has('make_public'),
        make_featured: formData.has('make_featured')
    };
    
    performBulkAction(data, 'bulkApproveModal');
}

function submitBulkReject() {
    const form = document.getElementById('bulkRejectForm');
    const formData = new FormData(form);
    const reason = formData.get('bulk_rejection_reason');
    
    if (!reason || !reason.trim()) {
        alert('Please provide a rejection reason.');
        return;
    }
    
    const submittedPhotos = Array.from(document.querySelectorAll('.photo-checkbox:checked'))
        .filter(cb => cb.dataset.status === 'submitted')
        .map(cb => cb.value);
    
    if (submittedPhotos.length === 0) {
        alert('No submitted photos selected for rejection.');
        return;
    }
    
    const data = {
        action: 'reject',
        photo_ids: submittedPhotos,
        bulk_rejection_reason: reason,
        comments: formData.get('comments')
    };
    
    performBulkAction(data, 'bulkRejectModal');
}

function submitBulkFeature(action) {
    const data = {
        action: action,
        photo_ids: selectedPhotos
    };
    
    performBulkAction(data, 'bulkFeatureModal');
}

function submitBulkPublic(action) {
    const data = {
        action: action,
        photo_ids: selectedPhotos
    };
    
    performBulkAction(data, 'bulkPublicModal');
}

function performBulkAction(data, modalId) {
    fetch('/pm/site-photos/bulk-action', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(result => {
        if (result.success || response.ok) {
            bootstrap.Modal.getInstance(document.getElementById(modalId)).hide();
            location.reload();
        } else {
            alert('Error: ' + (result.message || 'Bulk action failed'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error performing bulk action');
    });
}

function quickApprove(photoId) {
    if (confirm('Are you sure you want to approve this photo?')) {
        fetch(`/pm/site-photos/${photoId}/quick-approve`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                comments: 'Quick approved from bulk management',
                make_public: true
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success || response.ok) {
                location.reload();
            } else {
                alert('Error approving photo');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error approving photo');
        });
    }
}

function quickReject(photoId) {
    const reason = prompt('Please provide a reason for rejection:');
    if (reason && reason.trim()) {
        fetch(`/pm/site-photos/${photoId}/quick-reject`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                reason: reason
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success || response.ok) {
                location.reload();
            } else {
                alert('Error rejecting photo');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error rejecting photo');
        });
    }
}

function exportSelected() {
    if (selectedPhotos.length === 0) {
        alert('Please select photos to export.');
        return;
    }
    
    const params = new URLSearchParams();
    selectedPhotos.forEach(id => params.append('photo_ids[]', id));
    
    window.open(`/pm/site-photos/export?${params.toString()}`, '_blank');
}
</script>
@endpush
@endsection