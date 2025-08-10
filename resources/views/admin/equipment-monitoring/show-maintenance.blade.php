@extends('app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-9">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h1 class="h3 mb-0"><i class="fas fa-wrench me-2"></i>Maintenance Details</h1>
                <a href="{{ route('admin.equipment-monitoring.maintenance-list') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-arrow-left me-1"></i> Back to Maintenance
                </a>
            </div>

            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if($errors->any())
                <div class="alert alert-danger">{{ $errors->first() }}</div>
            @endif

            <div class="card mb-3">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-7">
                            <h5 class="mb-1">{{ $equipmentMaintenance->monitoredEquipment?->equipment_name ?? '—' }}</h5>
                            <div class="text-muted">{{ $equipmentMaintenance->monitoredEquipment?->equipment_description }}</div>
                            <div class="mt-2 d-flex flex-wrap gap-2">
                                <span class="badge bg-light text-dark">{{ $equipmentMaintenance->formatted_maintenance_type ?? ucfirst($equipmentMaintenance->maintenance_type) }}</span>
                                <span class="badge bg-{{ $equipmentMaintenance->priority_badge_color ?? 'secondary' }}">{{ $equipmentMaintenance->formatted_priority ?? ucfirst($equipmentMaintenance->priority) }}</span>
                                <span class="badge bg-{{ $equipmentMaintenance->status_badge_color ?? ($equipmentMaintenance->status === 'scheduled' ? 'warning' : ($equipmentMaintenance->status === 'completed' ? 'success' : 'secondary')) }}">{{ $equipmentMaintenance->formatted_status ?? ucfirst($equipmentMaintenance->status) }}</span>
                            </div>
                        </div>
                        <div class="col-md-5">
                            <dl class="mb-0">
                                <dt>Scheduled</dt>
                                <dd>{{ optional($equipmentMaintenance->scheduled_date)->format('M d, Y H:i') }}</dd>
                                <dt>Estimated Duration</dt>
                                <dd>{{ $equipmentMaintenance->estimated_duration }} min</dd>
                                <dt>SC</dt>
                                <dd>{{ $equipmentMaintenance->monitoredEquipment?->user?->full_name ?? '—' }}</dd>
                                <dt>Project</dt>
                                <dd>{{ $equipmentMaintenance->monitoredEquipment?->project?->name ?? '—' }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Admin Actions</h5>
                </div>
                <div class="card-body">
                    @if($equipmentMaintenance->status === 'scheduled')
                        <div class="row g-3">
                            <div class="col-md-6">
                                <form method="POST" action="{{ route('admin.equipment-monitoring.complete-maintenance', $equipmentMaintenance) }}">
                                    @csrf
                                    <div class="mb-2">
                                        <label class="form-label">Actual Duration (minutes)</label>
                                        <input type="number" name="actual_duration" class="form-control" min="1" max="480">
                                    </div>
                                    <div class="mb-2">
                                        <label class="form-label">Cost (₱)</label>
                                        <input type="number" step="0.01" name="cost" class="form-control" min="0" max="999999.99">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Completion Notes</label>
                                        <textarea name="completion_notes" class="form-control" rows="2"></textarea>
                                    </div>
                                    <button type="submit" class="btn btn-success w-100">
                                        <i class="fas fa-check me-1"></i> Mark as Completed
                                    </button>
                                </form>
                            </div>
                            <div class="col-md-6">
                                <form method="POST" action="{{ route('admin.equipment-monitoring.cancel-maintenance', $equipmentMaintenance) }}">
                                    @csrf
                                    <div class="mb-3">
                                        <label class="form-label">Cancel Reason</label>
                                        <textarea name="cancel_reason" class="form-control" rows="5" required></textarea>
                                    </div>
                                    <button type="submit" class="btn btn-danger w-100">
                                        <i class="fas fa-ban me-1"></i> Cancel Maintenance
                                    </button>
                                </form>
                            </div>
                        </div>
                    @else
                        <p class="text-muted mb-0">No actions available for {{ ucfirst($equipmentMaintenance->status) }} maintenance.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection


