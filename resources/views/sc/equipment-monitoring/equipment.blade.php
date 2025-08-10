@extends('app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h2">My Equipment</h1>
                <div class="d-flex gap-2">
                    <a href="{{ route('sc.equipment-monitoring.create-request') }}" class="btn btn-success">
                        <i class="fas fa-plus me-1"></i> Request Equipment
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
                    <form method="GET" action="{{ route('sc.equipment-monitoring.equipment') }}" class="row g-3">
                        <div class="col-md-3">
                            <label for="status" class="form-label">Status</label>
                            <select name="status" id="status" class="form-select">
                                <option value="">All Statuses</option>
                                <option value="active" {{ $statusFilter === 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ $statusFilter === 'inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="usage_type" class="form-label">Usage Type</label>
                            <select name="usage_type" id="usage_type" class="form-select">
                                <option value="">All Types</option>
                                <option value="personal" {{ $typeFilter === 'personal' ? 'selected' : '' }}>Personal Use</option>
                                <option value="project_site" {{ $typeFilter === 'project_site' ? 'selected' : '' }}>Project Site</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="availability" class="form-label">Availability</label>
                            <select name="availability" id="availability" class="form-select">
                                <option value="">All Availability</option>
                                <option value="available" {{ request('availability') === 'available' ? 'selected' : '' }}>Available</option>
                                <option value="in_use" {{ request('availability') === 'in_use' ? 'selected' : '' }}>In Use</option>
                                <option value="maintenance" {{ request('availability') === 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                                <option value="out_of_order" {{ request('availability') === 'out_of_order' ? 'selected' : '' }}>Out of Order</option>
                            </select>
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <div class="d-flex gap-2 w-100">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search me-1"></i> Filter
                                </button>
                                <a href="{{ route('sc.equipment-monitoring.equipment') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-times me-1"></i> Clear
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Equipment Table -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-cogs me-2"></i>Equipment List
                    </h5>
                </div>
                <div class="card-body">
                    @if($equipment->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Equipment Name</th>
                                        <th>Usage Type</th>
                                        <th>Project</th>
                                        <th>Quantity</th>
                                        <th>Status</th>
                                        <th>Availability</th>
                                        <th>Next Maintenance</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($equipment as $item)
                                        <tr class="{{ $item->availability_status === 'out_of_order' ? 'table-danger' : ($item->availability_status === 'maintenance' ? 'table-warning' : '') }}">
                                            <td>
                                                <strong>{{ $item->equipment_name }}</strong>
                                                @if($item->next_maintenance_date && $item->next_maintenance_date <= now()->addDays(7))
                                                    <span class="badge bg-warning ms-1">Maintenance Due</span>
                                                @endif
                                                <br><small class="text-muted">{{ Str::limit($item->equipment_description, 50) }}</small>
                                            </td>
                                            <td>
                                                <span class="badge bg-{{ $item->usage_type === 'personal' ? 'info' : 'primary' }}">
                                                    {{ $item->usage_type === 'personal' ? 'Personal Use' : 'Project Site' }}
                                                </span>
                                            </td>
                                            <td>
                                                @if($item->project)
                                                    <a href="{{ route('projects.show', $item->project) }}" class="text-decoration-none">
                                                        {{ $item->project->name }}
                                                    </a>
                                                @else
                                                    <span class="text-muted">Personal Equipment</span>
                                                @endif
                                            </td>
                                            <td>
                                                <strong>{{ $item->quantity }}</strong> units
                                            </td>
                                            <td>
                                                <span class="badge bg-{{ $item->status === 'active' ? 'success' : 'secondary' }}">
                                                    {{ ucfirst($item->status) }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-{{ $item->availability_status === 'available' ? 'success' : ($item->availability_status === 'in_use' ? 'warning' : 'danger') }}">
                                                    {{ ucfirst(str_replace('_', ' ', $item->availability_status)) }}
                                                </span>
                                            </td>
                                            <td>
                                                @if($item->next_maintenance_date)
                                                    @if($item->next_maintenance_date->isPast())
                                                        <span class="text-danger">
                                                            <i class="fas fa-exclamation-triangle me-1"></i>Overdue
                                                        </span>
                                                    @elseif($item->next_maintenance_date <= now()->addDays(7))
                                                        <span class="text-warning">
                                                            <i class="fas fa-clock me-1"></i>{{ $item->next_maintenance_date->format('M d, Y') }}
                                                        </span>
                                                    @else
                                                        <span class="text-muted">{{ $item->next_maintenance_date->format('M d, Y') }}</span>
                                                    @endif
                                                @else
                                                    <span class="text-muted">Not scheduled</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <a href="{{ route('sc.equipment-monitoring.show-equipment', $item) }}" 
                                                       class="btn btn-outline-primary" title="View Details">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    @if($item->status === 'active')
                                                        <button type="button" class="btn btn-outline-warning" 
                                                                data-bs-toggle="modal" data-bs-target="#availabilityModal"
                                                                data-equipment-id="{{ $item->id }}"
                                                                data-equipment-name="{{ $item->equipment_name }}"
                                                                data-current-availability="{{ $item->availability_status }}"
                                                                title="Update Availability">
                                                            <i class="fas fa-edit"></i>
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
                            {{ $equipment->appends(request()->query())->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-cogs fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No Equipment Found</h5>
                            <p class="text-muted">You don't have any equipment assigned yet.</p>
                            <a href="{{ route('sc.equipment-monitoring.create-request') }}" class="btn btn-primary">
                                <i class="fas fa-plus me-1"></i> Request Equipment
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Update Availability Modal -->
<div class="modal fade" id="availabilityModal" tabindex="-1" aria-labelledby="availabilityModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="availabilityModalLabel">Update Equipment Availability</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="availabilityForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <p>Update the availability status for:</p>
                    <div class="alert alert-info">
                        <strong id="equipmentName"></strong>
                    </div>
                    <div class="mb-3">
                        <label for="availability_status" class="form-label">Availability Status</label>
                        <select name="availability_status" id="availability_status" class="form-select" required>
                            <option value="available">Available</option>
                            <option value="in_use">In Use</option>
                            <option value="maintenance">Maintenance</option>
                            <option value="out_of_order">Out of Order</option>
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
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Update Availability Modal
    const availabilityModal = document.getElementById('availabilityModal');
    if (availabilityModal) {
        availabilityModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const equipmentId = button.getAttribute('data-equipment-id');
            const equipmentName = button.getAttribute('data-equipment-name');
            const currentAvailability = button.getAttribute('data-current-availability');
            
            document.getElementById('equipmentName').textContent = equipmentName;
            document.getElementById('availability_status').value = currentAvailability;
            document.getElementById('availabilityForm').action = `/sc/equipment-monitoring/equipment/${equipmentId}/availability`;
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