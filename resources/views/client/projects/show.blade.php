{{-- resources/views/client/projects/show.blade.php --}}
@extends('app')

@section('title', $project->name . ' - Project Details')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Page Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item">
                                <a href="{{ route('client.dashboard') }}">Dashboard</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">{{ $project->name }}</li>
                        </ol>
                    </nav>
                    <h1 class="h3 mb-0 text-gray-800">
                        <i class="fas fa-project-diagram me-2"></i>{{ $project->name }}
                    </h1>
                    <p class="text-muted mb-0">Project Details and Photo Gallery</p>
                </div>
                <div class="d-flex gap-2">
                    <span class="badge bg-{{ $project->client_health_color }} fs-6">
                        {{ ucfirst(str_replace('_', ' ', $project->client_health_status)) }}
                    </span>
                </div>
            </div>

            <!-- Project Overview Card -->
            <div class="card shadow mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-info-circle me-2"></i>Project Overview
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <h6 class="fw-bold">Description</h6>
                            <p class="text-muted">{{ $project->description ?: 'No description available.' }}</p>
                            
                            <div class="row mt-4">
                                <div class="col-sm-6">
                                    <h6 class="fw-bold">Start Date</h6>
                                    <p class="text-muted">
                                        <i class="fas fa-calendar-start me-1"></i>
                                        {{ $project->formatted_start_date ?: 'Not set' }}
                                    </p>
                                </div>
                                <div class="col-sm-6">
                                    <h6 class="fw-bold">End Date</h6>
                                    <p class="text-muted">
                                        <i class="fas fa-calendar-check me-1"></i>
                                        {{ $project->formatted_end_date ?: 'Not set' }}
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="progress-card bg-light p-3 rounded">
                                <h6 class="fw-bold">Project Progress</h6>
                                <div class="progress mb-2" style="height: 10px;">
                                    <div class="progress-bar bg-{{ $project->client_health_color }}" 
                                         role="progressbar" 
                                         style="width: {{ $project->client_completion_percentage ?? 0 }}%"
                                         aria-valuenow="{{ $project->client_completion_percentage ?? 0 }}" 
                                         aria-valuemin="0" 
                                         aria-valuemax="100">
                                    </div>
                                </div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="text-muted small">Completion</span>
                                    <span class="fw-bold">{{ $project->client_completion_percentage ?? 0 }}%</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Project Statistics -->
            <div class="row mb-4">
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-primary shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                        Public Photos
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        {{ $project->client_visible_photos_count ?? 0 }}
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-images fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-success shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                        Project Updates
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        {{ $project->recent_updates_count ?? 0 }}
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-bullhorn fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-info shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                        Recent Activity
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        {{ $project->recent_client_activity_count ?? 0 }}
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-chart-line fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-warning shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                        Project Health
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        <span class="badge bg-{{ $project->client_health_color }}">
                                            {{ ucfirst(str_replace('_', ' ', $project->client_health_status)) }}
                                        </span>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-heartbeat fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Project Photos Gallery -->
            <div class="card shadow">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-camera me-2"></i>Project Photo Gallery
                        @if($photos->total() > 0)
                            <span class="badge bg-secondary ms-2">{{ $photos->total() }} photos</span>
                        @endif
                    </h6>
                    <div class="d-flex gap-2">
                        @if($photos->total() > 0)
                            <a href="{{ route('projects.photos', $project->id) }}" class="btn btn-sm btn-primary">
                                <i class="fas fa-expand me-1"></i>View Full Gallery
                            </a>
                        @endif
                        <a href="{{ route('photos.featured') }}" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-star me-1"></i>Featured Photos
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if($photos->count() > 0)
                        <!-- Photo Grid -->
                        <div class="row g-3">
                            @foreach($photos as $photo)
                                <div class="col-lg-3 col-md-4 col-sm-6">
                                    <div class="photo-card">
                                        <div class="photo-container position-relative">
                                            <img src="{{ asset('storage/' . $photo->photo_path) }}" 
                                                 alt="{{ $photo->title }}" 
                                                 class="img-fluid rounded photo-thumbnail"
                                                 onclick="openPhotoModal('{{ asset('storage/' . $photo->photo_path) }}', '{{ $photo->title }}', '{{ $photo->description }}')">
                                            
                                            @if($photo->is_featured)
                                                <span class="position-absolute top-0 end-0 badge bg-warning m-2">
                                                    <i class="fas fa-star"></i> Featured
                                                </span>
                                            @endif

                                            <div class="photo-overlay">
                                                <div class="photo-overlay-content">
                                                    <button class="btn btn-light btn-sm" 
                                                            onclick="openPhotoModal('{{ asset('storage/' . $photo->photo_path) }}', '{{ $photo->title }}', '{{ $photo->description }}')">
                                                        <i class="fas fa-search-plus"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="photo-info mt-2">
                                            <h6 class="mb-1 fw-bold">{{ Str::limit($photo->title, 20) }}</h6>
                                            @if($photo->description)
                                                <p class="text-muted small mb-1">{{ Str::limit($photo->description, 50) }}</p>
                                            @endif
                                            <div class="d-flex justify-content-between align-items-center">
                                                <small class="text-muted">
                                                    <i class="fas fa-calendar me-1"></i>{{ $photo->photo_date->format('M d, Y') }}
                                                </small>
                                                <span class="badge bg-info">{{ ucfirst($photo->photo_category) }}</span>
                                            </div>
                                            @if($photo->uploader)
                                                <small class="text-muted d-block">
                                                    <i class="fas fa-user me-1"></i>By {{ $photo->uploader->first_name }}
                                                </small>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <!-- Pagination -->
                        @if($photos->hasPages())
                            <div class="d-flex justify-content-center mt-4">
                                {{ $photos->links() }}
                            </div>
                        @endif

                        <!-- Load More Button (if needed) -->
                        @if($photos->hasMorePages())
                            <div class="text-center mt-3">
                                <a href="{{ route('projects.photos', $project->id) }}" class="btn btn-outline-primary">
                                    <i class="fas fa-plus me-1"></i>View More Photos
                                </a>
                            </div>
                        @endif
                    @else
                        <!-- Empty State -->
                        <div class="text-center py-5">
                            <i class="fas fa-camera fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No Photos Available</h5>
                            <p class="text-muted">
                                Photos from this project will appear here once they are approved and made public.
                            </p>
                            <a href="{{ route('photos.featured') }}" class="btn btn-primary">
                                <i class="fas fa-star me-1"></i>Browse Featured Photos
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Photo Modal -->
<div class="modal fade" id="photoModal" tabindex="-1" aria-labelledby="photoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="photoModalLabel">Photo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <img id="modalImage" src="" alt="" class="img-fluid rounded">
                <div class="mt-3">
                    <h6 id="modalTitle" class="fw-bold"></h6>
                    <p id="modalDescription" class="text-muted"></p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
.border-left-primary {
    border-left: 0.25rem solid #4e73df !important;
}

.border-left-success {
    border-left: 0.25rem solid #1cc88a !important;
}

.border-left-info {
    border-left: 0.25rem solid #36b9cc !important;
}

.border-left-warning {
    border-left: 0.25rem solid #f6c23e !important;
}

.photo-card {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.photo-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
}

.photo-container {
    overflow: hidden;
    border-radius: 0.5rem;
    aspect-ratio: 4/3;
}

.photo-thumbnail {
    width: 100%;
    height: 100%;
    object-fit: cover;
    cursor: pointer;
    transition: transform 0.3s ease;
}

.photo-container:hover .photo-thumbnail {
    transform: scale(1.05);
}

.photo-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.photo-container:hover .photo-overlay {
    opacity: 1;
}

.photo-overlay-content {
    text-align: center;
}

.progress-card {
    border-left: 4px solid #4e73df;
}

.card {
    border: none;
    border-radius: 0.5rem;
}

.card-header {
    background-color: #f8f9fc;
    border-bottom: 1px solid #e3e6f0;
}

.photo-info h6 {
    color: #2c3e50;
}

.badge {
    font-size: 0.75rem;
}

/* Modal enhancements */
.modal-content {
    border-radius: 0.5rem;
}

#modalImage {
    max-height: 70vh;
    border-radius: 0.5rem;
}

/* Animation for statistics cards */
.border-left-primary,
.border-left-success,
.border-left-info,
.border-left-warning {
    opacity: 0;
    transform: translateY(20px);
    animation: fadeInUp 0.6s ease forwards;
}

.border-left-primary {
    animation-delay: 0.1s;
}

.border-left-success {
    animation-delay: 0.2s;
}

.border-left-info {
    animation-delay: 0.3s;
}

.border-left-warning {
    animation-delay: 0.4s;
}

@keyframes fadeInUp {
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .photo-card {
        margin-bottom: 1rem;
    }
    
    .card-header .d-flex {
        flex-direction: column;
        gap: 0.5rem;
        align-items: flex-start !important;
    }
}
</style>
@endpush

@push('scripts')
<script>
function openPhotoModal(imageSrc, title, description) {
    const modal = new bootstrap.Modal(document.getElementById('photoModal'));
    const modalImage = document.getElementById('modalImage');
    const modalTitle = document.getElementById('modalTitle');
    const modalDescription = document.getElementById('modalDescription');
    
    modalImage.src = imageSrc;
    modalTitle.textContent = title || 'Project Photo';
    modalDescription.textContent = description || '';
    
    modal.show();
}

document.addEventListener('DOMContentLoaded', function() {
    // Initialize statistics card animations
    const statCards = document.querySelectorAll('.border-left-primary, .border-left-success, .border-left-info, .border-left-warning');
    
    statCards.forEach(card => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
    });
    
    statCards.forEach((card, index) => {
        setTimeout(() => {
            card.style.transition = 'all 0.6s ease';
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, (index + 1) * 150);
    });

    // Add click tracking for photo interactions
    document.querySelectorAll('.photo-thumbnail').forEach(photo => {
        photo.addEventListener('click', function() {
            console.log('Photo viewed:', this.alt);
        });
    });

    // Progress bar animation
    const progressBar = document.querySelector('.progress-bar');
    if (progressBar) {
        const width = progressBar.style.width;
        progressBar.style.width = '0%';
        setTimeout(() => {
            progressBar.style.transition = 'width 1s ease';
            progressBar.style.width = width;
        }, 500);
    }

    // Keyboard navigation for photo modal
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const modal = bootstrap.Modal.getInstance(document.getElementById('photoModal'));
            if (modal) {
                modal.hide();
            }
        }
    });
});

// Enhanced photo loading with error handling
document.querySelectorAll('.photo-thumbnail').forEach(img => {
    img.addEventListener('error', function() {
        this.src = 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAwIiBoZWlnaHQ9IjE1MCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iMTAwJSIgaGVpZ2h0PSIxMDAlIiBmaWxsPSIjZGRkIi8+PHRleHQgeD0iNTAlIiB5PSI1MCUiIGZvbnQtZmFtaWx5PSJBcmlhbCwgc2Fucy1zZXJpZiIgZm9udC1zaXplPSIxNCIgZmlsbD0iIzk5OSIgdGV4dC1hbmNob3I9Im1pZGRsZSIgZHk9Ii4zZW0iPkltYWdlIG5vdCBhdmFpbGFibGU8L3RleHQ+PC9zdmc+';
        this.alt = 'Image not available';
    });
});
</script>
@endpush
@endsection