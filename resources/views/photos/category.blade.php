@extends('app')

@section('title', $categoryName . ' Photos')

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
                    <li class="breadcrumb-item active" aria-current="page">{{ $categoryName }}</li>
                </ol>
            </nav>
            <h1 class="h3 text-gray-800 mb-0">
                @php
                    $categoryIcons = [
                        'progress' => 'fa-chart-line',
                        'quality' => 'fa-check-circle',
                        'safety' => 'fa-shield-alt',
                        'equipment' => 'fa-tools',
                        'materials' => 'fa-boxes',
                        'workers' => 'fa-hard-hat',
                        'documentation' => 'fa-file-alt',
                        'issues' => 'fa-exclamation-triangle',
                        'completion' => 'fa-flag-checkered',
                        'other' => 'fa-images'
                    ];
                    $icon = $categoryIcons[$category] ?? 'fa-images';
                @endphp
                <i class="fas {{ $icon }} me-2"></i>{{ $categoryName }} Photos
            </h1>
            <p class="text-muted mb-0">Photos categorized as {{ strtolower($categoryName) }}</p>
        </div>
        
        <!-- Action Buttons -->
        <div class="d-flex gap-2">
            <a href="{{ route('photos.search') }}" class="btn btn-outline-primary">
                <i class="fas fa-search me-1"></i>Search Photos
            </a>
            <a href="{{ route('photos.featured') }}" class="btn btn-outline-warning">
                <i class="fas fa-star me-1"></i>Featured
            </a>
            @if(in_array(auth()->user()->role, ['admin', 'pm', 'sc']))
                <a href="{{ route('projects.index') }}" class="btn btn-primary">
                    <i class="fas fa-project-diagram me-1"></i>Projects
                </a>
            @endif
        </div>
    </div>

    <!-- Category Stats -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Photos
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $photos->total() }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas {{ $icon }} fa-2x text-primary"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Projects Covered
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $photos->pluck('project.id')->unique()->count() }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-project-diagram fa-2x text-success"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Featured Photos
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $photos->where('is_featured', true)->count() }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-star fa-2x text-warning"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Contributors
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $photos->pluck('uploader.id')->unique()->count() }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-info"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Category Navigation -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Browse Other Categories</h6>
        </div>
        <div class="card-body">
            <div class="row">
                @php
                    $allCategories = [
                        'progress' => ['icon' => 'fa-chart-line', 'color' => 'primary', 'name' => 'Progress'],
                        'quality' => ['icon' => 'fa-check-circle', 'color' => 'success', 'name' => 'Quality'],
                        'safety' => ['icon' => 'fa-shield-alt', 'color' => 'warning', 'name' => 'Safety'],
                        'equipment' => ['icon' => 'fa-tools', 'color' => 'info', 'name' => 'Equipment'],
                        'materials' => ['icon' => 'fa-boxes', 'color' => 'secondary', 'name' => 'Materials'],
                        'workers' => ['icon' => 'fa-hard-hat', 'color' => 'dark', 'name' => 'Workers'],
                        'documentation' => ['icon' => 'fa-file-alt', 'color' => 'light', 'name' => 'Documentation'],
                        'issues' => ['icon' => 'fa-exclamation-triangle', 'color' => 'danger', 'name' => 'Issues'],
                        'completion' => ['icon' => 'fa-flag-checkered', 'color' => 'success', 'name' => 'Completion'],
                        'other' => ['icon' => 'fa-images', 'color' => 'secondary', 'name' => 'Other']
                    ];
                @endphp
                
                @foreach($allCategories as $slug => $cat)
                    <div class="col-lg-2 col-md-3 col-6 mb-3">
                        <a href="{{ route('photos.category', $slug) }}" 
                           class="btn btn-{{ $slug === $category ? '' : 'outline-' }}{{ $cat['color'] }} btn-block {{ $slug === $category ? 'active' : '' }}">
                            <i class="fas {{ $cat['icon'] }} d-block mb-1"></i>
                            <small>{{ $cat['name'] }}</small>
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Photos Grid -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">
                {{ $categoryName }} Photos Gallery
                <span class="badge badge-secondary ml-2">{{ $photos->total() }} photos</span>
            </h6>
            
            <!-- Sort Options -->
            <div class="dropdown">
                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" 
                        id="sortDropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="fas fa-sort me-1"></i>Sort by Date
                </button>
                <div class="dropdown-menu" aria-labelledby="sortDropdown">
                    <a class="dropdown-item" href="{{ route('photos.category', ['category' => $category, 'sort' => 'newest']) }}">
                        <i class="fas fa-arrow-down me-1"></i>Newest First
                    </a>
                    <a class="dropdown-item" href="{{ route('photos.category', ['category' => $category, 'sort' => 'oldest']) }}">
                        <i class="fas fa-arrow-up me-1"></i>Oldest First
                    </a>
                    <a class="dropdown-item" href="{{ route('photos.category', ['category' => $category, 'sort' => 'featured']) }}">
                        <i class="fas fa-star me-1"></i>Featured First
                    </a>
                </div>
            </div>
        </div>
        <div class="card-body">
            @if($photos->count() > 0)
                <div class="row">
                    @foreach($photos as $photo)
                        <div class="col-lg-4 col-md-6 mb-4">
                            <div class="card h-100 shadow-sm hover-shadow">
                                <!-- Photo Image -->
                                <div class="position-relative overflow-hidden" style="height: 220px;">
                                    <img src="{{ Storage::url($photo->photo_path) }}" 
                                         alt="{{ $photo->title }}" 
                                         class="card-img-top h-100 w-100"
                                         style="object-fit: cover; cursor: pointer;"
                                         onclick="window.location.href='{{ route('photos.show', $photo->id) }}'">
                                    
                                    <!-- Featured Badge -->
                                    @if($photo->is_featured)
                                        <div class="position-absolute" style="top: 8px; left: 8px;">
                                            <span class="badge badge-warning">
                                                <i class="fas fa-star me-1"></i>Featured
                                            </span>
                                        </div>
                                    @endif
                                    
                                    <!-- Date Badge -->
                                    <div class="position-absolute" style="bottom: 8px; right: 8px;">
                                        <span class="badge badge-dark bg-dark text-white">
                                            {{ $photo->photo_date->format('M d, Y') }}
                                        </span>
                                    </div>
                                </div>
                                
                                <!-- Card Body -->
                                <div class="card-body p-3">
                                    <h6 class="card-title font-weight-bold mb-2" title="{{ $photo->title }}">
                                        {{ Str::limit($photo->title, 40) }}
                                    </h6>
                                    
                                    @if($photo->description)
                                        <p class="card-text small text-muted mb-3" style="height: 45px; overflow: hidden;">
                                            {{ Str::limit($photo->description, 90) }}
                                        </p>
                                    @endif
                                    
                                    <!-- Project Info -->
                                    <div class="mb-2">
                                        <small class="text-muted">
                                            <i class="fas fa-project-diagram me-1"></i>
                                            <a href="{{ route('projects.show', $photo->project->id) }}" 
                                               class="text-primary text-decoration-none">
                                                {{ Str::limit($photo->project->name, 30) }}
                                            </a>
                                        </small>
                                    </div>
                                    
                                    <!-- Additional Details -->
                                    <div class="d-flex justify-content-between align-items-center text-xs text-muted">
                                        <div>
                                            <i class="fas fa-user me-1"></i>
                                            {{ $photo->uploader->first_name }}
                                        </div>
                                        @if($photo->location)
                                            <div class="text-truncate" style="max-width: 100px;" title="{{ $photo->location }}">
                                                <i class="fas fa-map-marker-alt me-1"></i>
                                                {{ $photo->location }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                
                                <!-- Card Footer -->
                                <div class="card-footer bg-transparent border-top-0 p-2">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <!-- Weather if available -->
                                        @if($photo->weather_conditions)
                                            <small class="text-muted">
                                                @php
                                                    $weatherIcons = [
                                                        'sunny' => 'fa-sun',
                                                        'cloudy' => 'fa-cloud',
                                                        'rainy' => 'fa-cloud-rain',
                                                        'stormy' => 'fa-thunderstorm',
                                                        'windy' => 'fa-wind'
                                                    ];
                                                    $weatherIcon = $weatherIcons[$photo->weather_conditions] ?? 'fa-cloud-sun';
                                                @endphp
                                                <i class="fas {{ $weatherIcon }} me-1"></i>
                                                {{ ucfirst($photo->weather_conditions) }}
                                            </small>
                                        @else
                                            <div></div>
                                        @endif
                                        
                                        <!-- View Button -->
                                        <a href="{{ route('photos.show', $photo->id) }}" 
                                           class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-eye me-1"></i>View
                                        </a>
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
                    <i class="fas {{ $icon }} fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No {{ $categoryName }} Photos</h5>
                    <p class="text-muted">No photos have been uploaded in the {{ strtolower($categoryName) }} category yet.</p>
                    <div class="d-flex justify-content-center gap-2">
                        <a href="{{ route('photos.search') }}" class="btn btn-outline-primary">
                            <i class="fas fa-search me-1"></i>Search All Photos
                        </a>
                        <a href="{{ route('photos.featured') }}" class="btn btn-primary">
                            <i class="fas fa-star me-1"></i>View Featured
                        </a>
                        @if(auth()->user()->role === 'sc')
                            <a href="{{ route('sc.site-photos.create') }}" class="btn btn-success">
                                <i class="fas fa-plus me-1"></i>Upload Photo
                            </a>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<style>
.hover-shadow {
    transition: all 0.3s ease;
}

.hover-shadow:hover {
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
    transform: translateY(-2px);
}

.card-img-top {
    transition: transform 0.3s ease;
}

.card:hover .card-img-top {
    transform: scale(1.05);
}

.btn-block {
    width: 100%;
    display: block;
}

.border-left-primary {
    border-left: 4px solid #4e73df !important;
}
.border-left-success {
    border-left: 4px solid #1cc88a !important;
}
.border-left-info {
    border-left: 4px solid #36b9cc !important;
}
.border-left-warning {
    border-left: 4px solid #f6c23e !important;
}

@media (max-width: 768px) {
    .col-6 .btn-block {
        padding: 0.5rem;
        font-size: 0.875rem;
    }
}
</style>
@endsection