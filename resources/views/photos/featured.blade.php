@extends('app')

@section('title', 'Featured Photos')

@section('content')
<div class="container-fluid px-4">
    <!-- Header Section -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 text-gray-800 mb-0">
                <i class="fas fa-star text-warning me-2"></i>Featured Photos
            </h1>
            <p class="text-muted mb-0">Showcase of outstanding project photos</p>
        </div>
        
        <!-- Action Buttons -->
        <div class="d-flex gap-2">
            <a href="{{ route('photos.search') }}" class="btn btn-outline-primary">
                <i class="fas fa-search me-1"></i>Search Photos
            </a>
            @if(in_array(auth()->user()->role, ['admin', 'pm', 'sc']))
                <a href="{{ route('projects.index') }}" class="btn btn-primary">
                    <i class="fas fa-project-diagram me-1"></i>View Projects
                </a>
            @endif
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Featured Photos
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $photos->total() }}
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
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Categories
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $photos->pluck('photo_category')->unique()->count() }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-tags fa-2x text-info"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Contributors
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $photos->pluck('uploader.id')->unique()->count() }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-primary"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Categories -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Browse by Category</h6>
        </div>
        <div class="card-body">
            <div class="row">
                @php
                    $categories = [
                        'progress' => ['icon' => 'fa-chart-line', 'color' => 'primary', 'name' => 'Progress'],
                        'quality' => ['icon' => 'fa-check-circle', 'color' => 'success', 'name' => 'Quality'],
                        'safety' => ['icon' => 'fa-shield-alt', 'color' => 'warning', 'name' => 'Safety'],
                        'equipment' => ['icon' => 'fa-tools', 'color' => 'info', 'name' => 'Equipment'],
                        'materials' => ['icon' => 'fa-boxes', 'color' => 'secondary', 'name' => 'Materials'],
                        'workers' => ['icon' => 'fa-hard-hat', 'color' => 'dark', 'name' => 'Workers'],
                        'completion' => ['icon' => 'fa-flag-checkered', 'color' => 'success', 'name' => 'Completion']
                    ];
                @endphp
                
                @foreach($categories as $slug => $category)
                    <div class="col-lg-3 col-md-4 col-sm-6 mb-3">
                        <a href="{{ route('photos.category', $slug) }}" class="text-decoration-none">
                            <div class="card border-left-{{ $category['color'] }} h-100 hover-shadow">
                                <div class="card-body text-center py-3">
                                    <i class="fas {{ $category['icon'] }} fa-2x text-{{ $category['color'] }} mb-2"></i>
                                    <h6 class="font-weight-bold text-{{ $category['color'] }}">{{ $category['name'] }}</h6>
                                    <small class="text-muted">
                                        {{ $photos->where('photo_category', $slug)->count() }} photos
                                    </small>
                                </div>
                            </div>
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Photos Grid -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                Featured Photos Gallery
                <span class="badge badge-secondary ml-2">{{ $photos->total() }} photos</span>
            </h6>
        </div>
        <div class="card-body">
            @if($photos->count() > 0)
                <div class="row">
                    @foreach($photos as $photo)
                        <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                            <div class="card h-100 shadow-sm hover-shadow">
                                <!-- Photo Image -->
                                <div class="position-relative overflow-hidden" style="height: 200px;">
                                    <img src="{{ Storage::url($photo->photo_path) }}" 
                                         alt="{{ $photo->title }}" 
                                         class="card-img-top h-100 w-100"
                                         style="object-fit: cover; cursor: pointer;"
                                         onclick="window.location.href='{{ route('photos.show', $photo->id) }}'">
                                    
                                    <!-- Featured Badge -->
                                    <div class="position-absolute" style="top: 8px; left: 8px;">
                                        <span class="badge badge-warning">
                                            <i class="fas fa-star me-1"></i>Featured
                                        </span>
                                    </div>
                                    
                                    <!-- Category Badge -->
                                    <div class="position-absolute" style="top: 8px; right: 8px;">
                                        <span class="badge badge-primary">
                                            {{ ucfirst($photo->photo_category) }}
                                        </span>
                                    </div>
                                </div>
                                
                                <!-- Card Body -->
                                <div class="card-body p-3">
                                    <h6 class="card-title font-weight-bold text-truncate" title="{{ $photo->title }}">
                                        {{ $photo->title }}
                                    </h6>
                                    
                                    @if($photo->description)
                                        <p class="card-text small text-muted" style="height: 40px; overflow: hidden;">
                                            {{ Str::limit($photo->description, 80) }}
                                        </p>
                                    @endif
                                    
                                    <!-- Photo Details -->
                                    <div class="d-flex justify-content-between align-items-center text-xs text-muted mb-2">
                                        <div>
                                            <i class="fas fa-calendar-alt me-1"></i>
                                            {{ $photo->photo_date->format('M d, Y') }}
                                        </div>
                                        @if($photo->location)
                                            <div class="text-truncate ml-2" style="max-width: 100px;" title="{{ $photo->location }}">
                                                <i class="fas fa-map-marker-alt me-1"></i>
                                                {{ $photo->location }}
                                            </div>
                                        @endif
                                    </div>
                                    
                                    <!-- Project and Uploader Info -->
                                    <div class="border-top pt-2">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div class="text-xs">
                                                <strong>Project:</strong>
                                                <a href="{{ route('projects.show', $photo->project->id) }}" 
                                                   class="text-primary text-decoration-none">
                                                    {{ Str::limit($photo->project->name, 20) }}
                                                </a>
                                            </div>
                                            <div class="text-xs text-muted">
                                                by {{ $photo->uploader->first_name }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Card Footer -->
                                <div class="card-footer bg-transparent border-top-0 p-2">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <!-- Weather if available -->
                                        @if($photo->weather_conditions)
                                            <small class="text-muted">
                                                <i class="fas fa-cloud-sun me-1"></i>
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
                    {{ $photos->links() }}
                </div>
            @else
                <!-- Empty State -->
                <div class="text-center py-5">
                    <i class="fas fa-star fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No Featured Photos Yet</h5>
                    <p class="text-muted">Featured photos will appear here once they are marked by administrators.</p>
                    @if(in_array(auth()->user()->role, ['admin', 'pm']))
                        <a href="{{ route('admin.site-photos.index') }}" class="btn btn-primary">
                            <i class="fas fa-cog me-1"></i>Manage Photos
                        </a>
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>

<style>
.hover-shadow {
    transition: box-shadow 0.3s ease;
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
.border-left-secondary {
    border-left: 4px solid #858796 !important;
}
.border-left-dark {
    border-left: 4px solid #5a5c69 !important;
}
</style>
@endsection