@extends('app')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Daily Expenditures Management</h1>
        <div class="d-flex gap-2">
            <a href="{{ route('finance.expenditures.export.csv') }}" class="btn btn-success btn-sm">
                <i class="fas fa-download"></i> Export CSV
            </a>

        </div>
    </div>

    <!-- Filters Card -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Filters</h6>
        </div>
        <div class="card-body">
            <form id="filtersForm" method="GET" action="{{ route('finance.expenditures.index') }}">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="status">Status</label>
                            <select name="status" id="status" class="form-control">
                                <option value="">All Statuses</option>
                                <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                                <option value="submitted" {{ request('status') == 'submitted' ? 'selected' : '' }}>Submitted</option>
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
                            <label for="submitter_id">Submitter</label>
                            <select name="submitter_id" id="submitter_id" class="form-control">
                                <option value="">All Submitters</option>
                                @foreach($submitters as $submitter)
                                    <option value="{{ $submitter->id }}" {{ request('submitter_id') == $submitter->id ? 'selected' : '' }}>
                                        {{ $submitter->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="search">Search</label>
                            <input type="text" name="search" id="search" class="form-control" 
                                   value="{{ request('search') }}" placeholder="Description, vendor, reference...">
                        </div>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="date_from">Date From</label>
                            <input type="date" name="date_from" id="date_from" class="form-control" 
                                   value="{{ request('date_from') }}">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="date_to">Date To</label>
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
                            <a href="{{ route('finance.expenditures.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Clear
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Expenditures Table Card -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">Daily Expenditures</h6>
            <div class="text-muted">
                Showing {{ $expenditures->firstItem() ?? 0 }} to {{ $expenditures->lastItem() ?? 0 }} 
                of {{ $expenditures->total() }} entries
            </div>
        </div>
        <div class="card-body">
            @if($expenditures->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Project</th>
                                <th>Submitter</th>
                                <th>Expense Date</th>
                                <th>Category</th>
                                <th>Description</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Submitted</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($expenditures as $expenditure)
                            <tr>
                                <td>{{ $expenditure->id }}</td>
                                <td>
                                    @if($expenditure->project)
                                        <span class="font-weight-bold">{{ $expenditure->project->name }}</span>
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                                <td>
                                    @if($expenditure->submitter)
                                        <span class="font-weight-bold">{{ $expenditure->submitter->name }}</span>
                                        <br><small class="text-muted">{{ $expenditure->submitter->role }}</small>
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                                <td>{{ $expenditure->formatted_expense_date }}</td>
                                <td>
                                    <span class="badge badge-info">{{ $expenditure->formatted_category }}</span>
                                </td>
                                <td>
                                    <div class="text-truncate" style="max-width: 200px;" title="{{ $expenditure->description }}">
                                        {{ $expenditure->description }}
                                    </div>
                                    @if($expenditure->vendor_supplier)
                                        <small class="text-muted">Vendor: {{ $expenditure->vendor_supplier }}</small>
                                    @endif
                                </td>
                                <td>
                                    <span class="font-weight-bold text-success">{{ $expenditure->formatted_amount }}</span>
                                    @if($expenditure->location)
                                        <br><small class="text-muted">{{ $expenditure->location }}</small>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge badge-{{ $expenditure->status_color }}">
                                        {{ $expenditure->formatted_status }}
                                    </span>
                                </td>
                                <td>
                                    @if($expenditure->submitted_at)
                                        {{ $expenditure->formatted_submitted_date }}
                                        <br><small class="text-muted">{{ $expenditure->submitted_at->diffForHumans() }}</small>
                                    @else
                                        <span class="text-muted">Not submitted</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('finance.expenditures.show', $expenditure) }}" 
                                       class="btn btn-sm btn-info" title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-center">
                    {{ $expenditures->appends(request()->query())->links() }}
                </div>
            @else
                <div class="text-center py-4">
                    <i class="fas fa-receipt fa-3x text-gray-300 mb-3"></i>
                    <p class="text-gray-500">No expenditures found matching your criteria.</p>
                    <a href="{{ route('finance.expenditures.index') }}" class="btn btn-primary">
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
                <strong>Note:</strong> Submitted expenditures from Project Managers are automatically visible and do not require approval/decline. 
                Finance users can view and generate reports for all expenditures.
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
