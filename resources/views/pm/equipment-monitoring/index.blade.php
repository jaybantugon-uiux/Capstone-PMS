@extends('app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1>Equipment Monitoring Overview</h1>
            <p class="text-muted">Monitor and oversee equipment across your managed projects</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('pm.equipment-monitoring.equipment-list') }}" class="btn btn-primary">
                <i class="fas fa-tools me-1"></i>View Equipment
            </a>
            <a href="{{ route('pm.equipment-monitoring.requests') }}" class="btn btn-outline-warning">
                <i class="fas fa-clipboard-list me-1"></i>Equipment Requests
                @if(isset($stats['pending_requests']) && $stats['pending_requests'] > 0)
                    <span class="badge bg-danger ms-1">{{ $stats['pending_requests'] }}</span>
                @endif
            </a>
            <a href="{{ route('pm.equipment-monitoring.maintenance-list') }}" class="btn btn-outline-info">
                <i class="fas fa-wrench me-1"></i>Maintenance Schedule
            </a>
            <a href="{{ route('pm.equipment-monitoring.report-summary') }}" class="btn btn-outline-secondary">
                <i class="fas fa-chart-bar me-1"></i>Reports
            </a>
        </div>
    </div>

    <!-- Equipment Overview Statistics -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body text-center">
                    <i class="fas fa-tools fa-2x mb-2"></i>
                    <h3>{{ $stats['total_equipment'] ?? 0 }}</h3>
                    <p class="mb-0">Total Equipment</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body text-center">
                    <i class="fas fa-check-circle fa-2x mb-2"></i>
                    <h3>{{ $stats['active_equipment'] ?? 0 }}</h3>
                    <p class="mb-0">Active Equipment</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body text-center">
                    <i class="fas fa-clock fa-2x mb-2"></i>
                    <h3>{{ $stats['pending_requests'] ?? 0 }}</h3>
                    <p class="mb-0">Pending Requests</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-danger text-white">
                <div class="card-body text-center">
                    <i class="fas fa-exclamation-triangle fa-2x mb-2"></i>
                    <h3>{{ $stats['maintenance_overdue'] ?? 0 }}</h3>
                    <p class="mb-0">Overdue Maintenance</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Equipment Status Distribution -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Equipment Status Distribution</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="text-center p-3 border rounded">
                                <div class="equipment-status-indicator equipment-available mb-2"></div>
                                <h4 class="text-success">{{ $stats['equipment_available'] ?? 0 }}</h4>
                                <small class="text-muted">Available</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center p-3 border rounded">
                                <div class="equipment-status-indicator equipment-in-use mb-2"></div>
                                <h4 class="text-primary">{{ $stats['equipment_in_use'] ?? 0 }}</h4>
                                <small class="text-muted">In Use</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center p-3 border rounded">
                                <div class="equipment-status-indicator equipment-maintenance mb-2"></div>
                                <h4 class="text-warning">{{ $stats['equipment_maintenance'] ?? 0 }}</h4>
                                <small class="text-muted">Under Maintenance</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center p-3 border rounded">
                                <div class="equipment-status-indicator equipment-out-of-order mb-2"></div>
                                <h4 class="text-danger">{{ $stats['equipment_out_of_order'] ?? 0 }}</h4>
                                <small class="text-muted">Out of Order</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Equipment Requests and Upcoming Maintenance -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Recent Equipment Requests</h5>
                    <a href="{{ route('pm.equipment-monitoring.requests') }}" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-list me-1"></i>View All
                    </a>
                </div>
                <div class="card-body">
                    @if(isset($equipmentRequests) && $equipmentRequests->count() > 0)
                        @foreach($equipmentRequests->take(5) as $request)
                            <div class="mb-3 pb-3 border-bottom">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1">
                                            <a href="{{ route('pm.equipment-monitoring.show-request', $request) }}">
                                                {{ $request->equipment_name }}
                                            </a>
                                        </h6>
                                        <small class="text-muted">
                                            Requested by {{ $request->user->first_name }} {{ $request->user->last_name }}
                                        </small>
                                        <br>
                                        <small class="text-muted">
                                            {{ $request->project ? $request->project->name : 'Personal Use' }} • 
                                            {{ $request->formatted_usage_type }}
                                        </small>
                                    </div>
                                    <div class="text-end">
                                        <span class="badge bg-{{ $request->status_badge_color }}">
                                            {{ $request->formatted_status }}
                                        </span>
                                        @if($request->is_urgent)
                                            <br>
                                            <span class="badge bg-{{ $request->urgency_badge_color }} mt-1">
                                                {{ $request->formatted_urgency }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                <div class="mt-2">
                                    <small class="text-muted">
                                        {{ $request->created_at->diffForHumans() }} • 
                                        Qty: {{ $request->quantity }}
                                        @if($request->estimated_cost)
                                            • Est. Cost: ₱{{ number_format($request->estimated_cost, 2) }}
                                        @endif
                                    </small>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-clipboard-list fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No recent equipment requests</p>
                            <a href="{{ route('pm.equipment-monitoring.requests') }}" class="btn btn-outline-primary btn-sm">
                                View All Requests
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Upcoming Maintenance</h5>
                    <a href="{{ route('pm.equipment-monitoring.maintenance-list') }}" class="btn btn-outline-info btn-sm">
                        <i class="fas fa-calendar me-1"></i>Full Schedule
                    </a>
                </div>
                <div class="card-body">
                    @if(isset($upcomingEquipmentMaintenance) && $upcomingEquipmentMaintenance->count() > 0)
                        @foreach($upcomingEquipmentMaintenance as $maintenance)
                            <div class="mb-3 pb-3 border-bottom">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1">
                                            <a href="{{ route('pm.equipment-monitoring.show-maintenance', $maintenance) }}">
                                                {{ $maintenance->monitoredEquipment->equipment_name }}
                                            </a>
                                        </h6>
                                        <small class="text-muted">
                                            {{ $maintenance->formatted_maintenance_type }} • 
                                            {{ $maintenance->monitoredEquipment->user->first_name }} {{ $maintenance->monitoredEquipment->user->last_name }}
                                        </small>
                                        <br>
                                        <small class="text-muted">
                                            {{ $maintenance->monitoredEquipment->project->name ?? 'Personal Equipment' }}
                                        </small>
                                    </div>
                                    <div class="text-end">
                                        <span class="badge bg-{{ $maintenance->priority_badge_color }}">
                                            {{ $maintenance->formatted_priority }}
                                        </span>
                                        <br>
                                        <small class="text-muted">
                                            {{ $maintenance->scheduled_date->format('M d, Y') }}
                                        </small>
                                    </div>
                                </div>
                                <div class="mt-2">
                                    <small class="text-muted">
                                        {{ $maintenance->scheduled_date->diffForHumans() }}
                                        @if($maintenance->estimated_duration)
                                            • Duration: {{ $maintenance->estimated_duration_hours }}h
                                        @endif
                                    </small>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-wrench fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No upcoming maintenance scheduled</p>
                            <a href="{{ route('pm.equipment-monitoring.maintenance-list') }}" class="btn btn-outline-info btn-sm">
                                View Schedule
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Equipment Needing Attention -->
    @if(isset($equipmentNeedingAttention) && $equipmentNeedingAttention->count() > 0)
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card border-warning">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0">
                        <i class="fas fa-exclamation-triangle me-2"></i>Equipment Requiring Attention
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach($equipmentNeedingAttention as $equipment)
                            <div class="col-md-4 mb-3">
                                <div class="card border-left-warning h-100">
                                    <div class="card-body">
                                        <h6 class="card-title">{{ $equipment->equipment_name }}</h6>
                                        <p class="card-text">
                                            <small class="text-muted">
                                                {{ $equipment->project->name ?? 'Personal Use' }}<br>
                                                Managed by {{ $equipment->user->first_name }} {{ $equipment->user->last_name }}
                                            </small>
                                        </p>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="badge bg-{{ $equipment->availability_badge_color }}">
                                                {{ $equipment->formatted_availability_status }}
                                            </span>
                                            <a href="{{ route('pm.equipment-monitoring.show-equipment', $equipment) }}" class="btn btn-outline-primary btn-sm">
                                                View Details
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Project Equipment Summary -->
    @if(isset($projectEquipmentSummary) && $projectEquipmentSummary->count() > 0)
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Equipment Distribution by Project</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Project Name</th>
                                    <th class="text-center">Total Equipment</th>
                                    <th class="text-center">Available</th>
                                    <th class="text-center">In Use</th>
                                    <th class="text-center">Issues</th>
                                    <th class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($projectEquipmentSummary as $project)
                                    <tr>
                                        <td>
                                            <a href="{{ route('projects.show', $project) }}">{{ $project->name }}</a>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-primary">{{ $project->equipment_count }}</span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-success">{{ $project->equipment_available }}</span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-info">{{ $project->equipment_in_use }}</span>
                                        </td>
                                        <td class="text-center">
                                            @if($project->equipment_issues > 0)
                                                <span class="badge bg-warning">{{ $project->equipment_issues }}</span>
                                            @else
                                                <span class="text-muted">0</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <a href="{{ route('pm.equipment-monitoring.equipment-list', ['project_id' => $project->id]) }}" 
                                               class="btn btn-outline-primary btn-sm">
                                                <i class="fas fa-eye me-1"></i>View
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

@push('styles')
<style>
.equipment-status-indicator {
    width: 20px;
    height: 20px;
    border-radius: 50%;
    display: inline-block;
    margin: 0 auto;
}
.equipment-available { background-color: #28a745; }
.equipment-in-use { background-color: #007bff; }
.equipment-maintenance { background-color: #ffc107; }
.equipment-out-of-order { background-color: #dc3545; }

.border-left-warning {
    border-left: 3px solid #ffc107 !important;
}

.card-hover:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    transition: all 0.2s ease-in-out;
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-refresh stats every 5 minutes
    setInterval(function() {
        fetch('{{ route("pm.equipment-monitoring.api.stats") }}')
            .then(response => response.json())
            .then(data => {
                // Update dashboard statistics
                updateStatistics(data);
            })
            .catch(error => console.log('Error fetching stats:', error));
    }, 300000); // 5 minutes

    function updateStatistics(data) {
        // Update main statistics cards
        const statElements = {
            'total_equipment': document.querySelector('.card.bg-primary h3'),
            'active_equipment': document.querySelector('.card.bg-success h3'),
            'pending_requests': document.querySelector('.card.bg-warning h3'),
            'maintenance_overdue': document.querySelector('.card.bg-danger h3')
        };

        Object.keys(statElements).forEach(key => {
            if (statElements[key] && data[key] !== undefined) {
                statElements[key].textContent = data[key];
            }
        });

        // Update status distribution
        const statusElements = {
            'equipment_available': document.querySelector('.text-success'),
            'equipment_in_use': document.querySelector('.text-primary'),
            'equipment_maintenance': document.querySelector('.text-warning'),
            'equipment_out_of_order': document.querySelector('.text-danger')
        };

        Object.keys(statusElements).forEach(key => {
            if (statusElements[key] && data[key] !== undefined) {
                statusElements[key].textContent = data[key];
            }
        });
    }
});
</script>
@endpush
@endsection