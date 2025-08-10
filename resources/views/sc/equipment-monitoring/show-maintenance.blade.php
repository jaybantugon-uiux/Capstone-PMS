@extends('app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="card-title mb-0">
                            <i class="fas fa-tools me-2"></i>Maintenance Details
                        </h4>
                        <div class="d-flex gap-2">
                            <a href="{{ route('sc.equipment-monitoring.maintenance') }}" class="btn btn-outline-secondary btn-sm">
                                <i class="fas fa-arrow-left me-1"></i> Back to Maintenance
                            </a>
                            @if($equipmentMaintenance->status === 'scheduled')
                                <button type="button" class="btn btn-outline-success btn-sm" 
                                        data-bs-toggle="modal" data-bs-target="#completeModal">
                                    <i class="fas fa-check me-1"></i> Mark Complete
                                </button>
                                <button type="button" class="btn btn-outline-danger btn-sm" 
                                        data-bs-toggle="modal" data-bs-target="#cancelModal">
                                    <i class="fas fa-times me-1"></i> Cancel
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Status Alert -->
                    <div class="alert alert-{{ $equipmentMaintenance->status === 'completed' ? 'success' : ($equipmentMaintenance->status === 'cancelled' ? 'danger' : ($equipmentMaintenance->status === 'in_progress' ? 'warning' : 'info')) }} mb-4">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-{{ $equipmentMaintenance->status === 'completed' ? 'check-circle' : ($equipmentMaintenance->status === 'cancelled' ? 'times-circle' : ($equipmentMaintenance->status === 'in_progress' ? 'clock' : 'calendar')) }} me-2"></i>
                            <div>
                                <strong>Status: {{ ucfirst($equipmentMaintenance->status) }}</strong>
                                @if($equipmentMaintenance->status === 'completed')
                                    <br><small>This maintenance has been completed successfully.</small>
                                @elseif($equipmentMaintenance->status === 'cancelled')
                                    <br><small>This maintenance has been cancelled.</small>
                                @elseif($equipmentMaintenance->status === 'in_progress')
                                    <br><small>This maintenance is currently in progress.</small>
                                @else
                                    <br><small>This maintenance is scheduled.</small>
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
                                    <td>{{ $equipmentMaintenance->monitoredEquipment->equipment_name ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Description:</strong></td>
                                    <td>{{ $equipmentMaintenance->monitoredEquipment->equipment_description ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Quantity:</strong></td>
                                    <td><span class="badge bg-primary">{{ $equipmentMaintenance->monitoredEquipment->quantity ?? 0 }} units</span></td>
                                </tr>
                                <tr>
                                    <td><strong>Usage Type:</strong></td>
                                    <td>
                                        <span class="badge bg-{{ $equipmentMaintenance->monitoredEquipment->usage_type === 'personal' ? 'info' : 'primary' }}">
                                            {{ $equipmentMaintenance->monitoredEquipment->usage_type === 'personal' ? 'Personal Use' : 'Project Site' }}
                                        </span>
                                    </td>
                                </tr>
                                @if($equipmentMaintenance->monitoredEquipment->project)
                                <tr>
                                    <td><strong>Project:</strong></td>
                                    <td>
                                        <a href="{{ route('projects.show', $equipmentMaintenance->monitoredEquipment->project) }}" class="text-decoration-none">
                                            {{ $equipmentMaintenance->monitoredEquipment->project->name }}
                                        </a>
                                    </td>
                                </tr>
                                @endif
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h5 class="text-primary mb-3">
                                <i class="fas fa-tools me-2"></i>Maintenance Details
                            </h5>
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Maintenance Type:</strong></td>
                                    <td>
                                        <span class="badge bg-{{ $equipmentMaintenance->maintenance_type === 'preventive' ? 'info' : ($equipmentMaintenance->maintenance_type === 'corrective' ? 'warning' : 'danger') }}">
                                            {{ $equipmentMaintenance->formatted_maintenance_type }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Priority:</strong></td>
                                    <td>
                                        <span class="badge bg-{{ $equipmentMaintenance->priority_badge_color }}">
                                            {{ $equipmentMaintenance->formatted_priority }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Scheduled Date:</strong></td>
                                    <td>
                                        @if($equipmentMaintenance->scheduled_date)
                                            {{ $equipmentMaintenance->scheduled_date->format('M d, Y \a\t h:i A') }}
                                            @if($equipmentMaintenance->is_overdue)
                                                <br><span class="text-danger">Overdue</span>
                                            @elseif($equipmentMaintenance->is_upcoming)
                                                <br><span class="text-warning">Upcoming</span>
                                            @endif
                                        @else
                                            <span class="text-muted">Not scheduled</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Created Date:</strong></td>
                                    <td>{{ $equipmentMaintenance->created_at->format('M d, Y \a\t h:i A') }}</td>
                                </tr>
                                @if($equipmentMaintenance->estimated_duration_hours)
                                <tr>
                                    <td><strong>Estimated Duration:</strong></td>
                                    <td><strong>{{ $equipmentMaintenance->estimated_duration_hours }}</strong> hours</td>
                                </tr>
                                @endif
                                @if($equipmentMaintenance->estimated_cost)
                                <tr>
                                    <td><strong>Estimated Cost:</strong></td>
                                    <td>${{ number_format($equipmentMaintenance->estimated_cost, 2) }}</td>
                                </tr>
                                @endif
                            </table>
                        </div>
                    </div>

                    <!-- Maintenance Description -->
                    <div class="mb-4">
                        <h5 class="text-primary mb-3">
                            <i class="fas fa-file-alt me-2"></i>Maintenance Description
                        </h5>
                        <div class="card bg-light">
                            <div class="card-body">
                                <p class="mb-0">{{ $equipmentMaintenance->maintenance_description }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Additional Notes -->
                    @if($equipmentMaintenance->maintenance_notes)
                    <div class="mb-4">
                        <h5 class="text-primary mb-3">
                            <i class="fas fa-sticky-note me-2"></i>Additional Notes
                        </h5>
                        <div class="card bg-light">
                            <div class="card-body">
                                <p class="mb-0">{{ $equipmentMaintenance->maintenance_notes }}</p>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Completion Information -->
                    @if($equipmentMaintenance->status === 'completed')
                    <div class="mb-4">
                        <h5 class="text-primary mb-3">
                            <i class="fas fa-check-circle me-2"></i>Completion Information
                        </h5>
                        <div class="card bg-light">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <strong>Completed By:</strong> 
                                        {{ $equipmentMaintenance->performedBy->first_name }} {{ $equipmentMaintenance->performedBy->last_name }}
                                    </div>
                                    <div class="col-md-6">
                                        <strong>Completion Date:</strong> 
                                        {{ $equipmentMaintenance->completed_at ? $equipmentMaintenance->completed_at->format('M d, Y \a\t h:i A') : 'N/A' }}
                                    </div>
                                </div>
                                @if($equipmentMaintenance->actual_duration_hours)
                                <div class="row mt-2">
                                    <div class="col-md-6">
                                        <strong>Actual Duration:</strong> 
                                        <strong>{{ $equipmentMaintenance->actual_duration_hours }}</strong> hours
                                    </div>
                                    <div class="col-md-6">
                                        <strong>Cost:</strong> 
                                        ${{ number_format($equipmentMaintenance->cost ?? 0, 2) }}
                                    </div>
                                </div>
                                @endif
                                @if($equipmentMaintenance->completion_notes)
                                <div class="mt-3">
                                    <strong>Completion Notes:</strong>
                                    <p class="mb-0 mt-1">{{ $equipmentMaintenance->completion_notes }}</p>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Cancellation Information -->
                    @if($equipmentMaintenance->status === 'cancelled')
                    <div class="mb-4">
                        <h5 class="text-primary mb-3">
                            <i class="fas fa-times-circle me-2"></i>Cancellation Information
                        </h5>
                        <div class="card bg-light">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <strong>Cancelled By:</strong> 
                                        {{ $equipmentMaintenance->performedBy->first_name }} {{ $equipmentMaintenance->performedBy->last_name }}
                                    </div>
                                    <div class="col-md-6">
                                        <strong>Cancellation Date:</strong> 
                                        {{ $equipmentMaintenance->cancelled_at ? $equipmentMaintenance->cancelled_at->format('M d, Y \a\t h:i A') : 'N/A' }}
                                    </div>
                                </div>
                                @if($equipmentMaintenance->cancellation_reason)
                                <div class="mt-3">
                                    <strong>Cancellation Reason:</strong>
                                    <p class="mb-0 mt-1">{{ $equipmentMaintenance->cancellation_reason }}</p>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Actions -->
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('sc.equipment-monitoring.maintenance') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-1"></i> Back to Maintenance
                        </a>
                        <div class="d-flex gap-2">
                            <a href="{{ route('sc.equipment-monitoring.show-equipment', $equipmentMaintenance->monitoredEquipment) }}" class="btn btn-outline-info">
                                <i class="fas fa-cogs me-1"></i> View Equipment
                            </a>
                            @if($equipmentMaintenance->status === 'scheduled')
                                <button type="button" class="btn btn-outline-success" 
                                        data-bs-toggle="modal" data-bs-target="#completeModal">
                                    <i class="fas fa-check me-1"></i> Mark Complete
                                </button>
                                <button type="button" class="btn btn-outline-danger" 
                                        data-bs-toggle="modal" data-bs-target="#cancelModal">
                                    <i class="fas fa-times me-1"></i> Cancel
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Complete Maintenance Modal -->
@if($equipmentMaintenance->status === 'scheduled')
<div class="modal fade" id="completeModal" tabindex="-1" aria-labelledby="completeModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="completeModalLabel">Complete Maintenance</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="{{ route('sc.equipment-monitoring.maintenance') }}/{{ $equipmentMaintenance->id }}/complete">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <p>Mark maintenance as completed for:</p>
                    <div class="alert alert-info">
                        <strong>{{ $equipmentMaintenance->monitoredEquipment->equipment_name ?? 'N/A' }}</strong>
                    </div>
                    <div class="mb-3">
                        <label for="actual_duration_hours" class="form-label">Actual Duration (hours)</label>
                        <input type="number" name="actual_duration_hours" id="actual_duration_hours" 
                               class="form-control" step="0.5" min="0" max="1000"
                               value="{{ $equipmentMaintenance->estimated_duration_hours }}">
                    </div>
                    <div class="mb-3">
                        <label for="cost" class="form-label">Cost (Optional)</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" name="cost" id="cost" class="form-control" 
                                   step="0.01" min="0" max="999999.99"
                                   value="{{ $equipmentMaintenance->estimated_cost }}">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="completion_notes" class="form-label">Completion Notes</label>
                        <textarea name="completion_notes" id="completion_notes" class="form-control" rows="3" 
                                  placeholder="Add notes about the completed maintenance..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Mark as Completed</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Cancel Maintenance Modal -->
<div class="modal fade" id="cancelModal" tabindex="-1" aria-labelledby="cancelModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="cancelModalLabel">Cancel Maintenance</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="{{ route('sc.equipment-monitoring.maintenance') }}/{{ $equipmentMaintenance->id }}/cancel">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <p>Are you sure you want to cancel this maintenance?</p>
                    <div class="alert alert-warning">
                        <strong>{{ $equipmentMaintenance->monitoredEquipment->equipment_name ?? 'N/A' }}</strong>
                    </div>
                    <div class="mb-3">
                        <label for="cancellation_reason" class="form-label">Cancellation Reason</label>
                        <textarea name="cancellation_reason" id="cancellation_reason" class="form-control" rows="3" 
                                  placeholder="Provide a reason for cancellation..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Yes, Cancel Maintenance</button>
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