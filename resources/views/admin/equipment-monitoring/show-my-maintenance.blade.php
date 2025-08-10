@extends('app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-10 offset-md-1">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h2">Maintenance Details</h1>
                    <p class="text-muted">Maintenance ID: #{{ $equipmentMaintenance->id }}</p>
                </div>
                <div class="d-flex gap-2">
                    @if($equipmentMaintenance->status === 'scheduled')
                        <button class="btn btn-success complete-maintenance" data-bs-toggle="modal" data-bs-target="#completeMaintenanceModal">
                            <i class="fas fa-check me-1"></i>Mark Complete
                        </button>
                        <button class="btn btn-danger cancel-maintenance" data-bs-toggle="modal" data-bs-target="#cancelMaintenanceModal">
                            <i class="fas fa-times me-1"></i>Cancel
                        </button>
                    @endif
                    <a href="{{ route('admin.equipment-monitoring.my-maintenance') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i>Back to Maintenance
                    </a>
                </div>
            </div>

            <div class="row">
                <!-- Maintenance Information -->
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Maintenance Information</h5>
                        </div>
                        <div class="card-body">
                            <!-- Equipment Info -->
                            <div class="mb-3">
                                <strong>Equipment:</strong>
                                <p class="mb-0">
                                    <a href="{{ route('admin.equipment-monitoring.show-my-equipment', $equipmentMaintenance->monitoredEquipment) }}" class="text-decoration-none">
                                        {{ $equipmentMaintenance->monitoredEquipment->equipment_name }}
                                    </a>
                                    @if($equipmentMaintenance->monitoredEquipment->project)
                                        <br><small class="text-muted">
                                            <i class="fas fa-project-diagram me-1"></i>
                                            {{ $equipmentMaintenance->monitoredEquipment->project->name }}
                                        </small>
                                    @else
                                        <br><small class="text-muted">
                                            <i class="fas fa-user me-1"></i>Personal Equipment
                                        </small>
                                    @endif
                                </p>
                            </div>

                            <!-- Basic Info -->
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <strong>Maintenance Type:</strong>
                                    <p class="mb-0">
                                        <span class="badge bg-info">
                                            {{ $equipmentMaintenance->formatted_maintenance_type }}
                                        </span>
                                    </p>
                                </div>
                                <div class="col-md-4">
                                    <strong>Priority:</strong>
                                    <p class="mb-0">
                                        <span class="badge bg-{{ $equipmentMaintenance->priority_badge_color }}">
                                            {{ $equipmentMaintenance->formatted_priority }}
                                        </span>
                                    </p>
                                </div>
                                <div class="col-md-4">
                                    <strong>Status:</strong>
                                    <p class="mb-0">
                                        <span class="badge bg-{{ $equipmentMaintenance->status_badge_color }}">
                                            {{ $equipmentMaintenance->formatted_status }}
                                        </span>
                                        @if($equipmentMaintenance->is_overdue)
                                            <span class="badge bg-danger ms-1">Overdue</span>
                                        @elseif($equipmentMaintenance->is_upcoming)
                                            <span class="badge bg-warning ms-1">Due Soon</span>
                                        @endif
                                    </p>
                                </div>
                            </div>

                            <!-- Scheduling Info -->
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <strong>Scheduled Date & Time:</strong>
                                    <p class="mb-0">
                                        {{ $equipmentMaintenance->scheduled_date->format('l, M d, Y') }}
                                        <br><small class="text-muted">{{ $equipmentMaintenance->scheduled_date->format('g:i A') }}</small>
                                        @if($equipmentMaintenance->status === 'scheduled')
                                            <br><small class="text-muted">{{ $equipmentMaintenance->scheduled_date->diffForHumans() }}</small>
                                        @endif
                                    </p>
                                </div>
                                @if($equipmentMaintenance->completed_date)
                                    <div class="col-md-6">
                                        <strong>Completed Date:</strong>
                                        <p class="mb-0">
                                            {{ $equipmentMaintenance->completed_date->format('l, M d, Y') }}
                                            <br><small class="text-muted">{{ $equipmentMaintenance->completed_date->format('g:i A') }}</small>
                                            <br><small class="text-success">{{ $equipmentMaintenance->completed_date->diffForHumans() }}</small>
                                        </p>
                                    </div>
                                @endif
                            </div>

                            <!-- Duration Info -->
                            <div class="row mb-3">
                                @if($equipmentMaintenance->estimated_duration_hours)
                                    <div class="col-md-6">
                                        <strong>Estimated Duration:</strong>
                                        <p class="mb-0">
                                            {{ $equipmentMaintenance->estimated_duration_hours }} hours
                                            <small class="text-muted">({{ $equipmentMaintenance->estimated_duration }} minutes)</small>
                                        </p>
                                    </div>
                                @endif
                                @if($equipmentMaintenance->actual_duration_hours)
                                    <div class="col-md-6">
                                        <strong>Actual Duration:</strong>
                                        <p class="mb-0">
                                            {{ $equipmentMaintenance->actual_duration_hours }} hours
                                            <small class="text-muted">({{ $equipmentMaintenance->actual_duration }} minutes)</small>
                                        </p>
                                    </div>
                                @endif
                            </div>

                            @if($equipmentMaintenance->cost)
                                <div class="mb-3">
                                    <strong>Cost:</strong>
                                    <p class="mb-0">₱{{ number_format($equipmentMaintenance->cost, 2) }}</p>
                                </div>
                            @endif

                            <!-- Description -->
                            <div class="mb-3">
                                <strong>Description:</strong>
                                <p class="mb-0">{{ $equipmentMaintenance->description }}</p>
                            </div>

                            <!-- Notes -->
                            @if($equipmentMaintenance->notes)
                                <div class="mb-3">
                                    <strong>Initial Notes:</strong>
                                    <div class="bg-light p-3 rounded">
                                        {{ $equipmentMaintenance->notes }}
                                    </div>
                                </div>
                            @endif

                            <!-- Completion Notes -->
                            @if($equipmentMaintenance->completion_notes)
                                <div class="mb-3">
                                    <strong>Completion Notes:</strong>
                                    <div class="bg-success bg-opacity-10 p-3 rounded border-start border-success border-3">
                                        {{ $equipmentMaintenance->completion_notes }}
                                    </div>
                                </div>
                            @endif

                            <!-- Performer Info -->
                            @if($equipmentMaintenance->performedBy)
                                <div class="mb-0">
                                    <strong>Performed By:</strong>
                                    <p class="mb-0">
                                        {{ $equipmentMaintenance->performedBy->first_name }} {{ $equipmentMaintenance->performedBy->last_name }}
                                        <br><small class="text-muted">{{ $equipmentMaintenance->performedBy->email }}</small>
                                    </p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Status Timeline & Actions -->
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Maintenance Status</h5>
                        </div>
                        <div class="card-body">
                            <div class="text-center mb-3">
                                <span class="badge bg-{{ $equipmentMaintenance->status_badge_color }} fs-6">
                                    {{ $equipmentMaintenance->formatted_status }}
                                </span>
                            </div>

                            <!-- Timeline -->
                            <div class="timeline">
                                <div class="timeline-item">
                                    <div class="timeline-marker bg-primary"></div>
                                    <div class="timeline-content">
                                        <h6 class="timeline-title">Maintenance Scheduled</h6>
                                        <p class="timeline-description">
                                            {{ $equipmentMaintenance->created_at->format('M d, Y g:i A') }}
                                            <br><small class="text-muted">{{ $equipmentMaintenance->created_at->diffForHumans() }}</small>
                                        </p>
                                    </div>
                                </div>

                                @if($equipmentMaintenance->status === 'in_progress')
                                    <div class="timeline-item">
                                        <div class="timeline-marker bg-info"></div>
                                        <div class="timeline-content">
                                            <h6 class="timeline-title">Maintenance In Progress</h6>
                                            <p class="timeline-description">
                                                Work is currently being performed
                                            </p>
                                        </div>
                                    </div>
                                @endif

                                @if($equipmentMaintenance->status === 'completed')
                                    <div class="timeline-item">
                                        <div class="timeline-marker bg-success"></div>
                                        <div class="timeline-content">
                                            <h6 class="timeline-title">Maintenance Completed</h6>
                                            <p class="timeline-description">
                                                {{ $equipmentMaintenance->completed_date->format('M d, Y g:i A') }}
                                                <br><small class="text-muted">{{ $equipmentMaintenance->completed_date->diffForHumans() }}</small>
                                                @if($equipmentMaintenance->performedBy)
                                                    <br><small class="text-muted">by {{ $equipmentMaintenance->performedBy->first_name }} {{ $equipmentMaintenance->performedBy->last_name }}</small>
                                                @endif
                                            </p>
                                        </div>
                                    </div>
                                @endif

                                @if($equipmentMaintenance->status === 'cancelled')
                                    <div class="timeline-item">
                                        <div class="timeline-marker bg-danger"></div>
                                        <div class="timeline-content">
                                            <h6 class="timeline-title">Maintenance Cancelled</h6>
                                            <p class="timeline-description">
                                                {{ $equipmentMaintenance->updated_at->format('M d, Y g:i A') }}
                                                <br><small class="text-muted">{{ $equipmentMaintenance->updated_at->diffForHumans() }}</small>
                                            </p>
                                        </div>
                                    </div>
                                @endif
                            </div>

                            <!-- Quick Actions -->
                            <div class="mt-4">
                                <h6>Quick Actions</h6>
                                <div class="d-grid gap-2">
                                    @if($equipmentMaintenance->status === 'scheduled')
                                        <button class="btn btn-success btn-sm complete-maintenance" data-bs-toggle="modal" data-bs-target="#completeMaintenanceModal">
                                            <i class="fas fa-check me-1"></i>Mark Complete
                                        </button>
                                        <button class="btn btn-danger btn-sm cancel-maintenance" data-bs-toggle="modal" data-bs-target="#cancelMaintenanceModal">
                                            <i class="fas fa-times me-1"></i>Cancel
                                        </button>
                                    @endif
                                    
                                    <a href="{{ route('admin.equipment-monitoring.show-my-equipment', $equipmentMaintenance->monitoredEquipment) }}" class="btn btn-outline-primary btn-sm">
                                        <i class="fas fa-cubes me-1"></i>View Equipment
                                    </a>
                                    
                                    <a href="{{ route('admin.equipment-monitoring.my-maintenance') }}" class="btn btn-outline-secondary btn-sm">
                                        <i class="fas fa-list me-1"></i>All Maintenance
                                    </a>
                                    
                                    <a href="{{ route('admin.equipment-monitoring.my-dashboard') }}" class="btn btn-outline-primary btn-sm">
                                        <i class="fas fa-home me-1"></i>Dashboard
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Maintenance Info -->
                    <div class="card mt-3">
                        <div class="card-header">
                            <h6 class="card-title mb-0">Maintenance Summary</h6>
                        </div>
                        <div class="card-body">
                            <div class="row text-center">
                                <div class="col-6">
                                    <div class="border-end">
                                        <h6 class="text-muted mb-1">Priority</h6>
                                        <h5 class="mb-0">
                                            <span class="badge bg-{{ $equipmentMaintenance->priority_badge_color }}">
                                                {{ ucfirst($equipmentMaintenance->priority) }}
                                            </span>
                                        </h5>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <h6 class="text-muted mb-1">Type</h6>
                                    <h5 class="mb-0">
                                        <span class="badge bg-info">
                                            {{ ucfirst($equipmentMaintenance->maintenance_type) }}
                                        </span>
                                    </h5>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Complete Maintenance Modal -->
<div class="modal fade" id="completeMaintenanceModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="completeMaintenanceForm">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Complete Maintenance</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="actual_duration" class="form-label">Actual Duration (minutes)</label>
                        <input type="number" class="form-control" id="actual_duration" name="actual_duration" min="1" max="480" value="{{ $equipmentMaintenance->estimated_duration }}">
                        <small class="form-text text-muted">Estimated: {{ $equipmentMaintenance->estimated_duration }} minutes</small>
                    </div>
                    <div class="mb-3">
                        <label for="cost" class="form-label">Total Cost (₱)</label>
                        <input type="number" class="form-control" id="cost" name="cost" min="0" step="0.01" placeholder="0.00">
                    </div>
                    <div class="mb-3">
                        <label for="completion_notes" class="form-label">Completion Notes <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="cancel_reason" name="cancel_reason" rows="3" placeholder="Explain why this maintenance is being cancelled..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-danger">Cancel Maintenance</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Cancel Maintenance Modal -->
<div class="modal fade" id="cancelMaintenanceModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="cancelMaintenanceForm">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Cancel Maintenance</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="cancel_reason" class="form-label">Cancellation Reason <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="cancel_reason" name="cancel_reason" rows="3" placeholder="Explain why this maintenance is being cancelled..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-danger">Cancel Maintenance</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline:before {
    content: '';
    position: absolute;
    left: 10px;
    top: 0;
    bottom: 0;
    width: 2px;
    background-color: #dee2e6;
}

.timeline-item {
    position: relative;
    margin-bottom: 20px;
}

.timeline-marker {
    position: absolute;
    left: -25px;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    border: 2px solid #fff;
    box-shadow: 0 0 0 2px #dee2e6;
}

.timeline-content {
    background: #f8f9fa;
    padding: 10px 15px;
    border-radius: 5px;
    border-left: 3px solid #007bff;
}

.timeline-title {
    font-size: 0.9rem;
    font-weight: 600;
    margin-bottom: 5px;
}

.timeline-description {
    font-size: 0.85rem;
    margin-bottom: 0;
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const maintenanceId = {{ $equipmentMaintenance->id }};

    // Complete maintenance form submission
    document.getElementById('completeMaintenanceForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        
        fetch(`/admin/equipment-monitoring/maintenance/${maintenanceId}/complete`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.message || 'Error completing maintenance');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error completing maintenance');
        });
    });

    // Cancel maintenance form submission
    document.getElementById('cancelMaintenanceForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        
        fetch(`/admin/equipment-monitoring/maintenance/${maintenanceId}/cancel`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.message || 'Error cancelling maintenance');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error cancelling maintenance');
        });
    });
</script>
@endpush