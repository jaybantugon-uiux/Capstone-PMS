@extends('app')

@section('title', 'Progress Reports Management')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-file-chart-line me-2"></i>Progress Reports
            </h1>
            <p class="mb-0 text-muted">Manage and track client progress reports</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.progress-reports.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i>New Report
            </a>
            <a href="{{ route('admin.progress-reports.export') }}" class="btn btn-outline-secondary">
                <i class="fas fa-download me-1"></i>Export CSV
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
                                Total Reports
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
                <i class="fas fa-filter me-2"></i>Filters
            </h6>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.progress-reports.index') }}">
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
                            <label for="project_id">Project</label>
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
                                   placeholder="Search title or description..." value="{{ request('search') }}">
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
                            <a href="{{ route('admin.progress-reports.index') }}" class="btn btn-outline-secondary">
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
            <h6 class="m-0 font-weight-bold text-primary">Progress Reports</h6>
            <div class="dropdown">
                <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" 
                        data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-cog me-1"></i>Actions
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="#" onclick="showBulkActions()">
                        <i class="fas fa-check-square me-2"></i>Bulk Actions
                    </a></li>
                    <li><a class="dropdown-item" href="{{ route('admin.progress-reports.export') }}">
                        <i class="fas fa-download me-2"></i>Export All
                    </a></li>
                </ul>
            </div>
        </div>
        <div class="card-body">
            @if($reports->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered" id="reportsTable">
                        <thead>
                            <tr>
                                <th width="40">
                                    <input type="checkbox" id="selectAll" class="form-check-input">
                                </th>
                                <th>Title</th>
                                <th>Client</th>
                                <th>Project</th>
                                <th>Creator</th>
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
                                        <input type="checkbox" name="selected_reports[]" 
                                               value="{{ $report->id }}" class="form-check-input report-checkbox">
                                    </td>
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
                                            {{ Str::limit($report->project->name, 25) }}
                                        @else
                                            <span class="text-muted">General</span>
                                        @endif
                                    </td>
                                    <td>
                                        {{ $report->creator->first_name }} {{ $report->creator->last_name }}
                                        <br><span class="badge bg-{{ $report->creator_role_badge_color }}">
                                            {{ $report->formatted_creator_role }}
                                        </span>
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
                                            @if(auth()->user()->role === 'admin' || $report->created_by === auth()->id())
                                                <a href="{{ route('admin.progress-reports.edit', $report) }}" 
                                                   class="btn btn-sm btn-outline-secondary" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <button type="button" class="btn btn-sm btn-outline-danger" 
                                                        onclick="deleteReport({{ $report->id }})" title="Delete">
                                                    <i class="fas fa-trash"></i>
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
                    <p class="text-muted">Get started by creating your first progress report.</p>
                    <a href="{{ route('admin.progress-reports.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i>Create First Report
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Bulk Actions Modal -->
<div class="modal fade" id="bulkActionsModal" tabindex="-1" aria-labelledby="bulkActionsModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="bulkActionsModalLabel">Bulk Actions</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="bulkActionForm" method="POST" action="{{ route('admin.progress-reports.bulk-action') }}">
                @csrf
                <div class="modal-body">
                    <div class="form-group mb-3">
                        <label for="bulkAction">Select Action</label>
                        <select name="action" id="bulkAction" class="form-control" required>
                            <option value="">Choose action...</option>
                            <option value="mark-viewed">Mark as Viewed</option>
                            <option value="archive">Archive</option>
                            <option value="delete">Delete</option>
                        </select>
                    </div>
                    <input type="hidden" name="ids" id="selectedIds">
                    <div id="selectedCount" class="alert alert-info"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Apply Action</button>
                </div>
            </form>
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
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Select All functionality
    const selectAllCheckbox = document.getElementById('selectAll');
    const reportCheckboxes = document.querySelectorAll('.report-checkbox');

    selectAllCheckbox.addEventListener('change', function() {
        reportCheckboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
    });

    // Update Select All when individual checkboxes change
    reportCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const checkedCount = document.querySelectorAll('.report-checkbox:checked').length;
            selectAllCheckbox.checked = checkedCount === reportCheckboxes.length;
            selectAllCheckbox.indeterminate = checkedCount > 0 && checkedCount < reportCheckboxes.length;
        });
    });
});

function showBulkActions() {
    const selectedCheckboxes = document.querySelectorAll('.report-checkbox:checked');
    
    if (selectedCheckboxes.length === 0) {
        alert('Please select at least one report.');
        return;
    }

    const selectedIds = Array.from(selectedCheckboxes).map(cb => cb.value);
    document.getElementById('selectedIds').value = selectedIds.join(',');
    document.getElementById('selectedCount').innerHTML = 
        `<strong>${selectedIds.length}</strong> report(s) selected.`;

    const modal = new bootstrap.Modal(document.getElementById('bulkActionsModal'));
    modal.show();
}

function deleteReport(reportId) {
    const form = document.getElementById('deleteForm');
    form.action = `{{ route('admin.progress-reports.index') }}/${reportId}`;
    
    const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
    modal.show();
}

// Auto-submit form when filters change (optional)
document.querySelectorAll('#status, #client_id, #project_id').forEach(select => {
    select.addEventListener('change', function() {
        if (this.value !== '') {
            this.form.submit();
        }
    });
});
</script>
@endpush