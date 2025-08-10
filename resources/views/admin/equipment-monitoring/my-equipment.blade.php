@extends('app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h2">My Equipment</h1>
                    <p class="text-muted">Manage your personal and project equipment</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.equipment-monitoring.create-request') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Request Equipment
                    </a>
                    <a href="{{ route('admin.equipment-monitoring.create-maintenance') }}" class="btn btn-warning">
                        <i class="fas fa-wrench"></i> Schedule Maintenance
                    </a>
                    <a href="{{ route('admin.equipment-monitoring.my-dashboard') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Dashboard
                    </a>
                </div>
            </div>

            <!-- Filters -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" action="{{ route('admin.equipment-monitoring.my-equipment') }}">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label for="status" class="form-label">Status</label>
                                <select name="status" id="status" class="form-control">
                                    <option value="">All Statuses</option>
                                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="pending_approval" {{ request('status') == 'pending_approval' ? 'selected' : '' }}>Pending Approval</option>
                                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                    <option value="maintenance" {{ request('status') == 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="usage_type" class="form-label">Usage Type</label>
                                <select name="usage_type" id="usage_type" class="form-control">
                                    <option value="">All Types</option>
                                    <option value="personal" {{ request('usage_type') == 'personal' ? 'selected' : '' }}>Personal</option>
                                    <option value="project_site" {{ request('usage_type') == 'project_site' ? 'selected' : '' }}>Project Site</option>
                                </select>
                            </div>
                            <div class="col-md-3 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary me-2">
                                    <i class="fas fa-filter me-1"></i>Filter
                                </button>
                                <a href="{{ route('admin.equipment-monitoring.my-equipment') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-times me-1"></i>Clear
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Equipment Table -->
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            My Equipment
                            @if($statusFilter || $typeFilter)
                                <small class="text-muted">(Filtered)</small>
                            @endif
                        </h5>
                        <div class="d-flex gap-2">
                            <span class="badge bg-secondary">{{ $equipment->total() }} Total</span>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    @if($equipment->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Equipment</th>
                                        <th>Type</th>
                                        <th>Project</th>
                                        <th>Status</th>
                                        <th>Availability</th>
                                        <th>Maintenance</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($equipment as $item)
                                        <tr class="{{ $item->needs_maintenance ? 'table-warning' : '' }}">
                                            <td>
                                                <div>
                                                    <strong>{{ $item->equipment_name }}</strong>
                                                    <br>
                                                    <small class="text-muted">
                                                        Qty: {{ $item->quantity }}
                                                        @if($item->serial_number)
                                                            • SN: {{ $item->serial_number }}
                                                        @endif
                                                    </small>
                                                    @if($item->location)
                                                        <br><small class="text-muted"><i class="fas fa-map-marker-alt me-1"></i>{{ $item->location }}</small>
                                                    @endif
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-{{ $item->usage_type === 'personal' ? 'secondary' : 'info' }}">
                                                    {{ $item->formatted_usage_type }}
                                                </span>
                                            </td>
                                            <td>
                                                @if($item->project)
                                                    <a href="{{ route('projects.show', $item->project) }}" class="text-decoration-none">
                                                        {{ Str::limit($item->project->name, 25) }}
                                                    </a>
                                                @else
                                                    <span class="text-muted">Personal Use</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge bg-{{ $item->status_badge_color }}">
                                                    {{ $item->formatted_status }}
                                                </span>
                                            </td>
                                            <td>
                                                @if($item->status === 'active')
                                                    <div class="dropdown">
                                                        <button class="btn btn-sm btn-outline-{{ $item->availability_badge_color }} dropdown-toggle" 
                                                                type="button" 
                                                                data-bs-toggle="dropdown">
                                                            {{ $item->formatted_availability_status }}
                                                        </button>
                                                        <ul class="dropdown-menu">
                                                            <li><a class="dropdown-item availability-update" 
                                                                   href="#" 
                                                                   data-equipment-id="{{ $item->id }}" 
                                                                   data-status="available">Available</a></li>
                                                            <li><a class="dropdown-item availability-update" 
                                                                   href="#" 
                                                                   data-equipment-id="{{ $item->id }}" 
                                                                   data-status="in_use">In Use</a></li>
                                                            <li><a class="dropdown-item availability-update" 
                                                                   href="#" 
                                                                   data-equipment-id="{{ $item->id }}" 
                                                                   data-status="maintenance">Maintenance</a></li>
                                                            <li><a class="dropdown-item availability-update" 
                                                                   href="#" 
                                                                   data-equipment-id="{{ $item->id }}" 
                                                                   data-status="out_of_order">Out of Order</a></li>
                                                        </ul>
                                                    </div>
                                                @else
                                                    <span class="badge bg-{{ $item->availability_badge_color }}">
                                                        {{ $item->formatted_availability_status }}
                                                    </span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($item->next_maintenance_date)
                                                    <div>
                                                        <small class="text-muted">Next:</small>
                                                        <br>{{ $item->next_maintenance_date->format('M d, Y') }}
                                                        @if($item->maintenance_overdue)
                                                            <span class="badge bg-danger ms-1">Overdue</span>
                                                        @elseif($item->needs_maintenance)
                                                            <span class="badge bg-warning ms-1">Due Soon</span>
                                                        @endif
                                                    </div>
                                                @else
                                                    <span class="text-muted">Not scheduled</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('admin.equipment-monitoring.show-my-equipment', $item) }}" 
                                                       class="btn btn-outline-primary btn-sm" 
                                                       title="View Details">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    @if($item->status === 'active')
                                                        <a href="{{ route('admin.equipment-monitoring.create-maintenance') }}?equipment={{ $item->id }}" 
                                                           class="btn btn-outline-warning btn-sm" 
                                                           title="Schedule Maintenance">
                                                            <i class="fas fa-wrench"></i>
                                                        </a>
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
                                    Showing {{ $equipment->firstItem() }} to {{ $equipment->lastItem() }} of {{ $equipment->total() }} results
                                </small>
                            </div>
                            <div>
                                {{ $equipment->links() }}
                            </div>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-cubes fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No Equipment Found</h5>
                            <p class="text-muted mb-3">
                                @if($statusFilter || $typeFilter)
                                    No equipment matches your current filters.
                                @else
                                    You don't have any equipment yet.
                                @endif
                            </p>
                            <div class="d-flex gap-2 justify-content-center">
                                <a href="{{ route('admin.equipment-monitoring.create-request') }}" class="btn btn-primary">
                                    <i class="fas fa-plus me-1"></i>Request Equipment
                                </a>
                                @if($statusFilter || $typeFilter)
                                    <a href="{{ route('admin.equipment-monitoring.my-equipment') }}" class="btn btn-outline-secondary">
                                        <i class="fas fa-times me-1"></i>Clear Filters
                                    </a>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Summary Cards -->
            @if($equipment->count() > 0)
                <div class="row mt-4">
                    <div class="col-md-2">
                        <div class="card bg-light">
                            <div class="card-body text-center">
                                <h5 class="text-success">{{ $equipment->where('status', 'active')->count() }}</h5>
                                <small class="text-muted">Active</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="card bg-light">
                            <div class="card-body text-center">
                                <h5 class="text-primary">{{ $equipment->where('availability_status', 'available')->count() }}</h5>
                                <small class="text-muted">Available</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="card bg-light">
                            <div class="card-body text-center">
                                <h5 class="text-info">{{ $equipment->where('availability_status', 'in_use')->count() }}</h5>
                                <small class="text-muted">In Use</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="card bg-light">
                            <div class="card-body text-center">
                                <h5 class="text-warning">{{ $equipment->where('availability_status', 'maintenance')->count() }}</h5>
                                <small class="text-muted">Maintenance</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="card bg-light">
                            <div class="card-body text-center">
                                <h5 class="text-danger">{{ $equipment->where('availability_status', 'out_of_order')->count() }}</h5>
                                <small class="text-muted">Out of Order</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="card bg-light">
                            <div class="card-body text-center">
                                <h5 class="text-warning">{{ $equipment->where('needs_maintenance', true)->count() }}</h5>
                                <small class="text-muted">Needs Maintenance</small>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle availability status updates
    document.querySelectorAll('.availability-update').forEach(function(link) {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            
            const equipmentId = this.dataset.equipmentId;
            const newStatus = this.dataset.status;
            
            if (!confirm('Are you sure you want to update this equipment\'s availability status?')) {
                return;
            }
            
            fetch(`/admin/equipment-monitoring/my-equipment/${equipmentId}/availability`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    availability_status: newStatus
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Error updating availability status');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error updating availability status');
            });
        });
    });
});
</script>
@endpush