@extends('app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h2 mb-0"><i class="fas fa-wrench me-2"></i>Maintenance Schedules</h1>
                <a href="{{ route('admin.equipment-monitoring.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Back to Dashboard
                </a>
            </div>

            <!-- Filters -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select form-select-sm">
                                <option value="">All</option>
                                <option value="scheduled" {{ ($statusFilter ?? '') === 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                                <option value="completed" {{ ($statusFilter ?? '') === 'completed' ? 'selected' : '' }}>Completed</option>
                                <option value="cancelled" {{ ($statusFilter ?? '') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Type</label>
                            <select name="maintenance_type" class="form-select form-select-sm">
                                <option value="">All</option>
                                <option value="routine" {{ ($typeFilter ?? '') === 'routine' ? 'selected' : '' }}>Routine</option>
                                <option value="repair" {{ ($typeFilter ?? '') === 'repair' ? 'selected' : '' }}>Repair</option>
                                <option value="inspection" {{ ($typeFilter ?? '') === 'inspection' ? 'selected' : '' }}>Inspection</option>
                                <option value="calibration" {{ ($typeFilter ?? '') === 'calibration' ? 'selected' : '' }}>Calibration</option>
                                <option value="replacement" {{ ($typeFilter ?? '') === 'replacement' ? 'selected' : '' }}>Replacement</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Site Coordinator</label>
                            <select name="user_id" class="form-select form-select-sm">
                                <option value="">All</option>
                                @foreach($users as $u)
                                    <option value="{{ $u->id }}" {{ ($userFilter ?? '') == $u->id ? 'selected' : '' }}>{{ $u->full_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary btn-sm w-100">
                                <i class="fas fa-filter me-1"></i> Filter
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Maintenance Table -->
            <div class="card">
                <div class="card-body p-0">
                    @if($maintenances->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th>Equipment</th>
                                        <th>SC</th>
                                        <th>Project</th>
                                        <th>Type</th>
                                        <th>Scheduled</th>
                                        <th>Priority</th>
                                        <th>Status</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($maintenances as $m)
                                        <tr>
                                            <td>{{ $m->monitoredEquipment?->equipment_name ?? '—' }}</td>
                                            <td>{{ $m->monitoredEquipment?->user?->full_name ?? '—' }}</td>
                                            <td>{{ $m->monitoredEquipment?->project?->name ?? '—' }}</td>
                                            <td><span class="badge bg-light text-dark">{{ $m->formatted_maintenance_type ?? ucfirst($m->maintenance_type) }}</span></td>
                                            <td>
                                                {{ optional($m->scheduled_date)->format('M d, Y H:i') }}
                                                @if(method_exists($m, 'getIsOverdueAttribute') ? $m->is_overdue : false)
                                                    <br><span class="badge bg-danger">Overdue</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge bg-{{ $m->priority_badge_color ?? 'secondary' }}">{{ $m->formatted_priority ?? ucfirst($m->priority) }}</span>
                                            </td>
                                            <td>
                                                <span class="badge bg-{{ $m->status_badge_color ?? ($m->status === 'scheduled' ? 'warning' : ($m->status === 'completed' ? 'success' : 'secondary')) }}">{{ $m->formatted_status ?? ucfirst($m->status) }}</span>
                                            </td>
                                            <td class="text-end">
                                                <a href="{{ route('admin.equipment-monitoring.show-maintenance', $m) }}" class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="d-flex justify-content-center p-3 border-top">
                            {{ $maintenances->appends(request()->query())->links() }}
                        </div>
                    @else
                        <div class="p-5 text-center">
                            <i class="fas fa-wrench fa-3x text-muted mb-2"></i>
                            <p class="text-muted mb-0">No maintenance schedules found.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection


