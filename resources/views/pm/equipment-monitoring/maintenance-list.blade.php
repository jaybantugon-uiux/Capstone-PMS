@extends('app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1>Maintenance Schedule</h1>
            <p class="text-muted">Monitor equipment maintenance across your managed projects</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('pm.equipment-monitoring.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i>Back to Overview
            </a>
            <a href="{{ route('pm.equipment-monitoring.equipment-list') }}" class="btn btn-outline-primary">
                <i class="fas fa-tools me-1"></i>Equipment List
            </a>
            <a href="{{ route('pm.equipment-monitoring.requests') }}" class="btn btn-outline-warning">
                <i class="fas fa-clipboard-list me-1"></i>Requests
            </a>
            <button class="btn btn-outline-info" data-bs-toggle="modal" data-bs-target="#calendarModal">
                <i class="fas fa-calendar-alt me-1"></i>Calendar View
            </button>
        </div>
    </div>

    <!-- Maintenance Statistics -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body text-center">
                    <i class="fas fa-wrench fa-2x mb-2"></i>
                    <h3>{{ $maintenances->total() }}</h3>
                    <p class="mb-0">Total Scheduled</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body text-center">
                    <i class="fas fa-clock fa-2x mb-2"></i>
                    <h3>{{ $maintenances->where('status', 'scheduled')->where('scheduled_date', '<', now())->count() }}</h3>
                    <p class="mb-0">Overdue</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body text-center">
                    <i class="fas fa-calendar-day fa-2x mb-2"></i>
                    <h3>{{ $maintenances->where('status', 'scheduled')->whereBetween('scheduled_date', [now(), now()->addDays(7)])->count() }}</h3>
                    <p class="mb-0">This Week</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body text-center">
                    <i class="fas fa-check-circle fa-2x mb-2"></i>
                    <h3>{{ $maintenances->where('status', 'completed')->count() }}</h3>
                    <p class="mb-0">Completed</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('pm.equipment-monitoring.maintenance-list') }}" class="row g-3">
                <div class="col-md-2">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All Status</option>
                        <option value="scheduled" {{ request('status') === 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                        <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                        <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="overdue" {{ request('status') === 'overdue' ? 'selected' : '' }}>Overdue</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Type</label>
                    <select name="maintenance_type" class="form-select">
                        <option value="">All Types</option>
                        <option value="routine" {{ request('maintenance_type') === 'routine' ? 'selected' : '' }}>Routine</option>
                        <option value="repair" {{ request('maintenance_type') === 'repair' ? 'selected' : '' }}>Repair</option>
                        <option value="inspection" {{ request('maintenance_type') === 'inspection' ? 'selected' : '' }}>Inspection</option>
                        <option value="calibration" {{ request('maintenance_type') === 'calibration' ? 'selected' : '' }}>Calibration</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Priority</label>
                    <select name="priority" class="form-select">
                        <option value="">All Priority</option>
                        <option value="critical" {{ request('priority') === 'critical' ? 'selected' : '' }}>Critical</option>
                        <option value="high" {{ request('priority') === 'high' ? 'selected' : '' }}>High</option>
                        <option value="medium" {{ request('priority') === 'medium' ? 'selected' : '' }}>Medium</option>
                        <option value="low" {{ request('priority') === 'low' ? 'selected' : '' }}>Low</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Project</label>
                    <select name="project_id" class="form-select">
                        <option value="">All Projects</option>
                        @foreach($managedProjects as $project)
                            <option value="{{ $project->id }}" {{ request('project_id') == $project->id ? 'selected' : '' }}>
                                {{ $project->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="fas fa-filter me-1"></i>Filter
                    </button>
                    <a href="{{ route('pm.equipment-monitoring.maintenance-list') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-times me-1"></i>Clear
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Upcoming Maintenance Alert -->
    @if($maintenances->where('status', 'scheduled')->where('scheduled_date', '<', now()->addDays(3))->count() > 0)
        <div class="alert alert-warning mb-4">
            <div class="d-flex align-items-center">
                <i class="fas fa-exclamation-triangle fa-2x me-3"></i>
                <div class="flex-grow-1">
                    <strong>Urgent Maintenance Attention Required!</strong><br>
                    {{ $maintenances->where('status', 'scheduled')->where('scheduled_date', '<', now()->addDays(3))->count() }} 
                    maintenance activities are due within the next 3 days.
                </div>
                <a href="{{ route('pm.equipment-monitoring.maintenance-list', ['status' => 'scheduled', 'urgent' => 1]) }}" 
                   class="btn btn-warning btn-sm ms-2">
                    <i class="fas fa-eye me-1"></i>View Urgent
                </a>
            </div>
        </div>
    @endif

    <!-- Maintenance Schedule List -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Maintenance Schedule</h5>
            <div class="d-flex gap-2">
                <button class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#exportModal">
                    <i class="fas fa-download me-1"></i>Export
                </button>
                <div class="dropdown">
                    <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <i class="fas fa-cog me-1"></i>Actions
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="#" onclick="showOverdueOnly()">
                            <i class="fas fa-clock me-1"></i>Show Overdue Only
                        </a></li>
                        <li><a class="dropdown-item" href="#" onclick="showThisWeek()">
                            <i class="fas fa-calendar-week me-1"></i>This Week Only
                        </a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="{{ route('pm.equipment-monitoring.report-summary') }}">
                            <i class="fas fa-chart-bar me-1"></i>Maintenance Reports
                        </a></li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="card-body">
            @if($maintenances->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Equipment</th>
                                <th>Type</th>
                                <th>Priority</th>
                                <th>Scheduled Date</th>
                                <th>Duration</th>
                                <th>Assigned To</th>
                                <th>Status</th>
                                <th>Project</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($maintenances as $maintenance)
                                <tr class="{{ $maintenance->is_overdue ? 'table-danger' : ($maintenance->is_upcoming ? 'table-warning' : '') }}">
                                    <td>
                                        <div>
                                            <h6 class="mb-1">{{ $maintenance->monitoredEquipment->equipment_name }}</h6>
                                            <small class="text-muted">{{ Str::limit($maintenance->description, 50) }}</small>
                                            @if($maintenance->monitoredEquipment->serial_number)
                                                <br><small class="text-muted">S/N: {{ $maintenance->monitoredEquipment->serial_number }}</small>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-info">{{ $maintenance->formatted_maintenance_type }}</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $maintenance->priority_badge_color }}">
                                            {{ $maintenance->formatted_priority }}
                                        </span>
                                    </td>
                                    <td>
                                        <strong>{{ $maintenance->scheduled_date->format('M d, Y') }}</strong>
                                        <br><small class="text-muted">{{ $maintenance->scheduled_date->format('g:i A') }}</small>
                                        @if($maintenance->is_overdue)
                                            <br><small class="text-danger">
                                                <i class="fas fa-clock"></i> {{ abs($maintenance->days_until_scheduled) }} days overdue
                                            </small>
                                        @elseif($maintenance->days_until_scheduled !== null)
                                            <br><small class="text-muted">
                                                {{ $maintenance->days_until_scheduled > 0 ? 'In ' . $maintenance->days_until_scheduled . ' days' : 'Today' }}
                                            </small>
                                        @endif
                                    </td>
                                    <td>
                                        @if($maintenance->estimated_duration)
                                            {{ $maintenance->estimated_duration_hours }}h
                                            @if($maintenance->actual_duration)
                                                <br><small class="text-success">
                                                    Actual: {{ $maintenance->actual_duration_hours }}h
                                                </small>
                                            @endif
                                        @else
                                            <span class="text-muted">Not specified</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div>
                                            <strong>{{ $maintenance->monitoredEquipment->user->first_name }} {{ $maintenance->monitoredEquipment->user->last_name }}</strong>
                                            <br><small class="text-muted">{{ $maintenance->monitoredEquipment->user->email }}</small>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $maintenance->status_badge_color }}">
                                            {{ $maintenance->formatted_status }}
                                        </span>
                                        @if($maintenance->performedBy && $maintenance->status === 'completed')
                                            <br><small class="text-muted">
                                                By {{ $maintenance->performedBy->first_name }}
                                            </small>
                                        @endif
                                    </td>
                                    <td>
                                        @if($maintenance->monitoredEquipment->project)
                                            <a href="{{ route('projects.show', $maintenance->monitoredEquipment->project) }}">
                                                {{ $maintenance->monitoredEquipment->project->name }}
                                            </a>
                                        @else
                                            <span class="text-muted">Personal Equipment</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('pm.equipment-monitoring.show-maintenance', $maintenance) }}" 
                                               class="btn btn-outline-primary" title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('pm.equipment-monitoring.show-equipment', $maintenance->monitoredEquipment) }}" 
                                               class="btn btn-outline-info" title="View Equipment">
                                                <i class="fas fa-tools"></i>
                                            </a>
                                            @if($maintenance->status === 'scheduled' && $maintenance->is_upcoming)
                                                <button class="btn btn-outline-warning" 
                                                        onclick="sendMaintenanceReminder({{ $maintenance->id }})" 
                                                        title="Send Reminder">
                                                    <i class="fas fa-bell"></i>
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
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div>
                        <small class="text-muted">
                            Showing {{ $maintenances->firstItem() }} to {{ $maintenances->lastItem() }} of {{ $maintenances->total() }} results
                        </small>
                    </div>
                    <div>
                        {{ $maintenances->appends(request()->query())->links() }}
                    </div>
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-wrench fa-4x text-muted mb-3"></i>
                    <h4>No Maintenance Scheduled</h4>
                    <p class="text-muted">No maintenance activities match your current filters.</p>
                    <a href="{{ route('pm.equipment-monitoring.maintenance-list') }}" class="btn btn-outline-primary">
                        <i class="fas fa-refresh me-1"></i>Clear Filters
                    </a>
                </div>
            @endif
        </div>
    </div>

    <!-- Maintenance Performance Summary -->
    @if($maintenances->where('status', 'completed')->count() > 0)
        <div class="row mt-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Maintenance Performance Summary</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3 text-center">
                                <h4 class="text-success">{{ $maintenances->where('status', 'completed')->count() }}</h4>
                                <small class="text-muted">Completed</small>
                            </div>
                            <div class="col-md-3 text-center">
                                <h4 class="text-info">
                                    {{ $maintenances->where('status', 'completed')->where('actual_duration', '<=', 'estimated_duration')->count() }}
                                </h4>
                                <small class="text-muted">On Time</small>
                            </div>
                            <div class="col-md-3 text-center">
                                <h4 class="text-warning">
                                    ₱{{ number_format($maintenances->where('status', 'completed')->sum('cost'), 2) }}
                                </h4>
                                <small class="text-muted">Total Cost</small>
                            </div>
                            <div class="col-md-3 text-center">
                                <h4 class="text-primary">
                                    {{ round($maintenances->where('status', 'completed')->avg('actual_duration') / 60, 1) }}h
                                </h4>
                                <small class="text-muted">Avg Duration</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

<!-- Calendar Modal -->
<div class="modal fade" id="calendarModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Maintenance Calendar</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="maintenanceCalendar" style="height: 500px;">
                    <!-- Calendar would be rendered here with a JavaScript library like FullCalendar -->
                    <div class="text-center py-5">
                        <i class="fas fa-calendar-alt fa-3x text-muted mb-3"></i>
                        <p class="text-muted">Calendar view will be implemented with FullCalendar.js</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Export Modal -->
<div class="modal fade" id="exportModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Export Maintenance Schedule</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('pm.equipment-monitoring.export-maintenance') }}" method="GET">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Export Format</label>
                        <select name="format" class="form-select" required>
                            <option value="csv">CSV</option>
                            <option value="excel">Excel</option>
                            <option value="pdf">PDF</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status Filter</label>
                        <select name="status" class="form-select">
                            <option value="">All Status</option>
                            <option value="scheduled">Scheduled Only</option>
                            <option value="completed">Completed Only</option>
                            <option value="overdue">Overdue Only</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Date Range</label>
                        <div class="row">
                            <div class="col-6">
                                <input type="date" name="date_from" class="form-control" placeholder="From">
                            </div>
                            <div class="col-6">
                                <input type="date" name="date_to" class="form-control" placeholder="To">
                            </div>
                        </div>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="include_costs" id="includeCosts" checked>
                        <label class="form-check-label" for="includeCosts">
                            Include cost information
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-download me-1"></i>Export
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('styles')
<style>
.table-danger {
    background-color: rgba(220, 53, 69, 0.1);
}

.table-warning {
    background-color: rgba(255, 193, 7, 0.1);
}

.maintenance-overdue {
    border-left: 3px solid #dc3545;
}

.maintenance-upcoming {
    border-left: 3px solid #ffc107;
}

.maintenance-priority-critical {
    background: linear-gradient(90deg, rgba(220, 53, 69, 0.1) 0%, transparent 100%);
}

.calendar-event {
    font-size: 12px;
    padding: 2px 4px;
    border-radius: 3px;
    margin: 1px 0;
}

.calendar-event-routine { background-color: #17a2b8; color: white; }
.calendar-event-repair { background-color: #dc3545; color: white; }
.calendar-event-inspection { background-color: #28a745; color: white; }
.calendar-event-calibration { background-color: #ffc107; color: black; }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Quick filter functions
    window.showOverdueOnly = function() {
        const url = new URL(window.location);
        url.searchParams.set('status', 'overdue');
        window.location.href = url.toString();
    };

    window.showThisWeek = function() {
        const url = new URL(window.location);
        url.searchParams.set('this_week', '1');
        window.location.href = url.toString();
    };

    // Send maintenance reminder
    window.sendMaintenanceReminder = function(maintenanceId) {
        if (confirm('Send maintenance reminder to the assigned site coordinator?')) {
            fetch(`/pm/equipment-monitoring/maintenance/${maintenanceId}/send-reminder`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Reminder sent successfully.');
                } else {
                    alert('Error sending reminder: ' + (data.message || 'Unknown error'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error sending reminder');
            });
        }
    };

    // Auto-refresh maintenance status every 5 minutes
    setInterval(function() {
        fetch('{{ route("pm.equipment-monitoring.api.stats") }}')
            .then(response => response.json())
            .then(data => {
                updateMaintenanceStats(data);
            })
            .catch(error => console.log('Error fetching maintenance stats:', error));
    }, 300000); // 5 minutes

    function updateMaintenanceStats(data) {
        // Update statistics cards
        const cards = document.querySelectorAll('.row.mb-4 .card h3');
        if (cards.length >= 4) {
            cards[0].textContent = data.maintenance_scheduled || 0;
            cards[1].textContent = data.maintenance_overdue || 0;
            cards[2].textContent = data.maintenance_this_week || 0;
            cards[3].textContent = data.maintenance_completed || 0;
        }
    }

    // Highlight overdue and upcoming maintenance
    document.querySelectorAll('tr').forEach(row => {
        if (row.classList.contains('table-danger')) {
            row.classList.add('maintenance-overdue');
        } else if (row.classList.contains('table-warning')) {
            row.classList.add('maintenance-upcoming');
        }

        // Highlight critical priority
        const priorityBadge = row.querySelector('.badge');
        if (priorityBadge && priorityBadge.textContent.includes('Critical')) {
            row.classList.add('maintenance-priority-critical');
        }
    });

    // Initialize tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>
@endpush
@endsection