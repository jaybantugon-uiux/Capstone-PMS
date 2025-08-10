
@extends('app')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('pm.equipment-monitoring.index') }}">Equipment Monitoring</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('pm.equipment-monitoring.maintenance-list') }}">Maintenance</a></li>
                    <li class="breadcrumb-item active">Maintenance Details</li>
                </ol>
            </nav>
            <h1>Maintenance Details</h1>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('pm.equipment-monitoring.maintenance-list') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i>Back to Schedule
            </a>
            <a href="{{ route('pm.equipment-monitoring.show-equipment', $equipmentMaintenance->monitoredEquipment) }}" 
               class="btn btn-outline-primary">
                <i class="fas fa-tools me-1"></i>View Equipment
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Maintenance Information -->
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-wrench me-2"></i>Maintenance Information
                    </h5>
                    <div>
                        <span class="badge bg-{{ $equipmentMaintenance->status_badge_color }} me-2">
                            {{ $equipmentMaintenance->formatted_status }}
                        </span>
                        <span class="badge bg-{{ $equipmentMaintenance->priority_badge_color }}">
                            {{ $equipmentMaintenance->formatted_priority }}
                        </span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <strong>Maintenance Type:</strong>
                                <span class="ms-2">{{ $equipmentMaintenance->formatted_maintenance_type }}</span>
                            </div>

                            <div class="mb-3">
                                <strong>Scheduled Date:</strong>
                                <span class="ms-2 {{ $equipmentMaintenance->is_overdue ? 'text-danger fw-bold' : '' }}">
                                    {{ $equipmentMaintenance->scheduled_date->format('M d, Y g:i A') }}
                                    @if($equipmentMaintenance->is_overdue)
                                        <i class="fas fa-exclamation-triangle text-danger ms-1" title="Overdue"></i>
                                    @elseif($equipmentMaintenance->is_upcoming)
                                        <i class="fas fa-clock text-warning ms-1" title="Due Soon"></i>
                                    @endif
                                </span>
                            </div>

                            @if($equipmentMaintenance->completed_date)
                                <div class="mb-3">
                                    <strong>Completed Date:</strong>
                                    <span class="ms-2">{{ $equipmentMaintenance->completed_date->format('M d, Y g:i A') }}</span>
                                </div>
                            @endif

                            @if($equipmentMaintenance->estimated_duration)
                                <div class="mb-3">
                                    <strong>Estimated Duration:</strong>
                                    <span class="ms-2">{{ $equipmentMaintenance->estimated_duration_hours }} hours</span>
                                </div>
                            @endif

                            @if($equipmentMaintenance->actual_duration)
                                <div class="mb-3">
                                    <strong>Actual Duration:</strong>
                                    <span class="ms-2">{{ $equipmentMaintenance->actual_duration_hours }} hours</span>
                                </div>
                            @endif
                        </div>

                        <div class="col-md-6">
                            @if($equipmentMaintenance->cost)
                                <div class="mb-3">
                                    <strong>Cost:</strong>
                                    <span class="ms-2">₱{{ number_format($equipmentMaintenance->cost, 2) }}</span>
                                </div>
                            @endif

                            @if($equipmentMaintenance->performedBy)
                                <div class="mb-3">
                                    <strong>Performed By:</strong>
                                    <div class="ms-2">
                                        {{ $equipmentMaintenance->performedBy->first_name }} {{ $equipmentMaintenance->performedBy->last_name }}
                                        <br>
                                        <small class="text-muted">{{ $equipmentMaintenance->performedBy->email }}</small>
                                    </div>
                                </div>
                            @endif

                            @if($equipmentMaintenance->status === 'scheduled')
                                <div class="mb-3">
                                    <strong>Days Until Scheduled:</strong>
                                    <span class="ms-2">
                                        @if($equipmentMaintenance->days_until_scheduled < 0)
                                            <span class="text-danger">{{ abs($equipmentMaintenance->days_until_scheduled) }} days overdue</span>
                                        @elseif($equipmentMaintenance->days_until_scheduled == 0)
                                            <span class="text-warning">Due today</span>
                                        @else
                                            {{ $equipmentMaintenance->days_until_scheduled }} days
                                        @endif
                                    </span>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="mt-4">
                        <strong>Description:</strong>
                        <p class="mt-2 mb-0">{{ $equipmentMaintenance->description }}</p>
                    </div>

                    @if($equipmentMaintenance->notes)
                        <div class="mt-4 pt-3 border-top">
                            <strong>Notes:</strong>
                            <p class="mt-2 mb-0">{{ $equipmentMaintenance->notes }}</p>
                        </div>
                    @endif

                    @if($equipmentMaintenance->completion_notes)
                        <div class="mt-4 pt-3 border-top">
                            <strong>Completion Notes:</strong>
                            <p class="mt-2 mb-0">{{ $equipmentMaintenance->completion_notes }}</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Equipment Information -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-tools me-2"></i>Equipment Information
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>{{ $equipmentMaintenance->monitoredEquipment->equipment_name }}</h6>
                            <p class="text-muted">{{ $equipmentMaintenance->monitoredEquipment->equipment_description }}</p>

                            <div class="mb-2">
                                <strong>Status:</strong>
                                <span class="badge bg-{{ $equipmentMaintenance->monitoredEquipment->status_badge_color }} ms-2">
                                    {{ $equipmentMaintenance->monitoredEquipment->formatted_status }}
                                </span>
                            </div>

                            <div class="mb-2">
                                <strong>Availability:</strong>
                                <span class="badge bg-{{ $equipmentMaintenance->monitoredEquipment->availability_badge_color }} ms-2">
                                    {{ $equipmentMaintenance->monitoredEquipment->formatted_availability_status }}
                                </span>
                            </div>

                            @if($equipmentMaintenance->monitoredEquipment->location)
                                <div class="mb-2">
                                    <strong>Location:</strong>
                                    <span class="ms-2">{{ $equipmentMaintenance->monitoredEquipment->location }}</span>
                                </div>
                            @endif
                        </div>

                        <div class="col-md-6">
                            <div class="mb-2">
                                <strong>Managed By:</strong>
                                <div class="ms-2">
                                    {{ $equipmentMaintenance->monitoredEquipment->user->first_name }} {{ $equipmentMaintenance->monitoredEquipment->user->last_name }}
                                    <br>
                                    <small class="text-muted">{{ $equipmentMaintenance->monitoredEquipment->user->email }}</small>
                                </div>
                            </div>

                            @if($equipmentMaintenance->monitoredEquipment->project)
                                <div class="mb-2">
                                    <strong>Project:</strong>
                                    <div class="ms-2">
                                        <a href="{{ route('projects.show', $equipmentMaintenance->monitoredEquipment->project) }}">
                                            {{ $equipmentMaintenance->monitoredEquipment->project->name }}
                                        </a>
                                    </div>
                                </div>
                            @else
                                <div class="mb-2">
                                    <strong>Usage Type:</strong>
                                    <span class="badge bg-info ms-2">Personal Use</span>
                                </div>
                            @endif

                            <div class="mt-3">
                                <a href="{{ route('pm.equipment-monitoring.show-equipment', $equipmentMaintenance->monitoredEquipment) }}" 
                                   class="btn btn-outline-primary btn-sm">
                                    <i class="fas fa-eye me-1"></i>View Equipment Details
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Maintenance Timeline -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-history me-2"></i>Maintenance Timeline
                    </h5>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        <div class="timeline-item">
                            <div class="timeline-marker bg-primary"></div>
                            <div class="timeline-content">
                                <h6 class="timeline-title">Maintenance Scheduled</h6>
                                <p class="timeline-text">{{ $equipmentMaintenance->created_at->format('M d, Y g:i A') }}</p>
                            </div>
                        </div>

                        @if($equipmentMaintenance->status === 'in_progress')
                            <div class="timeline-item">
                                <div class="timeline-marker bg-warning"></div>
                                <div class="timeline-content">
                                    <h6 class="timeline-title">Maintenance In Progress</h6>
                                    <p class="timeline-text">Maintenance work is currently being performed</p>
                                </div>
                            </div>
                        @endif

                        @if($equipmentMaintenance->status === 'completed')
                            <div class="timeline-item">
                                <div class="timeline-marker bg-success"></div>
                                <div class="timeline-content">
                                    <h6 class="timeline-title">Maintenance Completed</h6>
                                    <p class="timeline-text">{{ $equipmentMaintenance->completed_date->format('M d, Y g:i A') }}</p>
                                    @if($equipmentMaintenance->performedBy)
                                        <small class="text-muted">
                                            Performed by {{ $equipmentMaintenance->performedBy->first_name }} {{ $equipmentMaintenance->performedBy->last_name }}
                                        </small>
                                    @endif
                                </div>
                            </div>
                        @endif

                        @if($equipmentMaintenance->status === 'cancelled')
                            <div class="timeline-item">
                                <div class="timeline-marker bg-danger"></div>
                                <div class="timeline-content">
                                    <h6 class="timeline-title">Maintenance Cancelled</h6>
                                    <p class="timeline-text">{{ $equipmentMaintenance->updated_at->format('M d, Y g:i A') }}</p>
                                    @if($equipmentMaintenance->completion_notes)
                                        <small class="text-muted">Reason: {{ $equipmentMaintenance->completion_notes }}</small>
                                    @endif
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-md-4">
            <!-- Maintenance Status -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-info-circle me-2"></i>Maintenance Status
                    </h5>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <div class="maintenance-status-icon status-{{ $equipmentMaintenance->status }} mb-2">
                            @switch($equipmentMaintenance->status)
                                @case('scheduled')
                                    <i class="fas fa-clock fa-2x"></i>
                                    @break
                                @case('in_progress')
                                    <i class="fas fa-cog fa-spin fa-2x"></i>
                                    @break
                                @case('completed')
                                    <i class="fas fa-check-circle fa-2x"></i>
                                    @break
                                @case('cancelled')
                                    <i class="fas fa-times-circle fa-2x"></i>
                                    @break
                                @default
                                    <i class="fas fa-question-circle fa-2x"></i>
                            @endswitch
                        </div>
                        <h5 class="text-{{ $equipmentMaintenance->status_badge_color }}">
                            {{ $equipmentMaintenance->formatted_status }}
                        </h5>
                    </div>

                    @if($equipmentMaintenance->status === 'scheduled')
                        @if($equipmentMaintenance->is_overdue)
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <strong>Overdue!</strong>
                                <br>
                                <small>{{ abs($equipmentMaintenance->days_until_scheduled) }} days overdue</small>
                            </div>
                        @elseif($equipmentMaintenance->is_upcoming)
                            <div class="alert alert-warning">
                                <i class="fas fa-clock me-2"></i>
                                <strong>Due Soon!</strong>
                                <br>
                                <small>{{ $equipmentMaintenance->days_until_scheduled }} days remaining</small>
                            </div>
                        @else
                            <div class="alert alert-info">
                                <i class="fas fa-calendar me-2"></i>
                                <strong>Scheduled</strong>
                                <br>
                                <small>{{ $equipmentMaintenance->days_until_scheduled }} days remaining</small>
                            </div>
                        @endif
                    @endif

                    @if($equipmentMaintenance->status === 'completed')
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle me-2"></i>
                            <strong>Completed Successfully!</strong>
                            @if($equipmentMaintenance->completed_date)
                                <br>
                                <small>{{ $equipmentMaintenance->completed_date->format('M d, Y') }}</small>
                            @endif
                        </div>
                    @endif
                </div>
            </div>

            <!-- Key Metrics -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-bar me-2"></i>Key Metrics
                    </h5>
                </div>
                <div class="card-body">
                    @if($equipmentMaintenance->estimated_duration && $equipmentMaintenance->actual_duration)
                        <div class="mb-3">
                            <div class="d-flex justify-content-between">
                                <span>Duration Variance:</span>
                                @php
                                    $variance = $equipmentMaintenance->actual_duration - $equipmentMaintenance->estimated_duration;
                                    $variancePercent = ($variance / $equipmentMaintenance->estimated_duration) * 100;
                                @endphp
                                <span class="{{ $variance > 0 ? 'text-warning' : 'text-success' }}">
                                    {{ $variance > 0 ? '+' : '' }}{{ round($variance / 60, 1) }}h
                                    ({{ $variance > 0 ? '+' : '' }}{{ round($variancePercent, 1) }}%)
                                </span>
                            </div>
                        </div>
                    @endif

                    @if($equipmentMaintenance->estimated_duration)
                        <div class="mb-3">
                            <div class="d-flex justify-content-between">
                                <span>Estimated Duration:</span>
                                <span>{{ $equipmentMaintenance->estimated_duration_hours }}h</span>
                            </div>
                        </div>
                    @endif

                    @if($equipmentMaintenance->actual_duration)
                        <div class="mb-3">
                            <div class="d-flex justify-content-between">
                                <span>Actual Duration:</span>
                                <span>{{ $equipmentMaintenance->actual_duration_hours }}h</span>
                            </div>
                        </div>
                    @endif

                    @if($equipmentMaintenance->cost)
                        <div class="mb-3">
                            <div class="d-flex justify-content-between">
                                <span>Cost:</span>
                                <span class="fw-bold">₱{{ number_format($equipmentMaintenance->cost, 2) }}</span>
                            </div>
                        </div>
                    @endif

                    @if($equipmentMaintenance->created_at && $equipmentMaintenance->completed_date)
                        <div class="mb-3">
                            <div class="d-flex justify-content-between">
                                <span>Total Lifecycle:</span>
                                <span>{{ $equipmentMaintenance->created_at->diffInDays($equipmentMaintenance->completed_date) }} days</span>
                            </div>
                        </div>
                    @endif
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
                        <a href="{{ route('pm.equipment-monitoring.show-equipment', $equipmentMaintenance->monitoredEquipment) }}" 
                           class="btn btn-outline-primary">
                            <i class="fas fa-tools me-1"></i>View Equipment
                        </a>

                        <a href="{{ route('pm.equipment-monitoring.maintenance-list', ['equipment_id' => $equipmentMaintenance->monitoredEquipment->id]) }}" 
                           class="btn btn-outline-info">
                            <i class="fas fa-history me-1"></i>Maintenance History
                        </a>

                        @if($equipmentMaintenance->monitoredEquipment->project)
                            <a href="{{ route('projects.show', $equipmentMaintenance->monitoredEquipment->project) }}" 
                               class="btn btn-outline-success">
                                <i class="fas fa-project-diagram me-1"></i>View Project
                            </a>
                        @endif

                        <a href="{{ route('pm.equipment-monitoring.maintenance-list') }}" 
                           class="btn btn-outline-secondary">
                            <i class="fas fa-calendar me-1"></i>Maintenance Schedule
                        </a>

                        @if($equipmentMaintenance->monitoredEquipment->equipmentRequest)
                            <a href="{{ route('pm.equipment-monitoring.show-request', $equipmentMaintenance->monitoredEquipment->equipmentRequest) }}" 
                               class="btn btn-outline-warning">
                                <i class="fas fa-clipboard-list me-1"></i>Original Request
                            </a>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Related Equipment -->
            @if($equipmentMaintenance->monitoredEquipment->user->monitoredEquipment->where('id', '!=', $equipmentMaintenance->monitoredEquipment->id)->count() > 0)
                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-tools me-2"></i>Other Equipment by Same User
                        </h5>
                    </div>
                    <div class="card-body">
                        @foreach($equipmentMaintenance->monitoredEquipment->user->monitoredEquipment->where('id', '!=', $equipmentMaintenance->monitoredEquipment->id)->take(3) as $otherEquipment)
                            <div class="mb-2">
                                <a href="{{ route('pm.equipment-monitoring.show-equipment', $otherEquipment) }}" 
                                   class="text-decoration-none">
                                    <small>{{ $otherEquipment->equipment_name }}</small>
                                </a>
                                <br>
                                <span class="badge bg-{{ $otherEquipment->availability_badge_color }} badge-sm">
                                    {{ $otherEquipment->formatted_availability_status }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
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
    margin-bottom: 0;
    color: #6c757d;
}

.maintenance-status-icon.status-scheduled { color: #ffc107; }
.maintenance-status-icon.status-in_progress { color: #17a2b8; }
.maintenance-status-icon.status-completed { color: #28a745; }
.maintenance-status-icon.status-cancelled { color: #dc3545; }

.badge-sm {
    font-size: 0.75em;
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