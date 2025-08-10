@extends('app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-10 offset-md-1">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h2">Equipment Request Details</h1>
                    <p class="text-muted">Request #{{ $equipmentRequest->id }}</p>
                </div>
                <div class="d-flex gap-2">
                    @if($equipmentRequest->monitoredEquipment && $equipmentRequest->status === 'approved')
                        <a href="{{ route('admin.equipment-monitoring.show-my-equipment', $equipmentRequest->monitoredEquipment) }}" class="btn btn-success">
                            <i class="fas fa-cubes me-1"></i>View Equipment
                        </a>
                    @endif
                    <a href="{{ route('admin.equipment-monitoring.my-requests') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i>Back to Requests
                    </a>
                </div>
            </div>

            <div class="row">
                <!-- Request Information -->
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Request Information</h5>
                        </div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <strong>Equipment Name:</strong>
                                    <p class="mb-0">{{ $equipmentRequest->equipment_name }}</p>
                                </div>
                                <div class="col-md-3">
                                    <strong>Quantity:</strong>
                                    <p class="mb-0">{{ $equipmentRequest->quantity }}</p>
                                </div>
                                <div class="col-md-3">
                                    <strong>Usage Type:</strong>
                                    <p class="mb-0">
                                        <span class="badge bg-{{ $equipmentRequest->usage_type === 'personal' ? 'secondary' : 'info' }}">
                                            {{ $equipmentRequest->formatted_usage_type }}
                                        </span>
                                    </p>
                                </div>
                            </div>

                            @if($equipmentRequest->project)
                                <div class="mb-3">
                                    <strong>Project:</strong>
                                    <p class="mb-0">
                                        <a href="{{ route('projects.show', $equipmentRequest->project) }}" class="text-decoration-none">
                                            {{ $equipmentRequest->project->name }}
                                        </a>
                                    </p>
                                </div>
                            @endif

                            <div class="mb-3">
                                <strong>Equipment Description:</strong>
                                <p class="mb-0">{{ $equipmentRequest->equipment_description }}</p>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <strong>Urgency Level:</strong>
                                    <p class="mb-0">
                                        <span class="badge bg-{{ $equipmentRequest->urgency_badge_color }}">
                                            {{ $equipmentRequest->formatted_urgency }}
                                        </span>
                                        @if($equipmentRequest->is_overdue && $equipmentRequest->status === 'pending')
                                            <span class="badge bg-warning ms-1">Overdue</span>
                                        @endif
                                    </p>
                                </div>
                                @if($equipmentRequest->estimated_cost)
                                    <div class="col-md-6">
                                        <strong>Estimated Cost:</strong>
                                        <p class="mb-0">₱{{ number_format($equipmentRequest->estimated_cost, 2) }}</p>
                                    </div>
                                @endif
                            </div>

                            <div class="mb-3">
                                <strong>Justification:</strong>
                                <p class="mb-0">{{ $equipmentRequest->justification }}</p>
                            </div>

                            @if($equipmentRequest->additional_notes)
                                <div class="mb-3">
                                    <strong>Additional Notes:</strong>
                                    <p class="mb-0">{{ $equipmentRequest->additional_notes }}</p>
                                </div>
                            @endif

                            <!-- Admin Response Section -->
                            @if($equipmentRequest->status !== 'pending')
                                <hr>
                                <h6 class="text-primary mb-3">Admin Response</h6>
                                
                                @if($equipmentRequest->admin_notes)
                                    <div class="mb-3">
                                        <strong>Admin Notes:</strong>
                                        <p class="mb-0">{{ $equipmentRequest->admin_notes }}</p>
                                    </div>
                                @endif

                                @if($equipmentRequest->decline_reason)
                                    <div class="mb-3">
                                        <strong>Decline Reason:</strong>
                                        <div class="alert alert-danger">
                                            {{ $equipmentRequest->decline_reason }}
                                        </div>
                                    </div>
                                @endif

                                @if($equipmentRequest->approvedBy)
                                    <div class="mb-3">
                                        <strong>{{ $equipmentRequest->status === 'approved' ? 'Approved' : 'Declined' }} By:</strong>
                                        <p class="mb-0">
                                            {{ $equipmentRequest->approvedBy->first_name }} {{ $equipmentRequest->approvedBy->last_name }}
                                            <br><small class="text-muted">on {{ $equipmentRequest->approved_at->format('M d, Y g:i A') }}</small>
                                        </p>
                                    </div>
                                @endif
                            @endif
                        </div>
                    </div>

                    <!-- Equipment Information (if approved) -->
                    @if($equipmentRequest->monitoredEquipment && $equipmentRequest->status === 'approved')
                        <div class="card mt-4">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Equipment Information</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <strong>Equipment Status:</strong>
                                        <p class="mb-2">
                                            <span class="badge bg-{{ $equipmentRequest->monitoredEquipment->status_badge_color }}">
                                                {{ $equipmentRequest->monitoredEquipment->formatted_status }}
                                            </span>
                                        </p>

                                        <strong>Availability:</strong>
                                        <p class="mb-2">
                                            <span class="badge bg-{{ $equipmentRequest->monitoredEquipment->availability_badge_color }}">
                                                {{ $equipmentRequest->monitoredEquipment->formatted_availability_status }}
                                            </span>
                                        </p>

                                        @if($equipmentRequest->monitoredEquipment->location)
                                            <strong>Location:</strong>
                                            <p class="mb-2">{{ $equipmentRequest->monitoredEquipment->location }}</p>
                                        @endif
                                    </div>
                                    <div class="col-md-6">
                                        @if($equipmentRequest->monitoredEquipment->serial_number)
                                            <strong>Serial Number:</strong>
                                            <p class="mb-2">{{ $equipmentRequest->monitoredEquipment->serial_number }}</p>
                                        @endif

                                        @if($equipmentRequest->monitoredEquipment->next_maintenance_date)
                                            <strong>Next Maintenance:</strong>
                                            <p class="mb-2">
                                                {{ $equipmentRequest->monitoredEquipment->next_maintenance_date->format('M d, Y') }}
                                                @if($equipmentRequest->monitoredEquipment->maintenance_overdue)
                                                    <span class="badge bg-danger ms-1">Overdue</span>
                                                @elseif($equipmentRequest->monitoredEquipment->needs_maintenance)
                                                    <span class="badge bg-warning ms-1">Due Soon</span>
                                                @endif
                                            </p>
                                        @endif
                                    </div>
                                </div>

                                <div class="mt-3">
                                    <a href="{{ route('admin.equipment-monitoring.show-my-equipment', $equipmentRequest->monitoredEquipment) }}" class="btn btn-primary">
                                        <i class="fas fa-cubes me-1"></i>View Equipment Details
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Request Status & Timeline -->
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Request Status</h5>
                        </div>
                        <div class="card-body">
                            <div class="text-center mb-3">
                                <span class="badge bg-{{ $equipmentRequest->status_badge_color }} fs-6">
                                    {{ $equipmentRequest->formatted_status }}
                                </span>
                            </div>

                            <!-- Timeline -->
                            <div class="timeline">
                                <div class="timeline-item">
                                    <div class="timeline-marker bg-primary"></div>
                                    <div class="timeline-content">
                                        <h6 class="timeline-title">Request Submitted</h6>
                                        <p class="timeline-description">
                                            {{ $equipmentRequest->created_at->format('M d, Y g:i A') }}
                                            <br><small class="text-muted">{{ $equipmentRequest->created_at->diffForHumans() }}</small>
                                        </p>
                                    </div>
                                </div>

                                @if($equipmentRequest->approved_at)
                                    <div class="timeline-item">
                                        <div class="timeline-marker bg-{{ $equipmentRequest->status === 'approved' ? 'success' : 'danger' }}"></div>
                                        <div class="timeline-content">
                                            <h6 class="timeline-title">
                                                {{ $equipmentRequest->status === 'approved' ? 'Request Approved' : 'Request Declined' }}
                                            </h6>
                                            <p class="timeline-description">
                                                {{ $equipmentRequest->approved_at->format('M d, Y g:i A') }}
                                                <br><small class="text-muted">{{ $equipmentRequest->approved_at->diffForHumans() }}</small>
                                                @if($equipmentRequest->approvedBy)
                                                    <br><small class="text-muted">by {{ $equipmentRequest->approvedBy->first_name }} {{ $equipmentRequest->approvedBy->last_name }}</small>
                                                @endif
                                            </p>
                                        </div>
                                    </div>
                                @endif

                                @if($equipmentRequest->monitoredEquipment && $equipmentRequest->status === 'approved')
                                    <div class="timeline-item">
                                        <div class="timeline-marker bg-info"></div>
                                        <div class="timeline-content">
                                            <h6 class="timeline-title">Equipment Activated</h6>
                                            <p class="timeline-description">
                                                Equipment is now available for use
                                                <br><small class="text-muted">Status: {{ $equipmentRequest->monitoredEquipment->formatted_status }}</small>
                                            </p>
                                        </div>
                                    </div>
                                @endif
                            </div>

                            <!-- Quick Actions -->
                            <div class="mt-4">
                                <h6>Quick Actions</h6>
                                @if($equipmentRequest->monitoredEquipment && $equipmentRequest->status === 'approved')
                                    <div class="d-grid gap-2">
                                        <a href="{{ route('admin.equipment-monitoring.show-my-equipment', $equipmentRequest->monitoredEquipment) }}" class="btn btn-outline-primary btn-sm">
                                            <i class="fas fa-cubes me-1"></i>View Equipment
                                        </a>
                                        <a href="{{ route('admin.equipment-monitoring.create-maintenance') }}?equipment={{ $equipmentRequest->monitoredEquipment->id }}" class="btn btn-outline-warning btn-sm">
                                            <i class="fas fa-wrench me-1"></i>Schedule Maintenance
                                        </a>
                                    </div>
                                @else
                                    <div class="d-grid gap-2">
                                        <a href="{{ route('admin.equipment-monitoring.create-request') }}" class="btn btn-outline-primary btn-sm">
                                            <i class="fas fa-plus me-1"></i>New Request
                                        </a>
                                        <a href="{{ route('admin.equipment-monitoring.my-requests') }}" class="btn btn-outline-secondary btn-sm">
                                            <i class="fas fa-list me-1"></i>All Requests
                                        </a>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Request Stats -->
                    <div class="card mt-3">
                        <div class="card-header">
                            <h6 class="card-title mb-0">Request Information</h6>
                        </div>
                        <div class="card-body">
                            <div class="row text-center">
                                <div class="col-6">
                                    <div class="border-end">
                                        <h6 class="text-muted mb-1">Days Since</h6>
                                        <h5 class="mb-0">{{ $equipmentRequest->days_since_created }}</h5>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <h6 class="text-muted mb-1">Urgency</h6>
                                    <h5 class="mb-0">
                                        <span class="badge bg-{{ $equipmentRequest->urgency_badge_color }}">
                                            {{ ucfirst($equipmentRequest->urgency_level) }}
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