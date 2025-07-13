
@extends('app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h2">Report Site Issue</h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('sc.site-issues.index') }}">Site Issues</a></li>
                            <li class="breadcrumb-item active">Report New Issue</li>
                        </ol>
                    </nav>
                </div>
                <a href="{{ route('sc.site-issues.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Back to Issues
                </a>
            </div>

            <div class="row">
                <div class="col-lg-8">
                    <!-- Main Form -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-exclamation-triangle me-2"></i>Issue Details
                            </h5>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('sc.site-issues.store') }}" method="POST" enctype="multipart/form-data" id="issueForm">
                                @csrf

                                <!-- Project and Task Selection -->
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="project_id" class="form-label">Project <span class="text-danger">*</span></label>
                                        <select name="project_id" id="project_id" class="form-select @error('project_id') is-invalid @enderror" required>
                                            <option value="">Select Project</option>
                                            @foreach($projects as $project)
                                                <option value="{{ $project->id }}" 
                                                    {{ (old('project_id') == $project->id || request('project_id') == $project->id) ? 'selected' : '' }}>
                                                    {{ $project->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('project_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label for="task_id" class="form-label">Related Task (Optional)</label>
                                        <select name="task_id" id="task_id" class="form-select @error('task_id') is-invalid @enderror">
                                            <option value="">Select Task (Optional)</option>
                                            @if($selectedProject && $availableTasks->count() > 0)
                                                @foreach($availableTasks as $task)
                                                    <option value="{{ $task->id }}" 
                                                        {{ (old('task_id') == $task->id || request('task_id') == $task->id) ? 'selected' : '' }}>
                                                        {{ $task->task_name }}
                                                    </option>
                                                @endforeach
                                            @endif
                                        </select>
                                        @error('task_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <!-- Issue Title -->
                                <div class="mb-3">
                                    <label for="issue_title" class="form-label">Issue Title <span class="text-danger">*</span></label>
                                    <input type="text" name="issue_title" id="issue_title" 
                                           class="form-control @error('issue_title') is-invalid @enderror" 
                                           value="{{ old('issue_title') }}" 
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
                                            <option value="safety" {{ old('issue_type') == 'safety' ? 'selected' : '' }}>
                                                ⚠️ Safety
                                            </option>
                                            <option value="equipment" {{ old('issue_type') == 'equipment' ? 'selected' : '' }}>
                                                🔧 Equipment
                                            </option>
                                            <option value="environmental" {{ old('issue_type') == 'environmental' ? 'selected' : '' }}>
                                                🌱 Environmental
                                            </option>
                                            <option value="personnel" {{ old('issue_type') == 'personnel' ? 'selected' : '' }}>
                                                👥 Personnel
                                            </option>
                                            <option value="quality" {{ old('issue_type') == 'quality' ? 'selected' : '' }}>
                                                ✅ Quality
                                            </option>
                                            <option value="timeline" {{ old('issue_type') == 'timeline' ? 'selected' : '' }}>
                                                ⏰ Timeline
                                            </option>
                                            <option value="other" {{ old('issue_type') == 'other' ? 'selected' : '' }}>
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
                                            <option value="low" {{ old('priority') == 'low' ? 'selected' : '' }}>
                                                🟢 Low
                                            </option>
                                            <option value="medium" {{ old('priority') == 'medium' ? 'selected' : '' }}>
                                                🟡 Medium
                                            </option>
                                            <option value="high" {{ old('priority') == 'high' ? 'selected' : '' }}>
                                                🟠 High
                                            </option>
                                            <option value="critical" {{ old('priority') == 'critical' ? 'selected' : '' }}>
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
                                           value="{{ old('location') }}" 
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
                                              placeholder="Provide a detailed description of the issue, including what happened, when it happened, and any relevant circumstances">{{ old('description') }}</textarea>
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
                                              placeholder="List areas, systems, or processes affected by this issue">{{ old('affected_areas') }}</textarea>
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
                                              placeholder="Describe any immediate actions you have already taken to address or contain the issue">{{ old('immediate_actions_taken') }}</textarea>
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
                                              placeholder="Provide any suggestions for resolving this issue">{{ old('suggested_solutions') }}</textarea>
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
                                               value="{{ old('estimated_cost') }}" 
                                               step="0.01" min="0" 
                                               placeholder="0.00">
                                    </div>
                                    <small class="text-muted">Estimated cost to resolve this issue (if applicable)</small>
                                    @error('estimated_cost')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Photos -->
                                <div class="mb-3">
                                    <label for="photos" class="form-label">Photos</label>
                                    <input type="file" name="photos[]" id="photos" 
                                           class="form-control @error('photos.*') is-invalid @enderror" 
                                           multiple accept="image/*" capture="environment">
                                    <small class="text-muted">Upload photos of the issue. Max 5MB per image. Supported: JPG, PNG, GIF</small>
                                    @error('photos.*')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div id="photo-preview" class="mt-2"></div>
                                </div>

                                <!-- Attachments -->
                                <div class="mb-3">
                                    <label for="attachments" class="form-label">Additional Files</label>
                                    <input type="file" name="attachments[]" id="attachments" 
                                           class="form-control @error('attachments.*') is-invalid @enderror" 
                                           multiple accept=".pdf,.doc,.docx,.xls,.xlsx,.txt">
                                    <small class="text-muted">Upload additional documents. Max 10MB per file. Supported: PDF, DOC, DOCX, XLS, XLSX, TXT</small>
                                    @error('attachments.*')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div id="attachment-preview" class="mt-2"></div>
                                </div>

                                <!-- Submit Buttons -->
                                <div class="d-flex justify-content-between">
                                    <a href="{{ route('sc.site-issues.index') }}" class="btn btn-secondary">
                                        <i class="fas fa-times me-1"></i> Cancel
                                    </a>
                                    <button type="submit" class="btn btn-danger">
                                        <i class="fas fa-exclamation-triangle me-1"></i> Report Issue
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="col-lg-4">
                    <!-- Guidelines -->
                    <div class="card mb-4">
                        <div class="card-header bg-info text-white">
                            <h6 class="mb-0">
                                <i class="fas fa-info-circle me-1"></i> Reporting Guidelines
                            </h6>
                        </div>
                        <div class="card-body">
                            <ul class="list-unstyled mb-0">
                                <li class="mb-2">
                                    <i class="fas fa-check text-success me-2"></i>
                                    <small>Be specific and detailed in your description</small>
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-check text-success me-2"></i>
                                    <small>Include photos when possible</small>
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-check text-success me-2"></i>
                                    <small>Set appropriate priority level</small>
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-check text-success me-2"></i>
                                    <small>Document immediate actions taken</small>
                                </li>
                                <li class="mb-0">
                                    <i class="fas fa-check text-success me-2"></i>
                                    <small>Report critical issues immediately</small>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <!-- Priority Levels -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="fas fa-exclamation me-1"></i> Priority Levels
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="mb-2">
                                <span class="badge bg-danger">Critical</span>
                                <small class="text-muted d-block">Immediate safety risk or work stoppage</small>
                            </div>
                            <div class="mb-2">
                                <span class="badge bg-warning">High</span>
                                <small class="text-muted d-block">Significant impact on project timeline</small>
                            </div>
                            <div class="mb-2">
                                <span class="badge bg-info">Medium</span>
                                <small class="text-muted d-block">Moderate impact, needs attention</small>
                            </div>
                            <div>
                                <span class="badge bg-success">Low</span>
                                <small class="text-muted d-block">Minor issue, can wait for resolution</small>
                            </div>
                        </div>
                    </div>

                    <!-- Contact Info -->
                    <div class="card">
                        <div class="card-header bg-warning text-dark">
                            <h6 class="mb-0">
                                <i class="fas fa-phone me-1"></i> Emergency Contact
                            </h6>
                        </div>
                        <div class="card-body">
                            <p class="mb-2"><strong>For Critical Issues:</strong></p>
                            <p class="mb-1">📞 Emergency Hotline: <strong>911</strong></p>
                            <p class="mb-1">📱 Site Manager: <strong>(02) 8123-4567</strong></p>
                            <p class="mb-0">📧 Emergency Email: <strong>emergency@company.com</strong></p>
                            <hr>
                            <small class="text-muted">
                                For critical safety issues, contact emergency services first, then report through this system.
                            </small>
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
.preview-item .remove-btn {
    position: absolute;
    top: -5px;
    right: -5px;
    background: #dc3545;
    color: white;
    border: none;
    border-radius: 50%;
    width: 20px;
    height: 20px;
    font-size: 12px;
    cursor: pointer;
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Project and Task dependency
    const projectSelect = document.getElementById('project_id');
    const taskSelect = document.getElementById('task_id');

    projectSelect.addEventListener('change', function() {
        const projectId = this.value;
        taskSelect.innerHTML = '<option value="">Loading...</option>';

        if (projectId) {
            fetch(`{{ route('sc.site-issues.get-project-tasks') }}?project_id=${projectId}`)
                .then(response => response.json())
                .then(tasks => {
                    taskSelect.innerHTML = '<option value="">Select Task (Optional)</option>';
                    tasks.forEach(task => {
                        const option = document.createElement('option');
                        option.value = task.id;
                        option.textContent = task.task_name;
                        taskSelect.appendChild(option);
                    });
                })
                .catch(error => {
                    console.error('Error fetching tasks:', error);
                    taskSelect.innerHTML = '<option value="">Error loading tasks</option>';
                });
        } else {
            taskSelect.innerHTML = '<option value="">Select Task (Optional)</option>';
        }
    });

    // Photo preview
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
                        <img src="${e.target.result}" alt="Preview">
                        <button type="button" class="remove-btn" onclick="removePreview(this, ${index}, 'photos')">×</button>
                    `;
                    photoPreview.appendChild(div);
                };
                reader.readAsDataURL(file);
            }
        });
    });

    // Attachment preview
    const attachmentInput = document.getElementById('attachments');
    const attachmentPreview = document.getElementById('attachment-preview');

    attachmentInput.addEventListener('change', function() {
        attachmentPreview.innerHTML = '';
        Array.from(this.files).forEach((file, index) => {
            const div = document.createElement('div');
            div.className = 'd-flex align-items-center mb-1';
            div.innerHTML = `
                <i class="fas fa-file me-2"></i>
                <span class="text-muted">${file.name}</span>
                <button type="button" class="btn btn-sm btn-outline-danger ms-2" onclick="removeAttachment(this, ${index})">
                    <i class="fas fa-times"></i>
                </button>
            `;
            attachmentPreview.appendChild(div);
        });
    });

    // Auto-resize textareas
    document.querySelectorAll('textarea').forEach(textarea => {
        textarea.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = this.scrollHeight + 'px';
        });
    });

    // Form validation
    const form = document.getElementById('issueForm');
    form.addEventListener('submit', function(e) {
        const priority = document.getElementById('priority').value;
        const description = document.getElementById('description').value;

        if (priority === 'critical' && description.length < 50) {
            e.preventDefault();
            alert('Critical issues require a detailed description (at least 50 characters).');
            document.getElementById('description').focus();
            return false;
        }

        // Show loading state
        const submitBtn = this.querySelector('button[type="submit"]');
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Reporting...';
    });
});

// Global functions for file removal
function removePreview(button, index, type) {
    const input = document.getElementById(type);
    const dt = new DataTransfer();
    
    Array.from(input.files).forEach((file, i) => {
        if (i !== index) {
            dt.items.add(file);
        }
    });
    
    input.files = dt.files;
    button.parentElement.remove();
}

function removeAttachment(button, index) {
    const input = document.getElementById('attachments');
    const dt = new DataTransfer();
    
    Array.from(input.files).forEach((file, i) => {
        if (i !== index) {
            dt.items.add(file);
        }
    });
    
    input.files = dt.files;
    button.parentElement.remove();
}
</script>
@endpush