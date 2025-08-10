@extends('app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="card-title mb-0">
                            <i class="fas fa-cogs me-2"></i>Equipment Details
                        </h4>
                        <div class="d-flex gap-2">
                            <a href="{{ route('sc.equipment-monitoring.equipment') }}" class="btn btn-outline-secondary btn-sm">
                                <i class="fas fa-arrow-left me-1"></i> Back to Equipment
                            </a>
                            @if($monitoredEquipment->status === 'active')
                                <button type="button" class="btn btn-outline-warning btn-sm" 
                                        data-bs-toggle="modal" data-bs-target="#availabilityModal">
                                    <i class="fas fa-edit me-1"></i> Update Availability
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Status Alert -->
                    <div class="alert alert-{{ $monitoredEquipment->availability_status === 'available' ? 'success' : ($monitoredEquipment->availability_status === 'in_use' ? 'warning' : 'danger') }} mb-4">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-{{ $monitoredEquipment->availability_status === 'available' ? 'check-circle' : ($monitoredEquipment->availability_status === 'in_use' ? 'clock' : 'exclamation-triangle') }} me-2"></i>
                            <div>
                                <strong>Availability: {{ ucfirst(str_replace('_', ' ', $monitoredEquipment->availability_status)) }}</strong>
                                @if($monitoredEquipment->availability_status === 'available')
                                    <br><small>This equipment is available for use.</small>
                                @elseif($monitoredEquipment->availability_status === 'in_use')
                                    <br><small>This equipment is currently in use.</small>
                                @elseif($monitoredEquipment->availability_status === 'maintenance')
                                    <br><small>This equipment is under maintenance.</small>
                                @else
                                    <br><small>This equipment is out of order.</small>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Equipment Information -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h5 class="text-primary mb-3">
                                <i class="fas fa-cogs me-2"></i>Equipment Information
                            </h5>
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Equipment Name:</strong></td>
                                    <td>{{ $monitoredEquipment->equipment_name }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Description:</strong></td>
                                    <td>{{ $monitoredEquipment->equipment_description }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Quantity:</strong></td>
                                    <td><span class="badge bg-primary">{{ $monitoredEquipment->quantity }} units</span></td>
                                </tr>
                                <tr>
                                    <td><strong>Usage Type:</strong></td>
                                    <td>
                                        <span class="badge bg-{{ $monitoredEquipment->usage_type === 'personal' ? 'info' : 'primary' }}">
                                            {{ $monitoredEquipment->usage_type === 'personal' ? 'Personal Use' : 'Project Site' }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Status:</strong></td>
                                    <td>
                                        <span class="badge bg-{{ $monitoredEquipment->status === 'active' ? 'success' : 'secondary' }}">
                                            {{ ucfirst($monitoredEquipment->status) }}
                                        </span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h5 class="text-primary mb-3">
                                <i class="fas fa-info-circle me-2"></i>Assignment Details
                            </h5>
                            <table class="table table-borderless">
                                @if($monitoredEquipment->project)
                                <tr>
                                    <td><strong>Project:</strong></td>
                                    <td>
                                        <a href="{{ route('projects.show', $monitoredEquipment->project) }}" class="text-decoration-none">
                                            {{ $monitoredEquipment->project->name }}
                                        </a>
                                    </td>
                                </tr>
                                @endif
                                <tr>
                                    <td><strong>Assigned Date:</strong></td>
                                    <td>{{ $monitoredEquipment->created_at->format('M d, Y \a\t h:i A') }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Last Updated:</strong></td>
                                    <td>{{ $monitoredEquipment->updated_at->format('M d, Y \a\t h:i A') }}</td>
                                </tr>
                                @if($monitoredEquipment->next_maintenance_date)
                                <tr>
                                    <td><strong>Next Maintenance:</strong></td>
                                    <td>
                                        @if($monitoredEquipment->next_maintenance_date->isPast())
                                            <span class="text-danger">
                                                <i class="fas fa-exclamation-triangle me-1"></i>Overdue
                                            </span>
                                        @elseif($monitoredEquipment->next_maintenance_date <= now()->addDays(7))
                                            <span class="text-warning">
                                                <i class="fas fa-clock me-1"></i>{{ $monitoredEquipment->next_maintenance_date->format('M d, Y') }}
                                            </span>
                                        @else
                                            <span class="text-muted">{{ $monitoredEquipment->next_maintenance_date->format('M d, Y') }}</span>
                                        @endif
                                    </td>
                                </tr>
                                @endif
                                @if($monitoredEquipment->equipmentRequest)
                                <tr>
                                    <td><strong>Request ID:</strong></td>
                                    <td>
                                        <a href="{{ route('sc.equipment-monitoring.show-request', $monitoredEquipment->equipmentRequest) }}" class="text-decoration-none">
                                            #{{ $monitoredEquipment->equipmentRequest->id }}
                                        </a>
                                    </td>
                                </tr>
                                @endif
                            </table>
                        </div>
                    </div>

                    <!-- Maintenance Schedules -->
                    @if($monitoredEquipment->maintenanceSchedules && $monitoredEquipment->maintenanceSchedules->count() > 0)
                    <div class="mb-4">
                        <h5 class="text-primary mb-3">
                            <i class="fas fa-tools me-2"></i>Maintenance Schedules
                        </h5>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Type</th>
                                        <th>Scheduled Date</th>
                                        <th>Status</th>
                                        <th>Priority</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($monitoredEquipment->maintenanceSchedules as $maintenance)
                                        <tr class="{{ $maintenance->is_overdue ? 'table-danger' : ($maintenance->is_upcoming ? 'table-warning' : '') }}">
                                            <td>{{ $maintenance->formatted_maintenance_type }}</td>
                                            <td>
                                                {{ $maintenance->scheduled_date ? $maintenance->scheduled_date->format('M d, Y') : 'Not scheduled' }}
                                                @if($maintenance->is_overdue)
                                                    <br><small class="text-danger">Overdue</small>
                                                @elseif($maintenance->is_upcoming)
                                                    <br><small class="text-warning">Upcoming</small>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge bg-{{ $maintenance->status_badge_color }}">
                                                    {{ $maintenance->formatted_status }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-{{ $maintenance->priority_badge_color }}">
                                                    {{ $maintenance->formatted_priority }}
                                                </span>
                                            </td>
                                            <td>
                                                <a href="{{ route('sc.equipment-monitoring.show-maintenance', $maintenance) }}" 
                                                   class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-eye"></i> View
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @endif

                    <!-- Equipment Request Information -->
                    @if($monitoredEquipment->equipmentRequest)
                    <div class="mb-4">
                        <h5 class="text-primary mb-3">
                            <i class="fas fa-truck-loading me-2"></i>Original Request
                        </h5>
                        <div class="card bg-light">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <strong>Request ID:</strong> 
                                        <a href="{{ route('sc.equipment-monitoring.show-request', $monitoredEquipment->equipmentRequest) }}" class="text-decoration-none">
                                            #{{ $monitoredEquipment->equipmentRequest->id }}
                                        </a>
                                    </div>
                                    <div class="col-md-6">
                                        <strong>Request Status:</strong> 
                                        <span class="badge bg-{{ $monitoredEquipment->equipmentRequest->status_badge_color }}">
                                            {{ $monitoredEquipment->equipmentRequest->formatted_status }}
                                        </span>
                                    </div>
                                </div>
                                <div class="row mt-2">
                                    <div class="col-md-6">
                                        <strong>Urgency:</strong> 
                                        <span class="badge bg-{{ $monitoredEquipment->equipmentRequest->urgency_badge_color }}">
                                            {{ $monitoredEquipment->equipmentRequest->formatted_urgency }}
                                        </span>
                                    </div>
                                    <div class="col-md-6">
                                        <strong>Requested Date:</strong> 
                                        {{ $monitoredEquipment->equipmentRequest->created_at->format('M d, Y') }}
                                    </div>
                                </div>
                                @if($monitoredEquipment->equipmentRequest->justification)
                                <div class="mt-3">
                                    <strong>Justification:</strong>
                                    <p class="mb-0 mt-1">{{ $monitoredEquipment->equipmentRequest->justification }}</p>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Actions -->
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('sc.equipment-monitoring.equipment') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-1"></i> Back to Equipment
                        </a>
                        <div class="d-flex gap-2">
                            @if($monitoredEquipment->equipmentRequest)
                                <a href="{{ route('sc.equipment-monitoring.show-request', $monitoredEquipment->equipmentRequest) }}" class="btn btn-outline-info">
                                    <i class="fas fa-truck-loading me-1"></i> View Request
                                </a>
                            @endif
                            @if($monitoredEquipment->maintenanceSchedules && $monitoredEquipment->maintenanceSchedules->count() > 0)
                                <a href="{{ route('sc.equipment-monitoring.maintenance', ['equipment_id' => $monitoredEquipment->id]) }}" class="btn btn-outline-warning">
                                    <i class="fas fa-tools me-1"></i> View Maintenance
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Update Availability Modal -->
@if($monitoredEquipment->status === 'active')
<div class="modal fade" id="availabilityModal" tabindex="-1" aria-labelledby="availabilityModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="availabilityModalLabel">Update Equipment Availability</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="{{ route('sc.equipment-monitoring.update-availability', $monitoredEquipment) }}">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <p>Update the availability status for:</p>
                    <div class="alert alert-info">
                        <strong>{{ $monitoredEquipment->equipment_name }}</strong>
                    </div>
                    <div class="mb-3">
                        <label for="availability_status" class="form-label">Availability Status</label>
                        <select name="availability_status" id="availability_status" class="form-select" required>
                            <option value="available" {{ $monitoredEquipment->availability_status === 'available' ? 'selected' : '' }}>Available</option>
                            <option value="in_use" {{ $monitoredEquipment->availability_status === 'in_use' ? 'selected' : '' }}>In Use</option>
                            <option value="maintenance" {{ $monitoredEquipment->availability_status === 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                            <option value="out_of_order" {{ $monitoredEquipment->availability_status === 'out_of_order' ? 'selected' : '' }}>Out of Order</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="availability_notes" class="form-label">Notes (Optional)</label>
                        <textarea name="availability_notes" id="availability_notes" class="form-control" rows="3" 
                                  placeholder="Add any notes about the availability change..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Availability</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endsection

@push('styles')
<style>
.card {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    border: 1px solid rgba(0, 0, 0, 0.125);
}
.table-borderless td {
    padding: 0.5rem 0;
    border: none;
}
</style>
@endpush 