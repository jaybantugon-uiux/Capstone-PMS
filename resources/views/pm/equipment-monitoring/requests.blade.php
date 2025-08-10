@extends('app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1>Equipment Requests</h1>
            <p class="text-muted">Monitor equipment requests from your project teams</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('pm.equipment-monitoring.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i>Back to Overview
            </a>
            <a href="{{ route('pm.equipment-monitoring.equipment-list') }}" class="btn btn-outline-primary">
                <i class="fas fa-tools me-1"></i>View Equipment
            </a>
            <a href="{{ route('pm.equipment-monitoring.maintenance-list') }}" class="btn btn-outline-info">
                <i class="fas fa-wrench me-1"></i>Maintenance
            </a>
        </div>
    </div>

    <!-- Request Statistics -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body text-center">
                    <i class="fas fa-clipboard-list fa-2x mb-2"></i>
                    <h3>{{ $equipmentRequests->total() }}</h3>
                    <p class="mb-0">Total Requests</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body text-center">
                    <i class="fas fa-clock fa-2x mb-2"></i>
                    <h3>{{ $equipmentRequests->where('status', 'pending')->count() }}</h3>
                    <p class="mb-0">Pending Review</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body text-center">
                    <i class="fas fa-check-circle fa-2x mb-2"></i>
                    <h3>{{ $equipmentRequests->where('status', 'approved')->count() }}</h3>
                    <p class="mb-0">Approved</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-danger text-white">
                <div class="card-body text-center">
                    <i class="fas fa-exclamation-triangle fa-2x mb-2"></i>
                    <h3>{{ $equipmentRequests->where('urgency_level', 'critical')->where('status', 'pending')->count() }}</h3>
                    <p class="mb-0">Critical Pending</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('pm.equipment-monitoring.requests') }}" class="row g-3">
                <div class="col-md-2">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All Statuses</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="declined" {{ request('status') === 'declined' ? 'selected' : '' }}>Declined</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Urgency</label>
                    <select name="urgency" class="form-select">
                        <option value="">All Urgency</option>
                        <option value="critical" {{ request('urgency') === 'critical' ? 'selected' : '' }}>Critical</option>
                        <option value="high" {{ request('urgency') === 'high' ? 'selected' : '' }}>High</option>
                        <option value="medium" {{ request('urgency') === 'medium' ? 'selected' : '' }}>Medium</option>
                        <option value="low" {{ request('urgency') === 'low' ? 'selected' : '' }}>Low</option>
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
                <div class="col-md-2">
                    <label class="form-label">Usage Type</label>
                    <select name="usage_type" class="form-select">
                        <option value="">All Types</option>
                        <option value="personal" {{ request('usage_type') === 'personal' ? 'selected' : '' }}>Personal</option>
                        <option value="project_site" {{ request('usage_type') === 'project_site' ? 'selected' : '' }}>Project Site</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="fas fa-filter me-1"></i>Filter
                    </button>
                    <a href="{{ route('pm.equipment-monitoring.requests') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-times me-1"></i>Clear
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Equipment Requests List -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Equipment Requests</h5>
            <div class="d-flex gap-2">
                <button class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#exportModal">
                    <i class="fas fa-download me-1"></i>Export
                </button>
                <div class="dropdown">
                    <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <i class="fas fa-cog me-1"></i>Actions
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="#" onclick="showPendingOnly()">
                            <i class="fas fa-filter me-1"></i>Show Pending Only
                        </a></li>
                        <li><a class="dropdown-item" href="#" onclick="showUrgentOnly()">
                            <i class="fas fa-fire me-1"></i>Show Urgent Only
                        </a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="{{ route('pm.equipment-monitoring.report-summary') }}">
                            <i class="fas fa-chart-bar me-1"></i>View Reports
                        </a></li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="card-body">
            @if($equipmentRequests->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Equipment Details</th>
                                <th>Requester</th>
                                <th>Project</th>
                                <th>Usage Type</th>
                                <th>Urgency</th>
                                <th>Status</th>
                                <th>Requested</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($equipmentRequests as $request)
                                <tr class="{{ $request->is_urgent && $request->status === 'pending' ? 'table-warning' : '' }}">
                                    <td>
                                        <div>
                                            <h6 class="mb-1">{{ $request->equipment_name }}</h6>
                                            <small class="text-muted">{{ Str::limit($request->equipment_description, 60) }}</small>
                                            <br>
                                            <small class="text-muted">
                                                Qty: {{ $request->quantity }}
                                                @if($request->estimated_cost)
                                                    • Est. Cost: ₱{{ number_format($request->estimated_cost, 2) }}
                                                @endif
                                            </small>
                                        </div>
                                    </td>
                                    <td>
                                        <div>
                                            <strong>{{ $request->user->first_name }} {{ $request->user->last_name }}</strong>
                                            <br><small class="text-muted">{{ $request->user->email }}</small>
                                        </div>
                                    </td>
                                    <td>
                                        @if($request->project)
                                            <a href="{{ route('projects.show', $request->project) }}">{{ $request->project->name }}</a>
                                        @else
                                            <span class="text-muted">Personal Use</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $request->usage_type === 'project_site' ? 'primary' : 'secondary' }}">
                                            {{ $request->formatted_usage_type }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $request->urgency_badge_color }}">
                                            {{ $request->formatted_urgency }}
                                        </span>
                                        @if($request->is_overdue)
                                            <br><small class="text-danger">
                                                <i class="fas fa-clock"></i> Overdue
                                            </small>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $request->status_badge_color }}">
                                            {{ $request->formatted_status }}
                                        </span>
                                        @if($request->status === 'approved' && $request->approved_at)
                                            <br><small class="text-muted">{{ $request->approved_at->format('M d, Y') }}</small>
                                        @elseif($request->status === 'declined')
                                            <br><small class="text-danger">Declined</small>
                                        @endif
                                    </td>
                                    <td>
                                        {{ $request->created_at->format('M d, Y') }}
                                        <br><small class="text-muted">{{ $request->created_at->diffForHumans() }}</small>
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('pm.equipment-monitoring.show-request', $request) }}" 
                                               class="btn btn-outline-primary" title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            @if($request->monitoredEquipment)
                                                <a href="{{ route('pm.equipment-monitoring.show-equipment', $request->monitoredEquipment) }}" 
                                                   class="btn btn-outline-info" title="View Equipment">
                                                    <i class="fas fa-tools"></i>
                                                </a>
                                            @endif
                                            @if($request->status === 'pending' && $request->is_urgent)
                                                <button class="btn btn-outline-warning" 
                                                        onclick="flagUrgent({{ $request->id }})" 
                                                        title="Flag as Urgent">
                                                    <i class="fas fa-flag"></i>
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
                            Showing {{ $equipmentRequests->firstItem() }} to {{ $equipmentRequests->lastItem() }} of {{ $equipmentRequests->total() }} results
                        </small>
                    </div>
                    <div>
                        {{ $equipmentRequests->appends(request()->query())->links() }}
                    </div>
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-clipboard-list fa-4x text-muted mb-3"></i>
                    <h4>No Equipment Requests Found</h4>
                    <p class="text-muted">No equipment requests match your current filters.</p>
                    <a href="{{ route('pm.equipment-monitoring.requests') }}" class="btn btn-outline-primary">
                        <i class="fas fa-refresh me-1"></i>Clear Filters
                    </a>
                </div>
            @endif
        </div>
    </div>

    <!-- Pending Requests Alert -->
    @if($equipmentRequests->where('status', 'pending')->where('urgency_level', 'critical')->count() > 0)
        <div class="alert alert-warning mt-4">
            <div class="d-flex align-items-center">
                <i class="fas fa-exclamation-triangle fa-2x me-3"></i>
                <div class="flex-grow-1">
                    <strong>Critical Equipment Requests Pending!</strong><br>
                    {{ $equipmentRequests->where('status', 'pending')->where('urgency_level', 'critical')->count() }} 
                    critical equipment requests are waiting for admin approval.
                </div>
                <a href="{{ route('pm.equipment-monitoring.requests', ['urgency' => 'critical', 'status' => 'pending']) }}" 
                   class="btn btn-warning btn-sm ms-2">
                    <i class="fas fa-eye me-1"></i>Review Critical
                </a>
            </div>
        </div>
    @endif
</div>

<!-- Export Modal -->
<div class="modal fade" id="exportModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Export Equipment Requests</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('pm.equipment-monitoring.export-requests') }}" method="GET">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Export Format</label>
                        <select name="format" class="form-select" required>
                            <option value="csv">CSV</option>
                            <option value="excel">Excel</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status Filter</label>
                        <select name="status" class="form-select">
                            <option value="">All Statuses</option>
                            <option value="pending">Pending Only</option>
                            <option value="approved">Approved Only</option>
                            <option value="declined">Declined Only</option>
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
                        <input class="form-check-input" type="checkbox" name="include_justification" id="includeJustification" checked>
                        <label class="form-check-label" for="includeJustification">
                            Include justification and notes
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
.table-warning {
    background-color: rgba(255, 193, 7, 0.1);
}

.urgency-critical {
    border-left: 3px solid #dc3545;
}

.urgency-high {
    border-left: 3px solid #fd7e14;
}

.badge-pulse {
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% { opacity: 1; }
    50% { opacity: 0.5; }
    100% { opacity: 1; }
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Quick filter functions
    window.showPendingOnly = function() {
        const url = new URL(window.location);
        url.searchParams.set('status', 'pending');
        window.location.href = url.toString();
    };

    window.showUrgentOnly = function() {
        const url = new URL(window.location);
        url.searchParams.set('urgency', 'critical');
        url.searchParams.set('status', 'pending');
        window.location.href = url.toString();
    };

    // Flag urgent function
    window.flagUrgent = function(requestId) {
        if (confirm('Flag this request for urgent admin attention?')) {
            fetch(`/pm/equipment-monitoring/requests/${requestId}/flag-urgent`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Request flagged for urgent attention.');
                    location.reload();
                } else {
                    alert('Error flagging request: ' + (data.message || 'Unknown error'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error flagging request');
            });
        }
    };

    // Add pulse animation to critical badges
    document.querySelectorAll('.badge').forEach(badge => {
        if (badge.textContent.includes('Critical') && badge.classList.contains('bg-danger')) {
            badge.classList.add('badge-pulse');
        }
    });
});
</script>
@endpush
@endsection