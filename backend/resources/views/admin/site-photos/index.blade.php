{{-- Admin Site Photos Index - views/admin/site-photos/index.blade.php --}}
@extends('app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h2">Site Photos Management</h1>
                    <p class="text-muted">Review and manage photo submissions from site coordinators</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.site-photos.export', request()->query()) }}" 
                       class="btn btn-outline-success">
                        <i class="fas fa-download me-1"></i>Export CSV
                    </a>
                    <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#bulkActionsModal">
                        <i class="fas fa-cog me-1"></i>Bulk Actions
                    </button>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card border-primary">
                        <div class="card-body text-center">
                            <h3 class="text-primary">{{ $stats['total'] ?? 0 }}</h3>
                            <p class="mb-0">Total Photos</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-warning">
                        <div class="card-body text-center">
                            <h3 class="text-warning">{{ $stats['submitted'] ?? 0 }}</h3>
                            <p class="mb-0">Pending Review</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-success">
                        <div class="card-body text-center">
                            <h3 class="text-success">{{ $stats['approved'] ?? 0 }}</h3>
                            <p class="mb-0">Approved</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-info">
                        <div class="card-body text-center">
                            <h3 class="text-info">{{ $stats['featured'] ?? 0 }}</h3>
                            <p class="mb-0">Featured</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Alert for overdue reviews -->
            @if(($stats['overdue_reviews'] ?? 0) > 0)
                <div class="alert alert-warning mb-4">
                    <i class="fas fa-clock me-2"></i>
                    <strong>{{ $stats['overdue_reviews'] }}</strong> photos are overdue for review (submitted more than 3 days ago).
                    <a href="{{ route('admin.site-photos.index', ['overdue' => '1']) }}" class="alert-link">
                        View overdue photos →
                    </a>
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
                    <form method="GET" action="{{ route('admin.site-photos.index') }}">
                        <div class="row">
                            <div class="col-md-2">
                                <label for="status" class="form-label">Status</label>
                                <select name="status" id="status" class="form-select">
                                    <option value="">All Statuses</option>
                                    <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                                    <option value="submitted" {{ request('status') == 'submitted' ? 'selected' : '' }}>Submitted</option>
                                    <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                                    <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
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
                                <label for="uploader_id" class="form-label">Uploader</label>
                                <select name="uploader_id" id="uploader_id" class="form-select">
                                    <option value="">All Uploaders</option>
                                    @foreach($uploaders as $uploader)
                                        <option value="{{ $uploader->id }}" {{ request('uploader_id') == $uploader->id ? 'selected' : '' }}>
                                            {{ $uploader->first_name }} {{ $uploader->last_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="date_from" class="form-label">From Date</label>
                                <input type="date" name="date_from" id="date_from" class="form-control" 
                                       value="{{ request('date_from') }}">
                            </div>
                            <div class="col-md-2">
                                <label for="date_to" class="form-label">To Date</label>
                                <input type="date" name="date_to" id="date_to" class="form-control" 
                                       value="{{ request('date_to') }}">
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-md-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search me-1"></i>Apply Filters
                                </button>
                                <a href="{{ route('admin.site-photos.index') }}" class="btn btn-outline-secondary ms-2">
                                    <i class="fas fa-times me-1"></i>Clear Filters
                                </a>
                                <div class="form-check form-check-inline ms-3">
                                    <input class="form-check-input" type="checkbox" name="featured" value="1" 
                                           id="featured" {{ request('featured') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="featured">
                                        <i class="fas fa-star text-warning"></i> Featured Only
                                    </label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="checkbox" name="public" value="1" 
                                           id="public" {{ request('public') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="public">
                                        <i class="fas fa-eye text-info"></i> Public Only
                                    </label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="checkbox" name="overdue" value="1" 
                                           id="overdue" {{ request('overdue') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="overdue">
                                        <i class="fas fa-clock text-danger"></i> Overdue Reviews
                                    </label>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Photos Table -->
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            Photos ({{ $photos->total() }} total)
                        </h5>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="selectAll">
                            <label class="form-check-label" for="selectAll">
                                Select All
                            </label>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    @if($photos->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th width="50">
                                            <input type="checkbox" id="selectAllTable">
                                        </th>
                                        <th width="80">Photo</th>
                                        <th>Title & Details</th>
                                        <th>Project</th>
                                        <th>Category</th>
                                        <th>Status</th>
                                        <th>Uploader</th>
                                        <th>Submitted</th>
                                        <th width="200">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($photos as $photo)
                                        <tr class="{{ $photo->is_overdue_for_review ? 'table-warning' : '' }}" 
                                            data-photo-id="{{ $photo->id }}">
                                            <td>
                                                <input type="checkbox" class="photo-checkbox" value="{{ $photo->id }}">
                                            </td>
                                            <td>
                                                <img src="{{ $photo->thumbnail_url }}" 
                                                     alt="Photo thumbnail" 
                                                     class="img-thumbnail cursor-pointer" 
                                                     style="width: 60px; height: 60px; object-fit: cover;"
                                                     onclick="showPhotoModal('{{ $photo->photo_url }}', '{{ $photo->title }}')">
                                            </td>
                                            <td>
                                                <div>
                                                    <strong>{{ $photo->title }}</strong>
                                                    @if($photo->is_featured)
                                                        <span class="badge bg-warning ms-1">
                                                            <i class="fas fa-star"></i> Featured
                                                        </span>
                                                    @endif
                                                    @if($photo->is_public)
                                                        <span class="badge bg-info ms-1">
                                                            <i class="fas fa-eye"></i> Public
                                                        </span>
                                                    @endif
                                                    @if($photo->is_overdue_for_review)
                                                        <span class="badge bg-danger ms-1">
                                                            <i class="fas fa-clock"></i> Overdue
                                                        </span>
                                                    @endif
                                                </div>
                                                @if($photo->description)
                                                    <small class="text-muted d-block">{{ Str::limit($photo->description, 60) }}</small>
                                                @endif
                                                <small class="text-muted">
                                                    <i class="fas fa-calendar me-1"></i>{{ $photo->formatted_photo_date }}
                                                    @if($photo->location)
                                                        • <i class="fas fa-map-marker-alt me-1"></i>{{ Str::limit($photo->location, 20) }}
                                                    @endif
                                                </small>
                                                @if($photo->admin_rating)
                                                    <div class="mt-1">
                                                        {!! $photo->rating_stars !!}
                                                        <small class="text-muted ms-1">({{ $photo->admin_rating }}/5)</small>
                                                    </div>
                                                @endif
                                            </td>
                                            <td>
                                                <a href="{{ route('projects.show', $photo->project) }}" 
                                                   class="text-decoration-none">
                                                    {{ Str::limit($photo->project->name, 25) }}
                                                </a>
                                                @if($photo->task)
                                                    <br><small class="text-muted">{{ Str::limit($photo->task->task_name, 20) }}</small>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge bg-{{ $photo->photo_category_badge_color }}">
                                                    {{ $photo->formatted_photo_category }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-{{ $photo->submission_status_badge_color }}">
                                                    {{ $photo->formatted_submission_status }}
                                                </span>
                                                @if($photo->submission_status === 'rejected' && $photo->rejection_reason)
                                                    <br><small class="text-danger">{{ Str::limit($photo->rejection_reason, 30) }}</small>
                                                @endif
                                            </td>
                                            <td>
                                                <div>{{ $photo->uploader->first_name }} {{ $photo->uploader->last_name }}</div>
                                                <small class="text-muted">{{ $photo->uploader->email }}</small>
                                            </td>
                                            <td>
                                                @if($photo->submitted_at)
                                                    {{ $photo->formatted_submitted_at }}
                                                    <br><small class="text-muted">{{ $photo->submitted_at->diffForHumans() }}</small>
                                                    @if($photo->days_since_submission > 3)
                                                        <br><span class="badge bg-warning">{{ $photo->days_since_submission }} days</span>
                                                    @endif
                                                @else
                                                    <span class="text-muted">Not submitted</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm" role="group">
                                                    <a href="{{ route('admin.site-photos.show', $photo) }}" 
                                                       class="btn btn-outline-primary" title="View Details">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    @if($photo->submission_status === 'submitted')
                                                        <button class="btn btn-outline-success" title="Quick Approve"
                                                                onclick="quickApprove({{ $photo->id }})">
                                                            <i class="fas fa-check"></i>
                                                        </button>
                                                        <button class="btn btn-outline-danger" title="Quick Reject"
                                                                onclick="quickReject({{ $photo->id }})">
                                                            <i class="fas fa-times"></i>
                                                        </button>
                                                    @endif
                                                    @if($photo->submission_status === 'approved')
                                                        <button class="btn btn-outline-warning" title="Toggle Featured"
                                                                onclick="toggleFeatured({{ $photo->id }}, {{ $photo->is_featured ? 'false' : 'true' }})">
                                                            <i class="fas fa-star {{ $photo->is_featured ? 'text-warning' : '' }}"></i>
                                                        </button>
                                                        <button class="btn btn-outline-info" title="Toggle Public"
                                                                onclick="togglePublic({{ $photo->id }}, {{ $photo->is_public ? 'false' : 'true' }})">
                                                            <i class="fas fa-eye {{ $photo->is_public ? 'text-info' : '' }}"></i>
                                                        </button>
                                                    @endif
                                                    @if($photo->canBeDeletedBy(auth()->user()))
                                                        <button class="btn btn-outline-danger" title="Delete"
                                                                onclick="deletePhoto({{ $photo->id }}, '{{ addslashes($photo->title) }}')">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="d-flex justify-content-between align-items-center p-3 border-top">
                            <div>
                                Showing {{ $photos->firstItem() }} to {{ $photos->lastItem() }} of {{ $photos->total() }} photos
                            </div>
                            {{ $photos->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-camera fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No photos found</h5>
                            <p class="text-muted">No photos match your current filters.</p>
                            <a href="{{ route('admin.site-photos.index') }}" class="btn btn-outline-primary">
                                Clear Filters
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Photo Preview Modal -->
<div class="modal fade" id="photoModal" tabindex="-1" aria-labelledby="photoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="photoModalLabel">Photo Preview</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <img id="modalPhoto" src="" alt="Photo" class="img-fluid">
            </div>
        </div>
    </div>
</div>

<!-- Bulk Actions Modal -->
<div class="modal fade" id="bulkActionsModal" tabindex="-1" aria-labelledby="bulkActionsModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="bulkActionsModalLabel">Bulk Actions</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Select photos and choose an action:</p>
                <div class="d-grid gap-2">
                    <button class="btn btn-success" onclick="showBulkApprove()">
                        <i class="fas fa-check me-2"></i>Bulk Approve
                    </button>
                    <button class="btn btn-danger" onclick="showBulkReject()">
                        <i class="fas fa-times me-2"></i>Bulk Reject
                    </button>
                    <button class="btn btn-warning" onclick="showBulkFeature()">
                        <i class="fas fa-star me-2"></i>Bulk Feature
                    </button>
                    <button class="btn btn-info" onclick="showBulkMakePublic()">
                        <i class="fas fa-eye me-2"></i>Bulk Make Public
                    </button>
                    <hr>
                    <button class="btn btn-outline-danger" onclick="showBulkDelete()">
                        <i class="fas fa-trash me-2"></i>Bulk Delete
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<style>
.cursor-pointer {
    cursor: pointer;
}
.table-warning {
    --bs-table-accent-bg: var(--bs-warning-bg-subtle);
}
.img-thumbnail:hover {
    transform: scale(1.05);
    transition: transform 0.2s;
}
</style>
@endpush

@push('scripts')
<script>
function showPhotoModal(photoUrl, title) {
    document.getElementById('modalPhoto').src = photoUrl;
    document.getElementById('photoModalLabel').textContent = title;
    new bootstrap.Modal(document.getElementById('photoModal')).show();
}

// Select All functionality
document.getElementById('selectAll').addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('.photo-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.checked = this.checked;
    });
});

document.getElementById('selectAllTable').addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('.photo-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.checked = this.checked;
    });
});

function getSelectedPhotoIds() {
    const selected = [];
    document.querySelectorAll('.photo-checkbox:checked').forEach(checkbox => {
        selected.push(checkbox.value);
    });
    return selected;
}

// Quick Actions
function quickApprove(photoId) {
    window.location.href = `/admin/site-photos/${photoId}?action=approve`;
}

function quickReject(photoId) {
    window.location.href = `/admin/site-photos/${photoId}?action=reject`;
}

function toggleFeatured(photoId, isFeatured) {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = `/admin/site-photos/${photoId}/toggle-feature`;
    
    const csrfToken = document.createElement('input');
    csrfToken.type = 'hidden';
    csrfToken.name = '_token';
    csrfToken.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    
    const featuredInput = document.createElement('input');
    featuredInput.type = 'hidden';
    featuredInput.name = 'is_featured';
    featuredInput.value = isFeatured;
    
    form.appendChild(csrfToken);
    form.appendChild(featuredInput);
    document.body.appendChild(form);
    form.submit();
}

function togglePublic(photoId, isPublic) {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = `/admin/site-photos/${photoId}/toggle-public`;
    
    const csrfToken = document.createElement('input');
    csrfToken.type = 'hidden';
    csrfToken.name = '_token';
    csrfToken.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    
    const publicInput = document.createElement('input');
    publicInput.type = 'hidden';
    publicInput.name = 'is_public';
    publicInput.value = isPublic;
    
    form.appendChild(csrfToken);
    form.appendChild(publicInput);
    document.body.appendChild(form);
    form.submit();
}

function deletePhoto(photoId, photoTitle) {
    if (confirm(`Are you sure you want to delete the photo "${photoTitle}"? This action cannot be undone.`)) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/admin/site-photos/${photoId}`;
        
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        
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

// Bulk Actions
function showBulkApprove() {
    const selected = getSelectedPhotoIds();
    if (selected.length === 0) {
        alert('Please select photos to approve.');
        return;
    }
    window.location.href = `/admin/site-photos/bulk-action?action=approve&photo_ids=${selected.join(',')}`;
}

function showBulkReject() {
    const selected = getSelectedPhotoIds();
    if (selected.length === 0) {
        alert('Please select photos to reject.');
        return;
    }
    window.location.href = `/admin/site-photos/bulk-action?action=reject&photo_ids=${selected.join(',')}`;
}

function showBulkFeature() {
    const selected = getSelectedPhotoIds();
    if (selected.length === 0) {
        alert('Please select photos to feature.');
        return;
    }
    window.location.href = `/admin/site-photos/bulk-action?action=feature&photo_ids=${selected.join(',')}`;
}

function showBulkMakePublic() {
    const selected = getSelectedPhotoIds();
    if (selected.length === 0) {
        alert('Please select photos to make public.');
        return;
    }
    window.location.href = `/admin/site-photos/bulk-action?action=make_public&photo_ids=${selected.join(',')}`;
}

function showBulkDelete() {
    const selected = getSelectedPhotoIds();
    if (selected.length === 0) {
        alert('Please select photos to delete.');
        return;
    }
    if (confirm(`Are you sure you want to delete ${selected.length} photo(s)? This action cannot be undone.`)) {
        window.location.href = `/admin/site-photos/bulk-delete?photo_ids=${selected.join(',')}`;
    }
}
</script>
@endpush