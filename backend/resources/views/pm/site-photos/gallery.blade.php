@extends('app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1>Featured Photo Gallery</h1>
            <p class="text-muted">Showcase of approved and featured site photos from your projects</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('pm.site-photos.index') }}" class="btn btn-outline-primary">
                <i class="fas fa-list me-1"></i>Manage All Photos
            </a>
            <div class="dropdown">
                <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    <i class="fas fa-filter me-1"></i>Filter
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="{{ route('photos.featured') }}">Featured Photos</a></li>
                    <li><a class="dropdown-item" href="{{ route('photos.category', 'progress') }}">Progress Photos</a></li>
                    <li><a class="dropdown-item" href="{{ route('photos.category', 'completion') }}">Completion Photos</a></li>
                    <li><a class="dropdown-item" href="{{ route('photos.category', 'quality') }}">Quality Photos</a></li>
                    <li><a class="dropdown-item" href="{{ route('photos.category', 'safety') }}">Safety Photos</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="{{ route('photos.search') }}">Advanced Search</a></li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body text-center">
                    <i class="fas fa-star fa-2x mb-2"></i>
                    <h4>{{ $stats['featured'] ?? 0 }}</h4>
                    <small>Featured Photos</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body text-center">
                    <i class="fas fa-eye fa-2x mb-2"></i>
                    <h4>{{ $stats['public'] ?? 0 }}</h4>
                    <small>Public Photos</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body text-center">
                    <i class="fas fa-images fa-2x mb-2"></i>
                    <h4>{{ $stats['approved'] ?? 0 }}</h4>
                    <small>Approved Photos</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body text-center">
                    <i class="fas fa-project-diagram fa-2x mb-2"></i>
                    <h4>{{ $stats['projects_with_photos'] ?? 0 }}</h4>
                    <small>Projects with Photos</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Category Tabs -->
    <div class="card mb-4">
        <div class="card-header">
            <ul class="nav nav-tabs card-header-tabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" data-bs-toggle="tab" href="#featured" role="tab">
                        <i class="fas fa-star me-1"></i>Featured
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#progress" role="tab">
                        <i class="fas fa-chart-line me-1"></i>Progress
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#completion" role="tab">
                        <i class="fas fa-check-circle me-1"></i>Completion
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#quality" role="tab">
                        <i class="fas fa-award me-1"></i>Quality
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#safety" role="tab">
                        <i class="fas fa-hard-hat me-1"></i>Safety
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#recent" role="tab">
                        <i class="fas fa-clock me-1"></i>Recent
                    </a>
                </li>
            </ul>
        </div>
        
        <div class="card-body">
            <div class="tab-content">
                <!-- Featured Photos Tab -->
                <div class="tab-pane fade show active" id="featured" role="tabpanel">
                    <div class="row" id="featuredPhotosGrid">
                        <!-- Content will be loaded via AJAX -->
                        <div class="col-12 text-center py-4">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="mt-2 text-muted">Loading featured photos...</p>
                        </div>
                    </div>
                </div>
                
                <!-- Progress Photos Tab -->
                <div class="tab-pane fade" id="progress" role="tabpanel">
                    <div class="row" id="progressPhotosGrid">
                        <!-- Content will be loaded via AJAX -->
                    </div>
                </div>
                
                <!-- Completion Photos Tab -->
                <div class="tab-pane fade" id="completion" role="tabpanel">
                    <div class="row" id="completionPhotosGrid">
                        <!-- Content will be loaded via AJAX -->
                    </div>
                </div>
                
                <!-- Quality Photos Tab -->
                <div class="tab-pane fade" id="quality" role="tabpanel">
                    <div class="row" id="qualityPhotosGrid">
                        <!-- Content will be loaded via AJAX -->
                    </div>
                </div>
                
                <!-- Safety Photos Tab -->
                <div class="tab-pane fade" id="safety" role="tabpanel">
                    <div class="row" id="safetyPhotosGrid">
                        <!-- Content will be loaded via AJAX -->
                    </div>
                </div>
                
                <!-- Recent Photos Tab -->
                <div class="tab-pane fade" id="recent" role="tabpanel">
                    <div class="row" id="recentPhotosGrid">
                        <!-- Content will be loaded via AJAX -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Photo Lightbox Modal -->
<div class="modal fade" id="photoLightbox" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="photoTitle">Photo Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-8">
                        <img id="lightboxImage" src="" class="img-fluid w-100" alt="">
                    </div>
                    <div class="col-md-4">
                        <div id="photoDetails">
                            <!-- Photo details will be populated here -->
                        </div>
                        <div class="mt-3">
                            <button type="button" class="btn btn-success btn-sm" id="approvePhotoBtn" style="display: none;">
                                <i class="fas fa-check me-1"></i>Approve
                            </button>
                            <button type="button" class="btn btn-warning btn-sm" id="featurePhotoBtn" style="display: none;">
                                <i class="fas fa-star me-1"></i>Toggle Featured
                            </button>
                            <button type="button" class="btn btn-info btn-sm" id="publicPhotoBtn" style="display: none;">
                                <i class="fas fa-eye me-1"></i>Toggle Public
                            </button>
                            <a id="viewDetailsBtn" href="#" class="btn btn-primary btn-sm">
                                <i class="fas fa-external-link-alt me-1"></i>View Details
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
.photo-card {
    position: relative;
    overflow: hidden;
    border-radius: 0.5rem;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    cursor: pointer;
    height: 250px;
}

.photo-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.15);
}

.photo-card img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.photo-card:hover img {
    transform: scale(1.05);
}

.photo-overlay {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    background: linear-gradient(transparent, rgba(0,0,0,0.8));
    color: white;
    padding: 20px 15px 15px;
    transform: translateY(100%);
    transition: transform 0.3s ease;
}

.photo-card:hover .photo-overlay {
    transform: translateY(0);
}

.photo-badges {
    position: absolute;
    top: 10px;
    right: 10px;
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.photo-badges .badge {
    font-size: 0.7rem;
    backdrop-filter: blur(5px);
    background-color: rgba(255,255,255,0.9) !important;
    color: #333 !important;
}

.project-badge {
    position: absolute;
    top: 10px;
    left: 10px;
    backdrop-filter: blur(5px);
    background-color: rgba(0,0,0,0.7) !important;
    color: white !important;
    font-size: 0.7rem;
}

.loading-grid {
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 300px;
    flex-direction: column;
}

.nav-tabs .nav-link {
    border: none;
    color: #6c757d;
    font-weight: 500;
}

.nav-tabs .nav-link.active {
    color: #0d6efd;
    border-bottom: 2px solid #0d6efd;
    background: none;
}

.empty-state {
    text-align: center;
    padding: 3rem 1rem;
    color: #6c757d;
}

.empty-state i {
    font-size: 3rem;
    margin-bottom: 1rem;
    opacity: 0.5;
}

.masonry-grid {
    column-count: 3;
    column-gap: 1rem;
}

.masonry-item {
    break-inside: avoid;
    margin-bottom: 1rem;
}

@media (max-width: 992px) {
    .masonry-grid {
        column-count: 2;
    }
}

@media (max-width: 576px) {
    .masonry-grid {
        column-count: 1;
    }
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Load featured photos by default
    loadPhotos('featured');
    
    // Tab click handlers
    document.querySelectorAll('[data-bs-toggle="tab"]').forEach(tab => {
        tab.addEventListener('shown.bs.tab', function(e) {
            const target = e.target.getAttribute('href').substring(1);
            loadPhotos(target);
        });
    });
});

function loadPhotos(category) {
    const gridId = category + 'PhotosGrid';
    const grid = document.getElementById(gridId);
    
    if (!grid) return;
    
    // Show loading state
    grid.innerHTML = `
        <div class="col-12 loading-grid">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2 text-muted">Loading ${category} photos...</p>
        </div>
    `;
    
    // Determine API endpoint based on category
    let endpoint = '/pm/site-photos/api/gallery';
    let params = new URLSearchParams();
    
    if (category === 'featured') {
        params.append('featured', '1');
    } else if (category === 'recent') {
        params.append('recent', '1');
    } else {
        params.append('category', category);
    }
    
    // Only show approved and public photos in gallery
    params.append('status', 'approved');
    params.append('public', '1');
    
    fetch(`${endpoint}?${params.toString()}`)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.photos && data.photos.length > 0) {
                renderPhotos(grid, data.photos);
            } else {
                showEmptyState(grid, category);
            }
        })
        .catch(error => {
            console.error('Error loading photos:', error);
            showErrorState(grid, category);
        });
}

function renderPhotos(grid, photos) {
    grid.innerHTML = '';
    
    photos.forEach(photo => {
        const photoCard = createPhotoCard(photo);
        grid.appendChild(photoCard);
    });
}

function createPhotoCard(photo) {
    const col = document.createElement('div');
    col.className = 'col-md-4 col-lg-3 mb-4';
    
    col.innerHTML = `
        <div class="photo-card" onclick="openLightbox(${photo.id})">
            <img src="/storage/${photo.photo_path}" alt="${photo.title}" loading="lazy">
            
            <!-- Project Badge -->
            <span class="badge project-badge">${photo.project_name}</span>
            
            <!-- Status Badges -->
            <div class="photo-badges">
                ${photo.is_featured ? '<span class="badge bg-warning"><i class="fas fa-star"></i></span>' : ''}
                ${photo.is_public ? '<span class="badge bg-info"><i class="fas fa-eye"></i></span>' : ''}
                ${photo.admin_rating ? `<span class="badge bg-success">${'★'.repeat(photo.admin_rating)}</span>` : ''}
            </div>
            
            <!-- Overlay Info -->
            <div class="photo-overlay">
                <h6 class="mb-1">${photo.title}</h6>
                <small class="text-light">
                    <i class="fas fa-user me-1"></i>${photo.uploader_name}<br>
                    <i class="fas fa-calendar me-1"></i>${photo.formatted_photo_date}<br>
                    <i class="fas fa-tag me-1"></i>${photo.formatted_category}
                </small>
            </div>
        </div>
    `;
    
    return col;
}

function showEmptyState(grid, category) {
    grid.innerHTML = `
        <div class="col-12">
            <div class="empty-state">
                <i class="fas fa-camera"></i>
                <h5>No ${category} photos found</h5>
                <p class="text-muted">There are no ${category} photos to display at the moment.</p>
                <a href="/pm/site-photos" class="btn btn-outline-primary">
                    <i class="fas fa-cog me-1"></i>Manage Photos
                </a>
            </div>
        </div>
    `;
}

function showErrorState(grid, category) {
    grid.innerHTML = `
        <div class="col-12">
            <div class="empty-state">
                <i class="fas fa-exclamation-triangle text-warning"></i>
                <h5>Error loading photos</h5>
                <p class="text-muted">There was an error loading ${category} photos. Please try again.</p>
                <button onclick="loadPhotos('${category}')" class="btn btn-outline-primary">
                    <i class="fas fa-refresh me-1"></i>Retry
                </button>
            </div>
        </div>
    `;
}

function openLightbox(photoId) {
    // Fetch photo details
    fetch(`/pm/site-photos/${photoId}/api`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                populateLightbox(data.photo);
                const modal = new bootstrap.Modal(document.getElementById('photoLightbox'));
                modal.show();
            } else {
                alert('Error loading photo details');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error loading photo details');
        });
}

function populateLightbox(photo) {
    // Update modal title and image
    document.getElementById('photoTitle').textContent = photo.title;
    document.getElementById('lightboxImage').src = `/storage/${photo.photo_path}`;
    document.getElementById('lightboxImage').alt = photo.title;
    document.getElementById('viewDetailsBtn').href = `/pm/site-photos/${photo.id}`;
    
    // Update photo details
    const detailsHtml = `
        <h6>Photo Information</h6>
        <table class="table table-sm">
            <tr><th>Project:</th><td>${photo.project_name}</td></tr>
            ${photo.task_name ? `<tr><th>Task:</th><td>${photo.task_name}</td></tr>` : ''}
            <tr><th>Category:</th><td>${photo.formatted_category}</td></tr>
            <tr><th>Uploader:</th><td>${photo.uploader_name}</td></tr>
            <tr><th>Date:</th><td>${photo.formatted_photo_date}</td></tr>
            <tr><th>Status:</th><td><span class="badge bg-${photo.status_color}">${photo.formatted_status}</span></td></tr>
            ${photo.location ? `<tr><th>Location:</th><td>${photo.location}</td></tr>` : ''}
            ${photo.weather_conditions ? `<tr><th>Weather:</th><td>${photo.formatted_weather}</td></tr>` : ''}
            ${photo.admin_rating ? `<tr><th>Rating:</th><td>${'★'.repeat(photo.admin_rating)} (${photo.admin_rating}/5)</td></tr>` : ''}
        </table>
        
        ${photo.description ? `
            <h6>Description</h6>
            <p class="text-muted">${photo.description}</p>
        ` : ''}
        
        ${photo.tags && photo.tags.length > 0 ? `
            <h6>Tags</h6>
            <div>
                ${photo.tags.map(tag => `<span class="badge bg-secondary me-1">${tag}</span>`).join('')}
            </div>
        ` : ''}
    `;
    
    document.getElementById('photoDetails').innerHTML = detailsHtml;
    
    // Show/hide action buttons based on status
    const approveBtn = document.getElementById('approvePhotoBtn');
    const featureBtn = document.getElementById('featurePhotoBtn');
    const publicBtn = document.getElementById('publicPhotoBtn');
    
    if (photo.submission_status === 'submitted') {
        approveBtn.style.display = 'inline-block';
        approveBtn.onclick = () => quickApproveFromLightbox(photo.id);
    } else {
        approveBtn.style.display = 'none';
    }
    
    if (photo.submission_status === 'approved') {
        featureBtn.style.display = 'inline-block';
        featureBtn.onclick = () => toggleFeatureFromLightbox(photo.id, photo.is_featured);
        featureBtn.innerHTML = `<i class="fas fa-star me-1"></i>${photo.is_featured ? 'Unfeature' : 'Feature'}`;
        
        publicBtn.style.display = 'inline-block';
        publicBtn.onclick = () => togglePublicFromLightbox(photo.id, photo.is_public);
        publicBtn.innerHTML = `<i class="fas fa-eye me-1"></i>${photo.is_public ? 'Make Private' : 'Make Public'}`;
    } else {
        featureBtn.style.display = 'none';
        publicBtn.style.display = 'none';
    }
}

function quickApproveFromLightbox(photoId) {
    if (confirm('Are you sure you want to approve this photo?')) {
        fetch(`/pm/site-photos/${photoId}/quick-approve`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                comments: 'Quick approved from gallery',
                make_public: true
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success || response.ok) {
                bootstrap.Modal.getInstance(document.getElementById('photoLightbox')).hide();
                // Reload current tab
                const activeTab = document.querySelector('.nav-link.active').getAttribute('href').substring(1);
                loadPhotos(activeTab);
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

function toggleFeatureFromLightbox(photoId, currentState) {
    fetch(`/pm/site-photos/${photoId}/toggle-feature`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({
            is_featured: !currentState
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('photoLightbox')).hide();
            // Reload current tab
            const activeTab = document.querySelector('.nav-link.active').getAttribute('href').substring(1);
            loadPhotos(activeTab);
        } else {
            alert('Error updating featured status');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error updating featured status');
    });
}

function togglePublicFromLightbox(photoId, currentState) {
    fetch(`/pm/site-photos/${photoId}/toggle-public`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({
            is_public: !currentState
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('photoLightbox')).hide();
            // Reload current tab
            const activeTab = document.querySelector('.nav-link.active').getAttribute('href').substring(1);
            loadPhotos(activeTab);
        } else {
            alert('Error updating public status');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error updating public status');
    });
}

// Keyboard navigation for lightbox
document.addEventListener('keydown', function(e) {
    const modal = bootstrap.Modal.getInstance(document.getElementById('photoLightbox'));
    if (modal && modal._isShown) {
        if (e.key === 'Escape') {
            modal.hide();
        }
    }
});
</script>
@endpush
@endsection