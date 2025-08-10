{{-- resources/views/pm/equipment-monitoring/show-request.blade.php --}}
@extends('app')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('pm.equipment-monitoring.index') }}">Equipment Monitoring</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('pm.equipment-monitoring.requests') }}">Requests</a></li>
                    <li class="breadcrumb-item active">Request Details</li>
                </ol>
            </nav>
            <h1>Equipment Request Details</h1>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('pm.equipment-monitoring.requests') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i>Back to Requests
            </a>
            @if($equipmentRequest->monitoredEquipment)
                <a href="{{ route('pm.equipment-monitoring.show-equipment', $equipmentRequest->monitoredEquipment) }}" 
                   class="btn btn-outline-success">
                    <i class="fas fa-tools me-1"></i>View Equipment
                </a>
            @endif
        </div>
    </div>

    <div class="row">
        <!-- Request Information -->
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-clipboard-list me-2"></i>Request Information
                    </h5>
                    <div>
                        <span class="badge bg-{{ $equipmentRequest->status_badge_color }} me-2">
                            {{ $equipmentRequest->formatted_status }}
                        </span>
                        <span class="badge bg-{{ $equipmentRequest->urgency_badge_color }}">
                            {{ $equipmentRequest->formatted_urgency }}
                        </span>
                        @if($equipmentRequest->is_overdue)
                            <span class="badge bg-danger ms-1">OVERDUE</span>
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h4>{{ $equipmentRequest->equipment_name }}</h4>
                            <p class="text-muted mb-3">{{ $equipmentRequest->equipment_description }}</p>
                            
                            <div class="mb-3">
                                <strong>Usage Type:</strong>
                                <span class="badge bg-{{ $equipmentRequest->usage_type === 'personal' ? 'info' : 'primary' }} ms-2">
                                    {{ $equipmentRequest->formatted_usage_type }}
                                </span>
                            </div>

                            <div class="mb-3">
                                <strong>Quantity Requested:</strong>
                                <span class="ms-2 fw-bold">{{ $equipmentRequest->quantity }}</span>
                            </div>

                            @if($equipmentRequest->estimated_cost)
                                <div class="mb-3">
                                    <strong>Estimated Cost:</strong>
                                    <span class="ms-2 fw-bold">₱{{ number_format($equipmentRequest->estimated_cost, 2) }}</span>
                                </div>
                            @endif

                            <div class="mb-3">
                                <strong>Request Date:</strong>
                                <span class="ms-2">{{ $equipmentRequest->created_at->format('M d, Y g:i A') }}</span>
                                <br>
                                <small class="text-muted">{{ $equipmentRequest->created_at->diffForHumans() }}</small>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <strong>Requested By:</strong>
                                <div class="ms-2">
                                    {{ $equipmentRequest->user->first_name }} {{ $equipmentRequest->user->last_name }}
                                    <br>
                                    <small class="text-muted">{{ $equipmentRequest->user->email }}</small>
                                    <br>
                                    <span class="badge bg-secondary">{{ ucfirst($equipmentRequest->user->role) }}</span>
                                </div>
                            </div>

                            @if($equipmentRequest->project)
                                <div class="mb-3">
                                    <strong>Project:</strong>
                                    <div class="ms-2">
                                        <a href="{{ route('projects.show', $equipmentRequest->project) }}" class="text-decoration-none">
                                            {{ $equipmentRequest->project->name }}
                                        </a>
                                        <br>
                                        <small class="text-muted">
                                            {{ $equipmentRequest->project->start_date->format('M d, Y') }} - 
                                            {{ $equipmentRequest->project->end_date ? $equipmentRequest->project->end_date->format('M d, Y') : 'Ongoing' }}
                                        </small>
                                    </div>
                                </div>
                            @endif

                            @if($equipmentRequest->approved_at)
                                <div class="mb-3">
                                    <strong>{{ $equipmentRequest->status === 'approved' ? 'Approved' : 'Processed' }} Date:</strong>
                                    <span class="ms-2">{{ $equipmentRequest->approved_at->format('M d, Y g:i A') }}</span>
                                    <br>
                                    <small class="text-muted">{{ $equipmentRequest->approved_at->diffForHumans() }}</small>
                                </div>
                            @endif

                            @if($equipmentRequest->approvedBy)
                                <div class="mb-3">
                                    <strong>{{ $equipmentRequest->status === 'approved' ? 'Approved' : 'Processed' }} By:</strong>
                                    <div class="ms-2">
                                        {{ $equipmentRequest->approvedBy->first_name }} {{ $equipmentRequest->approvedBy->last_name }}
                                        <br>
                                        <small class="text-muted">{{ $equipmentRequest->approvedBy->email }}</small>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="mt-4 pt-3 border-top">
                        <strong>Justification:</strong>
                        <p class="mt-2 mb-0">{{ $equipmentRequest->justification }}</p>
                    </div>

                    @if($equipmentRequest->additional_notes)
                        <div class="mt-4 pt-3 border-top">
                            <strong>Additional Notes:</strong>
                            <p class="mt-2 mb-0">{{ $equipmentRequest->additional_notes }}</p>
                        </div>
                    @endif

                    @if($equipmentRequest->admin_notes)
                        <div class="mt-4 pt-3 border-top">
                            <strong>Admin Notes:</strong>
                            <p class="mt-2 mb-0">{{ $equipmentRequest->admin_notes }}</p>
                        </div>
                    @endif

                    @if($equipmentRequest->decline_reason)
                        <div class="mt-4 pt-3 border-top">
                            <strong class="text-danger">Decline Reason:</strong>
                            <div class="alert alert-danger mt-2">
                                {{ $equipmentRequest->decline_reason }}
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Equipment Status (if created) -->
            @if($equipmentRequest->monitoredEquipment)
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-tools me-2"></i>Created Equipment
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <strong>Equipment Status:</strong>
                                    <span class="badge bg-{{ $equipmentRequest->monitoredEquipment->status_badge_color }} ms-2">
                                        {{ $equipmentRequest->monitoredEquipment->formatted_status }}
                                    </span>
                                </div>

                                <div class="mb-3">
                                    <strong>Availability:</strong>
                                    <span class="badge bg-{{ $equipmentRequest->monitoredEquipment->availability_badge_color }} ms-2">
                                        {{ $equipmentRequest->monitoredEquipment->formatted_availability_status }}
                                    </span>
                                </div>

                                @if($equipmentRequest->monitoredEquipment->location)
                                    <div class="mb-3">
                                        <strong>Current Location:</strong>
                                        <span class="ms-2">{{ $equipmentRequest->monitoredEquipment->location }}</span>
                                    </div>
                                @endif

                                @if($equipmentRequest->monitoredEquipment->serial_number)
                                    <div class="mb-3">
                                        <strong>Serial Number:</strong>
                                        <span class="ms-2">{{ $equipmentRequest->monitoredEquipment->serial_number }}</span>
                                    </div>
                                @endif
                            </div>

                            <div class="col-md-6">
                                @if($equipmentRequest->monitoredEquipment->last_maintenance_date)
                                    <div class="mb-3">
                                        <strong>Last Maintenance:</strong>
                                        <span class="ms-2">{{ $equipmentRequest->monitoredEquipment->last_maintenance_date->format('M d, Y') }}</span>
                                    </div>
                                @endif

                                @if($equipmentRequest->monitoredEquipment->next_maintenance_date)
                                    <div class="mb-3">
                                        <strong>Next Maintenance:</strong>
                                        <span class="ms-2 {{ $equipmentRequest->monitoredEquipment->maintenance_overdue ? 'text-danger' : '' }}">
                                            {{ $equipmentRequest->monitoredEquipment->next_maintenance_date->format('M d, Y') }}
                                        </span>
                                        @if($equipmentRequest->monitoredEquipment->needs_maintenance)
                                            <br>
                                            <small class="{{ $equipmentRequest->monitoredEquipment->maintenance_overdue ? 'text-danger' : 'text-warning' }}">
                                                {{ $equipmentRequest->monitoredEquipment->maintenance_overdue ? 'Overdue!' : 'Due Soon' }}
                                            </small>
                                        @endif
                                    </div>
                                @endif

                                <div class="mt-3">
                                    <a href="{{ route('pm.equipment-monitoring.show-equipment', $equipmentRequest->monitoredEquipment) }}" 
                                       class="btn btn-outline-primary">
                                        <i class="fas fa-eye me-1"></i>View Equipment Details
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Request Timeline -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-history me-2"></i>Request Timeline
                    </h5>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        <div class="timeline-item">
                            <div class="timeline-marker bg-primary"></div>
                            <div class="timeline-content">
                                <h6 class="timeline-title">Request Submitted</h6>
                                <p class="timeline-text">{{ $equipmentRequest->created_at->format('M d, Y g:i A') }}</p>
                                <small class="text-muted">
                                    Submitted by {{ $equipmentRequest->user->first_name }} {{ $equipmentRequest->user->last_name }}
                                </small>
                            </div>
                        </div>

                        @if($equipmentRequest->status === 'approved')
                            <div class="timeline-item">
                                <div class="timeline-marker bg-success"></div>
                                <div class="timeline-content">
                                    <h6 class="timeline-title">Request Approved</h6>
                                    <p class="timeline-text">{{ $equipmentRequest->approved_at->format('M d, Y g:i A') }}</p>
                                    @if($equipmentRequest->approvedBy)
                                        <small class="text-muted">
                                            Approved by {{ $equipmentRequest->approvedBy->first_name }} {{ $equipmentRequest->approvedBy->last_name }}
                                        </small>
                                    @endif
                                </div>
                            </div>

                            @if($equipmentRequest->monitoredEquipment && $equipmentRequest->monitoredEquipment->status === 'active')
                                <div class="timeline-item">
                                    <div class="timeline-marker bg-info"></div>
                                    <div class="timeline-content">
                                        <h6 class="timeline-title">Equipment Activated</h6>
                                        <p class="timeline-text">{{ $equipmentRequest->monitoredEquipment->updated_at->format('M d, Y g:i A') }}</p>
                                        <small class="text-muted">Equipment is now active and available for use</small>
                                    </div>
                                </div>
                            @endif
                        @endif

                        @if($equipmentRequest->status === 'declined')
                            <div class="timeline-item">
                                <div class="timeline-marker bg-danger"></div>
                                <div class="timeline-content">
                                    <h6 class="timeline-title">Request Declined</h6>
                                    <p class="timeline-text">{{ $equipmentRequest->approved_at->format('M d, Y g:i A') }}</p>
                                    @if($equipmentRequest->approvedBy)
                                        <small class="text-muted">
                                            Declined by {{ $equipmentRequest->approvedBy->first_name }} {{ $equipmentRequest->approvedBy->last_name }}
                                        </small>
                                    @endif
                                </div>
                            </div>
                        @endif

                        @if($equipmentRequest->status === 'pending')
                            <div class="timeline-item">
                                <div class="timeline-marker bg-warning"></div>
                                <div class="timeline-content">
                                    <h6 class="timeline-title">Awaiting Approval</h6>
                                    <p class="timeline-text">Request is currently under review</p>
                                    <small class="text-muted">
                                        Pending for {{ $equipmentRequest->days_since_created }} days
                                        @if($equipmentRequest->is_overdue)
                                            <span class="text-danger fw-bold"> - OVERDUE</span>
                                        @endif
                                    </small>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-md-4">
            <!-- Request Status -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-info-circle me-2"></i>Request Status
                    </h5>
                </div>
                <div class="card-body text-center">
                    <div class="request-status-icon status-{{ $equipmentRequest->status }} mb-3">
                        @switch($equipmentRequest->status)
                            @case('pending')
                                <i class="fas fa-clock fa-3x"></i>
                                @break
                            @case('approved')
                                <i class="fas fa-check-circle fa-3x"></i>
                                @break
                            @case('declined')
                                <i class="fas fa-times-circle fa-3x"></i>
                                @break
                            @default
                                <i class="fas fa-question-circle fa-3x"></i>
                        @endswitch
                    </div>
                    <h4 class="text-{{ $equipmentRequest->status_badge_color }}">
                        {{ $equipmentRequest->formatted_status }}
                    </h4>
                    
                    @if($equipmentRequest->status === 'pending')
                        @if($equipmentRequest->is_overdue)
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <strong>Overdue for Review!</strong>
                                <br>
                                <small>{{ $equipmentRequest->days_since_created }} days since submission</small>
                            </div>
                        @else
                            <div class="alert alert-warning">
                                <i class="fas fa-clock me-2"></i>
                                <strong>Awaiting Admin Review</strong>
                                <br>
                                <small>{{ $equipmentRequest->days_since_created }} days since submission</small>
                            </div>
                        @endif
                    @endif

                    @if($equipmentRequest->status === 'approved')
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle me-2"></i>
                            <strong>Request Approved!</strong>
                            @if($equipmentRequest->approved_at)
                                <br>
                                <small>{{ $equipmentRequest->approved_at->format('M d, Y') }}</small>
                            @endif
                        </div>
                    @endif

                    @if($equipmentRequest->status === 'declined')
                        <div class="alert alert-danger">
                            <i class="fas fa-times-circle me-2"></i>
                            <strong>Request Declined</strong>
                            @if($equipmentRequest->approved_at)
                                <br>
                                <small>{{ $equipmentRequest->approved_at->format('M d, Y') }}</small>
                            @endif
                        </div>
                    @endif
                </div>
            </div>

            <!-- Request Summary -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-clipboard-check me-2"></i>Request Summary
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6">
                            <h4 class="text-primary">{{ $equipmentRequest->quantity }}</h4>
                            <small class="text-muted">Quantity</small>
                        </div>
                        <div class="col-6">
                            <h4 class="text-{{ $equipmentRequest->urgency_badge_color }}">
                                {{ $equipmentRequest->formatted_urgency }}
                            </h4>
                            <small class="text-muted">Urgency</small>
                        </div>
                    </div>

                    @if($equipmentRequest->estimated_cost)
                        <hr>
                        <div class="text-center">
                            <h4 class="text-success">₱{{ number_format($equipmentRequest->estimated_cost, 2) }}</h4>
                            <small class="text-muted">Estimated Cost</small>
                        </div>
                    @endif

                    <hr>
                    <div class="text-center">
                        <span class="badge bg-{{ $equipmentRequest->usage_type === 'personal' ? 'info' : 'primary' }} badge-lg">
                            {{ $equipmentRequest->formatted_usage_type }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-bolt me-2"></i>Quick Actions
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        @if($equipmentRequest->monitoredEquipment)
                            <a href="{{ route('pm.equipment-monitoring.show-equipment', $equipmentRequest->monitoredEquipment) }}" 
                               class="btn btn-outline-success">
                                <i class="fas fa-tools me-1"></i>View Equipment
                            </a>
                        @endif

                        @if($equipmentRequest->project)
                            <a href="{{ route('projects.show', $equipmentRequest->project) }}" 
                               class="btn btn-outline-primary">
                                <i class="fas fa-project-diagram me-1"></i>View Project
                            </a>
                        @endif

                        <a href="{{ route('pm.equipment-monitoring.requests', ['status' => $equipmentRequest->status]) }}" 
                           class="btn btn-outline-info">
                            <i class="fas fa-filter me-1"></i>Similar Requests
                        </a>

                        <a href="{{ route('pm.equipment-monitoring.requests') }}" 
                           class="btn btn-outline-secondary">
                            <i class="fas fa-list me-1"></i>All Requests
                        </a>

                        <a href="{{ route('pm.equipment-monitoring.index') }}" 
                           class="btn btn-outline-dark">
                            <i class="fas fa-tachometer-alt me-1"></i>Dashboard
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

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
    background: #dee2e6;
}

.timeline-item {
    position: relative;
    margin-bottom: 20px;
}

.timeline-marker {
    position: absolute;
    left: -25px;
    top: 5px;
    width: 20px;
    height: 20px;
    border-radius: 50%;
    border: 3px solid #fff;
    box-shadow: 0 0 0 2px #dee2e6;
}

.timeline-content {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 8px;
    border-left: 3px solid #007bff;
}

.timeline-title {
    margin-bottom: 5px;
    font-weight: 600;
}

.timeline-text {
    margin-bottom: 5px;
    color: #495057;
}

.request-status-icon.status-pending { color: #ffc107; }
.request-status-icon.status-approved { color: #28a745; }
.request-status-icon.status-declined { color: #dc3545; }

.badge-lg {
    padding: 0.5rem 1rem;
    font-size: 0.9rem;
}

.border-left-warning {
    border-left: 3px solid #ffc107 !important;
}

.border-left-danger {
    border-left: 3px solid #dc3545 !important;
}
</style>
@endpush

@endsection