@extends('app')

@section('title', 'Search Photos')

@section('content')
<div class="container-fluid px-4">
    <!-- Header Section -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 text-gray-800 mb-0">
                <i class="fas fa-search me-2"></i>Search Photos
            </h1>
            <p class="text-muted mb-0">Find project photos by keyword, project, or category</p>
        </div>
        
        <!-- Quick Links -->
        <div class="d-flex gap-2">
            <a href="{{ route('photos.featured') }}" class="btn btn-outline-warning">
                <i class="fas fa-star me-1"></i>Featured Photos
            </a>
            @if(in_array(auth()->user()->role, ['admin', 'pm', 'sc']))
                <a href="{{ route('projects.index') }}" class="btn btn-primary">
                    <i class="fas fa-project-diagram me-1"></i>View Projects
                </a>
            @endif
        </div>
    </div>

    <!-- Search Form -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-filter me-2"></i>Search & Filter Photos
            </h6>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('photos.search') }}" class="row g-3">
                <!-- Search Query -->
                <div class="col-md-4">
                    <label for="search_query" class="form-label">Search Keywords</label>
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="fas fa-search"></i>
                        </span>
                        <input type="text" 
                               class="form-control" 
                               id="search_query"
                               name="q" 
                               value="{{ $query }}" 
                               placeholder="Search by title, description, or tags...">
                    </div>
                </div>

                <!-- Project Filter -->
                <div class="col-md-3">
                    <label for="project_filter" class="form-label">Project</label>
                    <select class="form-select" id="project_filter" name="project">
                        <option value="">All Projects</option>
                        @foreach($projects as $proj)
                            <option value="{{ $proj->id }}" {{ $project == $proj->id ? 'selected' : '' }}>
                                {{ $proj->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Category Filter -->
                <div class="col-md-3">
                    <label for="category_filter" class="form-label">Category</label>
                    <select class="form-select" id="category_filter" name="category">
                        <option value="">All Categories</option>
                        <option value="progress" {{ $category == 'progress' ? 'selected' : '' }}>Progress</option>
                        <option value="quality" {{ $category == 'quality' ? 'selected' : '' }}>Quality</option>
                        <option value="safety" {{ $category == 'safety' ? 'selected' : '' }}>Safety</option>
                        <option value="equipment" {{ $category == 'equipment' ? 'selected' : '' }}>Equipment</option>
                        <option value="materials" {{ $category == 'materials' ? 'selected' : '' }}>Materials</option>
                        <option value="workers" {{ $category == 'workers' ? 'selected' : '' }}>Workers</option>
                        <option value="documentation" {{ $category == 'documentation' ? 'selected' : '' }}>Documentation</option>
                        <option value="issues" {{ $category == 'issues' ? 'selected' : '' }}>Issues</option>
                        <option value="completion" {{ $category == 'completion' ? 'selected' : '' }}>Completion</option>
                        <option value="other" {{ $category == 'other' ? 'selected' : '' }}>Other</option>
                    </select>
                </div>

                <!-- Search Buttons -->
                <div class="col-md-2">
                    <label class="form-label">&nbsp;</label>
                    <div class="d-flex flex-column gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search me-1"></i>Search
                        </button>
                        <a href="{{ route('photos.search') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-times me-1"></i>Clear
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Search Results -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">
                Search Results
                @if($query || $project || $category)
                    <span class="badge badge-info ml-2">{{ $photos->total() }} results</span>
                @else
                    <span class="badge badge-secondary ml-2">{{ $photos->total() }} total photos</span>
                @endif
            </h6>
            
            <!-- Results Info -->
            @if($query || $project || $category)
                <div class="text-sm text-muted">
                    @if($query)
                        <span class="badge badge-light">Query: "{{ $query }}"</span>
                    @endif
                    @if($project)
                        <span class="badge badge-light">
                            Project: {{ $projects->where('id', $project)->first()->name ?? 'Unknown' }}
                        </span>
                    @endif
                    @if($category)
                        <span class="badge badge-light">Category: {{ ucfirst($category) }}</span>
                    @endif
                </div>
            @endif
        </div>
        <div class="card-body">
            @if($photos->count() > 0)
                <!-- Results Grid -->
                <div class="row">
                    @foreach($photos as $photo)
                        <div class="col-lg-4 col-md-6 mb-4">
                            <div class="card h-100 shadow-sm hover-shadow">
                                <!-- Photo Image -->
                                <div class="position-relative overflow-hidden" style="height: 200px;">
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
                                    
                                    <!-- Category Badge -->
                                    <div class="position-absolute" style="top: 8px; right: 8px;">
                                        <span class="badge badge-primary">
                                            {{ ucfirst($photo->photo_category) }}
                                        </span>
                                    </div>
                                </div>
                                
                                <!-- Card Body -->
                                <div class="card-body p-3">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <h6 class="card-title font-weight-bold mb-0" title="{{ $photo->title }}">
                                            {{ Str::limit($photo->title, 30) }}
                                        </h6>
                                        <small class="text-muted ml-2">
                                            {{ $photo->photo_date->format('M d') }}
                                        </small>
                                    </div>
                                    
                                    @if($photo->description)
                                        <p class="card-text small text-muted mb-2" style="height: 40px; overflow: hidden;">
                                            {{ Str::limit($photo->description, 80) }}
                                        </p>
                                    @endif
                                    
                                    <!-- Project Link -->
                                    <div class="mb-2">
                                        <small class="text-muted">
                                            <i class="fas fa-project-diagram me-1"></i>
                                            <a href="{{ route('projects.show', $photo->project->id) }}" 
                                               class="text-primary text-decoration-none">
                                                {{ Str::limit($photo->project->name, 25) }}
                                            </a>
                                        </small>
                                    </div>
                                    
                                    <!-- Photo Details -->
                                    <div class="d-flex justify-content-between align-items-center text-xs text-muted">
                                        <div>
                                            <i class="fas fa-user me-1"></i>
                                            {{ $photo->uploader->first_name }}
                                        </div>
                                        @if($photo->location)
                                            <div class="text-truncate" style="max-width: 120px;" title="{{ $photo->location }}">
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
                    {{ $photos->appends(request()->query())->links() }}
                </div>
            @else
                <!-- Empty State -->
                <div class="text-center py-5">
                    @if($query || $project || $category)
                        <i class="fas fa-search fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No Photos Found</h5>
                        <p class="text-muted">No photos match your search criteria. Try adjusting your filters or search terms.</p>
                        <div class="d-flex justify-content-center gap-2">
                            <a href="{{ route('photos.search') }}" class="btn btn-outline-primary">
                                <i class="fas fa-times me-1"></i>Clear Filters
                            </a>
                            <a href="{{ route('photos.featured') }}" class="btn btn-primary">
                                <i class="fas fa-star me-1"></i>View Featured Photos
                            </a>
                        </div>
                    @else
                        <i class="fas fa-camera fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No Photos Available</h5>
                        <p class="text-muted">No photos have been uploaded yet.</p>
                        @if(auth()->user()->role === 'sc')
                            <a href="{{ route('sc.site-photos.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus me-1"></i>Upload First Photo
                            </a>
                        @endif
                    @endif
                </div>
            @endif
        </div>
    </div>

    <!-- Quick Category Links -->
    @if(!$category)
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Browse by Category</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    @php
                        $quickCategories = [
                            'progress' => ['icon' => 'fa-chart-line', 'color' => 'primary'],
                            'quality' => ['icon' => 'fa-check-circle', 'color' => 'success'],
                            'safety' => ['icon' => 'fa-shield-alt', 'color' => 'warning'],
                            'equipment' => ['icon' => 'fa-tools', 'color' => 'info'],
                            'completion' => ['icon' => 'fa-flag-checkered', 'color' => 'success']
                        ];
                    @endphp
                    
                    @foreach($quickCategories as $slug => $catInfo)
                        <div class="col-lg-2 col-md-4 col-6 mb-3">
                            <a href="{{ route('photos.search', ['category' => $slug]) }}" 
                               class="btn btn-outline-{{ $catInfo['color'] }} btn-block">
                                <i class="fas {{ $catInfo['icon'] }} d-block mb-1"></i>
                                <small>{{ ucfirst($slug) }}</small>
                            </a>
                        </div>
                    @endforeach
                    
                    <div class="col-lg-2 col-md-4 col-6 mb-3">
                        <a href="{{ route('photos.category', 'other') }}" 
                           class="btn btn-outline-secondary btn-block">
                            <i class="fas fa-ellipsis-h d-block mb-1"></i>
                            <small>View All</small>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @endif
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

.form-select, .form-control {
    border-radius: 0.25rem;
}

@media (max-width: 768px) {
    .col-6 .btn-block {
        padding: 0.5rem;
        font-size: 0.875rem;
    }
}
</style>

@push('scripts')
<script>
// Auto-submit form when filters change (optional)
document.addEventListener('DOMContentLoaded', function() {
    const projectFilter = document.getElementById('project_filter');
    const categoryFilter = document.getElementById('category_filter');
    
    if (projectFilter && categoryFilter) {
        projectFilter.addEventListener('change', function() {
            if (this.value !== '') {
                this.form.submit();
            }
        });
        
        categoryFilter.addEventListener('change', function() {
            if (this.value !== '') {
                this.form.submit();
            }
        });
    }
});
</script>
@endpush
@endsection