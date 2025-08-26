@extends('app')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Financial Reports Management</h1>
        <div class="d-flex gap-2">
            <a href="{{ route('finance.financial-reports.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> Create Report
            </a>
        </div>
    </div>

    <!-- Filters Card -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Filters</h6>
        </div>
        <div class="card-body">
            <form id="filtersForm" method="GET" action="{{ route('finance.financial-reports.index') }}">
                <div class="row">
                    <div class="col-md-3">
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
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="search">Search</label>
                            <input type="text" name="search" id="search" class="form-control" 
                                   value="{{ request('search') }}" placeholder="Title, description...">
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
                    <div class="col-md-3">
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
                    <div class="col-md-3 d-flex align-items-end">
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i> Apply Filters
                            </button>
                            <a href="{{ route('finance.financial-reports.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Clear
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Reports Table Card -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">Financial Reports</h6>
            <div class="text-muted">
                Showing {{ $reports->firstItem() ?? 0 }} to {{ $reports->lastItem() ?? 0 }} 
                of {{ $reports->total() }} entries
            </div>
        </div>
        <div class="card-body">
            @if($reports->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Title</th>
                                <th>Project</th>
                                <th>Type</th>
                                <th>Period</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($reports as $report)
                            <tr>
                                <td>{{ $report->id }}</td>
                                <td>
                                    <div class="text-truncate" style="max-width: 200px;" title="{{ $report->title }}">
                                        <strong>{{ $report->title }}</strong>
                                    </div>
                                    @if($report->description)
                                        <small class="text-muted">{{ Str::limit($report->description, 50) }}</small>
                                    @endif
                                </td>
                                <td>
                                    @if($report->project)
                                        <span class="font-weight-bold text-primary">{{ $report->project->name }}</span>
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge badge-info">{{ $reportTypeOptions[$report->report_type] ?? $report->report_type }}</span>
                                </td>
                                <td>{{ $report->formatted_period }}</td>
                                <td>
                                    <span class="badge badge-{{ $report->status_color }}">
                                        {{ $report->formatted_status }}
                                    </span>
                                    @if($report->is_overdue)
                                        <br><small class="text-danger">Overdue</small>
                                    @endif
                                </td>
                                <td>
                                    {{ $report->created_at->format('M d, Y') }}
                                    <br><small class="text-muted">{{ $report->created_at->diffForHumans() }}</small>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('finance.financial-reports.show', $report) }}" 
                                           class="btn btn-sm btn-info" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @if($report->canBeEdited())
                                            <a href="{{ route('finance.financial-reports.edit', $report) }}" 
                                               class="btn btn-sm btn-warning" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        @endif
                                        @if($report->canBeGenerated())
                                            <form method="POST" action="{{ route('finance.financial-reports.generate', $report) }}" 
                                                  style="display: inline;" onsubmit="return confirm('Generate this report?')">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-success" title="Generate Report">
                                                    <i class="fas fa-cog"></i>
                                                </button>
                                            </form>
                                        @endif
                                        @if($report->canBeDeleted())
                                            <form method="POST" action="{{ route('finance.financial-reports.destroy', $report) }}" 
                                                  style="display: inline;" onsubmit="return confirm('Delete this report?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger" title="Delete">
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
                    <i class="fas fa-chart-line fa-3x text-gray-300 mb-3"></i>
                    <p class="text-gray-500">No financial reports found matching your criteria.</p>
                    <a href="{{ route('finance.financial-reports.index') }}" class="btn btn-primary">
                        <i class="fas fa-refresh"></i> Reset Filters
                    </a>
                </div>
            @endif
        </div>
    </div>

    <!-- Information Card -->
    <div class="card shadow">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Information</h6>
        </div>
        <div class="card-body">
            <div class="alert alert-info mb-0">
                <i class="fas fa-info-circle"></i>
                <strong>Note:</strong> Financial reports can be created, generated, and exported. 
                Reports must be generated before they can be exported or used to create liquidated forms.
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // Auto-submit form when per_page changes
    $('#per_page').change(function() {
        $('#filtersForm').submit();
    });

    // Date validation
    $('#date_from, #date_to').change(function() {
        const dateFrom = $('#date_from').val();
        const dateTo = $('#date_to').val();
        
        if (dateFrom && dateTo && dateFrom > dateTo) {
            alert('Start date cannot be after end date');
            $(this).val('');
        }
    });
});
</script>
@endpush
@endsection
