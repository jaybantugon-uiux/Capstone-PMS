@extends('app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h2 mb-1">
                        <i class="fas fa-gauge-high me-2"></i>Equipment Monitoring - Admin
                    </h1>
                    <p class="text-muted mb-0">Review requests, track monitored equipment, and manage maintenance</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.equipment-monitoring.requests') }}" class="btn btn-primary">
                        <i class="fas fa-clipboard-list me-1"></i> Requests
                    </a>
                    <a href="{{ route('admin.equipment-monitoring.equipment-list') }}" class="btn btn-info">
                        <i class="fas fa-cubes me-1"></i> Equipment
                    </a>
                    <a href="{{ route('admin.equipment-monitoring.maintenance-list') }}" class="btn btn-warning">
                        <i class="fas fa-wrench me-1"></i> Maintenance
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
                                    <h4 class="card-title">{{ $stats['total_requests'] ?? 0 }}</h4>
                                    <p class="card-text">Total Requests</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-clipboard-list fa-2x"></i>
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
                                    <h4 class="card-title">{{ $stats['pending_requests'] ?? 0 }}</h4>
                                    <p class="card-text">Pending</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-clock fa-2x"></i>
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
                                    <h4 class="card-title">{{ $stats['approved_requests'] ?? 0 }}</h4>
                                    <p class="card-text">Approved</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-check-circle fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-danger text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4 class="card-title">{{ $stats['maintenance_overdue'] ?? 0 }}</h4>
                                    <p class="card-text">Overdue Maint.</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-triangle-exclamation fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Recent Equipment Requests -->
                <div class="col-lg-6 mb-4">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">Recent Equipment Requests</h5>
                            <a href="{{ route('admin.equipment-monitoring.requests') }}" class="btn btn-sm btn-outline-primary">View All</a>
                        </div>
                        <div class="card-body">
                            @if($equipmentRequests->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Equipment</th>
                                                <th>SC</th>
                                                <th>Project/Type</th>
                                                <th>Status</th>
                                                <th>Requested</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($equipmentRequests as $req)
                                                <tr>
                                                    <td><strong>{{ Str::limit($req->equipment_name, 28) }}</strong></td>
                                                    <td>{{ $req->user?->full_name }}</td>
                                                    <td>{{ $req->project?->name ?? $req->formatted_usage_type }}</td>
                                                    <td><span class="badge bg-{{ $req->status_badge_color ?? 'secondary' }}">{{ $req->formatted_status ?? ucfirst($req->status) }}</span></td>
                                                    <td>{{ $req->created_at->diffForHumans() }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <p class="text-muted text-center mb-0">No recent equipment requests</p>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Quick Glance: Equipment & Maintenance -->
                <div class="col-lg-6 mb-4">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">Overview</h5>
                            <div>
                                <a href="{{ route('admin.equipment-monitoring.equipment-list') }}" class="btn btn-sm btn-outline-info">Equipment</a>
                                <a href="{{ route('admin.equipment-monitoring.maintenance-list') }}" class="btn btn-sm btn-outline-warning">Maintenance</a>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="bg-light p-3 rounded h-100">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h5 class="mb-1">{{ $stats['total_equipment'] ?? 0 }}</h5>
                                                <small class="text-muted">Total Equipment</small>
                                            </div>
                                            <i class="fas fa-cubes text-info"></i>
                                        </div>
                                        <div class="mt-2">
                                            <span class="badge bg-success">Active: {{ $stats['active_equipment'] ?? 0 }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="bg-light p-3 rounded h-100">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h5 class="mb-1">{{ $stats['maintenance_scheduled'] ?? 0 }}</h5>
                                                <small class="text-muted">Scheduled Maint.</small>
                                            </div>
                                            <i class="fas fa-wrench text-warning"></i>
                                        </div>
                                        <div class="mt-2">
                                            <span class="badge bg-danger">Overdue: {{ $stats['maintenance_overdue'] ?? 0 }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection


