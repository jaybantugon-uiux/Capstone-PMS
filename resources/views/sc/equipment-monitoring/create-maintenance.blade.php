@extends('app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="card-title mb-0">
                            <i class="fas fa-tools me-2"></i>Schedule Maintenance
                        </h4>
                        <a href="{{ route('sc.equipment-monitoring.maintenance') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-arrow-left me-1"></i> Back to Maintenance
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

                    <form method="POST" action="{{ route('sc.equipment-monitoring.store-maintenance') }}">
                        @csrf
                        
                        <!-- Equipment Selection -->
                        <div class="mb-4">
                            <h5 class="text-primary mb-3">
                                <i class="fas fa-cogs me-2"></i>Equipment Selection
                            </h5>
                            
                            <!-- Personal Equipment -->
                            @if($personalEquipment->count() > 0)
                            <div class="mb-3">
                                <label class="form-label fw-bold">Personal Equipment</label>
                                <div class="row">
                                    @foreach($personalEquipment as $equipment)
                                        <div class="col-md-6 mb-2">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="monitored_equipment_id" 
                                                       id="equipment_{{ $equipment->id }}" value="{{ $equipment->id }}"
                                                       {{ old('monitored_equipment_id') == $equipment->id ? 'checked' : '' }}>
                                                <label class="form-check-label" for="equipment_{{ $equipment->id }}">
                                                    <strong>{{ $equipment->equipment_name }}</strong>
                                                    <br><small class="text-muted">{{ $equipment->equipment_description }}</small>
                                                    <br><small class="text-muted">Quantity: {{ $equipment->quantity }}</small>
                                                </label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                            @endif

                            <!-- Project Equipment -->
                            @if($projectEquipment->count() > 0)
                            <div class="mb-3">
                                <label class="form-label fw-bold">Project Equipment</label>
                                <div class="row">
                                    @foreach($projectEquipment as $equipment)
                                        <div class="col-md-6 mb-2">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="monitored_equipment_id" 
                                                       id="equipment_{{ $equipment->id }}" value="{{ $equipment->id }}"
                                                       {{ old('monitored_equipment_id') == $equipment->id ? 'checked' : '' }}>
                                                <label class="form-check-label" for="equipment_{{ $equipment->id }}">
                                                    <strong>{{ $equipment->equipment_name }}</strong>
                                                    <br><small class="text-muted">{{ $equipment->project->name ?? 'N/A' }}</small>
                                                    <br><small class="text-muted">Quantity: {{ $equipment->quantity }}</small>
                                                </label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                            @endif

                            @if($personalEquipment->count() === 0 && $projectEquipment->count() === 0)
                                <div class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    You don't have any equipment assigned yet. Please request equipment first.
                                </div>
                            @endif
                        </div>

                        <!-- Maintenance Details -->
                        <div class="mb-4">
                            <h5 class="text-primary mb-3">
                                <i class="fas fa-tools me-2"></i>Maintenance Details
                            </h5>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="maintenance_type" class="form-label">Maintenance Type <span class="text-danger">*</span></label>
                                    <select class="form-select @error('maintenance_type') is-invalid @enderror" 
                                            id="maintenance_type" name="maintenance_type" required>
                                        <option value="">Select Maintenance Type</option>
                                        <option value="routine" {{ old('maintenance_type') === 'routine' ? 'selected' : '' }}>Routine Maintenance</option>
                                        <option value="repair" {{ old('maintenance_type') === 'repair' ? 'selected' : '' }}>Repair</option>
                                        <option value="inspection" {{ old('maintenance_type') === 'inspection' ? 'selected' : '' }}>Inspection</option>
                                        <option value="calibration" {{ old('maintenance_type') === 'calibration' ? 'selected' : '' }}>Calibration</option>
                                        <option value="replacement" {{ old('maintenance_type') === 'replacement' ? 'selected' : '' }}>Replacement</option>
                                    </select>
                                    @error('maintenance_type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label for="priority" class="form-label">Priority <span class="text-danger">*</span></label>
                                    <select class="form-select @error('priority') is-invalid @enderror" 
                                            id="priority" name="priority" required>
                                        <option value="">Select Priority</option>
                                        <option value="low" {{ old('priority') === 'low' ? 'selected' : '' }}>Low</option>
                                        <option value="medium" {{ old('priority') === 'medium' ? 'selected' : '' }}>Medium</option>
                                        <option value="high" {{ old('priority') === 'high' ? 'selected' : '' }}>High</option>
                                        <option value="critical" {{ old('priority') === 'critical' ? 'selected' : '' }}>Critical</option>
                                    </select>
                                    @error('priority')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="scheduled_date" class="form-label">Scheduled Date <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control @error('scheduled_date') is-invalid @enderror" 
                                           id="scheduled_date" name="scheduled_date" 
                                           value="{{ old('scheduled_date') }}" required>
                                    @error('scheduled_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label for="scheduled_time" class="form-label">Scheduled Time</label>
                                    <input type="time" class="form-control @error('scheduled_time') is-invalid @enderror" 
                                           id="scheduled_time" name="scheduled_time" 
                                           value="{{ old('scheduled_time', '09:00') }}">
                                    @error('scheduled_time')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="estimated_duration" class="form-label">Estimated Duration (minutes) <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control @error('estimated_duration') is-invalid @enderror" 
                                           id="estimated_duration" name="estimated_duration" 
                                           value="{{ old('estimated_duration') }}" 
                                           step="1" min="1" max="480" placeholder="e.g., 120 for 2 hours">
                                    <div class="form-text">Enter duration in minutes (1-480 minutes, max 8 hours)</div>
                                    @error('estimated_duration')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
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
                            </div>
                        </div>

                        <!-- Maintenance Description -->
                        <div class="mb-4">
                            <h5 class="text-primary mb-3">
                                <i class="fas fa-file-alt me-2"></i>Maintenance Information
                            </h5>
                            
                            <div class="mb-3">
                                <label for="description" class="form-label">Maintenance Description <span class="text-danger">*</span></label>
                                <textarea class="form-control @error('description') is-invalid @enderror" 
                                          id="description" name="description" 
                                          rows="4" required>{{ old('description') }}</textarea>
                                <div class="form-text">Describe what maintenance work needs to be performed.</div>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="notes" class="form-label">Additional Notes (Optional)</label>
                                <textarea class="form-control @error('notes') is-invalid @enderror" 
                                          id="notes" name="notes" 
                                          rows="3">{{ old('notes') }}</textarea>
                                <div class="form-text">Any additional information about the maintenance.</div>
                                @error('notes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('sc.equipment-monitoring.maintenance') }}" class="btn btn-secondary">
                                <i class="fas fa-times me-1"></i> Cancel
                            </a>
                            <button type="submit" class="btn btn-primary" {{ $personalEquipment->count() === 0 && $projectEquipment->count() === 0 ? 'disabled' : '' }}>
                                <i class="fas fa-save me-1"></i> Schedule Maintenance
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
    // Set minimum date to today
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('scheduled_date').min = today;
    
    // Set default time if not already set
    if (!document.getElementById('scheduled_time').value) {
        document.getElementById('scheduled_time').value = '09:00';
    }
    
    // Convert hours to minutes helper
    const durationInput = document.getElementById('estimated_duration');
    durationInput.addEventListener('input', function() {
        const value = parseInt(this.value);
        if (value > 480) {
            this.value = 480;
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
.form-check-input:checked {
    background-color: #0d6efd;
    border-color: #0d6efd;
}
</style>
@endpush 