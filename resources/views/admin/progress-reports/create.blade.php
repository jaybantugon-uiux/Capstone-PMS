@extends('app')

@section('title', 'Create Progress Report')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Page Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0 text-gray-800">
                        <i class="fas fa-file-alt me-2"></i>Create Progress Report
                    </h1>
                    <p class="text-muted mb-0">Create and send a progress report to a client</p>
                </div>
                <div class="d-flex gap-2">
                    <span class="badge bg-{{ auth()->user()->role === 'admin' ? 'danger' : 'primary' }}">
                        {{ auth()->user()->role === 'admin' ? 'Administrator' : 'Project Manager' }}
                    </span>
                    <a href="{{ route('admin.progress-reports.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-1"></i>Back to Reports
                    </a>
                </div>
            </div>

            <!-- Main Content Card -->
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-plus-circle me-2"></i>New Progress Report
                        <small class="ms-2 opacity-75">
                            ({{ auth()->user()->role === 'admin' ? 'Admin' : 'PM' }} Access)
                        </small>
                    </h5>
                </div>
                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <h6><i class="fas fa-exclamation-triangle me-1"></i>Please correct the following errors:</h6>
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('admin.progress-reports.store') }}" method="POST" enctype="multipart/form-data" id="reportForm">
                        @csrf
                        
                        <div class="row">
                            <!-- Left Column -->
                            <div class="col-lg-8">
                                <!-- Report Title -->
                                <div class="mb-3">
                                    <label for="title" class="form-label fw-bold">
                                        <i class="fas fa-heading me-1"></i>Report Title <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" class="form-control @error('title') is-invalid @enderror" 
                                           id="title" name="title" value="{{ old('title') }}" 
                                           placeholder="Enter a descriptive title for the progress report" 
                                           maxlength="255" required>
                                    @error('title')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">
                                        <span id="titleCount">0</span>/255 characters
                                    </div>
                                </div>

                                <!-- Report Description -->
                                <div class="mb-3">
                                    <label for="description" class="form-label fw-bold">
                                        <i class="fas fa-align-left me-1"></i>Report Description <span class="text-danger">*</span>
                                    </label>
                                    <textarea class="form-control @error('description') is-invalid @enderror" 
                                              id="description" name="description" rows="8" 
                                              placeholder="Provide detailed information about the project progress, achievements, milestones, and any important updates..."
                                              maxlength="5000" required>{{ old('description') }}</textarea>
                                    @error('description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">
                                        <span id="descriptionCount">0</span>/5000 characters
                                    </div>
                                </div>

                                <!-- File Attachment -->
                                <div class="mb-3">
                                    <label for="attachment" class="form-label fw-bold">
                                        <i class="fas fa-paperclip me-1"></i>Attachment (Optional)
                                    </label>
                                    <input type="file" class="form-control @error('attachment') is-invalid @enderror" 
                                           id="attachment" name="attachment" 
                                           accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.gif">
                                    @error('attachment')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Supported formats: PDF, DOC, DOCX, JPG, JPEG, PNG, GIF (Max: 50MB)
                                    </div>
                                    
                                    <!-- File Preview -->
                                    <div id="filePreview" class="mt-2" style="display: none;">
                                        <div class="alert alert-info">
                                            <i class="fas fa-file me-2"></i>
                                            <span id="fileName"></span>
                                            <span class="badge bg-secondary ms-2" id="fileSize"></span>
                                            <button type="button" class="btn btn-sm btn-outline-danger ms-2" onclick="removeFile()">
                                                <i class="fas fa-times"></i> Remove
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Right Column -->
                            <div class="col-lg-4">
                                <!-- Client Selection -->
                                <div class="mb-3">
                                    <label for="client_id" class="form-label fw-bold">
                                        <i class="fas fa-user me-1"></i>Select Client <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select @error('client_id') is-invalid @enderror" 
                                            id="client_id" name="client_id" required>
                                        <option value="">Choose a client...</option>
                                        @foreach($clients as $client)
                                            <option value="{{ $client->id }}" {{ old('client_id') == $client->id ? 'selected' : '' }}>
                                                {{ $client->first_name }} {{ $client->last_name }} ({{ $client->email }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('client_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">
                                        The client will receive both email and in-app notifications
                                    </div>
                                </div>

                                <!-- Project Association -->
                                <div class="mb-3">
                                    <label for="project_id" class="form-label fw-bold">
                                        <i class="fas fa-project-diagram me-1"></i>Associated Project (Optional)
                                    </label>
                                    <select class="form-select @error('project_id') is-invalid @enderror" 
                                            id="project_id" name="project_id">
                                        <option value="">No specific project</option>
                                        @foreach($projects as $project)
                                            @if(auth()->user()->role === 'admin' || auth()->user()->canManageProject($project->id))
                                                <option value="{{ $project->id }}" {{ old('project_id') == $project->id ? 'selected' : '' }}>
                                                    {{ $project->name }}
                                                    @if(auth()->user()->role === 'pm')
                                                        <small class="text-muted">(You manage this project)</small>
                                                    @endif
                                                </option>
                                            @endif
                                        @endforeach
                                    </select>
                                    @error('project_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">
                                        @if(auth()->user()->role === 'pm')
                                            Only projects you manage are shown
                                        @else
                                            Link this report to a specific project if applicable
                                        @endif
                                    </div>
                                </div>

                                <!-- Report Summary Card -->
                                <div class="card bg-light">
                                    <div class="card-header">
                                        <h6 class="card-title mb-0">
                                            <i class="fas fa-info-circle me-1"></i>Report Summary
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="small">
                                            <div class="row mb-2">
                                                <div class="col-5 text-muted">Created by:</div>
                                                <div class="col-7">{{ auth()->user()->first_name }} {{ auth()->user()->last_name }}</div>
                                            </div>
                                            <div class="row mb-2">
                                                <div class="col-5 text-muted">Date:</div>
                                                <div class="col-7">{{ now()->format('M d, Y') }}</div>
                                            </div>
                                            <div class="row mb-2">
                                                <div class="col-5 text-muted">Status:</div>
                                                <div class="col-7">
                                                    <span class="badge bg-warning">Will be sent</span>
                                                </div>
                                            </div>
                                            <div class="row mb-2">
                                                <div class="col-5 text-muted">Recipient:</div>
                                                <div class="col-7" id="selectedClient">
                                                    <em class="text-muted">No client selected</em>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-5 text-muted">Project:</div>
                                                <div class="col-7" id="selectedProject">
                                                    <em class="text-muted">General report</em>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Action Buttons -->
                                <div class="d-grid gap-2 mt-4">
                                    <button type="submit" class="btn btn-primary btn-lg">
                                        <i class="fas fa-paper-plane me-2"></i>Create & Send Report
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary" onclick="saveDraft()">
                                        <i class="fas fa-save me-2"></i>Save as Draft
                                    </button>
                                    <a href="{{ route('admin.progress-reports.index') }}" class="btn btn-outline-danger">
                                        <i class="fas fa-times me-2"></i>Cancel
                                    </a>
                                    
                                    @if(auth()->user()->role === 'pm')
                                        <div class="alert alert-info mt-2">
                                            <i class="fas fa-info-circle me-1"></i>
                                            <small>As a Project Manager, you can create reports for projects you manage and send them to any registered client.</small>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .form-label.fw-bold {
        color: #495057;
    }
    
    .card-header.bg-primary {
        border-bottom: 1px solid rgba(0,0,0,.125);
    }
    
    #filePreview .alert {
        border-left: 4px solid #17a2b8;
    }
    
    .character-count {
        font-size: 0.875rem;
        color: #6c757d;
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Character counters
    const titleInput = document.getElementById('title');
    const descriptionTextarea = document.getElementById('description');
    const titleCount = document.getElementById('titleCount');
    const descriptionCount = document.getElementById('descriptionCount');
    
    function updateCharacterCount(input, counter) {
        counter.textContent = input.value.length;
        
        // Change color based on usage
        const usage = input.value.length / input.getAttribute('maxlength');
        if (usage > 0.9) {
            counter.className = 'text-danger';
        } else if (usage > 0.7) {
            counter.className = 'text-warning';
        } else {
            counter.className = 'text-muted';
        }
    }
    
    titleInput.addEventListener('input', () => updateCharacterCount(titleInput, titleCount));
    descriptionTextarea.addEventListener('input', () => updateCharacterCount(descriptionTextarea, descriptionCount));
    
    // Initial count
    updateCharacterCount(titleInput, titleCount);
    updateCharacterCount(descriptionTextarea, descriptionCount);
    
    // File upload preview
    const attachmentInput = document.getElementById('attachment');
    const filePreview = document.getElementById('filePreview');
    const fileName = document.getElementById('fileName');
    const fileSize = document.getElementById('fileSize');
    
    attachmentInput.addEventListener('change', function() {
        if (this.files && this.files[0]) {
            const file = this.files[0];
            fileName.textContent = file.name;
            fileSize.textContent = formatFileSize(file.size);
            filePreview.style.display = 'block';
        } else {
            filePreview.style.display = 'none';
        }
    });
    
    // Client and Project selection updates
    const clientSelect = document.getElementById('client_id');
    const projectSelect = document.getElementById('project_id');
    const selectedClient = document.getElementById('selectedClient');
    const selectedProject = document.getElementById('selectedProject');
    
    clientSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        if (this.value) {
            selectedClient.innerHTML = selectedOption.text.split(' (')[0];
        } else {
            selectedClient.innerHTML = '<em class="text-muted">No client selected</em>';
        }
    });
    
    projectSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        if (this.value) {
            selectedProject.textContent = selectedOption.text;
        } else {
            selectedProject.innerHTML = '<em class="text-muted">General report</em>';
        }
    });
    
    // Form validation
    const form = document.getElementById('reportForm');
    form.addEventListener('submit', function(e) {
        const submitBtn = form.querySelector('button[type="submit"]');
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Creating Report...';
    });
});

function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

function removeFile() {
    document.getElementById('attachment').value = '';
    document.getElementById('filePreview').style.display = 'none';
}

function saveDraft() {
    // Change form action to save as draft
    const form = document.getElementById('reportForm');
    const statusInput = document.createElement('input');
    statusInput.type = 'hidden';
    statusInput.name = 'save_as_draft';
    statusInput.value = '1';
    form.appendChild(statusInput);
    form.submit();
}
</script>
@endpush
@endsection