@extends('app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Schedule Equipment Maintenance</h5>
                        <a href="{{ route('admin.equipment-monitoring.my-maintenance') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-1"></i>Back to Maintenance
                        </a>
                    </div>
                    <p class="text-muted mt-2 mb-0">Schedule maintenance for any equipment in the system</p>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.equipment-monitoring.store-maintenance') }}" method="POST">
                        @csrf

                        <!-- Equipment Selection -->
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <h6 class="text-primary">Equipment Selection</h6>
                                <hr>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="monitored_equipment_id" class="form-label">Select Equipment <span class="text-danger">*</span></label>
                            <select class="form-control @error('monitored_equipment_id') is-invalid @enderror" 
                                    id="monitored_equipment_id" 
                                    name="monitored_equipment_id" 
                                    required>
                                <option value="">Select Equipment</option>
                                
                                @if($personalEquipment->count() > 0)
                                    <optgroup label="Personal Equipment">
                                        @foreach($personalEquipment as $equipment)
                                            <option value="{{ $equipment->id }}" 
                                                    data-owner="{{ $equipment->user->full_name ?? 'Unknown' }}"
                                                    {{ old('monitored_equipment_id') == $equipment->id ? 'selected' : '' }}
                                                    {{ request('equipment') == $equipment->id ? 'selected' : '' }}>
                                                {{ $equipment->equipment_name }} (Qty: {{ $equipment->quantity }}) - {{ ucfirst($equipment->availability_status) }}
                                                @if($equipment->user_id !== auth()->id())
                                                    - Owner: {{ $equipment->user->full_name ?? 'Unknown' }}
                                                @endif
                                            </option>
                                        @endforeach
                                    </optgroup>
                                @endif
                                
                                @if($projectEquipment->count() > 0)
                                    <optgroup label="Project Equipment">
                                        @foreach($projectEquipment as $equipment)
                                            <option value="{{ $equipment->id }}" 
                                                    data-owner="{{ $equipment->user->full_name ?? 'Unknown' }}"
                                                    data-project="{{ $equipment->project->name ?? 'Unknown Project' }}"
                                                    {{ old('monitored_equipment_id') == $equipment->id ? 'selected' : '' }}
                                                    {{ request('equipment') == $equipment->id ? 'selected' : '' }}>
                                                {{ $equipment->equipment_name }} ({{ $equipment->project->name ?? 'Unknown Project' }}) - {{ ucfirst($equipment->availability_status) }}
                                                @if($equipment->user_id !== auth()->id())
                                                    - Owner: {{ $equipment->user->full_name ?? 'Unknown' }}
                                                @endif
                                            </option>
                                        @endforeach
                                    </optgroup>
                                @endif
                            </select>
                            @error('monitored_equipment_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            @if($personalEquipment->count() === 0 && $projectEquipment->count() === 0)
                                <small class="form-text text-muted text-danger">
                                    No active equipment available for maintenance. 
                                    <a href="{{ route('admin.equipment-monitoring.equipment-list') }}">View all equipment</a>.
                                </small>
                            @else
                                <small class="form-text text-muted">
                                    <i class="fas fa-info-circle me-1"></i>As an admin, you can schedule maintenance for any equipment in the system.
                                </small>
                            @endif
                        </div>

                        <!-- Equipment Details Display -->
                        <div id="equipment-details" class="alert alert-info d-none mb-3">
                            <h6><i class="fas fa-cube me-2"></i>Equipment Details</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <strong>Owner:</strong> <span id="equipment-owner"></span>
                                </div>
                                <div class="col-md-6">
                                    <strong>Project:</strong> <span id="equipment-project"></span>
                                </div>
                            </div>
                        </div>

                        <!-- Maintenance Details -->
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <h6 class="text-primary">Maintenance Details</h6>
                                <hr>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="maintenance_type" class="form-label">Maintenance Type <span class="text-danger">*</span></label>
                                <select class="form-control @error('maintenance_type') is-invalid @enderror" 
                                        id="maintenance_type" 
                                        name="maintenance_type" 
                                        required>
                                    <option value="">Select Type</option>
                                    <option value="routine" {{ old('maintenance_type') == 'routine' ? 'selected' : '' }}>
                                        Routine Maintenance
                                    </option>
                                    <option value="repair" {{ old('maintenance_type') == 'repair' ? 'selected' : '' }}>
                                        Repair
                                    </option>
                                    <option value="inspection" {{ old('maintenance_type') == 'inspection' ? 'selected' : '' }}>
                                        Inspection
                                    </option>
                                    <option value="calibration" {{ old('maintenance_type') == 'calibration' ? 'selected' : '' }}>
                                        Calibration
                                    </option>
                                    <option value="replacement" {{ old('maintenance_type') == 'replacement' ? 'selected' : '' }}>
                                        Replacement
                                    </option>
                                </select>
                                @error('maintenance_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="priority" class="form-label">Priority <span class="text-danger">*</span></label>
                                <select class="form-control @error('priority') is-invalid @enderror" 
                                        id="priority" 
                                        name="priority" 
                                        required>
                                    <option value="">Select Priority</option>
                                    <option value="low" {{ old('priority') == 'low' ? 'selected' : '' }}>
                                        Low
                                    </option>
                                    <option value="medium" {{ old('priority', 'medium') == 'medium' ? 'selected' : '' }}>
                                        Medium
                                    </option>
                                    <option value="high" {{ old('priority') == 'high' ? 'selected' : '' }}>
                                        High
                                    </option>
                                    <option value="critical" {{ old('priority') == 'critical' ? 'selected' : '' }}>
                                        Critical
                                    </option>
                                </select>
                                @error('priority')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Scheduling -->
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <h6 class="text-primary">Scheduling</h6>
                                <hr>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="scheduled_date" class="form-label">Scheduled Date <span class="text-danger">*</span></label>
                                <input type="date" 
                                       class="form-control @error('scheduled_date') is-invalid @enderror" 
                                       id="scheduled_date" 
                                       name="scheduled_date" 
                                       value="{{ old('scheduled_date') }}" 
                                       min="{{ date('Y-m-d') }}"
                                       required>
                                @error('scheduled_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label for="scheduled_time" class="form-label">Scheduled Time</label>
                                <input type="time" 
                                       class="form-control @error('scheduled_time') is-invalid @enderror" 
                                       id="scheduled_time" 
                                       name="scheduled_time" 
                                       value="{{ old('scheduled_time') }}">
                                @error('scheduled_time')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">Optional - defaults to business hours</small>
                            </div>
                            <div class="col-md-4">
                                <label for="estimated_duration" class="form-label">Estimated Duration (minutes) <span class="text-danger">*</span></label>
                                <input type="number" 
                                       class="form-control @error('estimated_duration') is-invalid @enderror" 
                                       id="estimated_duration" 
                                       name="estimated_duration" 
                                       value="{{ old('estimated_duration') }}" 
                                       min="1" 
                                       max="480"
                                       placeholder="60"
                                       required>
                                @error('estimated_duration')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">Maximum 8 hours (480 minutes)</small>
                            </div>
                        </div>

                        <!-- Description and Notes -->
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <h6 class="text-primary">Description and Notes</h6>
                                <hr>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Maintenance Description <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" 
                                      name="description" 
                                      rows="3" 
                                      placeholder="Describe what maintenance work needs to be done..."
                                      required>{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="notes" class="form-label">Additional Notes</label>
                            <textarea class="form-control @error('notes') is-invalid @enderror" 
                                      id="notes" 
                                      name="notes" 
                                      rows="2" 
                                      placeholder="Special instructions, required tools, or other important information...">{{ old('notes') }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Information Box -->
                        <div class="alert alert-info">
                            <h6><i class="fas fa-info-circle me-2"></i>Maintenance Scheduling Information</h6>
                            <ul class="mb-0">
                                <li><strong>Admin Privileges:</strong> As an admin, you can schedule maintenance for any equipment in the system</li>
                                <li><strong>Equipment Availability:</strong> Equipment will be marked as "Under Maintenance" during the scheduled period</li>
                                <li><strong>Priority Levels:</strong> Higher priority maintenance may require immediate attention</li>
                                <li><strong>Duration:</strong> Plan for buffer time in case maintenance takes longer than estimated</li>
                                <li><strong>Notifications:</strong> The equipment owner will be notified of the scheduled maintenance</li>
                            </ul>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.equipment-monitoring.my-maintenance') }}" class="btn btn-secondary">
                                <i class="fas fa-times me-1"></i>Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-calendar-plus me-1"></i>Schedule Maintenance
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
    // Set default time if not provided
    const timeInput = document.getElementById('scheduled_time');
    if (!timeInput.value) {
        timeInput.value = '09:00';
    }
    
    // Set minimum date to today
    const dateInput = document.getElementById('scheduled_date');
    if (!dateInput.value) {
        const today = new Date();
        const tomorrow = new Date(today);
        tomorrow.setDate(tomorrow.getDate() + 1);
        dateInput.value = tomorrow.toISOString().split('T')[0];
    }
    
    // Equipment selection change handler to show context
    const equipmentSelect = document.getElementById('monitored_equipment_id');
    const equipmentDetails = document.getElementById('equipment-details');
    const equipmentOwner = document.getElementById('equipment-owner');
    const equipmentProject = document.getElementById('equipment-project');
    
    equipmentSelect.addEventListener('change', function() {
        if (this.value) {
            const selectedOption = this.options[this.selectedIndex];
            const owner = selectedOption.getAttribute('data-owner');
            const project = selectedOption.getAttribute('data-project');
            
            if (owner) {
                equipmentOwner.textContent = owner;
                equipmentProject.textContent = project || 'Personal Equipment';
                equipmentDetails.classList.remove('d-none');
            } else {
                equipmentDetails.classList.add('d-none');
            }
        } else {
            equipmentDetails.classList.add('d-none');
        }
    });
    
    // Trigger change event if equipment is pre-selected
    if (equipmentSelect.value) {
        equipmentSelect.dispatchEvent(new Event('change'));
    }
    
    // Maintenance type change handler for suggested durations
    const maintenanceTypeSelect = document.getElementById('maintenance_type');
    const durationInput = document.getElementById('estimated_duration');
    
    maintenanceTypeSelect.addEventListener('change', function() {
        const suggestedDurations = {
            'routine': 60,
            'repair': 120,
            'inspection': 30,
            'calibration': 90,
            'replacement': 180
        };
        
        if (this.value && !durationInput.value) {
            durationInput.value = suggestedDurations[this.value] || 60;
        }
    });
});
</script>
@endpush