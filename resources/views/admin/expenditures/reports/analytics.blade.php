@extends('app')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Expenditure Analytics</h1>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.expenditures.index') }}" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Back to List
            </a>
            <button type="button" class="btn btn-success btn-sm" onclick="window.print()">
                <i class="fas fa-print"></i> Print Analytics
            </button>
        </div>
    </div>

    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.expenditures.index') }}">Daily Expenditures</a></li>
            <li class="breadcrumb-item active" aria-current="page">Analytics</li>
        </ol>
    </nav>

    <!-- Filters -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Date Range Filter</h6>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.expenditures.reports.analytics') }}">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="date_from">Date From</label>
                            <input type="date" name="date_from" id="date_from" class="form-control" value="{{ request('date_from') }}">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="date_to">Date To</label>
                            <input type="date" name="date_to" id="date_to" class="form-control" value="{{ request('date_to') }}">
                        </div>
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i> Apply Filter
                            </button>
                            <a href="{{ route('admin.expenditures.reports.analytics') }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Clear
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Key Metrics -->
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
                    <h4>₱{{ number_format($average_amount, 2) }}</h4>
                    <small>Average Amount</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body text-center">
                    <h4>{{ $total_expenditures > 0 ? number_format($total_amount / $total_expenditures, 2) : '0.00' }}</h4>
                    <small>Amount per Expenditure</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Analytics Charts -->
    <div class="row mb-4">
        <!-- Category Distribution -->
        <div class="col-md-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Category Distribution</h6>
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
                                        <th>Average Amount</th>
                                        <th>Percentage</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($by_category as $category)
                                        <tr>
                                            <td>{{ $category->category }}</td>
                                            <td>{{ $category->count }}</td>
                                            <td>₱{{ number_format($category->total_amount, 2) }}</td>
                                            <td>₱{{ number_format($category->avg_amount, 2) }}</td>
                                            <td>{{ number_format(($category->count / $total_expenditures) * 100, 1) }}%</td>
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

    <!-- Monthly Trends -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Monthly Trends (Last 12 Months)</h6>
        </div>
        <div class="card-body">
            @if($by_month->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Month</th>
                                <th>Count</th>
                                <th>Total Amount</th>
                                <th>Average Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($by_month as $month)
                                <tr>
                                    <td>
                                        @php
                                            $date = \Carbon\Carbon::createFromDate($month->year, $month->month, 1);
                                        @endphp
                                        {{ $date->format('M Y') }}
                                    </td>
                                    <td>{{ $month->count }}</td>
                                    <td>₱{{ number_format($month->total_amount, 2) }}</td>
                                    <td>₱{{ number_format($month->total_amount / $month->count, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-muted text-center">No monthly data available</p>
            @endif
        </div>
    </div>

    <!-- Top Projects -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Top Projects by Amount</h6>
                </div>
                <div class="card-body">
                    @if($top_projects->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Project</th>
                                        <th>Count</th>
                                        <th>Total Amount</th>
                                        <th>Average Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($top_projects as $project)
                                        <tr>
                                            <td>{{ $project->name }}</td>
                                            <td>{{ $project->count }}</td>
                                            <td>₱{{ number_format($project->total_amount, 2) }}</td>
                                            <td>₱{{ number_format($project->total_amount / $project->count, 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted text-center">No project data available</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Top Submitters -->
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Top Submitters by Amount</h6>
                </div>
                <div class="card-body">
                    @if($top_submitters->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Submitter</th>
                                        <th>Count</th>
                                        <th>Total Amount</th>
                                        <th>Average Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($top_submitters as $submitter)
                                        <tr>
                                            <td>{{ $submitter->name }}</td>
                                            <td>{{ $submitter->count }}</td>
                                            <td>₱{{ number_format($submitter->total_amount, 2) }}</td>
                                            <td>₱{{ number_format($submitter->total_amount / $submitter->count, 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted text-center">No submitter data available</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Insights -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Key Insights</h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h6>Expenditure Summary</h6>
                    <ul class="list-unstyled">
                        <li><strong>Total Expenditures:</strong> {{ $total_expenditures }}</li>
                        @if($by_category->count() > 0)
                            @php
                                $highestCategory = $by_category->sortByDesc('total_amount')->first();
                            @endphp
                            <li><strong>Highest Spending Category:</strong> {{ $highestCategory->category }}</li>
                        @endif
                    </ul>
                </div>
                <div class="col-md-6">
                    <h6>Trends & Patterns</h6>
                    <ul class="list-unstyled">
                        @if($by_month->count() > 0)
                            @php
                                $highestMonth = $by_month->sortByDesc('total_amount')->first();
                                $lowestMonth = $by_month->sortByDesc('total_amount')->last();
                            @endphp
                            <li><strong>Highest Spending Month:</strong> 
                                @php
                                    $date = \Carbon\Carbon::createFromDate($highestMonth->year, $highestMonth->month, 1);
                                @endphp
                                {{ $date->format('M Y') }} (₱{{ number_format($highestMonth->total_amount, 2) }})
                            </li>
                            <li><strong>Lowest Spending Month:</strong> 
                                @php
                                    $date = \Carbon\Carbon::createFromDate($lowestMonth->year, $lowestMonth->month, 1);
                                @endphp
                                {{ $date->format('M Y') }} (₱{{ number_format($lowestMonth->total_amount, 2) }})
                            </li>
                        @endif
                        @if($top_projects->count() > 0)
                            <li><strong>Top Project:</strong> {{ $top_projects->first()->name }} (₱{{ number_format($top_projects->first()->total_amount, 2) }})</li>
                        @endif
                        @if($top_submitters->count() > 0)
                            <li><strong>Top Submitter:</strong> {{ $top_submitters->first()->name }} (₱{{ number_format($top_submitters->first()->total_amount, 2) }})</li>
                        @endif
                    </ul>
                </div>
            </div>
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
