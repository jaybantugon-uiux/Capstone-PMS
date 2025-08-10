@extends('app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h2">Maintenance Schedules</h1>
                <div class="d-flex gap-2">
                    <a href="{{ route('sc.equipment-monitoring.create-maintenance') }}" class="btn btn-success">
                        <i class="fas fa-plus me-1"></i> Schedule Maintenance
                    </a>
                    <a href="{{ route('sc.equipment-monitoring.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Back to Dashboard
                    </a>
                </div>
            </div>

            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <!-- Filters -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-filter me-2"></i>Filters
                    </h5>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('sc.equipment-monitoring.maintenance') }}" class="row g-3">
                        <div class="col-md-3">
                            <label for="status" class="form-label">Status</label>
                            <select name="status" id="status" class="form-select">
                                <option value="">All Statuses</option>
                                <option value="scheduled" {{ $statusFilter === 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                                <option value="in_progress" {{ $statusFilter === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                <option value="completed" {{ $statusFilter === 'completed' ? 'selected' : '' }}>Completed</option>
                                <option value="cancelled" {{ $statusFilter === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="maintenance_type" class="form-label">Maintenance Type</label>
                            <select name="maintenance_type" id="maintenance_type" class="form-select">
                                <option value="">All Types</option>
                                <option value="preventive" {{ request('maintenance_type') === 'preventive' ? 'selected' : '' }}>Preventive</option>
                                <option value="corrective" {{ request('maintenance_type') === 'corrective' ? 'selected' : '' }}>Corrective</option>
                                <option value="emergency" {{ request('maintenance_type') === 'emergency' ? 'selected' : '' }}>Emergency</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="priority" class="form-label">Priority</label>
                            <select name="priority" id="priority" class="form-select">
                                <option value="">All Priorities</option>
                                <option value="low" {{ request('priority') === 'low' ? 'selected' : '' }}>Low</option>
                                <option value="medium" {{ request('priority') === 'medium' ? 'selected' : '' }}>Medium</option>
                                <option value="high" {{ request('priority') === 'high' ? 'selected' : '' }}>High</option>
                                <option value="critical" {{ request('priority') === 'critical' ? 'selected' : '' }}>Critical</option>
                            </select>
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <div class="d-flex gap-2 w-100">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search me-1"></i> Filter
                                </button>
                                <a href="{{ route('sc.equipment-monitoring.maintenance') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-times me-1"></i> Clear
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Maintenance Table -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-tools me-2"></i>Maintenance Schedules
                    </h5>
                </div>
                <div class="card-body">
                    @if($maintenances->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Equipment</th>
                                        <th>Maintenance Type</th>
                                        <th>Scheduled Date</th>
                                        <th>Status</th>
                                        <th>Priority</th>
                                        <th>Duration</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($maintenances as $maintenance)
                                        <tr class="{{ $maintenance->is_overdue ? 'table-danger' : ($maintenance->is_upcoming ? 'table-warning' : '') }}">
                                            <td>
                                                <strong>{{ $maintenance->monitoredEquipment->equipment_name ?? 'N/A' }}</strong>
                                                @if($maintenance->is_overdue)
                                                    <span class="badge bg-danger ms-1">Overdue</span>
                                                @elseif($maintenance->is_upcoming)
                                                    <span class="badge bg-warning ms-1">Upcoming</span>
                                                @endif
                                                <br><small class="text-muted">{{ $maintenance->monitoredEquipment->project->name ?? 'Personal Equipment' }}</small>
                                            </td>
                                            <td>
                                                <span class="badge bg-{{ $maintenance->maintenance_type === 'preventive' ? 'info' : ($maintenance->maintenance_type === 'corrective' ? 'warning' : 'danger') }}">
                                                    {{ $maintenance->formatted_maintenance_type }}
                                                </span>
                                            </td>
                                            <td>
                                                @if($maintenance->scheduled_date)
                                                    {{ $maintenance->scheduled_date->format('M d, Y') }}
                                                    <br><small class="text-muted">{{ $maintenance->scheduled_date->format('h:i A') }}</small>
                                                @else
                                                    <span class="text-muted">Not scheduled</span>
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
                                                @if($maintenance->estimated_duration_hours)
                                                    <strong>{{ $maintenance->estimated_duration_hours }}</strong> hours
                                                @else
                                                    <span class="text-muted">Not specified</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <a href="{{ route('sc.equipment-monitoring.show-maintenance', $maintenance) }}" 
                                                       class="btn btn-outline-primary" title="View Details">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    @if($maintenance->status === 'scheduled')
                                                        <button type="button" class="btn btn-outline-success" 
                                                                data-bs-toggle="modal" data-bs-target="#completeModal"
                                                                data-maintenance-id="{{ $maintenance->id }}"
                                                                data-maintenance-name="{{ $maintenance->monitoredEquipment->equipment_name ?? 'N/A' }}"
                                                                title="Mark as Completed">
                                                            <i class="fas fa-check"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-outline-danger" 
                                                                data-bs-toggle="modal" data-bs-target="#cancelModal"
                                                                data-maintenance-id="{{ $maintenance->id }}"
                                                                data-maintenance-name="{{ $maintenance->monitoredEquipment->equipment_name ?? 'N/A' }}"
                                                                title="Cancel Maintenance">
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
                        <div class="d-flex justify-content-center mt-4">
                            {{ $maintenances->appends(request()->query())->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-tools fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No Maintenance Schedules Found</h5>
                            <p class="text-muted">You don't have any maintenance schedules yet.</p>
                            <a href="{{ route('sc.equipment-monitoring.create-maintenance') }}" class="btn btn-primary">
                                <i class="fas fa-plus me-1"></i> Schedule Maintenance
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Complete Maintenance Modal -->
<div class="modal fade" id="completeModal" tabindex="-1" aria-labelledby="completeModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="completeModalLabel">Complete Maintenance</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="completeForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <p>Mark maintenance as completed for:</p>
                    <div class="alert alert-info">
                        <strong id="maintenanceName"></strong>
                    </div>
                    <div class="mb-3">
                        <label for="actual_duration_hours" class="form-label">Actual Duration (hours)</label>
                        <input type="number" name="actual_duration_hours" id="actual_duration_hours" 
                               class="form-control" step="0.5" min="0" max="1000">
                    </div>
                    <div class="mb-3">
                        <label for="cost" class="form-label">Cost (Optional)</label>
                        <div class="input-group">
                            <span class="input-group-text">₱</span>
                            <input type="number" name="cost" id="cost" class="form-control" 
                                   step="0.01" min="0" max="999999.99">
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
            <form id="cancelForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <p>Are you sure you want to cancel this maintenance?</p>
                    <div class="alert alert-warning">
                        <strong id="cancelMaintenanceName"></strong>
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
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Complete Maintenance Modal
    const completeModal = document.getElementById('completeModal');
    if (completeModal) {
        completeModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const maintenanceId = button.getAttribute('data-maintenance-id');
            const maintenanceName = button.getAttribute('data-maintenance-name');
            
            document.getElementById('maintenanceName').textContent = maintenanceName;
            document.getElementById('completeForm').action = `/sc/equipment-monitoring/maintenance/${maintenanceId}/complete`;
        });
    }

    // Cancel Maintenance Modal
    const cancelModal = document.getElementById('cancelModal');
    if (cancelModal) {
        cancelModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const maintenanceId = button.getAttribute('data-maintenance-id');
            const maintenanceName = button.getAttribute('data-maintenance-name');
            
            document.getElementById('cancelMaintenanceName').textContent = maintenanceName;
            document.getElementById('cancelForm').action = `/sc/equipment-monitoring/maintenance/${maintenanceId}/cancel`;
        });
    }
});
</script>
@endpush

@push('styles')
<style>
.table-hover tbody tr:hover {
    background-color: rgba(0, 0, 0, 0.075);
}
.btn-group-sm .btn {
    padding: 0.25rem 0.5rem;
    font-size: 0.75rem;
}
@media (max-width: 768px) {
    .btn-group-sm {
        display: flex;
        flex-direction: column;
    }
    .btn-group-sm .btn {
        margin-bottom: 2px;
    }
}
</style>
@endpush 