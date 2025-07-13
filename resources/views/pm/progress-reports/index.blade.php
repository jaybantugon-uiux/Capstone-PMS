@extends('app')

@section('title', 'My Progress Reports')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-file-chart-line me-2"></i>My Progress Reports
            </h1>
            <p class="mb-0 text-muted">Manage progress reports for your projects</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.progress-reports.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i>New Report
            </a>
            <a href="{{ route('pm.dashboard') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i>Back to Dashboard
            </a>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                My Total Reports
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $reports->total() }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-file-alt fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Sent Reports
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $reports->where('status', 'sent')->count() }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-paper-plane fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Viewed Reports
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $reports->where('status', 'viewed')->count() }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-eye fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Total Views
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $reports->sum('view_count') }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-chart-bar fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card shadow mb-4">
        <div class="card-header">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-filter me-2"></i>Filter Reports
            </h6>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('pm.progress-reports.index') }}">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="status">Status</label>
                            <select name="status" id="status" class="form-control">
                                <option value="">All Status</option>
                                <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                                <option value="sent" {{ request('status') === 'sent' ? 'selected' : '' }}>Sent</option>
                                <option value="viewed" {{ request('status') === 'viewed' ? 'selected' : '' }}>Viewed</option>
                                <option value="archived" {{ request('status') === 'archived' ? 'selected' : '' }}>Archived</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="client_id">Client</label>
                            <select name="client_id" id="client_id" class="form-control">
                                <option value="">All Clients</option>
                                @foreach($clients as $client)
                                    <option value="{{ $client->id }}" {{ request('client_id') == $client->id ? 'selected' : '' }}>
                                        {{ $client->first_name }} {{ $client->last_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="project_id">My Projects</label>
                            <select name="project_id" id="project_id" class="form-control">
                                <option value="">All Projects</option>
                                @foreach($projects as $project)
                                    <option value="{{ $project->id }}" {{ request('project_id') == $project->id ? 'selected' : '' }}>
                                        {{ $project->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="search">Search</label>
                            <input type="text" name="search" id="search" class="form-control" 
                                   placeholder="Search title..." value="{{ request('search') }}">
                        </div>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-md-6">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="date_from">Date From</label>
                                    <input type="date" name="date_from" id="date_from" class="form-control" 
                                           value="{{ request('date_from') }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="date_to">Date To</label>
                                    <input type="date" name="date_to" id="date_to" class="form-control" 
                                           value="{{ request('date_to') }}">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 d-flex align-items-end">
                        <div class="form-group w-100">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="fas fa-search me-1"></i>Filter
                            </button>
                            <a href="{{ route('pm.progress-reports.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-1"></i>Clear
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Reports Table -->
    <div class="card shadow">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">My Progress Reports</h6>
            <span class="badge bg-primary">PM Access</span>
        </div>
        <div class="card-body">
            @if($reports->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered" id="reportsTable">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Client</th>
                                <th>Project</th>
                                <th>Status</th>
                                <th>Views</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($reports as $report)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div>
                                                <strong>{{ Str::limit($report->title, 40) }}</strong>
                                                @if($report->hasAttachment())
                                                    <br><i class="fas fa-paperclip text-muted me-1"></i>
                                                    <small class="text-muted">{{ $report->original_filename }}</small>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        {{ $report->client->first_name }} {{ $report->client->last_name }}
                                        <br><small class="text-muted">{{ $report->client->email }}</small>
                                    </td>
                                    <td>
                                        @if($report->project)
                                            <span class="badge bg-info">{{ Str::limit($report->project->name, 25) }}</span>
                                        @else
                                            <span class="text-muted">General</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $report->status_color }}">
                                            {{ $report->formatted_status }}
                                        </span>
                                        @if($report->sent_at)
                                            <br><small class="text-muted">
                                                Sent {{ $report->sent_at->diffForHumans() }}
                                            </small>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-light text-dark">{{ $report->view_count }}</span>
                                        @if($report->first_viewed_at)
                                            <br><small class="text-muted">
                                                First viewed {{ $report->first_viewed_at->diffForHumans() }}
                                            </small>
                                        @endif
                                    </td>
                                    <td>
                                        {{ $report->created_at->format('M d, Y') }}
                                        <br><small class="text-muted">{{ $report->created_at->diffForHumans() }}</small>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('admin.progress-reports.show', $report) }}" 
                                               class="btn btn-sm btn-outline-primary" title="View">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.progress-reports.edit', $report) }}" 
                                               class="btn btn-sm btn-outline-secondary" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button type="button" class="btn btn-sm btn-outline-danger" 
                                                    onclick="deleteReport({{ $report->id }})" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
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
                            Showing {{ $reports->firstItem() }} to {{ $reports->lastItem() }} 
                            of {{ $reports->total() }} results
                        </small>
                    </div>
                    <div>
                        {{ $reports->appends(request()->query())->links() }}
                    </div>
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-file-alt fa-4x text-muted mb-3"></i>
                    <h5 class="text-muted">No Progress Reports Found</h5>
                    <p class="text-muted">Start by creating your first progress report for your projects.</p>
                    <a href="{{ route('admin.progress-reports.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i>Create First Report
                    </a>
                </div>
            @endif
        </div>
    </div>

    <!-- Quick Actions Card -->
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-primary">Quick Actions</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <a href="{{ route('admin.progress-reports.create') }}" class="btn btn-outline-primary w-100 mb-2 h-100 d-flex flex-column justify-content-center align-items-center">
                                <i class="fas fa-plus fa-2x mb-2"></i>
                                <span>Create New Report</span>
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="{{ route('pm.progress-reports.index', ['status' => 'draft']) }}" class="btn btn-outline-secondary w-100 mb-2 h-100 d-flex flex-column justify-content-center align-items-center">
                                <i class="fas fa-save fa-2x mb-2"></i>
                                <span>Draft Reports</span>
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="{{ route('pm.progress-reports.index', ['status' => 'sent']) }}" class="btn btn-outline-warning w-100 mb-2 h-100 d-flex flex-column justify-content-center align-items-center">
                                <i class="fas fa-paper-plane fa-2x mb-2"></i>
                                <span>Sent Reports</span>
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="{{ route('projects.index') }}" class="btn btn-outline-info w-100 mb-2 h-100 d-flex flex-column justify-content-center align-items-center">
                                <i class="fas fa-project-diagram fa-2x mb-2"></i>
                                <span>My Projects</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Confirm Deletion</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this progress report? This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="deleteForm" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.border-left-primary {
    border-left: 0.25rem solid #4e73df !important;
}

.border-left-success {
    border-left: 0.25rem solid #1cc88a !important;
}

.border-left-warning {
    border-left: 0.25rem solid #f6c23e !important;
}

.border-left-info {
    border-left: 0.25rem solid #36b9cc !important;
}

.table th {
    border-top: none;
    font-weight: 600;
    background-color: #f8f9fc;
}

.btn-group .btn {
    margin-right: 2px;
}

.btn-group .btn:last-child {
    margin-right: 0;
}

.badge {
    font-size: 0.75em;
}

.card {
    border: none;
    border-radius: 0.5rem;
}

.card-header {
    background-color: #f8f9fc;
    border-bottom: 1px solid #e3e6f0;
}
</style>
@endpush

@push('scripts')
<script>
function deleteReport(reportId) {
    const form = document.getElementById('deleteForm');
    form.action = `{{ route('admin.progress-reports.index') }}/${reportId}`;
    
    const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
    modal.show();
}

// Auto-submit form when status filter changes
document.getElementById('status').addEventListener('change', function() {
    if (this.value !== '') {
        this.form.submit();
    }
});
</script>
@endpush