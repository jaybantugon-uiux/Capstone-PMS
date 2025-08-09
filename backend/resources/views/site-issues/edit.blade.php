
@extends('app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h2">Edit Site Issue</h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('sc.site-issues.index') }}">Site Issues</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('sc.site-issues.show', $siteIssue) }}">{{ $siteIssue->issue_title }}</a></li>
                            <li class="breadcrumb-item active">Edit</li>
                        </ol>
                    </nav>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('sc.site-issues.show', $siteIssue) }}" class="btn btn-outline-secondary">
                        <i class="fas fa-eye me-1"></i> View Issue
                    </a>
                    <a href="{{ route('sc.site-issues.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Back to Issues
                    </a>
                </div>
            </div>

            <!-- Status Warning -->
            @if(in_array($siteIssue->status, ['resolved', 'closed']))
                <div class="alert alert-warning" role="alert">
                    <h5 class="alert-heading">
                        <i class="fas fa-exclamation-triangle me-2"></i>Issue Status Notice
                    </h5>
                    <p class="mb-0">This issue has been marked as {{ $siteIssue->formatted_status }} and cannot be edited.</p>
                </div>
            @endif

            <div class="row">
                <div class="col-lg-8">
                    <!-- Main Form -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-edit me-2"></i>Edit Issue Details
                            </h5>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('sc.site-issues.update', $siteIssue) }}" method="POST" enctype="multipart/form-data" id="editIssueForm">
                                @csrf
                                @method('PUT')

                                <!-- Issue Title -->
                                <div class="mb-3">
                                    <label for="issue_title" class="form-label">Issue Title <span class="text-danger">*</span></label>
                                    <input type="text" name="issue_title" id="issue_title" 
                                           class="form-control @error('issue_title') is-invalid @enderror" 
                                           value="{{ old('issue_title', $siteIssue->issue_title) }}" 
                                           placeholder="Brief description of the issue" required>
                                    @error('issue_title')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Type and Priority -->
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="issue_type" class="form-label">Issue Type <span class="text-danger">*</span></label>
                                        <select name="issue_type" id="issue_type" class="form-select @error('issue_type') is-invalid @enderror" required>
                                            <option value="">Select Type</option>
                                            <option value="safety" {{ old('issue_type', $siteIssue->issue_type) == 'safety' ? 'selected' : '' }}>
                                                ⚠️ Safety
                                            </option>
                                            <option value="equipment" {{ old('issue_type', $siteIssue->issue_type) == 'equipment' ? 'selected' : '' }}>
                                                🔧 Equipment
                                            </option>
                                            <option value="environmental" {{ old('issue_type', $siteIssue->issue_type) == 'environmental' ? 'selected' : '' }}>
                                                🌱 Environmental
                                            </option>
                                            <option value="personnel" {{ old('issue_type', $siteIssue->issue_type) == 'personnel' ? 'selected' : '' }}>
                                                👥 Personnel
                                            </option>
                                            <option value="quality" {{ old('issue_type', $siteIssue->issue_type) == 'quality' ? 'selected' : '' }}>
                                                ✅ Quality
                                            </option>
                                            <option value="timeline" {{ old('issue_type', $siteIssue->issue_type) == 'timeline' ? 'selected' : '' }}>
                                                ⏰ Timeline
                                            </option>
                                            <option value="other" {{ old('issue_type', $siteIssue->issue_type) == 'other' ? 'selected' : '' }}>
                                                ❓ Other
                                            </option>
                                        </select>
                                        @error('issue_type')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label for="priority" class="form-label">Priority <span class="text-danger">*</span></label>
                                        <select name="priority" id="priority" class="form-select @error('priority') is-invalid @enderror" required>
                                            <option value="">Select Priority</option>
                                            <option value="low" {{ old('priority', $siteIssue->priority) == 'low' ? 'selected' : '' }}>
                                                🟢 Low
                                            </option>
                                            <option value="medium" {{ old('priority', $siteIssue->priority) == 'medium' ? 'selected' : '' }}>
                                                🟡 Medium
                                            </option>
                                            <option value="high" {{ old('priority', $siteIssue->priority) == 'high' ? 'selected' : '' }}>
                                                🟠 High
                                            </option>
                                            <option value="critical" {{ old('priority', $siteIssue->priority) == 'critical' ? 'selected' : '' }}>
                                                🔴 Critical
                                            </option>
                                        </select>
                                        @error('priority')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <!-- Location -->
                                <div class="mb-3">
                                    <label for="location" class="form-label">Location</label>
                                    <input type="text" name="location" id="location" 
                                           class="form-control @error('location') is-invalid @enderror" 
                                           value="{{ old('location', $siteIssue->location) }}" 
                                           placeholder="Specific location where the issue occurred">
                                    @error('location')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Description -->
                                <div class="mb-3">
                                    <label for="description" class="form-label">Detailed Description <span class="text-danger">*</span></label>
                                    <textarea name="description" id="description" 
                                              class="form-control @error('description') is-invalid @enderror" 
                                              rows="4" required 
                                              placeholder="Provide a detailed description of the issue">{{ old('description', $siteIssue->description) }}</textarea>
                                    @error('description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Affected Areas -->
                                <div class="mb-3">
                                    <label for="affected_areas" class="form-label">Affected Areas</label>
                                    <textarea name="affected_areas" id="affected_areas" 
                                              class="form-control @error('affected_areas') is-invalid @enderror" 
                                              rows="2" 
                                              placeholder="List areas, systems, or processes affected by this issue">{{ old('affected_areas', $siteIssue->affected_areas) }}</textarea>
                                    @error('affected_areas')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Immediate Actions Taken -->
                                <div class="mb-3">
                                    <label for="immediate_actions_taken" class="form-label">Immediate Actions Taken</label>
                                    <textarea name="immediate_actions_taken" id="immediate_actions_taken" 
                                              class="form-control @error('immediate_actions_taken') is-invalid @enderror" 
                                              rows="3" 
                                              placeholder="Describe any immediate actions you have taken to address or contain the issue">{{ old('immediate_actions_taken', $siteIssue->immediate_actions_taken) }}</textarea>
                                    @error('immediate_actions_taken')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Suggested Solutions -->
                                <div class="mb-3">
                                    <label for="suggested_solutions" class="form-label">Suggested Solutions</label>
                                    <textarea name="suggested_solutions" id="suggested_solutions" 
                                              class="form-control @error('suggested_solutions') is-invalid @enderror" 
                                              rows="3" 
                                              placeholder="Provide any suggestions for resolving this issue">{{ old('suggested_solutions', $siteIssue->suggested_solutions) }}</textarea>
                                    @error('suggested_solutions')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Estimated Cost -->
                                <div class="mb-3">
                                    <label for="estimated_cost" class="form-label">Estimated Cost (₱)</label>
                                    <div class="input-group">
                                        <span class="input-group-text">₱</span>
                                        <input type="number" name="estimated_cost" id="estimated_cost" 
                                               class="form-control @error('estimated_cost') is-invalid @enderror" 
                                               value="{{ old('estimated_cost', $siteIssue->estimated_cost) }}" 
                                               step="0.01" min="0" 
                                               placeholder="0.00">
                                    </div>
                                    <small class="text-muted">Estimated cost to resolve this issue (if applicable)</small>
                                    @error('estimated_cost')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Existing Photos -->
                                @if($siteIssue->photos && count($siteIssue->photos) > 0)
                                <div class="mb-3">
                                    <label class="form-label">Current Photos</label>
                                    <div class="row">
                                        @foreach($siteIssue->photos as $index => $photo)
                                            <div class="col-md-3 mb-2">
                                                <div class="position-relative">
                                                    <img src="{{ Storage::url($photo) }}" 
                                                         class="img-thumbnail" 
                                                         style="height: 120px; width: 100%; object-fit: cover;">
                                                    <button type="button" 
                                                            class="btn btn-sm btn-danger position-absolute top-0 end-0" 
                                                            onclick="removeExistingPhoto({{ $index }})"
                                                            style="transform: translate(25%, -25%);">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                    <input type="hidden" name="existing_photos" id="existing_photos" value="{{ json_encode($siteIssue->photos) }}">
                                </div>
                                @endif

                                <!-- New Photos -->
                                <div class="mb-3">
                                    <label for="photos" class="form-label">Add New Photos</label>
                                    <input type="file" name="photos[]" id="photos" 
                                           class="form-control @error('photos.*') is-invalid @enderror" 
                                           multiple accept="image/*" capture="environment">
                                    <small class="text-muted">Upload additional photos. Max 5MB per image. Supported: JPG, PNG, GIF</small>
                                    @error('photos.*')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div id="photo-preview" class="mt-2"></div>
                                </div>

                                <!-- Submit Buttons -->
                                <div class="d-flex justify-content-between">
                                    <a href="{{ route('sc.site-issues.show', $siteIssue) }}" class="btn btn-secondary">
                                        <i class="fas fa-times me-1"></i> Cancel
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-1"></i> Update Issue
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="col-lg-4">
                    <!-- Issue Status -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="fas fa-info-circle me-1"></i> Current Status
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <strong>Status:</strong>
                                <span class="badge bg-{{ $siteIssue->status_badge_color }} ms-2">
                                    {{ $siteIssue->formatted_status }}
                                </span>
                            </div>
                            
                            <div class="mb-3">
                                <strong>Reported:</strong>
                                <br><small>{{ $siteIssue->formatted_reported_at }}</small>
                                <br><small class="text-muted">{{ $siteIssue->age }}</small>
                            </div>

                            @if($siteIssue->acknowledged_at)
                            <div class="mb-3">
                                <strong>Acknowledged:</strong>
                                <br><small class="text-success">{{ $siteIssue->formatted_acknowledged_at }}</small>
                            </div>
                            @endif

                            @if($siteIssue->assignedTo)
                            <div class="mb-3">
                                <strong>Assigned To:</strong>
                                <br><small>{{ $siteIssue->assignedTo->first_name }} {{ $siteIssue->assignedTo->last_name }}</small>
                            </div>
                            @endif

                            <div class="mb-0">
                                <strong>Project:</strong>
                                <br><a href="{{ route('projects.show', $siteIssue->project) }}" class="text-decoration-none">
                                    {{ $siteIssue->project->name }}
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Edit Guidelines -->
                    <div class="card mb-4">
                        <div class="card-header bg-warning text-dark">
                            <h6 class="mb-0">
                                <i class="fas fa-exclamation-triangle me-1"></i> Edit Guidelines
                            </h6>
                        </div>
                        <div class="card-body">
                            <ul class="list-unstyled mb-0">
                                <li class="mb-2">
                                    <i class="fas fa-info text-primary me-2"></i>
                                    <small>You can only edit issues that are not yet resolved</small>
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-info text-primary me-2"></i>
                                    <small>Changes may require admin re-review</small>
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-info text-primary me-2"></i>
                                    <small>Adding photos helps with resolution</small>
                                </li>
                                <li class="mb-0">
                                    <i class="fas fa-info text-primary me-2"></i>
                                    <small>Update actions taken as you implement them</small>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="fas fa-bolt me-1"></i> Quick Actions
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <a href="{{ route('sc.site-issues.show', $siteIssue) }}" class="btn btn-outline-primary">
                                    <i class="fas fa-eye me-1"></i> View Details
                                </a>
                                
                                <a href="{{ route('sc.site-issues.create', ['project_id' => $siteIssue->project_id]) }}" class="btn btn-outline-danger">
                                    <i class="fas fa-exclamation-triangle me-1"></i> Report New Issue
                                </a>

                                @if($siteIssue->task)
                                    <a href="{{ route('sc.task-reports.create', ['task_id' => $siteIssue->task_id]) }}" class="btn btn-outline-success">
                                        <i class="fas fa-file-alt me-1"></i> Create Task Report
                                    </a>
                                @endif

                                <a href="{{ route('sc.site-issues.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-list me-1"></i> All Issues
                                </a>
                            </div>
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
.form-label {
    font-weight: 600;
}
.text-danger {
    font-weight: bold;
}
.preview-item {
    position: relative;
    display: inline-block;
    margin: 5px;
}
.preview-item img {
    max-width: 100px;
    max-height: 100px;
    object-fit: cover;
    border-radius: 4px;
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Photo preview for new photos
    const photoInput = document.getElementById('photos');
    const photoPreview = document.getElementById('photo-preview');

    photoInput.addEventListener('change', function() {
        photoPreview.innerHTML = '';
        Array.from(this.files).forEach((file, index) => {
            if (file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const div = document.createElement('div');
                    div.className = 'preview-item';
                    div.innerHTML = `
                        <img src="${e.target.result}" alt="Preview" class="img-thumbnail">
                        <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0" 
                                onclick="removeNewPhoto(this, ${index})" style="transform: translate(25%, -25%);">
                            <i class="fas fa-times"></i>
                        </button>
                    `;
                    photoPreview.appendChild(div);
                };
                reader.readAsDataURL(file);
            }
        });
    });

    // Auto-resize textareas
    document.querySelectorAll('textarea').forEach(textarea => {
        textarea.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = this.scrollHeight + 'px';
        });
        
        // Initial resize
        textarea.style.height = 'auto';
        textarea.style.height = textarea.scrollHeight + 'px';
    });

    // Form validation
    const form = document.getElementById('editIssueForm');
    form.addEventListener('submit', function(e) {
        const submitBtn = this.querySelector('button[type="submit"]');
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Updating...';
    });
});

// Global functions
function removeExistingPhoto(index) {
    if (confirm('Are you sure you want to remove this photo?')) {
        const existingPhotosInput = document.getElementById('existing_photos');
        let photos = JSON.parse(existingPhotosInput.value);
        photos.splice(index, 1);
        existingPhotosInput.value = JSON.stringify(photos);
        
        // Hide the photo element
        event.target.closest('.col-md-3').style.display = 'none';
    }
}

function removeNewPhoto(button, index) {
    const input = document.getElementById('photos');
    const dt = new DataTransfer();
    
    Array.from(input.files).forEach((file, i) => {
        if (i !== index) {
            dt.items.add(file);
        }
    });
    
    input.files = dt.files;
    button.closest('.preview-item').remove();
}
</script>
@endpush