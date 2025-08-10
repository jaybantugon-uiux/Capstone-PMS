@extends('app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h2 mb-1">
                        <i class="fas fa-user-gear me-2"></i>My Equipment Dashboard
                    </h1>
                    <p class="text-muted mb-0">Manage your personal and project equipment as an Administrator</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.equipment-monitoring.create-request') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i> New Request
                    </a>
                    <a href="{{ route('admin.equipment-monitoring.my-requests') }}" class="btn btn-outline-primary">
                        <i class="fas fa-list me-1"></i> All Requests
                    </a>
                    <a href="{{ route('admin.equipment-monitoring.my-equipment') }}" class="btn btn-outline-info">
                        <i class="fas fa-cubes me-1"></i> My Equipment
                    </a>
                    <a href="{{ route('admin.equipment-monitoring.my-maintenance') }}" class="btn btn-outline-warning">
                        <i class="fas fa-wrench me-1"></i> Maintenance
                    </a>
                </div>
            </div>

            <!-- Personal Statistics Cards -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card bg-gradient-primary text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4 class="card-title">{{ $stats['pending_requests'] ?? 0 }}</h4>
                                    <p class="card-text mb-0">Pending Requests</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-clock fa-2x opacity-75"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-gradient-success text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4 class="card-title">{{ $stats['approved_requests'] ?? 0 }}</h4>
                                    <p class="card-text mb-0">Approved Requests</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-check-circle fa-2x opacity-75"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-gradient-info text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4 class="card-title">{{ ($stats['personal_equipment'] ?? 0) + ($stats['project_equipment'] ?? 0) }}</h4>
                                    <p class="card-text mb-0">Total Equipment</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-cubes fa-2x opacity-75"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-gradient-warning text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4 class="card-title">{{ $stats['upcoming_maintenance'] ?? 0 }}</h4>
                                    <p class="card-text mb-0">Upcoming Maint.</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-calendar-check fa-2x opacity-75"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Recent Equipment Requests -->
                <div class="col-lg-6 mb-4">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-clipboard-list me-2"></i>Recent Equipment Requests
                            </h5>
                            <a href="{{ route('admin.equipment-monitoring.my-requests') }}" class="btn btn-sm btn-outline-primary">
                                View All
                            </a>
                        </div>
                        <div class="card-body">
                            @if($equipmentRequests->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Equipment</th>
                                                <th>Type</th>
                                                <th>Status</th>
                                                <th>Date</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($equipmentRequests as $request)
                                                <tr>
                                                    <td>
                                                        <strong>{{ Str::limit($request->equipment_name, 25) }}</strong>
                                                        @if($request->urgency_level === 'critical')
                                                            <span class="badge bg-danger ms-1">Critical</span>
                                                        @elseif($request->urgency_level === 'high')
                                                            <span class="badge bg-warning ms-1">High</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($request->usage_type === 'personal')
                                                            <span class="badge bg-info">Personal</span>
                                                        @else
                                                            <span class="badge bg-primary">Project</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-{{ $request->status_badge_color ?? 'secondary' }}">
                                                            {{ $request->formatted_status ?? ucfirst($request->status) }}
                                                        </span>
                                                    </td>
                                                    <td>{{ $request->created_at->format('M d') }}</td>
                                                    <td>
                                                        <a href="{{ route('admin.equipment-monitoring.show-my-request', $request) }}" 
                                                           class="btn btn-sm btn-outline-primary">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-center py-4">
                                    <i class="fas fa-clipboard-list fa-3x text-muted mb-3"></i>
                                    <p class="text-muted mb-0">No equipment requests yet</p>
                                    <a href="{{ route('admin.equipment-monitoring.create-request') }}" class="btn btn-primary mt-2">
                                        <i class="fas fa-plus me-1"></i> Create First Request
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Personal Equipment -->
                <div class="col-lg-6 mb-4">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-user-cog me-2"></i>Personal Equipment
                            </h5>
                            <a href="{{ route('admin.equipment-monitoring.my-equipment') }}?usage_type=personal" class="btn btn-sm btn-outline-info">
                                View All
                            </a>
                        </div>
                        <div class="card-body">
                            @if($personalEquipment->count() > 0)
                                <div class="list-group list-group-flush">
                                    @foreach($personalEquipment->take(5) as $equipment)
                                        <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                                            <div>
                                                <h6 class="mb-1">{{ $equipment->equipment_name }}</h6>
                                                <small class="text-muted">Qty: {{ $equipment->quantity }}</small>
                                                <span class="badge bg-{{ $equipment->availability_badge_color ?? 'secondary' }} ms-2">
                                                    {{ $equipment->formatted_availability_status ?? ucfirst($equipment->availability_status) }}
                                                </span>
                                            </div>
                                            <div>
                                                <a href="{{ route('admin.equipment-monitoring.show-my-equipment', $equipment) }}" 
                                                   class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-center py-3">
                                    <i class="fas fa-user-cog fa-2x text-muted mb-2"></i>
                                    <p class="text-muted mb-0">No personal equipment yet</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Project Equipment -->
                <div class="col-lg-6 mb-4">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-project-diagram me-2"></i>Project Equipment
                            </h5>
                            <a href="{{ route('admin.equipment-monitoring.my-equipment') }}?usage_type=project_site" class="btn btn-sm btn-outline-primary">
                                View All
                            </a>
                        </div>
                        <div class="card-body">
                            @if($projectEquipment->count() > 0)
                                <div class="list-group list-group-flush">
                                    @foreach($projectEquipment->take(5) as $equipment)
                                        <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                                            <div>
                                                <h6 class="mb-1">{{ $equipment->equipment_name }}</h6>
                                                <small class="text-muted">{{ $equipment->project->name ?? 'No Project' }}</small>
                                                <span class="badge bg-{{ $equipment->availability_badge_color ?? 'secondary' }} ms-2">
                                                    {{ $equipment->formatted_availability_status ?? ucfirst($equipment->availability_status) }}
                                                </span>
                                            </div>
                                            <div>
                                                <a href="{{ route('admin.equipment-monitoring.show-my-equipment', $equipment) }}" 
                                                   class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-center py-3">
                                    <i class="fas fa-project-diagram fa-2x text-muted mb-2"></i>
                                    <p class="text-muted mb-0">No project equipment yet</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Upcoming Maintenance -->
                <div class="col-lg-6 mb-4">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-calendar-check me-2"></i>Upcoming Maintenance
                                @if(($stats['overdue_maintenance'] ?? 0) > 0)
                                    <span class="badge bg-danger ms-2">{{ $stats['overdue_maintenance'] }} Overdue</span>
                                @endif
                            </h5>
                            <a href="{{ route('admin.equipment-monitoring.my-maintenance') }}" class="btn btn-sm btn-outline-warning">
                                View All
                            </a>
                        </div>
                        <div class="card-body">
                            @if($upcomingMaintenance->count() > 0)
                                <div class="list-group list-group-flush">
                                    @foreach($upcomingMaintenance as $maintenance)
                                        <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                                            <div>
                                                <h6 class="mb-1">{{ $maintenance->monitoredEquipment->equipment_name ?? 'Equipment' }}</h6>
                                                <small class="text-muted d-block">{{ $maintenance->formatted_maintenance_type ?? ucfirst($maintenance->maintenance_type) }}</small>
                                                <small class="text-muted">
                                                    {{ $maintenance->scheduled_date->format('M d, Y g:i A') }}
                                                    @if($maintenance->scheduled_date < now())
                                                        <span class="badge bg-danger ms-1">Overdue</span>
                                                    @elseif($maintenance->scheduled_date <= now()->addDays(3))
                                                        <span class="badge bg-warning ms-1">Due Soon</span>
                                                    @endif
                                                </small>
                                            </div>
                                            <div>
                                                <span class="badge bg-{{ $maintenance->priority_badge_color ?? 'secondary' }}">
                                                    {{ $maintenance->formatted_priority ?? ucfirst($maintenance->priority) }}
                                                </span>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                <div class="text-center mt-3">
                                    <a href="{{ route('admin.equipment-monitoring.create-maintenance') }}" class="btn btn-sm btn-outline-warning">
                                        <i class="fas fa-plus me-1"></i> Schedule Maintenance
                                    </a>
                                </div>
                            @else
                                <div class="text-center py-3">
                                    <i class="fas fa-calendar-check fa-2x text-muted mb-2"></i>
                                    <p class="text-muted mb-0">No upcoming maintenance scheduled</p>
                                    @if(($personalEquipment->count() + $projectEquipment->count()) > 0)
                                        <a href="{{ route('admin.equipment-monitoring.create-maintenance') }}" class="btn btn-warning mt-2">
                                            <i class="fas fa-plus me-1"></i> Schedule Maintenance
                                        </a>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions Row -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-bolt me-2"></i>Quick Actions
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <div class="d-grid">
                                        <a href="{{ route('admin.equipment-monitoring.create-request') }}" class="btn btn-primary">
                                            <i class="fas fa-plus me-2"></i>New Equipment Request
                                        </a>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="d-grid">
                                        <a href="{{ route('admin.equipment-monitoring.create-maintenance') }}" class="btn btn-warning">
                                            <i class="fas fa-wrench me-2"></i>Schedule Maintenance
                                        </a>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="d-grid">
                                        <a href="{{ route('admin.equipment-monitoring.my-equipment') }}" class="btn btn-info">
                                            <i class="fas fa-cubes me-2"></i>View All Equipment
                                        </a>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="d-grid">
                                        <a href="{{ route('admin.equipment-monitoring.index') }}" class="btn btn-secondary">
                                            <i class="fas fa-gauge-high me-2"></i>System Overview
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
.bg-gradient-primary {
    background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
}
.bg-gradient-success {
    background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%);
}
.bg-gradient-info {
    background: linear-gradient(135deg, #17a2b8 0%, #117a8b 100%);
}
.bg-gradient-warning {
    background: linear-gradient(135deg, #ffc107 0%, #d39e00 100%);
}

.list-group-item {
    border-left: none;
    border-right: none;
}

.list-group-item:first-child {
    border-top: none;
}

.list-group-item:last-child {
    border-bottom: none;
}

.card {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    border: 1px solid rgba(0, 0, 0, 0.125);
}

.card-header {
    background-color: rgba(0, 0, 0, 0.03);
    border-bottom: 1px solid rgba(0, 0, 0, 0.125);
}

.opacity-75 {
    opacity: 0.75;
}
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // Initialize tooltips if needed
    $('[data-bs-toggle="tooltip"]').tooltip();
    
    // Auto-refresh every 5 minutes
    setTimeout(function() {
        location.reload();
    }, 300000);
});
</script>
@endpush
@endsection