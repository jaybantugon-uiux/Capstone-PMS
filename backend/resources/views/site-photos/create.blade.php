
@extends('app')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h2 mb-1">Upload Site Photo</h1>
                    <p class="text-muted mb-0">Document your project progress with photos</p>
                </div>
                <div>
                    <a href="{{ route('sc.site-photos.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Back to Photos
                    </a>
                </div>
            </div>

            <!-- Upload Form -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-camera me-2"></i>Photo Upload Form
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('sc.site-photos.store') }}" method="POST" enctype="multipart/form-data" id="photoUploadForm">
                        @csrf
                        
                        <!-- Photo File Upload -->
                        <div class="mb-4">
                            <label for="photo" class="form-label required">
                                <i class="fas fa-image me-1"></i>Select Photo
                            </label>
                            <input type="file" 
                                   class="form-control @error('photo') is-invalid @enderror" 
                                   id="photo" 
                                   name="photo" 
                                   accept="image/*" 
                                   required>
                            <div class="form-text">
                                Supported formats: JPG, JPEG, PNG, GIF. Maximum size: 50MB
                            </div>
                            @error('photo')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            
                            <!-- Photo Preview -->
                            <div id="photoPreview" class="mt-3" style="display: none;">
                                <img id="previewImage" src="" alt="Preview" class="img-thumbnail" style="max-height: 200px;">
                            </div>
                        </div>

                        <div class="row">
                            <!-- Project Selection -->
                            <div class="col-md-6 mb-3">
                                <label for="project_id" class="form-label required">
                                    <i class="fas fa-project-diagram me-1"></i>Project
                                </label>
                                <select class="form-select @error('project_id') is-invalid @enderror" 
                                        id="project_id" 
                                        name="project_id" 
                                        required>
                                    <option value="">Select Project</option>
                                    @foreach($projects as $project)
                                        <option value="{{ $project->id }}" {{ old('project_id', $selectedProjectId) == $project->id ? 'selected' : '' }}>
                                            {{ $project->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('project_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Task Selection (Optional) -->
                            <div class="col-md-6 mb-3">
                                <label for="task_id" class="form-label">
                                    <i class="fas fa-tasks me-1"></i>Related Task (Optional)
                                </label>
                                <select class="form-select @error('task_id') is-invalid @enderror" 
                                        id="task_id" 
                                        name="task_id">
                                    <option value="">Select Task (Optional)</option>
                                    @foreach($tasks as $task)
                                        <option value="{{ $task->id }}" {{ old('task_id') == $task->id ? 'selected' : '' }}>
                                            {{ $task->task_name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('task_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Photo Title -->
                        <div class="mb-3">
                            <label for="title" class="form-label required">
                                <i class="fas fa-heading me-1"></i>Photo Title
                            </label>
                            <input type="text" 
                                   class="form-control @error('title') is-invalid @enderror" 
                                   id="title" 
                                   name="title" 
                                   value="{{ old('title') }}"
                                   placeholder="Brief, descriptive title for your photo"
                                   maxlength="255"
                                   required>
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Description -->
                        <div class="mb-3">
                            <label for="description" class="form-label">
                                <i class="fas fa-align-left me-1"></i>Description (Optional)
                            </label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" 
                                      name="description" 
                                      rows="3"
                                      placeholder="Provide additional details about what's shown in the photo">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <!-- Photo Date -->
                            <div class="col-md-4 mb-3">
                                <label for="photo_date" class="form-label required">
                                    <i class="fas fa-calendar me-1"></i>Photo Date
                                </label>
                                <input type="date" 
                                       class="form-control @error('photo_date') is-invalid @enderror" 
                                       id="photo_date" 
                                       name="photo_date" 
                                       value="{{ old('photo_date', date('Y-m-d')) }}"
                                       max="{{ date('Y-m-d') }}"
                                       required>
                                @error('photo_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Category -->
                            <div class="col-md-4 mb-3">
                                <label for="photo_category" class="form-label required">
                                    <i class="fas fa-tag me-1"></i>Category
                                </label>
                                <select class="form-select @error('photo_category') is-invalid @enderror" 
                                        id="photo_category" 
                                        name="photo_category" 
                                        required>
                                    <option value="">Select Category</option>
                                    <option value="progress" {{ old('photo_category') == 'progress' ? 'selected' : '' }}>Progress</option>
                                    <option value="quality" {{ old('photo_category') == 'quality' ? 'selected' : '' }}>Quality</option>
                                    <option value="safety" {{ old('photo_category') == 'safety' ? 'selected' : '' }}>Safety</option>
                                    <option value="equipment" {{ old('photo_category') == 'equipment' ? 'selected' : '' }}>Equipment</option>
                                    <option value="materials" {{ old('photo_category') == 'materials' ? 'selected' : '' }}>Materials</option>
                                    <option value="workers" {{ old('photo_category') == 'workers' ? 'selected' : '' }}>Workers</option>
                                    <option value="documentation" {{ old('photo_category') == 'documentation' ? 'selected' : '' }}>Documentation</option>
                                    <option value="issues" {{ old('photo_category') == 'issues' ? 'selected' : '' }}>Issues</option>
                                    <option value="completion" {{ old('photo_category') == 'completion' ? 'selected' : '' }}>Completion</option>
                                    <option value="other" {{ old('photo_category') == 'other' ? 'selected' : '' }}>Other</option>
                                </select>
                                @error('photo_category')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Weather Conditions -->
                            <div class="col-md-4 mb-3">
                                <label for="weather_conditions" class="form-label">
                                    <i class="fas fa-cloud-sun me-1"></i>Weather (Optional)
                                </label>
                                <select class="form-select @error('weather_conditions') is-invalid @enderror" 
                                        id="weather_conditions" 
                                        name="weather_conditions">
                                    <option value="">Select Weather</option>
                                    <option value="sunny" {{ old('weather_conditions') == 'sunny' ? 'selected' : '' }}>
                                        ☀️ Sunny
                                    </option>
                                    <option value="cloudy" {{ old('weather_conditions') == 'cloudy' ? 'selected' : '' }}>
                                        ☁️ Cloudy
                                    </option>
                                    <option value="rainy" {{ old('weather_conditions') == 'rainy' ? 'selected' : '' }}>
                                        🌧️ Rainy
                                    </option>
                                    <option value="stormy" {{ old('weather_conditions') == 'stormy' ? 'selected' : '' }}>
                                        ⛈️ Stormy
                                    </option>
                                    <option value="windy" {{ old('weather_conditions') == 'windy' ? 'selected' : '' }}>
                                        💨 Windy
                                    </option>
                                </select>
                                @error('weather_conditions')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Location -->
                        <div class="mb-3">
                            <label for="location" class="form-label">
                                <i class="fas fa-map-marker-alt me-1"></i>Location (Optional)
                            </label>
                            <input type="text" 
                                   class="form-control @error('location') is-invalid @enderror" 
                                   id="location" 
                                   name="location" 
                                   value="{{ old('location') }}"
                                   placeholder="Specific location within the project site"
                                   maxlength="255">
                            @error('location')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Tags -->
                        <div class="mb-4">
                            <label for="tags" class="form-label">
                                <i class="fas fa-tags me-1"></i>Tags (Optional)
                            </label>
                            <div id="tagsContainer">
                                <div class="input-group mb-2">
                                    <input type="text" 
                                           class="form-control" 
                                           id="tagInput" 
                                           placeholder="Add a tag and press Enter"
                                           maxlength="50">
                                    <button type="button" class="btn btn-outline-secondary" onclick="addTag()">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                                <div id="tagsList" class="mb-2"></div>
                            </div>
                            <div class="form-text">
                                Add relevant tags to help categorize and search for this photo later
                            </div>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('sc.site-photos.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-1"></i> Cancel
                            </a>
                            <button type="submit" class="btn btn-primary" id="submitBtn">
                                <i class="fas fa-upload me-1"></i> Upload Photo
                                <span class="spinner-border spinner-border-sm ms-2 d-none" id="submitSpinner"></span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Quick Tips -->
            <div class="card mt-4">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-lightbulb me-2"></i>Quick Tips for Better Photos
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <ul class="list-unstyled mb-0">
                                <li class="mb-2">
                                    <i class="fas fa-check text-success me-2"></i>
                                    Use good lighting for clear images
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-check text-success me-2"></i>
                                    Capture multiple angles when documenting progress
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-check text-success me-2"></i>
                                    Include reference objects for scale
                                </li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <ul class="list-unstyled mb-0">
                                <li class="mb-2">
                                    <i class="fas fa-check text-success me-2"></i>
                                    Write descriptive titles and descriptions
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-check text-success me-2"></i>
                                    Select appropriate categories for easy searching
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-check text-success me-2"></i>
                                    Add relevant tags for better organization
                                </li>
                            </ul>
                        </div>
                    </div>
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

.form-label i {
    color: #6c757d;
}

#photoPreview {
    border: 2px dashed #dee2e6;
    border-radius: 0.375rem;
    padding: 1rem;
    text-align: center;
}

#previewImage {
    max-width: 100%;
    border-radius: 0.375rem;
}

.tag-item {
    display: inline-block;
    background-color: #e9ecef;
    color: #495057;
    padding: 0.25rem 0.5rem;
    margin: 0.125rem;
    border-radius: 0.25rem;
    font-size: 0.875rem;
}

.tag-item .remove-tag {
    margin-left: 0.5rem;
    color: #6c757d;
    cursor: pointer;
    font-weight: bold;
}

.tag-item .remove-tag:hover {
    color: #dc3545;
}

.card-header h6 {
    color: #495057;
    font-weight: 600;
}

.list-unstyled li {
    font-size: 0.9rem;
}

@media (max-width: 768px) {
    .col-md-4, .col-md-6 {
        margin-bottom: 1rem;
    }
    
    .d-flex.justify-content-between {
        flex-direction: column;
        gap: 1rem;
    }
    
    .btn {
        width: 100%;
    }
}
</style>
@endpush

@push('scripts')
<script>
let tags = [];

document.addEventListener('DOMContentLoaded', function() {
    // Photo preview functionality
    const photoInput = document.getElementById('photo');
    const photoPreview = document.getElementById('photoPreview');
    const previewImage = document.getElementById('previewImage');

    photoInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                previewImage.src = e.target.result;
                photoPreview.style.display = 'block';
            };
            reader.readAsDataURL(file);
        } else {
            photoPreview.style.display = 'none';
        }
    });

    // Project change handler to load tasks
    const projectSelect = document.getElementById('project_id');
    const taskSelect = document.getElementById('task_id');

    projectSelect.addEventListener('change', function() {
        const projectId = this.value;
        
        // Clear existing tasks
        taskSelect.innerHTML = '<option value="">Select Task (Optional)</option>';
        
        if (projectId) {
            // Fetch tasks for selected project
            fetch(`{{ route('sc.site-photos.get-project-tasks') }}?project_id=${projectId}`)
                .then(response => response.json())
                .then(tasks => {
                    tasks.forEach(task => {
                        const option = document.createElement('option');
                        option.value = task.id;
                        option.textContent = task.task_name;
                        taskSelect.appendChild(option);
                    });
                })
                .catch(error => {
                    console.error('Error fetching tasks:', error);
                });
        }
    });

    // Tag input functionality
    const tagInput = document.getElementById('tagInput');
    
    tagInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            addTag();
        }
    });

    // Form submission handler
    const form = document.getElementById('photoUploadForm');
    const submitBtn = document.getElementById('submitBtn');
    const submitSpinner = document.getElementById('submitSpinner');

    form.addEventListener('submit', function(e) {
        // Add tags to form data
        updateTagsInput();
        
        // Show loading state
        submitBtn.disabled = true;
        submitSpinner.classList.remove('d-none');
    });
});

function addTag() {
    const tagInput = document.getElementById('tagInput');
    const tagValue = tagInput.value.trim();
    
    if (tagValue && !tags.includes(tagValue)) {
        tags.push(tagValue);
        updateTagsDisplay();
        tagInput.value = '';
    }
}

function removeTag(index) {
    tags.splice(index, 1);
    updateTagsDisplay();
}

function updateTagsDisplay() {
    const tagsList = document.getElementById('tagsList');
    tagsList.innerHTML = '';
    
    tags.forEach((tag, index) => {
        const tagElement = document.createElement('span');
        tagElement.className = 'tag-item';
        tagElement.innerHTML = `
            ${tag}
            <span class="remove-tag" onclick="removeTag(${index})">&times;</span>
        `;
        tagsList.appendChild(tagElement);
    });
}

function updateTagsInput() {
    // Remove existing tag inputs
    const existingInputs = document.querySelectorAll('input[name="tags[]"]');
    existingInputs.forEach(input => input.remove());
    
    // Add current tags as hidden inputs
    const form = document.getElementById('photoUploadForm');
    tags.forEach(tag => {
        const hiddenInput = document.createElement('input');
        hiddenInput.type = 'hidden';
        hiddenInput.name = 'tags[]';
        hiddenInput.value = tag;
        form.appendChild(hiddenInput);
    });
}

// Auto-generate title based on category and project (optional helper)
function autoGenerateTitle() {
    const project = document.getElementById('project_id').selectedOptions[0]?.text;
    const category = document.getElementById('photo_category').selectedOptions[0]?.text;
    const date = document.getElementById('photo_date').value;
    
    if (project && category && date) {
        const titleInput = document.getElementById('title');
        if (!titleInput.value) {
            const formattedDate = new Date(date).toLocaleDateString();
            titleInput.value = `${category} - ${project} - ${formattedDate}`;
        }
    }
}

// Optional: Auto-generate title when category or project changes
document.getElementById('photo_category').addEventListener('change', autoGenerateTitle);
document.getElementById('project_id').addEventListener('change', autoGenerateTitle);
</script>
@endpush