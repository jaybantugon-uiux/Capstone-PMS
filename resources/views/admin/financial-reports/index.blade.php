@extends('app')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Financial Reports Administration</h1>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.financial-reports.create') }}" class="btn btn-success btn-sm">
                <i class="fas fa-plus"></i> Create Report
            </a>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-2">
            <div class="card bg-primary text-white">
                <div class="card-body text-center">
                    <h4>{{ $reports->total() }}</h4>
                    <small>Total</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-secondary text-white">
                <div class="card-body text-center">
                    <h4>{{ $reports->where('status', 'draft')->count() }}</h4>
                    <small>Draft</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-info text-white">
                <div class="card-body text-center">
                    <h4>{{ $reports->where('status', 'generated')->count() }}</h4>
                    <small>Generated</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-success text-white">
                <div class="card-body text-center">
                    <h4>{{ $reports->where('status', 'liquidated')->count() }}</h4>
                    <small>Liquidated</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-warning text-white">
                <div class="card-body text-center">
                    <h4>{{ $reports->sum('total_expenditures') ? '₱' . number_format($reports->sum('total_expenditures'), 2) : '₱0.00' }}</h4>
                    <small>Total Expenditures</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-danger text-white">
                <div class="card-body text-center">
                    <h4>{{ $reports->sum('total_receipts') ? '₱' . number_format($reports->sum('total_receipts'), 2) : '₱0.00' }}</h4>
                    <small>Total Receipts</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters Card -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Filters</h6>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.financial-reports.index') }}">
                <div class="row">
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="status">Status</label>
                            <select name="status" id="status" class="form-control">
                                <option value="">All Statuses</option>
                                @foreach($statusOptions as $value => $label)
                                    <option value="{{ $value }}" {{ request('status') == $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
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
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="report_type">Report Type</label>
                            <select name="report_type" id="report_type" class="form-control">
                                <option value="">All Types</option>
                                @foreach($reportTypeOptions as $value => $label)
                                    <option value="{{ $value }}" {{ request('report_type') == $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="search">Search</label>
                            <input type="text" name="search" id="search" class="form-control" 
                                   value="{{ request('search') }}" placeholder="Title, description...">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="per_page">Per Page</label>
                            <select name="per_page" id="per_page" class="form-control">
                                <option value="15" {{ request('per_page') == '15' ? 'selected' : '' }}>15</option>
                                <option value="25" {{ request('per_page') == '25' ? 'selected' : '' }}>25</option>
                                <option value="50" {{ request('per_page') == '50' ? 'selected' : '' }}>50</option>
                                <option value="100" {{ request('per_page') == '100' ? 'selected' : '' }}>100</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i> Apply Filters
                            </button>
                            <a href="{{ route('admin.financial-reports.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Clear
                            </a>
                        </div>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="date_from">Period Start From</label>
                            <input type="date" name="date_from" id="date_from" class="form-control" 
                                   value="{{ request('date_from') }}">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="date_to">Period End To</label>
                            <input type="date" name="date_to" id="date_to" class="form-control" 
                                   value="{{ request('date_to') }}">
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Reports Table Card -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Financial Reports</h6>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-danger btn-sm" onclick="bulkAction('delete')">
                    <i class="fas fa-trash"></i> Delete Selected
                </button>
                <button type="button" class="btn btn-info btn-sm" onclick="bulkAction('generate')">
                    <i class="fas fa-cog"></i> Generate Selected
                </button>
            </div>
        </div>
        <div class="card-body">
            @if($reports->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered" id="reportsTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th width="30">
                                    <input type="checkbox" id="selectAll" class="form-check-input">
                                </th>
                                <th>Title</th>
                                <th>Type</th>
                                <th>Project</th>
                                <th>Period</th>
                                <th>Status</th>
                                <th>Total Expenditures</th>
                                <th>Total Receipts</th>
                                <th>Variance</th>
                                <th>Created By</th>
                                <th>Created At</th>
                                <th width="150">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($reports as $report)
                                <tr>
                                    <td>
                                        <input type="checkbox" name="selected_reports[]" value="{{ $report->id }}" class="form-check-input report-checkbox">
                                    </td>
                                    <td>
                                        <strong>{{ $report->title }}</strong>
                                        @if($report->description)
                                            <br><small class="text-muted">{{ Str::limit($report->description, 50) }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge badge-info">{{ ucfirst($report->report_type) }}</span>
                                    </td>
                                    <td>
                                        @if($report->project)
                                            <span class="badge badge-primary">{{ $report->project->name }}</span>
                                        @else
                                            <span class="text-muted">No Project</span>
                                        @endif
                                    </td>
                                    <td>
                                        <small>{{ $report->formatted_period }}</small>
                                    </td>
                                    <td>
                                        <span class="badge badge-{{ $report->status_badge_color }}">
                                            {{ $report->formatted_status }}
                                        </span>
                                    </td>
                                    <td>
                                        <strong>{{ $report->formatted_total_expenditures }}</strong>
                                    </td>
                                    <td>
                                        <strong>{{ $report->formatted_total_receipts }}</strong>
                                    </td>
                                    <td>
                                        <span class="badge badge-{{ $report->is_variance_positive ? 'success' : 'danger' }}">
                                            {{ $report->formatted_variance_amount }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($report->creator)
                                            <small>{{ $report->creator->full_name }}</small>
                                        @else
                                            <span class="text-muted">Unknown</span>
                                        @endif
                                    </td>
                                    <td>
                                        <small>{{ $report->created_at->format('M d, Y H:i') }}</small>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('admin.financial-reports.show', $report) }}" 
                                               class="btn btn-info btn-sm" title="View">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            @if($report->canBeEdited())
                                                <a href="{{ route('admin.financial-reports.edit', $report) }}" 
                                                   class="btn btn-warning btn-sm" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            @endif
                                            @if($report->canBeGenerated())
                                                <form method="POST" action="{{ route('admin.financial-reports.force-generate', $report) }}" 
                                                      class="d-inline" onsubmit="return confirm('Generate this financial report?')">
                                                    @csrf
                                                    <button type="submit" class="btn btn-success btn-sm" title="Generate">
                                                        <i class="fas fa-cog"></i>
                                                    </button>
                                                </form>
                                            @endif
                                            @if($report->canBeDeleted())
                                                <form method="POST" action="{{ route('admin.financial-reports.destroy', $report) }}" 
                                                      class="d-inline" onsubmit="return confirm('Delete this financial report?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger btn-sm" title="Delete">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-center">
                    {{ $reports->appends(request()->query())->links() }}
                </div>
            @else
                <div class="text-center py-4">
                    <i class="fas fa-file-alt fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No financial reports found</h5>
                    <p class="text-muted">Create your first financial report to get started.</p>
                    <a href="{{ route('admin.financial-reports.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Create Report
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Bulk Action Form -->
<form id="bulkActionForm" method="POST" action="{{ route('admin.financial-reports.bulk-action') }}" style="display: none;">
    @csrf
    <input type="hidden" name="action" id="bulkActionType">
    <input type="hidden" name="selected_reports" id="selectedReports">
</form>

@endsection

@push('scripts')
<script>
    // Select all functionality
    document.getElementById('selectAll').addEventListener('change', function() {
        const checkboxes = document.querySelectorAll('.report-checkbox');
        checkboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
    });

    // Bulk action functionality
    function bulkAction(action) {
        const selectedReports = Array.from(document.querySelectorAll('.report-checkbox:checked'))
            .map(checkbox => checkbox.value);

        if (selectedReports.length === 0) {
            alert('Please select at least one financial report.');
            return;
        }

        let confirmMessage = '';
        switch(action) {
            case 'delete':
                confirmMessage = `Are you sure you want to delete ${selectedReports.length} selected financial report(s)?`;
                break;
            case 'generate':
                confirmMessage = `Are you sure you want to generate ${selectedReports.length} selected financial report(s)?`;
                break;
            default:
                confirmMessage = `Are you sure you want to perform this action on ${selectedReports.length} selected financial report(s)?`;
        }

        if (confirm(confirmMessage)) {
            document.getElementById('bulkActionType').value = action;
            document.getElementById('selectedReports').value = JSON.stringify(selectedReports);
            document.getElementById('bulkActionForm').submit();
        }
    }

    // Auto-submit form when per_page changes
    document.getElementById('per_page').addEventListener('change', function() {
        this.form.submit();
    });
</script>
@endpush
