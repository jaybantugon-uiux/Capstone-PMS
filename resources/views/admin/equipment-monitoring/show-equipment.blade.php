@extends('app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-10 col-xl-9">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h1 class="h3 mb-0"><i class="fas fa-cube me-2"></i>Equipment Details</h1>
                <a href="{{ route('admin.equipment-monitoring.equipment-list') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-arrow-left me-1"></i> Back to Equipment
                </a>
            </div>

            <div class="card mb-3">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <h5 class="mb-1">{{ $monitoredEquipment->equipment_name }}</h5>
                            <div class="text-muted">{{ $monitoredEquipment->equipment_description }}</div>
                            <div class="mt-2">
                                <span class="badge bg-light text-dark">{{ ucfirst(str_replace('_',' ',$monitoredEquipment->usage_type)) }}</span>
                                <span class="badge bg-{{ $monitoredEquipment->status === 'active' ? 'success' : ($monitoredEquipment->status === 'pending_approval' ? 'warning' : 'secondary') }}">{{ ucfirst(str_replace('_',' ',$monitoredEquipment->status)) }}</span>
                                <span class="badge bg-{{ $monitoredEquipment->availability_status === 'available' ? 'success' : ($monitoredEquipment->availability_status === 'in_use' ? 'warning' : ($monitoredEquipment->availability_status === 'maintenance' ? 'info' : 'danger')) }}">{{ ucfirst(str_replace('_',' ',$monitoredEquipment->availability_status)) }}</span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <dl class="mb-0">
                                <dt>Quantity</dt>
                                <dd>{{ $monitoredEquipment->quantity }}</dd>
                                <dt>Site Coordinator</dt>
                                <dd>{{ $monitoredEquipment->user?->full_name ?? '—' }}</dd>
                                <dt>Project</dt>
                                <dd>{{ $monitoredEquipment->project?->name ?? '—' }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Linked Request & Maintenance</h5>
                    @if($monitoredEquipment->equipmentRequest)
                        <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.equipment-monitoring.show-request', $monitoredEquipment->equipmentRequest) }}">
                            <i class="fas fa-clipboard-list me-1"></i>View Request
                        </a>
                    @endif
                </div>
                <div class="card-body">
                    @if(isset($monitoredEquipment->maintenanceSchedules) && $monitoredEquipment->maintenanceSchedules->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Type</th>
                                        <th>Scheduled</th>
                                        <th>Priority</th>
                                        <th>Status</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($monitoredEquipment->maintenanceSchedules as $m)
                                        <tr>
                                            <td>{{ $m->formatted_maintenance_type ?? ucfirst($m->maintenance_type) }}</td>
                                            <td>{{ optional($m->scheduled_date)->format('M d, Y H:i') }}</td>
                                            <td><span class="badge bg-{{ $m->priority_badge_color ?? 'secondary' }}">{{ $m->formatted_priority ?? ucfirst($m->priority) }}</span></td>
                                            <td><span class="badge bg-{{ $m->status_badge_color ?? ($m->status === 'scheduled' ? 'warning' : ($m->status === 'completed' ? 'success' : 'secondary')) }}">{{ $m->formatted_status ?? ucfirst($m->status) }}</span></td>
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
                    @else
                        <p class="text-muted mb-0">No maintenance schedules for this equipment.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection


