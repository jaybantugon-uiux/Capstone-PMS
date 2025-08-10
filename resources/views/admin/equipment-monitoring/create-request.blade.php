@extends('app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Request Equipment</h5>
                        <a href="{{ route('admin.equipment-monitoring.my-dashboard') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-1"></i>Back to Dashboard
                        </a>
                    </div>
                    <p class="text-muted mt-2 mb-0">Submit a request for personal or project-specific equipment</p>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.equipment-monitoring.store-request') }}" method="POST">
                        @csrf

                        <!-- Equipment Information -->
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <h6 class="text-primary">Equipment Information</h6>
                                <hr>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-8">
                                <label for="equipment_name" class="form-label">Equipment Name <span class="text-danger">*</span></label>
                                <input type="text" 
                                       class="form-control @error('equipment_name') is-invalid @enderror" 
                                       id="equipment_name" 
                                       name="equipment_name" 
                                       value="{{ old('equipment_name') }}" 
                                       placeholder="e.g., Laptop, Drill, Camera"
                                       required>
                                @error('equipment_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label for="quantity" class="form-label">Quantity <span class="text-danger">*</span></label>
                                <input type="number" 
                                       class="form-control @error('quantity') is-invalid @enderror" 
                                       id="quantity" 
                                       name="quantity" 
                                       value="{{ old('quantity', 1) }}" 
                                       min="1" 
                                       max="100"
                                       required>
                                @error('quantity')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="equipment_description" class="form-label">Equipment Description <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('equipment_description') is-invalid @enderror" 
                                      id="equipment_description" 
                                      name="equipment_description" 
                                      rows="3" 
                                      placeholder="Detailed description of the equipment needed, including specifications, model, or requirements"
                                      required>{{ old('equipment_description') }}</textarea>
                            @error('equipment_description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Usage Type and Project -->
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <h6 class="text-primary">Usage Information</h6>
                                <hr>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="usage_type" class="form-label">Usage Type <span class="text-danger">*</span></label>
                                <select class="form-control @error('usage_type') is-invalid @enderror" 
                                        id="usage_type" 
                                        name="usage_type" 
                                        required>
                                    <option value="">Select Usage Type</option>
                                    <option value="personal" {{ old('usage_type') == 'personal' ? 'selected' : '' }}>
                                        Personal Use
                                    </option>
                                    <option value="project_site" {{ old('usage_type') == 'project_site' ? 'selected' : '' }}>
                                        Project Site
                                    </option>
                                </select>
                                @error('usage_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">
                                    Personal equipment is auto-approved. Project equipment requires admin approval.
                                </small>
                            </div>
                            <div class="col-md-6">
                                <label for="project_id" class="form-label">Project <span class="text-danger project-required" style="display: none;">*</span></label>
                                <select class="form-control @error('project_id') is-invalid @enderror" 
                                        id="project_id" 
                                        name="project_id" 
                                        disabled>
                                    <option value="">Select Project</option>
                                    @foreach($projects as $project)
                                        <option value="{{ $project->id }}" {{ old('project_id') == $project->id ? 'selected' : '' }}>
                                            {{ $project->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('project_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">Required for project site equipment</small>
                            </div>
                        </div>

                        <!-- Request Details -->
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <h6 class="text-primary">Request Details</h6>
                                <hr>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="urgency_level" class="form-label">Urgency Level <span class="text-danger">*</span></label>
                                <select class="form-control @error('urgency_level') is-invalid @enderror" 
                                        id="urgency_level" 
                                        name="urgency_level" 
                                        required>
                                    <option value="">Select Urgency</option>
                                    <option value="low" {{ old('urgency_level') == 'low' ? 'selected' : '' }}>
                                        Low (10+ days)
                                    </option>
                                    <option value="medium" {{ old('urgency_level') == 'medium' ? 'selected' : '' }}>
                                        Medium (5-10 days)
                                    </option>
                                    <option value="high" {{ old('urgency_level') == 'high' ? 'selected' : '' }}>
                                        High (2-5 days)
                                    </option>
                                    <option value="critical" {{ old('urgency_level') == 'critical' ? 'selected' : '' }}>
                                        Critical (1-2 days)
                                    </option>
                                </select>
                                @error('urgency_level')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="estimated_cost" class="form-label">Estimated Cost (₱)</label>
                                <input type="number" 
                                       class="form-control @error('estimated_cost') is-invalid @enderror" 
                                       id="estimated_cost" 
                                       name="estimated_cost" 
                                       value="{{ old('estimated_cost') }}" 
                                       min="0" 
                                       step="0.01"
                                       placeholder="0.00">
                                @error('estimated_cost')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">Optional - approximate cost if known</small>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="justification" class="form-label">Justification <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('justification') is-invalid @enderror" 
                                      id="justification" 
                                      name="justification" 
                                      rows="3" 
                                      placeholder="Explain why this equipment is needed, how it will be used, and its importance for your work or project"
                                      required>{{ old('justification') }}</textarea>
                            @error('justification')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="additional_notes" class="form-label">Additional Notes</label>
                            <textarea class="form-control @error('additional_notes') is-invalid @enderror" 
                                      id="additional_notes" 
                                      name="additional_notes" 
                                      rows="2" 
                                      placeholder="Any additional information, special requirements, or instructions">{{ old('additional_notes') }}</textarea>
                            @error('additional_notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Information Box -->
                        <div class="alert alert-info">
                            <h6><i class="fas fa-info-circle me-2"></i>Request Processing Information</h6>
                            <ul class="mb-0">
                                <li><strong>Personal Equipment:</strong> Auto-approved immediately and ready for use</li>
                                <li><strong>Project Equipment:</strong> Requires admin approval before activation</li>
                                <li><strong>Critical/High Priority:</strong> Requests are prioritized for faster processing</li>
                                <li>You will receive email notifications about your request status</li>
                            </ul>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.equipment-monitoring.my-dashboard') }}" class="btn btn-secondary">
                                <i class="fas fa-times me-1"></i>Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-paper-plane me-1"></i>Submit Request
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const usageTypeSelect = document.getElementById('usage_type');
    const projectSelect = document.getElementById('project_id');
    const projectRequired = document.querySelector('.project-required');

    function toggleProjectField() {
        if (usageTypeSelect.value === 'project_site') {
            projectSelect.disabled = false;
            projectSelect.required = true;
            projectRequired.style.display = 'inline';
        } else {
            projectSelect.disabled = true;
            projectSelect.required = false;
            projectSelect.value = '';
            projectRequired.style.display = 'none';
        }
    }

    usageTypeSelect.addEventListener('change', toggleProjectField);
    
    // Initialize on page load
    toggleProjectField();
});
</script>
@endpush