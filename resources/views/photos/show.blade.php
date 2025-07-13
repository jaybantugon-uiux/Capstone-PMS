@extends('app')

@section('title', $sitePhoto->title)

@section('content')
<div class="container-fluid px-4">
    <!-- Header Section -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="{{ route('photos.featured') }}" class="text-decoration-none">Photos</a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="{{ route('photos.category', $sitePhoto->photo_category) }}" class="text-decoration-none">
                            {{ ucfirst($sitePhoto->photo_category) }}
                        </a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">{{ Str::limit($sitePhoto->title, 30) }}</li>
                </ol>
            </nav>
            <h1 class="h3 text-gray-800 mb-0">
                <i class="fas fa-camera me-2"></i>{{ $sitePhoto->title }}
            </h1>
        </div>
        
        <!-- Action Buttons -->
        <div class="d-flex gap-2">
            <a href="{{ route('photos.category', $sitePhoto->photo_category) }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i>Back to {{ ucfirst($sitePhoto->photo_category) }}
            </a>
            @if(in_array(auth()->user()->role, ['admin', 'pm']))
                <a href="{{ route('admin.site-photos.show', $sitePhoto->id) }}" class="btn btn-outline-primary">
                    <i class="fas fa-cog me-1"></i>Manage
                </a>
            @elseif(auth()->user()->role === 'sc' && $sitePhoto->user_id === auth()->id())
                <a href="{{ route('sc.site-photos.show', $sitePhoto->id) }}" class="btn btn-outline-primary">
                    <i class="fas fa-edit me-1"></i>Edit
                </a>
            @endif
        </div>
    </div>

    <div class="row">
        <!-- Main Photo Section -->
        <div class="col-lg-8">
            <!-- Photo Display Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-image me-2"></i>Photo Details
                    </h6>
                    
                    <!-- Photo Status Badges -->
                    <div class="d-flex gap-2">
                        @if($sitePhoto->is_featured)
                            <span class="badge badge-warning">
                                <i class="fas fa-star me-1"></i>Featured
                            </span>
                        @endif
                        
                        <span class="badge badge-primary">
                            {{ ucfirst($sitePhoto->photo_category) }}
                        </span>
                        
                        @php
                            $statusColors = [
                                'draft' => 'secondary',
                                'submitted' => 'warning',
                                'approved' => 'success',
                                'rejected' => 'danger'
                            ];
                            $statusColor = $statusColors[$sitePhoto->submission_status] ?? 'secondary';
                        @endphp
                        
                        <span class="badge badge-{{ $statusColor }}">
                            {{ ucfirst($sitePhoto->submission_status) }}
                        </span>
                    </div>
                </div>
                <div class="card-body p-0">
                    <!-- Main Photo -->
                    <div class="position-relative">
                        <img src="{{ Storage::url($sitePhoto->photo_path) }}" 
                             alt="{{ $sitePhoto->title }}" 
                             class="img-fluid w-100"
                             style="max-height: 600px; object-fit: contain; background: #f8f9fc;">
                        
                        <!-- Photo Overlay Info -->
                        <div class="position-absolute bg-dark bg-opacity-75 text-white p-2" 
                             style="bottom: 0; left: 0; right: 0;">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <small>
                                        <i class="fas fa-calendar-alt me-1"></i>
                                        {{ $sitePhoto->photo_date->format('F d, Y') }}
                                    </small>
                                    @if($sitePhoto->location)
                                        <br>
                                        <small>
                                            <i class="fas fa-map-marker-alt me-1"></i>
                                            {{ $sitePhoto->location }}
                                        </small>
                                    @endif
                                </div>
                                <div class="text-end">
                                    @if($sitePhoto->weather_conditions)
                                        <small>
                                            @php
                                                $weatherIcons = [
                                                    'sunny' => 'fa-sun',
                                                    'cloudy' => 'fa-cloud',
                                                    'rainy' => 'fa-cloud-rain',
                                                    'stormy' => 'fa-thunderstorm',
                                                    'windy' => 'fa-wind'
                                                ];
                                                $weatherIcon = $weatherIcons[$sitePhoto->weather_conditions] ?? 'fa-cloud-sun';
                                            @endphp
                                            <i class="fas {{ $weatherIcon }} me-1"></i>
                                            {{ ucfirst($sitePhoto->weather_conditions) }}
                                        </small>
                                        <br>
                                    @endif
                                    <small>
                                        {{ number_format($sitePhoto->file_size / 1024, 1) }} KB
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Photo Description -->
            @if($sitePhoto->description)
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-align-left me-2"></i>Description
                        </h6>
                    </div>
                    <div class="card-body">
                        <p class="mb-0">{{ $sitePhoto->description }}</p>
                    </div>
                </div>
            @endif

            <!-- Admin Comments (if available) -->
            @if($sitePhoto->admin_comments && (in_array(auth()->user()->role, ['admin', 'pm']) || auth()->user()->id === $sitePhoto->user_id))
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-info">
                            <i class="fas fa-comments me-2"></i>Admin Comments
                        </h6>
                    </div>
                    <div class="card-body">
                        <p class="mb-0">{{ $sitePhoto->admin_comments }}</p>
                        @if($sitePhoto->admin_rating)
                            <hr>
                            <div class="d-flex align-items-center">
                                <strong class="me-2">Rating:</strong>
                                @for($i = 1; $i <= 5; $i++)
                                    <i class="fas fa-star {{ $i <= $sitePhoto->admin_rating ? 'text-warning' : 'text-muted' }}"></i>
                                @endfor
                                <span class="ml-2 text-muted">({{ $sitePhoto->admin_rating }}/5)</span>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            <!-- Comments Section -->
            @if($sitePhoto->comments->count() > 0)
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-comment me-2"></i>Comments
                            <span class="badge badge-secondary ml-2">{{ $sitePhoto->comments->count() }}</span>
                        </h6>
                    </div>
                    <div class="card-body">
                        @foreach($sitePhoto->comments as $comment)
                            <div class="border-bottom pb-3 mb-3">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div class="d-flex align-items-center">
                                        <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center text-white me-2" 
                                             style="width: 32px; height: 32px; font-size: 14px;">
                                            {{ substr($comment->user->first_name, 0, 1) }}{{ substr($comment->user->last_name, 0, 1) }}
                                        </div>
                                        <div>
                                            <strong>{{ $comment->user->first_name }} {{ $comment->user->last_name }}</strong>
                                            <small class="text-muted d-block">{{ $comment->created_at->diffForHumans() }}</small>
                                        </div>
                                    </div>
                                    @if($comment->is_internal && in_array(auth()->user()->role, ['admin', 'pm']))
                                        <span class="badge badge-warning">Internal</span>
                                    @endif
                                </div>
                                <p class="mb-0 ml-5">{{ $comment->comment }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Photo Information -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-info-circle me-2"></i>Photo Information
                    </h6>
                </div>
                <div class="card-body">
                    <!-- Project Info -->
                    <div class="row mb-3">
                        <div class="col-4"><strong>Project:</strong></div>
                        <div class="col-8">
                            <a href="{{ route('projects.show', $sitePhoto->project->id) }}" 
                               class="text-primary text-decoration-none">
                                {{ $sitePhoto->project->name }}
                            </a>
                        </div>
                    </div>

                    <!-- Task Info -->
                    @if($sitePhoto->task)
                        <div class="row mb-3">
                            <div class="col-4"><strong>Task:</strong></div>
                            <div class="col-8">
                                <a href="{{ route('tasks.show', $sitePhoto->task->id) }}" 
                                   class="text-primary text-decoration-none">
                                    {{ $sitePhoto->task->task_name }}
                                </a>
                            </div>
                        </div>
                    @endif

                    <!-- Uploader Info -->
                    <div class="row mb-3">
                        <div class="col-4"><strong>Uploaded by:</strong></div>
                        <div class="col-8">
                            {{ $sitePhoto->uploader->first_name }} {{ $sitePhoto->uploader->last_name }}
                            <small class="text-muted d-block">{{ $sitePhoto->created_at->format('M d, Y g:i A') }}</small>
                        </div>
                    </div>

                    <!-- Category -->
                    <div class="row mb-3">
                        <div class="col-4"><strong>Category:</strong></div>
                        <div class="col-8">
                            <a href="{{ route('photos.category', $sitePhoto->photo_category) }}" 
                               class="badge badge-primary text-decoration-none">
                                {{ ucfirst($sitePhoto->photo_category) }}
                            </a>
                        </div>
                    </div>

                    <!-- Photo Date -->
                    <div class="row mb-3">
                        <div class="col-4"><strong>Photo Date:</strong></div>
                        <div class="col-8">{{ $sitePhoto->photo_date->format('F d, Y') }}</div>
                    </div>

                    <!-- File Info -->
                    <div class="row mb-3">
                        <div class="col-4"><strong>File Size:</strong></div>
                        <div class="col-8">{{ number_format($sitePhoto->file_size / 1024, 1) }} KB</div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-4"><strong>Original Name:</strong></div>
                        <div class="col-8">
                            <small class="text-muted">{{ $sitePhoto->original_filename }}</small>
                        </div>
                    </div>

                    <!-- Review Status -->
                    @if($sitePhoto->reviewed_at)
                        <div class="row mb-3">
                            <div class="col-4"><strong>Reviewed:</strong></div>
                            <div class="col-8">
                                {{ $sitePhoto->reviewed_at->format('M d, Y') }}
                                @if($sitePhoto->reviewedBy)
                                    <small class="text-muted d-block">by {{ $sitePhoto->reviewedBy->first_name }}</small>
                                @endif
                            </div>
                        </div>
                    @endif

                    <!-- Tags if available -->
                    @if($sitePhoto->tags && is_array($sitePhoto->tags) && count($sitePhoto->tags) > 0)
                        <div class="row mb-3">
                            <div class="col-4"><strong>Tags:</strong></div>
                            <div class="col-8">
                                @foreach($sitePhoto->tags as $tag)
                                    <span class="badge badge-light mr-1">#{{ $tag }}</span>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Quick Actions -->
            @if(in_array(auth()->user()->role, ['admin', 'pm']) || 
                (auth()->user()->role === 'sc' && $sitePhoto->user_id === auth()->id()))
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-tools me-2"></i>Quick Actions
                        </h6>
                    </div>
                    <div class="card-body">
                        @if(auth()->user()->role === 'sc' && $sitePhoto->user_id === auth()->id())
                            <a href="{{ route('sc.site-photos.edit', $sitePhoto->id) }}" 
                               class="btn btn-outline-primary btn-sm btn-block mb-2">
                                <i class="fas fa-edit me-1"></i>Edit Photo
                            </a>
                        @endif

                        @if(in_array(auth()->user()->role, ['admin', 'pm']))
                            <a href="{{ route('admin.site-photos.show', $sitePhoto->id) }}" 
                               class="btn btn-outline-success btn-sm btn-block mb-2">
                                <i class="fas fa-cog me-1"></i>Manage Photo
                            </a>
                            
                            @if($sitePhoto->submission_status === 'submitted')
                                <div class="dropdown">
                                    <button class="btn btn-outline-warning btn-sm btn-block dropdown-toggle" 
                                            type="button" id="reviewDropdown" data-toggle="dropdown">
                                        <i class="fas fa-clipboard-check me-1"></i>Review Photo
                                    </button>
                                    <div class="dropdown-menu w-100">
                                        <form action="{{ route('admin.site-photos.update-review', $sitePhoto->id) }}" 
                                              method="POST" class="px-3 py-2">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="submission_status" value="approved">
                                            <button type="submit" class="dropdown-item">
                                                <i class="fas fa-check text-success me-1"></i>Approve
                                            </button>
                                        </form>
                                        <form action="{{ route('admin.site-photos.update-review', $sitePhoto->id) }}" 
                                              method="POST" class="px-3 py-2">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="submission_status" value="rejected">
                                            <button type="submit" class="dropdown-item">
                                                <i class="fas fa-times text-danger me-1"></i>Reject
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            @endif
                        @endif

                        <!-- Download Original -->
                        <a href="{{ Storage::url($sitePhoto->photo_path) }}" 
                           download="{{ $sitePhoto->original_filename }}"
                           class="btn btn-outline-secondary btn-sm btn-block mt-2">
                            <i class="fas fa-download me-1"></i>Download Original
                        </a>
                    </div>
                </div>
            @endif

            <!-- Navigation -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-compass me-2"></i>Navigation
                    </h6>
                </div>
                <div class="card-body">
                    <a href="{{ route('photos.category', $sitePhoto->photo_category) }}" 
                       class="btn btn-outline-primary btn-sm btn-block mb-2">
                        <i class="fas fa-images me-1"></i>More {{ ucfirst($sitePhoto->photo_category) }} Photos
                    </a>
                    
                    <a href="{{ route('projects.photos', $sitePhoto->project->id) }}" 
                       class="btn btn-outline-success btn-sm btn-block mb-2">
                        <i class="fas fa-project-diagram me-1"></i>Project Photos
                    </a>
                    
                    <a href="{{ route('photos.featured') }}" 
                       class="btn btn-outline-warning btn-sm btn-block mb-2">
                        <i class="fas fa-star me-1"></i>Featured Photos
                    </a>

                    <a href="{{ route('photos.search') }}" 
                       class="btn btn-outline-info btn-sm btn-block">
                        <i class="fas fa-search me-1"></i>Search Photos
                    </a>
                </div>
            </div>

            <!-- Related Photos -->
            @if($relatedPhotos->count() > 0)
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-images me-2"></i>Related Photos
                            <small class="text-muted">from {{ $sitePhoto->project->name }}</small>
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @foreach($relatedPhotos as $related)
                                <div class="col-6 mb-3">
                                    <div class="card border-0 shadow-sm hover-shadow-sm">
                                        <div class="position-relative" style="height: 100px; overflow: hidden;">
                                            <img src="{{ Storage::url($related->photo_path) }}" 
                                                 alt="{{ $related->title }}" 
                                                 class="card-img-top h-100 w-100"
                                                 style="object-fit: cover; cursor: pointer;"
                                                 onclick="window.location.href='{{ route('photos.show', $related->id) }}'">
                                            
                                            @if($related->is_featured)
                                                <div class="position-absolute" style="top: 4px; left: 4px;">
                                                    <span class="badge badge-warning badge-sm">
                                                        <i class="fas fa-star"></i>
                                                    </span>
                                                </div>
                                            @endif
                                        </div>
                                        <div class="card-body p-2">
                                            <h6 class="card-title small font-weight-bold mb-1" 
                                                title="{{ $related->title }}">
                                                {{ Str::limit($related->title, 25) }}
                                            </h6>
                                            <small class="text-muted">
                                                {{ $related->photo_date->format('M d') }}
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        
                        @if($relatedPhotos->count() >= 6)
                            <div class="text-center">
                                <a href="{{ route('projects.photos', $sitePhoto->project->id) }}" 
                                   class="btn btn-sm btn-outline-primary">
                                    View All Project Photos
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            <!-- Photo Stats -->
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-chart-bar me-2"></i>Photo Statistics
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="border-right">
                                <div class="h5 font-weight-bold text-primary">
                                    {{ $sitePhoto->created_at->diffInDays($sitePhoto->photo_date) }}
                                </div>
                                <small class="text-muted">Days to Upload</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="h5 font-weight-bold text-success">
                                {{ $relatedPhotos->count() + 1 }}
                            </div>
                            <small class="text-muted">Project Photos</small>
                        </div>
                    </div>
                    
                    @if($sitePhoto->reviewed_at)
                        <hr>
                        <div class="row text-center">
                            <div class="col-12">
                                <div class="h6 font-weight-bold text-info">
                                    {{ $sitePhoto->submitted_at->diffInDays($sitePhoto->reviewed_at) }}
                                </div>
                                <small class="text-muted">Days to Review</small>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.hover-shadow-sm {
    transition: all 0.2s ease;
}

.hover-shadow-sm:hover {
    box-shadow: 0 0.2rem 0.5rem rgba(0, 0, 0, 0.1) !important;
    transform: translateY(-1px);
}

.card-img-top {
    transition: transform 0.3s ease;
}

.card:hover .card-img-top {
    transform: scale(1.02);
}

.btn-block {
    width: 100%;
    display: block;
}

.bg-opacity-75 {
    background-color: rgba(0, 0, 0, 0.75) !important;
}

@media (max-width: 768px) {
    .col-lg-8, .col-lg-4 {
        margin-bottom: 1rem;
    }
}
</style>

@push('scripts')
<script>
// Image zoom functionality (optional)
document.addEventListener('DOMContentLoaded', function() {
    const mainImage = document.querySelector('.card-body img');
    if (mainImage) {
        mainImage.addEventListener('click', function() {
            // Create modal for full-size image view
            const modal = document.createElement('div');
            modal.className = 'modal fade';
            modal.innerHTML = `
                <div class="modal-dialog modal-xl">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">${this.alt}</h5>
                            <button type="button" class="close" data-dismiss="modal">
                                <span>&times;</span>
                            </button>
                        </div>
                        <div class="modal-body text-center">
                            <img src="${this.src}" alt="${this.alt}" class="img-fluid">
                        </div>
                    </div>
                </div>
            `;
            document.body.appendChild(modal);
            $(modal).modal('show');
            
            // Remove modal from DOM when hidden
            $(modal).on('hidden.bs.modal', function() {
                document.body.removeChild(modal);
            });
        });
        
        // Add cursor pointer to indicate clickable
        mainImage.style.cursor = 'pointer';
    }
});
</script>
@endpush
@endsection