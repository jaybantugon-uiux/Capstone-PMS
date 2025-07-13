{{-- resources/views/sc/site-photos/edit.blade.php --}}
@extends('layouts.app')

@section('title', 'Edit Photo - ' . $sitePhoto->title)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('sc.site-photos.index') }}">Site Photos</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('sc.site-photos.show', $sitePhoto) }}">{{ $sitePhoto->title }}</a></li>
                            <li class="breadcrumb-item active">Edit</li>
                        </ol>
                    </nav>
                    <h1 class="h3 mb-0">Edit Photo</h1>
                    <p class="text-muted">Update photo information and resubmit for review</p>
                </div>
                <div>
                    <a href="{{ route('sc.site-photos.show', $sitePhoto) }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Back to Photo
                    </a>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-8">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <form action="{{ route('sc.site-photos.update', $sitePhoto) }}" method="POST" enctype="multipart/form-data" id="photoEditForm">
                                @csrf
                                @method('PUT')
                                
                                {{-- Current Photo Display --}}
                                <div class="mb-4">
                                    <label class="form-label">Current Photo</label>
                                    <div class="current-photo-container">
                                        <img src="{{ $sitePhoto->photo_url }}" 
                                             class="img-thumbnail current-photo" 
                                             alt="{{ $sitePhoto->title }}"
                                             style="max-width: 300px; max-height: 300px; object-fit: cover;">
                                    </div>
                                </div>

                                {{-- Photo Upload (Optional) --}}
                                <div class="mb-4">
                                    <label for="photo" class="form-label">Replace Photo (Optional)</label>
                                    <div class="photo-upload-container">
                                        <input type="file" class="form-control @error('photo') is-invalid @enderror" 
                                               id="photo" name="photo" accept="image/*">
                                        <div class="invalid-feedback">
                                            @error('photo'){{ $message }}@enderror
                                        </div>
                                        <small class="form-text text-muted">
                                            Maximum file size: 50MB. Leave empty to keep current photo.
                                        </small>
                                        
                                        {{-- Image Preview for New Photo --}}
                                        <div id="imagePreview" class="mt-3" style="display: none;">
                                            <img id="previewImg" src="#" alt="Preview" class="img-thumbnail" style="max-width: 300px; max-height: 300px;">
                                            <div class="mt-2">
                                                <button type="button" class="btn btn-sm btn-outline-danger" id="removePreview">
                                                    <i class="fas fa-times me-1"></i>Remove
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Basic Information --}}
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="project_id" class="form-label required">Project</label>
                                        <select class="form-select @error('project_id') is-invalid @enderror" 
                                                id="project_id" name="project_id" required>
                                            <option value="">Select Project</option>
                                            @foreach($projects as $project)
                                                <option value="{{ $project->id }}" 
                                                        {{ old('project_id', $sitePhoto->project_id) == $project->id ? 'selected' : '' }}>
                                                    {{ $project->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <div class="invalid-feedback">
                                            @error('project_id'){{ $message }}@enderror
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label for="task_id" class="form-label">Related Task (Optional)</label>
                                        <select class="form-select @error('task_id') is-invalid @enderror" 
                                                id="task_id" name="task_id">
                                            <option value="">Select Task (Optional)</option>
                                            @foreach($tasks as $task)
                                                <option value="{{ $task->id }}" 
                                                        {{ old('task_id', $sitePhoto->task_id) == $task->id ? 'selected' : '' }}>
                                                    {{ $task->task_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <div class="invalid-feedback">
                                            @error('task_id'){{ $message }}@enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="title" class="form-label required">Photo Title</label>
                                    <input type="text" class="form-control @error('title') is-invalid @enderror" 
                                           id="title" name="title" value="{{ old('title', $sitePhoto->title) }}" 
                                           placeholder="Enter a descriptive title for this photo" required>
                                    <div class="invalid-feedback">
                                        @error('title'){{ $message }}@enderror
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="description" class="form-label">Description</label>
                                    <textarea class="form-control @error('description') is-invalid @enderror" 
                                              id="description" name="description" rows="3" 
                                              placeholder="Provide additional details about this photo">{{ old('description', $sitePhoto->description) }}</textarea>
                                    <div class="invalid-feedback">
                                        @error('description'){{ $message }}@enderror
                                    </div>
                                </div>

                                {{-- Photo Details --}}
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="photo_date" class="form-label required">Photo Date</label>
                                        <input type="date" class="form-control @error('photo_date') is-invalid @enderror" 
                                               id="photo_date" name="photo_date" 
                                               value="{{ old('photo_date', $sitePhoto->photo_date->format('Y-m-d')) }}" 
                                               max="{{ date('Y-m-d') }}" required>
                                        <div class="invalid-feedback">
                                            @error('photo_date'){{ $message }}@enderror
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label for="photo_category" class="form-label required">Category</label>
                                        <select class="form-select @error('photo_category') is-invalid @enderror" 
                                                id="photo_category" name="photo_category" required>
                                            <option value="">Select Category</option>
                                            <option value="progress" {{ old('photo_category', $sitePhoto->photo_category) == 'progress' ? 'selected' : '' }}>Progress</option>
                                            <option value="quality" {{ old('photo_category', $sitePhoto->photo_category) == 'quality' ? 'selected' : '' }}>Quality</option>
                                            <option value="safety" {{ old('photo_category', $sitePhoto->photo_category) == 'safety' ? 'selected' : '' }}>Safety</option>
                                            <option value="equipment" {{ old('photo_category', $sitePhoto->photo_category) == 'equipment' ? 'selected' : '' }}>Equipment</option>
                                            <option value="materials" {{ old('photo_category', $sitePhoto->photo_category) == 'materials' ? 'selected' : '' }}>Materials</option>
                                            <option value="workers" {{ old('photo_category', $sitePhoto->photo_category) == 'workers' ? 'selected' : '' }}>Workers</option>
                                            <option value="documentation" {{ old('photo_category', $sitePhoto->photo_category) == 'documentation' ? 'selected' : '' }}>Documentation</option>
                                            <option value="issues" {{ old('photo_category', $sitePhoto->photo_category) == 'issues' ? 'selected' : '' }}>Issues</option>
                                            <option value="completion" {{ old('photo_category', $sitePhoto->photo_category) == 'completion' ? 'selected' : '' }}>Completion</option>
                                            <option value="other" {{ old('photo_category', $sitePhoto->photo_category) == 'other' ? 'selected' : '' }}>Other</option>
                                        </select>
                                        <div class="invalid-feedback">
                                            @error('photo_category'){{ $message }}@enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="location" class="form-label">Location</label>
                                        <input type="text" class="form-control @error('location') is-invalid @enderror" 
                                               id="location" name="location" value="{{ old('location', $sitePhoto->location) }}" 
                                               placeholder="Specific location within the project site">
                                        <div class="invalid-feedback">
                                            @error('location'){{ $message }}@enderror
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label for="weather_conditions" class="form-label">Weather Conditions</label>
                                        <select class="form-select @error('weather_conditions') is-invalid @enderror" 
                                                id="weather_conditions" name="weather_conditions">
                                            <option value="">Select Weather</option>
                                            <option value="sunny" {{ old('weather_conditions', $sitePhoto->weather_conditions) == 'sunny' ? 'selected' : '' }}>Sunny</option>
                                            <option value="cloudy" {{ old('weather_conditions', $sitePhoto->weather_conditions) == 'cloudy' ? 'selected' : '' }}>Cloudy</option>
                                            <option value="rainy" {{ old('weather_conditions', $sitePhoto->weather_conditions) == 'rainy' ? 'selected' : '' }}>Rainy</option>
                                            <option value="stormy" {{ old('weather_conditions', $sitePhoto->weather_conditions) == 'stormy' ? 'selected' : '' }}>Stormy</option>
                                            <option value="windy" {{ old('weather_conditions', $sitePhoto->weather_conditions) == 'windy' ? 'selected' : '' }}>Windy</option>
                                        </select>
                                        <div class="invalid-feedback">
                                            @error('weather_conditions'){{ $message }}@enderror
                                        </div>
                                    </div>
                                </div>

                                {{-- Tags --}}
                                <div class="mb-4">
                                    <label for="tags" class="form-label">Tags</label>
                                    <div id="tagsContainer">
                                        <div class="input-group mb-2">
                                            <input type="text" class="form-control" id="newTag" placeholder="Add a tag">
                                            <button type="button" class="btn btn-outline-secondary" id="addTag">
                                                <i class="fas fa-plus"></i> Add
                                            </button>
                                        </div>
                                        <div id="tagsList" class="d-flex flex-wrap gap-2">
                                            @php
                                                $currentTags = old('tags', $sitePhoto->tags ?? []);
                                            @endphp
                                            @if($currentTags)
                                                @foreach($currentTags as $tag)
                                                    @if($tag)
                                                        <span class="badge bg-secondary d-flex align-items-center">
                                                            {{ $tag }}
                                                            <input type="hidden" name="tags[]" value="{{ $tag }}">
                                                            <button type="button" class="btn-close btn-close-white ms-2" style="font-size: 0.6em;" onclick="removeTag(this)"></button>
                                                        </span>
                                                    @endif
                                                @endforeach
                                            @endif
                                        </div>
                                    </div>
                                    <small class="form-text text-muted">Add tags to help organize and search photos</small>
                                </div>

                                {{-- Rejection Reason (if applicable) --}}
                                @if($sitePhoto->submission_status === 'rejected' && $sitePhoto->rejection_reason)
                                    <div class="alert alert-warning mb-4">
                                        <h6 class="alert-heading">
                                            <i class="fas fa-exclamation-triangle me-2"></i>Previous Rejection Reason:
                                        </h6>
                                        <p class="mb-0">{{ $sitePhoto->rejection_reason }}</p>
                                    </div>
                                @endif

                                {{-- Submit Buttons --}}
                                <div class="d-flex justify-content-between">
                                    <a href="{{ route('sc.site-photos.show', $sitePhoto) }}" class="btn btn-outline-secondary">
                                        <i class="fas fa-times me-2"></i>Cancel
                                    </a>
                                    <button type="submit" class="btn btn-primary" id="submitBtn">
                                        <i class="fas fa-save me-2"></i>Update & Resubmit
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                {{-- Current Info Sidebar --}}
                <div class="col-lg-4">
                    {{-- Current Status --}}
                    <div class="card shadow-sm mb-4">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-info-circle me-2"></i>Current Status
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3">
                                <span class="badge bg-{{ $sitePhoto->submission_status_badge_color }} me-2">
                                    {{ $sitePhoto->formatted_submission_status }}
                                </span>
                                @if($sitePhoto->is_featured)
                                    <span class="badge bg-warning me-2">
                                        <i class="fas fa-star me-1"></i>Featured
                                    </span>
                                @endif
                                @if($sitePhoto->is_public)
                                    <span class="badge bg-info">
                                        <i class="fas fa-globe me-1"></i>Public
                                    </span>
                                @endif
                            </div>
                            
                            <div class="small text-muted">
                                <p><strong>Uploaded:</strong> {{ $sitePhoto->created_at->format('M d, Y H:i') }}</p>
                                @if($sitePhoto->submitted_at)
                                    <p><strong>Submitted:</strong> {{ $sitePhoto->formatted_submitted_at }}</p>
                                @endif
                                @if($sitePhoto->reviewed_at)
                                    <p><strong>Reviewed:</strong> {{ $sitePhoto->formatted_reviewed_at }}</p>
                                    <p><strong>Reviewer:</strong> {{ $sitePhoto->reviewer->full_name }}</p>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- File Information --}}
                    <div class="card shadow-sm mb-4">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-file-image me-2"></i>File Information
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="small">
                                <p><strong>Original Name:</strong> {{ $sitePhoto->original_filename }}</p>
                                <p><strong>File Size:</strong> {{ $sitePhoto->formatted_file_size }}</p>
                                <p><strong>MIME Type:</strong> {{ $sitePhoto->mime_type }}</p>
                                @if($sitePhoto->image_dimensions)
                                    <p><strong>Dimensions:</strong> {{ $sitePhoto->image_dimensions }}</p>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Guidelines --}}
                    <div class="card shadow-sm mb-4">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-lightbulb text-warning me-2"></i>Edit Guidelines
                            </h5>
                        </div>
                        <div class="card-body">
                            <ul class="small mb-0">
                                <li>Update title and description to address feedback</li>
                                <li>Correct category if misclassified</li>
                                <li>Add or update location information</li>
                                <li>Replace photo only if quality issues were noted</li>
                                <li>Review tags for better searchability</li>
                                <li>After saving, photo will be resubmitted for review</li>
                            </ul>
                        </div>
                    </div>

                    {{-- Previous Comments --}}
                    @if($sitePhoto->admin_comments || $sitePhoto->rejection_reason)
                        <div class="card shadow-sm">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-comments me-2"></i>Admin Feedback
                                </h5>
                            </div>
                            <div class="card-body">
                                @if($sitePhoto->rejection_reason)
                                    <div class="mb-3">
                                        <h6 class="text-danger">Rejection Reason:</h6>
                                        <p class="small mb-0">{{ $sitePhoto->rejection_reason }}</p>
                                    </div>
                                @endif
                                
                                @if($sitePhoto->admin_comments)
                                    <div>
                                        <h6>Additional Comments:</h6>
                                        <p class="small mb-0">{{ $sitePhoto->admin_comments }}</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Loading Modal --}}
<div class="modal fade" id="updatingModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body text-center py-4">
                <div class="spinner-border text-primary mb-3" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <h5>Updating Photo...</h5>
                <p class="text-muted mb-0">Please wait while we process your changes.</p>
                <div class="progress mt-3">
                    <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 100%"></div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.required::after {
    content: " *";
    color: #dc3545;
}

.current-photo {
    border: 2px solid #e9ecef;
    border-radius: 8px;
}

.photo-upload-container {
    position: relative;
}

#tagsList .badge {
    cursor: default;
}

#tagsList .badge button {
    cursor: pointer;
}

.card {
    border: none;
    border-radius: 12px;
}

.card-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 12px 12px 0 0 !important;
}

.alert {
    border-radius: 8px;
    border: none;
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Image preview functionality
    const photoInput = document.getElementById('photo');
    const imagePreview = document.getElementById('imagePreview');
    const previewImg = document.getElementById('previewImg');
    const removePreview = document.getElementById('removePreview');
    const currentPhoto = document.querySelector('.current-photo');
    
    photoInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                previewImg.src = e.target.result;
                imagePreview.style.display = 'block';
                currentPhoto.style.opacity = '0.5';
            };
            reader.readAsDataURL(file);
        }
    });
    
    removePreview.addEventListener('click', function() {
        photoInput.value = '';
        imagePreview.style.display = 'none';
        previewImg.src = '#';
        currentPhoto.style.opacity = '1';
    });
    
    // Project selection - load tasks
    const projectSelect = document.getElementById('project_id');
    const taskSelect = document.getElementById('task_id');
    
    projectSelect.addEventListener('change', function() {
        const projectId = this.value;
        const currentTaskId = '{{ $sitePhoto->task_id }}';
        
        taskSelect.innerHTML = '<option value="">Loading tasks...</option>';
        
        if (projectId) {
            fetch(`{{ route('sc.site-photos.get-project-tasks') }}?project_id=${projectId}`)
                .then(response => response.json())
                .then(tasks => {
                    taskSelect.innerHTML = '<option value="">Select Task (Optional)</option>';
                    tasks.forEach(task => {
                        const option = document.createElement('option');
                        option.value = task.id;
                        option.textContent = task.task_name;
                        option.selected = task.id == currentTaskId;
                        taskSelect.appendChild(option);
                    });
                })
                .catch(error => {
                    console.error('Error loading tasks:', error);
                    taskSelect.innerHTML = '<option value="">Error loading tasks</option>';
                });
        } else {
            taskSelect.innerHTML = '<option value="">Select Task (Optional)</option>';
        }
    });
    
    // Tags functionality
    const newTagInput = document.getElementById('newTag');
    const addTagBtn = document.getElementById('addTag');
    const tagsList = document.getElementById('tagsList');
    
    function addTag() {
        const tagValue = newTagInput.value.trim();
        if (tagValue && !isDuplicateTag(tagValue)) {
            const tagElement = createTagElement(tagValue);
            tagsList.appendChild(tagElement);
            newTagInput.value = '';
        }
    }
    
    function isDuplicateTag(value) {
        const existingTags = Array.from(tagsList.querySelectorAll('input[name="tags[]"]'));
        return existingTags.some(input => input.value.toLowerCase() === value.toLowerCase());
    }
    
    function createTagElement(value) {
        const span = document.createElement('span');
        span.className = 'badge bg-secondary d-flex align-items-center';
        span.innerHTML = `
            ${value}
            <input type="hidden" name="tags[]" value="${value}">
            <button type="button" class="btn-close btn-close-white ms-2" style="font-size: 0.6em;" onclick="removeTag(this)"></button>
        `;
        return span;
    }
    
    addTagBtn.addEventListener('click', addTag);
    newTagInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            addTag();
        }
    });
    
    // Form submission with loading modal
    const form = document.getElementById('photoEditForm');
    const updatingModal = new bootstrap.Modal(document.getElementById('updatingModal'));
    
    form.addEventListener('submit', function(e) {
        const submitBtn = document.getElementById('submitBtn');
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Updating...';
        updatingModal.show();
    });
});

// Remove tag function (called from onclick)
function removeTag(button) {
    button.closest('.badge').remove();
}

// Validate file size
function validateFileSize() {
    const photoInput = document.getElementById('photo');
    const file = photoInput.files[0];
    
    if (file && file.size > 52428800) { // 50MB in bytes
        alert('File size must be less than 50MB. Please choose a smaller file.');
        photoInput.value = '';
        document.getElementById('imagePreview').style.display = 'none';
        document.querySelector('.current-photo').style.opacity = '1';
        return false;
    }
    return true;
}

document.getElementById('photo').addEventListener('change', validateFileSize);
</script>
@endpush