@extends('app')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Analytics Dashboard</h1>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>
    </div>

    <!-- Analytics Overview Cards -->
    <div class="row mb-4">


        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body text-center">
                    <i class="fas fa-chart-pie fa-3x mb-3"></i>
                    <h4>Financial Reports</h4>
                    <p class="mb-0">Financial reporting and analysis tools</p>
                </div>
                <div class="card-footer bg-transparent border-0">
                    <a href="{{ route('admin.financial-reports.index') }}" class="btn btn-light btn-block">
                        <i class="fas fa-arrow-right"></i> View Reports
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body text-center">
                    <i class="fas fa-chart-bar fa-3x mb-3"></i>
                    <h4>Project Analytics</h4>
                    <p class="mb-0">Project performance and metrics</p>
                </div>
                <div class="card-footer bg-transparent border-0">
                    <a href="{{ route('projects.index') }}" class="btn btn-light btn-block">
                        <i class="fas fa-arrow-right"></i> View Projects
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body text-center">
                    <i class="fas fa-chart-area fa-3x mb-3"></i>
                    <h4>System Analytics</h4>
                    <p class="mb-0">Overall system performance metrics</p>
                </div>
                <div class="card-footer bg-transparent border-0">
                    <a href="{{ route('admin.dashboard') }}" class="btn btn-light btn-block">
                        <i class="fas fa-arrow-right"></i> View Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Statistics -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Quick Statistics</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-2 text-center">
                            <div class="border-right">
                                <h4 class="text-primary">{{ \App\Models\Project::count() }}</h4>
                                <small class="text-muted">Total Projects</small>
                            </div>
                        </div>
                        <div class="col-md-2 text-center">
                            <div class="border-right">
                                <h4 class="text-success">{{ \App\Models\Task::count() }}</h4>
                                <small class="text-muted">Total Tasks</small>
                            </div>
                        </div>
                        <div class="col-md-2 text-center">
                            <div class="border-right">
                                <h4 class="text-info">{{ \App\Models\DailyExpenditure::count() }}</h4>
                                <small class="text-muted">Total Expenses</small>
                            </div>
                        </div>
                        <div class="col-md-2 text-center">
                            <div class="border-right">
                                <h4 class="text-warning">₱{{ number_format(\App\Models\DailyExpenditure::sum('amount'), 2) }}</h4>
                                <small class="text-muted">Total Amount</small>
                            </div>
                        </div>
                        <div class="col-md-2 text-center">
                            <div class="border-right">
                                <h4 class="text-danger">{{ \App\Models\User::count() }}</h4>
                                <small class="text-muted">Total Users</small>
                            </div>
                        </div>
                        <div class="col-md-2 text-center">
                            <div>
                                <h4 class="text-secondary">{{ \App\Models\SiteIssue::count() }}</h4>
                                <small class="text-muted">Site Issues</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Analytics Tools -->
    <div class="row">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Expense Reports</h6>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">

                        <a href="{{ route('admin.expenditures.reports.analytics') }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            <div>
                                <i class="fas fa-file-alt text-info"></i>
                                <span class="ml-2">Detailed Analytics Report</span>
                            </div>
                            <i class="fas fa-arrow-right"></i>
                        </a>
                        <a href="{{ route('admin.expenditures.reports.detailed') }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            <div>
                                <i class="fas fa-table text-warning"></i>
                                <span class="ml-2">Detailed Expenditure Report</span>
                            </div>
                            <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Other Analytics</h6>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        <a href="{{ route('admin.liquidated-forms.index') }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            <div>
                                <i class="fas fa-file-invoice-dollar text-primary"></i>
                                <span class="ml-2">Liquidated Forms</span>
                            </div>
                            <i class="fas fa-arrow-right"></i>
                        </a>
                        <a href="{{ route('admin.receipts.index') }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            <div>
                                <i class="fas fa-receipt text-success"></i>
                                <span class="ml-2">Receipts Management</span>
                            </div>
                            <i class="fas fa-arrow-right"></i>
                        </a>
                        <a href="{{ route('admin.financial-reports.index') }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            <div>
                                <i class="fas fa-chart-bar text-info"></i>
                                <span class="ml-2">Financial Reports</span>
                            </div>
                            <i class="fas fa-arrow-right"></i>
                        </a>
                        <a href="{{ route('admin.equipment-monitoring.index') }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            <div>
                                <i class="fas fa-tools text-warning"></i>
                                <span class="ml-2">Equipment Monitoring</span>
                            </div>
                            <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Recent Activity</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Activity</th>
                                    <th>User</th>
                                    <th>Details</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $recentActivities = collect([
                                        (object)[
                                            'date' => now()->subHours(2),
                                            'activity' => 'New expenditure submitted',
                                            'user' => 'John Doe',
                                            'details' => 'Project A - ₱5,000'
                                        ],
                                        (object)[
                                            'date' => now()->subHours(4),
                                            'activity' => 'Financial report generated',
                                            'user' => 'Jane Smith',
                                            'details' => 'Q1 2024 Report'
                                        ],
                                        (object)[
                                            'date' => now()->subHours(6),
                                            'activity' => 'Equipment request approved',
                                            'user' => 'Mike Johnson',
                                            'details' => 'Laptop for Project B'
                                        ],
                                        (object)[
                                            'date' => now()->subHours(8),
                                            'activity' => 'Site issue reported',
                                            'user' => 'Sarah Wilson',
                                            'details' => 'Critical issue at Site X'
                                        ]
                                    ]);
                                @endphp

                                @foreach($recentActivities as $activity)
                                <tr>
                                    <td>{{ $activity->date->format('M d, Y H:i') }}</td>
                                    <td>{{ $activity->activity }}</td>
                                    <td>{{ $activity->user }}</td>
                                    <td>{{ $activity->details }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Add any interactive features here
    console.log('Analytics Dashboard loaded');
});
</script>
@endpush
