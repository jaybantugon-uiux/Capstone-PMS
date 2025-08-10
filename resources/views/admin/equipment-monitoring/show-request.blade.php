@extends('app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-10 col-xl-9">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h1 class="h3 mb-0">
                    <i class="fas fa-clipboard-list me-2"></i>Equipment Request Details
                </h1>
                <a href="{{ route('admin.equipment-monitoring.requests') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-arrow-left me-1"></i> Back to Requests
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
                        <div class="col-md-6">
                            <h5 class="mb-1">{{ $equipmentRequest->equipment_name }}</h5>
                            <small class="text-muted">Qty: {{ $equipmentRequest->quantity }}</small>
                            <p class="mt-2 mb-0">{{ $equipmentRequest->equipment_description }}</p>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex flex-wrap gap-2 justify-content-md-end">
                                <span class="badge bg-{{ $equipmentRequest->status_badge_color ?? 'secondary' }}">{{ $equipmentRequest->formatted_status ?? ucfirst($equipmentRequest->status) }}</span>
                                <span class="badge bg-{{ $equipmentRequest->urgency_badge_color ?? 'info' }}">{{ $equipmentRequest->formatted_urgency ?? ucfirst($equipmentRequest->urgency_level) }}</span>
                                <span class="badge bg-light text-dark">{{ $equipmentRequest->formatted_usage_type ?? ucfirst(str_replace('_',' ',$equipmentRequest->usage_type)) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-7 mb-3">
                    <div class="card h-100">
                        <div class="card-header">
                            <h5 class="mb-0">Request Information</h5>
                        </div>
                        <div class="card-body">
                            <dl class="row mb-0">
                                <dt class="col-sm-4">Requested By</dt>
                                <dd class="col-sm-8">{{ $equipmentRequest->user?->full_name }}</dd>

                                <dt class="col-sm-4">Project</dt>
                                <dd class="col-sm-8">{{ $equipmentRequest->project?->name ?? 'Personal Use' }}</dd>

                                <dt class="col-sm-4">Estimated Cost</dt>
                                <dd class="col-sm-8">{{ $equipmentRequest->estimated_cost ? '₱' . number_format($equipmentRequest->estimated_cost, 2) : '—' }}</dd>

                                <dt class="col-sm-4">Justification</dt>
                                <dd class="col-sm-8">{{ $equipmentRequest->justification }}</dd>

                                <dt class="col-sm-4">Additional Notes</dt>
                                <dd class="col-sm-8">{{ $equipmentRequest->additional_notes ?: '—' }}</dd>

                                <dt class="col-sm-4">Requested At</dt>
                                <dd class="col-sm-8">{{ $equipmentRequest->created_at->format('M d, Y H:i') }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
                <div class="col-md-5 mb-3">
                    <div class="card h-100">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Approval</h5>
                            @if($equipmentRequest->approvedBy)
                                <small class="text-muted">by {{ $equipmentRequest->approvedBy->full_name }} on {{ optional($equipmentRequest->approved_at)->format('M d, Y H:i') }}</small>
                            @endif
                        </div>
                        <div class="card-body">
                            @if($equipmentRequest->status === 'pending')
                                <form class="mb-2" method="POST" action="{{ route('admin.equipment-monitoring.approve-request', $equipmentRequest) }}">
                                    @csrf
                                    <div class="mb-2">
                                        <label class="form-label">Admin Notes (optional)</label>
                                        <textarea name="admin_notes" class="form-control" rows="2" placeholder="Notes for approval..."></textarea>
                                    </div>
                                    <button type="submit" class="btn btn-success w-100">
                                        <i class="fas fa-check me-1"></i> Approve Request
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('admin.equipment-monitoring.decline-request', $equipmentRequest) }}">
                                    @csrf
                                    <div class="mb-2">
                                        <label class="form-label">Decline Reason</label>
                                        <textarea name="decline_reason" class="form-control" rows="2" required placeholder="Provide reason for decline..."></textarea>
                                    </div>
                                    <button type="submit" class="btn btn-danger w-100">
                                        <i class="fas fa-times me-1"></i> Decline Request
                                    </button>
                                </form>
                            @else
                                <div class="alert alert-{{ $equipmentRequest->status === 'approved' ? 'success' : 'danger' }} mb-0">
                                    This request has been {{ $equipmentRequest->formatted_status ?? $equipmentRequest->status }}.
                                </div>
                                @if($equipmentRequest->decline_reason)
                                    <div class="mt-2">
                                        <strong>Reason:</strong> {{ $equipmentRequest->decline_reason }}
                                    </div>
                                @endif
                                @if($equipmentRequest->admin_notes)
                                    <div class="mt-2">
                                        <strong>Notes:</strong> {{ $equipmentRequest->admin_notes }}
                                    </div>
                                @endif
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Linked Monitored Equipment</h5>
                    @if($equipmentRequest->monitoredEquipment)
                        <a href="{{ route('admin.equipment-monitoring.show-equipment', $equipmentRequest->monitoredEquipment) }}" class="btn btn-sm btn-outline-info">View Equipment</a>
                    @endif
                </div>
                <div class="card-body">
                    @if($equipmentRequest->monitoredEquipment)
                        @php($me = $equipmentRequest->monitoredEquipment)
                        <div class="row g-3">
                            <div class="col-md-4">
                                <div class="bg-light rounded p-3 h-100">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <div class="fw-bold">Status</div>
                                            <div><span class="badge bg-{{ $me->status === 'active' ? 'success' : ($me->status === 'pending_approval' ? 'warning' : 'secondary') }}">{{ ucfirst(str_replace('_',' ',$me->status)) }}</span></div>
                                        </div>
                                        <i class="fas fa-cog text-secondary"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="bg-light rounded p-3 h-100">
                                    <div class="fw-bold">Availability</div>
                                    <div><span class="badge bg-{{ $me->availability_status === 'available' ? 'success' : ($me->availability_status === 'in_use' ? 'warning' : ($me->availability_status === 'maintenance' ? 'info' : 'danger')) }}">{{ ucfirst(str_replace('_',' ',$me->availability_status)) }}</span></div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="bg-light rounded p-3 h-100">
                                    <div class="fw-bold">Project</div>
                                    <div>{{ $me->project?->name ?? '—' }}</div>
                                </div>
                            </div>
                        </div>
                    @else
                        <p class="text-muted mb-0">No monitored equipment is linked to this request.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection


