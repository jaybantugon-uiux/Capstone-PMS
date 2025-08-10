@extends('app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h2">My Equipment Maintenance</h1>
                    <p class="text-muted">Manage maintenance schedules for your equipment</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.equipment-monitoring.create-maintenance') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Schedule Maintenance
                    </a>
                    <a href="{{ route('admin.equipment-monitoring.my-dashboard') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Dashboard
                    </a>
                </div>
            </div>

            <!-- Filters -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" action="{{ route('admin.equipment-monitoring.my-maintenance') }}">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label for="status" class="form-label">Status</label>
                                <select name="status" id="status" class="form-control">
                                    <option value="">All Statuses</option>
                                    <option value="scheduled" {{ request('status') == 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                                    <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                    <option value="overdue" {{ request('status') == 'overdue' ? 'selected' : '' }}>Overdue</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="maintenance_type" class="form-label">Type</label>
                                <select name="maintenance_type" id="maintenance_type" class="form-control">
                                    <option value="">All Types</option>
                                    <option value="routine" {{ request('maintenance_type') == 'routine' ? 'selected' : '' }}>Routine</option>
                                    <option value="repair" {{ request('maintenance_type') == 'repair' ? 'selected' : '' }}>Repair</option>
                                    <option value="inspection" {{ request('maintenance_type') == 'inspection' ? 'selected' : '' }}>Inspection</option>
                                    <option value="calibration" {{ request('maintenance_type') == 'calibration' ? 'selected' : '' }}>Calibration</option>
                                    <option value="replacement" {{ request('maintenance_type') == 'replacement' ? 'selected' : '' }}>Replacement</option>
                                </select>
                            </div>
                            <div class="col-md-3 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary me-2">
                                    <i class="fas fa-filter me-1"></i>Filter
                                </button>
                                <a href="{{ route('admin.equipment-monitoring.my-maintenance') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-times me-1"></i>Clear
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Maintenance Table -->
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            Maintenance Schedule
                            @if($statusFilter || $typeFilter)
                                <small class="text-muted">(Filtered)</small>
                            @endif
                        </h5>
                        <div class="d-flex gap-2">
                            <span class="badge bg-secondary">{{ $maintenances->total() }} Total</span>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    @if($maintenances->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Equipment</th>
                                        <th>Type</th>
                                        <th>Scheduled</th>
                                        <th>Duration</th>
                                        <th>Priority</th>
                                        <th>Status</th>
                                        <th>Progress</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($maintenances as $maintenance)
                                        <tr class="{{ $maintenance->is_overdue ? 'table-warning' : '' }}">
                                            <td>
                                                <div>
                                                    <strong>{{ $maintenance->monitoredEquipment->equipment_name }}</strong>
                                                    @if($maintenance->monitoredEquipment->project)
                                                        <br><small class="text-muted">
                                                            <i class="fas fa-project-diagram me-1"></i>
                                                            {{ Str::limit($maintenance->monitoredEquipment->project->name, 20) }}
                                                        </small>
                                                    @else
                                                        <br><small class="text-muted">
                                                            <i class="fas fa-user me-1"></i>Personal Equipment
                                                        </small>
                                                    @endif
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-info">
                                                    {{ $maintenance->formatted_maintenance_type }}
                                                </span>
                                            </td>
                                            <td>
                                                <div>
                                                    {{ $maintenance->scheduled_date->format('M d, Y') }}
                                                    <br><small class="text-muted">{{ $maintenance->scheduled_date->format('g:i A') }}</small>
                                                </div>
                                                @if($maintenance->is_overdue)
                                                    <span class="badge bg-danger mt-1">Overdue</span>
                                                @elseif($maintenance->is_upcoming)
                                                    <span class="badge bg-warning mt-1">Due Soon</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div>
                                                    @if($maintenance->estimated_duration_hours)
                                                        <span class="text-muted">Est:</span> {{ $maintenance->estimated_duration_hours }}h
                                                    @else
                                                        <span class="text-muted">Not specified</span>
                                                    @endif
                                                    @if($maintenance->actual_duration_hours)
                                                        <br><span class="text-success">Act:</span> {{ $maintenance->actual_duration_hours }}h
                                                    @endif
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-{{ $maintenance->priority_badge_color }}">
                                                    {{ $maintenance->formatted_priority }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-{{ $maintenance->status_badge_color }}">
                                                    {{ $maintenance->formatted_status }}
                                                </span>
                                            </td>
                                            <td>
                                                @if($maintenance->status === 'completed')
                                                    <div class="text-success">
                                                        <i class="fas fa-check-circle me-1"></i>
                                                        {{ $maintenance->completed_date->format('M d, Y') }}
                                                    </div>
                                                @elseif($maintenance->status === 'in_progress')
                                                    <div class="text-info">
                                                        <i class="fas fa-cog fa-spin me-1"></i>
                                                        In Progress
                                                    </div>
                                                @elseif($maintenance->status === 'scheduled')
                                                    <div class="text-muted">
                                                        <i class="fas fa-clock me-1"></i>
                                                        {{ $maintenance->days_until_scheduled }} days
                                                    </div>
                                                @else
                                                    <div class="text-secondary">
                                                        <i class="fas fa-minus-circle me-1"></i>
                                                        {{ $maintenance->formatted_status }}
                                                    </div>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('admin.equipment-monitoring.show-my-maintenance', $maintenance) }}" 
                                                       class="btn btn-outline-primary btn-sm" 
                                                       title="View Details">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    @if($maintenance->status === 'scheduled')
                                                        <button class="btn btn-outline-success btn-sm complete-maintenance" 
                                                                data-maintenance-id="{{ $maintenance->id }}"
                                                                title="Mark as Completed">
                                                            <i class="fas fa-check"></i>
                                                        </button>
                                                        <button class="btn btn-outline-danger btn-sm cancel-maintenance" 
                                                                data-maintenance-id="{{ $maintenance->id }}"
                                                                title="Cancel">
                                                            <i class="fas fa-times"></i>
                                                        </button>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <div>
                                <small class="text-muted">
                                    Showing {{ $maintenances->firstItem() }} to {{ $maintenances->lastItem() }} of {{ $maintenances->total() }} results
                                </small>
                            </div>
                            <div>
                                {{ $maintenances->links() }}
                            </div>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-wrench fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No Maintenance Schedules Found</h5>
                            <p class="text-muted mb-3">
                                @if($statusFilter || $typeFilter)
                                    No maintenance schedules match your current filters.
                                @else
                                    You haven't scheduled any maintenance yet.
                                @endif
                            </p>
                            <div class="d-flex gap-2 justify-content-center">
                                <a href="{{ route('admin.equipment-monitoring.create-maintenance') }}" class="btn btn-primary">
                                    <i class="fas fa-plus me-1"></i>Schedule Maintenance
                                </a>
                                @if($statusFilter || $typeFilter)
                                    <a href="{{ route('admin.equipment-monitoring.my-maintenance') }}" class="btn btn-outline-secondary">
                                        <i class="fas fa-times me-1"></i>Clear Filters
                                    </a>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Summary Cards -->
            @if($maintenances->count() > 0)
                <div class="row mt-4">
                    <div class="col-md-2">
                        <div class="card bg-light">
                            <div class="card-body text-center">
                                <h5 class="text-warning">{{ $maintenances->where('status', 'scheduled')->count() }}</h5>
                                <small class="text-muted">Scheduled</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="card bg-light">
                            <div class="card-body text-center">
                                <h5 class="text-info">{{ $maintenances->where('status', 'in_progress')->count() }}</h5>
                                <small class="text-muted">In Progress</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="card bg-light">
                            <div class="card-body text-center">
                                <h5 class="text-success">{{ $maintenances->where('status', 'completed')->count() }}</h5>
                                <small class="text-muted">Completed</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="card bg-light">
                            <div class="card-body text-center">
                                <h5 class="text-danger">{{ $maintenances->where('is_overdue', true)->count() }}</h5>
                                <small class="text-muted">Overdue</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="card bg-light">
                            <div class="card-body text-center">
                                <h5 class="text-warning">{{ $maintenances->where('is_upcoming', true)->count() }}</h5>
                                <small class="text-muted">Due Soon</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="card bg-light">
                            <div class="card-body text-center">
                                <h5 class="text-secondary">{{ $maintenances->where('status', 'cancelled')->count() }}</h5>
                                <small class="text-muted">Cancelled</small>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
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
                        <input type="number" class="form-control" id="actual_duration" name="actual_duration" min="1" max="480">
                    </div>
                    <div class="mb-3">
                        <label for="cost" class="form-label">Cost (₱)</label>
                        <input type="number" class="form-control" id="cost" name="cost" min="0" step="0.01">
                    </div>
                    <div class="mb-3">
                        <label for="completion_notes" class="form-label">Completion Notes</label>
                        <textarea class="form-control" id="completion_notes" name="completion_notes" rows="3" placeholder="Describe what was done..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Complete Maintenance</button>
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
                        <label for="cancel_reason" class="form-label">Reason for Cancellation <span class="text-danger">*</span></label>
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

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    let currentMaintenanceId = null;

    // Complete maintenance handlers
    document.querySelectorAll('.complete-maintenance').forEach(function(button) {
        button.addEventListener('click', function() {
            currentMaintenanceId = this.dataset.maintenanceId;
            const modal = new bootstrap.Modal(document.getElementById('completeMaintenanceModal'));
            modal.show();
        });
    });

    // Cancel maintenance handlers
    document.querySelectorAll('.cancel-maintenance').forEach(function(button) {
        button.addEventListener('click', function() {
            currentMaintenanceId = this.dataset.maintenanceId;
            const modal = new bootstrap.Modal(document.getElementById('cancelMaintenanceModal'));
            modal.show();
        });
    });

    // Complete maintenance form submission
    document.getElementById('completeMaintenanceForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        
        fetch(`/admin/equipment-monitoring/maintenance/${currentMaintenanceId}/complete`, {
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
                alert('Error completing maintenance');
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
        
        fetch(`/admin/equipment-monitoring/maintenance/${currentMaintenanceId}/cancel`, {
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
                alert('Error cancelling maintenance');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error cancelling maintenance');
        });
    });
});
</script>
@endpush