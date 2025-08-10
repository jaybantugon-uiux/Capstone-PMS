@extends('app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h2">Equipment Monitoring Dashboard</h1>
                <div class="d-flex gap-2">
                    <a href="{{ route('sc.equipment-monitoring.create-request') }}" class="btn btn-success">
                        <i class="fas fa-truck-loading me-1"></i> Request Equipment
                    </a>
                    <a href="{{ route('sc.equipment-monitoring.create-maintenance') }}" class="btn btn-warning">
                        <i class="fas fa-tools me-1"></i> Schedule Maintenance
                    </a>
                    <a href="{{ route('sc.dashboard') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Back to Dashboard
                    </a>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-lg-3 col-md-6">
                    <div class="card bg-primary text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4 class="card-title">{{ $stats['pending_requests'] }}</h4>
                                    <p class="card-text">Pending Requests</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-clock fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="card bg-success text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4 class="card-title">{{ $stats['approved_requests'] }}</h4>
                                    <p class="card-text">Approved Requests</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-check-circle fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="card bg-info text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4 class="card-title">{{ $stats['personal_equipment'] }}</h4>
                                    <p class="card-text">Personal Equipment</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-user-cog fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="card bg-warning text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4 class="card-title">{{ $stats['project_equipment'] }}</h4>
                                    <p class="card-text">Project Equipment</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-project-diagram fa-2x"></i>
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
                            <h5 class="card-title mb-0">
                                <i class="fas fa-truck-loading me-2"></i>Recent Equipment Requests
                            </h5>
                            <a href="{{ route('sc.equipment-monitoring.requests') }}" class="btn btn-sm btn-outline-primary">View All</a>
                        </div>
                        <div class="card-body">
                            @if($equipmentRequests->count() > 0)
                                @foreach($equipmentRequests as $request)
                                    <div class="d-flex justify-content-between align-items-center mb-3 p-2 bg-light rounded">
                                        <div class="flex-grow-1">
                                            <strong>{{ $request->equipment_name }}</strong>
                                            <br><small class="text-muted">{{ $request->project->name ?? 'Personal Use' }}</small>
                                            <br><small class="text-muted">{{ $request->formatted_usage_type ?? '' }}</small>
                                        </div>
                                        <div class="text-end">
                                            <span class="badge bg-{{ $request->status_badge_color }}">{{ $request->formatted_status }}</span>
                                            @if($request->is_urgent)
                                                <br><span class="badge bg-danger mt-1">Urgent</span>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            @else
                                <p class="text-center text-muted">No equipment requests found.</p>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Upcoming Maintenance -->
                <div class="col-lg-6 mb-4">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-tools me-2"></i>Upcoming Maintenance
                            </h5>
                            <a href="{{ route('sc.equipment-monitoring.maintenance') }}" class="btn btn-sm btn-outline-primary">View All</a>
                        </div>
                        <div class="card-body">
                            @if($upcomingMaintenance->count() > 0)
                                @foreach($upcomingMaintenance as $maintenance)
                                    <div class="d-flex justify-content-between align-items-center mb-3 p-2 bg-light rounded">
                                        <div class="flex-grow-1">
                                            <strong>{{ $maintenance->monitoredEquipment->equipment_name ?? 'N/A' }}</strong>
                                            <br><small class="text-muted">{{ $maintenance->scheduled_date ? $maintenance->scheduled_date->format('M d, Y') : '-' }}</small>
                                            <br><small class="text-muted">{{ $maintenance->formatted_maintenance_type ?? '' }}</small>
                                        </div>
                                        <div class="text-end">
                                            <span class="badge bg-{{ $maintenance->status_badge_color }}">{{ $maintenance->formatted_status }}</span>
                                            @if($maintenance->is_overdue)
                                                <br><span class="badge bg-danger mt-1">Overdue</span>
                                            @elseif($maintenance->is_upcoming)
                                                <br><span class="badge bg-warning mt-1">Upcoming</span>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            @else
                                <p class="text-center text-muted">No upcoming maintenance scheduled.</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Personal Equipment -->
                <div class="col-lg-6 mb-4">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-user-cog me-2"></i>Personal Equipment
                            </h5>
                            <a href="{{ route('sc.equipment-monitoring.equipment', ['usage_type' => 'personal']) }}" class="btn btn-sm btn-outline-primary">View All</a>
                        </div>
                        <div class="card-body">
                            @if($personalEquipment->count() > 0)
                                @foreach($personalEquipment as $equipment)
                                    <div class="d-flex justify-content-between align-items-center mb-3 p-2 bg-light rounded">
                                        <div class="flex-grow-1">
                                            <strong>{{ $equipment->equipment_name }}</strong>
                                            <br><small class="text-muted">{{ $equipment->equipment_description }}</small>
                                            <br><small class="text-muted">Quantity: {{ $equipment->quantity }}</small>
                                        </div>
                                        <div class="text-end">
                                            <span class="badge bg-{{ $equipment->availability_status === 'available' ? 'success' : ($equipment->availability_status === 'in_use' ? 'warning' : 'danger') }}">
                                                {{ ucfirst(str_replace('_', ' ', $equipment->availability_status)) }}
                                            </span>
                                            @if($equipment->next_maintenance_date && $equipment->next_maintenance_date <= now()->addDays(7))
                                                <br><span class="badge bg-warning mt-1">Maintenance Due</span>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            @else
                                <p class="text-center text-muted">No personal equipment found.</p>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Project Equipment -->
                <div class="col-lg-6 mb-4">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-project-diagram me-2"></i>Project Equipment
                            </h5>
                            <a href="{{ route('sc.equipment-monitoring.equipment', ['usage_type' => 'project_site']) }}" class="btn btn-sm btn-outline-primary">View All</a>
                        </div>
                        <div class="card-body">
                            @if($projectEquipment->count() > 0)
                                @foreach($projectEquipment as $equipment)
                                    <div class="d-flex justify-content-between align-items-center mb-3 p-2 bg-light rounded">
                                        <div class="flex-grow-1">
                                            <strong>{{ $equipment->equipment_name }}</strong>
                                            <br><small class="text-muted">{{ $equipment->project->name ?? 'N/A' }}</small>
                                            <br><small class="text-muted">Quantity: {{ $equipment->quantity }}</small>
                                        </div>
                                        <div class="text-end">
                                            <span class="badge bg-{{ $equipment->availability_status === 'available' ? 'success' : ($equipment->availability_status === 'in_use' ? 'warning' : 'danger') }}">
                                                {{ ucfirst(str_replace('_', ' ', $equipment->availability_status)) }}
                                            </span>
                                            @if($equipment->next_maintenance_date && $equipment->next_maintenance_date <= now()->addDays(7))
                                                <br><span class="badge bg-warning mt-1">Maintenance Due</span>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            @else
                                <p class="text-center text-muted">No project equipment found.</p>
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
                            <h5 class="card-title mb-0">
                                <i class="fas fa-bolt me-2"></i>Quick Actions
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-lg-3 col-md-4 col-6">
                                    <a href="{{ route('sc.equipment-monitoring.create-request') }}" class="btn btn-outline-success w-100 mb-2 h-100 d-flex flex-column justify-content-center align-items-center">
                                        <i class="fas fa-truck-loading fa-2x mb-2"></i>
                                        <span>Request Equipment</span>
                                    </a>
                                </div>
                                <div class="col-lg-3 col-md-4 col-6">
                                    <a href="{{ route('sc.equipment-monitoring.create-maintenance') }}" class="btn btn-outline-warning w-100 mb-2 h-100 d-flex flex-column justify-content-center align-items-center">
                                        <i class="fas fa-tools fa-2x mb-2"></i>
                                        <span>Schedule Maintenance</span>
                                    </a>
                                </div>
                                <div class="col-lg-3 col-md-4 col-6">
                                    <a href="{{ route('sc.equipment-monitoring.requests') }}" class="btn btn-outline-primary w-100 mb-2 h-100 d-flex flex-column justify-content-center align-items-center">
                                        <i class="fas fa-clipboard-list fa-2x mb-2"></i>
                                        <span>View Requests</span>
                                    </a>
                                </div>
                                <div class="col-lg-3 col-md-4 col-6">
                                    <a href="{{ route('sc.equipment-monitoring.equipment') }}" class="btn btn-outline-info w-100 mb-2 h-100 d-flex flex-column justify-content-center align-items-center">
                                        <i class="fas fa-cogs fa-2x mb-2"></i>
                                        <span>My Equipment</span>
                                    </a>
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

@push('styles')
<style>
.card {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    border: 1px solid rgba(0, 0, 0, 0.125);
}
.btn {
    border-radius: 0.375rem;
}
.quick-action-card {
    min-height: 120px;
}
@media (max-width: 768px) {
    .quick-action-card {
        min-height: 100px;
    }
}
</style>
@endpush 