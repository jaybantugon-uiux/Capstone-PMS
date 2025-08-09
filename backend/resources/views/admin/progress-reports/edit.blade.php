@extends('app')

@section('title', 'Edit Progress Report')

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
                                <a href="{{ route('admin.progress-reports.index') }}">Progress Reports</a>
                            </li>
                            <li class="breadcrumb-item">
                                <a href="{{ route('admin.progress-reports.show', $progressReport) }}">{{ Str::limit($progressReport->title, 20) }}</a>
                            </li>
                            <li class="breadcrumb-item active">Edit</li>
                        </ol>
                    </nav>
                    <h1 class="h3 mb-0 text-gray-800">
                        <i class="fas fa-edit me-2"></i>Edit Progress Report
                    </h1>
                    <p class="text-muted mb-0">Modify report details and settings</p>
                </div>
                <div class="d-flex gap-2">
                    <span class="badge bg-{{ auth()->user()->role === 'admin' ? 'danger' : 'primary' }}">
                        {{ auth()->user()->role === 'admin' ? 'Administrator' : 'Project Manager' }}
                    </span>
                    <a href="{{ route('admin.progress-reports.show', $progressReport) }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-1"></i>Back to Report
                    </a>
                </div>
            </div>

            <!-- Main Content Card -->
            <div class="card shadow">
                <div class="card-header bg-warning text-dark">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-edit me-2"></i>Edit Report: {{ Str::limit($progressReport->title, 40) }}
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

                    <!-- Report Status Alert -->
                    <div class="alert alert-{{ $progressReport->status_color }} mb-4">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-info-circle fa-2x me-3"></i>
                            <div>
                                <strong>Current Status: {{ $progressReport->formatted_status }}</strong>
                                @if($progressReport->sent_at)
                                    <br><small>Sent to {{ $progressReport->client->first_name }} {{ $progressReport->client->last_name }} on {{ $progressReport->sent_at->format('M d, Y g:i A') }}</small>
                                @endif
                                @if($progressReport->view_count > 0)
                                    <br><small>Viewed {{ $progressReport->view_count }} time(s)</small>
                                @endif
                            </div>
                        </div>
                    </div>

                    <form action="{{ route('admin.progress-reports.update', $progressReport) }}" method="POST" enctype="multipart/form-data" id="editReportForm">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <!-- Left Column -->
                            <div class="col-lg-8">
                                <!-- Report Title -->
                                <div class="mb-3">
                                    <label for="title" class="form-label fw-bold">
                                        <i class="fas fa-heading me-1"></i>Report Title <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" class="form-control @error('title') is-invalid @enderror" 
                                           id="title" name="title" value="{{ old('title', $progressReport->title) }}" 
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
                                              placeholder="Provide detailed information about the project progress..."
                                              maxlength="5000" required>{{ old('description', $progressReport->description) }}</textarea>
                                    @error('description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">
                                        <span id="descriptionCount">0</span>/5000 characters
                                    </div>
                                </div>

                                <!-- Current Attachment Display -->
                                @if($progressReport->hasAttachment())
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">
                                            <i class="fas fa-paperclip me-1"></i>Current Attachment
                                        </label>
                                        <div class="alert alert-info">
                                            <div class="d-flex align-items-center">
                                                <i class="{{ $progressReport->attachment_icon }} me-2"></i>
                                                <div class="flex-grow-1">
                                                    <strong>{{ $progressReport->original_filename }}</strong>
                                                    <br><small class="text-muted">{{ $progressReport->formatted_file_size }}</small>
                                                </div>
                                                <div>
                                                    <a href="{{ route('admin.progress-reports.download-attachment', $progressReport) }}" 
                                                       class="btn btn-sm btn-outline-primary me-2">
                                                        <i class="fas fa-download"></i> Download
                                                    </a>
                                                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeCurrentAttachment()">
                                                        <i class="fas fa-times"></i> Remove
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                <!-- New File Attachment -->
                                <div class="mb-3">
                                    <label for="attachment" class="form-label fw-bold">
                                        <i class="fas fa-paperclip me-1"></i>
                                        @if($progressReport->hasAttachment())
                                            Replace Attachment (Optional)
                                        @else
                                            Add Attachment (Optional)
                                        @endif
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
                                        @if($progressReport->hasAttachment())
                                            <br><small class="text-warning">Uploading a new file will replace the current attachment.</small>
                                        @endif
                                    </div>
                                    
                                    <!-- New File Preview -->
                                    <div id="newFilePreview" class="mt-2" style="display: none;">
                                        <div class="alert alert-success">
                                            <i class="fas fa-file me-2"></i>
                                            <span id="newFileName"></span>
                                            <span class="badge bg-secondary ms-2" id="newFileSize"></span>
                                            <button type="button" class="btn btn-sm btn-outline-danger ms-2" onclick="removeNewFile()">
                                                <i class="fas fa-times"></i> Remove
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Right Column -->
                            <div class="col-lg-4">
                                <!-- Report Status -->
                                <div class="mb-3">
                                    <label for="status" class="form-label fw-bold">
                                        <i class="fas fa-flag me-1"></i>Report Status <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select @error('status') is-invalid @enderror" 
                                            id="status" name="status" required>
                                        <option value="draft" {{ old('status', $progressReport->status) === 'draft' ? 'selected' : '' }}>
                                            Draft
                                        </option>
                                        <option value="sent" {{ old('status', $progressReport->status) === 'sent' ? 'selected' : '' }}>
                                            Sent
                                        </option>
                                        <option value="viewed" {{ old('status', $progressReport->status) === 'viewed' ? 'selected' : '' }}>
                                            Viewed
                                        </option>
                                        <option value="archived" {{ old('status', $progressReport->status) === 'archived' ? 'selected' : '' }}>
                                            Archived
                                        </option>
                                    </select>
                                    @error('status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">
                                        Changing to "Sent" will notify the client if not already sent
                                    </div>
                                </div>

                                <!-- Client Information (Read-only) -->
                                <div class="mb-3">
                                    <label class="form-label fw-bold">
                                        <i class="fas fa-user me-1"></i>Client
                                    </label>
                                    <div class="form-control-plaintext border rounded p-2 bg-light">
                                        <strong>{{ $progressReport->client->first_name }} {{ $progressReport->client->last_name }}</strong>
                                        <br><small class="text-muted">{{ $progressReport->client->email }}</small>
                                    </div>
                                    <div class="form-text">
                                        Client cannot be changed after report creation
                                    </div>
                                </div>

                                <!-- Project Information (Read-only) -->
                                <div class="mb-3">
                                    <label class="form-label fw-bold">
                                        <i class="fas fa-project-diagram me-1"></i>Associated Project
                                    </label>
                                    <div class="form-control-plaintext border rounded p-2 bg-light">
                                        @if($progressReport->project)
                                            <strong>{{ $progressReport->project->name }}</strong>
                                            <br><small class="text-muted">{{ Str::limit($progressReport->project->description, 60) }}</small>
                                        @else
                                            <em class="text-muted">General Report (No specific project)</em>
                                        @endif
                                    </div>
                                    <div class="form-text">
                                        Project association cannot be changed after creation
                                    </div>
                                </div>

                                <!-- Edit Summary Card -->
                                <div class="card bg-light">
                                    <div class="card-header">
                                        <h6 class="card-title mb-0">
                                            <i class="fas fa-info-circle me-1"></i>Edit Summary
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="small">
                                            <div class="row mb-2">
                                                <div class="col-5 text-muted">Original Creator:</div>
                                                <div class="col-7">
                                                    {{ $progressReport->creator->first_name }} {{ $progressReport->creator->last_name }}
                                                    <br><span class="badge bg-{{ $progressReport->creator_role_badge_color }}">
                                                        {{ $progressReport->formatted_creator_role }}
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="row mb-2">
                                                <div class="col-5 text-muted">Created:</div>
                                                <div class="col-7">{{ $progressReport->created_at->format('M d, Y') }}</div>
                                            </div>
                                            <div class="row mb-2">
                                                <div class="col-5 text-muted">Last Modified:</div>
                                                <div class="col-7">{{ $progressReport->updated_at->format('M d, Y') }}</div>
                                            </div>
                                            <div class="row mb-2">
                                                <div class="col-5 text-muted">View Count:</div>
                                                <div class="col-7">
                                                    <span class="badge bg-info">{{ $progressReport->view_count }}</span>
                                                </div>
                                            </div>
                                            @if($progressReport->sent_at)
                                                <div class="row">
                                                    <div class="col-5 text-muted">Sent:</div>
                                                    <div class="col-7">{{ $progressReport->sent_at->diffForHumans() }}</div>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <!-- Action Buttons -->
                                <div class="d-grid gap-2 mt-4">
                                    <button type="submit" class="btn btn-warning btn-lg">
                                        <i class="fas fa-save me-2"></i>Update Report
                                    </button>
                                    <a href="{{ route('admin.progress-reports.show', $progressReport) }}" class="btn btn-outline-secondary">
                                        <i class="fas fa-times me-2"></i>Cancel Changes
                                    </a>
                                    <a href="{{ route('admin.progress-reports.index') }}" class="btn btn-outline-primary">
                                        <i class="fas fa-list me-2"></i>Back to All Reports
                                    </a>
                                    
                                    @if(auth()->user()->role === 'pm')
                                        <div class="alert alert-info mt-2">
                                            <i class="fas fa-info-circle me-1"></i>
                                            <small>As a PM, you can only edit reports you created. Status changes will affect client notifications.</small>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Hidden field to track attachment removal -->
                        <input type="hidden" name="remove_attachment" id="removeAttachment" value="0">
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
    
    .card-header.bg-warning {
        border-bottom: 1px solid rgba(0,0,0,.125);
    }
    
    #newFilePreview .alert {
        border-left: 4px solid #28a745;
    }
    
    .form-control-plaintext {
        background-color: #f8f9fa !important;
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
    
    // New file upload preview
    const attachmentInput = document.getElementById('attachment');
    const newFilePreview = document.getElementById('newFilePreview');
    const newFileName = document.getElementById('newFileName');
    const newFileSize = document.getElementById('newFileSize');
    
    attachmentInput.addEventListener('change', function() {
        if (this.files && this.files[0]) {
            const file = this.files[0];
            newFileName.textContent = file.name;
            newFileSize.textContent = formatFileSize(file.size);
            newFilePreview.style.display = 'block';
        } else {
            newFilePreview.style.display = 'none';
        }
    });
    
    // Form validation and submission
    const form = document.getElementById('editReportForm');
    form.addEventListener('submit', function(e) {
        const submitBtn = form.querySelector('button[type="submit"]');
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Updating Report...';
    });

    // Status change warning
    const statusSelect = document.getElementById('status');
    const originalStatus = '{{ $progressReport->status }}';
    
    statusSelect.addEventListener('change', function() {
        if (originalStatus !== 'sent' && this.value === 'sent') {
            if (!confirm('Changing status to "Sent" will send a notification to the client. Are you sure?')) {
                this.value = originalStatus;
            }
        }
    });
});

function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

function removeNewFile() {
    document.getElementById('attachment').value = '';
    document.getElementById('newFilePreview').style.display = 'none';
}

function removeCurrentAttachment() {
    if (confirm('Are you sure you want to remove the current attachment? This action cannot be undone.')) {
        document.getElementById('removeAttachment').value = '1';
        // Hide the current attachment display
        const currentAttachmentDiv = document.querySelector('.alert.alert-info');
        if (currentAttachmentDiv) {
            currentAttachmentDiv.style.display = 'none';
        }
        // Show a confirmation message
        const confirmDiv = document.createElement('div');
        confirmDiv.className = 'alert alert-warning';
        confirmDiv.innerHTML = '<i class="fas fa-exclamation-triangle me-2"></i>Current attachment will be removed when you save the report.';
        currentAttachmentDiv.parentNode.insertBefore(confirmDiv, currentAttachmentDiv.nextSibling);
    }
}
</script>
@endpush
@endsection