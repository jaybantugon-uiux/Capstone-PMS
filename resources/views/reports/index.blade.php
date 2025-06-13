@extends('app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0 text-gray-800">Reports Dashboard</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Reports</li>
                    </ol>
                </nav>
            </div>

            <!-- Report Cards -->
            <div class="row">
                <!-- Project Report Card -->
                <div class="col-xl-4 col-md-6 mb-4">
                    <div class="card border-left-primary shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                        Project Reports
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        Analyze project performance and completion rates
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-project-diagram fa-2x text-gray-300"></i>
                                </div>
                            </div>
                            <div class="mt-3">
                                <a href="{{ route('reports.project') }}" class="btn btn-primary btn-sm">
                                    <i class="fas fa-chart-bar"></i> View Project Report
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Task Report Card -->
                <div class="col-xl-4 col-md-6 mb-4">
                    <div class="card border-left-success shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                        Task Reports
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        Track task completion and status distribution
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-tasks fa-2x text-gray-300"></i>
                                </div>
                            </div>
                            <div class="mt-3">
                                <a href="{{ route('reports.task') }}" class="btn btn-success btn-sm">
                                    <i class="fas fa-list-check"></i> View Task Report
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Performance Report Card -->
                <div class="col-xl-4 col-md-6 mb-4">
                    <div class="card border-left-info shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                        Performance Reports
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        Evaluate site coordinator performance metrics
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-chart-line fa-2x text-gray-300"></i>
                                </div>
                            </div>
                            <div class="mt-3">
                                <a href="{{ route('reports.performance') }}" class="btn btn-info btn-sm">
                                    <i class="fas fa-user-chart"></i> View Performance Report
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Stats -->
            <div class="row">
                <div class="col-12">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">
                                <i class="fas fa-info-circle"></i> Report Guidelines
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <h6 class="text-primary">
                                        <i class="fas fa-project-diagram"></i> Project Reports
                                    </h6>
                                    <ul class="text-sm">
                                        <li>View project completion rates</li>
                                        <li>Track project timelines</li>
                                        <li>Identify overdue projects</li>
                                        <li>Export data to CSV</li>
                                    </ul>
                                </div>
                                <div class="col-md-4">
                                    <h6 class="text-success">
                                        <i class="fas fa-tasks"></i> Task Reports
                                    </h6>
                                    <ul class="text-sm">
                                        <li>Analyze task distribution</li>
                                        <li>Filter by status and assignee</li>
                                        <li>Monitor task completion trends</li>
                                        <li>Export filtered results</li>
                                    </ul>
                                </div>
                                <div class="col-md-4">
                                    <h6 class="text-info">
                                        <i class="fas fa-chart-line"></i> Performance Reports
                                    </h6>
                                    <ul class="text-sm">
                                        <li>Site coordinator performance metrics</li>
                                        <li>Completion rate analysis</li>
                                        <li>Overdue task tracking</li>
                                        <li>Performance scoring</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.card {
    transition: transform 0.3s ease-in-out;
}

.card:hover {
    transform: translateY(-5px);
}

.border-left-primary {
    border-left: 0.25rem solid #4e73df !important;
}

.border-left-success {
    border-left: 0.25rem solid #1cc88a !important;
}

.border-left-info {
    border-left: 0.25rem solid #36b9cc !important;
}

.text-sm {
    font-size: 0.875rem;
}

.text-gray-800 {
    color: #5a5c69 !important;
}

.text-gray-300 {
    color: #dddfeb !important;
}
</style>
@endsection