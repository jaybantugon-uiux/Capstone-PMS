@extends('app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h2">Admin Dashboard</h1>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.analytics.index') }}" class="btn btn-secondary">
                        <i class="fas fa-chart-line"></i> Analytics
                    </a>
                    <a href="{{ route('projects.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> New Project
                    </a>
                    <a href="{{ route('tasks.create') }}" class="btn btn-success">
                        <i class="fas fa-tasks"></i> New Task
                    </a>
                    <a href="{{ route('admin.progress-reports.create') }}" class="btn btn-info">
                        <i class="fas fa-file-alt"></i> New Progress Report
                    </a>
                    <a href="{{ route('admin.equipment-monitoring.create-request') }}" class="btn btn-warning">
                        <i class="fas fa-hammer"></i> Request Equipment
                    </a>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card bg-primary text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4 class="card-title">{{ $totalProjects }}</h4>
                                    <p class="card-text">Total Projects</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-project-diagram fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-success text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4 class="card-title">{{ $activeProjects }}</h4>
                                    <p class="card-text">Active Projects</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-play-circle fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-info text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4 class="card-title">{{ $totalTasks }}</h4>
                                    <p class="card-text">Total Tasks</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-tasks fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-warning text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4 class="card-title">{{ $overdueTasksCount }}</h4>
                                    <p class="card-text">Overdue Tasks</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-exclamation-triangle fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- NEW: Admin Equipment Monitoring Card -->
            <div class="row mb-4">
                <div class="col-md-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div>
                                    <h5 class="card-title text-dark mb-2">
                                        <i class="fas fa-screwdriver-wrench me-2"></i>My Equipment Monitoring
                                    </h5>
                                    <p class="text-muted mb-0">Manage your personal and project equipment requests</p>
                                </div>
                                <div class="text-dark">
                                    <i class="fas fa-user-cog fa-3x opacity-75"></i>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-3">
                                    <div class="bg-light p-3 rounded text-center">
                                        <h5 class="text-primary mb-1">{{ $adminEquipmentStats['total_equipment'] ?? 0 }}</h5>
                                        <small class="text-muted">My Equipment</small>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="bg-light p-3 rounded text-center">
                                        <h5 class="text-warning mb-1">{{ $adminEquipmentStats['pending_requests'] ?? 0 }}</h5>
                                        <small class="text-muted">Pending Requests</small>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="bg-light p-3 rounded text-center">
                                        <h5 class="text-success mb-1">{{ $adminEquipmentStats['approved_requests'] ?? 0 }}</h5>
                                        <small class="text-muted">Approved</small>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="bg-light p-3 rounded text-center">
                                        <h5 class="text-danger mb-1">{{ $adminEquipmentStats['overdue_maintenance'] ?? 0 }}</h5>
                                        <small class="text-muted">Overdue Maintenance</small>
                                    </div>
                                </div>
                            </div>

                            <!-- Alert for Urgent Items -->
                            @if(($adminEquipmentStats['overdue_maintenance'] ?? 0) > 0)
                                <div class="alert alert-warning mb-3">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    <strong>{{ $adminEquipmentStats['overdue_maintenance'] }}</strong> maintenance tasks are overdue for your equipment.
                                </div>
                            @endif

                            <div class="d-flex flex-wrap gap-2">
                                <a href="{{ route('admin.equipment-monitoring.my-dashboard') }}" class="btn btn-dark btn-sm">
                                    <i class="fas fa-gauge me-1"></i>My Dashboard
                                </a>
                                <a href="{{ route('admin.equipment-monitoring.create-request') }}" class="btn btn-primary btn-sm">
                                    <i class="fas fa-plus me-1"></i>New Request
                                </a>
                                <a href="{{ route('admin.equipment-monitoring.my-equipment') }}" class="btn btn-outline-info btn-sm">
                                    <i class="fas fa-cubes me-1"></i>My Equipment
                                    <span class="badge bg-info ms-1">{{ $adminEquipmentStats['total_equipment'] ?? 0 }}</span>
                                </a>
                                <a href="{{ route('admin.equipment-monitoring.my-maintenance') }}" class="btn btn-outline-warning btn-sm">
                                    <i class="fas fa-wrench me-1"></i>My Maintenance
                                    @if(($adminEquipmentStats['overdue_maintenance'] ?? 0) > 0)
                                        <span class="badge bg-danger ms-1">{{ $adminEquipmentStats['overdue_maintenance'] }}</span>
                                    @endif
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Equipment Monitoring Management Card -->
            <div class="row mb-4">
                <div class="col-md-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div>
                                    <h5 class="card-title text-dark mb-2">
                                        <i class="fas fa-screwdriver-wrench me-2"></i>Equipment Monitoring Management
                                    </h5>
                                    <p class="text-muted mb-0">Approve requests, track monitored equipment, and manage maintenance</p>
                                </div>
                                <div class="text-dark">
                                    <i class="fas fa-cogs fa-3x opacity-75"></i>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-3">
                                    <div class="bg-light p-3 rounded text-center">
                                        <h5 class="text-primary mb-1">{{ $equipmentMonitoringStats['total_requests'] ?? 0 }}</h5>
                                        <small class="text-muted">Total Requests</small>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="bg-light p-3 rounded text-center">
                                        <h5 class="text-warning mb-1">{{ $equipmentMonitoringStats['pending_requests'] ?? 0 }}</h5>
                                        <small class="text-muted">Pending</small>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="bg-light p-3 rounded text-center">
                                        <h5 class="text-success mb-1">{{ $equipmentMonitoringStats['approved_requests'] ?? 0 }}</h5>
                                        <small class="text-muted">Approved</small>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="bg-light p-3 rounded text-center">
                                        <h5 class="text-danger mb-1">{{ $equipmentMonitoringStats['maintenance_overdue'] ?? 0 }}</h5>
                                        <small class="text-muted">Overdue Maintenance</small>
                                    </div>
                                </div>
                            </div>

                            <!-- Alert for Urgent Items -->
                            @if(($equipmentMonitoringStats['urgent_requests'] ?? 0) > 0 || ($equipmentMonitoringStats['maintenance_overdue'] ?? 0) > 0)
                                <div class="alert alert-warning mb-3">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    @if(($equipmentMonitoringStats['urgent_requests'] ?? 0) > 0)
                                        <strong>{{ $equipmentMonitoringStats['urgent_requests'] }}</strong> urgent equipment requests need attention.
                                    @endif
                                    @if(($equipmentMonitoringStats['maintenance_overdue'] ?? 0) > 0)
                                        <strong>{{ $equipmentMonitoringStats['maintenance_overdue'] }}</strong> maintenance tasks are overdue.
                                    @endif
                                </div>
                            @endif

                            <div class="d-flex flex-wrap gap-2">
                                <a href="{{ route('admin.equipment-monitoring.index') }}" class="btn btn-dark btn-sm">
                                    <i class="fas fa-gauge me-1"></i>Management Dashboard
                                </a>
                                <a href="{{ route('admin.equipment-monitoring.requests') }}" class="btn btn-outline-primary btn-sm">
                                    <i class="fas fa-clipboard-list me-1"></i>All Requests
                                    @if(($equipmentMonitoringStats['pending_requests'] ?? 0) > 0)
                                        <span class="badge bg-warning text-dark ms-1">{{ $equipmentMonitoringStats['pending_requests'] }}</span>
                                    @endif
                                </a>
                                <a href="{{ route('admin.equipment-monitoring.equipment-list') }}" class="btn btn-outline-info btn-sm">
                                    <i class="fas fa-cubes me-1"></i>All Equipment
                                    <span class="badge bg-info ms-1">{{ $equipmentMonitoringStats['total_equipment'] ?? 0 }}</span>
                                </a>
                                <a href="{{ route('admin.equipment-monitoring.maintenance-list') }}" class="btn btn-outline-warning btn-sm">
                                    <i class="fas fa-wrench me-1"></i>All Maintenance
                                    @if(($equipmentMonitoringStats['maintenance_overdue'] ?? 0) > 0)
                                        <span class="badge bg-danger ms-1">{{ $equipmentMonitoringStats['maintenance_overdue'] }}</span>
                                    @endif
                                </a>
                                <a href="{{ route('admin.equipment-monitoring.report-equipment-status') }}" class="btn btn-outline-secondary btn-sm">
                                    <i class="fas fa-download me-1"></i>Export Status CSV
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- NEW: Progress Reports Management Card -->
            <div class="row mb-4">
                <div class="col-md-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div>
                                    <h5 class="card-title text-info mb-2">
                                        <i class="fas fa-file-chart-line me-2"></i>Progress Reports Management
                                    </h5>
                                    <p class="text-muted mb-0">Create and manage progress reports for clients</p>
                                </div>
                                <div class="text-info">
                                    <i class="fas fa-chart-line fa-3x opacity-75"></i>
                                </div>
                            </div>
                            
                            <!-- Progress Reports Statistics -->
                            <div class="row mb-3">
                                <div class="col-md-3">
                                    <div class="bg-light p-3 rounded text-center">
                                        <h5 class="text-primary mb-1">{{ $progressReportsStats['total'] ?? 0 }}</h5>
                                        <small class="text-muted">Total Reports</small>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="bg-light p-3 rounded text-center">
                                        <h5 class="text-warning mb-1">{{ $progressReportsStats['sent'] ?? 0 }}</h5>
                                        <small class="text-muted">Sent</small>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="bg-light p-3 rounded text-center">
                                        <h5 class="text-success mb-1">{{ $progressReportsStats['viewed'] ?? 0 }}</h5>
                                        <small class="text-muted">Viewed</small>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="bg-light p-3 rounded text-center">
                                        <h5 class="text-info mb-1">{{ $progressReportsStats['recent'] ?? 0 }}</h5>
                                        <small class="text-muted">Recent (7 days)</small>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="d-flex gap-2">
                                <a href="{{ route('admin.progress-reports.index') }}" class="btn btn-info btn-sm">
                                    <i class="fas fa-list me-1"></i>View All Reports
                                </a>
                                <a href="{{ route('admin.progress-reports.create') }}" class="btn btn-outline-info btn-sm">
                                    <i class="fas fa-plus me-1"></i>New Report
                                </a>
                                <a href="{{ route('admin.progress-reports.index', ['status' => 'sent']) }}" class="btn btn-outline-warning btn-sm">
                                    <i class="fas fa-paper-plane me-1"></i>Sent Reports
                                </a>
                                <a href="{{ route('admin.progress-reports.export') }}" class="btn btn-outline-secondary btn-sm">
                                    <i class="fas fa-download me-1"></i>Export CSV
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- NEW: Expense Liquidation Subsystem Management Card -->
            <div class="row mb-4">
                <div class="col-md-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div>
                                    <h5 class="card-title text-success mb-2">
                                        <i class="fas fa-file-invoice-dollar me-2"></i>Expense Liquidation Subsystem
                                    </h5>
                                    <p class="text-muted mb-0">Manage financial reports, liquidated forms, expenditures, and receipts</p>
                                </div>
                                <div class="text-success">
                                    <i class="fas fa-chart-pie fa-3x opacity-75"></i>
                                </div>
                            </div>
                            
                            <!-- Expense Liquidation Statistics -->
                            <div class="row mb-3">
                                <div class="col-md-2">
                                    <div class="bg-light p-3 rounded text-center">
                                        <h5 class="text-primary mb-1">{{ $liquidatedFormsStats['total'] ?? 0 }}</h5>
                                        <small class="text-muted">Liquidated Forms</small>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="bg-light p-3 rounded text-center">
                                        <h5 class="text-info mb-1">{{ $financialReportsStats['total'] ?? 0 }}</h5>
                                        <small class="text-muted">Financial Reports</small>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="bg-light p-3 rounded text-center">
                                        <h5 class="text-warning mb-1">{{ $dailyExpendituresStats['total'] ?? 0 }}</h5>
                                        <small class="text-muted">Daily Expenditures</small>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="bg-light p-3 rounded text-center">
                                        <h5 class="text-success mb-1">{{ $receiptsStats['total'] ?? 0 }}</h5>
                                        <small class="text-muted">Receipts</small>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="bg-light p-3 rounded text-center">
                                        <h5 class="text-danger mb-1">{{ $liquidatedFormsStats['flagged'] ?? 0 }}</h5>
                                        <small class="text-muted">Flagged Forms</small>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="bg-light p-3 rounded text-center">
                                        <h5 class="text-secondary mb-1">₱{{ number_format($liquidatedFormsStats['total_amount'] ?? 0, 2) }}</h5>
                                        <small class="text-muted">Total Amount</small>
                                    </div>
                                </div>
                            </div>

                            <!-- Alert for Urgent Items -->
                            @if(($liquidatedFormsStats['flagged'] ?? 0) > 0 || ($liquidatedFormsStats['revision_requested'] ?? 0) > 0 || ($liquidatedFormsStats['clarification_requested'] ?? 0) > 0)
                                <div class="alert alert-warning mb-3">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    @if(($liquidatedFormsStats['flagged'] ?? 0) > 0)
                                        <strong>{{ $liquidatedFormsStats['flagged'] }}</strong> liquidated forms are flagged for review.
                                    @endif
                                    @if(($liquidatedFormsStats['revision_requested'] ?? 0) > 0)
                                        <strong>{{ $liquidatedFormsStats['revision_requested'] }}</strong> forms need revision.
                                    @endif
                                    @if(($liquidatedFormsStats['clarification_requested'] ?? 0) > 0)
                                        <strong>{{ $liquidatedFormsStats['clarification_requested'] }}</strong> forms need clarification.
                                    @endif
                                </div>
                            @endif
                            
                            <div class="d-flex flex-wrap gap-2">
                                <!-- Liquidated Forms Management -->
                                <div class="dropdown">
                                    <button class="btn btn-success btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                        <i class="fas fa-file-invoice-dollar me-1"></i>Liquidated Forms
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" href="{{ route('admin.liquidated-forms.index') }}">
                                            <i class="fas fa-list me-1"></i>View All Forms
                                        </a></li>
                                        <li><a class="dropdown-item" href="{{ route('admin.liquidated-forms.index', ['status' => 'flagged']) }}">
                                            <i class="fas fa-flag me-1"></i>Flagged Forms
                                            @if(($liquidatedFormsStats['flagged'] ?? 0) > 0)
                                                <span class="badge bg-danger ms-1">{{ $liquidatedFormsStats['flagged'] }}</span>
                                            @endif
                                        </a></li>
                                        <li><a class="dropdown-item" href="{{ route('admin.liquidated-forms.index', ['status' => 'revision_requested']) }}">
                                            <i class="fas fa-edit me-1"></i>Revision Requested
                                            @if(($liquidatedFormsStats['revision_requested'] ?? 0) > 0)
                                                <span class="badge bg-warning ms-1">{{ $liquidatedFormsStats['revision_requested'] }}</span>
                                            @endif
                                        </a></li>

                                    </ul>
                                </div>

                                <!-- Financial Reports Management -->
                                <div class="dropdown">
                                    <button class="btn btn-info btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                        <i class="fas fa-chart-line me-1"></i>Financial Reports
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" href="{{ route('admin.financial-reports.index') }}">
                                            <i class="fas fa-list me-1"></i>View All Reports
                                        </a></li>
                                        <li><a class="dropdown-item" href="{{ route('admin.financial-reports.index', ['status' => 'draft']) }}">
                                            <i class="fas fa-edit me-1"></i>Draft Reports
                                            <span class="badge bg-secondary ms-1">{{ $financialReportsStats['draft'] ?? 0 }}</span>
                                        </a></li>
                                        <li><a class="dropdown-item" href="{{ route('admin.financial-reports.index', ['status' => 'generated']) }}">
                                            <i class="fas fa-check me-1"></i>Generated Reports
                                            <span class="badge bg-success ms-1">{{ $financialReportsStats['generated'] ?? 0 }}</span>
                                        </a></li>
                                        <li><a class="dropdown-item" href="{{ route('admin.financial-reports.index', ['status' => 'liquidated']) }}">
                                            <i class="fas fa-file-invoice me-1"></i>Liquidated Reports
                                            <span class="badge bg-info ms-1">{{ $financialReportsStats['liquidated'] ?? 0 }}</span>
                                        </a></li>
                                    </ul>
                                </div>

                                <!-- Daily Expenditures Management -->
                                <div class="dropdown">
                                    <button class="btn btn-warning btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                        <i class="fas fa-receipt me-1"></i>Daily Expenditures
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" href="{{ route('admin.expenditures.index') }}">
                                            <i class="fas fa-list me-1"></i>View All Expenditures
                                        </a></li>

                                    </ul>
                                </div>

                                <!-- Receipts Management -->
                                <div class="dropdown">
                                    <button class="btn btn-primary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                        <i class="fas fa-file-upload me-1"></i>Receipts
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" href="{{ route('admin.receipts.index') }}">
                                            <i class="fas fa-list me-1"></i>View All Receipts
                                        </a></li>

                                    </ul>
                                </div>


                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Site Photos Management Card -->
            <div class="row mb-4">
                <div class="col-md-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div>
                                    <h5 class="card-title text-success mb-2">
                                        <i class="fas fa-camera me-2"></i>Site Photos Management
                                    </h5>
                                    <p class="text-muted mb-0">Review and manage photo submissions from site coordinators</p>
                                </div>
                                <div class="text-success">
                                    <i class="fas fa-chart-pie fa-3x opacity-75"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Task Status Overview -->
            <div class="row mb-4">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Task Status Overview</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3 text-center">
                                    <div class="bg-light p-3 rounded">
                                        <h4 class="text-secondary">{{ $pendingTasks }}</h4>
                                        <p class="mb-0">Pending</p>
                                    </div>
                                </div>
                                <div class="col-md-3 text-center">
                                    <div class="bg-light p-3 rounded">
                                        <h4 class="text-warning">{{ $inProgressTasks }}</h4>
                                        <p class="mb-0">In Progress</p>
                                    </div>
                                </div>
                                <div class="col-md-3 text-center">
                                    <div class="bg-light p-3 rounded">
                                        <h4 class="text-success">{{ $completedTasks }}</h4>
                                        <p class="mb-0">Completed</p>
                                    </div>
                                </div>
                                <div class="col-md-3 text-center">
                                    <div class="bg-light p-3 rounded">
                                        <h4 class="text-danger">{{ $overdueTasksCount }}</h4>
                                        <p class="mb-0">Overdue</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- NEW: Recent Progress Reports Section -->
            <div class="row mb-4">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">Recent Progress Reports</h5>
                            <a href="{{ route('admin.progress-reports.index') }}" class="btn btn-sm btn-outline-info">View All</a>
                        </div>
                        <div class="card-body">
                            @if(isset($recentProgressReports) && $recentProgressReports->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
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
                                            @foreach($recentProgressReports as $report)
                                                <tr>
                                                    <td>
                                                        <strong>{{ Str::limit($report->title, 30) }}</strong>
                                                        @if($report->hasAttachment())
                                                            <br><i class="fas fa-paperclip text-muted"></i> <small class="text-muted">Has attachment</small>
                                                        @endif
                                                    </td>
                                                    <td>{{ $report->client->first_name }} {{ $report->client->last_name }}</td>
                                                    <td>{{ $report->project ? Str::limit($report->project->name, 20) : 'General' }}</td>
                                                    <td>
                                                        {{ $report->creator->first_name }} {{ $report->creator->last_name }}
                                                        <br><span class="badge bg-{{ $report->creator_role_badge_color }}">{{ $report->formatted_creator_role }}</span>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-{{ $report->status_color }}">
                                                            {{ $report->formatted_status }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-light text-dark">{{ $report->view_count }}</span>
                                                    </td>
                                                    <td>
                                                        {{ $report->created_at->format('M d, Y') }}
                                                        <br><small class="text-muted">{{ $report->created_at->diffForHumans() }}</small>
                                                    </td>
                                                    <td>
                                                        <a href="{{ route('admin.progress-reports.show', $report) }}" 
                                                           class="btn btn-outline-primary btn-sm" title="View Report">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-center py-4">
                                    <i class="fas fa-file-alt fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">No progress reports yet</p>
                                    <a href="{{ route('admin.progress-reports.create') }}" class="btn btn-info">
                                        <i class="fas fa-plus me-1"></i>Create First Report
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- NEW: Recent Expense Liquidation Items Section -->
            <div class="row mb-4">
                <!-- Recent Liquidated Forms -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">Recent Liquidated Forms</h5>
                            <a href="{{ route('admin.liquidated-forms.index') }}" class="btn btn-sm btn-outline-success">View All</a>
                        </div>
                        <div class="card-body">
                            @if(isset($recentLiquidatedForms) && $recentLiquidatedForms->count() > 0)
                                <div class="list-group list-group-flush">
                                    @foreach($recentLiquidatedForms as $form)
                                        <div class="list-group-item px-0">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div class="flex-grow-1">
                                                    <h6 class="mb-1">
                                                        <a href="{{ route('admin.liquidated-forms.show', $form) }}" class="text-decoration-none">
                                                            {{ $form->form_number }}
                                                        </a>
                                                    </h6>
                                                    <p class="mb-1 text-muted small">{{ Str::limit($form->title, 60) }}</p>
                                                    <small class="text-muted">
                                                        {{ $form->project->name ?? 'N/A' }} • 
                                                        {{ $form->preparer->first_name ?? 'N/A' }} {{ $form->preparer->last_name ?? '' }} • 
                                                        {{ $form->created_at->diffForHumans() }}
                                                    </small>
                                                </div>
                                                <div class="text-end">
                                                    <span class="badge bg-{{ $form->status_color }}">
                                                        {{ ucfirst(str_replace('_', ' ', $form->status)) }}
                                                    </span>
                                                    <br><small class="text-muted">₱{{ number_format($form->total_amount, 2) }}</small>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-muted text-center py-3">No recent liquidated forms available</p>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Recent Financial Reports -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">Recent Financial Reports</h5>
                            <a href="{{ route('admin.financial-reports.index') }}" class="btn btn-sm btn-outline-info">View All</a>
                        </div>
                        <div class="card-body">
                            @if(isset($recentFinancialReports) && $recentFinancialReports->count() > 0)
                                <div class="list-group list-group-flush">
                                    @foreach($recentFinancialReports as $report)
                                        <div class="list-group-item px-0">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div class="flex-grow-1">
                                                    <h6 class="mb-1">
                                                        <a href="{{ route('admin.financial-reports.show', $report) }}" class="text-decoration-none">
                                                            {{ Str::limit($report->title, 40) }}
                                                        </a>
                                                    </h6>
                                                    <p class="mb-1 text-muted small">{{ $report->project->name ?? 'N/A' }}</p>
                                                    <small class="text-muted">
                                                        {{ $report->creator->first_name ?? 'N/A' }} {{ $report->creator->last_name ?? '' }} • 
                                                        {{ $report->created_at->diffForHumans() }}
                                                    </small>
                                                </div>
                                                <div class="text-end">
                                                    <span class="badge bg-{{ $report->status_badge_color }}">
                                                        {{ ucfirst($report->status) }}
                                                    </span>
                                                    <br><small class="text-muted">₱{{ number_format($report->total_expenditures, 2) }}</small>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-muted text-center py-3">No recent financial reports available</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Daily Expenditures and Receipts -->
            <div class="row mb-4">
                <!-- Recent Daily Expenditures -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">Recent Daily Expenditures</h5>
                            <a href="{{ route('admin.expenditures.index') }}" class="btn btn-sm btn-outline-warning">View All</a>
                        </div>
                        <div class="card-body">
                            @if(isset($recentDailyExpenditures) && $recentDailyExpenditures->count() > 0)
                                <div class="list-group list-group-flush">
                                    @foreach($recentDailyExpenditures as $expenditure)
                                        <div class="list-group-item px-0">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div class="flex-grow-1">
                                                    <h6 class="mb-1">
                                                        <a href="{{ route('admin.expenditures.show', $expenditure) }}" class="text-decoration-none">
                                                            {{ Str::limit($expenditure->purpose, 50) }}
                                                        </a>
                                                    </h6>
                                                    <p class="mb-1 text-muted small">{{ $expenditure->project->name ?? 'N/A' }}</p>
                                                    <small class="text-muted">
                                                        {{ $expenditure->submitter->first_name ?? 'N/A' }} {{ $expenditure->submitter->last_name ?? '' }} • 
                                                        {{ $expenditure->created_at->diffForHumans() }}
                                                    </small>
                                                </div>
                                                <div class="text-end">
                                                    <small class="text-muted">₱{{ number_format($expenditure->amount, 2) }}</small>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-muted text-center py-3">No recent daily expenditures available</p>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Recent Receipts -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">Recent Receipts</h5>
                            <a href="{{ route('admin.receipts.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
                        </div>
                        <div class="card-body">
                            @if(isset($recentReceipts) && $recentReceipts->count() > 0)
                                <div class="list-group list-group-flush">
                                    @foreach($recentReceipts as $receipt)
                                        <div class="list-group-item px-0">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div class="flex-grow-1">
                                                    <h6 class="mb-1">
                                                        <a href="{{ route('admin.receipts.show', $receipt) }}" class="text-decoration-none">
                                                            {{ Str::limit($receipt->original_file_name, 40) }}
                                                        </a>
                                                    </h6>
                                                    <p class="mb-1 text-muted small">{{ $receipt->vendor_name ?? 'N/A' }}</p>
                                                    <small class="text-muted">
                                                        {{ $receipt->uploader->first_name ?? 'N/A' }} {{ $receipt->uploader->last_name ?? '' }} • 
                                                        {{ $receipt->created_at->diffForHumans() }}
                                                    </small>
                                                </div>
                                                <div class="text-end">
                                                    <small class="text-muted">₱{{ number_format($receipt->amount, 2) }}</small>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-muted text-center py-3">No recent receipts available</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="row mt-4">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Quick Actions</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-2">
                                    <a href="{{ route('projects.index') }}" class="btn btn-outline-primary w-100 mb-2 h-100 d-flex flex-column justify-content-center align-items-center">
                                        <i class="fas fa-project-diagram fa-2x mb-2"></i>
                                        <span>Manage Projects</span>
                                    </a>
                                </div>
                                <div class="col-md-2">
                                    <a href="{{ route('tasks.index') }}" class="btn btn-outline-success w-100 mb-2 h-100 d-flex flex-column justify-content-center align-items-center">
                                        <i class="fas fa-tasks fa-2x mb-2"></i>
                                        <span>Manage Tasks</span>
                                    </a>
                                </div>
                                <div class="col-md-2">
                                    <a href="{{ route('admin.progress-reports.index') }}" class="btn btn-outline-info w-100 mb-2 h-100 d-flex flex-column justify-content-center align-items-center">
                                        <i class="fas fa-file-chart-line fa-2x mb-2"></i>
                                        <span>Progress Reports</span>
                                    </a>
                                </div>
                                <div class="col-md-2">
                                    <a href="{{ route('admin.site-photos.index') }}" class="btn btn-outline-success w-100 mb-2 h-100 d-flex flex-column justify-content-center align-items-center">
                                        <i class="fas fa-camera fa-2x mb-2"></i>
                                        <span>Site Photos</span>
                                    </a>
                                </div>
                                <div class="col-md-2">
                                    <a href="{{ route('admin.site-issues.index') }}" class="btn btn-outline-danger w-100 mb-2 h-100 d-flex flex-column justify-content-center align-items-center">
                                        <i class="fas fa-exclamation-triangle fa-2x mb-2"></i>
                                        <span>Site Issues</span>
                                    </a>
                                </div>
                                <div class="col-md-2">
                                    <a href="{{ route('admin.task-reports.index') }}" class="btn btn-outline-info w-100 mb-2 h-100 d-flex flex-column justify-content-center align-items-center">
                                        <i class="fas fa-clipboard-check fa-2x mb-2"></i>
                                        <span>Task Reports</span>
                                    </a>
                                </div>
                                <div class="col-md-2">
                                    <a href="{{ route('equipment.index') }}" class="btn btn-outline-info w-100 mb-2 h-100 d-flex flex-column justify-content-center align-items-center">
                                        <i class="fas fa-boxes fa-2x mb-2"></i>
                                        <span>Equipment</span>
                                    </a>
                                </div>
                                <div class="col-md-2">
                                    <a href="{{ route('admin.equipment-monitoring.my-dashboard') }}" class="btn btn-outline-warning w-100 mb-2 h-100 d-flex flex-column justify-content-center align-items-center">
                                        <i class="fas fa-hammer fa-2x mb-2"></i>
                                        <span>My Equipment</span>
                                    </a>
                                </div>
                                <div class="col-md-2">
                                    <a href="{{ route('projects.archived') }}" class="btn btn-outline-secondary w-100 mb-2 h-100 d-flex flex-column justify-content-center align-items-center">
                                        <i class="fas fa-archive fa-2x mb-2"></i>
                                        <span>Archived Projects</span>
                                    </a>
                                </div>
                                <div class="col-md-2">
                                    <a href="{{ route('tasks.archived') }}" class="btn btn-outline-secondary w-100 mb-2 h-100 d-flex flex-column justify-content-center align-items-center">
                                        <i class="fas fa-archive fa-2x mb-2"></i>
                                        <span>Archived Tasks</span>
                                    </a>
                                </div>
                                <div class="col-md-2">
                                    <a href="{{ route('reports.view-staff') }}" class="btn btn-outline-info w-100 mb-2 h-100 d-flex flex-column justify-content-center align-items-center">
                                        <i class="fas fa-users fa-2x mb-2"></i>
                                        <span>View Staff</span>
                                    </a>
                                </div>
                                <div class="col-md-2">
                                    <a href="{{ route('tasks.calendar') }}" class="btn btn-outline-secondary w-100 mb-2 h-100 d-flex flex-column justify-content-center align-items-center">
                                        <i class="fas fa-calendar-alt fa-2x mb-2"></i>
                                        <span>Calendar</span>
                                    </a>
                                </div>
                                <div class="col-md-2">
                                    <a href="{{ route('reports.generate') }}" class="btn btn-outline-warning w-100 mb-2 h-100 d-flex flex-column justify-content-center align-items-center">
                                        <i class="fas fa-chart-line fa-2x mb-2"></i>
                                        <span>Generate Reports</span>
                                    </a>
                                </div>
                                <div class="col-md-2">
                                    <a href="{{ route('photos.featured') }}" class="btn btn-outline-warning w-100 mb-2 h-100 d-flex flex-column justify-content-center align-items-center">
                                        <i class="fas fa-star fa-2x mb-2"></i>
                                        <span>Featured Photos</span>
                                    </a>
                                </div>
                                <!-- Expense Liquidation Quick Actions -->
                                <div class="col-md-2">
                                    <a href="{{ route('admin.liquidated-forms.index') }}" class="btn btn-outline-success w-100 mb-2 h-100 d-flex flex-column justify-content-center align-items-center">
                                        <i class="fas fa-file-invoice-dollar fa-2x mb-2"></i>
                                        <span>Liquidated Forms</span>
                                    </a>
                                </div>
                                <div class="col-md-2">
                                    <a href="{{ route('admin.financial-reports.index') }}" class="btn btn-outline-info w-100 mb-2 h-100 d-flex flex-column justify-content-center align-items-center">
                                        <i class="fas fa-chart-line fa-2x mb-2"></i>
                                        <span>Financial Reports</span>
                                    </a>
                                </div>
                                <div class="col-md-2">
                                    <a href="{{ route('admin.expenditures.index') }}" class="btn btn-outline-warning w-100 mb-2 h-100 d-flex flex-column justify-content-center align-items-center">
                                        <i class="fas fa-receipt fa-2x mb-2"></i>
                                        <span>Daily Expenditures</span>
                                    </a>
                                </div>
                                <div class="col-md-2">
                                    <a href="{{ route('admin.receipts.index') }}" class="btn btn-outline-primary w-100 mb-2 h-100 d-flex flex-column justify-content-center align-items-center">
                                        <i class="fas fa-file-upload fa-2x mb-2"></i>
                                        <span>Receipts</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Site Photos Section -->
            <div class="row mb-4">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">Recent Site Photos</h5>
                            <a href="{{ route('admin.site-photos.index') }}" class="btn btn-sm btn-outline-success">View All</a>
                        </div>
                        <div class="card-body">
                            @if(isset($recentSitePhotos) && $recentSitePhotos->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Photo</th>
                                                <th>Title</th>
                                                <th>Category</th>
                                                <th>Status</th>
                                                <th>Project</th>
                                                <th>Uploader</th>
                                                <th>Submitted</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($recentSitePhotos as $photo)
                                                <tr class="{{ $photo->is_overdue_for_review ? 'table-warning' : '' }}">
                                                    <td>
                                                        <img src="{{ $photo->thumbnail_url }}" 
                                                             alt="Photo thumbnail" 
                                                             class="img-thumbnail" 
                                                             style="width: 50px; height: 50px; object-fit: cover;">
                                                    </td>
                                                    <td>
                                                        <strong>{{ Str::limit($photo->title, 25) }}</strong>
                                                        @if($photo->is_featured)
                                                            <br><span class="badge bg-warning"><i class="fas fa-star"></i> Featured</span>
                                                        @endif
                                                        @if($photo->is_overdue_for_review)
                                                            <br><span class="badge bg-danger">Overdue</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-{{ $photo->photo_category_badge_color }}">
                                                            {{ $photo->formatted_photo_category }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-{{ $photo->submission_status_badge_color }}">
                                                            {{ $photo->formatted_submission_status }}
                                                        </span>
                                                    </td>
                                                    <td>{{ Str::limit($photo->project->name, 20) }}</td>
                                                    <td>{{ $photo->uploader->first_name }} {{ $photo->uploader->last_name }}</td>
                                                    <td>
                                                        {{ $photo->formatted_submitted_at ?? 'Not submitted' }}
                                                        @if($photo->submitted_at)
                                                            <br><small class="text-muted">{{ $photo->submitted_at->diffForHumans() }}</small>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <a href="{{ route('admin.site-photos.show', $photo) }}" 
                                                           class="btn btn-outline-primary btn-sm" title="View/Review">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <p class="text-muted text-center py-3">No recent site photos</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Site Issues Section -->
            <div class="row mb-4">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">Recent Site Issues</h5>
                            <a href="{{ route('admin.site-issues.index') }}" class="btn btn-sm btn-outline-danger">View All</a>
                        </div>
                        <div class="card-body">
                            @if(isset($recentSiteIssues) && $recentSiteIssues->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Issue Title</th>
                                                <th>Priority</th>
                                                <th>Type</th>
                                                <th>Status</th>
                                                <th>Project</th>
                                                <th>Reporter</th>
                                                <th>Reported</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($recentSiteIssues as $issue)
                                                <tr class="{{ $issue->needs_attention ? 'table-warning' : '' }}">
                                                    <td>
                                                        <strong>{{ Str::limit($issue->issue_title, 30) }}</strong>
                                                        @if($issue->needs_attention)
                                                            <br><span class="badge bg-warning">Urgent</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-{{ $issue->priority_badge_color }}">
                                                            {{ $issue->formatted_priority }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-{{ $issue->issue_type_badge_color }}">
                                                            {{ $issue->formatted_issue_type }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-{{ $issue->status_badge_color }}">
                                                            {{ $issue->formatted_status }}
                                                        </span>
                                                    </td>
                                                    <td>{{ Str::limit($issue->project->name, 20) }}</td>
                                                    <td>{{ $issue->reporter->first_name }} {{ $issue->reporter->last_name }}</td>
                                                    <td>
                                                        {{ $issue->formatted_reported_at }}
                                                        <br><small class="text-muted">{{ $issue->age }}</small>
                                                    </td>
                                                    <td>
                                                        <a href="{{ route('admin.site-issues.show', $issue) }}" 
                                                           class="btn btn-outline-primary btn-sm" title="View/Manage">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <p class="text-muted text-center py-3">No recent site issues</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Task Reports -->
            <div class="row mb-4">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">Recent Task Reports</h5>
                            <a href="{{ route('admin.task-reports.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
                        </div>
                        <div class="card-body">
                            @if(isset($recentTaskReports) && $recentTaskReports->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Report Title</th>
                                                <th>Task</th>
                                                <th>Submitted By</th>
                                                <th>Progress</th>
                                                <th>Review Status</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($recentTaskReports as $report)
                                                <tr class="{{ $report->is_overdue_for_review ? 'table-warning' : '' }}">
                                                    <td>
                                                        <strong>{{ Str::limit($report->report_title, 30) }}</strong>
                                                        @if($report->is_overdue_for_review)
                                                            <br><span class="badge bg-warning">Overdue</span>
                                                        @endif
                                                    </td>
                                                    <td>{{ Str::limit($report->task->task_name, 25) }}</td>
                                                    <td>{{ $report->user->first_name }} {{ $report->user->last_name }}</td>
                                                    <td>
                                                        <div class="progress" style="height: 15px; width: 60px;">
                                                            <div class="progress-bar bg-{{ $report->progress_color }}" 
                                                                 style="width: {{ $report->progress_percentage }}%">
                                                            </div>
                                                        </div>
                                                        <small>{{ $report->progress_percentage }}%</small>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-{{ $report->review_status_badge_color }}">
                                                            {{ $report->formatted_review_status }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <a href="{{ route('admin.task-reports.show', $report) }}" 
                                                           class="btn btn-outline-primary btn-sm" title="View/Review">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <p class="text-muted text-center py-3">No recent task reports available</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Recent Projects -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">Recent Projects</h5>
                            <a href="{{ route('projects.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
                        </div>
                        <div class="card-body">
                            @if($recentProjects->count() > 0)
                                <div class="list-group list-group-flush">
                                    @foreach($recentProjects as $project)
                                        <div class="list-group-item px-0">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div class="flex-grow-1">
                                                    <h6 class="mb-1">
                                                        <a href="{{ route('projects.show', $project) }}" class="text-decoration-none">
                                                            {{ $project->name }}
                                                        </a>
                                                    </h6>
                                                    <p class="mb-1 text-muted small">{{ Str::limit($project->description, 80) }}</p>
                                                    <small class="text-muted">
                                                        Created by {{ $project->creator->first_name }} {{ $project->creator->last_name }} • 
                                                        {{ $project->created_at->diffForHumans() }}
                                                    </small>
                                                </div>
                                                <div class="text-end">
                                                    <span class="badge bg-{{ $project->status == 'Completed' ? 'success' : ($project->status == 'In Progress' ? 'warning' : 'secondary') }}">
                                                        {{ $project->status }}
                                                    </span>
                                                    @if($project->is_overdue)
                                                        <br><span class="badge bg-danger mt-1">Overdue</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-muted text-center py-3">No projects available</p>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Overdue Tasks -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">Overdue Tasks</h5>
                            <a href="{{ route('tasks.index') }}" class="btn btn-sm btn-outline-danger">View All</a>
                        </div>
                        <div class="card-body">
                            @if($overdueTasks->count() > 0)
                                <div class="list-group list-group-flush">
                                    @foreach($overdueTasks as $task)
                                        <div class="list-group-item px-0">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div class="flex-grow-1">
                                                    <h6 class="mb-1">
                                                        <a href="{{ route('tasks.show', $task) }}" class="text-decoration-none">
                                                            {{ $task->task_name }}
                                                        </a>
                                                    </h6>
                                                    <p class="mb-1 text-muted small">{{ $task->project->name }}</p>
                                                    <small class="text-muted">
                                                        Assigned to {{ $task->siteCoordinator->first_name }} {{ $task->siteCoordinator->last_name }} • 
                                                        Due: {{ $task->formatted_due_date }}
                                                    </small>
                                                </div>
                                                <div class="text-end">
                                                    <span class="badge bg-{{ $task->status_badge_color }}">
                                                        {{ $task->formatted_status }}
                                                    </span>
                                                    <br><span class="badge bg-danger mt-1">Overdue</span>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-muted text-center py-3">No overdue tasks</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<style>
.opacity-75 {
    opacity: 0.75;
}
.progress {
    min-width: 60px;
}
.table-warning {
    --bs-table-accent-bg: var(--bs-warning-bg-subtle);
}
.img-thumbnail {
    border-radius: 8px;
}
</style>
@endpush