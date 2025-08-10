@extends('app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="card-title mb-0">
                            <i class="fas fa-truck-loading me-2"></i>Request Equipment
                        </h4>
                        <a href="{{ route('sc.equipment-monitoring.requests') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-arrow-left me-1"></i> Back to Requests
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('sc.equipment-monitoring.store-request') }}">
                        @csrf
                        
                        <!-- Equipment Information -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="equipment_name" class="form-label">Equipment Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('equipment_name') is-invalid @enderror" 
                                       id="equipment_name" name="equipment_name" 
                                       value="{{ old('equipment_name') }}" required>
                                @error('equipment_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="quantity" class="form-label">Quantity <span class="text-danger">*</span></label>
                                <input type="number" class="form-control @error('quantity') is-invalid @enderror" 
                                       id="quantity" name="quantity" 
                                       value="{{ old('quantity', 1) }}" min="1" max="100" required>
                                @error('quantity')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="equipment_description" class="form-label">Equipment Description <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('equipment_description') is-invalid @enderror" 
                                      id="equipment_description" name="equipment_description" 
                                      rows="3" required>{{ old('equipment_description') }}</textarea>
                            <div class="form-text">Provide a detailed description of the equipment you need.</div>
                            @error('equipment_description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Usage Type and Project -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="usage_type" class="form-label">Usage Type <span class="text-danger">*</span></label>
                                <select class="form-select @error('usage_type') is-invalid @enderror" 
                                        id="usage_type" name="usage_type" required>
                                    <option value="">Select Usage Type</option>
                                    <option value="personal" {{ old('usage_type') === 'personal' ? 'selected' : '' }}>Personal Use</option>
                                    <option value="project_site" {{ old('usage_type') === 'project_site' ? 'selected' : '' }}>Project Site</option>
                                </select>
                                @error('usage_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6" id="project_section" style="display: none;">
                                <label for="project_id" class="form-label">Project <span class="text-danger">*</span></label>
                                <select class="form-select @error('project_id') is-invalid @enderror" 
                                        id="project_id" name="project_id">
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
                            </div>
                        </div>

                        <!-- Cost and Urgency -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="estimated_cost" class="form-label">Estimated Cost (Optional)</label>
                                <div class="input-group">
                                    <span class="input-group-text">₱</span>
                                    <input type="number" class="form-control @error('estimated_cost') is-invalid @enderror" 
                                           id="estimated_cost" name="estimated_cost" 
                                           value="{{ old('estimated_cost') }}" 
                                           step="0.01" min="0" max="999999.99">
                                </div>
                                @error('estimated_cost')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="urgency_level" class="form-label">Urgency Level <span class="text-danger">*</span></label>
                                <select class="form-select @error('urgency_level') is-invalid @enderror" 
                                        id="urgency_level" name="urgency_level" required>
                                    <option value="">Select Urgency</option>
                                    <option value="low" {{ old('urgency_level') === 'low' ? 'selected' : '' }}>Low</option>
                                    <option value="medium" {{ old('urgency_level') === 'medium' ? 'selected' : '' }}>Medium</option>
                                    <option value="high" {{ old('urgency_level') === 'high' ? 'selected' : '' }}>High</option>
                                    <option value="critical" {{ old('urgency_level') === 'critical' ? 'selected' : '' }}>Critical</option>
                                </select>
                                @error('urgency_level')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Justification -->
                        <div class="mb-3">
                            <label for="justification" class="form-label">Justification <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('justification') is-invalid @enderror" 
                                      id="justification" name="justification" 
                                      rows="4" required>{{ old('justification') }}</textarea>
                            <div class="form-text">Explain why you need this equipment and how it will be used.</div>
                            @error('justification')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Additional Notes -->
                        <div class="mb-3">
                            <label for="additional_notes" class="form-label">Additional Notes (Optional)</label>
                            <textarea class="form-control @error('additional_notes') is-invalid @enderror" 
                                      id="additional_notes" name="additional_notes" 
                                      rows="3">{{ old('additional_notes') }}</textarea>
                            <div class="form-text">Any additional information that might be helpful for the approval process.</div>
                            @error('additional_notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Form Actions -->
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('sc.equipment-monitoring.requests') }}" class="btn btn-secondary">
                                <i class="fas fa-times me-1"></i> Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-paper-plane me-1"></i> Submit Request
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
    const projectSection = document.getElementById('project_section');
    const projectSelect = document.getElementById('project_id');

    function toggleProjectSection() {
        if (usageTypeSelect.value === 'project_site') {
            projectSection.style.display = 'block';
            projectSelect.required = true;
        } else {
            projectSection.style.display = 'none';
            projectSelect.required = false;
            projectSelect.value = '';
        }
    }

    // Initial state - check if project_site was previously selected
    toggleProjectSection();

    // Listen for changes
    usageTypeSelect.addEventListener('change', toggleProjectSection);

    // Form validation before submit
    const form = document.querySelector('form');
    form.addEventListener('submit', function(e) {
        const usageType = usageTypeSelect.value;
        const projectId = projectSelect.value;
        
        if (usageType === 'project_site' && !projectId) {
            e.preventDefault();
            alert('Please select a project for project site equipment requests.');
            projectSelect.focus();
            return false;
        }
        
        // Show loading state
        const submitBtn = form.querySelector('button[type="submit"]');
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Submitting...';
        }
    });
});
</script>
@endpush

@push('styles')
<style>
.card {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    border: 1px solid rgba(0, 0, 0, 0.125);
}
.form-control:focus, .form-select:focus {
    border-color: #86b7fe;
    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
}
.invalid-feedback {
    display: block;
}
</style>
@endpush