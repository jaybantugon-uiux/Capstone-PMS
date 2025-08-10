@extends('app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h2">Equipment Requests</h1>
                <div class="d-flex gap-2">
                    <a href="{{ route('sc.equipment-monitoring.create-request') }}" class="btn btn-success">
                        <i class="fas fa-plus me-1"></i> New Request
                    </a>
                    <a href="{{ route('sc.equipment-monitoring.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Back to Dashboard
                    </a>
                </div>
            </div>

            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <!-- Filters -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-filter me-2"></i>Filters
                    </h5>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('sc.equipment-monitoring.requests') }}" class="row g-3">
                        <div class="col-md-3">
                            <label for="status" class="form-label">Status</label>
                            <select name="status" id="status" class="form-select">
                                <option value="">All Statuses</option>
                                <option value="pending" {{ $statusFilter === 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="approved" {{ $statusFilter === 'approved' ? 'selected' : '' }}>Approved</option>
                                <option value="declined" {{ $statusFilter === 'declined' ? 'selected' : '' }}>Declined</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="usage_type" class="form-label">Usage Type</label>
                            <select name="usage_type" id="usage_type" class="form-select">
                                <option value="">All Types</option>
                                <option value="personal" {{ $typeFilter === 'personal' ? 'selected' : '' }}>Personal Use</option>
                                <option value="project_site" {{ $typeFilter === 'project_site' ? 'selected' : '' }}>Project Site</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="urgency" class="form-label">Urgency</label>
                            <select name="urgency" id="urgency" class="form-select">
                                <option value="">All Urgency Levels</option>
                                <option value="low" {{ request('urgency') === 'low' ? 'selected' : '' }}>Low</option>
                                <option value="medium" {{ request('urgency') === 'medium' ? 'selected' : '' }}>Medium</option>
                                <option value="high" {{ request('urgency') === 'high' ? 'selected' : '' }}>High</option>
                                <option value="critical" {{ request('urgency') === 'critical' ? 'selected' : '' }}>Critical</option>
                            </select>
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <div class="d-flex gap-2 w-100">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search me-1"></i> Filter
                                </button>
                                <a href="{{ route('sc.equipment-monitoring.requests') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-times me-1"></i> Clear
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Equipment Requests Table -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-truck-loading me-2"></i>Equipment Requests
                    </h5>
                </div>
                <div class="card-body">
                    @if($equipmentRequests->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Equipment Name</th>
                                        <th>Usage Type</th>
                                        <th>Project</th>
                                        <th>Quantity</th>
                                        <th>Urgency</th>
                                        <th>Status</th>
                                        <th>Requested Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($equipmentRequests as $request)
                                        <tr class="{{ $request->is_urgent ? 'table-warning' : '' }}">
                                            <td>
                                                <strong>{{ $request->equipment_name }}</strong>
                                                @if($request->is_urgent)
                                                    <span class="badge bg-danger ms-1">Urgent</span>
                                                @endif
                                                <br><small class="text-muted">{{ Str::limit($request->equipment_description, 50) }}</small>
                                            </td>
                                            <td>
                                                <span class="badge bg-{{ $request->usage_type === 'personal' ? 'info' : 'primary' }}">
                                                    {{ $request->formatted_usage_type }}
                                                </span>
                                            </td>
                                            <td>
                                                @if($request->project)
                                                    <a href="{{ route('projects.show', $request->project) }}" class="text-decoration-none">
                                                        {{ $request->project->name }}
                                                    </a>
                                                @else
                                                    <span class="text-muted">Personal Use</span>
                                                @endif
                                            </td>
                                            <td>
                                                <strong>{{ $request->quantity }}</strong> units
                                            </td>
                                            <td>
                                                <span class="badge bg-{{ $request->urgency_badge_color }}">
                                                    {{ $request->formatted_urgency }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-{{ $request->status_badge_color }}">
                                                    {{ $request->formatted_status }}
                                                </span>
                                                @if($request->approved_by)
                                                    <br><small class="text-muted">by {{ $request->approvedBy->first_name }} {{ $request->approvedBy->last_name }}</small>
                                                @endif
                                            </td>
                                            <td>
                                                {{ $request->created_at->format('M d, Y') }}
                                                <br><small class="text-muted">{{ $request->created_at->format('h:i A') }}</small>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <a href="{{ route('sc.equipment-monitoring.show-request', $request) }}" 
                                                       class="btn btn-outline-primary" title="View Details">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    @if($request->status === 'pending')
                                                        <button type="button" class="btn btn-outline-danger" 
                                                                data-bs-toggle="modal" data-bs-target="#cancelModal"
                                                                data-request-id="{{ $request->id }}"
                                                                data-request-name="{{ $request->equipment_name }}"
                                                                title="Cancel Request">
                                                            <i class="fas fa-times"></i>
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
                        <div class="d-flex justify-content-center mt-4">
                            {{ $equipmentRequests->appends(request()->query())->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-truck-loading fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No Equipment Requests Found</h5>
                            <p class="text-muted">You haven't made any equipment requests yet.</p>
                            <a href="{{ route('sc.equipment-monitoring.create-request') }}" class="btn btn-primary">
                                <i class="fas fa-plus me-1"></i> Create Your First Request
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Cancel Request Modal -->
<div class="modal fade" id="cancelModal" tabindex="-1" aria-labelledby="cancelModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="cancelModalLabel">Cancel Equipment Request</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to cancel this equipment request?</p>
                <div class="alert alert-warning">
                    <strong id="requestName"></strong>
                </div>
                <p class="text-muted">This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="cancelForm" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Yes, Cancel Request</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Cancel Request Modal
    const cancelModal = document.getElementById('cancelModal');
    if (cancelModal) {
        cancelModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const requestId = button.getAttribute('data-request-id');
            const requestName = button.getAttribute('data-request-name');
            
            document.getElementById('requestName').textContent = requestName;
            document.getElementById('cancelForm').action = `/sc/equipment-monitoring/requests/${requestId}`;
        });
    }
});
</script>
@endpush

@push('styles')
<style>
.table-hover tbody tr:hover {
    background-color: rgba(0, 0, 0, 0.075);
}
.btn-group-sm .btn {
    padding: 0.25rem 0.5rem;
    font-size: 0.75rem;
}
@media (max-width: 768px) {
    .btn-group-sm {
        display: flex;
        flex-direction: column;
    }
    .btn-group-sm .btn {
        margin-bottom: 2px;
    }
}
</style>
@endpush 