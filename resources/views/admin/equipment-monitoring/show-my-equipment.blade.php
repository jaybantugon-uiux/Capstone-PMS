@extends('app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-10 offset-md-1">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h2">{{ $monitoredEquipment->equipment_name }}</h1>
                    <p class="text-muted">Equipment ID: #{{ $monitoredEquipment->id }}</p>
                </div>
                <div class="d-flex gap-2">
                    @if($monitoredEquipment->status === 'active')
                        <a href="{{ route('admin.equipment-monitoring.create-maintenance') }}?equipment={{ $monitoredEquipment->id }}" class="btn btn-warning">
                            <i class="fas fa-wrench me-1"></i>Schedule Maintenance
                        </a>
                    @endif
                    <a href="{{ route('admin.equipment-monitoring.my-equipment') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i>Back to Equipment
                    </a>
                </div>
            </div>

            <div class="row">
                <!-- Equipment Details -->
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Equipment Details</h5>
                        </div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <strong>Equipment Name:</strong>
                                    <p class="mb-0">{{ $monitoredEquipment->equipment_name }}</p>
                                </div>
                                <div class="col-md-3">
                                    <strong>Quantity:</strong>
                                    <p class="mb-0">{{ $monitoredEquipment->quantity }}</p>
                                </div>
                                <div class="col-md-3">
                                    <strong>Usage Type:</strong>
                                    <p class="mb-0">
                                        <span class="badge bg-{{ $monitoredEquipment->usage_type === 'personal' ? 'secondary' : 'info' }}">
                                            {{ $monitoredEquipment->formatted_usage_type }}
                                        </span>
                                    </p>
                                </div>
                            </div>

                            @if($monitoredEquipment->project)
                                <div class="mb-3">
                                    <strong>Project:</strong>
                                    <p class="mb-0">
                                        <a href="{{ route('projects.show', $monitoredEquipment->project) }}" class="text-decoration-none">
                                            {{ $monitoredEquipment->project->name }}
                                        </a>
                                    </p>
                                </div>
                            @endif

                            <div class="mb-3">
                                <strong>Description:</strong>
                                <p class="mb-0">{{ $monitoredEquipment->equipment_description }}</p>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <strong>Status:</strong>
                                    <p class="mb-0">
                                        <span class="badge bg-{{ $monitoredEquipment->status_badge_color }}">
                                            {{ $monitoredEquipment->formatted_status }}
                                        </span>
                                    </p>
                                </div>
                                <div class="col-md-6">
                                    <strong>Availability:</strong>
                                    <p class="mb-0">
                                        @if($monitoredEquipment->status === 'active')
                                            <div class="dropdown d-inline">
                                                <button class="btn btn-sm btn-outline-{{ $monitoredEquipment->availability_badge_color }} dropdown-toggle" 
                                                        type="button" 
                                                        data-bs-toggle="dropdown">
                                                    {{ $monitoredEquipment->formatted_availability_status }}
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li><a class="dropdown-item availability-update" 
                                                           href="#" 
                                                           data-equipment-id="{{ $monitoredEquipment->id }}" 
                                                           data-status="available">Available</a></li>
                                                    <li><a class="dropdown-item availability-update" 
                                                           href="#" 
                                                           data-equipment-id="{{ $monitoredEquipment->id }}" 
                                                           data-status="in_use">In Use</a></li>
                                                    <li><a class="dropdown-item availability-update" 
                                                           href="#" 
                                                           data-equipment-id="{{ $monitoredEquipment->id }}" 
                                                           data-status="maintenance">Maintenance</a></li>
                                                    <li><a class="dropdown-item availability-update" 
                                                           href="#" 
                                                           data-equipment-id="{{ $monitoredEquipment->id }}" 
                                                           data-status="out_of_order">Out of Order</a></li>
                                                </ul>
                                            </div>
                                        @else
                                            <span class="badge bg-{{ $monitoredEquipment->availability_badge_color }}">
                                                {{ $monitoredEquipment->formatted_availability_status }}
                                            </span>
                                        @endif
                                    </p>
                                </div>
                            </div>

                            @if($monitoredEquipment->location || $monitoredEquipment->serial_number)
                                <div class="row mb-3">
                                    @if($monitoredEquipment->location)
                                        <div class="col-md-6">
                                            <strong>Location:</strong>
                                            <p class="mb-0"><i class="fas fa-map-marker-alt me-1"></i>{{ $monitoredEquipment->location }}</p>
                                        </div>
                                    @endif
                                    @if($monitoredEquipment->serial_number)
                                        <div class="col-md-6">
                                            <strong>Serial Number:</strong>
                                            <p class="mb-0">{{ $monitoredEquipment->serial_number }}</p>
                                        </div>
                                    @endif
                                </div>
                            @endif

                            @if($monitoredEquipment->purchase_date || $monitoredEquipment->warranty_expiry)
                                <div class="row mb-3">
                                    @if($monitoredEquipment->purchase_date)
                                        <div class="col-md-6">
                                            <strong>Purchase Date:</strong>
                                            <p class="mb-0">{{ $monitoredEquipment->purchase_date->format('M d, Y') }}</p>
                                        </div>
                                    @endif
                                    @if($monitoredEquipment->warranty_expiry)
                                        <div class="col-md-6">
                                            <strong>Warranty Expiry:</strong>
                                            <p class="mb-0">
                                                {{ $monitoredEquipment->warranty_expiry->format('M d, Y') }}
                                                @if($monitoredEquipment->warranty_expiry < now())
                                                    <span class="badge bg-warning ms-1">Expired</span>
                                                @elseif($monitoredEquipment->warranty_expiry <= now()->addDays(30))
                                                    <span class="badge bg-warning ms-1">Expires Soon</span>
                                                @endif
                                            </p>
                                        </div>
                                    @endif
                                </div>
                            @endif

                            @if($monitoredEquipment->notes)
                                <div class="mb-3">
                                    <strong>Notes:</strong>
                                    <p class="mb-0">{{ $monitoredEquipment->notes }}</p>
                                </div>
                            @endif

                            @if($monitoredEquipment->last_status_update)
                                <div class="mb-0">
                                    <strong>Last Status Update:</strong>
                                    <p class="mb-0">
                                        {{ $monitoredEquipment->last_status_update->format('M d, Y g:i A') }}
                                        <small class="text-muted">({{ $monitoredEquipment->last_status_update->diffForHumans() }})</small>
                                    </p>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Maintenance History -->
                    <div class="card mt-4">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">Maintenance History</h5>
                            @if($monitoredEquipment->status === 'active')
                                <a href="{{ route('admin.equipment-monitoring.create-maintenance') }}?equipment={{ $monitoredEquipment->id }}" class="btn btn-outline-warning btn-sm">
                                    <i class="fas fa-plus me-1"></i>Schedule New
                                </a>
                            @endif
                        </div>
                        <div class="card-body">
                            @if($monitoredEquipment->maintenanceSchedules->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Type</th>
                                                <th>Scheduled</th>
                                                <th>Status</th>
                                                <th>Priority</th>
                                                <th>Duration</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($monitoredEquipment->maintenanceSchedules->take(10) as $maintenance)
                                                <tr class="{{ $maintenance->is_overdue ? 'table-warning' : '' }}">
                                                    <td>
                                                        <span class="badge bg-info">
                                                            {{ $maintenance->formatted_maintenance_type }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        {{ $maintenance->scheduled_date->format('M d, Y') }}
                                                        @if($maintenance->is_overdue)
                                                            <br><span class="badge bg-danger">Overdue</span>
                                                        @elseif($maintenance->is_upcoming)
                                                            <br><span class="badge bg-warning">Due Soon</span>
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
                                                        @if($maintenance->actual_duration_hours)
                                                            <span class="text-success">{{ $maintenance->actual_duration_hours }}h</span>
                                                        @elseif($maintenance->estimated_duration_hours)
                                                            <span class="text-muted">~{{ $maintenance->estimated_duration_hours }}h</span>
                                                        @else
                                                            <span class="text-muted">-</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <a href="{{ route('admin.equipment-monitoring.show-my-maintenance', $maintenance) }}" 
                                                           class="btn btn-outline-primary btn-sm" 
                                                           title="View Details">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                @if($monitoredEquipment->maintenanceSchedules->count() > 10)
                                    <div class="text-end">
                                        <a href="{{ route('admin.equipment-monitoring.my-maintenance') }}" class="btn btn-outline-primary btn-sm">
                                            View All Maintenance
                                        </a>
                                    </div>
                                @endif
                            @else
                                <div class="text-center py-3">
                                    <i class="fas fa-wrench fa-2x text-muted mb-2"></i>
                                    <p class="text-muted mb-2">No maintenance scheduled yet</p>
                                    @if($monitoredEquipment->status === 'active')
                                        <a href="{{ route('admin.equipment-monitoring.create-maintenance') }}?equipment={{ $monitoredEquipment->id }}" class="btn btn-warning btn-sm">
                                            <i class="fas fa-plus me-1"></i>Schedule First Maintenance
                                        </a>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Request Information -->
                    @if($monitoredEquipment->equipmentRequest)
                        <div class="card mt-4">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Original Request</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <strong>Request Date:</strong>
                                        <p class="mb-2">{{ $monitoredEquipment->equipmentRequest->created_at->format('M d, Y') }}</p>

                                        <strong>Urgency Level:</strong>
                                        <p class="mb-2">
                                            <span class="badge bg-{{ $monitoredEquipment->equipmentRequest->urgency_badge_color }}">
                                                {{ $monitoredEquipment->equipmentRequest->formatted_urgency }}
                                            </span>
                                        </p>
                                    </div>
                                    <div class="col-md-6">
                                        <strong>Request Status:</strong>
                                        <p class="mb-2">
                                            <span class="badge bg-{{ $monitoredEquipment->equipmentRequest->status_badge_color }}">
                                                {{ $monitoredEquipment->equipmentRequest->formatted_status }}
                                            </span>
                                        </p>

                                        @if($monitoredEquipment->equipmentRequest->estimated_cost)
                                            <strong>Estimated Cost:</strong>
                                            <p class="mb-2">₱{{ number_format($monitoredEquipment->equipmentRequest->estimated_cost, 2) }}</p>
                                        @endif
                                    </div>
                                </div>

                                <strong>Justification:</strong>
                                <p class="mb-0">{{ $monitoredEquipment->equipmentRequest->justification }}</p>

                                <div class="mt-3">
                                    <a href="{{ route('admin.equipment-monitoring.show-my-request', $monitoredEquipment->equipmentRequest) }}" class="btn btn-outline-primary btn-sm">
                                        <i class="fas fa-eye me-1"></i>View Full Request
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Equipment Status & Actions -->
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Equipment Status</h5>
                        </div>
                        <div class="card-body">
                            <div class="text-center mb-3">
                                <span class="badge bg-{{ $monitoredEquipment->status_badge_color }} fs-6">
                                    {{ $monitoredEquipment->formatted_status }}
                                </span>
                            </div>

                            <div class="text-center mb-3">
                                <h6>Current Availability</h6>
                                <span class="badge bg-{{ $monitoredEquipment->availability_badge_color }} fs-6">
                                    {{ $monitoredEquipment->formatted_availability_status }}
                                </span>
                            </div>

                            <!-- Maintenance Status -->
                            @if($monitoredEquipment->next_maintenance_date || $monitoredEquipment->last_maintenance_date)
                                <hr>
                                <h6>Maintenance Status</h6>
                                @if($monitoredEquipment->last_maintenance_date)
                                    <div class="mb-2">
                                        <small class="text-muted">Last Maintenance:</small>
                                        <br>{{ $monitoredEquipment->last_maintenance_date->format('M d, Y') }}
                                        <small class="text-muted">({{ $monitoredEquipment->last_maintenance_date->diffForHumans() }})</small>
                                    </div>
                                @endif
                                @if($monitoredEquipment->next_maintenance_date)
                                    <div class="mb-2">
                                        <small class="text-muted">Next Maintenance:</small>
                                        <br>{{ $monitoredEquipment->next_maintenance_date->format('M d, Y') }}
                                        @if($monitoredEquipment->maintenance_overdue)
                                            <span class="badge bg-danger ms-1">Overdue</span>
                                        @elseif($monitoredEquipment->needs_maintenance)
                                            <span class="badge bg-warning ms-1">Due Soon</span>
                                        @endif
                                    </div>
                                @endif
                            @endif

                            <!-- Quick Actions -->
                            <hr>
                            <h6>Quick Actions</h6>
                            <div class="d-grid gap-2">
                                @if($monitoredEquipment->status === 'active')
                                    <a href="{{ route('admin.equipment-monitoring.create-maintenance') }}?equipment={{ $monitoredEquipment->id }}" class="btn btn-warning btn-sm">
                                        <i class="fas fa-wrench me-1"></i>Schedule Maintenance
                                    </a>
                                @endif
                                
                                <a href="{{ route('admin.equipment-monitoring.my-equipment') }}" class="btn btn-outline-secondary btn-sm">
                                    <i class="fas fa-cubes me-1"></i>All Equipment
                                </a>
                                
                                @if($monitoredEquipment->equipmentRequest)
                                    <a href="{{ route('admin.equipment-monitoring.show-my-request', $monitoredEquipment->equipmentRequest) }}" class="btn btn-outline-info btn-sm">
                                        <i class="fas fa-clipboard-list me-1"></i>View Request
                                    </a>
                                @endif
                                
                                <a href="{{ route('admin.equipment-monitoring.my-dashboard') }}" class="btn btn-outline-primary btn-sm">
                                    <i class="fas fa-home me-1"></i>Dashboard
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Equipment Stats -->
                    <div class="card mt-3">
                        <div class="card-header">
                            <h6 class="card-title mb-0">Equipment Stats</h6>
                        </div>
                        <div class="card-body">
                            <div class="row text-center">
                                <div class="col-6">
                                    <div class="border-end">
                                        <h6 class="text-muted mb-1">Days Active</h6>
                                        <h5 class="mb-0">{{ $monitoredEquipment->created_at->diffInDays(now()) }}</h5>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <h6 class="text-muted mb-1">Maintenance</h6>
                                    <h5 class="mb-0">
                                        <span class="badge bg-{{ $monitoredEquipment->maintenanceSchedules->where('status', 'completed')->count() > 0 ? 'success' : 'secondary' }}">
                                            {{ $monitoredEquipment->maintenanceSchedules->where('status', 'completed')->count() }}
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