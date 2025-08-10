@extends('app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h2">My Equipment Requests</h1>
                    <p class="text-muted">View and manage your equipment requests</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.equipment-monitoring.create-request') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> New Request
                    </a>
                    <a href="{{ route('admin.equipment-monitoring.my-dashboard') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Dashboard
                    </a>
                </div>
            </div>

            <!-- Filters -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" action="{{ route('admin.equipment-monitoring.my-requests') }}">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label for="status" class="form-label">Status</label>
                                <select name="status" id="status" class="form-control">
                                    <option value="">All Statuses</option>
                                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                                    <option value="declined" {{ request('status') == 'declined' ? 'selected' : '' }}>Declined</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="usage_type" class="form-label">Usage Type</label>
                                <select name="usage_type" id="usage_type" class="form-control">
                                    <option value="">All Types</option>
                                    <option value="personal" {{ request('usage_type') == 'personal' ? 'selected' : '' }}>Personal</option>
                                    <option value="project_site" {{ request('usage_type') == 'project_site' ? 'selected' : '' }}>Project Site</option>
                                </select>
                            </div>
                            <div class="col-md-3 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary me-2">
                                    <i class="fas fa-filter me-1"></i>Filter
                                </button>
                                <a href="{{ route('admin.equipment-monitoring.my-requests') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-times me-1"></i>Clear
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Equipment Requests Table -->
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            Equipment Requests
                            @if($statusFilter || $typeFilter)
                                <small class="text-muted">(Filtered)</small>
                            @endif
                        </h5>
                        <div class="d-flex gap-2">
                            <span class="badge bg-secondary">{{ $equipmentRequests->total() }} Total</span>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    @if($equipmentRequests->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Equipment</th>
                                        <th>Type</th>
                                        <th>Project</th>
                                        <th>Status</th>
                                        <th>Urgency</th>
                                        <th>Requested</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($equipmentRequests as $request)
                                        <tr class="{{ $request->is_overdue ? 'table-warning' : '' }}">
                                            <td>
                                                <div>
                                                    <strong>{{ $request->equipment_name }}</strong>
                                                    <br>
                                                    <small class="text-muted">
                                                        Qty: {{ $request->quantity }}
                                                        @if($request->estimated_cost)
                                                            • ₱{{ number_format($request->estimated_cost, 2) }}
                                                        @endif
                                                    </small>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-{{ $request->usage_type === 'personal' ? 'secondary' : 'info' }}">
                                                    {{ $request->formatted_usage_type }}
                                                </span>
                                            </td>
                                            <td>
                                                @if($request->project)
                                                    <a href="{{ route('projects.show', $request->project) }}" class="text-decoration-none">
                                                        {{ Str::limit($request->project->name, 25) }}
                                                    </a>
                                                @else
                                                    <span class="text-muted">Personal Use</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge bg-{{ $request->status_badge_color }}">
                                                    {{ $request->formatted_status }}
                                                </span>
                                                @if($request->is_overdue && $request->status === 'pending')
                                                    <br><span class="badge bg-warning mt-1">Overdue</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge bg-{{ $request->urgency_badge_color }}">
                                                    {{ $request->formatted_urgency }}
                                                </span>
                                            </td>
                                            <td>
                                                <div>
                                                    {{ $request->created_at->format('M d, Y') }}
                                                    <br><small class="text-muted">{{ $request->created_at->diffForHumans() }}</small>
                                                </div>
                                                @if($request->approved_at)
                                                    <div class="mt-1">
                                                        <small class="text-success">
                                                            <i class="fas fa-check me-1"></i>{{ $request->approved_at->format('M d, Y') }}
                                                        </small>
                                                    </div>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('admin.equipment-monitoring.show-my-request', $request) }}" 
                                                       class="btn btn-outline-primary btn-sm" 
                                                       title="View Details">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    @if($request->monitoredEquipment && $request->status === 'approved')
                                                        <a href="{{ route('admin.equipment-monitoring.show-my-equipment', $request->monitoredEquipment) }}" 
                                                           class="btn btn-outline-success btn-sm" 
                                                           title="View Equipment">
                                                            <i class="fas fa-cubes"></i>
                                                        </a>
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
                                {{ $equipmentRequests->links() }}
                            </div>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-clipboard-list fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No Equipment Requests Found</h5>
                            <p class="text-muted mb-3">
                                @if($statusFilter || $typeFilter)
                                    No requests match your current filters.
                                @else
                                    You haven't made any equipment requests yet.
                                @endif
                            </p>
                            <div class="d-flex gap-2 justify-content-center">
                                <a href="{{ route('admin.equipment-monitoring.create-request') }}" class="btn btn-primary">
                                    <i class="fas fa-plus me-1"></i>Create First Request
                                </a>
                                @if($statusFilter || $typeFilter)
                                    <a href="{{ route('admin.equipment-monitoring.my-requests') }}" class="btn btn-outline-secondary">
                                        <i class="fas fa-times me-1"></i>Clear Filters
                                    </a>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Summary Cards -->
            @if($equipmentRequests->count() > 0)
                <div class="row mt-4">
                    <div class="col-md-3">
                        <div class="card bg-light">
                            <div class="card-body text-center">
                                <h5 class="text-secondary">{{ $equipmentRequests->where('status', 'pending')->count() }}</h5>
                                <small class="text-muted">Pending Requests</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-light">
                            <div class="card-body text-center">
                                <h5 class="text-success">{{ $equipmentRequests->where('status', 'approved')->count() }}</h5>
                                <small class="text-muted">Approved Requests</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-light">
                            <div class="card-body text-center">
                                <h5 class="text-danger">{{ $equipmentRequests->where('status', 'declined')->count() }}</h5>
                                <small class="text-muted">Declined Requests</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-light">
                            <div class="card-body text-center">
                                <h5 class="text-warning">{{ $equipmentRequests->where('is_urgent', true)->count() }}</h5>
                                <small class="text-muted">Urgent Requests</small>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection