@extends('app')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Daily Expenditures Administration</h1>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.expenditures.create') }}" class="btn btn-success btn-sm">
                <i class="fas fa-plus"></i> Create Expenditure
            </a>
            <a href="{{ route('admin.expenditures.reports.detailed') }}" class="btn btn-info btn-sm">
                <i class="fas fa-chart-bar"></i> Detailed Report
            </a>
            <a href="{{ route('admin.expenditures.reports.analytics') }}" class="btn btn-warning btn-sm">
                <i class="fas fa-analytics"></i> Analytics
            </a>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-2">
            <div class="card bg-primary text-white">
                <div class="card-body text-center">
                    <h4>{{ $expenditures->total() }}</h4>
                    <small>Total</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-warning text-white">
                <div class="card-body text-center">
                    <h4>{{ $expenditures->where('status', 'draft')->count() }}</h4>
                    <small>Draft</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-info text-white">
                <div class="card-body text-center">
                    <h4>{{ $expenditures->where('status', 'submitted')->count() }}</h4>
                    <small>Submitted</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-success text-white">
                <div class="card-body text-center">
                    <h4>{{ $expenditures->where('status', 'approved')->count() }}</h4>
                    <small>Approved</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-danger text-white">
                <div class="card-body text-center">
                    <h4>{{ $expenditures->where('status', 'rejected')->count() }}</h4>
                    <small>Rejected</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-secondary text-white">
                <div class="card-body text-center">
                    <h4>₱{{ number_format($expenditures->sum('amount'), 2) }}</h4>
                    <small>Total Amount</small>
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
            <form method="GET" action="{{ route('admin.expenditures.index') }}">
                <div class="row">
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="status">Status</label>
                            <select name="status" id="status" class="form-control">
                                <option value="">All Statuses</option>
                                @foreach($status_options as $value => $label)
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
                            <label for="submitter_id">Submitter</label>
                            <select name="submitter_id" id="submitter_id" class="form-control">
                                <option value="">All Submitters</option>
                                                                    @foreach($submitters as $submitter)
                                        <option value="{{ $submitter->id }}" {{ request('submitter_id') == $submitter->id ? 'selected' : '' }}>
                                            {{ $submitter->full_name }}
                                        </option>
                                    @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="category">Category</label>
                            <select name="category" id="category" class="form-control">
                                <option value="">All Categories</option>
                                @foreach($category_options as $value => $label)
                                    <option value="{{ $value }}" {{ request('category') == $value ? 'selected' : '' }}>
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
                                   placeholder="Description, vendor, reference..." value="{{ request('search') }}">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="per_page">Per Page</label>
                            <select name="per_page" id="per_page" class="form-control">
                                <option value="15" {{ request('per_page', 15) == 15 ? 'selected' : '' }}>15</option>
                                <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
                                <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                                <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="date_from">Date From</label>
                            <input type="date" name="date_from" id="date_from" class="form-control" value="{{ request('date_from') }}">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="date_to">Date To</label>
                            <input type="date" name="date_to" id="date_to" class="form-control" value="{{ request('date_to') }}">
                        </div>
                    </div>
                    <div class="col-md-6 d-flex align-items-end">
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i> Apply Filters
                            </button>
                            <a href="{{ route('admin.expenditures.index') }}" class="btn btn-secondary">
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
                <span>Showing {{ $expenditures->firstItem() ?? 0 }} to {{ $expenditures->lastItem() ?? 0 }} of {{ $expenditures->total() }} entries</span>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th width="5%">ID</th>
                            <th width="15%">Project</th>
                            <th width="12%">Submitter</th>
                            <th width="10%">Expense Date</th>
                            <th width="10%">Category</th>
                            <th width="20%">Description</th>
                            <th width="8%">Amount</th>
                            <th width="8%">Status</th>
                            <th width="12%">Created</th>
                            <th width="15%">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($expenditures as $expenditure)
                            <tr>
                                <td>{{ $expenditure->id }}</td>
                                <td>
                                    @if($expenditure->project)
                                        <span class="badge badge-info">{{ $expenditure->project->name }}</span>
                                    @else
                                        <span class="text-muted">No Project</span>
                                    @endif
                                </td>
                                <td>
                                    @if($expenditure->submitter)
                                        <span class="badge badge-secondary">{{ $expenditure->submitter->full_name }}</span>
                                    @else
                                        <span class="text-muted">Unknown</span>
                                    @endif
                                </td>
                                <td>{{ $expenditure->expense_date->format('M d, Y') }}</td>
                                <td>
                                    <span class="badge badge-primary">{{ $expenditure->category }}</span>
                                </td>
                                <td>
                                    <div class="text-truncate" style="max-width: 200px;" title="{{ $expenditure->description }}">
                                        {{ $expenditure->description }}
                                    </div>
                                </td>
                                <td class="text-right">
                                    <strong>₱{{ number_format($expenditure->amount, 2) }}</strong>
                                </td>
                                <td>
                                    <span class="badge badge-{{ $expenditure->status_badge_color }}">
                                        {{ $expenditure->formatted_status }}
                                    </span>
                                </td>
                                <td>{{ $expenditure->created_at->format('M d, Y H:i') }}</td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('admin.expenditures.show', $expenditure) }}" 
                                           class="btn btn-sm btn-info" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.expenditures.edit', $expenditure) }}" 
                                           class="btn btn-sm btn-warning" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button type="button" class="btn btn-sm btn-danger" 
                                                onclick="deleteExpenditure({{ $expenditure->id }})" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center">
                                    <div class="alert alert-info mb-0">
                                        <i class="fas fa-info-circle"></i> No expenditures found matching your criteria.
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div class="d-flex justify-content-between align-items-center mt-3">
                <div>
                    Showing {{ $expenditures->firstItem() ?? 0 }} to {{ $expenditures->lastItem() ?? 0 }} of {{ $expenditures->total() }} entries
                </div>
                <div>
                    {{ $expenditures->appends(request()->query())->links() }}
                </div>
            </div>
        </div>
    </div>
</div>



@endsection

@push('scripts')
<script>
function deleteExpenditure(id) {
    if (confirm('Are you sure you want to delete this expenditure? This action cannot be undone.')) {
        fetch(`/admin/expenditures/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                location.reload();
            } else {
                alert('Error: ' + (data.message || 'Unknown error occurred'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while deleting the expenditure.');
        });
    }
}
</script>
@endpush
