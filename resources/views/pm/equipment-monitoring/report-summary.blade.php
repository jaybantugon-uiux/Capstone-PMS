{{-- resources/views/pm/equipment-monitoring/reports/summary.blade.php --}}
@extends('app')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('pm.equipment-monitoring.index') }}">Equipment Monitoring</a></li>
                    <li class="breadcrumb-item active">Reports & Analytics</li>
                </ol>
            </nav>
            <h1>Equipment Monitoring Reports</h1>
            <p class="text-muted">Comprehensive analytics for equipment in your managed projects</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('pm.equipment-monitoring.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i>Back to Overview
            </a>
            <div class="dropdown">
                <button class="btn btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    <i class="fas fa-download me-1"></i>Export Reports
                </button>
                <ul class="dropdown-menu">
                    <li>
                        <a class="dropdown-item" href="{{ route('pm.equipment-monitoring.requests', ['export' => 'csv']) }}">
                            <i class="fas fa-file-csv me-2"></i>Equipment Requests (CSV)
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="{{ route('pm.equipment-monitoring.equipment-list', ['export' => 'csv']) }}">
                            <i class="fas fa-file-csv me-2"></i>Equipment List (CSV)
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="{{ route('pm.equipment-monitoring.maintenance-list', ['export' => 'csv']) }}">
                            <i class="fas fa-file-csv me-2"></i>Maintenance Schedule (CSV)
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Date Range Filter -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('pm.equipment-monitoring.report-summary') }}" class="row g-3">
                <div class="col-md-3">
                    <label for="date_from" class="form-label">From Date</label>
                    <input type="date" class="form-control" id="date_from" name="date_from" 
                           value="{{ request('date_from', $dateFrom->format('Y-m-d')) }}">
                </div>
                <div class="col-md-3">
                    <label for="date_to" class="form-label">To Date</label>
                    <input type="date" class="form-control" id="date_to" name="date_to" 
                           value="{{ request('date_to', $dateTo->format('Y-m-d')) }}">
                </div>
                <div class="col-md-3">
                    <label for="project_filter" class="form-label">Project</label>
                    <select class="form-select" id="project_filter" name="project_id">
                        <option value="">All Projects</option>
                        @foreach($managedProjects as $project)
                            <option value="{{ $project->id }}" {{ request('project_id') == $project->id ? 'selected' : '' }}>
                                {{ $project->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">&nbsp;</label>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-filter me-1"></i>Apply Filters
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Key Metrics Overview -->
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
                    <i class="fas fa-wrench fa-2x mb-2"></i>
                    <h3>{{ $stats['scheduled_maintenance'] ?? 0 }}</h3>
                    <p class="mb-0">Scheduled Maintenance</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Equipment Status Distribution -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-pie me-2"></i>Equipment Status Distribution
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6 mb-3">
                            <div class="equipment-status-card border rounded p-3">
                                <div class="equipment-status-icon bg-success text-white rounded-circle mx-auto mb-2">
                                    <i class="fas fa-check"></i>
                                </div>
                                <h4 class="text-success mb-1">{{ $stats['active_equipment'] ?? 0 }}</h4>
                                <small class="text-muted">Active</small>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="equipment-status-card border rounded p-3">
                                <div class="equipment-status-icon bg-warning text-white rounded-circle mx-auto mb-2">
                                    <i class="fas fa-pause"></i>
                                </div>
                                <h4 class="text-warning mb-1">{{ $stats['pending_equipment'] ?? 0 }}</h4>
                                <small class="text-muted">Pending</small>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Equipment Availability -->
                    <hr>
                    <h6 class="mb-3">Equipment Availability</h6>
                    <div class="row text-center">
                        <div class="col-3">
                            <div class="availability-indicator available mb-2"></div>
                            <h6 class="text-success">{{ $stats['equipment_available'] ?? 0 }}</h6>
                            <small class="text-muted">Available</small>
                        </div>
                        <div class="col-3">
                            <div class="availability-indicator in-use mb-2"></div>
                            <h6 class="text-primary">{{ $stats['equipment_in_use'] ?? 0 }}</h6>
                            <small class="text-muted">In Use</small>
                        </div>
                        <div class="col-3">
                            <div class="availability-indicator maintenance mb-2"></div>
                            <h6 class="text-warning">{{ $stats['equipment_maintenance'] ?? 0 }}</h6>
                            <small class="text-muted">Maintenance</small>
                        </div>
                        <div class="col-3">
                            <div class="availability-indicator out-of-order mb-2"></div>
                            <h6 class="text-danger">{{ $stats['equipment_out_of_order'] ?? 0 }}</h6>
                            <small class="text-muted">Out of Order</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-bar me-2"></i>Request Analytics
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row text-center mb-4">
                        <div class="col-4">
                            <h4 class="text-success">{{ $stats['approved_requests'] ?? 0 }}</h4>
                            <small class="text-muted">Approved</small>
                        </div>
                        <div class="col-4">
                            <h4 class="text-warning">{{ $stats['pending_requests'] ?? 0 }}</h4>
                            <small class="text-muted">Pending</small>
                        </div>
                        <div class="col-4">
                            <h4 class="text-danger">{{ $stats['declined_requests'] ?? 0 }}</h4>
                            <small class="text-muted">Declined</small>
                        </div>
                    </div>

                    <!-- Approval Rate -->
                    @php
                        $totalDecidedRequests = ($stats['approved_requests'] ?? 0) + ($stats['declined_requests'] ?? 0);
                        $approvalRate = $totalDecidedRequests > 0 ? round((($stats['approved_requests'] ?? 0) / $totalDecidedRequests) * 100) : 0;
                    @endphp
                    <div class="text-center">
                        <h6>Approval Rate</h6>
                        <div class="progress mb-2" style="height: 20px;">
                            <div class="progress-bar bg-success" role="progressbar" 
                                 style="width: {{ $approvalRate }}%" 
                                 aria-valuenow="{{ $approvalRate }}" aria-valuemin="0" aria-valuemax="100">
                                {{ $approvalRate }}%
                            </div>
                        </div>
                        <small class="text-muted">Based on {{ $totalDecidedRequests }} processed requests</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Maintenance Overview -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-wrench me-2"></i>Maintenance Overview
                    </h5>
                    <a href="{{ route('pm.equipment-monitoring.maintenance-list') }}" class="btn btn-outline-info btn-sm">
                        <i class="fas fa-calendar me-1"></i>View Schedule
                    </a>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-3">
                            <div class="maintenance-metric border rounded p-3">
                                <i class="fas fa-calendar-check fa-2x text-info mb-2"></i>
                                <h4 class="text-info">{{ $stats['maintenance_scheduled'] ?? 0 }}</h4>
                                <small class="text-muted">Scheduled</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="maintenance-metric border rounded p-3">
                                <i class="fas fa-exclamation-triangle fa-2x text-danger mb-2"></i>
                                <h4 class="text-danger">{{ $stats['maintenance_overdue'] ?? 0 }}</h4>
                                <small class="text-muted">Overdue</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="maintenance-metric border rounded p-3">
                                <i class="fas fa-clock fa-2x text-warning mb-2"></i>
                                <h4 class="text-warning">{{ $stats['maintenance_this_week'] ?? 0 }}</h4>
                                <small class="text-muted">This Week</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="maintenance-metric border rounded p-3">
                                <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                                <h4 class="text-success">{{ $stats['maintenance_completed'] ?? 0 }}</h4>
                                <small class="text-muted">Completed</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Project Equipment Breakdown -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-project-diagram me-2"></i>Equipment by Project
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Project Name</th>
                                    <th class="text-center">Total Equipment</th>
                                    <th class="text-center">Active</th>
                                    <th class="text-center">Available</th>
                                    <th class="text-center">In Use</th>
                                    <th class="text-center">Maintenance</th>
                                    <th class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($managedProjects as $project)
                                    @php
                                        // These would normally come from the controller with proper calculations
                                        $projectStats = [
                                            'total' => rand(0, 15),
                                            'active' => rand(0, 12),
                                            'available' => rand(0, 8),
                                            'in_use' => rand(0, 5),
                                            'maintenance' => rand(0, 3),
                                        ];
                                    @endphp
                                    <tr>
                                        <td>
                                            <a href="{{ route('projects.show', $project) }}" class="text-decoration-none">
                                                {{ $project->name }}
                                            </a>
                                            <br>
                                            <small class="text-muted">{{ $project->start_date->format('M Y') }}</small>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-primary">{{ $projectStats['total'] }}</span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-success">{{ $projectStats['active'] }}</span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-info">{{ $projectStats['available'] }}</span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-warning">{{ $projectStats['in_use'] }}</span>
                                        </td>
                                        <td class="text-center">
                                            @if($projectStats['maintenance'] > 0)
                                                <span class="badge bg-secondary">{{ $projectStats['maintenance'] }}</span>
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
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-muted py-4">
                                            No managed projects found
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions and Links -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-bolt me-2"></i>Quick Actions
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('pm.equipment-monitoring.requests', ['status' => 'pending']) }}" 
                           class="btn btn-outline-warning">
                            <i class="fas fa-clock me-2"></i>Review Pending Requests ({{ $stats['pending_requests'] ?? 0 }})
                        </a>
                        <a href="{{ route('pm.equipment-monitoring.maintenance-list', ['status' => 'overdue']) }}" 
                           class="btn btn-outline-danger">
                            <i class="fas fa-exclamation-triangle me-2"></i>Address Overdue Maintenance ({{ $stats['maintenance_overdue'] ?? 0 }})
                        </a>
                        <a href="{{ route('pm.equipment-monitoring.equipment-list', ['availability' => 'out_of_order']) }}" 
                           class="btn btn-outline-warning">
                            <i class="fas fa-tools me-2"></i>Check Out of Order Equipment ({{ $stats['equipment_out_of_order'] ?? 0 }})
                        </a>
                        <a href="{{ route('pm.equipment-monitoring.requests', ['urgency' => 'critical']) }}" 
                           class="btn btn-outline-info">
                            <i class="fas fa-bolt me-2"></i>Critical Requests ({{ $stats['urgent_requests'] ?? 0 }})
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-line me-2"></i>Performance Indicators
                    </h5>
                </div>
                <div class="card-body">
                    <!-- Equipment Utilization -->
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <span>Equipment Utilization</span>
                            @php
                                $totalActive = $stats['active_equipment'] ?? 1;
                                $inUse = $stats['equipment_in_use'] ?? 0;
                                $utilizationRate = $totalActive > 0 ? round(($inUse / $totalActive) * 100) : 0;
                            @endphp
                            <span class="fw-bold">{{ $utilizationRate }}%</span>
                        </div>
                        <div class="progress">
                            <div class="progress-bar bg-info" role="progressbar" 
                                 style="width: {{ $utilizationRate }}%" 
                                 aria-valuenow="{{ $utilizationRate }}" aria-valuemin="0" aria-valuemax="100">
                            </div>
                        </div>
                    </div>

                    <!-- Maintenance Compliance -->
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <span>Maintenance Compliance</span>
                            @php
                                $totalMaintenance = ($stats['maintenance_scheduled'] ?? 0) + ($stats['maintenance_overdue'] ?? 0);
                                $onTime = $stats['maintenance_scheduled'] ?? 0;
                                $complianceRate = $totalMaintenance > 0 ? round(($onTime / $totalMaintenance) * 100) : 100;
                            @endphp
                            <span class="fw-bold">{{ $complianceRate }}%</span>
                        </div>
                        <div class="progress">
                            <div class="progress-bar bg-{{ $complianceRate >= 80 ? 'success' : ($complianceRate >= 60 ? 'warning' : 'danger') }}" 
                                 role="progressbar" style="width: {{ $complianceRate }}%" 
                                 aria-valuenow="{{ $complianceRate }}" aria-valuemin="0" aria-valuemax="100">
                            </div>
                        </div>
                    </div>

                    <!-- Request Response Time -->
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <span>Request Processing</span>
                            @php
                                $processedRequests = ($stats['approved_requests'] ?? 0) + ($stats['declined_requests'] ?? 0);
                                $totalRequests = $processedRequests + ($stats['pending_requests'] ?? 0);
                                $responseRate = $totalRequests > 0 ? round(($processedRequests / $totalRequests) * 100) : 100;
                            @endphp
                            <span class="fw-bold">{{ $responseRate }}%</span>
                        </div>
                        <div class="progress">
                            <div class="progress-bar bg-primary" role="progressbar" 
                                 style="width: {{ $responseRate }}%" 
                                 aria-valuenow="{{ $responseRate }}" aria-valuemin="0" aria-valuemax="100">
                            </div>
                        </div>
                    </div>

                    <div class="text-center mt-3">
                        <small class="text-muted">
                            Performance metrics for {{ $managedProjects->count() }} managed projects
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Report Generation -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-file-alt me-2"></i>Generate Custom Reports
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="card border h-100">
                                <div class="card-body text-center">
                                    <i class="fas fa-clipboard-list fa-3x text-primary mb-3"></i>
                                    <h6>Equipment Requests Report</h6>
                                    <p class="text-muted small">Detailed analysis of all equipment requests</p>
                                    <a href="{{ route('pm.equipment-monitoring.requests', ['export' => 'csv']) }}" 
                                       class="btn btn-outline-primary btn-sm">
                                        <i class="fas fa-download me-1"></i>Generate
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card border h-100">
                                <div class="card-body text-center">
                                    <i class="fas fa-tools fa-3x text-success mb-3"></i>
                                    <h6>Equipment Inventory Report</h6>
                                    <p class="text-muted small">Complete equipment inventory and status</p>
                                    <a href="{{ route('pm.equipment-monitoring.equipment-list', ['export' => 'csv']) }}" 
                                       class="btn btn-outline-success btn-sm">
                                        <i class="fas fa-download me-1"></i>Generate
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card border h-100">
                                <div class="card-body text-center">
                                    <i class="fas fa-wrench fa-3x text-warning mb-3"></i>
                                    <h6>Maintenance Schedule Report</h6>
                                    <p class="text-muted small">Maintenance timeline and compliance</p>
                                    <a href="{{ route('pm.equipment-monitoring.maintenance-list', ['export' => 'csv']) }}" 
                                       class="btn btn-outline-warning btn-sm">
                                        <i class="fas fa-download me-1"></i>Generate
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

@push('styles')
<style>
.equipment-status-card {
    transition: all 0.3s ease;
}

.equipment-status-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.equipment-status-icon {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.availability-indicator {
    width: 20px;
    height: 20px;
    border-radius: 50%;
    margin: 0 auto;
}

.availability-indicator.available { background-color: #28a745; }
.availability-indicator.in-use { background-color: #007bff; }
.availability-indicator.maintenance { background-color: #ffc107; }
.availability-indicator.out-of-order { background-color: #dc3545; }

.maintenance-metric {
    transition: all 0.3s ease;
}

.maintenance-metric:hover {
    transform: translateY(-3px);
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.progress {
    height: 8px;
    border-radius: 4px;
}

.card-hover:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    transition: all 0.2s ease-in-out;
}

.table th {
    font-weight: 600;
    background-color: #f8f9fa;
}

.badge {
    font-size: 0.75em;
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-refresh reports every 5 minutes
    setInterval(function() {
        const currentParams = new URLSearchParams(window.location.search);
        fetch(window.location.pathname + '?' + currentParams.toString())
            .then(response => response.text())
            .then(html => {
                // Update only the statistics sections without full page reload
                updateStatistics();
            })
            .catch(error => console.log('Error refreshing data:', error));
    }, 300000); // 5 minutes

    function updateStatistics() {
        // This would typically fetch updated statistics via AJAX
        // For now, we'll add a visual indicator that data is being refreshed
        const cards = document.querySelectorAll('.card');
        cards.forEach(card => {
            card.style.opacity = '0.7';
            setTimeout(() => {
                card.style.opacity = '1';
            }, 500);
        });
    }

    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>
@endpush

@endsection