
@extends('app')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('pm.equipment-monitoring.index') }}">Equipment Monitoring</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('pm.equipment-monitoring.equipment-list') }}">Equipment</a></li>
                    <li class="breadcrumb-item active">{{ $monitoredEquipment->equipment_name }}</li>
                </ol>
            </nav>
            <h1>Equipment Details</h1>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('pm.equipment-monitoring.equipment-list') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i>Back to Equipment List
            </a>
            @if($monitoredEquipment->project)
                <a href="{{ route('projects.show', $monitoredEquipment->project) }}" class="btn btn-outline-primary">
                    <i class="fas fa-project-diagram me-1"></i>View Project
                </a>
            @endif
        </div>
    </div>

    <div class="row">
        <!-- Equipment Information -->
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-tools me-2"></i>Equipment Information
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h4>{{ $monitoredEquipment->equipment_name }}</h4>
                            <p class="text-muted mb-3">{{ $monitoredEquipment->equipment_description }}</p>
                            
                            <div class="mb-3">
                                <strong>Usage Type:</strong>
                                <span class="badge bg-info ms-2">{{ $monitoredEquipment->formatted_usage_type }}</span>
                            </div>

                            <div class="mb-3">
                                <strong>Quantity:</strong>
                                <span class="ms-2">{{ $monitoredEquipment->quantity }}</span>
                            </div>

                            @if($monitoredEquipment->location)
                                <div class="mb-3">
                                    <strong>Location:</strong>
                                    <span class="ms-2">{{ $monitoredEquipment->location }}</span>
                                </div>
                            @endif

                            @if($monitoredEquipment->serial_number)
                                <div class="mb-3">
                                    <strong>Serial Number:</strong>
                                    <span class="ms-2">{{ $monitoredEquipment->serial_number }}</span>
                                </div>
                            @endif
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <strong>Status:</strong>
                                <span class="badge bg-{{ $monitoredEquipment->status_badge_color }} ms-2">
                                    {{ $monitoredEquipment->formatted_status }}
                                </span>
                            </div>

                            <div class="mb-3">
                                <strong>Availability:</strong>
                                <span class="badge bg-{{ $monitoredEquipment->availability_badge_color }} ms-2">
                                    {{ $monitoredEquipment->formatted_availability_status }}
                                </span>
                            </div>

                            <div class="mb-3">
                                <strong>Managed By:</strong>
                                <div class="ms-2">
                                    {{ $monitoredEquipment->user->first_name }} {{ $monitoredEquipment->user->last_name }}
                                    <br>
                                    <small class="text-muted">{{ $monitoredEquipment->user->email }}</small>
                                </div>
                            </div>

                            @if($monitoredEquipment->project)
                                <div class="mb-3">
                                    <strong>Project:</strong>
                                    <div class="ms-2">
                                        <a href="{{ route('projects.show', $monitoredEquipment->project) }}">
                                            {{ $monitoredEquipment->project->name }}
                                        </a>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    @if($monitoredEquipment->notes)
                        <div class="mt-4 pt-3 border-top">
                            <strong>Notes:</strong>
                            <p class="mt-2 mb-0">{{ $monitoredEquipment->notes }}</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Equipment Request Information -->
            @if($monitoredEquipment->equipmentRequest)
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-clipboard-list me-2"></i>Original Request Information
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <strong>Request Date:</strong>
                                    <span class="ms-2">{{ $monitoredEquipment->equipmentRequest->created_at->format('M d, Y g:i A') }}</span>
                                </div>

                                <div class="mb-3">
                                    <strong>Urgency Level:</strong>
                                    <span class="badge bg-{{ $monitoredEquipment->equipmentRequest->urgency_badge_color }} ms-2">
                                        {{ $monitoredEquipment->equipmentRequest->formatted_urgency }}
                                    </span>
                                </div>

                                @if($monitoredEquipment->equipmentRequest->estimated_cost)
                                    <div class="mb-3">
                                        <strong>Estimated Cost:</strong>
                                        <span class="ms-2">₱{{ number_format($monitoredEquipment->equipmentRequest->estimated_cost, 2) }}</span>
                                    </div>
                                @endif
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <strong>Request Status:</strong>
                                    <span class="badge bg-{{ $monitoredEquipment->equipmentRequest->status_badge_color }} ms-2">
                                        {{ $monitoredEquipment->equipmentRequest->formatted_status }}
                                    </span>
                                </div>

                                @if($monitoredEquipment->equipmentRequest->approved_at)
                                    <div class="mb-3">
                                        <strong>Approved Date:</strong>
                                        <span class="ms-2">{{ $monitoredEquipment->equipmentRequest->approved_at->format('M d, Y g:i A') }}</span>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="mt-3">
                            <strong>Justification:</strong>
                            <p class="mt-2 mb-0">{{ $monitoredEquipment->equipmentRequest->justification }}</p>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Maintenance History -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-history me-2"></i>Maintenance History
                    </h5>
                    <a href="{{ route('pm.equipment-monitoring.maintenance-list', ['equipment_id' => $monitoredEquipment->id]) }}" 
                       class="btn btn-outline-info btn-sm">
                        <i class="fas fa-calendar me-1"></i>View All
                    </a>
                </div>
                <div class="card-body">
                    @if($monitoredEquipment->maintenanceSchedules->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
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
                                    @foreach($monitoredEquipment->maintenanceSchedules->take(5) as $maintenance)
                                        <tr>
                                            <td>{{ $maintenance->formatted_maintenance_type }}</td>
                                            <td>{{ $maintenance->scheduled_date->format('M d, Y') }}</td>
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
                                                <a href="{{ route('pm.equipment-monitoring.show-maintenance', $maintenance) }}" 
                                                   class="btn btn-outline-primary btn-sm">
                                                    <i class="fas fa-eye me-1"></i>View
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-wrench fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No maintenance records found</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-md-4">
            <!-- Equipment Status Summary -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-info-circle me-2"></i>Status Summary
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span>Equipment Status:</span>
                            <span class="badge bg-{{ $monitoredEquipment->status_badge_color }}">
                                {{ $monitoredEquipment->formatted_status }}
                            </span>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span>Availability:</span>
                            <span class="badge bg-{{ $monitoredEquipment->availability_badge_color }}">
                                {{ $monitoredEquipment->formatted_availability_status }}
                            </span>
                        </div>
                    </div>

                    @if($monitoredEquipment->last_maintenance_date)
                        <div class="mb-3">
                            <div class="d-flex justify-content-between">
                                <span>Last Maintenance:</span>
                                <span>{{ $monitoredEquipment->last_maintenance_date->format('M d, Y') }}</span>
                            </div>
                        </div>
                    @endif

                    @if($monitoredEquipment->next_maintenance_date)
                        <div class="mb-3">
                            <div class="d-flex justify-content-between">
                                <span>Next Maintenance:</span>
                                <span class="{{ $monitoredEquipment->maintenance_overdue ? 'text-danger' : '' }}">
                                    {{ $monitoredEquipment->next_maintenance_date->format('M d, Y') }}
                                </span>
                            </div>
                        </div>

                        @if($monitoredEquipment->needs_maintenance)
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <strong>Maintenance Due Soon!</strong>
                                <br>
                                <small>
                                    {{ $monitoredEquipment->maintenance_overdue ? 'Overdue by' : 'Due in' }}
                                    {{ abs($monitoredEquipment->next_maintenance_date->diffInDays(now())) }} days
                                </small>
                            </div>
                        @endif
                    @endif
                </div>
            </div>

            <!-- Warranty Information -->
            @if($monitoredEquipment->purchase_date || $monitoredEquipment->warranty_expiry)
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-shield-alt me-2"></i>Warranty Information
                        </h5>
                    </div>
                    <div class="card-body">
                        @if($monitoredEquipment->purchase_date)
                            <div class="mb-3">
                                <div class="d-flex justify-content-between">
                                    <span>Purchase Date:</span>
                                    <span>{{ $monitoredEquipment->purchase_date->format('M d, Y') }}</span>
                                </div>
                            </div>
                        @endif

                        @if($monitoredEquipment->warranty_expiry)
                            <div class="mb-3">
                                <div class="d-flex justify-content-between">
                                    <span>Warranty Expires:</span>
                                    <span class="{{ $monitoredEquipment->warranty_expiry->isPast() ? 'text-danger' : '' }}">
                                        {{ $monitoredEquipment->warranty_expiry->format('M d, Y') }}
                                    </span>
                                </div>
                            </div>

                            @if($monitoredEquipment->warranty_expiry->isPast())
                                <div class="alert alert-danger">
                                    <i class="fas fa-exclamation-circle me-2"></i>
                                    <strong>Warranty Expired!</strong>
                                </div>
                            @elseif($monitoredEquipment->warranty_expiry->diffInDays(now()) <= 30)
                                <div class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    <strong>Warranty expires in {{ $monitoredEquipment->warranty_expiry->diffInDays(now()) }} days</strong>
                                </div>
                            @endif
                        @endif
                    </div>
                </div>
            @endif

            <!-- Quick Actions -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-bolt me-2"></i>Quick Actions
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        @if($monitoredEquipment->equipmentRequest)
                            <a href="{{ route('pm.equipment-monitoring.show-request', $monitoredEquipment->equipmentRequest) }}" 
                               class="btn btn-outline-primary">
                                <i class="fas fa-clipboard-list me-1"></i>View Original Request
                            </a>
                        @endif

                        <a href="{{ route('pm.equipment-monitoring.maintenance-list', ['equipment_id' => $monitoredEquipment->id]) }}" 
                           class="btn btn-outline-info">
                            <i class="fas fa-wrench me-1"></i>Maintenance Schedule
                        </a>

                        @if($monitoredEquipment->project)
                            <a href="{{ route('projects.show', $monitoredEquipment->project) }}" 
                               class="btn btn-outline-success">
                                <i class="fas fa-project-diagram me-1"></i>View Project
                            </a>
                        @endif

                        <a href="{{ route('pm.equipment-monitoring.equipment-list') }}" 
                           class="btn btn-outline-secondary">
                            <i class="fas fa-list me-1"></i>Back to Equipment List
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
.border-left-warning {
    border-left: 3px solid #ffc107 !important;
}

.border-left-danger {
    border-left: 3px solid #dc3545 !important;
}

.card-hover:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    transition: all 0.2s ease-in-out;
}
</style>
@endpush

@endsection