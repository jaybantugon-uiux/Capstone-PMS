@extends('app')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Detailed Expenditure Report</h1>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.expenditures.index') }}" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Back to List
            </a>
            <button type="button" class="btn btn-success btn-sm" onclick="window.print()">
                <i class="fas fa-print"></i> Print Report
            </button>
        </div>
    </div>

    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.expenditures.index') }}">Daily Expenditures</a></li>
            <li class="breadcrumb-item active" aria-current="page">Detailed Report</li>
        </ol>
    </nav>

    <!-- Summary Statistics -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body text-center">
                    <h4>{{ $total_expenditures }}</h4>
                    <small>Total Expenditures</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body text-center">
                    <h4>₱{{ number_format($total_amount, 2) }}</h4>
                    <small>Total Amount</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body text-center">
                    <h4>{{ $expenditures->unique('project_id')->count() }}</h4>
                    <small>Projects Involved</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body text-center">
                    <h4>{{ $expenditures->unique('submitted_by')->count() }}</h4>
                    <small>Unique Submitters</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Filters</h6>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.expenditures.reports.detailed') }}">
                <div class="row">
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
                                            {{ $submitter->full_name }}
                                        </option>
                                    @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="date_from">Date From</label>
                            <input type="date" name="date_from" id="date_from" class="form-control" value="{{ request('date_from') }}">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="date_to">Date To</label>
                            <input type="date" name="date_to" id="date_to" class="form-control" value="{{ request('date_to') }}">
                        </div>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i> Apply
                            </button>
                            <a href="{{ route('admin.expenditures.reports.detailed') }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Clear
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Breakdown by Category -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Breakdown by Category</h6>
                </div>
                <div class="card-body">
                    @if($by_category->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Category</th>
                                        <th>Count</th>
                                        <th>Total Amount</th>
                                        <th>Percentage</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($by_category as $category => $group)
                                        <tr>
                                            <td>{{ $category }}</td>
                                            <td>{{ $group->count() }}</td>
                                            <td>₱{{ number_format($group->sum('amount'), 2) }}</td>
                                            <td>{{ number_format(($group->count() / $total_expenditures) * 100, 1) }}%</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted text-center">No data available</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Breakdown by Project -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Breakdown by Project</h6>
        </div>
        <div class="card-body">
            @if($by_project->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Project</th>
                                <th>Count</th>
                                <th>Total Amount</th>
                                <th>Percentage</th>
                                <th>Average Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($by_project as $project_id => $group)
                                <tr>
                                    <td>
                                        @if($group->first()->project)
                                            {{ $group->first()->project->name }}
                                        @else
                                            <span class="text-muted">No Project</span>
                                        @endif
                                    </td>
                                    <td>{{ $group->count() }}</td>
                                    <td>₱{{ number_format($group->sum('amount'), 2) }}</td>
                                    <td>{{ number_format(($group->count() / $total_expenditures) * 100, 1) }}%</td>
                                    <td>₱{{ number_format($group->avg('amount'), 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-muted text-center">No data available</p>
            @endif
        </div>
    </div>

    <!-- Detailed Expenditures Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Detailed Expenditures</h6>
        </div>
        <div class="card-body">
            @if($expenditures->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered">
                                                        <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Project</th>
                                        <th>Submitter</th>
                                        <th>Expense Date</th>
                                        <th>Category</th>
                                        <th>Description</th>
                                        <th>Amount</th>
                                        <th>Created</th>
                                    </tr>
                                </thead>
                        <tbody>
                            @foreach($expenditures as $expenditure)
                                <tr>
                                    <td>{{ $expenditure->id }}</td>
                                    <td>
                                        @if($expenditure->project)
                                            {{ $expenditure->project->name }}
                                        @else
                                            <span class="text-muted">No Project</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($expenditure->submitter)
                                            {{ $expenditure->submitter->full_name }}
                                        @else
                                            <span class="text-muted">Unknown</span>
                                        @endif
                                    </td>
                                    <td>{{ $expenditure->expense_date->format('M d, Y') }}</td>
                                    <td>{{ $expenditure->category }}</td>
                                    <td>
                                        <div class="text-truncate" style="max-width: 200px;" title="{{ $expenditure->description }}">
                                            {{ $expenditure->description }}
                                        </div>
                                    </td>
                                    <td class="text-right">₱{{ number_format($expenditure->amount, 2) }}</td>
                                    <td>{{ $expenditure->created_at->format('M d, Y H:i') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center text-muted">
                    <i class="fas fa-inbox fa-2x mb-2"></i>
                    <p>No expenditures found matching your criteria.</p>
                </div>
            @endif
        </div>
    </div>
</div>

<style>
@media print {
    .btn, .breadcrumb, .card-header {
        display: none !important;
    }
    .card {
        border: none !important;
        box-shadow: none !important;
    }
    .card-body {
        padding: 0 !important;
    }
}
</style>
@endsection
